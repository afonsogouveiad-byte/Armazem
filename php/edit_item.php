<?php
require_once __DIR__ . '/connexio.php';

$tableNames = ['items','productes','produtos','products','materials'];
$selectedTable = null;
foreach ($tableNames as $t) {
    $r = $conn->query("SHOW TABLES LIKE '" . $conn->real_escape_string($t) . "'");
    if ($r && $r->num_rows) { $selectedTable = $t; break; }
}
if ($selectedTable === null) { echo 'No table'; exit; }

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: itens.php'); exit; }

// find columns
$cols = [];
$res = $conn->query("SHOW COLUMNS FROM `" . $conn->real_escape_string($selectedTable) . "`");
while ($row = $res->fetch_assoc()) { $cols[] = $row['Field']; }

// find pk
$pk = null;
$res2 = $conn->query("SHOW COLUMNS FROM `" . $conn->real_escape_string($selectedTable) . "`");
while ($r = $res2->fetch_assoc()) { if (strpos($r['Extra'] ?? '', 'auto_increment') !== false) { $pk = $r['Field']; break; } }
if ($pk === null) { foreach (['id','ID','item_id','product_id'] as $c) { if (in_array($c,$cols,true)) { $pk = $c; break; } } }
if ($pk === null) { echo 'PK not found'; exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $updates = [];
  $values = [];
  // handle file upload
  $uploadedImage = null;
  if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $tmp = $_FILES['image']['tmp_name'];
    $name = basename($_FILES['image']['name']);
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg','jpeg','png','gif','webp'], true)) {
      $targetDir = __DIR__ . '/../uploads/';
      if (!is_dir($targetDir)) @mkdir($targetDir, 0755, true);
      $newName = uniqid('img_') . '.' . $ext;
      if (is_dir($targetDir) && is_writable($targetDir) && move_uploaded_file($tmp, $targetDir . $newName)) {
        $uploadedImage = 'uploads/' . $newName;
      }
    }
  }
  // map POSTed columns to update set
  $set = [];
  foreach ($cols as $c) {
    if ($c === $pk) continue;
    if ($uploadedImage !== null && (strpos(strtolower($c),'image') !== false || strpos(strtolower($c),'img') !== false || strpos(strtolower($c),'foto') !== false)) {
      $set[] = "`$c` = ?"; $values[] = $uploadedImage;
      continue;
    }
    if (array_key_exists($c, $_POST)) { $set[] = "`$c` = ?"; $values[] = $_POST[$c]; }
  }
    if (!empty($set)) {
        $sql = 'UPDATE `'. $conn->real_escape_string($selectedTable) .'` SET ' . implode(',', $set) . ' WHERE `'. $conn->real_escape_string($pk) .'` = ? LIMIT 1';
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $types = str_repeat('s', count($values)) . 's';
            $values[] = $id;
            $stmt->bind_param($types, ...$values);
            $stmt->execute();
            $stmt->close();
            header('Location: itens.php'); exit;
        }
    }
}

// fetch current row
$stmt = $conn->prepare('SELECT * FROM `'. $conn->real_escape_string($selectedTable) .'` WHERE `'. $conn->real_escape_string($pk) .'` = ? LIMIT 1');
$stmt->bind_param('s', $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

?>
<!doctype html>
<html lang="ca">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Editar ítem</title>
  <style>
    :root{--bg:#f4f6f9;--card:#fff;--primary:#1e3a5f}
    body{font-family:Inter,Arial;padding:28px;background:var(--bg);color:#0f172a}
    .container{max-width:760px;margin:18px auto}
    .card{background:var(--card);padding:20px;border-radius:12px;box-shadow:0 12px 30px rgba(2,6,23,0.08)}
    h1{margin-bottom:8px;color:var(--primary)}
    label{display:block;margin-top:12px;font-weight:600;color:#334155}
    input[type=text],input[type=number],input[type=file]{width:100%;padding:10px;margin-top:8px;border:1px solid #e6eef6;border-radius:8px}
    .actions{margin-top:18px;display:flex;gap:10px}
    .btn{padding:10px 14px;border-radius:8px;text-decoration:none;display:inline-block;font-weight:600}
    .btn-primary{background:var(--primary);color:#fff;border:none}
    .btn-muted{background:#f1f5f9;border:1px solid #e2e8f0}
    .img-preview{margin-top:12px;max-height:160px;border-radius:8px;object-fit:cover;border:1px solid #e2e8f0}
  </style>
</head>
<body>
  <div class="container">
    <div class="card">
      <h1>Editar ítem</h1>
      <form method="post" enctype="multipart/form-data">
        <?php foreach ($cols as $c): ?>
          <?php if ($c === $pk) continue; ?>
          <label for="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></label>
          <?php if (strpos(strtolower($c),'stock') !== false): ?>
            <input type="number" name="<?= htmlspecialchars($c) ?>" id="<?= htmlspecialchars($c) ?>" value="<?= htmlspecialchars($row[$c] ?? '') ?>">
          <?php elseif (strpos(strtolower($c),'image') !== false || strpos(strtolower($c),'img') !== false || strpos(strtolower($c),'foto') !== false): ?>
            <input type="file" name="image" accept="image/*">
            <img id="preview" class="img-preview" src="<?= htmlspecialchars($row[$c] ?? '') ?>" <?= empty($row[$c]) ? 'style="display:none"' : '' ?> >
            <input type="hidden" name="<?= htmlspecialchars($c) ?>" value="<?= htmlspecialchars($row[$c] ?? '') ?>">
          <?php else: ?>
            <input type="text" name="<?= htmlspecialchars($c) ?>" id="<?= htmlspecialchars($c) ?>" value="<?= htmlspecialchars($row[$c] ?? '') ?>">
          <?php endif; ?>
        <?php endforeach; ?>
        <div class="actions">
          <button class="btn btn-primary" type="submit">Desar</button>
          <a href="itens.php" class="btn btn-muted">Cancelar</a>
        </div>
      </form>
    </div>
  </div>

  <script>
    const fileInput = document.querySelector('input[type=file]');
    const preview = document.getElementById('preview');
    if (fileInput) fileInput.addEventListener('change', e => {
      const f = e.target.files[0];
      if (!f) return; const url = URL.createObjectURL(f); preview.src = url; preview.style.display = 'block';
    });
  </script>
</body>
</html>
