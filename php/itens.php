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
    if ($value === '') {
        return 'images/inspedr.jpg';
    }

    if (preg_match('#^(https?://|/|[A-Za-z]:\\\\)#', $value)) {
        return $value;
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
    <title>Llista d’ítems - Magatzem Escolar</title>
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
        }

        header {
            background-color: #1e3a5f;
            color: white;
            padding: 18px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
            color: white;
        }

        .logo img {
            height: 50px;
            border-radius: 8px;
            object-fit: contain;
        }

        .logo span {
            font-size: 1.25rem;
            font-weight: bold;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin-left: 24px;
            font-weight: bold;
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
            max-width: 1100px;
            margin: 0 auto 60px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            padding: 0 40px 40px;
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
            min-height: 190px;
            object-fit: cover;
            display: block;
        }

        .item-card-content {
            padding: 24px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
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

        footer {
            text-align: center;
            padding: 22px 20px 24px;
            background: #1e3a5f;
            color: white;
            font-size: 0.95rem;
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
        <span>Magatzem Escolar</span>
    </a>

    <nav>
        <a href="index.html">Inici</a>
        <a href="itens.php">Llistar ítems</a>
    </nav>
</header>

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
            $description = getField($item, ['description', 'descr', 'descripcio', 'detalls', 'details', 'note', 'nota'], 'Cap descripció disponible.');
            $stock = getField($item, ['stock', 'estoc', 'quantitat', 'quantitat_actual', 'quantity', 'available'], 'N/D');
            $image = normalizeImage(getField($item, ['image', 'img', 'imatge', 'imagem', 'foto', 'foto_url', 'url'], ''));
            $extra = getField($item, ['category', 'categoria', 'categoria_id', 'tipus', 'type'], '');
        ?>
        <article class="item-card">
            <img src="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>">
            <div class="item-card-content">
                <div>
                    <h2><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h2>
                    <p><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div class="item-meta">
                    <span>Stock: <?= htmlspecialchars($stock, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php if ($extra !== ''): ?>
                        <small><?= htmlspecialchars($extra, ENT_QUOTES, 'UTF-8') ?></small>
                    <?php endif; ?>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
</div>

<footer>
    © 2026 - Sistema de Gestió de Magatzem Escolar
</footer>
</body>
</html>
