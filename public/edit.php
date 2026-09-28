<?php
require_once '../config/db.php';

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
$stmt->execute(["id" => $id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: index.php");
    exit;
}

$errors = [];
$name = $product["name"];
$category = $product["category"];
$price = $product["price"];
$stock = $product["stock"];
$currentImage = $product["image"] ?? null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $price = filter_input(INPUT_POST, "price", FILTER_VALIDATE_FLOAT);
    $stock = filter_input(INPUT_POST, "stock", FILTER_VALIDATE_INT);
    $post_id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

    if (mb_strlen($name) < 3) {
        $errors["name"] = "Nama produk minimal 3 karakter.";
    }
    if ($price === false || $price <= 0) {
        $errors["price"] = "Harga harus lebih dari 0.";
    }
    if ($stock === false || $stock < 0) {
        $errors["stock"] = "Stok tidak boleh negatif.";
    }
    if (empty($category)) {
        $category = "Umum";
    }

    if (empty($errors) && $post_id === $id) {
        $imageName = $currentImage;

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['image']['tmp_name'];
            $newImageName = time() . '_' . basename($_FILES['image']['name']);
            $destination = __DIR__ . '/uploads/' . $newImageName;

            if (move_uploaded_file($tmpName, $destination)) {
                if (!empty($currentImage) && file_exists(__DIR__ . '/uploads/' . $currentImage)) {
                    unlink(__DIR__ . '/uploads/' . $currentImage);
                }
                $imageName = $newImageName; 
            } else {
                $errors["db"] = "Gagal mengunggah foto baru.";
            }
        }

        if (empty($errors)) {
            try {
                
                $updateStmt = $pdo->prepare("UPDATE products SET name = :name, category = :category, price = :price, stock = :stock, image = :image WHERE id = :id");
                $updateStmt->execute([
                    "name" => $name,
                    "category" => $category,
                    "price" => $price,
                    "stock" => $stock,
                    "image" => $imageName, 
                    "id" => $id
                ]);

                header("Location: index.php");
                exit;
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $errors["name"] = "Nama produk sudah terdaftar, gunakan nama lain.";
                } else {
                    $errors["db"] = "Gagal mengubah data: " . $e->getMessage();
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Produk</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <h2>Edit Produk</h2>

        <?php if(isset($errors["db"])): ?>
            <div class="error" style="margin-bottom:10px; font-size:14px;"><?= $errors["db"] ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" action="edit.php?id=<?= $id ?>">
            <input type="hidden" name="id" value="<?= $id ?>">

            <div class="form-group">
                <label for="name">Nama Produk *</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($name, ENT_QUOTES) ?>" required minlength="3">
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

            <div class="form-group">
                <label for="price">Harga (Rp) *</label>
                <input type="number" id="price" name="price" value="<?= $price ?>" required min="1">
                <?php if(isset($errors["price"])) echo '<span class="error">'.$errors["price"].'</span>'; ?>
            </div>

            <div class="form-group">
                <label for="stock">Stok *</label>
                <input type="number" id="stock" name="stock" value="<?= $stock ?>" required min="0">
                <?php if(isset($errors["stock"])) echo '<span class="error">'.$errors["stock"].'</span>'; ?>
            </div>

            <div class="form-group">
                <label for="image">Ganti Foto Produk (Opsional)</label>
                <br>
                <input type="file" name="image" id="image" accept="image/*" style="margin-top: 5px;">
                <br>
                <small style="color: #666;"><i>*Biarkan kosong jika tidak ingin mengganti foto</i></small>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="index.php" class="btn btn-warning" style="margin-left: 10px;">Batal</a>
        </form>
    </div>
</body>
</html>