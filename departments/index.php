<?php
// departments/index.php
require_once '../config/db.php';
require_once '../includes/header.php';

$where = [];
$params = [];

if (!empty($_GET['search'])) {
    $where[] = "name LIKE ?";
    $params[] = '%' . $_GET['search'] . '%';
}
if (!empty($_GET['status'])) {
    $where[] = "status = ?";
    $params[] = $_GET['status'];
}

$sql = "SELECT * FROM departments";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$departments = $stmt->fetchAll();
?>

<?php require_once '../includes/sidebar.php'; ?>

<div class="main-content">
    <header class="top-header">
        <h1>Departments</h1>
        <div class="user-info"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
    </header>

    <div class="content-area">

        <?php if (isset($_GET['success'])): ?>
            <div class="alert-success">
                <?php
                if ($_GET['success'] === 'added')   echo "Department added successfully.";
                if ($_GET['success'] === 'updated') echo "Department updated successfully.";
                if ($_GET['success'] === 'deleted') echo "Department deleted successfully.";
                if ($_GET['success'] === 'toggled') echo "Department status updated.";
                ?>
            </div>
        <?php endif; ?>

        <div class="page-header">
            <div>
                <h2 class="page-heading">Departments</h2>
                <p class="page-subtext" style="margin-bottom:0;">Manage all company departments.</p>
            </div>
            <a href="create.php" class="btn-primary"><i class="fas fa-plus"></i> Add Department</a>
        </div>

        <div class="filter-card">
            <form method="GET" action="index.php">
                <div class="filter-grid" style="grid-template-columns: 2fr 1fr auto;">
                    <div class="filter-item">
                        <label>Search Department</label>
                        <input type="text" name="search" class="form-control" placeholder="Department name..."
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

        <p class="table-info">Showing <?php echo count($departments); ?> department(s).</p>

        <div class="table-container">
            <?php if (count($departments) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Department Name</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($departments as $i => $d): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td><?php echo htmlspecialchars($d['name']); ?></td>
                                <td><span class="badge badge-<?php echo strtolower($d['status']); ?>"><?php echo $d['status']; ?></span></td>
                                <td>
                                    <a href="edit.php?id=<?php echo $d['id']; ?>" class="btn-edit">Edit</a>
                                    <a href="toggle.php?id=<?php echo $d['id']; ?>"
                                       class="btn-<?php echo $d['status']==='Active'?'delete':'edit'; ?>"
                                       onclick="return confirm('Change status for this department?');">
                                        <?php echo $d['status']==='Active'?'Deactivate':'Activate'; ?>
                                    </a>
                                    <a href="delete.php?id=<?php echo $d['id']; ?>" class="btn-delete"
                                       onclick="return confirm('Delete this department permanently?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-building" style="font-size: 3rem; margin-bottom: 15px; color: #cbd5e1;"></i>
                    <h3>No Departments Found</h3>
                    <p>Try adjusting your filters or add a new department.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>