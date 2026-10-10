<?php
// positions/index.php
require_once '../config/db.php';
require_once '../includes/header.php';

$where = [];
$params = [];

if (!empty($_GET['search'])) {
    $where[] = "(p.title LIKE ? OR p.description LIKE ?)";
    $s = '%' . $_GET['search'] . '%';
    array_push($params, $s, $s);
}
if (!empty($_GET['status'])) {
    $where[] = "p.status = ?";
    $params[] = $_GET['status'];
}
if (!empty($_GET['department'])) {
    $where[] = "p.department_id = ?";
    $params[] = $_GET['department'];
}

$sql = "SELECT p.*, d.name AS department_name,
        (SELECT COUNT(*) FROM employees e WHERE e.position_id = p.id) AS employee_count
        FROM positions p
        LEFT JOIN departments d ON p.department_id = d.id";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY p.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$positions = $stmt->fetchAll();

$departments = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll();
?>

<?php require_once '../includes/sidebar.php'; ?>

<div class="main-content">
    <header class="top-header">
        <h1>Positions</h1>
        <div class="user-info"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
    </header>

    <div class="content-area">

        <?php if (isset($_GET['success'])): ?>
            <div class="alert-success">
                <?php
                if ($_GET['success'] === 'added')   echo "Position added successfully.";
                if ($_GET['success'] === 'updated') echo "Position updated successfully.";
                if ($_GET['success'] === 'deleted') echo "Position deleted successfully.";
                if ($_GET['success'] === 'toggled') echo "Position deactivated successfully.";
                ?>
            </div>
        <?php endif; ?>

        <div class="page-header">
            <div>
                <h2 class="page-heading">Positions</h2>
                <p class="page-subtext" style="margin-bottom:0;">Manage employee positions and their departments.</p>
            </div>
            <a href="create.php" class="btn-primary"><i class="fas fa-plus"></i> Add Position</a>
        </div>

        <div class="filter-card">
            <form method="GET" action="index.php">
                <div class="filter-grid" style="grid-template-columns: 2fr 1fr 1fr auto;">
                    <div class="filter-item">
                        <label>Search Position</label>
                        <input type="text" name="search" class="form-control" placeholder="Position or description..."
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
                        <label>Department</label>
                        <select name="department" class="form-control">
                            <option value="">All Departments</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?php echo $d['id']; ?>" <?php if(($_GET['department']??'')==$d['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($d['name']); ?>
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

        <p class="table-info">Showing <?php echo count($positions); ?> position(s).</p>

        <div class="table-container">
            <?php if (count($positions) > 0): ?>
                <table class="data-table positions-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th>Description</th>
                            <th>Employees</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($positions as $i => $p): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td><?php echo htmlspecialchars($p['title']); ?></td>
                                <td><?php echo htmlspecialchars($p['department_name'] ?? 'N/A'); ?></td>
                                <td class="wrap-cell"><?php echo htmlspecialchars($p['description']); ?></td>
                                <td><?php echo $p['employee_count']; ?></td>
                                <td>
                                    <span class="badge badge-<?php echo strtolower($p['status']); ?>">
                                        <?php echo $p['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-stack">
                                        <a href="edit.php?id=<?php echo $p['id']; ?>" class="btn-yellow">Edit</a>
                                        <?php if ($p['status'] === 'Active'): ?>
                                            <a href="toggle.php?id=<?php echo $p['id']; ?>" class="btn-red"
                                               onclick="return confirm('Deactivate this position?');">Deactivate</a>
                                        <?php else: ?>
                                            <a href="toggle.php?id=<?php echo $p['id']; ?>" class="btn-green"
                                               onclick="return confirm('Activate this position?');">Activate</a>
                                        <?php endif; ?>
                                        <a href="delete.php?id=<?php echo $p['id']; ?>" class="btn-red"
                                           onclick="return confirm('Delete this position permanently?');">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-briefcase" style="font-size: 3rem; margin-bottom: 15px; color: #cbd5e1;"></i>
                    <h3>No Positions Found</h3>
                    <p>Try adjusting your filters or add a new position.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>