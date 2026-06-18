<?php
require_once __DIR__ . '/connexio.php';

function findTable(mysqli $conn, array $names): ?string
{
    foreach ($names as $name) {
        $escaped = $conn->real_escape_string($name);
        $result = $conn->query("SHOW TABLES LIKE '$escaped'");
        if ($result && $result->num_rows > 0) {
            return $name;
        }
    }

    return null;
}

function getField(array $row, array $keys, string $default = ''): string
{
    foreach ($keys as $key) {
        if (array_key_exists($key, $row) && trim((string) $row[$key]) !== '') {
            return (string) $row[$key];
        }
    }

    return $default;
}

function normalizeImage(string $value): string
{
    $value = trim($value);
    // Do not return the site logo as a fallback for missing item images.
    // Return an empty string so the template can render a proper placeholder.
    if ($value === '') {
        return '';
    }

    if (preg_match('#^(https?://|/|[A-Za-z]:\\\\)#', $value)) {
        return $value;
    }
    if (strpos($value, 'uploads/') === 0) {
        return '/' . $value;
    }
    if (strpos($value, 'images/') === 0) {
        return '/' . $value;
    }
    return 'images/' . basename($value);
}

$tableNames = ['items', 'productes', 'produtos', 'products', 'materials', 'materials'];
$selectedTable = findTable($conn, $tableNames);
$items = [];
$errorMessage = '';

if ($selectedTable !== null) {
    $query = "SELECT * FROM `$selectedTable`";
    $result = $conn->query($query);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        $result->free();
    } else {
        $errorMessage = 'Error llegint la taula ' . htmlspecialchars($selectedTable, ENT_QUOTES, 'UTF-8') . ': ' . htmlspecialchars($conn->error, ENT_QUOTES, 'UTF-8');
    }
} else {
    $errorMessage = 'No s\'ha trobat cap taula d\'ítems a la base de dades. Comprova el nom de la taula i les migracions.';
}
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Llista d’ítems</title>
    <link rel="icon" href="images/inspedr.jpg" type="image/jpeg">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f4f6f9;
            color: #1e3a5f;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header{
            background:#1e3a5f;
            color:white;
            padding:14px 40px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            box-shadow:0 4px 18px rgba(0,0,0,0.15);
        }

        .logo{
            display:flex;
            align-items:center;
            gap:12px;
            font-weight:700;
            font-size:1.1rem;
        }

        .logo img{
            height:40px;
            border-radius:6px;
        }

        nav a{
            color:white;
            text-decoration:none;
            margin-left:20px;
            font-weight:600;
            opacity:0.9;
            transition:0.2s;
        }

        nav a:hover{
            opacity:1;
        }

        .page-heading {
            padding: 42px 40px 20px;
            max-width: 1100px;
            margin: 0 auto;
        }

        .page-heading h1 {
            font-size: 2.6rem;
            margin-bottom: 16px;
        }

        .page-heading p {
            font-size: 1rem;
            color: #546e88;
            max-width: 760px;
            line-height: 1.7;
        }

        .items-grid {
            /* Force 5 cards per row on wide screens */
            max-width: 1400px;
            margin: 0 auto 60px;
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 24px;
            padding: 0 40px 40px;
        }

        @media (max-width: 1200px) {
            .items-grid { grid-template-columns: repeat(4, 1fr); }
        }

        @media (max-width: 992px) {
            .items-grid { grid-template-columns: repeat(3, 1fr); }
        }

        @media (max-width: 640px) {
            .items-grid { grid-template-columns: repeat(2, 1fr); padding: 0 16px 24px; }
        }

        .item-card {
            background: white;
            border-radius: 18px;
            box-shadow: 0 18px 45px rgba(30, 58, 95, 0.08);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            min-height: 360px;
        }

        .item-card img {
            width: 100%;
            min-height: 220px;
            object-fit: cover;
            display: block;
        }

        .media {
            overflow: hidden;
            border-top-left-radius: 18px;
            border-top-right-radius: 18px;
        }

        .item-placeholder {
            width: 100%;
            min-height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(180deg, #f0f3f7, #e8edf3);
            color: #6b7280;
            font-weight: 600;
        }

        .item-card-content {
            padding: 24px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .main {
            flex: 1 0 auto;
        }

        .item-card h2 {
            font-size: 1.4rem;
            margin-bottom: 12px;
        }

        .item-card p {
            color: #55697d;
            font-size: 0.96rem;
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .item-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .item-meta span {
            font-weight: bold;
            color: #1e3a5f;
        }

        .item-meta small {
            color: #798999;
        }

            .btn{
                padding:10px 14px;
                border-radius:8px;
                text-decoration:none;
                display:inline-block;
                font-weight:600;
            }

            .btn-primary{ background:#1e3a5f; color:#fff }
            .btn-add{ background:#2563eb; color:#fff }
            .btn-edit{ background:#2563eb; color:#fff; border:none }

            .card-actions{ display:flex; gap:8px; margin-top:12px }

            .header-actions{ width:100%; margin-top:12px; padding:0 40px; display:flex; justify-content:flex-end; align-items:center }
            @media (max-width:960px){ .header-actions{ padding:0 20px } }
            @media (max-width:640px){ .header-actions{ padding:0 16px } header { padding: 14px 16px } }

        .empty-state,
        .error-state {
            max-width: 760px;
            margin: 30px auto;
            background: white;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 12px 28px rgba(30, 58, 95, 0.08);
            color: #1e3a5f;
            line-height: 1.8;
        }

        footer{
            text-align:center;
            padding:25px;
            background:#1e3a5f;
            color:white;
        }

        @media (max-width: 768px) {
            header {
                padding: 18px 20px;
            }

            .page-heading {
                padding: 32px 20px 20px;
            }

            .items-grid {
                padding: 0 20px 40px;
            }

            nav a {
                margin-left: 14px;
            }
        }
    </style>
</head>
<body>
<header>
  <a href="index.html" class="logo">
        <img src="images/inspedr.jpg" alt="Logo de l'escola">
    </a>

</header>

    <div class="header-actions">
        <a href="add_item.php" class="btn btn-add">Afegir ítem</a>
    </div>

<main class="main">
<section class="page-heading">
    <h1>Llista d’ítems del magatzem</h1>
    <p>Consulta tots els articles registrats, amb imatge, nom i l’estat del stock. Aquesta pàgina llegeix la base de dades i mostra cada element com una targeta fàcil de llegir.</p>
</section>

<?php if ($errorMessage !== ''): ?>
    <div class="error-state">
        <strong>Avís:</strong> <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<?php if (empty($items) && $errorMessage === ''): ?>
    <div class="empty-state">
        <strong>No s’han trobat ítems.</strong>
        <p>Si ja tens dades a la base de dades, comprova que existeix la taula correcta i que conté registres.</p>
    </div>
<?php endif; ?>

<div class="items-grid">
    <?php foreach ($items as $item): ?>
        <?php
            $title = getField($item, ['name', 'nom', 'nome', 'product', 'producte', 'material', 'title'], 'Ítem sense nom');
            $stock = getField($item, ['stock', 'estoc', 'quantitat', 'quantitat_actual', 'quantity', 'available'], 'N/D');
            $rawImage = getField($item, ['image', 'img', 'imatge', 'imagem', 'foto', 'foto_url', 'url'], '');
            $image = normalizeImage($rawImage);
            $noImage = $rawImage === '';
            $extra = getField($item, ['category', 'categoria', 'categoria_id', 'tipus', 'type'], '');
            // detect primary key value
            $id = '';
            foreach (['id','ID','item_id','product_id','codigo','cod'] as $k) {
                if (array_key_exists($k, $item)) { $id = (string) $item[$k]; break; }
            }
        ?>
          <article class="item-card">
              <div class="media">
                  <?php if ($image !== ''): ?>
                      <img src="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>">
                  <?php else: ?>
                      <div class="item-placeholder">Imatge no disponible</div>
                  <?php endif; ?>
              </div>
              <div class="item-card-content">
                  <div>
                      <h2><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h2>
                  </div>
                <div class="item-meta">
                    <span>Stock: <?= htmlspecialchars($stock, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php if ($extra !== ''): ?>
                        <small><?= htmlspecialchars($extra, ENT_QUOTES, 'UTF-8') ?></small>
                    <?php endif; ?>
                </div>
                <div class="card-actions">
                    <a href="edit_item.php?id=<?= urlencode($id) ?>&table=<?= urlencode($selectedTable) ?>" class="btn btn-edit">Editar</a>
                    <form method="post" action="delete_item.php" onsubmit="return confirm('Confirmes eliminar aquest ítem?');" style="display:inline">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="table" value="<?= htmlspecialchars($selectedTable, ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="btn" style="background:#ef4444;color:white;border:none;border-radius:8px;padding:8px 12px;">Eliminar</button>
                    </form>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
</div>
</main>

<footer>
    © 2026 - Sistema de Gestió de Magatzem Escolar
</footer>
</body>
</html>
