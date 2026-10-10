<?php
// payment_methods/create.php
require_once '../config/db.php';
require_once '../includes/header.php';
?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="main-content">
    <header class="top-header">
        <h1>Add Payment Method</h1>
        <div class="user-info"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
    </header>

    <div class="content-area">
        <div class="page-header">
            <div>
                <h2 class="page-heading">Add New Payment Method</h2>
                <p class="page-subtext" style="margin-bottom:0;">Register a new method used to pay employees.</p>
            </div>
            <a href="index.php" class="btn-cancel">&larr; Back to List</a>
        </div>

        <div class="form-card">
            <form action="save.php" method="POST">
                <div class="form-group">
                    <label>Method Name *</label>
                    <input type="text" name="method_name" class="form-control" placeholder="e.g. Bank Transfer, Cash, Cheque" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Optional short description..."></textarea>
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
                    <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Save Payment Method</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>