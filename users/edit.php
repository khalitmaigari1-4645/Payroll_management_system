<?php
// users/edit.php
require_once '../config/db.php';
require_once '../includes/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([(int)$_GET['id']]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: index.php");
    exit;
}
?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="main-content">
    <header class="top-header">
        <h1>Edit User</h1>
        <div class="user-info"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
    </header>

    <div class="content-area">
        <div class="page-header">
            <div>
                <h2 class="page-heading">Edit User</h2>
                <p class="page-subtext" style="margin-bottom:0;">Update details for <strong><?php echo htmlspecialchars($user['username']); ?></strong>.</p>
            </div>
            <a href="index.php" class="btn-cancel">&larr; Back to List</a>
        </div>

        <div class="form-card">
            <form action="save.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label>Username *</label>
                        <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Password (leave blank to keep current)</label>
                        <input type="text" name="password" class="form-control" placeholder="Enter new password to change">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Role *</label>
                        <select name="role" class="form-control" required>
                            <option value="User"  <?php if($user['role']==='User')  echo 'selected'; ?>>User</option>
                            <option value="Admin" <?php if($user['role']==='Admin') echo 'selected'; ?>>Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status *</label>
                        <select name="status" class="form-control" required>
                            <option value="Active"   <?php if($user['status']==='Active')   echo 'selected'; ?>>Active</option>
                            <option value="Inactive" <?php if($user['status']==='Inactive') echo 'selected'; ?>>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="index.php" class="btn-cancel">Cancel</a>
                    <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>