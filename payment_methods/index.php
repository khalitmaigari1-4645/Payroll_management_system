<?php
// payment_methods/index.php
require_once '../config/db.php';
require_once '../includes/header.php';

$where = [];
$params = [];

if (!empty($_GET['search'])) {
    $where[] = "(method_name LIKE ? OR description LIKE ?)";
    $s = '%' . $_GET['search'] . '%';
    array_push($params, $s, $s);
}
if (!empty($_GET['status'])) {
    $where[] = "status = ?";
    $params[] = $_GET['status'];
}

$sql = "SELECT * FROM payment_methods";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$methods = $stmt->fetchAll();
?>

<?php require_once '../includes/sidebar.php'; ?>

<div class="main-content">
    <header class="top-header">
        <h1>Payment Methods</h1>
        <div class="user-info"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
    </header>

    <div class="content-area">

        <?php if (isset($_GET['success'])): ?>
            <div class="alert-success">
                <?php
                if ($_GET['success'] === 'added')   echo "Payment method added successfully.";
                if ($_GET['success'] === 'updated') echo "Payment method updated successfully.";
                if ($_GET['success'] === 'deleted') echo "Payment method deleted successfully.";
                if ($_GET['success'] === 'toggled') echo "Payment method status updated.";
                ?>
            </div>
        <?php endif; ?>

        <div class="page-header">
            <div>
                <h2 class="page-heading">Payment Methods</h2>
                <p class="page-subtext" style="margin-bottom:0;">Manage methods used to pay employees.</p>
            </div>
            <a href="create.php" class="btn-primary"><i class="fas fa-plus"></i> Add Payment Method</a>
        </div>

        <div class="filter-card">
            <form method="GET" action="index.php">
                <div class="filter-grid" style="grid-template-columns: 2fr 1fr auto;">
                    <div class="filter-item">
                        <label>Search Method</label>
                        <input type="text" name="search" class="form-control" placeholder="Method name or description..."
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
                    <div class="filter-buttons">
                        <button type="submit" class="btn-primary"><i class="fas fa-search"></i> Search</button>
                        <a href="index.php" class="btn-cancel">Clear</a>
                    </div>
                </div>
            </form>
        </div>

        <p class="table-info">Showing <?php echo count($methods); ?> payment method(s).</p>

        <div class="table-container">
            <?php if (count($methods) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Method Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($methods as $i => $m): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td><?php echo htmlspecialchars($m['method_name']); ?></td>
                                <td><?php echo htmlspecialchars($m['description'] ?? ''); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo strtolower($m['status']); ?>">
                                        <?php echo $m['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-stack">
                                        <a href="edit.php?id=<?php echo $m['id']; ?>" class="btn-yellow">Edit</a>
                                        <?php if ($m['status'] === 'Active'): ?>
                                            <a href="toggle.php?id=<?php echo $m['id']; ?>" class="btn-red"
                                               onclick="return confirm('Deactivate this payment method?');">Deactivate</a>
                                        <?php else: ?>
                                            <a href="toggle.php?id=<?php echo $m['id']; ?>" class="btn-green"
                                               onclick="return confirm('Activate this payment method?');">Activate</a>
                                        <?php endif; ?>
                                        <a href="delete.php?id=<?php echo $m['id']; ?>" class="btn-red"
                                           onclick="return confirm('Delete this payment method permanently?');">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-credit-card" style="font-size: 3rem; margin-bottom: 15px; color: #cbd5e1;"></i>
                    <h3>No Payment Methods Found</h3>
                    <p>Try adjusting your filters or add a new payment method.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>