<?php
// positions/create.php
require_once '../config/db.php';
require_once '../includes/header.php';
$departments = $pdo->query("SELECT * FROM departments WHERE status='Active' ORDER BY name")->fetchAll();
?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="main-content">
    <header class="top-header">
        <h1>Add Position</h1>
        <div class="user-info"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
    </header>

    <div class="content-area">
        <div class="page-header">
            <div>
                <h2 class="page-heading">Add New Position</h2>
                <p class="page-subtext" style="margin-bottom:0;">Register a new position in a department.</p>
            </div>
            <a href="index.php" class="btn-cancel">&larr; Back to List</a>
        </div>

        <div class="form-card">
            <form action="save.php" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Position Title *</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Department *</label>
                        <select name="department_id" class="form-control" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label>Status *</label>
                    <select name="status" class="form-control" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>

                <div class="form-actions">
                    <a href="index.php" class="btn-cancel">Cancel</a>
                    <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Save Position</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>