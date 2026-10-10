<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

require_once '../config/db.php';

// --- LOGIKA TAMBAH DATA SUPPLIER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_supplier'])) {
    $name = trim($_POST['name']);
    $contact = trim($_POST['contact']);
    $address = trim($_POST['address']);
    
    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO suppliers (name, contact_info, address) VALUES (?, ?, ?)");
        $stmt->execute([$name, $contact, $address]);
        header("Location: suppliers.php?status=success");
        exit;
    }
}

// --- AMBIL DATA SUPPLIER UNTUK DITAMPILKAN ---
$stmt = $pdo->query("SELECT * FROM suppliers ORDER BY id DESC");
$suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Supplier - Multivision Gear</title>
    <!-- Kita pakai style langsung agar simpel dan senada dengan desain aslimu -->
    <style>
        body { font-family: sans-serif; background-color: #fafafa; margin: 0; }
        .top-bar { display: flex; align-items: center; padding: 10px 20px; background-color: #fff; border-bottom: 1px solid #eee; }
        .container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); margin-bottom: 30px; border: 1px solid #eaeaea;}
        
        h2, h3 { color: #111; margin-top: 0; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-size: 14px; color: #555; margin-bottom: 5px; font-weight: bold;}
        input, textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; box-sizing: border-box; font-family: inherit;}
        
        .btn { background: #111; color: white; padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; font-weight: bold; transition: 0.2s;}
        .btn:hover { background: #333; }
        .btn-back { display: inline-block; margin-right: auto; color: #555; text-decoration: none; font-size: 14px; font-weight: bold; border: 1px solid #ddd; padding: 6px 15px; border-radius: 20px;}
        .btn-back:hover { background: #f4f4f4; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; font-size: 15px;}
        th { background-color: #f8f9fa; color: #555; text-transform: uppercase; font-size: 13px; letter-spacing: 0.5px;}
        tr:hover { background-color: #fcfcfc; }
    </style>
</head>
<body>
    <div class="top-bar">
        <a href="index.php" class="btn-back">&larr; Kembali ke Dashboard</a>
        <span style="font-size: 14px; color: #555; margin-left: auto;">Panel <strong>Supplier</strong></span>
    </div>

    <div class="container">
        <h2>Manajemen Mitra / Supplier</h2>

        <!-- KOTAK FORM TAMBAH SUPPLIER -->
        <div class="card">
            <h3>+ Tambah Supplier Baru</h3>
            <form method="POST">
                <div style="display: flex; gap: 20px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Nama Perusahaan / Toko</label>
                        <input type="text" name="name" required placeholder="Contoh: PT. Sentra Kamera">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Kontak (HP / Email)</label>
                        <input type="text" name="contact" placeholder="Contoh: 0812-xxxx-xxxx">
                    </div>
                </div>
                <div class="form-group">
                    <label>Alamat Lengkap</label>
                    <textarea name="address" rows="2" placeholder="Alamat gudang atau kantor supplier..."></textarea>
                </div>
                <button type="submit" name="add_supplier" class="btn">Simpan Supplier</button>
            </form>
        </div>

        <!-- KOTAK TABEL DAFTAR SUPPLIER -->
        <div class="card">
            <h3>Daftar Supplier Aktif</h3>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Nama Supplier</th>
                    <th>Info Kontak</th>
                    <th>Alamat</th>
                </tr>
                <?php if (count($suppliers) > 0): ?>
                    <?php foreach ($suppliers as $s): ?>
                    <tr>
                        <td>#<?= $s['id'] ?></td>
                        <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
                        <td><?= htmlspecialchars($s['contact_info']) ?></td>
                        <td><?= htmlspecialchars($s['address']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center; color:#888; padding: 20px;">Belum ada data supplier yang ditambahkan.</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</body>
</html>