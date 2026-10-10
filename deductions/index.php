<?php
// deductions/index.php
require_once '../config/db.php';
require_once '../includes/header.php';

$where = [];
$params = [];

if (!empty($_GET['search'])) {
    $where[] = "(d.deduction_name LIKE ? OR e.full_name LIKE ?)";
    $s = '%' . $_GET['search'] . '%';
    array_push($params, $s, $s);
}
if (!empty($_GET['status'])) {
    $where[] = "d.status = ?";
    $params[] = $_GET['status'];
}
if (!empty($_GET['employee'])) {
    $where[] = "d.employee_id = ?";
    $params[] = $_GET['employee'];
}

$sql = "SELECT d.*, e.full_name AS employee_name
        FROM deductions d
        LEFT JOIN employees e ON d.employee_id = e.id";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY d.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$deductions = $stmt->fetchAll();

$employees = $pdo->query("SELECT id, full_name, employee_id_code FROM employees ORDER BY full_name")->fetchAll();
?>

<?php require_once '../includes/sidebar.php'; ?>

<div class="main-content">
    <header class="top-header">
        <h1>Deductions</h1>
        <div class="user-info"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
    </header>

    <div class="content-area">

        <?php if (isset($_GET['success'])): ?>
            <div class="alert-success">
                <?php
                if ($_GET['success'] === 'added')   echo "Deduction added successfully.";
                if ($_GET['success'] === 'updated') echo "Deduction updated successfully.";
                if ($_GET['success'] === 'deleted') echo "Deduction deleted successfully.";
                if ($_GET['success'] === 'toggled') echo "Deduction status updated successfully.";
                ?>
            </div>
        <?php endif; ?>

        <div class="page-header">
            <div>
                <h2 class="page-heading">Deductions</h2>
                <p class="page-subtext" style="margin-bottom:0;">Manage tax, pension, and other deductions applied to payslips.</p>
            </div>
            <a href="create.php" class="btn-primary"><i class="fas fa-plus"></i> Add Deduction</a>
        </div>

        <div class="filter-card">
            <form method="GET" action="index.php">
                <div class="filter-grid" style="grid-template-columns: 2fr 1fr 1fr auto;">
                    <div class="filter-item">
                        <label>Search Deduction</label>
                        <input type="text" name="search" class="form-control" placeholder="Deduction name or employee..."
                               value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    </div>
                    <div class="filter-item">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="Active"   <?php if(($_GET['status']??'')==='Active')   echo 'selected'; ?>>Active</option>
                            <option value="Inactive" <?php if(($_GET['status']??'')==='Inactive') echo 'selected'; ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <label>Employee</label>
                        <select name="employee" class="form-control">
                            <option value="">All Employees</option>
                            <?php foreach ($employees as $e): ?>
                                <option value="<?php echo $e['id']; ?>" <?php if(($_GET['employee']??'')==$e['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($e['full_name']); ?>
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

        <p class="table-info">Showing <?php echo count($deductions); ?> deduction(s).</p>

        <div class="table-container">
            <?php if (count($deductions) > 0): ?>
                <table class="data-table users-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Deduction</th>
                            <th>Amount / Rate</th>
                            <th>Type</th>
                            <th>Effective Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deductions as $i => $d): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td>
                                    <?php if ($d['employee_id']): ?>
                                        <?php echo htmlspecialchars($d['employee_name']); ?>
                                    <?php else: ?>
                                        <strong>All Employees</strong>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($d['deduction_name']); ?></td>
                                <td>
                                    <?php echo $d['is_percentage'] ? $d['amount'].'%' : '₦'.number_format($d['amount'], 2); ?>
                                </td>
                                <td><?php echo $d['is_percentage'] ? 'Percentage' : 'Fixed'; ?></td>
                                <td><?php echo htmlspecialchars($d['effective_date']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo strtolower($d['status']); ?>">
                                        <?php echo $d['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-stack">
                                        <a href="edit.php?id=<?php echo $d['id']; ?>" class="btn-yellow">Edit</a>
                                        <?php if ($d['status'] === 'Active'): ?>
                                            <a href="toggle.php?id=<?php echo $d['id']; ?>" class="btn-red"
                                               onclick="return confirm('Deactivate this deduction?');">Deactivate</a>
                                        <?php else: ?>
                                            <a href="toggle.php?id=<?php echo $d['id']; ?>" class="btn-green"
                                               onclick="return confirm('Activate this deduction?');">Activate</a>
                                        <?php endif; ?>
                                        <a href="delete.php?id=<?php echo $d['id']; ?>" class="btn-red"
                                           onclick="return confirm('Delete this deduction permanently?');">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-minus-circle" style="font-size: 3rem; margin-bottom: 15px; color: #cbd5e1;"></i>
                    <h3>No Deductions Found</h3>
                    <p>Try adjusting your filters or add a new deduction.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>