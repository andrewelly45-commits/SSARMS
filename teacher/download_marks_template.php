<?php
session_start();
include '../db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

/* ================= AUTH CHECK ================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher') {
    header("Location: ../auth/login.php");
    exit();
}

/* ================= GET POST DATA ================= */
$class_id = isset($_POST['class_id']) ? (int)$_POST['class_id'] : 0;
$subject_id = isset($_POST['subject_id']) ? (int)$_POST['subject_id'] : 0;
$term = isset($_POST['term']) ? mysqli_real_escape_string($conn, $_POST['term']) : '';
$academic_year = isset($_POST['academic_year']) ? mysqli_real_escape_string($conn, $_POST['academic_year']) : date('Y');

if (!$class_id || !$subject_id || empty($term)) {
    die("Missing required parameters. Please go back and try again.");
}

/* ================= GET CLASS & SUBJECT DETAILS ================= */
$class_query = mysqli_query($conn, "SELECT class_name FROM class WHERE class_id='$class_id'");
$class = mysqli_fetch_assoc($class_query);
$class_name = $class['class_name'] ?? 'Class';

$subject_query = mysqli_query($conn, "SELECT subject_name FROM subject WHERE subject_id='$subject_id'");
$subject = mysqli_fetch_assoc($subject_query);
$subject_name = $subject['subject_name'] ?? 'Subject';

/* ================= GET STUDENTS ================= */
$students_query = mysqli_query($conn,
    "SELECT 
        s.student_id,
        s.registration_no,
        u.full_name
     FROM student s
     INNER JOIN users u ON s.user_id = u.user_id
     WHERE s.class_id='$class_id'
     ORDER BY u.full_name ASC"
);

if (mysqli_num_rows($students_query) == 0) {
    die("No students found in this class.");
}

/* ================= GET EXISTING MARKS ================= */
$existing_marks = [];
$marks_query = mysqli_query($conn,
    "SELECT student_id, marks 
     FROM marks 
     WHERE class_id='$class_id' 
     AND subject_id='$subject_id' 
     AND term='$term' 
     AND academic_year='$academic_year'"
);

while ($row = mysqli_fetch_assoc($marks_query)) {
    $existing_marks[$row['student_id']] = $row['marks'];
}

// ===== GENERATE CSV =====
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="marks_template_' . str_replace(' ', '_', $class_name) . '_' . str_replace(' ', '_', $subject_name) . '_' . $term . '_' . $academic_year . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// ===== HEADER WITH REGISTRATION NO AS READ-ONLY =====
$escape_char = '\\';
fputcsv($output, ['Registration No', 'Student Name', 'Marks'], ',', '"', $escape_char);

// ===== STUDENT DATA =====
while ($student = mysqli_fetch_assoc($students_query)) {
    $student_id = $student['student_id'];
    $marks = isset($existing_marks[$student_id]) ? $existing_marks[$student_id] : '';
    
    // Registration number is included but will be read-only when opened
    fputcsv($output, [
        $student['registration_no'],
        $student['full_name'],
        $marks
    ], ',', '"', $escape_char);
}

fclose($output);
exit();
?>