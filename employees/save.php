<?php
// employees/save.php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /payroll_system/auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id              = $_POST['id'] ?? '';
    $code            = trim($_POST['employee_id_code']);
    $full_name       = trim($_POST['full_name']);
    $gender          = $_POST['gender'];
    $email           = trim($_POST['email']);
    $phone           = trim($_POST['phone']);
    $department      = $_POST['department_id'];
    $position        = $_POST['position_id'];
    $employment_type = $_POST['employment_type'];
    $salary          = $_POST['basic_salary'];
    $status          = $_POST['status'] ?? 'Active';

    if (!empty($id)) {
        $stmt = $pdo->prepare("UPDATE employees 
            SET employee_id_code=?, full_name=?, gender=?, email=?, phone=?, department_id=?, position_id=?, employment_type=?, basic_salary=?, status=? 
            WHERE id=?");
        $stmt->execute([$code, $full_name, $gender, $email, $phone, $department, $position, $employment_type, $salary, $status, $id]);
        header("Location: index.php?success=updated");
    } else {
        $stmt = $pdo->prepare("INSERT INTO employees 
            (employee_id_code, full_name, gender, email, phone, department_id, position_id, employment_type, basic_salary, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$code, $full_name, $gender, $email, $phone, $department, $position, $employment_type, $salary, $status]);
        header("Location: index.php?success=added");
    }
    exit;
}

header("Location: index.php");
exit;
?>