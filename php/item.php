<?php
require_once __DIR__ . '/connexio.php';

$id = $_GET['id'] ?? '';

if ($id === '') {
    die("Missing id");
}

/* tabela fixa (não vem do URL) */
$table = "items";

/* fetch item */
$stmt = $conn->prepare("SELECT * FROM `$table` WHERE id = ? LIMIT 1");
$stmt->bind_param("s", $id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$item) {
    die("Item not found");
}

/* helper */
function get($row, $keys, $default = '') {
    foreach ($keys as $k) {
        if (!empty($row[$k])) return $row[$k];
    }
    return $default;
}

$title = get($item, ['name','nom','title'], 'Sem nome');
$desc  = get($item, ['description','desc','detalhes','info'], 'Sem descrição');

$img1  = get($item, ['image','img','foto','url'], '');
$img2  = get($item, ['image2','img2','foto2'], '');
?>

<!DOCTYPE html>
<html lang="ca">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($title) ?></title>

<style>
body{
    font-family:Arial;
    background:#f4f6f9;
    margin:0;
    padding:30px;
    color:#1e3a5f;
}

.container{
    max-width:900px;
    margin:auto;
    background:white;
    padding:20px;
    border-radius:12px;
    box-shadow:0 10px 30px rgba(0,0,0,0.1);
}

.images{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:15px;
    margin-bottom:20px;
}

img{
    width:100%;
    border-radius:10px;
    object-fit:cover;
    min-height:250px;
    background:#eee;
}

.btn{
    display:inline-block;
    padding:10px 14px;
    background:#1e3a5f;
    color:white;
    text-decoration:none;
    border-radius:8px;
    margin-top:15px;
}
</style>
</head>

<body>

<div class="container">

<h1><?= htmlspecialchars($title) ?></h1>

<div class="images">
    <?php if ($img1): ?>
        <img src="<?= htmlspecialchars($img1) ?>">
    <?php else: ?>
        <div style="background:#eee;display:flex;align-items:center;justify-content:center;">Sem imagem</div>
    <?php endif; ?>

    <?php if ($img2): ?>
        <img src="<?= htmlspecialchars($img2) ?>">
    <?php else: ?>
        <div style="background:#eee;display:flex;align-items:center;justify-content:center;">Sem imagem</div>
    <?php endif; ?>
</div>

<p><?= htmlspecialchars($desc) ?></p>

<a class="btn" href="edit_item.php?id=<?= urlencode($id) ?>">
    Editar item
</a>

</div>

</body>
</html>