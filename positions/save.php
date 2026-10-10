<?php
// positions/save.php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /payroll_system/auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = $_POST['id'] ?? '';
    $title       = trim($_POST['title']);
    $department  = $_POST['department_id'];
    $description = trim($_POST['description']);
    $status      = $_POST['status'] ?? 'Active';

    if (!empty($id)) {
        $stmt = $pdo->prepare("UPDATE positions SET title=?, department_id=?, description=?, status=? WHERE id=?");
        $stmt->execute([$title, $department, $description, $status, $id]);
        header("Location: index.php?success=updated");
    } else {
        $stmt = $pdo->prepare("INSERT INTO positions (title, department_id, description, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $department, $description, $status]);
        header("Location: index.php?success=added");
    }
    exit;
}

header("Location: index.php");
exit;
?>