<?php
session_start();
include '../db.php';

// ============================================
// INCLUDE AUDIT LOGGER - ONLY ONCE
// ============================================
$audit_paths = [
    '../audit_logger.php',
    'audit_logger.php',
    '../includes/audit_logger.php',
    '../../audit_logger.php',
    dirname(__DIR__) . '/audit_logger.php'
];

$audit_loaded = false;
foreach ($audit_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $audit_loaded = true;
        break;
    }
}

if (!$audit_loaded && !function_exists('logAction')) {
    function logAction($action_type, $module, $description, $status = 'success', $affected_id = null, $affected_table = null, $old_values = null, $new_values = null) {
        global $conn;
        if ($conn) {
            $query = "INSERT INTO audit_logs (user_id, user_name, user_role, action_type, module, action_description, status, ip_address, user_agent) 
                      VALUES (
                          NULL, 
                          'System Fallback', 
                          'system', 
                          '$action_type', 
                          '$module', 
                          '$description', 
                          '$status',
                          '" . ($_SERVER['REMOTE_ADDR'] ?? '') . "',
                          '" . ($_SERVER['HTTP_USER_AGENT'] ?? '') . "'
                      )";
            mysqli_query($conn, $query);
        }
        return true;
    }
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ============================================
// AUTH CHECK
// ============================================
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    logAction('access_denied', 'results', "Unauthorized access attempt to student results by: " . ($_SESSION['full_name'] ?? 'Unknown'), 'failed');
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ================= STUDENT ================= */
$student_query = mysqli_query($conn, "
    SELECT s.student_id, s.class_id, u.full_name, c.class_name
    FROM student s
    JOIN users u ON s.user_id = u.user_id
    JOIN class c ON s.class_id = c.class_id
    WHERE s.user_id = '$user_id'
");

$student = mysqli_fetch_assoc($student_query);

if (!$student) {
    logAction('error', 'results', "Student not found for user_id: $user_id", 'failed');
    die("Student not found");
}

$student_id = $student['student_id'];
$class_id = $student['class_id'];

// Get selected term from URL, default to Term 1
$selected_term = $_GET['term'] ?? 'Term 1';
$year = $_GET['year'] ?? date('Y');

/* ================= SUBJECTS ================= */
$class_subject_query = mysqli_query($conn, "
    SELECT sub.subject_id, sub.subject_name
    FROM class_subject cs
    JOIN subject sub ON cs.subject_id = sub.subject_id
    WHERE cs.class_id = '$class_id'
");

$class_subjects = [];
while ($row = mysqli_fetch_assoc($class_subject_query)) {
    $class_subjects[$row['subject_id']] = $row['subject_name'];
}

// Function to get marks for a specific term
function getMarksForTerm($conn, $student_id, $term, $year) {
    $marks_query = mysqli_query($conn, "
        SELECT m.subject_id, sub.subject_name, m.marks
        FROM marks m
        JOIN subject sub ON m.subject_id = sub.subject_id
        WHERE m.student_id = '$student_id'
          AND m.term = '$term'
          AND m.academic_year = '$year'
          AND m.status = 'published'
    ");
    
    $marks = [];
    while ($row = mysqli_fetch_assoc($marks_query)) {
        $marks[$row['subject_id']] = $row;
    }
    return $marks;
}

// Function to calculate top 7 and summary
function calculateResults($student_marks, $class_subjects) {
    $marks_list = array_values($student_marks);
    usort($marks_list, fn($a, $b) => $b['marks'] <=> $a['marks']);

    $top_7 = array_slice($marks_list, 0, 7);

    $total_marks = 0;
    $total_points = 0;
    $top_subjects = [];

    foreach ($top_7 as $r) {
        $g = getGrade($r['marks']);
        $p = getPoints($g);

        $top_subjects[] = [
            'subject_name' => $r['subject_name'],
            'marks' => $r['marks'],
            'grade' => $g,
            'points' => $p
        ];

        $total_marks += $r['marks'];
        $total_points += $p;
    }

    $subject_count = count($top_subjects);
    $average = $subject_count ? $total_marks / $subject_count : 0;

    if ($subject_count < 7) {
        $division = 'INC';
    } else {
        $division = getDivision($total_points);
    }

    return [
        'top_subjects' => $top_subjects,
        'total_marks' => $total_marks,
        'total_points' => $total_points,
        'average' => $average,
        'division' => $division,
        'count' => $subject_count
    ];
}

function getGrade($m) {
    if ($m >= 75) return 'A';
    elseif ($m >= 65) return 'B';
    elseif ($m >= 45) return 'C';
    elseif ($m >= 30) return 'D';
    return 'F';
}

function getPoints($g) {
    return match($g) {
        'A' => 1,
        'B' => 2,
        'C' => 3,
        'D' => 4,
        default => 5
    };
}

function getDivision($p) {
    if ($p >= 7 && $p <= 17) return 'Division I';
    elseif ($p <= 21) return 'Division II';
    elseif ($p <= 25) return 'Division III';
    elseif ($p <= 33) return 'Division IV';
    return 'FAIL';
}

// Get marks for both terms
$marks_term1 = getMarksForTerm($conn, $student_id, 'Term 1', $year);
$results_term1 = calculateResults($marks_term1, $class_subjects);

$marks_term2 = getMarksForTerm($conn, $student_id, 'Term 2', $year);
$results_term2 = calculateResults($marks_term2, $class_subjects);

// Set current results based on selected term
if ($selected_term == 'Term 1') {
    $current_marks = $marks_term1;
    $current_results = $results_term1;
} else {
    $current_marks = $marks_term2;
    $current_results = $results_term2;
}

// ============================================
// LOG ONLY IF RESULTS EXIST
// ============================================
$has_results = !empty($current_marks) && count($current_marks) > 0;

if ($has_results) {
    // Log that student viewed results (only if results exist)
    logAction('view', 'results', "Student viewed $selected_term results for year $year: " . $student['full_name'] . " (Class: " . $student['class_name'] . ")", 'success', $student_id, 'students');
} else {
    // Log that student checked but no results found (less important, but still track)
    logAction('view', 'results', "Student checked $selected_term results for year $year - No results found: " . $student['full_name'], 'info', $student_id, 'students');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Results</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
        }

        .main-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            margin-left: 270px;
            margin-top: 85px;
            padding: 30px;
            transition: all 0.3s ease;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .page-header h1 {
            color: #1e293b;
            font-size: 28px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-header h1 i {
            color: #2563eb;
        }

        .student-info {
            background: white;
            padding: 8px 20px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            overflow: hidden;
        }

        .card-header {
            padding: 18px 24px;
            border-bottom: 1px solid #eef2f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .card-header h2 {
            font-size: 18px;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-body {
            padding: 24px;
        }

        .term-selector {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .term-btn {
            padding: 8px 20px;
            border-radius: 30px;
            border: 2px solid #e2e8f0;
            background: white;
            color: #64748b;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            font-size: 14px;
        }

        .term-btn:hover {
            border-color: #2563eb;
            color: #2563eb;
        }

        .term-btn.active {
            background: #2563eb;
            color: white;
            border-color: #2563eb;
        }

        .results-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .result-box {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            border: 1px solid #e2e8f0;
        }

        .result-box .label {
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .result-box .value {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin-top: 5px;
        }

        .result-box .value.division {
            color: #2563eb;
        }

        .result-box .value.inc {
            color: #f59e0b;
        }

        .result-box .value.fail {
            color: #dc2626;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f8fafc;
            padding: 12px 14px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            border-bottom: 2px solid #e2e8f0;
        }

        td {
            padding: 12px 14px;
            border-bottom: 1px solid #eef2f6;
            font-size: 14px;
            vertical-align: middle;
        }

        tr:hover td {
            background: #f8fafc;
        }

        .grade-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 13px;
            min-width: 35px;
            text-align: center;
        }

        .grade-A { background: #dcfce7; color: #166534; }
        .grade-B { background: #dbeafe; color: #1d4ed8; }
        .grade-C { background: #fef3c7; color: #92400e; }
        .grade-D { background: #fed7aa; color: #9a3412; }
        .grade-F { background: #fee2e2; color: #991b1b; }

        .no-marks {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
        }

        .no-marks i {
            font-size: 64px;
            display: block;
            margin-bottom: 20px;
            color: #cbd5e1;
        }

        .no-marks h3 {
            color: #1e293b;
            font-size: 20px;
            margin-bottom: 10px;
        }

        .no-marks p {
            font-size: 14px;
        }

        .info-message {
            background: #dbeafe;
            border-left: 4px solid #2563eb;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #1e40af;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .info-message i {
            font-size: 20px;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }

            .results-grid {
                grid-template-columns: 1fr;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 10px;
            }

            .term-selector {
                width: 100%;
            }

            .term-btn {
                flex: 1;
                text-align: center;
            }
        }

        .print-btn {
            background: #2563eb;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .print-btn:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
        }

        @media print {
            .main-wrapper {
                display: block;
            }
            .main-content {
                margin-left: 0;
                margin-top: 0;
                padding: 20px;
            }
            .card {
                box-shadow: none;
                border: 1px solid #e2e8f0;
            }
            .print-btn, .term-selector, .page-header .student-info {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="main-wrapper">
    <?php include 'student_sidebar.php'; ?>
    <?php include '../auth/topbar.php'; ?>

    <div class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>
                    <i class="fas fa-chart-line"></i>
                    My Results
                </h1>
                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <span class="student-info">
                        <i class="fas fa-user"></i> <?= htmlspecialchars($student['full_name'] ?? 'Student') ?>
                    </span>
                    <button class="print-btn" onclick="window.print()">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>

            <!-- Term Selector -->
            <div class="card">
                <div class="card-body">
                    <div class="term-selector">
                        <a href="?term=Term 1&year=<?= $year ?>" 
                           class="term-btn <?= $selected_term == 'Term 1' ? 'active' : '' ?>">
                            <i class="fas fa-calendar"></i> Term 1
                        </a>
                        <a href="?term=Term 2&year=<?= $year ?>" 
                           class="term-btn <?= $selected_term == 'Term 2' ? 'active' : '' ?>">
                            <i class="fas fa-calendar"></i> Term 2
                        </a>
                        <span style="margin-left: auto; color: #64748b; font-size: 14px;">
                            <i class="fas fa-calendar-alt"></i> Year: <?= $year ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Results Summary -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-chart-pie"></i> Results Summary - <?= $selected_term ?></h2>
                    <span style="font-size: 14px; color: #64748b;">
                        <?= $current_results['count'] ?? 0 ?> subjects
                    </span>
                </div>
                <div class="card-body">
                    <?php if(isset($current_results['count']) && $current_results['count'] > 0): ?>
                        <div class="results-grid">
                            <div class="result-box">
                                <div class="label">Division</div>
                                <div class="value division <?= strtolower($current_results['division']) == 'inc' ? 'inc' : (strtolower($current_results['division']) == 'fail' ? 'fail' : '') ?>">
                                    <?= htmlspecialchars($current_results['division']) ?>
                                </div>
                            </div>
                            <div class="result-box">
                                <div class="label">Total Points</div>
                                <div class="value"><?= $current_results['total_points'] ?? 0 ?></div>
                            </div>
                            <div class="result-box">
                                <div class="label">Average Score</div>
                                <div class="value"><?= number_format($current_results['average'] ?? 0, 1) ?>%</div>
                            </div>
                            <div class="result-box">
                                <div class="label">Total Marks</div>
                                <div class="value"><?= $current_results['total_marks'] ?? 0 ?></div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="no-marks">
                            <i class="fas fa-file-alt"></i>
                            <h3>No Results Available</h3>
                            <p>No results found for <?= $selected_term ?> <?= $year ?></p>
                            <p style="font-size: 12px; margin-top: 5px;">Results will appear here once published by your teachers.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Top Subjects Table -->
            <?php if(isset($current_results['top_subjects']) && !empty($current_results['top_subjects'])): ?>
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-star"></i> Top <?= min(7, count($current_results['top_subjects'])) ?> Subjects</h2>
                    <span style="font-size: 14px; color: #64748b;">
                        <i class="fas fa-info-circle"></i> Best performing subjects
                    </span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Subject</th>
                                    <th>Marks</th>
                                    <th>Grade</th>
                                    <th>Points</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rank = 1;
                                foreach($current_results['top_subjects'] as $subject): 
                                ?>
                                    <tr>
                                        <td><?= $rank++ ?></td>
                                        <td><strong><?= htmlspecialchars($subject['subject_name']) ?></strong></td>
                                        <td><?= $subject['marks'] ?>%</td>
                                        <td>
                                            <span class="grade-badge grade-<?= $subject['grade'] ?>">
                                                <?= $subject['grade'] ?>
                                            </span>
                                        </td>
                                        <td><?= $subject['points'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- All Subjects -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-book"></i> All Subjects</h2>
                    <span style="font-size: 14px; color: #64748b;">
                        <?= count($current_marks) ?> subjects
                    </span>
                </div>
                <div class="card-body">
                    <?php if(!empty($current_marks)): ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Subject</th>
                                        <th>Marks</th>
                                        <th>Grade</th>
                                        <th>Points</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $rank = 1;
                                    foreach($current_marks as $mark): 
                                        $grade = getGrade($mark['marks']);
                                        $points = getPoints($grade);
                                    ?>
                                        <tr>
                                            <td><?= $rank++ ?></td>
                                            <td><?= htmlspecialchars($mark['subject_name']) ?></td>
                                            <td><?= $mark['marks'] ?>%</td>
                                            <td>
                                                <span class="grade-badge grade-<?= $grade ?>">
                                                    <?= $grade ?>
                                                </span>
                                            </td>
                                            <td><?= $points ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="no-marks">
                            <i class="fas fa-book"></i>
                            <h3>No Subjects Available</h3>
                            <p>No subjects found for <?= $selected_term ?> <?= $year ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Legend -->
            <div class="card" style="background: #f8fafc;">
                <div class="card-body" style="padding: 15px 24px;">
                    <div style="display: flex; gap: 20px; flex-wrap: wrap; font-size: 13px;">
                        <span><strong>Grade Scale:</strong></span>
                        <span><span class="grade-badge grade-A">A</span> 75-100%</span>
                        <span><span class="grade-badge grade-B">B</span> 65-74%</span>
                        <span><span class="grade-badge grade-C">C</span> 45-64%</span>
                        <span><span class="grade-badge grade-D">D</span> 30-44%</span>
                        <span><span class="grade-badge grade-F">F</span> 0-29%</span>
                        <span style="margin-left: auto; color: #64748b;">
                            <i class="fas fa-info-circle"></i> Points: A=1, B=2, C=3, D=4, F=5
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
</body>
</html>