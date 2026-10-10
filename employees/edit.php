<?php
// employees/edit.php
require_once '../config/db.php';
require_once '../includes/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int) $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
$stmt->execute([$id]);
$emp = $stmt->fetch();

if (!$emp) {
    header("Location: index.php");
    exit;
}

$departments = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll();
$positions   = $pdo->query("SELECT * FROM positions WHERE status='Active' ORDER BY title")->fetchAll();
?>

<?php require_once '../includes/sidebar.php'; ?>

<div class="main-content">
    <header class="top-header">
        <h1>Edit Employee</h1>
        <div class="user-info"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
    </header>

    <div class="content-area">
        <div class="page-header">
            <div>
                <h2 class="page-heading">Edit Employee</h2>
                <p class="page-subtext" style="margin-bottom:0;">Update details for <strong><?php echo htmlspecialchars($emp['full_name']); ?></strong>.</p>
            </div>
            <a href="index.php" class="btn-cancel">&larr; Back to List</a>
        </div>

        <div class="form-card">
            <form action="save.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $emp['id']; ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label>Employee ID *</label>
                        <input type="text" name="employee_id_code" class="form-control" value="<?php echo htmlspecialchars($emp['employee_id_code']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($emp['full_name']); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Gender *</label>
                        <select name="gender" class="form-control" required>
                            <option value="Male"   <?php if($emp['gender']==='Male') echo 'selected'; ?>>Male</option>
                            <option value="Female" <?php if($emp['gender']==='Female') echo 'selected'; ?>>Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($emp['email']); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($emp['phone']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Employment Type *</label>
                        <select name="employment_type" class="form-control" required>
                            <option value="Full-time" <?php if($emp['employment_type']==='Full-time') echo 'selected'; ?>>Full-time</option>
                            <option value="Part-time" <?php if($emp['employment_type']==='Part-time') echo 'selected'; ?>>Part-time</option>
                            <option value="Contract"  <?php if($emp['employment_type']==='Contract')  echo 'selected'; ?>>Contract</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Department *</label>
                        <select name="department_id" class="form-control" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?php echo $d['id']; ?>" <?php if($emp['department_id']==$d['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($d['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Position *</label>
                        <select name="position_id" class="form-control" required>
                            <option value="">Select Position</option>
                            <?php foreach ($positions as $p): ?>
                                <option value="<?php echo $p['id']; ?>" <?php if($emp['position_id']==$p['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($p['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Basic Salary (₦) *</label>
                        <input type="number" step="0.01" name="basic_salary" class="form-control" value="<?php echo htmlspecialchars($emp['basic_salary']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Status *</label>
                        <select name="status" class="form-control" required>
                            <option value="Active"   <?php if($emp['status']==='Active')   echo 'selected'; ?>>Active</option>
                            <option value="Inactive" <?php if($emp['status']==='Inactive') echo 'selected'; ?>>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="index.php" class="btn-cancel">Cancel</a>
                    <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Update Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>