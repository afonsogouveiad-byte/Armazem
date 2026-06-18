<?php
require_once __DIR__ . '/connexio.php';

$tableNames = ['items','productes','produtos','products','materials'];

$selectedTable = null;
foreach ($tableNames as $t) {
    $r = $conn->query("SHOW TABLES LIKE '" . $conn->real_escape_string($t) . "'");
    if ($r && $r->num_rows) { 
        $selectedTable = $t; 
        break; 
    }
}

if ($selectedTable === null) {
    echo 'No table found';
    exit;
}

/* GET COLUMNS */
$cols = [];
$res = $conn->query("SHOW COLUMNS FROM `" . $conn->real_escape_string($selectedTable) . "`");
while ($row = $res->fetch_assoc()) {
    $cols[] = $row['Field'];
}

/* MAP */
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

/* VALIDATION STOCK (BACKEND SAFETY) */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stockValue = $_POST['stock'] ?? 0;

    if (!is_numeric($stockValue) || (int)$stockValue < 0) {
        $error = 'O stock não pode ser negativo.';
    }

    $values = [];
    $insertCols = [];
    $placeholders = [];

    /* IMAGE UPLOAD (OPTIONAL) */
    $imageValue = '';

    if (!$error && isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK && $_FILES['image']['name'] !== '') {

        $check = getimagesize($_FILES['image']['tmp_name']);

        if ($check === false) {
            $error = 'Ficheiro não é uma imagem válida';
        } else {

            $allowed = ['image/jpeg','image/png','image/gif','image/webp'];

            if (!in_array($check['mime'], $allowed, true)) {
                $error = 'Tipo de imagem não permitido';
            } else {

                $uploadDir = __DIR__ . '/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $newName = uniqid('img_', true) . '.' . $ext;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newName)) {
                    $imageValue = 'uploads/' . $newName;
                } else {
                    $error = 'Erro ao enviar imagem';
                }
            }
        }
    }

    /* INSERT BUILD */
    foreach (['name','stock','image'] as $k) {
        if (!isset($map[$k])) continue;

        if ($k === 'image') {
            $val = $imageValue; // opcional
        } elseif ($k === 'stock') {
            $val = $_POST['stock'] ?? 0;
            if ($val < 0) $val = 0;
        } else {
            $val = $_POST[$k] ?? null;
        }

        if ($val !== null) {
            $insertCols[] = '`' . $conn->real_escape_string($map[$k]) . '`';
            $placeholders[] = '?';
            $values[] = $val;
        }
    }

    if (empty($insertCols) && $error === '') {
        $error = 'No fields to insert';
    }

    if ($error === '') {

        $sql = 'INSERT INTO `'.$conn->real_escape_string($selectedTable).'`
                (' . implode(',', $insertCols) . ')
                VALUES (' . implode(',', $placeholders) . ')';

        $stmt = $conn->prepare($sql);

        if ($stmt) {

            $types = str_repeat('s', count($values));
            $stmt->bind_param($types, ...$values);

            if ($stmt->execute()) {
                header('Location: itens.php');
                exit;
            } else {
                $error = $stmt->error;
            }

        } else {
            $error = $conn->error;
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
:root{
    --bg:#f4f6f9;
    --card:#fff;
    --primary:#1e3a5f;
}

body{
    font-family:system-ui,Arial;
    background:var(--bg);
    padding:28px;
    color:#0f172a;
}

.container{
    max-width:760px;
    margin:auto;
}

.card{
    background:var(--card);
    padding:20px;
    border-radius:12px;
    box-shadow:0 10px 30px rgba(0,0,0,0.08);
}

h1{
    color:var(--primary);
}

label{
    display:block;
    margin-top:12px;
    font-weight:600;
}

input{
    width:100%;
    padding:10px;
    margin-top:6px;
    border:1px solid #e2e8f0;
    border-radius:8px;
}

.actions{
    margin-top:18px;
    display:flex;
    gap:10px;
}

button{
    padding:10px 14px;
    border:none;
    border-radius:8px;
    background:var(--primary);
    color:white;
    cursor:pointer;
}

a{
    padding:10px 14px;
    border-radius:8px;
    background:#e2e8f0;
    text-decoration:none;
    color:black;
}

.img-preview{
    margin-top:10px;
    max-height:160px;
    display:block;
}
</style>
</head>

<body>

<div class="container">
<div class="card">

<h1>Afegir ítem</h1>

<?php if (!empty($error)): ?>
    <p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">

<label>Nome</label>
<input type="text" name="name" required>

<label>Stock</label>
<input type="number" name="stock" min="0" value="0" required>

<label>Imagem</label>
<input type="file" name="image" accept="image/*">

<img id="preview" class="img-preview" style="display:none">

<div class="actions">
    <button type="submit">Criar</button>
    <a href="itens.php">Cancelar</a>
</div>

</form>

</div>
</div>

<script>
const fileInput = document.querySelector('input[type=file]');
const preview = document.getElementById('preview');

if (fileInput) {
    fileInput.addEventListener('change', e => {
        const f = e.target.files[0];
        if (!f) return;
        preview.src = URL.createObjectURL(f);
        preview.style.display = 'block';
    });
}
</script>

</body>
</html>