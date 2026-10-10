<?php
// payment_methods/delete.php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /payroll_system/auth/login.php");
    exit;
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM payment_methods WHERE id = ?");
    $stmt->execute([(int)$_GET['id']]);
}

header("Location: index.php?success=deleted");
exit;
?>