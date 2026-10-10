<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

require_once '../config/db.php';

$id = $_GET['id'] ?? null;

if ($id) {
    // Siapkan query Hapus
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
    $stmt->execute(['id' => $id]);
}

// Setelah dihapus, lempar kembali ke halaman utama
header("Location: index.php?status=deleted");
exit;