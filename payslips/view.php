<?php
// ============================================================
// payslips/view.php
// ============================================================

require_once '../config/db.php';

// ------------------------------------------------------------
// Validate payslip ID
// ------------------------------------------------------------

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$payslipId = (int) $_GET['id'];

// ------------------------------------------------------------
// Get payslip information
// ------------------------------------------------------------

$stmt = $pdo->prepare("
    SELECT
        p.*,

        e.full_name,
        e.employee_id_code,

        d.name AS department_name,

        pos.title AS position_title,

        pp.period_name,
        pp.start_date,
        pp.end_date,
        pp.pay_date

    FROM payslips p

    LEFT JOIN employees e
        ON p.employee_id = e.id

    LEFT JOIN departments d
        ON e.department_id = d.id

    LEFT JOIN positions pos
        ON e.position_id = pos.id

    LEFT JOIN payroll_periods pp
        ON p.payroll_period_id = pp.id

    WHERE p.id = ?
    LIMIT 1
");

$stmt->execute([$payslipId]);

$slip = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$slip) {
    header("Location: index.php");
    exit;
}

// ------------------------------------------------------------
// Get deductions
// ------------------------------------------------------------

$stmt = $pdo->prepare("
    SELECT *
    FROM deductions
    WHERE payslip_id = ?
    ORDER BY id ASC
");

$stmt->execute([$payslipId]);

$deductions = $stmt->fetchAll(PDO::FETCH_ASSOC);


// ------------------------------------------------------------
// Helper
// ------------------------------------------------------------

function money($amount)
{
    return '₦' . number_format((float)$amount, 2);
}

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}


// ------------------------------------------------------------
// Header
// ------------------------------------------------------------

require_once '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/payslip.css">

<?php require_once '../includes/sidebar.php'; ?>


<div class="main-content">

    <!-- =====================================================
         DASHBOARD HEADER
         ===================================================== -->

    <header class="top-header no-print">

        <h1>View Payslip</h1>

        <div class="user-info">
            <?php echo e($_SESSION['full_name'] ?? 'User'); ?>
        </div>

    </header>


    <div class="content-area">


        <!-- =================================================
             PAGE HEADER
             ================================================= -->

        <div class="page-header no-print">

            <div>

                <h2 class="page-heading">
                    Payslip Details
                </h2>

                <p class="page-subtext">
                    Viewing payslip for
                    <strong>
                        <?php echo e($slip['full_name']); ?>
                    </strong>.
                </p>

            </div>


            <a href="index.php" class="btn-cancel">
                &larr; Back to List
            </a>

        </div>


        <!-- =================================================
             PAYSLIP PAPER
             ================================================= -->

        <div class="slip-paper" id="payslipPaper">


            <!-- =============================================
                 COMPANY / SYSTEM HEADER
                 ============================================= -->

            <div class="slip-header">

                <h1>
                    Payroll Management System
                </h1>

                <p>
                    Employee Salary Payslip
                </p>

            </div>


            <!-- =============================================
                 PAY SLIP TITLE
                 ============================================= -->

            <div class="slip-title">
                PAY SLIP
            </div>


            <!-- =============================================
                 EMPLOYEE INFORMATION
                 ============================================= -->

            <table class="slip-info-table">

                <tbody>

                    <tr>

                        <td>

                            <span class="info-label">
                                Employee ID
                            </span>

                            <span class="info-value">
                                <?php echo e($slip['employee_id_code']); ?>
                            </span>

                        </td>


                        <td>

                            <span class="info-label">
                                Employee Name
                            </span>

                            <span class="info-value">
                                <?php echo e($slip['full_name']); ?>
                            </span>

                        </td>

                    </tr>


                    <tr>

                        <td>

                            <span class="info-label">
                                Department
                            </span>

                            <span class="info-value">
                                <?php
                                echo e(
                                    $slip['department_name'] ?? 'N/A'
                                );
                                ?>
                            </span>

                        </td>


                        <td>

                            <span class="info-label">
                                Position
                            </span>

                            <span class="info-value">
                                <?php
                                echo e(
                                    $slip['position_title'] ?? 'N/A'
                                );
                                ?>
                            </span>

                        </td>

                    </tr>


                    <tr>

                        <td>

                            <span class="info-label">
                                Payroll Period
                            </span>

                            <span class="info-value">
                                <?php echo e($slip['period_name']); ?>
                            </span>

                        </td>


                        <td>

                            <span class="info-label">
                                Pay Date
                            </span>

                            <span class="info-value">
                                <?php echo e($slip['pay_date']); ?>
                            </span>

                        </td>

                    </tr>

                </tbody>

            </table>


            <!-- =============================================
                 EARNINGS
                 ============================================= -->

            <div class="slip-section-title">
                Earnings
            </div>


            <table class="slip-table">

                <thead>

                    <tr>

                        <th>
                            Description
                        </th>

                        <th class="amount-column">
                            Amount
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <tr>

                        <td>
                            Basic Salary
                        </td>

                        <td class="amount-column">
                            <?php echo money($slip['basic_salary']); ?>
                        </td>

                    </tr>


                    <tr>

                        <td>
                            Total Allowances
                        </td>

                        <td class="amount-column">
                            <?php echo money($slip['total_allowances']); ?>
                        </td>

                    </tr>


                    <tr class="total-row">

                        <td>
                            Gross Salary
                        </td>

                        <td class="amount-column">
                            <?php echo money($slip['gross_salary']); ?>
                        </td>

                    </tr>

                </tbody>

            </table>


            <!-- =============================================
                 DEDUCTIONS
                 ============================================= -->

            <div class="slip-section-title">
                Deductions
            </div>


            <table class="slip-table">

                <thead>

                    <tr>

                        <th>
                            Description
                        </th>

                        <th class="amount-column">
                            Amount
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <?php if (!empty($deductions)): ?>

                        <?php foreach ($deductions as $deduction): ?>

                            <tr>

                                <td>
                                    <?php
                                    echo e(
                                        $deduction['deduction_name']
                                    );
                                    ?>
                                </td>

                                <td class="amount-column">
                                    <?php
                                    echo money(
                                        $deduction['amount']
                                    );
                                    ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>

                            <td>
                                Tax
                            </td>

                            <td class="amount-column">
                                ₦0.00
                            </td>

                        </tr>


                        <tr>

                            <td>
                                Pension
                            </td>

                            <td class="amount-column">
                                ₦0.00
                            </td>

                        </tr>


                        <tr>

                            <td>
                                Other Deductions
                            </td>

                            <td class="amount-column">
                                ₦0.00
                            </td>

                        </tr>

                    <?php endif; ?>


                    <tr class="total-row">

                        <td>
                            Total Deductions
                        </td>

                        <td class="amount-column">
                            <?php
                            echo money(
                                $slip['total_deductions']
                            );
                            ?>
                        </td>

                    </tr>

                </tbody>

            </table>


            <!-- =============================================
                 NET SALARY
                 ============================================= -->

            <div class="slip-net-box">

                <span class="net-label">
                    NET SALARY
                </span>

                <span class="net-value">
                    <?php echo money($slip['net_salary']); ?>
                </span>

            </div>


            <!-- =============================================
                 FOOTER NOTE
                 ============================================= -->

            <div class="slip-footer-note">

                This is a computer-generated payslip.<br>

                Payroll Period:
                <?php echo e($slip['start_date']); ?>
                -
                <?php echo e($slip['end_date']); ?>

            </div>


        </div>
        <!-- END PAYSLIP PAPER -->


        <!-- =================================================
             PRINT BUTTON
             ================================================= -->

        <div class="slip-print-bar no-print">

            <button
                type="button"
                onclick="window.print();"
                class="slip-print-btn"
            >

                <i class="fas fa-print"></i>

                Print Payslip

            </button>

        </div>


    </div>

</div>


<?php require_once '../includes/footer.php'; ?>