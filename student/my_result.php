<?php
session_start();
include '../db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
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

    // ✅ IMPORTANT RULE: LESS THAN 7 = INC
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

// Get marks for Term 1
$marks_term1 = getMarksForTerm($conn, $student_id, 'Term 1', $year);
$results_term1 = calculateResults($marks_term1, $class_subjects);

// Get marks for Term 2
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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Results | SSARMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
      

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f0f2f5;
        }

        .main-content {
            margin-left: 270px;
            padding: 25px 30px;
            min-height: 100vh;
        }

        .page-header {
            margin-bottom: 25px;
        }

        .page-header h2 {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page-header h2 i {
            color: #f59e0b;
        }

        .inc {
          color: #f59e0b;
           font-weight: 800;
        }

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

        /* Term Tabs */
        .term-tabs {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .term-btn {
            padding: 10px 28px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: 0.3s;
            border: none;
            background: #f1f5f9;
            color: #475569;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .term-btn i {
            font-size: 14px;
        }

        .term-btn.active {
            background: #074591;
            color: white;
        }

        .term-btn.active i {
            color: white;
        }

        .term-btn:hover:not(.active) {
            background: #e2e8f0;
        }

        /* Card */
        .card {
            background: white;
            border-radius: 12px;
            margin-bottom: 20px;
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
            font-weight: 600;
            color: #1e293b;
        }

        .card-header h3 i {
            color: #f59e0b;
            margin-right: 8px;
        }

        .card-body {
            padding: 20px;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 12px 15px;
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
            font-size: 13px;
            border-bottom: 1px solid #e2e8f0;
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
            color: #334155;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background: #fafafa;
        }

        /* Grade styling */
        .grade {
            font-weight: 700;
            font-size: 14px;
        }
        .grade.A { color: #16a34a; }
        .grade.B { color: #2563eb; }
        .grade.C { color: #f59e0b; }
        .grade.D { color: #f97316; }
        .grade.F { color: #ef4444; }

        .marks {
            font-weight: 500;
        }
        .marks.high { color: #16a34a; }
        .marks.medium { color: #f59e0b; }
        .marks.low { color: #ef4444; }

        /* Summary Grid */
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
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
        }

        .summary-box .division {
            font-size: 22px;
            font-weight: 800;
            color: #f59e0b;
        }

        .top-badge {
            display: inline-block;
            background: #fef3c7;
            color: #f59e0b;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 20px;
            margin-left: 8px;
        }

        .notice {
            background: #fef3c7;
            border-left: 3px solid #f59e0b;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #92400e;
        }

        .term-badge {
            display: inline-block;
            background: #e0e7ff;
            color: #3730a3;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 10px;
        }

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

<?php include '../auth/topbar.php'; ?>

<div class="main-content">

    <div class="page-header">
        <h2>
            <i class="fas fa-chart-simple"></i>
            My Results
        </h2>
    </div>

    <div class="info-bar">
        <span><i class="fas fa-user"></i> <?= htmlspecialchars($student['full_name'] ?? 'N/A') ?></span>
        <span><i class="fas fa-school"></i> Class: <?= htmlspecialchars($student['class_name'] ?? 'N/A') ?></span>
        <span><i class="fas fa-calendar-alt"></i> Year: <?= htmlspecialchars($year) ?></span>
    </div>

    <!-- Term Selection Tabs -->
    <div class="term-tabs">
        <a href="?term=Term%201&year=<?= $year ?>" class="term-btn <?= $selected_term == 'Term 1' ? 'active' : '' ?>">
            <i class="fas fa-book"></i> Term 1
            <?php if($results_term1['count'] > 0): ?>
                <span class="term-badge"><?= $results_term1['division'] ?></span>
            <?php endif; ?>
        </a>
        <a href="?term=Term%202&year=<?= $year ?>" class="term-btn <?= $selected_term == 'Term 2' ? 'active' : '' ?>">
            <i class="fas fa-book-open"></i> Term 2
            <?php if($results_term2['count'] > 0): ?>
                <span class="term-badge"><?= $results_term2['division'] ?></span>
            <?php endif; ?>
        </a>
    </div>

    <!-- Notice if less than 7 subjects -->
   <?php if ($current_results['division'] == 'INC'): ?>
    <div class="notice">
        <i class="fas fa-info-circle"></i>
        <strong>INCOMPLETE:</strong> Student has only <?= count($current_marks) ?> subjects. Minimum 7 required.
    </div>
<?php endif; ?>

    <!-- All Subjects Table -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-book-open"></i> Subject Performance - <?= $selected_term ?></h3>
        </div>
        <div class="card-body">
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Subject</th>
                            <th>Marks (%)</th>
                            <th>Grade</th>
                            <th>Points</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $counter = 1;
                        $top_subject_names = array_column($current_results['top_subjects'], 'subject_name');
                        
                        foreach ($class_subjects as $id => $name):
                            $mark = isset($current_marks[$id]) ? $current_marks[$id]['marks'] : null;
                            $grade = $mark !== null ? getGrade($mark) : '-';
                            $points = $grade !== '-' ? getPoints($grade) : '-';
                            $is_top_7 = in_array($name, $top_subject_names);
                            
                            $marks_class = '';
                            if ($mark !== null) {
                                if ($mark >= 75) $marks_class = 'high';
                                elseif ($mark >= 45) $marks_class = 'medium';
                                else $marks_class = 'low';
                            }
                        ?>
                            <tr>
                                <td><?= $counter++ ?></td>
                                <td>
                                    <?= htmlspecialchars($name) ?>
                                    <?php if ($is_top_7 && $mark !== null): ?>
                                        <span class="top-badge"><i class="fas fa-star"></i> TOP 7</span>
                                    <?php endif; ?>
                                </td>
                                <td class="marks <?= $marks_class ?>">
                                    <?= $mark !== null ? $mark . '%' : '—' ?>
                                </td>
                                <td>
                                    <?php if ($grade !== '-'): ?>
                                        <span class="grade <?= $grade ?>"><?= $grade ?></span>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td><?= $points !== '-' ? $points : '—' ?></td>
                                <td>
                                    <?php if ($mark !== null): ?>
                                        <span style="color:#16a34a;"><i class="fas fa-check-circle"></i> Published</span>
                                    <?php else: ?>
                                        <span style="color:#94a3b8;"><i class="fas fa-clock"></i> Pending</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Top 7 Subjects Summary -->
    <?php if (!empty($current_results['top_subjects'])): ?>
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-trophy"></i> Best 7 Subjects (Used for Division) - <?= $selected_term ?></h3>
        </div>
        <div class="card-body">
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Subject</th>
                            <th>Marks (%)</th>
                            <th>Grade</th>
                            <th>Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $rank = 1; foreach ($current_results['top_subjects'] as $row): ?>
                            <tr>
                                <td><?= $rank++ ?></td>
                                <td><strong><?= htmlspecialchars($row['subject_name']) ?></strong></td>
                                <td class="marks high"><?= $row['marks'] ?>%</td>
                                <td><span class="grade <?= $row['grade'] ?>"><?= $row['grade'] ?></span></td>
                                <td><strong><?= $row['points'] ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Summary / Division -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-chart-pie"></i> Performance Summary - <?= $selected_term ?></h3>
        </div>
        <div class="card-body">
            <div class="summary-grid">
                <div class="summary-box">
                    <h4><i class="fas fa-percent"></i> Average Score</h4>
                    <div class="value"><?= number_format($current_results['average'], 1) ?>%</div>
                </div>
                <div class="summary-box">
                    <h4><i class="fas fa-calculator"></i> Total Points</h4>
                    <div class="value"><?= $current_results['total_points'] ?></div>
                </div>
                <div class="summary-box">
                    <h4><i class="fas fa-medal"></i> Division</h4>
                    <div class="division"><?= $current_results['division'] ?></div>
                </div>
                <div class="summary-box">
                    <h4><i class="fas fa-star"></i> Best Subject</h4>
                    <div class="value" style="font-size: 16px;">
                        <?php if (!empty($current_results['top_subjects'])): ?>
                            <?= htmlspecialchars($current_results['top_subjects'][0]['subject_name']) ?>
                            <span style="font-size: 12px; color: #f59e0b;">(<?= $current_results['top_subjects'][0]['marks'] ?>%)</span>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Comparison: Term 1 vs Term 2 -->
    <?php if ($results_term1['count'] > 0 && $results_term2['count'] > 0): ?>
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-chart-line"></i> Term Comparison</h3>
        </div>
        <div class="card-body">
            <div class="summary-grid">
                <div class="summary-box">
                    <h4><i class="fas fa-percent"></i> Term 1 Average</h4>
                    <div class="value" style="font-size: 24px;"><?= number_format($results_term1['average'], 1) ?>%</div>
                </div>
                <div class="summary-box">
                    <h4><i class="fas fa-percent"></i> Term 2 Average</h4>
                    <div class="value" style="font-size: 24px;"><?= number_format($results_term2['average'], 1) ?>%</div>
                </div>
                <div class="summary-box">
                    <h4><i class="fas fa-chart-simple"></i> Progress</h4>
                    <div class="value" style="font-size: 20px;">
                        <?php 
                        $diff = $results_term2['average'] - $results_term1['average'];
                        if ($diff > 0): ?>
                            <span style="color:#16a34a;"><i class="fas fa-arrow-up"></i> +<?= number_format($diff, 1) ?>%</span>
                        <?php elseif ($diff < 0): ?>
                            <span style="color:#ef4444;"><i class="fas fa-arrow-down"></i> <?= number_format($diff, 1) ?>%</span>
                        <?php else: ?>
                            <span style="color:#64748b;"><i class="fas fa-minus"></i> No change</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php include '../footer.php'; ?>

</body>
</html>