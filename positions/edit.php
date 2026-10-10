<?php
// positions/edit.php
require_once '../config/db.php';
require_once '../includes/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM positions WHERE id = ?");
$stmt->execute([(int)$_GET['id']]);
$pos = $stmt->fetch();

if (!$pos) {
    header("Location: index.php");
    exit;
}

$departments = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll();
?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="main-content">
    <header class="top-header">
        <h1>Edit Position</h1>
        <div class="user-info"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
    </header>

    <div class="content-area">
        <div class="page-header">
            <div>
                <h2 class="page-heading">Edit Position</h2>
                <p class="page-subtext" style="margin-bottom:0;">Update details for <strong><?php echo htmlspecialchars($pos['title']); ?></strong>.</p>
            </div>
            <a href="index.php" class="btn-cancel">&larr; Back to List</a>
        </div>

        <div class="form-card">
            <form action="save.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $pos['id']; ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label>Position Title *</label>
                        <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($pos['title']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Department *</label>
                        <select name="department_id" class="form-control" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?php echo $d['id']; ?>" <?php if($pos['department_id']==$d['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($d['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($pos['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Status *</label>
                    <select name="status" class="form-control" required>
                        <option value="Active"   <?php if($pos['status']==='Active')   echo 'selected'; ?>>Active</option>
                        <option value="Inactive" <?php if($pos['status']==='Inactive') echo 'selected'; ?>>Inactive</option>
                    </select>
                </div>

                <div class="form-actions">
                    <a href="index.php" class="btn-cancel">Cancel</a>
                    <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Update Position</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>