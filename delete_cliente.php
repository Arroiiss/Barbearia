<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'client'");
    $stmt->execute([$id]);
}
header("Location: admin_dashboard.php");
exit();