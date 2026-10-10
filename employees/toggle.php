<?php
// employees/toggle.php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /payroll_system/auth/login.php");
    exit;
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];
    // Flip status
    $stmt = $pdo->prepare("UPDATE employees SET status = IF(status='Active','Inactive','Active') WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: index.php?success=toggled");
exit;
?>