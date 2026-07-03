<?php
session_start();
include '../db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

/* ================= AUTH CHECK ================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'academic') {
    die("Unauthorized access");
}

/* ================= FUNCTIONS ================= */

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

/* ================= SAVE RESULT ================= */
function saveResult($conn, $student_id, $class_id, $term, $year, $res) {

    $check = mysqli_query($conn, "
        SELECT result_id
        FROM student_results
        WHERE student_id='$student_id'
        AND class_id='$class_id'
        AND term='$term'
        AND academic_year='$year'
    ");

    if (mysqli_num_rows($check) > 0) {

        mysqli_query($conn, "
            UPDATE student_results SET
                total_marks='{$res['total_marks']}',
                total_points='{$res['total_points']}',
                average='{$res['average']}',
                division='{$res['division']}'
            WHERE student_id='$student_id'
            AND class_id='$class_id'
            AND term='$term'
            AND academic_year='$year'
        ");

    } else {

        mysqli_query($conn, "
            INSERT INTO student_results
            (
                student_id,
                class_id,
                term,
                academic_year,
                total_marks,
                total_points,
                average,
                division
            )
            VALUES
            (
                '$student_id',
                '$class_id',
                '$term',
                '$year',
                '{$res['total_marks']}',
                '{$res['total_points']}',
                '{$res['average']}',
                '{$res['division']}'
            )
        ");
    }
}

/* ================= GET ALL STUDENTS ================= */
$students = mysqli_query($conn, "
    SELECT student_id, class_id, academic_year
    FROM student
");

if (!$students) {
    die("Error fetching students");
}

$processed = 0;

/* ================= PROCESS EACH STUDENT ================= */
while ($st = mysqli_fetch_assoc($students)) {

    $student_id = $st['student_id'];
    $class_id   = $st['class_id'];
    $year       = $st['academic_year'];

    /* ================= GET MARKS FUNCTION ================= */
    $getMarks = function($term) use ($conn, $student_id, $year) {

        $q = mysqli_query($conn, "
            SELECT m.subject_id,
                   sub.subject_name,
                   m.marks
            FROM marks m
            JOIN subject sub
                ON m.subject_id = sub.subject_id
            WHERE m.student_id = '$student_id'
            AND m.term = '$term'
            AND m.academic_year = '$year'
            AND m.status = 'published'
        ");

        $data = [];

        while ($r = mysqli_fetch_assoc($q)) {
            $data[] = $r;
        }

        return $data;
    };

    /* ================= CALCULATE FUNCTION ================= */
$calculate = function($marks) {

    $list = array_values($marks);
    $count = count($list);

    // ❗ INCOMPLETE CONDITION
    if ($count < 7) {
        return [
            'total_marks' => 0,
            'total_points' => 0,
            'average' => 0,
            'division' => 'INC',
            'count' => $count
        ];
    }

    usort($list, fn($a,$b) => $b['marks'] <=> $a['marks']);
    $top7 = array_slice($list, 0, 7);

    $total_marks = 0;
    $total_points = 0;

    foreach ($top7 as $r) {
        $grade = getGrade($r['marks']);
        $points = getPoints($grade);

        $total_marks += $r['marks'];
        $total_points += $points;
    }

    $avg = $total_marks / 7;
    $division = getDivision($total_points);

    return [
        'total_marks' => $total_marks,
        'total_points' => $total_points,
        'average' => $avg,
        'division' => $division,
        'count' => $count
    ];
};

    /* ================= TERM 1 ================= */
    $marks1 = $getMarks('Term 1');

    if (!empty($marks1)) {
        $res1 = $calculate($marks1);
        saveResult(
            $conn,
            $student_id,
            $class_id,
            'Term 1',
            $year,
            $res1
        );
    }

    /* ================= TERM 2 ================= */
    $marks2 = $getMarks('Term 2');

    if (!empty($marks2)) {
        $res2 = $calculate($marks2);
        saveResult(
            $conn,
            $student_id,
            $class_id,
            'Term 2',
            $year,
            $res2
        );
    }

    $processed++;
}

echo "
<div style='padding:20px;font-family:Arial'>
    <h2 style='color:green'>
        Results processed successfully!
    </h2>
    <p>Total students processed: <b>$processed</b></p>
</div>
";
?>