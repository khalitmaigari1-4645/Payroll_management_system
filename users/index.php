<?php
// users/index.php
require_once '../config/db.php';
require_once '../includes/header.php';

$where = [];
$params = [];

if (!empty($_GET['search'])) {
    $where[] = "(username LIKE ? OR full_name LIKE ? OR email LIKE ?)";
    $s = '%' . $_GET['search'] . '%';
    array_push($params, $s, $s, $s);
}
if (!empty($_GET['status'])) {
    $where[] = "status = ?";
    $params[] = $_GET['status'];
}
if (!empty($_GET['role'])) {
    $where[] = "role = ?";
    $params[] = $_GET['role'];
}

$sql = "SELECT * FROM users";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<?php require_once '../includes/sidebar.php'; ?>

<div class="main-content">
    <header class="top-header">
        <h1>Users</h1>
        <div class="user-info"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
    </header>

    <div class="content-area">

        <?php if (isset($_GET['success'])): ?>
            <div class="alert-success">
                <?php
                if ($_GET['success'] === 'added')   echo "User added successfully.";
                if ($_GET['success'] === 'updated') echo "User updated successfully.";
                if ($_GET['success'] === 'deleted') echo "User deleted successfully.";
                if ($_GET['success'] === 'toggled') echo "User status updated successfully.";
                ?>
            </div>
        <?php endif; ?>

        <div class="page-header">
            <div>
                <h2 class="page-heading">System Users</h2>
                <p class="page-subtext" style="margin-bottom:0;">Manage users who can access the payroll system.</p>
            </div>
            <a href="create.php" class="btn-primary"><i class="fas fa-plus"></i> Add User</a>
        </div>

        <div class="filter-card">
            <form method="GET" action="index.php">
                <div class="filter-grid" style="grid-template-columns: 2fr 1fr 1fr auto;">
                    <div class="filter-item">
                        <label>Search User</label>
                        <input type="text" name="search" class="form-control" placeholder="Username, name, email..."
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
                        <label>Role</label>
                        <select name="role" class="form-control">
                            <option value="">All Roles</option>
                            <option value="Admin" <?php if(($_GET['role']??'')==='Admin') echo 'selected'; ?>>Admin</option>
                            <option value="User"  <?php if(($_GET['role']??'')==='User')  echo 'selected'; ?>>User</option>
                        </select>
                    </div>
                    <div class="filter-buttons">
                        <button type="submit" class="btn-primary"><i class="fas fa-search"></i> Search</button>
                        <a href="index.php" class="btn-cancel">Clear</a>
                    </div>
                </div>
            </form>
        </div>

        <p class="table-info">Showing <?php echo count($users); ?> user(s).</p>

        <div class="table-container">
            <?php if (count($users) > 0): ?>
                <table class="data-table users-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $i => $u): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td><?php echo htmlspecialchars($u['username']); ?></td>
                                <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td><?php echo htmlspecialchars($u['role']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo strtolower($u['status']); ?>">
                                        <?php echo $u['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-stack">
                                        <a href="edit.php?id=<?php echo $u['id']; ?>" class="btn-yellow">Edit</a>
                                        <?php if ($u['status'] === 'Active'): ?>
                                            <a href="toggle.php?id=<?php echo $u['id']; ?>" class="btn-red"
                                               onclick="return confirm('Deactivate this user?');">Deactivate</a>
                                        <?php else: ?>
                                            <a href="toggle.php?id=<?php echo $u['id']; ?>" class="btn-green"
                                               onclick="return confirm('Activate this user?');">Activate</a>
                                        <?php endif; ?>
                                        <a href="delete.php?id=<?php echo $u['id']; ?>" class="btn-red"
                                           onclick="return confirm('Delete this user permanently?');">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-user-shield" style="font-size: 3rem; margin-bottom: 15px; color: #cbd5e1;"></i>
                    <h3>No Users Found</h3>
                    <p>Try adjusting your filters or add a new user.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>