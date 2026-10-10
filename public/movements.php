<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

require_once '../config/db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_movement'])) {
    $product_id = $_POST['product_id'];
    $type = $_POST['type'];
    $quantity = (int)$_POST['quantity'];
    $description = trim($_POST['description']);

    if (!empty($product_id) && $quantity > 0) {
        try {
            // Gunakan Transaction agar aman: jika salah satu gagal, semua dibatalkan
            $pdo->beginTransaction(); 

            // 1. Catat ke tabel riwayat (stock_movements)
            $stmt = $pdo->prepare("INSERT INTO stock_movements (product_id, type, quantity, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$product_id, $type, $quantity, $description]);

            // 2. Update otomatis angka stok di tabel products utama
            if ($type == 'in') {
                $upd_stmt = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
            } else {
                $upd_stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            }
            $upd_stmt->execute([$quantity, $product_id]);

            $pdo->commit(); // Simpan permanen
            header("Location: movements.php?status=success");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack(); // Batalkan jika error
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Ambil Daftar Produk untuk dropdown form
$prod_stmt = $pdo->query("SELECT id, name, stock FROM products ORDER BY name ASC");
$products = $prod_stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil Riwayat Pergerakan (JOIN dengan tabel products)
$hist_stmt = $pdo->query("
    SELECT m.*, p.name as product_name 
    FROM stock_movements m 
    JOIN products p ON m.product_id = p.id 
    ORDER BY m.movement_date DESC
");
$movements = $hist_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pergerakan Stok - Multivision Gear</title>
    <style>
        body { font-family: sans-serif; background-color: #fafafa; margin: 0; }
        .top-bar { display: flex; align-items: center; padding: 10px 20px; background-color: #fff; border-bottom: 1px solid #eee; }
        .container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); margin-bottom: 30px; border: 1px solid #eaeaea;}
        
        h2, h3 { color: #111; margin-top: 0; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-size: 14px; color: #555; margin-bottom: 5px; font-weight: bold;}
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; box-sizing: border-box; font-family: inherit;}
        
        .btn { background: #111; color: white; padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; font-weight: bold; transition: 0.2s;}
        .btn:hover { background: #333; }
        .btn-back { display: inline-block; margin-right: auto; color: #555; text-decoration: none; font-size: 14px; font-weight: bold; border: 1px solid #ddd; padding: 6px 15px; border-radius: 20px;}
        .btn-back:hover { background: #f4f4f4; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; font-size: 14px;}
        th { background-color: #f8f9fa; color: #555; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px;}
        tr:hover { background-color: #fcfcfc; }
        
        .badge-in { background: #d4edda; color: #155724; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold;}
        .badge-out { background: #f8d7da; color: #721c24; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold;}
        .error { color: #dc3545; font-weight: bold; margin-bottom: 15px;}
    </style>
</head>
<body>
    <div class="top-bar">
        <a href="index.php" class="btn-back">&larr; Kembali ke Dashboard</a>
        <span style="font-size: 14px; color: #555; margin-left: auto;">Panel <strong>Log Stok</strong></span>
    </div>

    <div class="container">
        <h2>Catatan Keluar/Masuk Barang</h2>
        <?php if($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <!-- FORM TAMBAH RIWAYAT STOK -->
        <div class="card">
            <h3>+ Catat Pergerakan Baru</h3>
            <form method="POST">
                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <div class="form-group" style="flex: 2; min-width: 200px;">
                        <label>Pilih Barang</label>
                        <select name="product_id" required>
                            <option value="">-- Pilih Gear --</option>
                            <?php foreach ($products as $p): ?>
                                <option value="<?= $p['id'] ?>">
                                    <?= htmlspecialchars($p['name']) ?> (Stok Saat Ini: <?= $p['stock'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group" style="flex: 1; min-width: 120px;">
                        <label>Jenis</label>
                        <select name="type" required>
                            <option value="in">Barang Masuk (Tambah)</option>
                            <option value="out">Barang Keluar (Kurang)</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="flex: 1; min-width: 100px;">
                        <label>Jumlah</label>
                        <input type="number" name="quantity" required min="1" value="1">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Keterangan (Opsional)</label>
                    <input type="text" name="description" placeholder="Contoh: Disewa oleh pelanggan A / Beli baru dari supplier">
                </div>
                
                <button type="submit" name="add_movement" class="btn">Catat Stok</button>
            </form>
        </div>

        <!-- TABEL RIWAYAT STOK -->
        <div class="card">
            <h3>Histori Pergerakan Terakhir</h3>
            <table>
                <tr>
                    <th>Waktu</th>
                    <th>Nama Alat</th>
                    <th>Jenis</th>
                    <th>Jumlah</th>
                    <th>Keterangan</th>
                </tr>
                <?php if (count($movements) > 0): ?>
                    <?php foreach ($movements as $m): ?>
                    <tr>
                        <td><?= date('d M Y, H:i', strtotime($m['movement_date'])) ?></td>
                        <td><strong><?= htmlspecialchars($m['product_name']) ?></strong></td>
                        <td>
                            <?php if ($m['type'] == 'in'): ?>
                                <span class="badge-in">MASUK</span>
                            <?php else: ?>
                                <span class="badge-out">KELUAR</span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= $m['quantity'] ?></strong> unit</td>
                        <td><?= htmlspecialchars($m['description']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center; color:#888; padding: 20px;">Belum ada riwayat stok.</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</body>
</html>