<?php
// dashboard/index.php
require_once '../config/db.php';
require_once '../includes/header.php';

// Real stats from database
$totalEmployees   = $pdo->query("SELECT COUNT(*) FROM employees")->fetchColumn();
$totalDepartments = $pdo->query("SELECT COUNT(*) FROM departments")->fetchColumn();
$totalPositions   = $pdo->query("SELECT COUNT(*) FROM positions WHERE status='Active'")->fetchColumn();

$totalAllowances = 0; // placeholder until allowances module is built

$payrollThisMonth = $pdo->query("SELECT SUM(basic_salary) FROM employees")->fetchColumn();
if ($payrollThisMonth === null) $payrollThisMonth = 0;
?>

<?php require_once '../includes/sidebar.php'; ?>

<div class="main-content">
    <header class="top-header">
        <h1>Dashboard</h1>
        <div class="user-info"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
    </header>

    <div class="content-area">
        <h2 class="page-heading">Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h2>
        <p class="page-subtext">Here's an overview of your payroll management system.</p>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-title">Total Employees</div>
                <div class="stat-card-value"><?php echo $totalEmployees; ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-card-title">Departments</div>
                <div class="stat-card-value"><?php echo $totalDepartments; ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-card-title">Active Allowances</div>
                <div class="stat-card-value"><?php echo $totalPositions; ?></div>
                <div class="stat-card-sub">Total: ₦<?php echo number_format($totalAllowances, 2); ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-card-title">Payroll This Month</div>
                <div class="stat-card-value">₦<?php echo number_format($payrollThisMonth, 2); ?></div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>