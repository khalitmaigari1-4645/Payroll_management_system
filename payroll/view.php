<?php
// payroll/view.php
require_once '../config/db.php';
require_once '../includes/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM payroll_periods WHERE id = ?");
$stmt->execute([(int)$_GET['id']]);
$period = $stmt->fetch();

if (!$period) {
    header("Location: index.php");
    exit;
}

$employees = $pdo->query("
    SELECT e.*, d.name AS department_name 
    FROM employees e 
    LEFT JOIN departments d ON e.department_id = d.id 
    WHERE e.status='Active' 
    ORDER BY e.full_name
")->fetchAll();
?>

<?php require_once '../includes/sidebar.php'; ?>

<div class="main-content">
    <header class="top-header">
        <h1>Payroll Details</h1>
        <div class="user-info"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
    </header>

    <div class="content-area">
        <div class="page-header">
            <div>
                <h2 class="page-heading"><?php echo htmlspecialchars($period['period_name']); ?></h2>
                <p class="page-subtext" style="margin-bottom:0;">
                    Start: <?php echo $period['start_date']; ?> |
                    End: <?php echo $period['end_date']; ?> |
                    Pay Date: <?php echo $period['pay_date']; ?> |
                    Status: <span class="badge badge-<?php echo strtolower($period['status']); ?>"><?php echo $period['status']; ?></span>
                </p>
            </div>
            <a href="index.php" class="btn-cancel">&larr; Back to List</a>
        </div>

        <h3 style="margin-bottom: 15px; color: #1e293b;">Employee Payroll Summary</h3>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Basic Salary</th>
                        <th>Allowances</th>
                        <th>Gross Salary</th>
                        <th>Deductions</th>
                        <th>Net Salary</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_gross = 0;
                    $total_deductions_sum = 0;
                    $total_net = 0;

                    foreach ($employees as $i => $e):
                        $basic = (float) $e['basic_salary'];

                        // Sum allowances (direct + global)
                        $a_stmt = $pdo->prepare("
                            SELECT * FROM allowances 
                            WHERE status='Active' 
                              AND (employee_id = ? OR employee_id IS NULL)
                        ");
                        $a_stmt->execute([$e['id']]);
                        $empAllowances = $a_stmt->fetchAll();

                        $allowances = 0;
                        foreach ($empAllowances as $a) {
                            $allowances += $a['is_percentage']
                                ? ($basic * (float)$a['amount'] / 100)
                                : (float)$a['amount'];
                        }

                        $gross = $basic + $allowances;

                        // Sum deductions (direct + global, master list only)
                        $d_stmt = $pdo->prepare("
                            SELECT * FROM deductions 
                            WHERE status='Active' 
                              AND payslip_id IS NULL
                              AND (employee_id = ? OR employee_id IS NULL)
                        ");
                        $d_stmt->execute([$e['id']]);
                        $empDeductions = $d_stmt->fetchAll();

                        $deductions = 0;
                        foreach ($empDeductions as $d) {
                            $deductions += $d['is_percentage']
                                ? ($basic * (float)$d['amount'] / 100)
                                : (float)$d['amount'];
                        }

                        $net = $gross - $deductions;

                        $total_gross += $gross;
                        $total_deductions_sum += $deductions;
                        $total_net += $net;
                    ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><?php echo htmlspecialchars($e['employee_id_code']); ?></td>
                            <td><?php echo htmlspecialchars($e['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($e['department_name'] ?? 'N/A'); ?></td>
                            <td>₦<?php echo number_format($basic, 2); ?></td>
                            <td>₦<?php echo number_format($allowances, 2); ?></td>
                            <td>₦<?php echo number_format($gross, 2); ?></td>
                            <td>₦<?php echo number_format($deductions, 2); ?></td>
                            <td>₦<?php echo number_format($net, 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr style="background:#f8fafc; font-weight:bold;">
                        <td colspan="6" style="text-align:right;">TOTAL:</td>
                        <td>₦<?php echo number_format($total_gross, 2); ?></td>
                        <td>₦<?php echo number_format($total_deductions_sum, 2); ?></td>
                        <td>₦<?php echo number_format($total_net, 2); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>