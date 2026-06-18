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

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: itens.php');
    exit;
}

/* GET COLUMNS */
$cols = [];
$res = $conn->query("SHOW COLUMNS FROM `" . $conn->real_escape_string($selectedTable) . "`");
while ($row = $res->fetch_assoc()) {
    $cols[] = $row['Field'];
}

/* FIND PRIMARY KEY */
$pk = null;
$res2 = $conn->query("SHOW COLUMNS FROM `" . $conn->real_escape_string($selectedTable) . "`");

while ($r = $res2->fetch_assoc()) {
    if (strpos($r['Extra'] ?? '', 'auto_increment') !== false) {
        $pk = $r['Field'];
        break;
    }
}

if ($pk === null) {
    foreach (['id','ID','item_id','product_id'] as $c) {
        if (in_array($c, $cols, true)) {
            $pk = $c;
            break;
        }
    }
}

if ($pk === null) {
    echo 'PK not found';
    exit;
}

$error = '';

/* UPDATE */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $values = [];
    $set = [];

    /* STOCK VALIDATION */
    foreach ($cols as $c) {
        if (strpos(strtolower($c), 'stock') !== false) {
            $stock = $_POST[$c] ?? 0;
            if (!is_numeric($stock) || $stock < 0) {
                $error = "Stock não pode ser negativo";
            }
        }
    }

    /* IMAGE UPLOAD (OPTIONAL) */
    $uploadedImage = null;

    if (!$error && !empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {

        $tmp = $_FILES['image']['tmp_name'];

        $check = getimagesize($tmp);

        if ($check === false) {
            $error = "Ficheiro inválido";
        } else {

            $allowed = ['image/jpeg','image/png','image/gif','image/webp'];

            if (!in_array($check['mime'], $allowed, true)) {
                $error = "Tipo de imagem não permitido";
            } else {

                $targetDir = __DIR__ . '/uploads/';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $newName = uniqid('img_', true) . '.' . $ext;

                if (move_uploaded_file($tmp, $targetDir . $newName)) {
                    $uploadedImage = 'uploads/' . $newName;
                } else {
                    $error = "Erro no upload";
                }
            }
        }
    }

    /* BUILD UPDATE */
    foreach ($cols as $c) {

        if ($c === $pk) continue;

        if ($uploadedImage !== null && (
            strpos(strtolower($c),'image') !== false ||
            strpos(strtolower($c),'img') !== false ||
            strpos(strtolower($c),'foto') !== false
        )) {
            $set[] = "`$c` = ?";
            $values[] = $uploadedImage;
            continue;
        }

        if (isset($_POST[$c])) {
            $val = $_POST[$c];

            if (strpos(strtolower($c), 'stock') !== false) {
                if ($val < 0) $val = 0;
            }

            $set[] = "`$c` = ?";
            $values[] = $val;
        }
    }

    if (empty($set) && $error === '') {
        $error = "Nada para atualizar";
    }

    if ($error === '') {

        $sql = "UPDATE `".$conn->real_escape_string($selectedTable)."`
                SET ".implode(',', $set)."
                WHERE `".$conn->real_escape_string($pk)."` = ?
                LIMIT 1";

        $stmt = $conn->prepare($sql);

        if ($stmt) {

            $types = str_repeat('s', count($values)) . 's';
            $values[] = $id;

            $stmt->bind_param($types, ...$values);
            $stmt->execute();
            $stmt->close();

            header('Location: itens.php');
            exit;

        } else {
            $error = "Erro SQL";
        }
    }
}

/* FETCH ITEM */
$stmt = $conn->prepare(
    "SELECT * FROM `".$conn->real_escape_string($selectedTable)."`
     WHERE `".$conn->real_escape_string($pk)."` = ?
     LIMIT 1"
);

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
<link rel="icon" href="images/inspedr.jpg" type="image/jpeg">

<style>
body{
    font-family:system-ui,Arial;
    background:#f4f6f9;
    padding:30px;
    color:#0f172a;
}

.container{
    max-width:800px;
    margin:auto;
}

.card{
    background:white;
    padding:20px;
    border-radius:12px;
    box-shadow:0 10px 30px rgba(0,0,0,0.08);
}

h1{
    color:#1e3a5f;
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
    background:#1e3a5f;
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
    border-radius:8px;
}
</style>
</head>

<body>

<div class="container">
<div class="card">

<h1>Editar ítem</h1>

<?php if (!empty($error)): ?>
    <p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">

<?php foreach ($cols as $c): ?>
    <?php if ($c === $pk) continue; ?>

    <label><?= htmlspecialchars($c) ?></label>

    <?php if (strpos(strtolower($c), 'stock') !== false): ?>
        <input type="number" name="<?= $c ?>" min="0" value="<?= htmlspecialchars($row[$c] ?? '') ?>">

    <?php elseif (
        strpos(strtolower($c), 'image') !== false ||
        strpos(strtolower($c), 'img') !== false ||
        strpos(strtolower($c), 'foto') !== false
    ): ?>

        <input type="file" name="image" accept="image/*">

        <?php if (!empty($row[$c])): ?>
            <img id="preview" class="img-preview" src="<?= htmlspecialchars($row[$c]) ?>">
        <?php else: ?>
            <img id="preview" class="img-preview" style="display:none">
        <?php endif; ?>

        <input type="hidden" name="<?= $c ?>" value="<?= htmlspecialchars($row[$c] ?? '') ?>">

    <?php else: ?>
        <input type="text" name="<?= $c ?>" value="<?= htmlspecialchars($row[$c] ?? '') ?>">
    <?php endif; ?>

<?php endforeach; ?>

<div class="actions">
    <button type="submit">Guardar</button>
    <a href="itens.php">Cancelar</a>
</div>

</form>

</div>
</div>

<script>
const fileInput = document.querySelector('input[type=file]');
const preview = document.getElementById('preview');

if (fileInput && preview) {
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