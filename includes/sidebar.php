<aside class="sidebar">
    <div class="sidebar-header">Payroll System</div>
    <nav class="sidebar-nav">
        <?php $p = basename(dirname($_SERVER['PHP_SELF'])); ?>
        <a href="/payroll_system/dashboard/index.php" class="<?= $p=='dashboard'?'active':'' ?>">Dashboard</a>
        <a href="/payroll_system/employees/index.php" class="<?= $p=='employees'?'active':'' ?>">Employees</a>
        <a href="/payroll_system/departments/index.php" class="<?= $p=='departments'?'active':'' ?>">Departments</a>
        <a href="/payroll_system/positions/index.php" class="<?= $p=='positions'?'active':'' ?>">Positions</a>
        <a href="/payroll_system/payroll/index.php" class="<?= $p=='payroll'?'active':'' ?>">Payroll</a>
        <a href="/payroll_system/allowances/index.php" class="<?= $p=='allowances'?'active':'' ?>">Allowances</a>
   <a href="/payroll_system/deductions/index.php" class="<?= $p=='deductions'?'active':'' ?>">Deductions</a>
        <a href="/payroll_system/payment_methods/index.php" class="<?= $p=='payment_methods'?'active':'' ?>">Payment Methods</a>
        <a href="/payroll_system/payslips/index.php" class="<?= $p=='payslips'?'active':'' ?>">Payslips</a>
        <a href="/payroll_system/reports/index.php" class="<?= $p=='reports'?'active':'' ?>">Reports</a>
        <a href="/payroll_system/users/index.php" class="<?= $p=='users'?'active':'' ?>">Users</a>
        <a href="/payroll_system/auth/logout.php">Logout</a>
    </nav>
</aside>