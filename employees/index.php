<?php
// employees/index.php
require_once '../config/db.php';
require_once '../includes/header.php';

// Build query with filters
$where = [];
$params = [];

if (!empty($_GET['search'])) {
    $where[] = "(e.full_name LIKE ? OR e.employee_id_code LIKE ? OR e.email LIKE ? OR e.phone LIKE ?)";
    $s = '%' . $_GET['search'] . '%';
    array_push($params, $s, $s, $s, $s);
}
if (!empty($_GET['status'])) {
    $where[] = "e.status = ?";
    $params[] = $_GET['status'];
}
if (!empty($_GET['department'])) {
    $where[] = "e.department_id = ?";
    $params[] = $_GET['department'];
}
if (!empty($_GET['employment_type'])) {
    $where[] = "e.employment_type = ?";
    $params[] = $_GET['employment_type'];
}

$sql = "SELECT e.*, d.name AS department_name, p.title AS position_title 
        FROM employees e 
        LEFT JOIN departments d ON e.department_id = d.id 
        LEFT JOIN positions p ON e.position_id = p.id";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY e.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$employees = $stmt->fetchAll();

// For filter dropdowns
$departments = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll();
?>

<?php require_once '../includes/sidebar.php'; ?>

<div class="main-content">
    <header class="top-header">
        <h1>Employees</h1>
        <div class="user-info"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
    </header>

    <div class="content-area">

        <?php if (isset($_GET['success'])): ?>
            <div class="alert-success">
                <?php
                if ($_GET['success'] === 'added')   echo "Employee added successfully.";
                if ($_GET['success'] === 'updated') echo "Employee updated successfully.";
                if ($_GET['success'] === 'deleted') echo "Employee deleted successfully.";
                if ($_GET['success'] === 'toggled') echo "Employee status updated.";
                ?>
            </div>
        <?php endif; ?>

        <div class="page-header">
            <div>
                <h2 class="page-heading">Employees</h2>
                <p class="page-subtext" style="margin-bottom:0;">Manage all employees in the organization.</p>
            </div>
            <a href="create.php" class="btn-primary">
                <i class="fas fa-plus"></i> Add Employee
            </a>
        </div>

        <!-- Filter Card -->
        <div class="filter-card">
            <form method="GET" action="index.php">
                <div class="filter-grid">
                    <div class="filter-item">
                        <label>Search Employee</label>
                        <input type="text" name="search" class="form-control" placeholder="Name, ID, phone, email..."
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
                    <div class="filter-item">
                        <label>Employment Type</label>
                        <select name="employment_type" class="form-control">
                            <option value="">All Types</option>
                            <option value="Full-time" <?php if(($_GET['employment_type']??'')==='Full-time') echo 'selected'; ?>>Full-time</option>
                            <option value="Part-time" <?php if(($_GET['employment_type']??'')==='Part-time') echo 'selected'; ?>>Part-time</option>
                            <option value="Contract"  <?php if(($_GET['employment_type']??'')==='Contract')  echo 'selected'; ?>>Contract</option>
                        </select>
                    </div>
                    <div class="filter-buttons">
                        <button type="submit" class="btn-primary"><i class="fas fa-search"></i> Search</button>
                        <a href="index.php" class="btn-cancel">Clear</a>
                    </div>
                </div>
            </form>
        </div>

        <p class="table-info">Showing <?php echo count($employees); ?> employee(s).</p>

        <div class="table-container">
            <?php if (count($employees) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>Department</th>
                            <th>Position</th>
                            <th>Basic Salary</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employees as $i => $emp): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td><?php echo htmlspecialchars($emp['employee_id_code']); ?></td>
                                <td><?php echo htmlspecialchars($emp['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($emp['gender']); ?></td>
                                <td><?php echo htmlspecialchars($emp['department_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($emp['position_title'] ?? 'N/A'); ?></td>
                                <td>₦<?php echo number_format($emp['basic_salary'], 2); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo strtolower($emp['status']); ?>">
                                        <?php echo $emp['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="edit.php?id=<?php echo $emp['id']; ?>" class="btn-edit">Edit</a>

                                    <a href="toggle.php?id=<?php echo $emp['id']; ?>"
                                       class="btn-<?php echo $emp['status']==='Active'?'delete':'edit'; ?>"
                                       onclick="return confirm('Change status for this employee?');">
                                        <?php echo $emp['status']==='Active'?'Deactivate':'Activate'; ?>
                                    </a>

                                    <a href="delete.php?id=<?php echo $emp['id']; ?>"
                                       class="btn-delete"
                                       onclick="return confirm('Delete this employee permanently?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 15px; color: #cbd5e1;"></i>
                    <h3>No Employees Found</h3>
                    <p>Try adjusting your filters or add a new employee.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>