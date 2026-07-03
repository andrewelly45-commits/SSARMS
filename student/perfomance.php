<?php
session_start();
include '../db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

/* ================= AUTH CHECK ================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ================= GET STUDENT ================= */
$student_query = mysqli_query($conn, "
    SELECT s.student_id, s.class_id, u.full_name, u.email, u.profile_pic, c.class_name
    FROM student s
    JOIN users u ON s.user_id = u.user_id
    JOIN class c ON s.class_id = c.class_id
    WHERE s.user_id = '$user_id'
");

$student = mysqli_fetch_assoc($student_query);

if (!$student) {
    die("Student not found");
}

$student_id = $student['student_id'];
$student_name = $student['full_name'];
$class_name = $student['class_name'];

/* ================= GET ONLY PUBLISHED MARKS ================= */
$avg_query = mysqli_query($conn, "
    SELECT AVG(marks) AS avg_mark
    FROM marks
    WHERE student_id = '$student_id'
      AND status = 'published'
");

$avg_row = mysqli_fetch_assoc($avg_query);
$avg = $avg_row['avg_mark'] ?? 0;

/* ================= SUBJECT PERFORMANCE ================= */
$subject_query = mysqli_query($conn, "
    SELECT 
        s.subject_name,
        AVG(m.marks) AS avg_mark
    FROM marks m
    JOIN subject s ON m.subject_id = s.subject_id
    WHERE m.student_id = '$student_id'
      AND m.status = 'published'
    GROUP BY m.subject_id, s.subject_name
    ORDER BY avg_mark DESC
");

// Store data for best subject
$subjects = [];
$marks = [];

while ($row = mysqli_fetch_assoc($subject_query)) {
    $subjects[] = $row['subject_name'];
    $marks[] = round($row['avg_mark'], 1);
}

// Reset pointer for table display
mysqli_data_seek($subject_query, 0);

include 'student_sidebar.php';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Performance | SSARMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f0f2f5;
        }

        /* ========== MAIN CONTENT ========== */
        .main-content {
            margin-left: 270px;
            padding: 25px 30px;
            min-height: 100vh;
        }

        /* ========== PAGE HEADER ========== */
        .page-header {
            margin-bottom: 25px;
        }

        .page-header h2 {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-header h2 i {
            color: #f59e0b;
        }

        /* ========== STUDENT INFO BAR ========== */
        .info-bar {
            background: white;
            padding: 12px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .info-bar span {
            font-size: 13px;
            color: #475569;
        }

        .info-bar i {
            color: #f59e0b;
            width: 20px;
        }

        /* ========== CARDS ========== */
        .card {
            background: white;
            border-radius: 16px;
            margin-bottom: 25px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .card-header {
            padding: 15px 20px;
            border-bottom: 1px solid #e2e8f0;
            background: #fafafa;
        }

        .card-header h3 {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
        }

        .card-header h3 i {
            color: #f59e0b;
            margin-right: 8px;
        }

        .card-body {
            padding: 20px;
        }

        /* ========== SUMMARY BOX ========== */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .summary-box {
            background: #fafafa;
            padding: 15px 20px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            text-align: center;
        }

        .summary-box h4 {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .summary-box .value {
            font-size: 32px;
            font-weight: 700;
            color: #1e293b;
        }

        .summary-box .grade-status {
            font-size: 18px;
            font-weight: 700;
            margin-top: 5px;
        }

        .grade-A { color: #16a34a; }
        .grade-B { color: #2563eb; }
        .grade-C { color: #f59e0b; }
        .grade-D { color: #f97316; }
        .grade-F { color: #ef4444; }

        /* ========== TABLE ========== */
        .performance-table {
            width: 100%;
            border-collapse: collapse;
        }

        .performance-table th {
            text-align: left;
            padding: 12px 15px;
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
            font-size: 12px;
            border-bottom: 1px solid #e2e8f0;
        }

        .performance-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 13px;
            color: #334155;
        }

        .performance-table tr:hover td {
            background: #fefce8;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
        }

        .status-excellent {
            background: #dcfce7;
            color: #166534;
        }

        .status-good {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-average {
            background: #fef3c7;
            color: #92400e;
        }

        .status-needs {
            background: #fee2e2;
            color: #991b1b;
        }

        .progress-bar {
            width: 100%;
            background: #e2e8f0;
            border-radius: 10px;
            height: 8px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        .fill-high { background: #16a34a; }
        .fill-medium { background: #f59e0b; }
        .fill-low { background: #ef4444; }

        /* ========== EMPTY STATE ========== */
        .empty-state {
            text-align: center;
            padding: 50px;
            color: #94a3b8;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            display: block;
        }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
            .summary-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<?php include 'student_sidebar.php'; ?>

<div class="main-content">

    <!-- Page Header -->
    <div class="page-header">
        <h2>
            <i class="fas fa-chart-pie"></i>
            My Performance
        </h2>
    </div>

    <!-- Student Info Bar -->
    <div class="info-bar">
        <span><i class="fas fa-user-graduate"></i> <?= htmlspecialchars($student_name) ?></span>
        <span><i class="fas fa-school"></i> <?= htmlspecialchars($class_name) ?></span>
        <span><i class="fas fa-calendar-alt"></i> <?= date('Y') ?> Academic Year</span>
    </div>

    <!-- Summary Cards -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-chart-simple"></i> Performance Summary</h3>
        </div>
        <div class="card-body">
            <div class="summary-grid">
                <div class="summary-box">
                    <h4><i class="fas fa-percent"></i> Overall Average</h4>
                    <div class="value"><?= round($avg, 1) ?>%</div>
                </div>
                <div class="summary-box">
                    <h4><i class="fas fa-star"></i> Overall Grade</h4>
                    <div class="grade-status <?= 'grade-' . (function($avg) {
                        if ($avg >= 75) return 'A';
                        elseif ($avg >= 65) return 'B';
                        elseif ($avg >= 45) return 'C';
                        elseif ($avg >= 30) return 'D';
                        else return 'F';
                    })($avg) ?>">
                        <?php
                        if ($avg >= 75) echo 'A (Excellent)';
                        elseif ($avg >= 65) echo 'B (Good)';
                        elseif ($avg >= 45) echo 'C (Average)';
                        elseif ($avg >= 30) echo 'D (Below Average)';
                        else echo 'F (Needs Improvement)';
                        ?>
                    </div>
                </div>
                <div class="summary-box">
                    <h4><i class="fas fa-trophy"></i> Best Subject</h4>
                    <div class="value" style="font-size: 18px;">
                        <?php if (!empty($subjects) && !empty($marks)): ?>
                            <?= htmlspecialchars($subjects[array_search(max($marks), $marks)]) ?>
                            <span style="font-size: 12px; color: #f59e0b;">(<?= max($marks) ?>%)</span>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </div>
                </div>
                <div class="summary-box">
                    <h4><i class="fas fa-chart-line"></i> Subjects Taken</h4>
                    <div class="value"><?= count($subjects) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Subject Performance Table -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-book-open"></i> Subject-wise Performance</h3>
        </div>
        <div class="card-body">
            
            <?php if (mysqli_num_rows($subject_query) > 0): ?>

                <div style="overflow-x: auto;">
                    <table class="performance-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Subject</th>
                                <th>Average Marks</th>
                                <th>Progress</th>
                                <th>Status</th>
                            </thead>
                            <tbody>
                                <?php $i = 1; while($row = mysqli_fetch_assoc($subject_query)): 
                                    $mark = round($row['avg_mark'], 1);

                                    if ($mark >= 75) {
                                        $status = "Excellent";
                                        $status_class = "status-excellent";
                                        $fill_class = "fill-high";
                                    } elseif ($mark >= 60) {
                                        $status = "Good";
                                        $status_class = "status-good";
                                        $fill_class = "fill-high";
                                    } elseif ($mark >= 45) {
                                        $status = "Average";
                                        $status_class = "status-average";
                                        $fill_class = "fill-medium";
                                    } else {
                                        $status = "Needs Improvement";
                                        $status_class = "status-needs";
                                        $fill_class = "fill-low";
                                    }
                                ?>
                                    <tr>
                                        <td style="width: 50px;"><?= $i++ ?></td>
                                        <td><strong><?= htmlspecialchars($row['subject_name']) ?></strong></td>
                                        <td style="width: 100px;"><?= $mark ?>%</td>
                                        <td>
                                            <div class="progress-bar">
                                                <div class="progress-fill <?= $fill_class ?>" style="width: <?= $mark ?>%;"></div>
                                            </div>
                                        </td>
                                        <td style="width: 140px;">
                                            <span class="status-badge <?= $status_class ?>"><?= $status ?></span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                <?php else: ?>

                    <div class="empty-state">
                        <i class="fas fa-folder-open"></i>
                        <p>No published marks yet.</p>
                        <small>Your results will appear here once teachers publish them.</small>
                    </div>

                <?php endif; ?>

            </div>
        </div>

    </div>
<?php include '../footer.php'; ?>
    </body>
    </html>