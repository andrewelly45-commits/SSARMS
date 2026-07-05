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

/* ================= GET DATA (SUPPORTS BOTH GET AND POST) ================= */
// Get class_id from GET or POST
$class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
if ($class_id == 0 && isset($_POST['class_id'])) {
    $class_id = (int)$_POST['class_id'];
}

// Get subject_id from GET or POST
$subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;
if ($subject_id == 0 && isset($_POST['subject_id'])) {
    $subject_id = (int)$_POST['subject_id'];
}

// Get term from GET or POST
$term = isset($_GET['term']) ? mysqli_real_escape_string($conn, $_GET['term']) : '';
if (empty($term) && isset($_POST['term'])) {
    $term = mysqli_real_escape_string($conn, $_POST['term']);
}

// Get academic_year from GET or POST
$academic_year = isset($_GET['academic_year']) ? mysqli_real_escape_string($conn, $_GET['academic_year']) : date('Y');
if (empty($academic_year) && isset($_POST['academic_year'])) {
    $academic_year = mysqli_real_escape_string($conn, $_POST['academic_year']);
}

// Debug: Check if we have the required data
if (!$class_id || !$subject_id || empty($term)) {
    die("Missing required parameters. Class ID: $class_id, Subject ID: $subject_id, Term: $term. Please go back and try again.");
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

// ===== CHECK IF PHPSPREADSHEET IS INSTALLED =====
if (!file_exists('../vendor/autoload.php')) {
    // Fallback to CSV if PhpSpreadsheet is not installed
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="marks_template_' . str_replace(' ', '_', $class_name) . '_' . str_replace(' ', '_', $subject_name) . '_' . $term . '_' . $academic_year . '.csv"');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($output, ['Registration No', 'Student Name', 'Marks'], ',', '"', '\\');
    
    while ($student = mysqli_fetch_assoc($students_query)) {
        $student_id = $student['student_id'];
        $marks = isset($existing_marks[$student_id]) ? $existing_marks[$student_id] : '';
        fputcsv($output, [$student['registration_no'], $student['full_name'], $marks], ',', '"', '\\');
    }
    fclose($output);
    exit();
}

// ===== GENERATE XLSX USING PHPSPREADSHEET =====
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

// Create new Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set sheet title
$sheet->setTitle('Marks Template');

// ===== SET HEADERS =====
$headers = ['Registration No', 'Student Name', 'Marks'];
$column = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($column . '1', $header);
    $column++;
}

// ===== STYLE HEADERS =====
$headerStyle = [
    'font' => [
        'bold' => true,
        'size' => 12,
        'color' => ['rgb' => 'FFFFFF']
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '4F81BD']
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000']
        ]
    ]
];
$sheet->getStyle('A1:C1')->applyFromArray($headerStyle);

// ===== ADD STUDENT DATA =====
$row = 2;
while ($student = mysqli_fetch_assoc($students_query)) {
    $student_id = $student['student_id'];
    $marks = isset($existing_marks[$student_id]) ? $existing_marks[$student_id] : '';
    
    $sheet->setCellValue('A' . $row, $student['registration_no']);
    $sheet->setCellValue('B' . $row, $student['full_name']);
    $sheet->setCellValue('C' . $row, $marks);
    
    // Apply borders to data cells
    $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => 'CCCCCC']
            ]
        ]
    ]);
    
    $row++;
}

// ===== SET COLUMN WIDTHS =====
$sheet->getColumnDimension('A')->setWidth(20); // Registration No
$sheet->getColumnDimension('B')->setWidth(35); // Student Name
$sheet->getColumnDimension('C')->setWidth(15); // Marks

// ===== ADD DATA VALIDATION FOR MARKS COLUMN =====
// This restricts marks to numbers between 0 and 100
$validation = $sheet->getDataValidation('C2:C' . ($row - 1));
$validation->setType(DataValidation::TYPE_WHOLE);
$validation->setErrorStyle(DataValidation::STYLE_STOP);
$validation->setAllowBlank(true);
$validation->setShowInputMessage(true);
$validation->setShowErrorMessage(true);
$validation->setErrorTitle('Invalid Marks');
$validation->setError('Marks must be between 0 and 100');
$validation->setPromptTitle('Enter Marks');
$validation->setPrompt('Please enter marks between 0 and 100');
$validation->setFormula1(0);
$validation->setFormula2(100);

// ===== ADD INSTRUCTIONS SHEET =====
$instructionSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Instructions');
$spreadsheet->addSheet($instructionSheet, 1);

$instructionSheet->setCellValue('A1', 'INSTRUCTIONS FOR IMPORTING MARKS');
$instructionSheet->mergeCells('A1:C1');
$instructionSheet->getStyle('A1')->applyFromArray([
    'font' => ['bold' => true, 'size' => 14],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
]);

$instructions = [
    '',
    '1. Enter marks in the "Marks" column (Column C) for each student.',
    '2. DO NOT change the Registration No or Student Name columns.',
    '3. Marks must be numbers between 0 and 100.',
    '4. You can enter decimal marks (e.g., 85.5).',
    '5. Empty marks will be skipped during import.',
    '6. Save the file as .xlsx format after entering marks.',
    '7. Go back to the system and upload this file.',
    '',
    'IMPORTANT:',
    '- Registration numbers are read-only and should not be modified.',
    '- Student names are for reference only.',
    '- Make sure all marks are valid before uploading.'
];

$row = 3;
foreach ($instructions as $instruction) {
    $instructionSheet->setCellValue('A' . $row, $instruction);
    $instructionSheet->mergeCells('A' . $row . ':C' . $row);
    $row++;
}

// Style instructions
$instructionSheet->getColumnDimension('A')->setWidth(60);
$instructionSheet->getStyle('A3:A' . ($row - 1))->applyFromArray([
    'font' => ['size' => 11],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
]);

// ===== LOCK REGISTRATION NO COLUMN =====
// Protect the sheet but allow editing marks column
$sheet->getProtection()->setSheet(true);
$sheet->getProtection()->setPassword('marks123'); // Optional password
$sheet->getStyle('A2:A' . ($row - 1))->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_PROTECTED);
$sheet->getStyle('B2:B' . ($row - 1))->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_PROTECTED);
$sheet->getStyle('C2:C' . ($row - 1))->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);

// ===== SET ACTIVE SHEET =====
$spreadsheet->setActiveSheetIndex(0);

// ===== OUTPUT THE FILE =====
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="marks_template_' . str_replace(' ', '_', $class_name) . '_' . str_replace(' ', '_', $subject_name) . '_' . $term . '_' . $academic_year . '.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?>