<?php
// deductions/create.php
require_once '../config/db.php';
require_once '../includes/header.php';
$employees = $pdo->query("SELECT id, full_name, employee_id_code FROM employees ORDER BY full_name")->fetchAll();
?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="main-content">
    <header class="top-header">
        <h1>Add Deduction</h1>
        <div class="user-info"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
    </header>

    <div class="content-area">
        <div class="page-header">
            <div>
                <h2 class="page-heading">Add New Deduction</h2>
                <p class="page-subtext" style="margin-bottom:0;">Add a tax, pension, or custom deduction.</p>
            </div>
            <a href="index.php" class="btn-cancel">&larr; Back to List</a>
        </div>

        <div class="form-card">
            <form action="save.php" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Deduction Name *</label>
                        <input type="text" name="deduction_name" class="form-control" placeholder="e.g. Tax, Pension" required>
                    </div>
                    <div class="form-group">
                        <label>Applies To *</label>
                        <select name="employee_id" class="form-control">
                            <option value="">All Employees</option>
                            <?php foreach ($employees as $e): ?>
                                <option value="<?php echo $e['id']; ?>">
                                    <?php echo htmlspecialchars($e['full_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Amount / Rate *</label>
                        <input type="number" step="0.01" name="amount" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Type *</label>
                        <select name="is_percentage" class="form-control" required>
                            <option value="0">Fixed (₦)</option>
                            <option value="1">Percentage (%)</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Effective Date *</label>
                        <input type="date" name="effective_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Status *</label>
                        <select name="status" class="form-control" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="index.php" class="btn-cancel">Cancel</a>
                    <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Save Deduction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>