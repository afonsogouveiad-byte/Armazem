<?php
require_once __DIR__ . '/connexio.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: itens.php');
    exit;
}

$id = $_POST['id'] ?? '';
$table = $_POST['table'] ?? '';

// basic validation
$allowed = ['items','productes','produtos','products','materials'];
if (!in_array($table, $allowed, true) || $id === '') {
    $_SESSION['message'] = 'Paràmetres invàlids.';
    header('Location: itens.php');
    exit;
}

// find primary key column
$pk = null;
$res = $conn->query("SHOW COLUMNS FROM `" . $conn->real_escape_string($table) . "`");
while ($row = $res->fetch_assoc()) {
    if (strpos($row['Extra'] ?? '', 'auto_increment') !== false) { $pk = $row['Field']; break; }
}
if ($pk === null) {
    // fallback common names
    foreach (['id','ID','item_id','product_id'] as $c) {
        $r = $conn->query("SHOW COLUMNS FROM `" . $conn->real_escape_string($table) . "` LIKE '" . $conn->real_escape_string($c) . "'");
        if ($r && $r->num_rows) { $pk = $c; break; }
    }
}

if ($pk === null) {
    $_SESSION['message'] = 'No s\'ha pogut determinar la clau primària.';
    header('Location: itens.php');
    exit;
}

$stmt = $conn->prepare("DELETE FROM `" . $conn->real_escape_string($table) . "` WHERE `" . $conn->real_escape_string($pk) . "` = ? LIMIT 1");
if (!$stmt) {
    $_SESSION['message'] = 'Error al preparar la consulta.';
    header('Location: itens.php');
    exit;
}
$stmt->bind_param('s', $id);
if ($stmt->execute()) {
    $_SESSION['message'] = 'Ítem eliminat.';
} else {
    $_SESSION['message'] = 'No s\'ha pogut eliminar l\'ítem: ' . $conn->error;
}
$stmt->close();
header('Location: itens.php');
exit;

?>
