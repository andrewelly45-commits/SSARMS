<?php
session_start();
include '../db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

/* ================= AUTH CHECK ================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'academic') {
    header("Location: ../auth/login.php");
    exit();
}

$term = $_GET['term'] ?? '';
$year = $_GET['year'] ?? '';
$class_id = $_GET['class_id'] ?? '';

$where = "WHERE 1=1";

if (!empty($term)) {
    $where .= " AND sr.term='$term'";
}

if (!empty($year)) {
    $where .= " AND sr.academic_year='$year'";
}

if (!empty($class_id)) {
    $where .= " AND sr.class_id='$class_id'";
}

/* ================= MAIN QUERY ================= */
$query = mysqli_query($conn, "
    SELECT
        sr.*,
        u.full_name,
        c.class_name
    FROM student_results sr
    JOIN student s ON sr.student_id = s.student_id
    JOIN users u ON s.user_id = u.user_id
    JOIN class c ON sr.class_id = c.class_id
    $where
    ORDER BY sr.average DESC
");

/* ================= STATISTICS QUERY - USING SAME FILTERS ================= */
$stats_query = "
    SELECT 
        COUNT(*) as total_students,
        SUM(CASE WHEN division='Division I' THEN 1 ELSE 0 END) as div1,
        SUM(CASE WHEN division='Division II' THEN 1 ELSE 0 END) as div2,
        SUM(CASE WHEN division='Division III' THEN 1 ELSE 0 END) as div3,
        SUM(CASE WHEN division='Division IV' THEN 1 ELSE 0 END) as div4,
        SUM(CASE WHEN division='INC' THEN 1 ELSE 0 END) as inc,
        ROUND(AVG(average), 2) as overall_average,
        MAX(average) as highest_score,
        MIN(average) as lowest_score
    FROM student_results sr
    $where
";

$stats = mysqli_query($conn, $stats_query);
$stat = mysqli_fetch_assoc($stats);

// Calculate pass rate
$total = $stat['total_students'] > 0 ? $stat['total_students'] : 1;
$passed = ($stat['div1'] ?? 0) + ($stat['div2'] ?? 0) + ($stat['div3'] ?? 0);
$pass_rate = round(($passed / $total) * 100, 2);

// Debug - you can remove this after testing
// echo "Stats Query: " . $stats_query;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Results</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* ===== RESET & BASE ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
            min-height: 100vh;
        }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-left: 270px;
            margin-top: 75px;
            padding: 25px 30px;
            min-height: calc(100vh - 75px);
        }

        /* ===== CARDS ===== */
        .card {
            background: #ffffff;
            border-radius: 12px;
            padding: 20px 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .card-stats {
            background: #ffffff;
            border-radius: 8px;
            padding: 12px 18px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
            font-weight: 600;
            font-size: 14px;
            border-left: 4px solid #4f46e5;
            transition: all 0.3s;
            min-width: 120px;
        }

        .card-stats:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .card-stats .stat-number {
            font-size: 22px;
            font-weight: 700;
            display: block;
            margin-top: 4px;
        }

        .card-stats .stat-label {
            font-size: 13px;
            color: #64748b;
            font-weight: 400;
        }

        .card-stats.inc {
            border-left-color: #f59e0b;
        }

        .card-stats.primary {
            border-left-color: #4f46e5;
        }

        .card-stats.success {
            border-left-color: #10b981;
        }

        .card-stats.info {
            border-left-color: #3b82f6;
        }

        .card-stats.warning {
            border-left-color: #f59e0b;
        }

        .card-stats.danger {
            border-left-color: #ef4444;
        }

        .card-stats.purple {
            border-left-color: #8b5cf6;
        }

        .card-stats.pink {
            border-left-color: #ec4899;
        }

        /* ===== PAGE TITLE ===== */
        .page-title {
            margin-bottom: 25px;
            color: #1e293b;
            font-size: 24px;
            font-weight: 700;
        }

        .page-title i {
            color: #f59e0b;
            margin-right: 10px;
        }

        /* ===== FILTERS ===== */
        .filters {
            display: flex;
            gap: 12px;
            margin-bottom: 25px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filters select,
        .filters button {
            padding: 10px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            background: #ffffff;
            transition: all 0.2s;
        }

        .filters select:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .filters button {
            background: #f59e0b;
            color: #ffffff;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }

        .filters button:hover {
            background: #d97706;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }

        .filters button i {
            margin-right: 6px;
        }

        .filters .btn-reset {
            background: #94a3b8;
        }

        .filters .btn-reset:hover {
            background: #64748b;
        }

        /* ===== STATISTICS ROW ===== */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }

        /* ===== FILTER INFO ===== */
        .filter-info {
            background: #f1f5f9;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #475569;
            font-size: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .filter-info .badge {
            background: #4f46e5;
            color: #fff;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
            margin: 2px;
        }

        /* ===== TABLE ===== */
        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        thead {
            background: #1e293b;
            color: #ffffff;
        }

        thead th {
            padding: 14px 16px;
            text-align: left;
            font-weight: 600;
            white-space: nowrap;
        }

        tbody td {
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
        }

        tbody tr:hover {
            background: #f8fafc;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        /* ===== DIVISION COLORS ===== */
        .div1 {
            color: #10b981;
            font-weight: 700;
        }

        .div2 {
            color: #3b82f6;
            font-weight: 700;
        }

        .div3 {
            color: #f59e0b;
            font-weight: 700;
        }

        .div4 {
            color: #ef4444;
            font-weight: 700;
        }

        .inc {
            color: #f59e0b;
            font-weight: 700;
        }

        .fail {
            color: #ef4444;
            font-weight: 700;
        }

        /* ===== NO DATA ===== */
        .no-data {
            text-align: center;
            padding: 40px 20px;
            color: #94a3b8;
        }

        .no-data i {
            font-size: 48px;
            display: block;
            margin-bottom: 10px;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
                margin-top: 65px;
            }

            .filters {
                flex-direction: column;
                width: 100%;
            }

            .filters select,
            .filters button {
                width: 100%;
            }

            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }

            .card-stats {
                min-width: unset;
            }

            table {
                font-size: 13px;
            }

            thead th,
            tbody td {
                padding: 10px 12px;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 10px;
                margin-top: 60px;
            }

            .page-title {
                font-size: 20px;
            }

            .card {
                padding: 15px;
            }

            .stats-row {
                grid-template-columns: 1fr 1fr;
            }

            table {
                font-size: 12px;
            }

            thead th,
            tbody td {
                padding: 8px 10px;
            }

            .card-stats .stat-number {
                font-size: 18px;
            }
        }

        /* ===== SCROLLBAR ===== */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #4f46e5;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #4338ca;
        }

        /* ===== REFRESH INDICATOR ===== */
        .last-updated {
            font-size: 12px;
            color: #94a3b8;
            text-align: right;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
        }

        .last-updated i {
            margin-right: 5px;
        }
    </style>
</head>
<body>

    <!-- ===== SIDEBAR ===== -->
    <?php include 'academic_sidebar.php'; ?>

    <!-- ===== TOPBAR ===== -->
    <?php include '../auth/topbar.php'; ?>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="main-content">

        <!-- Page Title -->
        <h2 class="page-title">
            <i class="fas fa-chart-line"></i>
            School Results
        </h2>

        <!-- Results Card -->
        <div class="card">

            <!-- Filters -->
            <form method="GET" class="filters">
                <select name="term">
                    <option value="">All Terms</option>
                    <option value="Term 1" <?= ($term == 'Term 1') ? 'selected' : '' ?>>Term 1</option>
                    <option value="Term 2" <?= ($term == 'Term 2') ? 'selected' : '' ?>>Term 2</option>
                </select>

                <select name="year">
                    <option value="">All Years</option>
                    <?php
                    $years = mysqli_query($conn, "
                        SELECT DISTINCT academic_year
                        FROM student_results
                        ORDER BY academic_year DESC
                    ");
                    while ($y = mysqli_fetch_assoc($years)) {
                    ?>
                        <option value="<?= $y['academic_year'] ?>" <?= ($year == $y['academic_year']) ? 'selected' : '' ?>>
                            <?= $y['academic_year'] ?>
                        </option>
                    <?php } ?>
                </select>

                <select name="class_id">
                    <option value="">All Classes</option>
                    <?php
                    $cls = mysqli_query($conn, "SELECT * FROM class ORDER BY class_name");
                    while ($c = mysqli_fetch_assoc($cls)) {
                    ?>
                        <option value="<?= $c['class_id'] ?>" <?= ($class_id ?? '') == $c['class_id'] ? 'selected' : '' ?>>
                            <?= $c['class_name'] ?>
                        </option>
                    <?php } ?>
                </select>

                <button type="submit">
                    <i class="fas fa-search"></i> Filter
                </button>

                <?php if (!empty($term) || !empty($year) || !empty($class_id)): ?>
                    <a href="school_results.php" class="filters btn-reset" style="
                        padding: 10px 16px;
                        border-radius: 8px;
                        font-size: 14px;
                        background: #94a3b8;
                        color: #ffffff;
                        text-decoration: none;
                        font-weight: 600;
                        transition: all 0.2s;
                        display: inline-flex;
                        align-items: center;
                        gap: 6px;
                    ">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                <?php endif; ?>
            </form>

            <!-- Filter Info -->
            <?php if (!empty($term) || !empty($year) || !empty($class_id)): ?>
                <div class="filter-info">
                    <span>
                        <i class="fas fa-filter"></i> 
                        Showing filtered results:
                        <?php if (!empty($term)): ?>
                            <span class="badge"><?= $term ?></span>
                        <?php endif; ?>
                        <?php if (!empty($year)): ?>
                            <span class="badge"><?= $year ?></span>
                        <?php endif; ?>
                        <?php if (!empty($class_id)): ?>
                            <span class="badge">
                                <?php 
                                    $class_name = mysqli_fetch_assoc(mysqli_query($conn, "SELECT class_name FROM class WHERE class_id='$class_id'"));
                                    echo $class_name['class_name'] ?? $class_id;
                                ?>
                            </span>
                        <?php endif; ?>
                    </span>
                    <span>
                        Total: <strong><?= $stat['total_students'] ?? 0 ?></strong> students
                    </span>
                </div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="stats-row">
                <div class="card-stats primary">
                    <span class="stat-label"><i class="fas fa-users"></i> Total</span>
                    <span class="stat-number"><?= $stat['total_students'] ?? 0 ?></span>
                </div>
                <div class="card-stats success">
                    <span class="stat-label"><i class="fas fa-trophy"></i> Division I</span>
                    <span class="stat-number"><?= $stat['div1'] ?? 0 ?></span>
                </div>
                <div class="card-stats info">
                    <span class="stat-label"><i class="fas fa-award"></i> Division II</span>
                    <span class="stat-number"><?= $stat['div2'] ?? 0 ?></span>
                </div>
                <div class="card-stats warning">
                    <span class="stat-label"><i class="fas fa-medal"></i> Division III</span>
                    <span class="stat-number"><?= $stat['div3'] ?? 0 ?></span>
                </div>
                <div class="card-stats danger">
                    <span class="stat-label"><i class="fas fa-exclamation-triangle"></i> Division IV</span>
                    <span class="stat-number"><?= $stat['div4'] ?? 0 ?></span>
                </div>
                <div class="card-stats inc">
                    <span class="stat-label"><i class="fas fa-clock"></i> INC</span>
                    <span class="stat-number"><?= $stat['inc'] ?? 0 ?></span>
                </div>
                <div class="card-stats purple">
                    <span class="stat-label"><i class="fas fa-check-circle"></i> Pass Rate</span>
                    <span class="stat-number"><?= $pass_rate ?>%</span>
                </div>
                <div class="card-stats pink">
                    <span class="stat-label"><i class="fas fa-arrow-up"></i> Avg Score</span>
                    <span class="stat-number"><?= $stat['overall_average'] ?? 0 ?>%</span>
                </div>
            </div>

            <!-- Results Table -->
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Class</th>
                            <th>Term</th>
                            <th>Year</th>
                            <th>Total Marks</th>
                            <th>Points</th>
                            <th>Average</th>
                            <th>Division</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if (mysqli_num_rows($query) > 0) {
                            while ($row = mysqli_fetch_assoc($query)) {
                                $divisionClass = '';
                                switch ($row['division']) {
                                    case 'Division I':
                                        $divisionClass = 'div1';
                                        break;
                                    case 'Division II':
                                        $divisionClass = 'div2';
                                        break;
                                    case 'Division III':
                                        $divisionClass = 'div3';
                                        break;
                                    case 'Division IV':
                                        $divisionClass = 'div4';
                                        break;
                                    case 'INC':
                                        $divisionClass = 'inc';
                                        break;
                                    default:
                                        $divisionClass = 'fail';
                                }
                        ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['full_name']) ?></td>
                                <td><?= htmlspecialchars($row['class_name']) ?></td>
                                <td><?= $row['term'] ?></td>
                                <td><?= $row['academic_year'] ?></td>
                                <td><?= $row['total_marks'] ?></td>
                                <td><?= $row['total_points'] ?></td>
                                <td><?= number_format($row['average'], 2) ?>%</td>
                                <td class="<?= $divisionClass ?>"><?= $row['division'] ?></td>
                            </tr>
                        <?php
                            }
                        } else {
                        ?>
                            <tr>
                                <td colspan="9">
                                    <div class="no-data">
                                        <i class="fas fa-inbox"></i>
                                        No results found
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <!-- Last Updated -->
            <div class="last-updated">
                <i class="fas fa-sync-alt"></i>
                Last updated: <?= date('Y-m-d H:i:s') ?>
            </div>

        </div>
    </div>

    <!-- ===== FOOTER ===== -->
    <?php include '../footer.php'; ?>

</body>
</html>