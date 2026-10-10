<?php
// payment_methods/save.php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /payroll_system/auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = $_POST['id'] ?? '';
    $method_name = trim($_POST['method_name']);
    $description = trim($_POST['description']);
    $status      = $_POST['status'] ?? 'Active';

    if (!empty($id)) {
        $stmt = $pdo->prepare("UPDATE payment_methods SET method_name=?, description=?, status=? WHERE id=?");
        $stmt->execute([$method_name, $description, $status, $id]);
        header("Location: index.php?success=updated");
    } else {
        $stmt = $pdo->prepare("INSERT INTO payment_methods (method_name, description, status) VALUES (?, ?, ?)");
        $stmt->execute([$method_name, $description, $status]);
        header("Location: index.php?success=added");
    }
    exit;
}

header("Location: index.php");
exit;
?>