<?php
session_start();
require_once '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
    $csrf = $_POST["csrf"] ?? "";

    if (!hash_equals($_SESSION["csrf"] ?? "", $csrf)) {
        http_response_code(403);
        exit("Token keamanan tidak valid.");
    }

    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
        $stmt->execute(["id" => $id]);
    }

    header("Location: index.php?status=deleted");
    exit;
} else {
    header("Location: index.php");
    exit;
}