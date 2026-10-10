<?php
// payslips/index.php
require_once '../config/db.php';
require_once '../includes/header.php';

// ============================================================
// HANDLE GENERATE PAYSLIPS
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    $period_id = (int)$_POST['payroll_period_id'];

    $stmt = $pdo->prepare("SELECT * FROM payroll_periods WHERE id = ?");
    $stmt->execute([$period_id]);
    $period = $stmt->fetch();

    if ($period) {

        // ----------------------------------------------------
        // Clean up old payslip snapshot deductions first
        // ----------------------------------------------------
        $old = $pdo->prepare("SELECT id FROM payslips WHERE payroll_period_id = ?");
        $old->execute([$period_id]);
        $oldIds = $old->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($oldIds)) {
            $in = str_repeat('?,', count($oldIds) - 1) . '?';
            $pdo->prepare("DELETE FROM deductions WHERE payslip_id IN ($in)")->execute($oldIds);
        }

        // Now delete the old payslips themselves
        $pdo->prepare("DELETE FROM payslips WHERE payroll_period_id = ?")->execute([$period_id]);

        // ----------------------------------------------------
        // Fetch active employees
        // ----------------------------------------------------
        $employees = $pdo->query("SELECT * FROM employees WHERE status='Active'")->fetchAll();

        foreach ($employees as $e) {
            $basic = (float)$e['basic_salary'];

            // ----------------------------------------------------
            // 1. Sum active allowances for this employee (direct + global)
            // ----------------------------------------------------
            $stmt = $pdo->prepare("
                SELECT * FROM allowances
                WHERE status = 'Active'
                  AND (employee_id = ? OR employee_id IS NULL)
            ");
            $stmt->execute([$e['id']]);
            $empAllowances = $stmt->fetchAll();

            $allowances = 0;
            foreach ($empAllowances as $a) {
                $allowances += $a['is_percentage']
                    ? ($basic * (float)$a['amount'] / 100)
                    : (float)$a['amount'];
            }

            $gross = $basic + $allowances;

            // ----------------------------------------------------
            // 2. Sum active deductions for this employee (direct + global)
            //    Only rows from the master list (payslip_id IS NULL)
            // ----------------------------------------------------
            $stmt = $pdo->prepare("
                SELECT * FROM deductions
                WHERE status = 'Active'
                  AND payslip_id IS NULL
                  AND (employee_id = ? OR employee_id IS NULL)
            ");
            $stmt->execute([$e['id']]);
            $empDeductions = $stmt->fetchAll();

            $total_deductions = 0;
            $deductionLines = [];

            foreach ($empDeductions as $d) {
                $amt = $d['is_percentage']
                    ? ($basic * (float)$d['amount'] / 100)
                    : (float)$d['amount'];

                $total_deductions += $amt;
                $deductionLines[] = [
                    'name'   => $d['deduction_name'],
                    'amount' => $amt
                ];
            }

            $net = $gross - $total_deductions;

            // ----------------------------------------------------
            // 3. Insert payslip
            // ----------------------------------------------------
            $stmt = $pdo->prepare("
                INSERT INTO payslips
                    (employee_id, payroll_period_id, basic_salary, total_allowances, gross_salary, total_deductions, net_salary, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'Calculated')
            ");
            $stmt->execute([$e['id'], $period_id, $basic, $allowances, $gross, $total_deductions, $net]);
            $payslip_id = $pdo->lastInsertId();

            // ----------------------------------------------------
            // 4. Insert per-payslip deduction snapshot rows
            // ----------------------------------------------------
            foreach ($deductionLines as $line) {
                $pdo->prepare("
                    INSERT INTO deductions
                        (payslip_id, deduction_name, amount, is_percentage, effective_date, status)
                    VALUES (?, ?, ?, 0, CURDATE(), 'Active')
                ")->execute([$payslip_id, $line['name'], $line['amount']]);
            }
        }
        header("Location: index.php?success=generated");
        exit;
    }
}

// ============================================================
// FILTERS
// ============================================================
$where = [];
$params = [];

if (!empty($_GET['search'])) {
    $where[] = "(e.full_name LIKE ? OR e.employee_id_code LIKE ?)";
    $s = '%' . $_GET['search'] . '%';
    array_push($params, $s, $s);
}
if (!empty($_GET['period'])) {
    $where[] = "p.payroll_period_id = ?";
    $params[] = $_GET['period'];
}

$sql = "SELECT p.*, e.full_name, e.employee_id_code, pp.period_name, pp.pay_date 
        FROM payslips p
        LEFT JOIN employees e ON p.employee_id = e.id
        LEFT JOIN payroll_periods pp ON p.payroll_period_id = pp.id";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY p.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$payslips = $stmt->fetchAll();

$periods = $pdo->query("SELECT * FROM payroll_periods ORDER BY id DESC")->fetchAll();
?>

<?php require_once '../includes/sidebar.php'; ?>

<div class="main-content">
    <header class="top-header">
        <h1>Payslips</h1>
        <div class="user-info"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
    </header>

    <div class="content-area">

        <?php if (isset($_GET['success'])): ?>
            <div class="alert-success">
                <?php if ($_GET['success'] === 'generated') echo "Payslips generated successfully."; ?>
            </div>
        <?php endif; ?>

        <div class="page-header">
            <div>
                <h2 class="page-heading">Employee Payslips</h2>
                <p class="page-subtext" style="margin-bottom:0;">View and manage calculated employee payslips.</p>
            </div>
        </div>

        <!-- Generate Payslips Card -->
        <div class="form-card" style="margin-bottom:25px;">
            <h3 style="margin-bottom:15px; color:#1e293b;">Generate Payslips</h3>
            <form method="POST" action="index.php">
                <input type="hidden" name="generate" value="1">
                <div class="filter-grid" style="grid-template-columns: 2fr auto;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Select Payroll Period *</label>
                        <select name="payroll_period_id" class="form-control" required>
                            <option value="">Choose period...</option>
                            <?php foreach ($periods as $pp): ?>
                                <option value="<?php echo $pp['id']; ?>">
                                    <?php echo htmlspecialchars($pp['period_name']); ?> (<?php echo $pp['status']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-buttons" style="align-items:end;">
                        <button type="submit" class="btn-primary" onclick="return confirm('Generate payslips for all active employees in this period?');">
                            <i class="fas fa-cogs"></i> Generate
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Filters -->
        <div class="filter-card">
            <form method="GET" action="index.php">
                <div class="filter-grid" style="grid-template-columns: 2fr 1fr auto;">
                    <div class="filter-item">
                        <label>Search Employee</label>
                        <input type="text" name="search" class="form-control" placeholder="Name or ID..."
                               value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    </div>
                    <div class="filter-item">
                        <label>Payroll Period</label>
                        <select name="period" class="form-control">
                            <option value="">All Periods</option>
                            <?php foreach ($periods as $pp): ?>
                                <option value="<?php echo $pp['id']; ?>" <?php if(($_GET['period']??'')==$pp['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($pp['period_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-buttons">
                        <button type="submit" class="btn-primary"><i class="fas fa-search"></i> Search</button>
                        <a href="index.php" class="btn-cancel">Clear</a>
                    </div>
                </div>
            </form>
        </div>

        <p class="table-info">Showing <?php echo count($payslips); ?> payslip(s).</p>

        <div class="table-container">
            <?php if (count($payslips) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee ID</th>
                            <th>Employee Name</th>
                            <th>Payroll Period</th>
                            <th>Pay Date</th>
                            <th>Basic Salary</th>
                            <th>Allowances</th>
                            <th>Gross Salary</th>
                            <th>Deductions</th>
                            <th>Net Salary</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payslips as $i => $p): ?>
                            <tr>
                                <td><?php echo $i+1; ?></td>
                                <td><?php echo htmlspecialchars($p['employee_id_code']); ?></td>
                                <td><?php echo htmlspecialchars($p['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($p['period_name']); ?></td>
                                <td><?php echo htmlspecialchars($p['pay_date']); ?></td>
                                <td>₦<?php echo number_format($p['basic_salary'], 2); ?></td>
                                <td>₦<?php echo number_format($p['total_allowances'], 2); ?></td>
                                <td>₦<?php echo number_format($p['gross_salary'], 2); ?></td>
                                <td>₦<?php echo number_format($p['total_deductions'], 2); ?></td>
                                <td>₦<?php echo number_format($p['net_salary'], 2); ?></td>
                                <td><span class="badge badge-<?php echo strtolower($p['status']); ?>"><?php echo $p['status']; ?></span></td>
                                <td>
                                    <div class="action-stack">
                                        <a href="view.php?id=<?php echo $p['id']; ?>" class="btn-yellow">View Payslip</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-file-invoice-dollar" style="font-size: 3rem; margin-bottom: 15px; color: #cbd5e1;"></i>
                    <h3>No Payslips Found</h3>
                    <p>Generate payslips for a payroll period to get started.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>