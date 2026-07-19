<?php
session_start();
include '../db.php';

// ============================================
// INCLUDE AUDIT LOGGER
// ============================================
if (!function_exists('logAction')) {
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
    
    if (!$audit_loaded) {
        function logAction($action_type, $module, $description, $status = 'success', $affected_id = null, $affected_table = null, $old_values = null, $new_values = null) {
            global $conn;
            error_log("AUDIT FALLBACK: [$action_type] [$module] $description - Status: $status");
            
            if (isset($conn) && $conn) {
                $user_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'System';
                $user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'system';
                $ip = $_SERVER['REMOTE_ADDR'] ?? '';
                $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                
                $query = "INSERT INTO audit_logs (user_name, user_role, action_type, module, action_description, status, ip_address, user_agent, affected_id, affected_table) 
                          VALUES (
                              '" . mysqli_real_escape_string($conn, $user_name) . "',
                              '" . mysqli_real_escape_string($conn, $user_role) . "',
                              '" . mysqli_real_escape_string($conn, $action_type) . "',
                              '" . mysqli_real_escape_string($conn, $module) . "',
                              '" . mysqli_real_escape_string($conn, $description) . "',
                              '" . mysqli_real_escape_string($conn, $status) . "',
                              '" . mysqli_real_escape_string($conn, $ip) . "',
                              '" . mysqli_real_escape_string($conn, $agent) . "',
                              " . ($affected_id ? (int)$affected_id : 'NULL') . ",
                              '" . mysqli_real_escape_string($conn, $affected_table) . "'
                          )";
                mysqli_query($conn, $query);
            }
            return true;
        }
    }
}

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    if (function_exists('logAction')) {
        logAction('access_denied', 'students', "Unauthorized access attempt to manage students by: " . ($_SESSION['full_name'] ?? 'Unknown'), 'failed');
    }
    header("Location: ../auth/login.php");
    exit();
}

// ============================================
// LOG MANAGE STUDENTS PAGE VIEW
// ============================================
if (function_exists('logAction')) {
    logAction('view', 'students', "Admin viewed manage students page", 'success', null, 'students');
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

//  SCHOOL CODE 
$result = mysqli_query($conn, "SELECT school_code FROM school_settings LIMIT 1");
if(mysqli_num_rows($result) == 0) {
    $_SESSION['error_msg'] = "Please configure School Settings before registering students.";
    if (function_exists('logAction')) {
        logAction('error', 'students', "School settings not configured for student registration", 'failed');
    }
    header("Location: school_settings.php");
    exit();
}
$school = mysqli_fetch_assoc($result);
$school_code = $school['school_code'];

//  ADMISSION NUMBER 
function generateAdmissionNo($conn) {
    $year = date("Y");
    $query = mysqli_query($conn, "SELECT admission_no FROM student WHERE admission_no LIKE 'ADM/$year/%' ORDER BY CAST(SUBSTRING_INDEX(admission_no,'/',-1) AS UNSIGNED) DESC LIMIT 1");
    if(mysqli_num_rows($query) > 0) {
        $row = mysqli_fetch_assoc($query);
        $parts = explode("/", $row['admission_no']);
        $number = (int)$parts[2] + 1;
    } else {
        $number = 1;
    }
    while(true) {
        $admission = "ADM/".$year."/".str_pad($number,3,"0",STR_PAD_LEFT);
        $check = mysqli_query($conn, "SELECT student_id FROM student WHERE admission_no='$admission'");
        if(mysqli_num_rows($check) == 0) break;
        $number++;
    }
    return $admission;
}

//  REGISTRATION NUMBER 
function generateRegistrationNo($conn, $class_id, $school_code) {
    $class = mysqli_fetch_assoc(mysqli_query($conn, "SELECT reg_prefix FROM class WHERE class_id='$class_id'"));
    $prefix = $class['reg_prefix'];
    $year = date("y");
    $query = mysqli_query($conn, "SELECT registration_no FROM student WHERE registration_no LIKE '$prefix/$school_code/%/$year' ORDER BY CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(registration_no,'/',3),'/',-1) AS UNSIGNED) DESC LIMIT 1");
    if(mysqli_num_rows($query) > 0) {
        $row = mysqli_fetch_assoc($query);
        $parts = explode("/", $row['registration_no']);
        $number = (int)$parts[2] + 1;
    } else {
        $number = 1;
    }
    while(true) {
        $registration = $prefix."/".$school_code."/".str_pad($number,4,"0",STR_PAD_LEFT)."/".$year;
        $check = mysqli_query($conn, "SELECT student_id FROM student WHERE registration_no='$registration'");
        if(mysqli_num_rows($check) == 0) break;
        $number++;
    }
    return $registration;
}

//  ADD STUDENT 
if(isset($_POST['add_student'])) {
    $name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $class_id = $_POST['class_id'];
    $dob = $_POST['date_of_birth'];
    $academic_year = date("Y");
    $phone = $_POST['phone'];
    $gender = $_POST['gender'];

    $check_email = mysqli_query($conn, "SELECT user_id FROM users WHERE email='$email'");
    if(mysqli_num_rows($check_email) > 0) {
        $_SESSION['error_msg'] = "Email already exists!";
        if (function_exists('logAction')) {
            logAction('error', 'students', "Failed to add student: Email already exists - $email", 'failed');
        }
    } else {
        $insert_user = mysqli_query($conn, "INSERT INTO users (full_name, email, password, role, phone, gender) VALUES ('$name', '$email', '$password', 'student', '$phone', '$gender')");
        if($insert_user) {
            $user_id = mysqli_insert_id($conn);
            $admission_no = generateAdmissionNo($conn);
            $registration_no = generateRegistrationNo($conn, $class_id, $school_code);
            $insert_student = mysqli_query($conn, "INSERT INTO student (user_id, registration_no, class_id, date_of_birth, academic_year, admission_no) VALUES ('$user_id', '$registration_no', '$class_id', '$dob', '$academic_year', '$admission_no')");
            if($insert_student) {
                $_SESSION['success_msg'] = "Student added successfully! Admission No: ".$admission_no;
                
                // Log successful student addition
                if (function_exists('logAction')) {
                    $student_data = [
                        'name' => $name,
                        'email' => $email,
                        'class_id' => $class_id,
                        'admission_no' => $admission_no,
                        'registration_no' => $registration_no,
                        'dob' => $dob
                    ];
                    logAction(
                        'add', 
                        'students', 
                        "Added new student: $name (Admission: $admission_no, Reg: $registration_no)", 
                        'success', 
                        mysqli_insert_id($conn), 
                        'students',
                        null,
                        $student_data
                    );
                }
            } else {
                $_SESSION['error_msg'] = mysqli_error($conn);
                if (function_exists('logAction')) {
                    logAction('error', 'students', "Failed to add student: " . mysqli_error($conn), 'failed');
                }
            }
        } else {
            $_SESSION['error_msg'] = "Failed to create user!";
            if (function_exists('logAction')) {
                logAction('error', 'students', "Failed to create user for student: $name", 'failed');
            }
        }
    }
    header("Location: manage_students.php");
    exit();
}

//  UPDATE STUDENT 
if(isset($_POST['edit_student'])) {
    $student_id = (int)$_POST['student_id'];
    $name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $class_id = $_POST['class_id'];
    $dob = $_POST['date_of_birth'];
    $phone = $_POST['phone'];
    $gender = $_POST['gender'];

    // Get old data for audit
    $old_query = mysqli_query($conn, "
        SELECT s.*, u.full_name, u.email, u.phone, u.gender, c.class_name 
        FROM student s
        JOIN users u ON s.user_id = u.user_id
        JOIN class c ON s.class_id = c.class_id
        WHERE s.student_id='$student_id'
    ");
    $old_data = mysqli_fetch_assoc($old_query);
    
    $get = mysqli_query($conn, "SELECT user_id FROM student WHERE student_id='$student_id'");
    $user = mysqli_fetch_assoc($get);
    $user_id = $user['user_id'];

    $check = mysqli_query($conn, "SELECT user_id FROM users WHERE email='$email' AND user_id!='$user_id'");
    if(mysqli_num_rows($check) > 0) {
        $_SESSION['error_msg'] = "Email already exists!";
        if (function_exists('logAction')) {
            logAction('error', 'students', "Failed to update student $name: Email already exists - $email", 'failed', $student_id, 'students');
        }
    } else {
        mysqli_query($conn, "UPDATE users SET full_name='$name', email='$email', phone='$phone', gender='$gender' WHERE user_id='$user_id'");
        mysqli_query($conn, "UPDATE student SET class_id='$class_id', date_of_birth='$dob' WHERE student_id='$student_id'");
        $_SESSION['success_msg'] = "Student updated successfully!";
        
        // Log successful update
        if (function_exists('logAction')) {
            $new_data = [
                'name' => $name,
                'email' => $email,
                'class_id' => $class_id,
                'dob' => $dob,
                'phone' => $phone,
                'gender' => $gender
            ];
            logAction(
                'edit', 
                'students', 
                "Updated student: $name (ID: $student_id)", 
                'success', 
                $student_id, 
                'students',
                $old_data,
                $new_data
            );
        }
    }
    header("Location: manage_students.php");
    exit();
}

// SUSPEND/ACTIVATE STUDENT 
if(isset($_GET['suspend']) || isset($_GET['activate'])) {
    $action = isset($_GET['suspend']) ? 'suspend' : 'activate';
    $student_id = (int)$_GET[$action];
    $status = $action == 'suspend' ? 'suspended' : 'active';
    $msg = $action == 'suspend' ? 'suspended' : 'activated';
    
    // Get student info for logging
    $student_query = mysqli_query($conn, "
        SELECT s.*, u.full_name, u.email 
        FROM student s 
        JOIN users u ON s.user_id = u.user_id 
        WHERE s.student_id='$student_id'
    ");
    $student_info = mysqli_fetch_assoc($student_query);
    
    $get_user = mysqli_query($conn, "SELECT user_id FROM student WHERE student_id='$student_id'");
    if($user = mysqli_fetch_assoc($get_user)) {
        mysqli_query($conn, "UPDATE users SET status='$status' WHERE user_id='{$user['user_id']}'");
        $_SESSION['success_msg'] = "Student $msg successfully!";
        
        // Log suspend/activate
        if (function_exists('logAction')) {
            logAction(
                $action == 'suspend' ? 'suspend' : 'activate', 
                'students', 
                ucfirst($action) . "d student: " . ($student_info['full_name'] ?? 'Unknown') . " (ID: $student_id)", 
                'success', 
                $student_id, 
                'students',
                ['status' => $action == 'suspend' ? 'active' : 'suspended'],
                ['status' => $action == 'suspend' ? 'suspended' : 'active']
            );
        }
    }
    header("Location: manage_students.php");
    exit();
}

// DELETE STUDENT
if(isset($_GET['delete'])) {
    $student_id = (int)$_GET['delete'];
    
    // Get student info for logging
    $student_query = mysqli_query($conn, "
        SELECT s.*, u.full_name, u.email, u.user_id
        FROM student s 
        JOIN users u ON s.user_id = u.user_id 
        WHERE s.student_id='$student_id'
    ");
    $student_info = mysqli_fetch_assoc($student_query);
    
    if ($student_info) {
        $user_id = $student_info['user_id'];
        $name = $student_info['full_name'];
        
        // Delete student record
        $delete_student = mysqli_query($conn, "DELETE FROM student WHERE student_id='$student_id'");
        if ($delete_student) {
            // Delete user record
            mysqli_query($conn, "DELETE FROM users WHERE user_id='$user_id'");
            
            $_SESSION['success_msg'] = "Student deleted successfully!";
            
            // Log deletion
            if (function_exists('logAction')) {
                logAction(
                    'delete', 
                    'students', 
                    "Deleted student: $name (ID: $student_id, Email: {$student_info['email']})", 
                    'success', 
                    $student_id, 
                    'students',
                    $student_info,
                    null
                );
            }
        } else {
            $_SESSION['error_msg'] = "Failed to delete student!";
            if (function_exists('logAction')) {
                logAction('error', 'students', "Failed to delete student: $name (ID: $student_id)", 'failed', $student_id, 'students');
            }
        }
    }
    
    header("Location: manage_students.php");
    exit();
}

//  SEARCH 
$search = isset($_GET['search']) ? trim(mysqli_real_escape_string($conn, $_GET['search'])) : '';

// Log search if performed
if (!empty($search) && function_exists('logAction')) {
    logAction('search', 'students', "Admin searched students with keyword: $search", 'success', null, 'students');
}

$query = "SELECT s.student_id, s.user_id, s.registration_no, s.admission_no, u.full_name, u.email, u.phone, u.gender, u.status, c.class_id, c.class_name, s.academic_year, s.date_of_birth
FROM student s
JOIN users u ON s.user_id=u.user_id
JOIN class c ON s.class_id=c.class_id
WHERE u.role='student'";

if(!empty($search)) {
    $search_terms = array_filter(explode(' ', $search));
    $conditions = [];
    foreach ($search_terms as $term) {
        $term = mysqli_real_escape_string($conn, $term);
        if (strlen($term) > 0) {
            $conditions[] = "(u.full_name LIKE '%$term%' OR c.class_name LIKE '%$term%' OR s.registration_no LIKE '%$term%' OR s.admission_no LIKE '%$term%' OR u.email LIKE '%$term%' OR u.phone LIKE '%$term%')";
        }
    }
    if (!empty($conditions)) {
        $query .= " AND " . implode(' AND ', $conditions);
    }
}
$query .= " ORDER BY s.student_id DESC";
$students = mysqli_query($conn, $query);

$classes = mysqli_query($conn, "SELECT * FROM class ORDER BY class_name");
$classes_edit = mysqli_query($conn, "SELECT * FROM class ORDER BY class_name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students SSARMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
      
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Roboto, Arial, sans-serif;
            background: #f0f2f5;
            color: #1e293b;
        }

        .container {
            margin-left: 270px;
            margin-top: 85px;
            padding: 20px 30px;
            min-height: 100vh;
        }

        .card {
            background: #ffffff;
            padding: 24px 28px;
            border-radius: 20px;
            margin-bottom: 25px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
        }

        h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #1a1a2e;
        }

        h2 i {
            color: black;
        }

        h3 {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #1a1a2e;
        }

        h3 i {
            color: black;
        }

        .alert {
            padding: 14px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: #dcfce7;
            border-left: 4px solid #22c55e;
            color: #166534;
        }

        .alert-error {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 18px;
        }

        .input-group {
            display: flex;
            flex-direction: column;
        }

        .input-group label {
            font-weight: 600;
            font-size: 11px;
            margin-bottom: 6px;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-group label i {
            color: black;
            width: 18px;
        }

        input,
        select {
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 13px;
            transition: 0.2s;
            width: 100%;
            background: white;
        }

        input:focus,
        select:focus {
            border-color: #f59e0b;
            outline: none;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2);
        }

        input[readonly] {
            background: #f1f5f9;
            cursor: not-allowed;
        }

       /* Make button more visible */
.btn-primary {
    background: #061d3ade !important;
    color: white !important;
    border: none !important;
    font-weight: 600 !important;
    padding: 12px 30px !important;
    border-radius: 10px !important;
    cursor: pointer !important;
    font-size: 14px !important;
    transition: 0.2s !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    width: 100% !important;
    justify-content: center !important;
}

        .btn-search {
            background: #04367c;
            color: white;
            border: none;
            font-weight: 600;
            padding: 10px 22px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 13px;
            transition: 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-clear {
            background: #e2e8f0;
            color: #1e293b;
            border: none;
            font-weight: 600;
            padding: 10px 22px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 13px;
            transition: 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-edit {
            background: white;
            color: #2563eb;
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            border: 1px solid #bfdbfe;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: 0.2s;
        }

        .btn-edit:hover {
            background: #eff6ff;
        }

        .btn-suspend {
            background: #fee2e2;
            color: #dc2626;
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: 0.2s;
        }

        .btn-suspend:hover {
            background: #fecaca;
        }

        .btn-activate {
            background: #dcfce7;
            color: #15803d;
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: 0.2s;
        }

        .btn-activate:hover {
            background: #bbf7d0;
        }

        .table-wrapper {
            overflow-x: auto;
            border-radius: 12px;
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        th {
            text-align: left;
            padding: 14px;
            background: #1a1a2e;
            color: white;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 12px 14px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
            font-size: 13px;
            color: #334155;
        }

        tr:hover td {
            background: #fefce8;
        }

        .sn-number {
            background: #1a1a2e;
            color: white;
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: bold;
            font-size: 12px;
        }

        .empty-row td {
            text-align: center;
            padding: 40px !important;
            color: #94a3b8;
        }


        .status-active {
            color: #15803d;
            font-weight: 600;
        }

        .status-suspended {
            color: #dc2626;
            font-weight: 600;
        }

        .gender-male {
            color: #2563eb;
            font-weight: 600;
        }

        .gender-female {
            color: #be185d;
            font-weight: 600;
        }

        .class-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .search-section {
            display: flex;
            gap: 12px;
            margin-bottom: 18px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .search-section .input-group {
            flex: 1;
            min-width: 250px;
        }

        .search-result-info {
            font-size: 12px;
            color: #64748b;
            margin-top: 10px;
        }

        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            align-items: center;
        }


        .stat-badge {
            background: #f8fafc;
            padding: 8px 20px;
            border-radius: 30px;
            border: 1px solid #e2e8f0;
            font-size: 13px;
            font-weight: 600;
            color: #1e293b;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .stat-badge i {
            color: #074591;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1100;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 24px;
            padding: 24px;
            max-width: 700px;
            width: 90%;
            max-height: 85vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e2e8f0;
        }

        .modal-header h3 {
            margin-bottom: 0;
        }

        .close {
            font-size: 28px;
            cursor: pointer;
            color: #94a3b8;
            line-height: 1;
            transition: 0.2s;
        }

        .close:hover {
            color: #000;
        }


        .mobile-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            background: #1a1a2e;
            color: white;
            border: none;
            padding: 12px 15px;
            border-radius: 10px;
            cursor: pointer;
            z-index: 1100;
            font-size: 18px;
        }


        @media (max-width: 768px) {
            .sidebar {
                left: -280px;
            }

            .sidebar.active {
                left: 0;
            }

            .topbar {
                left: 0;
            }

            .container {
                margin-left: 0;
                margin-top: 80px;
                padding: 15px;
            }

            .mobile-toggle {
                display: block;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .search-section {
                flex-direction: column;
                align-items: stretch;
            }

            .search-section .input-group {
                min-width: auto;
            }

            .action-buttons {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>

    <button class="mobile-toggle" onclick="document.getElementById('sidebar').classList.toggle('active')">
        <i class="fas fa-bars"></i>
    </button>

    <?php include 'admin_sidebar.php'; ?>
    <?php include '../auth/topbar.php'; ?>

    <div class="container">
        <!-- Header -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
            <h2><i class="fas fa-graduation-cap"></i> Manage Students</h2>
            <div class="stat-badge">
                <i class="fas fa-users"></i>
                <span id="studentCountSpan">0</span> enrolled
            </div>
        </div>

        <!-- Alerts -->
        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success_msg']) ?>
            </div>
            <?php unset($_SESSION['success_msg']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_msg'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($_SESSION['error_msg']) ?>
            </div>
            <?php unset($_SESSION['error_msg']); ?>
        <?php endif; ?>

        <!-- ===== ADD STUDENT ===== -->
        <div class="card">
            <h3><i class="fas fa-user-plus"></i> Register Student</h3>
            <form method="POST">
                <div class="form-grid">
                    <div class="input-group">
                        <label><i class="fas fa-id-card"></i> Admission Number</label>
                        <input type="text" value="<?= generateAdmissionNo($conn); ?>" readonly>
                    </div>
                    <div class="input-group">
                        <label><i class="fas fa-user"></i> Full Name </label>
                        <input type="text" name="full_name" placeholder="Student name" required>
                    </div>
                    <div class="input-group">
                        <label><i class="fas fa-qrcode"></i> Registration Number</label>
                        <input type="text" id="registration_no_display" value="Select class first" readonly>
                    </div>
                    <div class="input-group">
                        <label><i class="fas fa-envelope"></i> Email </label>
                        <input type="email" name="email" placeholder="student@example.com" required>
                    </div>
                    <div class="input-group">
                        <label><i class="fas fa-lock"></i> Password </label>
                        <input type="password" name="password" placeholder="********" required>
                    </div>
                    <div class="input-group">
                        <label><i class="fas fa-calendar"></i> Date of Birth </label>
                        <input type="date" name="date_of_birth" required>
                    </div>
                    <div class="input-group">
                        <label><i class="fas fa-school"></i> Class </label>
                        <select name="class_id" id="class_id" required>
                            <option value="">-- Select Class --</option>
                            <?php mysqli_data_seek($classes, 0);
                            while ($c = mysqli_fetch_assoc($classes)): ?>
                                <option value="<?= $c['class_id'] ?>"><?= htmlspecialchars($c['class_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="input-group">
                        <label><i class="fas fa-phone"></i> Phone </label>
                        <input type="text" name="phone" placeholder="07XXXXXXXX" required>
                    </div>
                    <div class="input-group">
                        <label><i class="fas fa-venus-mars"></i> Gender </label>
                        <select name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label><i class="fas fa-calendar-alt"></i> Academic Year</label>
                        <input type="number" id="academicYear" value="<?= date('Y') ?>" readonly>
                    </div>
                    <div class="input-group" style="display:flex; align-items:center; justify-content:flex-end; margin-top:10px;">
                        <button type="submit" name="add_student" class="btn-primary" style="width:100%; padding:12px; font-size:14px; font-weight:600;">
                          <i class="fas fa-save"></i> Add Student
                       </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- ===== ENROLLED STUDENTS ===== -->
        <div class="card">
            <h3><i class="fas fa-users"></i> Enrolled Students</h3>

            <!-- Search Bar inside Enrolled Students -->
            <div class="search-section">
                <div class="input-group">
                    <label><i class="fas fa-search"></i> Search Students</label>
                    <input type="text" id="searchInput" placeholder="Search by name, class, admission or registration number" value="<?= htmlspecialchars($search) ?>">
                </div>
                <button id="searchBtn" class="btn-search"><i class="fas fa-search"></i> Search</button>
                <button id="clearBtn" class="btn-clear"><i class="fas fa-times"></i> Clear</button>
            </div>

            <?php if (!empty($search)): ?>
                <div class="search-result-info">
                    <i class="fas fa-info-circle"></i> Showing results for: <strong>"<?= htmlspecialchars($search) ?>"</strong>
                </div>
            <?php endif; ?>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th style="width:50px;">No</th>
                            <th>Name</th>
                            <th>Reg No</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Gender</th>
                            <th>Class</th>
                            <th>Status</th>
                            <th style="width:200px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="studentTableBody">
                        <?php if (mysqli_num_rows($students) > 0): ?>
                            <?php $i = 1;
                            while ($row = mysqli_fetch_assoc($students)): ?>
                                <tr>
                                    <td><span class="sn-number"><?= $i++ ?></span></td>
                                    <td><strong><?= htmlspecialchars($row['full_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['registration_no']) ?></td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= htmlspecialchars($row['phone']) ?></td>
                                    <td>
                                        <?php if ($row['gender'] == 'male'): ?>
                                            <span class="gender-male"><i class="fas fa-mars"></i> Male</span>
                                        <?php else: ?>
                                            <span class="gender-female"><i class="fas fa-venus"></i> Female</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="class-badge"><i class="fas fa-school"></i> <?= htmlspecialchars($row['class_name']) ?></span></td>
                                    <td>
                                        <span class="<?= $row['status'] == 'suspended' ? 'status-suspended' : 'status-active' ?>">
                                            <i class="fas <?= $row['status'] == 'suspended' ? 'fa-user-slash' : 'fa-user-check' ?>"></i>
                                            <?= ucfirst($row['status']) ?>
                                        </span>
                                    </td>
                                    <td class="action-buttons">
                                        <button class="btn-edit" onclick="openEditModal(
                                            <?= $row['student_id'] ?>,
                                            '<?= addslashes($row['full_name']) ?>',
                                            '<?= $row['email'] ?>',
                                            '<?= $row['phone'] ?>',
                                            '<?= $row['gender'] ?>',
                                            <?= $row['class_id'] ?>,
                                            '<?= $row['date_of_birth'] ?>'
                                        )">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <?php if ($row['status'] == 'active'): ?>
                                            <button class="btn-suspend" onclick="if(confirm('Suspend this student?')) window.location.href='?suspend=<?= $row['student_id'] ?>'">
                                                <i class="fas fa-user-slash"></i> Suspend
                                            </button>
                                        <?php else: ?>
                                            <button class="btn-activate" onclick="if(confirm('Activate this student?')) window.location.href='?activate=<?= $row['student_id'] ?>'">
                                                <i class="fas fa-user-check"></i> Activate
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr class="empty-row">
                                <td colspan="9"><i class="fas fa-folder-open"></i> No students found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ===== EDIT MODAL ===== -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Edit Student</h3>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="student_id" id="edit_student_id">
                <div class="form-grid">
                    <div class="input-group">
                        <label><i class="fas fa-user"></i> Full Name </label>
                        <input type="text" name="full_name" id="edit_full_name" required>
                    </div>
                    <div class="input-group">
                        <label><i class="fas fa-envelope"></i> Email </label>
                        <input type="email" name="email" id="edit_email" required>
                    </div>
                    <div class="input-group">
                        <label><i class="fas fa-calendar"></i> Date of Birth </label>
                        <input type="date" name="date_of_birth" id="edit_date_of_birth" required>
                    </div>
                    <div class="input-group">
                        <label><i class="fas fa-school"></i> Class </label>
                        <select name="class_id" id="edit_class_id" required>
                            <?php mysqli_data_seek($classes_edit, 0);
                            while ($c = mysqli_fetch_assoc($classes_edit)): ?>
                                <option value="<?= $c['class_id'] ?>"><?= htmlspecialchars($c['class_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="input-group">
                        <label><i class="fas fa-phone"></i> Phone </label>
                        <input type="text" name="phone" id="edit_phone" required>
                    </div>
                    <div class="input-group">
                        <label><i class="fas fa-venus-mars"></i> Gender</label>
                        <select name="gender" id="edit_gender" required>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                </div>
                <br>
                <button type="submit" name="edit_student" class="btn-primary">
                    <i class="fas fa-save"></i> Update Student
                </button>
            </form>
        </div>
    </div>

    <script>
        // AUTO-HIDE ALERTS
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.3s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }, 4000);
            });
        }, 500);

        // UPDATE STUDENT COUNT
        document.getElementById('studentCountSpan').innerText =
            document.querySelectorAll('#studentTableBody tr:not(.empty-row)').length;


        // EDIT MODAL FUNCTIONS
        function openEditModal(id, name, email, phone, gender, classId, dob) {
            document.getElementById('edit_student_id').value = id;
            document.getElementById('edit_full_name').value = name;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_phone').value = phone;
            document.getElementById('edit_gender').value = gender;
            document.getElementById('edit_class_id').value = classId;
            document.getElementById('edit_date_of_birth').value = dob;
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        window.onclick = function(e) {
            if (e.target == document.getElementById('editModal')) closeEditModal();
        };

       
        // GENERATE REGISTRATION NUMBER
        
        document.getElementById('class_id').addEventListener('change', function() {
            let regField = document.getElementById('registration_no_display');
            if (!this.value) {
                regField.value = "Select class first";
                return;
            }
            regField.value = 'Generating...';
            fetch("generate_reg_no.php?class_id=" + this.value)
                .then(response => response.text())
                .then(data => regField.value = data)
                .catch(() => regField.value = "Error generating number");
        });

        // SEARCH FUNCTIONS
       
        document.getElementById('searchInput')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') searchStudents();
        });

        document.getElementById('searchBtn')?.addEventListener('click', searchStudents);
        document.getElementById('clearBtn')?.addEventListener('click', clearSearch);

        function searchStudents() {
            let term = document.getElementById('searchInput').value.trim();
            let url = new URL(window.location.href);
            term ? url.searchParams.set('search', term) : url.searchParams.delete('search');
            window.location.href = url.toString();
        }

        function clearSearch() {
            let url = new URL(window.location.href);
            url.searchParams.delete('search');
            window.location.href = url.toString();
        }

        // MOBILE SIDEBAR TOGGLE
        document.querySelector('.mobile-toggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
    </script>

    <?php include '../footer.php'; ?>
</body>
</html>