<?php
// departments/edit.php
require_once '../config/db.php';
require_once '../includes/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM departments WHERE id = ?");
$stmt->execute([(int)$_GET['id']]);
$dept = $stmt->fetch();

if (!$dept) {
    header("Location: index.php");
    exit;
}
?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="main-content">
    <header class="top-header">
        <h1>Edit Department</h1>
        <div class="user-info"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
    </header>

    <div class="content-area">
        <div class="page-header">
            <div>
                <h2 class="page-heading">Edit Department</h2>
                <p class="page-subtext" style="margin-bottom:0;">Update details for <strong><?php echo htmlspecialchars($dept['name']); ?></strong>.</p>
            </div>
            <a href="index.php" class="btn-cancel">&larr; Back to List</a>
        </div>

        <div class="form-card">
            <form action="save.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $dept['id']; ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label>Department Name *</label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($dept['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Status *</label>
                        <select name="status" class="form-control" required>
                            <option value="Active"   <?php if($dept['status']==='Active')   echo 'selected'; ?>>Active</option>
                            <option value="Inactive" <?php if($dept['status']==='Inactive') echo 'selected'; ?>>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="index.php" class="btn-cancel">Cancel</a>
                    <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Update Department</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>