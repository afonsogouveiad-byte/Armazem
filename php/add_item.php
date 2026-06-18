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
$columnCandidates = [
    'name' => ['name','nom','nome','title','product','producte','material','item'],
    'stock' => ['stock','estoc','quantitat','quantidade','quantity','qty','available'],
    'image' => ['image','imagem','imatge','foto','img','foto_url','image_url','url'],
    'category' => ['category','categoria','type','tipus']
];
foreach ($columnCandidates as $logical => $candidates) {
    foreach ($candidates as $cand) {
        if (in_array($cand, $cols, true)) {
            $map[$logical] = $cand;
            break;
        }
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values = [];
    $insertCols = [];
    $placeholders = [];

    // handle uploaded image file
    $imageValue = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK && $_FILES['image']['name'] !== '') {
        $maxSize = 100 * 1024 * 1024; // 100 MB
        if ($_FILES['image']['size'] > $maxSize) {
            $error = 'Error: la imatge és massa gran.';
        } else {
            $check = getimagesize($_FILES['image']['tmp_name']);
            if ($check === false) {
                $error = 'Error: el fitxer no és una imatge vàlida.';
            } else {
                $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!in_array($check['mime'], $allowed, true)) {
                    $error = 'Error: tipus d\'imatge no permès.';
                } else {
                    $uploadDir = __DIR__ . '/uploads/';
                    if (!is_dir($uploadDir)) {
                        @mkdir($uploadDir, 0777, true);
                    }
                    if (is_dir($uploadDir) && !is_writable($uploadDir)) {
                        @chmod($uploadDir, 0777);
                    }

                    if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
                        $error = 'Error: no es pot escriure al directori d\'imatges.';
                    } else {
                        $imageFileName = uniqid('img_', true) . '_' . preg_replace(
                            '/[^A-Za-z0-9._-]/',
                            '_',
                            basename($_FILES['image']['name'])
                        );
                        if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageFileName)) {
                            $error = 'No s\'ha pogut pujar la imatge.';
                        } else {
                            $imageValue = 'uploads/' . $imageFileName;
                        }
                    }
                }
            }
        }
    }

    foreach (['name','stock','image'] as $k) {
        if (isset($map[$k])) {
            if ($k === 'image') {
                $val = $imageValue;
                if ($val === '') {
                    $val = '';
                }
            } else {
                $val = $_POST[$k] ?? null;
            }
            if ($val !== null) {
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

        <label for="image">Puja una imatge</label>
        <input type="file" name="image" id="image" accept="image/*" required>

        <img id="preview" class="img-preview" src="" alt="" style="display:none">

        <div class="actions">
          <button type="submit" class="btn btn-primary">Crear</button>
          <a href="itens.php" class="btn btn-muted">Cancelar</a>
        </div>
      </form>
    </div>
  </div>

  <script>
    const fileInput = document.getElementById('image');
    const preview = document.getElementById('preview');

    function showPreview(src){
      if (!src){ preview.style.display='none'; preview.src=''; return; }
      preview.src = src; preview.style.display='block';
    }

    fileInput.addEventListener('change', e => {
      const f = e.target.files[0];
      if (!f) return;
      const url = URL.createObjectURL(f);
      showPreview(url);
    });
  </script>
</body>
</html>
