<?php
// allowances/save.php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /payroll_system/auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id             = $_POST['id'] ?? '';
    $employee_id    = $_POST['employee_id'];
    $allowance_name = trim($_POST['allowance_name']);
    $amount         = $_POST['amount'];
    $effective_date = $_POST['effective_date'];
    $status         = $_POST['status'] ?? 'Active';

    if (!empty($id)) {
        $stmt = $pdo->prepare("UPDATE allowances SET employee_id=?, allowance_name=?, amount=?, effective_date=?, status=? WHERE id=?");
        $stmt->execute([$employee_id, $allowance_name, $amount, $effective_date, $status, $id]);
        header("Location: index.php?success=updated");
    } else {
        $stmt = $pdo->prepare("INSERT INTO allowances (employee_id, allowance_name, amount, effective_date, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$employee_id, $allowance_name, $amount, $effective_date, $status]);
        header("Location: index.php?success=added");
    }
    exit;
}

header("Location: index.php");
exit;
?>