<?php
// payroll/index.php
require_once '../config/db.php';
require_once '../includes/header.php';

// Handle creating a period
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_period'])) {
    $name  = trim($_POST['period_name']);
    $start = $_POST['start_date'];
    $end   = $_POST['end_date'];
    $pay   = $_POST['pay_date'];

    if ($name && $start && $end && $pay) {
        $stmt = $pdo->prepare("INSERT INTO payroll_periods (period_name, start_date, end_date, pay_date, status) VALUES (?, ?, ?, ?, 'Draft')");
        $stmt->execute([$name, $start, $end, $pay]);
        header("Location: index.php?success=created");
        exit;
    }
}

$periods = $pdo->query("SELECT * FROM payroll_periods ORDER BY id DESC")->fetchAll();
?>

<?php require_once '../includes/sidebar.php'; ?>

<div class="main-content">
    <header class="top-header">
        <h1>Payroll Periods</h1>
        <div class="user-info"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
    </header>

    <div class="content-area">

        <?php if (isset($_GET['success'])): ?>
            <div class="alert-success">
                <?php
                if ($_GET['success'] === 'created')   echo "Payroll period created successfully.";
                if ($_GET['success'] === 'processed') echo "Payroll period processed successfully.";
                if ($_GET['success'] === 'deleted')   echo "Payroll period deleted successfully.";
                ?>
            </div>
        <?php endif; ?>

        <div class="page-header">
            <div>
                <h2 class="page-heading">Payroll Periods</h2>
                <p class="page-subtext" style="margin-bottom:0;">Create and manage payroll processing periods.</p>
            </div>
        </div>

        <!-- Create Payroll Period Card -->
        <div class="form-card" style="margin-bottom: 25px;">
            <h3 style="margin-bottom: 20px; color: #1e293b;">Create Payroll Period</h3>
            <form method="POST" action="index.php">
                <input type="hidden" name="create_period" value="1">

                <div class="filter-grid" style="grid-template-columns: 2fr 1fr 1fr 1fr;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Period Name *</label>
                        <input type="text" name="period_name" class="form-control" placeholder="e.g. August 2026 Payroll" required>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Start Date *</label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label>End Date *</label>
                        <input type="date" name="end_date" class="form-control" required>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Pay Date *</label>
                        <input type="date" name="pay_date" class="form-control" required>
                    </div>
                </div>

                <div class="form-actions" style="margin-top: 20px;">
                    <button type="submit" class="btn-primary"><i class="fas fa-plus"></i> Create Payroll Period</button>
                </div>
            </form>
        </div>

        <!-- Payroll Periods List -->
        <h3 style="margin-bottom: 15px; color: #1e293b;">All Payroll Periods</h3>

        <div class="table-container">
            <?php if (count($periods) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Period</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Pay Date</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($periods as $i => $p): ?>
                            <tr>
                                <td><?php echo $p['id']; ?></td>
                                <td><?php echo htmlspecialchars($p['period_name']); ?></td>
                                <td><?php echo htmlspecialchars($p['start_date']); ?></td>
                                <td><?php echo htmlspecialchars($p['end_date']); ?></td>
                                <td><?php echo htmlspecialchars($p['pay_date']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo strtolower($p['status']); ?>">
                                        <?php echo $p['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($p['created_at']); ?></td>
                                <td>
                                    <div class="action-stack">
                                        <?php if ($p['status'] === 'Draft' || $p['status'] === 'Pending'): ?>
                                            <a href="process.php?id=<?php echo $p['id']; ?>" class="btn-green"
                                               onclick="return confirm('Process this payroll period?');">Process</a>
                                        <?php else: ?>
                                            <a href="view.php?id=<?php echo $p['id']; ?>" class="btn-yellow">View</a>
                                        <?php endif; ?>
                                        <a href="delete.php?id=<?php echo $p['id']; ?>" class="btn-red"
                                           onclick="return confirm('Delete this payroll period?');">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-alt" style="font-size: 3rem; margin-bottom: 15px; color: #cbd5e1;"></i>
                    <h3>No Payroll Periods Found</h3>
                    <p>Create your first payroll period above.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>