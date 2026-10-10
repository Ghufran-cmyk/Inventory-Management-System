<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

require_once '../config/db.php';

// =====================================================================
// [AUTHORIZATION] GEMBOK 403: Blokir akses jika yang login bukan admin
// =====================================================================
if ($_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    die("<div style='font-family:sans-serif; text-align:center; margin-top:100px;'>
          <h1 style='color:#dc3545; font-size:48px; margin-bottom:10px;'>403 Forbidden</h1>
          <h3 style='color:#555;'>Maaf, hanya Admin yang memiliki hak akses ke halaman ini.</h3>
          <br><br><a href='index.php' style='padding:10px 20px; background:#111; color:#fff; text-decoration:none; border-radius:5px;'>&larr; Kembali ke Dashboard</a>
         </div>");
}
// =====================================================================

// [SECURITY] Buat token rahasia CSRF
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // [SECURITY] Cek validitas token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf']) {
        die("<h3 style='color:red; text-align:center; margin-top:50px;'>Akses Ditolak: Token Keamanan (CSRF) Tidak Valid!</h3>");
    }

    $sku = trim($_POST['sku']);
    $name = trim($_POST['name']);
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $minimum_stock = $_POST['minimum_stock'];
    
    // Proses Upload Gambar
    $image_name = null;
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
        $error = "Peringatan: SKU, Nama, dan Kategori tidak boleh kosong!";
    } elseif (empty($error)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (category_id, sku, name, price, stock, minimum_stock, image) 
                                   VALUES (:category_id, :sku, :name, :price, :stock, :minimum_stock, :image)");
            $stmt->execute([
                'category_id' => $category_id, 
                'sku' => $sku, 
                'name' => $name, 
                'price' => $price, 
                'stock' => $stock, 
                'minimum_stock' => $minimum_stock, 
                'image' => $image_name
            ]);
            
            // Redirect jika sukses
            echo "<script>window.location.href = 'index.php?status=created';</script>";
            exit;
            
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { 
                $error = "Gagal: Kode SKU '$sku' sudah digunakan oleh barang lain!";
            } else { 
                $error = "Error Database: " . $e->getMessage(); 
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Alat - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; padding: 20px; }
        .container { max-width: 500px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input, select { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;}
        .btn { padding: 10px 15px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; }
        .btn-secondary { background: #6c757d; }
        .error { color: #721c24; margin-bottom: 15px; background: #f8d7da; padding: 10px; border-radius: 4px; border: 1px solid #f5c6cb;}
    </style>
</head>
<body>
    <div class="container">
        <h2>Tambah Gear Baru</h2>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            
            <!-- [SECURITY] Sisipkan token rahasia -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">

            <div class="form-group">
                <label>Foto Produk (Opsional)</label>
                <input type="file" name="image" accept="image/png, image/jpeg, image/webp">
            </div>
            <div class="form-group">
                <label>SKU (Kode Barang)</label>
                <input type="text" name="sku" placeholder="Contoh: CAM-001">
            </div>
            <div class="form-group">
                <label>Nama Alat</label>
                <input type="text" name="name">
            </div>
            <div class="form-group">
                <label>Kategori</label>
                <select name="category_id">
                    <option value="">-- Pilih --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Harga Sewa (Rp)</label>
                <input type="number" name="price" min="0" value="0">
            </div>
            <div class="form-group">
                <label>Stok Awal</label>
                <input type="number" name="stock" min="0" value="1">
            </div>
            <div class="form-group">
                <label>Batas Minimum Stok</label>
                <input type="number" name="minimum_stock" min="0" value="1">
            </div>
            
            <button type="submit" class="btn">Simpan Data</button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</body>
</html>