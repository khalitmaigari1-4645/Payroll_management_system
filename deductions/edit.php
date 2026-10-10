<?php
// deductions/edit.php
require_once '../config/db.php';
require_once '../includes/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM deductions WHERE id = ?");
$stmt->execute([(int)$_GET['id']]);
$ded = $stmt->fetch();

if (!$ded) {
    header("Location: index.php");
    exit;
}

$employees = $pdo->query("SELECT id, full_name FROM employees ORDER BY full_name")->fetchAll();
?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="main-content">
    <header class="top-header">
        <h1>Edit Deduction</h1>
        <div class="user-info"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
    </header>

    <div class="content-area">
        <div class="page-header">
            <div>
                <h2 class="page-heading">Edit Deduction</h2>
                <p class="page-subtext" style="margin-bottom:0;">Update details for <strong><?php echo htmlspecialchars($ded['deduction_name']); ?></strong>.</p>
            </div>
            <a href="index.php" class="btn-cancel">&larr; Back to List</a>
        </div>

        <div class="form-card">
            <form action="save.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $ded['id']; ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label>Deduction Name *</label>
                        <input type="text" name="deduction_name" class="form-control" value="<?php echo htmlspecialchars($ded['deduction_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Applies To *</label>
                        <select name="employee_id" class="form-control">
                            <option value="">All Employees</option>
                            <?php foreach ($employees as $e): ?>
                                <option value="<?php echo $e['id']; ?>" <?php if($ded['employee_id']==$e['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($e['full_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Amount / Rate *</label>
                        <input type="number" step="0.01" name="amount" class="form-control" value="<?php echo $ded['amount']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Type *</label>
                        <select name="is_percentage" class="form-control" required>
                            <option value="0" <?php if($ded['is_percentage']==0) echo 'selected'; ?>>Fixed (₦)</option>
                            <option value="1" <?php if($ded['is_percentage']==1) echo 'selected'; ?>>Percentage (%)</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Effective Date *</label>
                        <input type="date" name="effective_date" class="form-control" value="<?php echo $ded['effective_date']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Status *</label>
                        <select name="status" class="form-control" required>
                            <option value="Active"   <?php if($ded['status']==='Active')   echo 'selected'; ?>>Active</option>
                            <option value="Inactive" <?php if($ded['status']==='Inactive') echo 'selected'; ?>>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="index.php" class="btn-cancel">Cancel</a>
                    <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Update Deduction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>