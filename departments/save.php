<?php
// departments/save.php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /payroll_system/auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = $_POST['id'] ?? '';
    $name   = trim($_POST['name']);
    $status = $_POST['status'] ?? 'Active';

    if (!empty($id)) {
        $stmt = $pdo->prepare("UPDATE departments SET name=?, status=? WHERE id=?");
        $stmt->execute([$name, $status, $id]);
        header("Location: index.php?success=updated");
    } else {
        $stmt = $pdo->prepare("INSERT INTO departments (name, status) VALUES (?, ?)");
        $stmt->execute([$name, $status]);
        header("Location: index.php?success=added");
    }
    exit;
}

header("Location: index.php");
exit;
?>