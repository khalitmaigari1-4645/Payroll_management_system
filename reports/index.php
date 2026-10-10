<?php
// ============================================================
// reports/index.php
// Payroll Reports
// ============================================================

require_once '../config/db.php';
require_once '../includes/header.php';


// ============================================================
// INITIAL VALUES
// ============================================================

$selected_period = null;
$payslips = [];

$totals = [
    'employees'  => 0,
    'gross'      => 0,
    'deductions' => 0,
    'net'        => 0,
    'basic'      => 0,
    'allowances' => 0
];


// ============================================================
// GET SELECTED PERIOD
// ============================================================

if (!empty($_GET['period'])) {

    $period_id = (int) $_GET['period'];

    $stmt = $pdo->prepare("
        SELECT *
        FROM payroll_periods
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$period_id]);

    $selected_period = $stmt->fetch(PDO::FETCH_ASSOC);


    // ========================================================
    // GET PAYSLIPS FOR PERIOD
    // ========================================================

    if ($selected_period) {

        $stmt = $pdo->prepare("
            SELECT
                p.*,
                e.full_name,
                e.employee_id_code,
                d.name AS department_name

            FROM payslips p

            LEFT JOIN employees e
                ON p.employee_id = e.id

            LEFT JOIN departments d
                ON e.department_id = d.id

            WHERE p.payroll_period_id = ?

            ORDER BY p.id ASC
        ");

        $stmt->execute([
            $selected_period['id']
        ]);

        $payslips = $stmt->fetchAll(PDO::FETCH_ASSOC);


        // ====================================================
        // CALCULATE TOTALS
        // ====================================================

        foreach ($payslips as $p) {

            $totals['basic'] += (float) $p['basic_salary'];

            $totals['allowances'] +=
                (float) $p['total_allowances'];

            $totals['gross'] +=
                (float) $p['gross_salary'];

            $totals['deductions'] +=
                (float) $p['total_deductions'];

            $totals['net'] +=
                (float) $p['net_salary'];
        }

        $totals['employees'] = count($payslips);
    }
}


// ============================================================
// GET ALL PAYROLL PERIODS
// ============================================================

$periods = $pdo->query("
    SELECT *
    FROM payroll_periods
    ORDER BY id DESC
")->fetchAll(PDO::FETCH_ASSOC);


// ============================================================
// HELPER FUNCTIONS
// ============================================================

function reportMoney($amount)
{
    return '₦' . number_format((float) $amount, 2);
}


function reportEscape($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

?>

<link rel="stylesheet" href="../assets/css/reports.css">

<?php require_once '../includes/sidebar.php'; ?>


<div class="main-content">

    <!-- ======================================================
         PAGE HEADER
         ====================================================== -->

    <header class="top-header no-print">

        <h1>Payroll Reports</h1>

        <div class="user-info">
            <?php
            echo reportEscape(
                $_SESSION['full_name'] ?? 'User'
            );
            ?>
        </div>

    </header>


    <div class="content-area">


        <!-- ==================================================
             PAGE TITLE
             ================================================== -->

        <div class="report-page-header no-print">

            <div>

                <h2>
                    Payroll Reports
                </h2>

                <p>
                    View payroll summaries and employee
                    payroll details.
                </p>

            </div>

        </div>


        <!-- ==================================================
             PERIOD SELECTOR
             ================================================== -->

        <div class="report-filter-card no-print">

            <form
                method="GET"
                action="index.php"
            >

                <div class="report-filter-grid">

                    <div class="report-filter-field">

                        <label for="period">
                            Select Payroll Period
                        </label>

                        <select
                            name="period"
                            id="period"
                            required
                        >

                            <option value="">
                                Choose a period...
                            </option>

                            <?php foreach ($periods as $pp): ?>

                                <option
                                    value="<?php echo (int) $pp['id']; ?>"
                                    <?php
                                    if (
                                        isset($_GET['period']) &&
                                        (int) $_GET['period'] ===
                                        (int) $pp['id']
                                    ) {
                                        echo 'selected';
                                    }
                                    ?>
                                >

                                    <?php
                                    echo reportEscape(
                                        $pp['period_name']
                                    );
                                    ?>

                                    -
                                    <?php
                                    echo reportEscape(
                                        $pp['status']
                                    );
                                    ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="report-filter-button">

                        <button
                            type="submit"
                            class="report-btn-primary"
                        >

                            <i class="fas fa-chart-bar"></i>

                            Generate Report

                        </button>

                    </div>

                </div>

            </form>

        </div>


        <?php if ($selected_period): ?>


            <!-- ==================================================
                 REPORT PAPER
                 ================================================== -->

            <div
                class="report-paper"
                id="reportPaper"
            >


                <!-- ==============================================
                     REPORT HEADER
                     ============================================== -->

                <div class="report-document-header">

                    <h1>
                        Payroll Management System
                    </h1>

                    <p>
                        Employee Payroll Report
                    </p>

                </div>


                <!-- ==============================================
                     PERIOD TITLE
                     ============================================== -->

                <div class="report-title">

                    <?php
                    echo reportEscape(
                        $selected_period['period_name']
                    );
                    ?>

                </div>


                <!-- ==============================================
                     PERIOD INFORMATION
                     ============================================== -->

                <div class="report-period-grid">


                    <div class="report-info-box">

                        <span>
                            Start Date
                        </span>

                        <strong>
                            <?php
                            echo reportEscape(
                                $selected_period['start_date']
                            );
                            ?>
                        </strong>

                    </div>


                    <div class="report-info-box">

                        <span>
                            End Date
                        </span>

                        <strong>
                            <?php
                            echo reportEscape(
                                $selected_period['end_date']
                            );
                            ?>
                        </strong>

                    </div>


                    <div class="report-info-box">

                        <span>
                            Pay Date
                        </span>

                        <strong>
                            <?php
                            echo reportEscape(
                                $selected_period['pay_date']
                            );
                            ?>
                        </strong>

                    </div>


                    <div class="report-info-box">

                        <span>
                            Status
                        </span>

                        <strong>

                            <span
                                class="report-status
                                report-status-<?php
                                    echo strtolower(
                                        reportEscape(
                                            $selected_period['status']
                                        )
                                    );
                                ?>"
                            >

                                <?php
                                echo reportEscape(
                                    $selected_period['status']
                                );
                                ?>

                            </span>

                        </strong>

                    </div>

                </div>


                <!-- ==============================================
                     SUMMARY STATISTICS
                     ============================================== -->

                <div class="report-summary-grid">


                    <div class="report-summary-box">

                        <span>
                            Employees
                        </span>

                        <strong>
                            <?php
                            echo number_format(
                                $totals['employees']
                            );
                            ?>
                        </strong>

                    </div>


                    <div class="report-summary-box">

                        <span>
                            Total Gross
                        </span>

                        <strong>
                            <?php
                            echo reportMoney(
                                $totals['gross']
                            );
                            ?>
                        </strong>

                    </div>


                    <div class="report-summary-box">

                        <span>
                            Total Deductions
                        </span>

                        <strong>
                            <?php
                            echo reportMoney(
                                $totals['deductions']
                            );
                            ?>
                        </strong>

                    </div>


                    <div class="report-summary-box">

                        <span>
                            Total Net Salary
                        </span>

                        <strong>
                            <?php
                            echo reportMoney(
                                $totals['net']
                            );
                            ?>
                        </strong>

                    </div>

                </div>


                <!-- ==============================================
                     EMPLOYEE PAYROLL
                     ============================================== -->

                <div class="report-section-bar">
                    Employee Payroll Report
                </div>


                <?php if (!empty($payslips)): ?>

                    <div class="report-table-wrapper">

                        <table class="report-table">

                            <thead>

                                <tr>

                                    <th>#</th>

                                    <th>
                                        Employee ID
                                    </th>

                                    <th>
                                        Employee Name
                                    </th>

                                    <th>
                                        Department
                                    </th>

                                    <th class="money">
                                        Basic Salary
                                    </th>

                                    <th class="money">
                                        Allowances
                                    </th>

                                    <th class="money">
                                        Gross Salary
                                    </th>

                                    <th class="money">
                                        Deductions
                                    </th>

                                    <th class="money">
                                        Net Salary
                                    </th>

                                    <th>
                                        Status
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                <?php foreach ($payslips as $i => $p): ?>

                                    <tr>

                                        <td>
                                            <?php echo $i + 1; ?>
                                        </td>

                                        <td>
                                            <?php
                                            echo reportEscape(
                                                $p['employee_id_code']
                                            );
                                            ?>
                                        </td>

                                        <td class="employee-name">
                                            <?php
                                            echo reportEscape(
                                                $p['full_name']
                                            );
                                            ?>
                                        </td>

                                        <td>
                                            <?php
                                            echo reportEscape(
                                                $p['department_name']
                                                ?? 'N/A'
                                            );
                                            ?>
                                        </td>

                                        <td class="money">
                                            <?php
                                            echo reportMoney(
                                                $p['basic_salary']
                                            );
                                            ?>
                                        </td>

                                        <td class="money">
                                            <?php
                                            echo reportMoney(
                                                $p['total_allowances']
                                            );
                                            ?>
                                        </td>

                                        <td class="money">
                                            <?php
                                            echo reportMoney(
                                                $p['gross_salary']
                                            );
                                            ?>
                                        </td>

                                        <td class="money">
                                            <?php
                                            echo reportMoney(
                                                $p['total_deductions']
                                            );
                                            ?>
                                        </td>

                                        <td class="money">
                                            <?php
                                            echo reportMoney(
                                                $p['net_salary']
                                            );
                                            ?>
                                        </td>

                                        <td>

                                            <span
                                                class="report-row-status
                                                report-row-status-<?php
                                                    echo strtolower(
                                                        reportEscape(
                                                            $p['status']
                                                        )
                                                    );
                                                ?>"
                                            >

                                                <?php
                                                echo reportEscape(
                                                    $p['status']
                                                );
                                                ?>

                                            </span>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>


                                <!-- ==================================
                                     TOTAL ROW
                                     ================================== -->

                                <tr class="report-total-row">

                                    <td
                                        colspan="4"
                                        class="total-label"
                                    >
                                        TOTAL
                                    </td>

                                    <td class="money">
                                        <?php
                                        echo reportMoney(
                                            $totals['basic']
                                        );
                                        ?>
                                    </td>

                                    <td class="money">
                                        <?php
                                        echo reportMoney(
                                            $totals['allowances']
                                        );
                                        ?>
                                    </td>

                                    <td class="money">
                                        <?php
                                        echo reportMoney(
                                            $totals['gross']
                                        );
                                        ?>
                                    </td>

                                    <td class="money">
                                        <?php
                                        echo reportMoney(
                                            $totals['deductions']
                                        );
                                        ?>
                                    </td>

                                    <td class="money">
                                        <?php
                                        echo reportMoney(
                                            $totals['net']
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        -
                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>


                <?php else: ?>


                    <div class="report-empty">

                        <i class="fas fa-file-invoice"></i>

                        <h3>
                            No Payslips for This Period
                        </h3>

                        <p>
                            Generate payslips from the
                            Payslips module first.
                        </p>

                    </div>


                <?php endif; ?>


                <!-- ==============================================
                     REPORT FOOTER
                     ============================================== -->

                <div class="report-footer">

                    This is a computer-generated payroll report.<br>

                    Payroll Period:
                    <?php
                    echo reportEscape(
                        $selected_period['start_date']
                    );
                    ?>

                    -

                    <?php
                    echo reportEscape(
                        $selected_period['end_date']
                    );
                    ?>

                </div>


            </div>
            <!-- END REPORT PAPER -->


            <!-- ==================================================
                 ACTION BUTTONS
                 ================================================== -->

            <div class="report-actions no-print">

                <button
                    type="button"
                    onclick="window.print();"
                    class="report-btn-primary"
                >

                    <i class="fas fa-print"></i>

                    Print Report

                </button>


                <a
                    href="index.php"
                    class="report-btn-cancel"
                >

                    <i class="fas fa-times"></i>

                    Clear Report

                </a>

            </div>


        <?php else: ?>


            <!-- ==================================================
                 NO REPORT SELECTED
                 ================================================== -->

            <div class="report-empty report-empty-page">

                <i class="fas fa-chart-bar"></i>

                <h3>
                    No Report Selected
                </h3>

                <p>
                    Select a payroll period above and click
                    "Generate Report".
                </p>

            </div>


        <?php endif; ?>


    </div>

</div>


<?php require_once '../includes/footer.php'; ?>