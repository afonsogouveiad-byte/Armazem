<?php
require_once __DIR__ . '/connexio.php';

$tableNames = ['items','productes','produtos','products','materials'];
$selectedTable = null;
foreach ($tableNames as $t) {
    $r = $conn->query("SHOW TABLES LIKE '" . $conn->real_escape_string($t) . "'");
    if ($r && $r->num_rows) { $selectedTable = $t; break; }
}
if ($selectedTable === null) {
    echo 'No table found.'; exit;
}

// determine writable columns
$cols = [];
$res = $conn->query("SHOW COLUMNS FROM `" . $conn->real_escape_string($selectedTable) . "`");
while ($row = $res->fetch_assoc()) { $cols[] = $row['Field']; }

// map common names to actual columns
$map = [];
foreach (['name'=>'name','title'=>'title','product'=>'product','stock'=>'stock','image'=>'image','category'=>'category'] as $k => $_) {
    foreach ([$k, $k.'_id', $k.'e', substr($k,0,4)] as $cand) {
        if (in_array($cand, $cols, true)) { $map[$k] = $cand; break; }
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values = [];
    $insertCols = [];
    $placeholders = [];

    // handle uploaded image file
    $imageValue = '';
    if (!empty($_FILES['image_file']['name']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['image_file']['tmp_name'];
        $name = basename($_FILES['image_file']['name']);
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','gif'], true)) { $error = 'Tipus d\'imatge no permès.'; }
        else {
            $targetDir = dirname(__DIR__) . '/images/';
            if (!is_dir($targetDir)) @mkdir($targetDir, 0755, true);
            $newName = uniqid('img_') . '.' . $ext;
            if (move_uploaded_file($tmp, $targetDir . $newName)) { $imageValue = 'images/' . $newName; }
            else { $error = 'No s\'ha pogut pujar la imatge.'; }
        }
    }

    foreach (['name','stock','image'] as $k) {
        if (isset($map[$k])) {
            $val = null;
            if ($k === 'image') $val = $imageValue;
            else $val = $_POST[$k] ?? null;
            if ($val !== null && $val !== '') {
                $insertCols[] = '`' . $conn->real_escape_string($map[$k]) . '`';
                $placeholders[] = '?';
                $values[] = $val;
            }
        }
    }

    if (empty($insertCols) && $error === '') { $error = 'No hi ha camps per inserir.'; }
    if ($error === '') {
        $sql = 'INSERT INTO `'. $conn->real_escape_string($selectedTable) .'` (' . implode(',', $insertCols) . ') VALUES (' . implode(',', $placeholders) . ')';
        $stmt = $conn->prepare($sql);
        if (!$stmt) { $error = 'Error al preparar: ' . $conn->error; }
        else {
            $types = str_repeat('s', count($values));
            $stmt->bind_param($types, ...$values);
            if ($stmt->execute()) {
                header('Location: itens.php'); exit;
            } else { $error = 'Execute failed: ' . $stmt->error; }
        }
    }
}
?>
<!doctype html>
<html lang="ca">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Afegir ítem</title>
  <style>
    :root{--bg:#f4f6f9;--card:#fff;--primary:#1e3a5f}
    body{font-family:Inter,Arial,Helvetica,sans-serif;padding:28px;background:var(--bg);color:#0f172a}
    .container{max-width:760px;margin:18px auto}
    .card{background:var(--card);padding:20px;border-radius:12px;box-shadow:0 12px 30px rgba(2,6,23,0.08)}
    h1{margin-bottom:8px;color:var(--primary)}
    label{display:block;margin-top:12px;font-weight:600;color:#334155}
    input[type=text],input[type=number],input[type=file]{width:100%;padding:10px;margin-top:8px;border:1px solid #e6eef6;border-radius:8px}
    .row{display:flex;gap:12px}
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
      <h1>Afegir ítem</h1>
      <?php if (!empty($error)): ?><div style="color:#b91c1c;margin-bottom:12px"> <?= htmlspecialchars($error) ?></div><?php endif; ?>
      <form method="post" enctype="multipart/form-data">
        <label for="name">Nom / Títol</label>
        <input type="text" name="name" id="name" required>

        <label for="stock">Stock</label>
        <input type="number" name="stock" id="stock" step="1" value="0">

        <label for="image_file">Puja una imatge</label>
        <input type="file" name="image_file" id="image_file" accept="image/*" required>

        <img id="preview" class="img-preview" src="" alt="" style="display:none">

        <div class="actions">
          <button type="submit" class="btn btn-primary">Crear</button>
          <a href="itens.php" class="btn btn-muted">Cancelar</a>
        </div>
      </form>
    </div>
  </div>

  <script>
    const imageInput = document.getElementById('image');
    const fileInput = document.getElementById('image_file');
    const preview = document.getElementById('preview');

    function showPreview(src){
      if (!src){ preview.style.display='none'; preview.src=''; return; }
      preview.src = src; preview.style.display='block';
    }

    imageInput.addEventListener('input', e => { if (e.target.value) showPreview(e.target.value); else showPreview(''); });
    fileInput.addEventListener('change', e => {
      const f = e.target.files[0];
      if (!f) return;
      const url = URL.createObjectURL(f);
      showPreview(url);
    });
  </script>
</body>
</html>
