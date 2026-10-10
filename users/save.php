<?php
// users/save.php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /payroll_system/auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id        = $_POST['id'] ?? '';
    $username  = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $email     = trim($_POST['email']);
    $password  = $_POST['password'] ?? '';
    $role      = $_POST['role'];
    $status    = $_POST['status'];

    if (!empty($id)) {
        // Update
        if (!empty($password)) {
            $stmt = $pdo->prepare("UPDATE users SET username=?, full_name=?, email=?, password=?, role=?, status=? WHERE id=?");
            $stmt->execute([$username, $full_name, $email, $password, $role, $status, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username=?, full_name=?, email=?, role=?, status=? WHERE id=?");
            $stmt->execute([$username, $full_name, $email, $role, $status, $id]);
        }
        header("Location: index.php?success=updated");
    } else {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO users (username, full_name, email, password, role, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$username, $full_name, $email, $password, $role, $status]);
        header("Location: index.php?success=added");
    }
    exit;
}

header("Location: index.php");
exit;
?>