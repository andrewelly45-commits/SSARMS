<?php
session_start();
include '../db.php';

// In your import_marks.php after successful import
include '../auth/audit_functions.php';

// After successful upload
logSystemAction(
    $_SESSION['user_id'],
    $_SESSION['role'],
    $_SESSION['full_name'],
    'upload',
    "Uploaded $success_count marks for class ID: $class_id, subject ID: $subject_id, term: $term, year: $academic_year",
    'marks',
    'marks',
    $class_id,
    null,
    ['count' => $success_count, 'subject_id' => $subject_id, 'term' => $term]
);

/* ================= AUTH CHECK ================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher') {
    header("Location: ../auth/login.php");
    exit();
}

/* ================= GET POST DATA ================= */
$class_id = isset($_POST['class_id']) ? (int)$_POST['class_id'] : 0;
$subject_id = isset($_POST['subject_id']) ? (int)$_POST['subject_id'] : 0;
$term = isset($_POST['term']) ? mysqli_real_escape_string($conn, $_POST['term']) : '';
$academic_year = isset($_POST['academic_year']) ? mysqli_real_escape_string($conn, $_POST['academic_year']) : '';

// Validate required fields
if (!$class_id) {
    $_SESSION['error_msg'] = "Class ID is required.";
    header("Location: enter_marks.php");
    exit();
}

if (!$subject_id) {
    $_SESSION['error_msg'] = "Subject is required. Please select a subject.";
    header("Location: enter_marks.php?class_id=" . $class_id);
    exit();
}

if (empty($term)) {
    $_SESSION['error_msg'] = "Term is required. Please select a term.";
    header("Location: enter_marks.php?class_id=" . $class_id);
    exit();
}

if (empty($academic_year)) {
    $_SESSION['error_msg'] = "Academic year is required.";
    header("Location: enter_marks.php?class_id=" . $class_id);
    exit();
}

/* ================= CHECK IF FILE UPLOADED ================= */
if (!isset($_FILES['marks_file']) || $_FILES['marks_file']['error'] != 0) {
    $_SESSION['error_msg'] = "Please select a valid Excel/CSV file to upload.";
    header("Location: enter_marks.php?class_id=" . $class_id);
    exit();
}

/* ================= GET TEACHER ================= */
$user_id = $_SESSION['user_id'];
$teacher_query = mysqli_query($conn, "SELECT teacher_id FROM teacher WHERE user_id='$user_id'");
$teacher = mysqli_fetch_assoc($teacher_query);

if (!$teacher) {
    $_SESSION['error_msg'] = "Teacher not found.";
    header("Location: enter_marks.php?class_id=" . $class_id);
    exit();
}

$teacher_id = $teacher['teacher_id'];

/* ================= PROCESS UPLOADED FILE ================= */
$file_tmp = $_FILES['marks_file']['tmp_name'];
$file_name = $_FILES['marks_file']['name'];
$file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

// Validate file extension
if (!in_array($file_ext, ['csv', 'xls', 'xlsx'])) {
    $_SESSION['error_msg'] = "Invalid file format. Please upload CSV, XLS, or XLSX files only.";
    header("Location: enter_marks.php?class_id=" . $class_id);
    exit();
}

/* ================= GET ALL STUDENTS IN CLASS ================= */
$students_map = [];
$students_regnos = [];
$students_query = mysqli_query($conn,
    "SELECT 
        s.student_id,
        s.registration_no,
        u.full_name
     FROM student s
     INNER JOIN users u ON s.user_id = u.user_id
     WHERE s.class_id='$class_id'"
);

while ($student = mysqli_fetch_assoc($students_query)) {
    $students_map[$student['registration_no']] = [
        'student_id' => $student['student_id'],
        'name' => $student['full_name'],
        'reg_no' => $student['registration_no']
    ];
    $students_regnos[] = $student['registration_no'];
}

/* ================= READ FILE DATA ================= */
$data = [];
$errors = [];
$row_number = 2; // Start from row 2 (after headers)

if ($file_ext == 'csv') {
    // Read CSV file
    if (($handle = fopen($file_tmp, 'r')) !== false) {
        // Skip BOM if present
        $bom = fread($handle, 3);
        if ($bom != "\xEF\xBB\xBF") {
            rewind($handle);
        }
        
        // Read headers - SKIP THE FIRST ROW (headers)
        $headers = fgetcsv($handle, 0, ',', '"', '\\');
        
        // Read data rows - START FROM ROW 2
        while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            // Clean the row data with null checking
            $row = array_map(function($value) {
                return $value !== null ? trim($value) : '';
            }, $row);
            
            // Skip empty rows
            if (empty($row[0]) && empty($row[1]) && empty($row[2])) {
                $row_number++;
                continue;
            }
            
            // Check if we have at least 3 columns
            if (count($row) >= 3) {
                $data[] = [
                    'row_number' => $row_number,
                    'reg_no' => isset($row[0]) ? $row[0] : '',
                    'name' => isset($row[1]) ? $row[1] : '',
                    'marks' => isset($row[2]) ? $row[2] : ''
                ];
            } else {
                $errors[] = "Row $row_number: Invalid format - expected 3 columns (Registration No, Name, Marks)";
            }
            $row_number++;
        }
        fclose($handle);
    }
} else {
    // For Excel files (XLS/XLSX) - Need PhpSpreadsheet
    if (!file_exists('../vendor/autoload.php')) {
        $_SESSION['error_msg'] = "PhpSpreadsheet library not installed. Please install via Composer or use CSV format.";
        header("Location: enter_marks.php?class_id=" . $class_id);
        exit();
    }
    
    require_once '../vendor/autoload.php';
    
    try {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_tmp);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        // Skip header row (row 0) and start from row 1
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            
            // Clean the row data with null checking
            $row = array_map(function($value) {
                return $value !== null ? trim($value) : '';
            }, $row);
            
            // Skip empty rows
            if (empty($row[0]) && empty($row[1]) && empty($row[2])) {
                continue;
            }
            
            // Check if we have at least 3 columns
            if (count($row) >= 3 && !empty($row[0])) {
                $data[] = [
                    'row_number' => $i + 1, // +1 because array starts at 0
                    'reg_no' => isset($row[0]) ? $row[0] : '',
                    'name' => isset($row[1]) ? $row[1] : '',
                    'marks' => isset($row[2]) ? $row[2] : ''
                ];
            }
        }
    } catch (Exception $e) {
        $_SESSION['error_msg'] = "Error reading Excel file: " . $e->getMessage();
        header("Location: enter_marks.php?class_id=" . $class_id);
        exit();
    }
}

if (empty($data)) {
    $_SESSION['error_msg'] = "No data found in the file. Please check the file format.";
    header("Location: enter_marks.php?class_id=" . $class_id);
    exit();
}

/* ================= VALIDATE ALL DATA FIRST ================= */
$validation_passed = true;
$processed_data = [];

foreach ($data as $row_data) {
    $row_num = $row_data['row_number'];
    $reg_no = isset($row_data['reg_no']) ? trim($row_data['reg_no']) : '';
    $student_name = isset($row_data['name']) ? trim($row_data['name']) : '';
    $marks = isset($row_data['marks']) ? trim($row_data['marks']) : '';
    
    // Check 1: Registration number is required
    if (empty($reg_no)) {
        $errors[] = "Row $row_num: Missing registration number. Please provide registration number.";
        $validation_passed = false;
        continue;
    }
    
    // Check 2: Student must exist in the class
    if (!isset($students_map[$reg_no])) {
        // Try to find by name as fallback
        $found = false;
        foreach ($students_map as $reg => $student) {
            if (strtolower(trim($student['name'])) == strtolower($student_name)) {
                $reg_no = $reg;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $errors[] = "Row $row_num: Student with registration number '$reg_no' (Name: '$student_name') not found in this class.";
            $validation_passed = false;
            continue;
        }
    }
    
    // Check 3: Marks is required
    if (empty($marks) && $marks !== '0') {
        $errors[] = "Row $row_num: Missing marks for student '$reg_no'. Please provide marks.";
        $validation_passed = false;
        continue;
    }
    
    // Check 4: Marks must be numeric
    if (!is_numeric($marks)) {
        $errors[] = "Row $row_num: Invalid marks format '$marks' for student '$reg_no'. Marks must be numeric.";
        $validation_passed = false;
        continue;
    }
    
    // Check 5: Marks must be between 0 and 100 (NOT greater than 100)
    if ($marks < 0 || $marks > 100) {
        $errors[] = "Row $row_num: Invalid marks value '$marks' for student '$reg_no'. Marks must be between 0 and 100.";
        $validation_passed = false;
        continue;
    }
    
    // If all validations pass, add to processed data
    $processed_data[] = [
        'student_id' => $students_map[$reg_no]['student_id'],
        'reg_no' => $reg_no,
        'marks' => (float)$marks
    ];
}

/* ================= IF ANY ERROR, DON'T PROCESS ANYTHING ================= */
if (!$validation_passed || !empty($errors)) {
    // Store errors in session
    $_SESSION['error_msg'] = "File validation failed. Please fix the following errors:<br>" . implode('<br>', $errors);
    $_SESSION['error_details'] = $errors;
    $_SESSION['import_success'] = false;
    
    // Redirect back without processing anything
    header("Location: enter_marks.php?class_id=" . $class_id);
    exit();
}

/* ================= ALL VALIDATIONS PASSED - PROCESS MARKS ================= */
$success_count = 0;

foreach ($processed_data as $row_data) {
    $student_id = $row_data['student_id'];
    $marks = $row_data['marks'];
    
    // Check if mark exists
    $check = mysqli_query($conn,
        "SELECT mark_id 
         FROM marks 
         WHERE student_id='$student_id' 
         AND subject_id='$subject_id' 
         AND term='$term' 
         AND academic_year='$academic_year'"
    );
    
    if (mysqli_num_rows($check) > 0) {
        // Update existing mark
        mysqli_query($conn,
            "UPDATE marks 
             SET marks='$marks',
                 status='pending',
                 updated_at=NOW()
             WHERE student_id='$student_id' 
             AND subject_id='$subject_id' 
             AND term='$term' 
             AND academic_year='$academic_year'"
        );
        $success_count++;
    } else {
        // Insert new mark
        mysqli_query($conn,
            "INSERT INTO marks 
            (student_id, subject_id, class_id, teacher_id, marks, term, academic_year, status, created_at)
            VALUES 
            ('$student_id', '$subject_id', '$class_id', '$teacher_id', '$marks', '$term', '$academic_year', 'pending', NOW())"
        );
        $success_count++;
    }
}

/* ================= SESSION MESSAGES ================= */
$_SESSION['success_msg'] = "Successfully imported $success_count marks for $term, $academic_year!";
$_SESSION['import_success'] = true;

// Redirect back
header("Location: enter_marks.php?class_id=" . $class_id);
exit();
?>