<?php
session_start();
require_once '../config/db.php';
$_SESSION["csrf"] ??= bin2hex(random_bytes(32));

$q = trim($_GET["q"] ?? "");
$filter_cat = trim($_GET["cat"] ?? ""); 

if ($q) {
    
    $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE :q OR category LIKE :q ORDER BY id DESC");
    $stmt->execute(["q" => "%$q%"]);
} elseif ($filter_cat) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category = :cat ORDER BY id DESC");
    $stmt->execute(["cat" => $filter_cat]);
} else {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
}
$products = $stmt->fetchAll();

$categories = [
    "Kamera & Lensa",
    "Aksesori & Stabilizer",
    "Audio & Recording",
    "Studio & Alat Desain"
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Multivision Gear - Product Manager</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="navbar">
    <img src="assets/logo.png" class="navbar-logo" alt="Logo Multivision Gear">
    <h2>MULTIVISION GEAR</h2>
</div>

    <div class="container">
        <?php if(($_GET['status'] ?? '') === 'created') echo '<div class="success">Produk berhasil ditambahkan ke katalog.</div>'; ?>
        <?php if(($_GET['status'] ?? '') === 'deleted') echo '<div class="deleted-alert">Produk berhasil dihapus dari katalog.</div>'; ?>

        <div class="header-actions">
            <a href="create.php" class="btn btn-primary">Tambah Peralatan Baru</a>
            
            <form method="GET" class="search-form" action="index.php">
                <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Cari nama alat atau brand...">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>

        <!-- FITUR BARU: Filter Kategori -->
        <div class="category-filter">
            <!-- Tombol 'Semua' -->
            <a href="index.php" class="filter-btn <?= empty($filter_cat) && empty($q) ? 'active' : '' ?>">
                Semua Peralatan
            </a>
            
            <!-- Looping Tombol Kategori -->
            <?php foreach($categories as $cat): ?>
                <a href="index.php?cat=<?= urlencode($cat) ?>" class="filter-btn <?= $filter_cat === $cat ? 'active' : '' ?>">
                    <?= htmlspecialchars($cat) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="products">
            <?php if(empty($products)): ?>
                <p style="color: #888; text-align: center; grid-column: 1 / -1; padding: 60px 0; font-size: 15px;">Katalog kosong atau barang tidak ditemukan.</p>
            <?php else: ?>
                <?php foreach ($products as $p): ?>
                    <div class="card">
                        <?php if(!empty($p['image']) && file_exists("uploads/" . $p['image'])): ?>
                            <img src="uploads/<?= $p['image'] ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="card-img">
                        <?php else: ?>
                            <div class="card-img-placeholder">NO IMAGE AVAILABLE</div>
                        <?php endif; ?>

                        <div class="card-body">
                            <div class="card-category"><?= htmlspecialchars($p["category"], ENT_QUOTES, "UTF-8") ?></div>
                            <h3 class="card-title"><?= htmlspecialchars($p["name"], ENT_QUOTES, "UTF-8") ?></h3>
                            <h4 class="card-price">Rp <?= number_format($p["price"], 0, ",", ".") ?></h4>
                            <div class="card-stock">Stok Tersedia: <?= $p["stock"] ?> unit</div>
                            
                            <div class="actions">
                                <a href="edit.php?id=<?= $p["id"] ?>" class="btn btn-warning">Edit</a>
                                <form method="POST" action="delete.php">
                                    <input type="hidden" name="id" value="<?= $p["id"] ?>">
                                    <input type="hidden" name="csrf" value="<?= $_SESSION["csrf"] ?>">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Konfirmasi penghapusan peralatan ini dari sistem?')">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>