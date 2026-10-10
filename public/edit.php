<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

require_once '../config/db.php';

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: index.php"); exit; }

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
$stmt->execute(['id' => $id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) { header("Location: index.php"); exit; }

$cat_stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sku = trim($_POST['sku']);
    $name = trim($_POST['name']);
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $minimum_stock = $_POST['minimum_stock'];
    
    // Pertahankan gambar lama sebagai default
    $image_name = $product['image']; 
    
    // Cek jika ada gambar baru yang diupload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($file_ext, $allowed_ext)) {
            $image_name = uniqid() . '.' . $file_ext;
            move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $image_name);
        } else {
            $error = "Format gambar harus JPG, PNG, atau WEBP.";
        }
    }

    if (empty($sku) || empty($name) || empty($category_id)) {
        $error = "SKU, Nama, dan Kategori wajib diisi!";
    } elseif (empty($error)) {
        try {
            $update_stmt = $pdo->prepare("UPDATE products SET category_id = :category_id, sku = :sku, name = :name, price = :price, stock = :stock, minimum_stock = :minimum_stock, image = :image WHERE id = :id");
            $update_stmt->execute([
                'category_id' => $category_id, 'sku' => $sku, 'name' => $name,
                'price' => $price, 'stock' => $stock, 'minimum_stock' => $minimum_stock,
                'image' => $image_name, 'id' => $id
            ]);
            header("Location: index.php?status=updated");
            exit;
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Alat - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; padding: 20px; }
        .container { max-width: 500px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input, select { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;}
        .btn { padding: 10px 15px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block;}
        .btn-secondary { background: #6c757d; }
        .error { color: red; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Data Gear</h2>
        <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Foto Produk (Biarkan kosong jika tidak ingin ganti)</label>
                <?php if ($product['image']): ?>
                    <img src="uploads/<?= htmlspecialchars($product['image']) ?>" alt="Foto" style="height: 100px; border-radius: 5px; margin-bottom: 10px; display: block;">
                <?php endif; ?>
                <input type="file" name="image" accept="image/png, image/jpeg, image/webp">
            </div>
            <div class="form-group">
                <label>SKU (Kode Barang)</label>
                <input type="text" name="sku" required value="<?= htmlspecialchars($product['sku']) ?>">
            </div>
            <div class="form-group">
                <label>Nama Alat</label>
                <input type="text" name="name" required value="<?= htmlspecialchars($product['name']) ?>">
            </div>
            <div class="form-group">
                <label>Kategori</label>
                <select name="category_id" required>
                    <option value="">-- Pilih --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Harga Sewa (Rp)</label>
                <input type="number" name="price" required min="0" value="<?= htmlspecialchars($product['price']) ?>">
            </div>
            <div class="form-group">
                <label>Stok Saat Ini</label>
                <input type="number" name="stock" required min="0" value="<?= htmlspecialchars($product['stock']) ?>">
            </div>
            <div class="form-group">
                <label>Batas Minimum Stok</label>
                <input type="number" name="minimum_stock" required min="0" value="<?= htmlspecialchars($product['minimum_stock']) ?>">
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn">Update Data</button>
                <a href="index.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</body>
</html>