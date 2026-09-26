<?php
require_once '../config/db.php';

$errors = [];
$name = $category = "";
$price = $stock = 0;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $price = filter_input(INPUT_POST, "price", FILTER_VALIDATE_FLOAT);
    $stock = filter_input(INPUT_POST, "stock", FILTER_VALIDATE_INT);
    $image_name = null;

    if (mb_strlen($name) < 3) $errors["name"] = "Nama produk minimal 3 karakter.";
    if ($price === false || $price <= 0) $errors["price"] = "Harga harus lebih dari 0.";
    if ($stock === false || $stock < 0) $errors["stock"] = "Stok tidak boleh bernilai negatif.";
    if (empty($category)) $category = "Uncategorized";

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
        $file_name = $_FILES['image']['name'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (in_array($file_ext, $allowed_ext)) {
            $image_name = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "", $file_name);
            move_uploaded_file($file_tmp, 'uploads/' . $image_name);
        } else {
            $errors["image"] = "Format file tidak didukung. Gunakan JPG, PNG, atau WEBP.";
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (name, category, price, stock, image) VALUES (:name, :category, :price, :stock, :image)");
            $stmt->execute([
                "name" => $name,
                "category" => $category,
                "price" => $price,
                "stock" => $stock,
                "image" => $image_name
            ]);
            header("Location: index.php?status=created");
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) $errors["name"] = "Peralatan dengan nama ini sudah terdaftar di sistem.";
            else $errors["db"] = "System Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Input Equipment - Multivision Gear</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="navbar"><h2>MULTIVISION GEAR</h2></div>
    
    <div class="container">
        <form method="POST" action="create.php" class="main-form" enctype="multipart/form-data">
            <h3 style="margin:0 0 30px 0; font-size:22px; color:#111;">Input Peralatan Baru</h3>
            
            <?php if(isset($errors["db"])) echo '<div class="error">'.$errors["db"].'</div>'; ?>

            <div class="form-group">
                <label>Nama Alat / Tipe *</label>
                <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required minlength="3">
                <?php if(isset($errors["name"])) echo '<span class="error">'.$errors["name"].'</span>'; ?>
            </div>

            <div class="form-group">
    <label>Kategori</label>
    <select name="category" required>
        <option value="" disabled <?= empty($category) ? 'selected' : '' ?>>Pilih Kategori...</option>
        <option value="Kamera & Lensa" <?= $category === 'Kamera & Lensa' ? 'selected' : '' ?>>Kamera & Lensa</option>
        <option value="Aksesori & Stabilizer" <?= $category === 'Aksesori & Stabilizer' ? 'selected' : '' ?>>Aksesori & Stabilizer</option>
        <option value="Audio & Recording" <?= $category === 'Audio & Recording' ? 'selected' : '' ?>>Audio & Recording</option>
        <option value="Studio & Alat Desain" <?= $category === 'Studio & Alat Desain' ? 'selected' : '' ?>>Studio & Alat Desain</option>
    </select>
</div>

            <div class="form-group" style="display: flex; gap: 20px;">
                <div style="flex:2;">
                    <label>Harga Satuan (Rp) *</label>
                    <input type="number" name="price" value="<?= $price ?>" required min="1">
                    <?php if(isset($errors["price"])) echo '<span class="error">'.$errors["price"].'</span>'; ?>
                </div>
                <div style="flex:1;">
                    <label>Stok *</label>
                    <input type="number" name="stock" value="<?= $stock ?>" required min="0">
                    <?php if(isset($errors["stock"])) echo '<span class="error">'.$errors["stock"].'</span>'; ?>
                </div>
            </div>

            <div class="form-group">
                <label>Foto Produk (Opsional)</label>
                <input type="file" name="image" accept="image/png, image/jpeg, image/webp" style="padding: 10px; background: white;">
                <?php if(isset($errors["image"])) echo '<span class="error">'.$errors["image"].'</span>'; ?>
            </div>

            <div style="display: flex; gap: 15px; margin-top: 40px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Simpan ke Database</button>
                <a href="index.php" class="btn btn-warning" style="text-align: center; padding: 12px 24px;">Batal</a>
            </div>
        </form>
    </div>
</body>
</html>