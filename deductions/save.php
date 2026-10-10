<?php

// deductions/save.php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /payroll_system/auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id             = $_POST['id'] ?? '';
    $name           = trim($_POST['deduction_name']);
    $employee_id    = !empty($_POST['employee_id']) ? (int)$_POST['employee_id'] : null;
    $amount         = (float) $_POST['amount'];
    $is_percentage  = (int) $_POST['is_percentage'];
    $effective_date = $_POST['effective_date'];
    $status         = $_POST['status'] ?? 'Active';

    if (!empty($id)) {
        $stmt = $pdo->prepare("UPDATE deductions SET employee_id=?, deduction_name=?, amount=?, is_percentage=?, effective_date=?, status=? WHERE id=?");
        $stmt->execute([$employee_id, $name, $amount, $is_percentage, $effective_date, $status, (int)$id]);
        header("Location: index.php?success=updated");
    } else {
        $stmt = $pdo->prepare("INSERT INTO deductions (employee_id, deduction_name, amount, is_percentage, effective_date, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$employee_id, $name, $amount, $is_percentage, $effective_date, $status]);
        header("Location: index.php?success=added");
    }
    exit;
}

header("Location: index.php");
exit;
?>