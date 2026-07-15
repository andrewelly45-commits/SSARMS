<?php
// profile.php - Full Page Profile

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once('config.php');
require_once('db.php');

// ============================================================
// ENSURE UPLOAD DIRECTORY EXISTS - FIXED
// ============================================================
// Check if directory exists before trying to create it
if (!is_dir("uploads")) {
    @mkdir("uploads", 0777, true); // @ suppresses the warning
}
if (!is_dir("../uploads")) {
    @mkdir("../uploads", 0777, true);
}

// ============================================================
// AUTH CHECK
// ============================================================
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? '';

// ============================================================
// GET USER DATA (UNIVERSAL)
// ============================================================
$query = mysqli_query($conn, "SELECT * FROM users WHERE user_id='$user_id'");
$user = mysqli_fetch_assoc($query);

if (!$user) {
    die("User not found.");
}

// ============================================================
// ROLE-SPECIFIC DATA - FIXED
// ============================================================
$role_data = [];
$role_label = ucfirst($user_role);
$role_icon = 'fa-user';
$dashboard = 'auth/login.php';

// Check if we're using the correct database
// The error shows 'ssarms_db.admin' doesn't exist

switch($user_role) {
    case 'admin':
        // Check if admin table exists first
        $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'admin'");
        if(mysqli_num_rows($table_check) > 0) {
            $q = mysqli_query($conn, "SELECT * FROM admin WHERE user_id='$user_id'");
            if ($q) {
                $role_data = mysqli_fetch_assoc($q);
                if (!$role_data) {
                    $role_data = ['admin_id' => 'N/A'];
                }
            } else {
                $role_data = ['admin_id' => 'N/A'];
            }
        } else {
            // Admin table doesn't exist - use users table data
            $role_data = [
                'admin_id' => $user_id,
                'full_name' => $user['full_name'],
                'email' => $user['email']
            ];
        }
        $role_label = 'Administrator';
        $role_icon = 'fa-user-shield';
        $dashboard = 'admin/admin_dashboard.php';
        break;
        
    case 'teacher':
        // Check if teacher table exists
        $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'teacher'");
        if(mysqli_num_rows($table_check) > 0) {
            $q = mysqli_query($conn, "
                SELECT t.*, d.department_name 
                FROM teacher t 
                LEFT JOIN department d ON t.department_id = d.department_id 
                WHERE t.user_id='$user_id'
            ");
            if ($q) {
                $role_data = mysqli_fetch_assoc($q);
                if (!$role_data) {
                    $role_data = ['teacher_id' => 'N/A', 'department_name' => 'Not Assigned'];
                }
            } else {
                $role_data = ['teacher_id' => 'N/A', 'department_name' => 'Not Assigned'];
            }
        } else {
            $role_data = ['teacher_id' => 'N/A', 'department_name' => 'Not Assigned'];
        }
        $role_label = 'Teacher';
        $role_icon = 'fa-chalkboard-teacher';
        $dashboard = 'teacher/teacher_dashboard.php';
        break;
        
    case 'academic':
        // Check if teacher table exists
        $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'teacher'");
        if(mysqli_num_rows($table_check) > 0) {
            $q = mysqli_query($conn, "
                SELECT t.*, d.department_name 
                FROM teacher t 
                LEFT JOIN department d ON t.department_id = d.department_id 
                WHERE t.user_id='$user_id'
            ");
            if ($q) {
                $role_data = mysqli_fetch_assoc($q);
                if (!$role_data) {
                    $role_data = ['teacher_id' => 'N/A', 'department_name' => 'Not Assigned'];
                }
            } else {
                $role_data = ['teacher_id' => 'N/A', 'department_name' => 'Not Assigned'];
            }
        } else {
            $role_data = ['teacher_id' => 'N/A', 'department_name' => 'Not Assigned'];
        }
        $role_label = 'Academic Staff';
        $role_icon = 'fa-user-graduate';
        $dashboard = 'academic/academic_dashboard.php';
        break;
        
    case 'student':
        // Check if student table exists
        $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'student'");
        if(mysqli_num_rows($table_check) > 0) {
            $q = mysqli_query($conn, "
                SELECT s.*, c.class_name 
                FROM student s 
                LEFT JOIN class c ON s.class_id = c.class_id 
                WHERE s.user_id='$user_id'
            ");
            if ($q) {
                $role_data = mysqli_fetch_assoc($q);
                if (!$role_data) {
                    $role_data = [
                        'class_name' => 'Not Assigned', 
                        'registration_no' => 'N/A', 
                        'admission_no' => 'N/A'
                    ];
                }
            } else {
                $role_data = [
                    'class_name' => 'Not Assigned', 
                    'registration_no' => 'N/A', 
                    'admission_no' => 'N/A'
                ];
            }
        } else {
            $role_data = [
                'class_name' => 'Not Assigned', 
                'registration_no' => 'N/A', 
                'admission_no' => 'N/A'
            ];
        }
        $role_label = 'Student';
        $role_icon = 'fa-user';
        $dashboard = 'student/student_dashboard.php';
        break;
        
    default:
        $role_label = 'User';
        $role_icon = 'fa-user';
        $dashboard = 'auth/login.php';
        $role_data = [];
}

// ============================================================
// UPDATE PROFILE
// ============================================================
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_profile'])) {
    $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $gender = mysqli_real_escape_string($conn, $_POST['gender'] ?? '');
    
    $profile_pic = $user['profile_pic'];
    
    // Handle profile picture upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            // Create uploads directory if it doesn't exist
            if (!is_dir("uploads")) {
                @mkdir("uploads", 0777, true);
            }
            
            $new_name = "PROFILE_" . $user_id . "_" . time() . "." . $ext;
            $dest = "uploads/" . $new_name;
            
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $dest)) {
                // Delete old profile pic if exists
                if (!empty($user['profile_pic']) && $user['profile_pic'] != "default.png") {
                    $old_paths = [
                        "uploads/" . $user['profile_pic'],
                        "../uploads/" . $user['profile_pic']
                    ];
                    foreach ($old_paths as $old_path) {
                        if (file_exists($old_path)) {
                            @unlink($old_path);
                        }
                    }
                }
                $profile_pic = $new_name;
            } else {
                $error = "Unable to upload image.";
            }
        } else {
            $error = "Only JPG, JPEG, PNG, GIF allowed.";
        }
    }
    
    // Update database
    if (empty($error)) {
        $update = mysqli_query($conn, "
            UPDATE users SET 
                full_name='$full_name',
                email='$email',
                phone='$phone',
                gender='$gender',
                profile_pic='$profile_pic'
            WHERE user_id='$user_id'
        ");
        
        if ($update) {
            $_SESSION['full_name'] = $full_name;
            $_SESSION['email'] = $email;
            $success = "Profile updated successfully!";
            
            // Refresh user data
            $query = mysqli_query($conn, "SELECT * FROM users WHERE user_id='$user_id'");
            $user = mysqli_fetch_assoc($query);
        } else {
            $error = mysqli_error($conn);
        }
    }
}

// ============================================================
// CHANGE PASSWORD
// ============================================================
$pass_error = '';
$pass_success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $pass_error = "All password fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $pass_error = "New passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $pass_error = "Password must be at least 6 characters.";
    } else {
        // Verify current password
        $check = mysqli_query($conn, "SELECT password FROM users WHERE user_id='$user_id'");
        $user_data = mysqli_fetch_assoc($check);
        
        if ($user_data && password_verify($current_password, $user_data['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update = mysqli_query($conn, "UPDATE users SET password='$hashed_password' WHERE user_id='$user_id'");
            
            if ($update) {
                $pass_success = "Password changed successfully!";
            } else {
                $pass_error = "Failed to update password.";
            }
        } else {
            $pass_error = "Current password is incorrect.";
        }
    }
}

// ============================================================
// GET PROFILE PICTURE PATH
// ============================================================
function getProfilePicPath($user, $default = 'uploads/default.png') {
    if (!empty($user['profile_pic'])) {
        $possible_paths = [
            "uploads/" . $user['profile_pic'],
            "../uploads/" . $user['profile_pic'],
            "../../uploads/" . $user['profile_pic'],
            $user['profile_pic']
        ];
        foreach ($possible_paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
    }
    
    $default_paths = [
        "uploads/default.png",
        "../uploads/default.png",
        "../../uploads/default.png",
        "../assets/user.png",
        "assets/user.png"
    ];
    foreach ($default_paths as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }
    
    return $default;
}

$profile_pic_path = getProfilePicPath($user);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #eef2ff, #e0e7ff);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .page-wrapper {
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }


        .brand {
            font-size: 22px;
            font-weight: 700;
            color: #1f2937;
        }

        .brand i {
            color: #4f46e5;
            margin-right: 10px;
        }

       .nav-links {
            display: flex;
            gap: 15px;
            align-items: center;
        }

       .nav-links a {
            text-decoration: none;
            color: #6b7280;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.2s;
            font-weight: 500;
        }

        .nav-links a:hover {
            background: #f3f4f6;
            color: #1f2937;
        }

        .nav-links a.active {
            background: #4f46e5;
            color: #fff;
        }

        .nav-links a .badge {
            background: #ef4444;
            color: #fff;
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 10px;
            margin-left: 5px;
        }

        /* Alerts */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

      
.profile-header {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    padding: 20px 25px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 25px;
    flex-wrap: wrap;
}

.avatar-wrapper {
    position: relative;
    flex-shrink: 0;
}

       .avatar-wrapper img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #f59e0b;
}

.avatar-wrapper .upload-btn {
    position: absolute;
    bottom: 2px;
    right: 2px;
    background: #f59e0b;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: 2px solid #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    color: #fff;
    font-size: 12px;
}

      .avatar-wrapper .upload-btn:hover {
    transform: scale(1.1);
    background: #d97706;
}

.user-info h1 {
    font-size: 22px;
    color: #1f2937;
    margin-bottom: 3px;
}

.user-info .role-badge {
    display: inline-block;
    background: #f59e0b;
    color: #fff;
    padding: 2px 14px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    margin-bottom: 4px;
}
        
.user-info .email-text {
    color: #6b7280;
    font-size: 13px;
}

.user-info .email-text i {
    margin-right: 6px;
    color: #4f46e5;
}

.user-info .status-badge {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    margin-top: 4px;
}

.status-badge.active {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.inactive {
    background: #fee2e2;
    color: #991b1b;
}
        /* Stats */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .stat-box {
            background: #fff;
            padding: 18px 22px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            display: flex;
            align-items: center;
            gap: 15px;
            transition: transform 0.2s;
        }

        .stat-box:hover {
            transform: translateY(-2px);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #fff;
            flex-shrink: 0;
        }

        .stat-icon.blue { background: #4f46e5; }
        .stat-icon.green { background: #10b981; }
        .stat-icon.orange { background: #f59e0b; }
        .stat-icon.purple { background: #8b5cf6; }
        .stat-icon.pink { background: #ec4899; }

        .stat-info .label {
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: 600;
        }

        .stat-info .value {
            font-weight: 700;
            color: #1f2937;
            font-size: 16px;
        }

        /* Grid */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            padding: 28px;
        }

        .card.full {
            grid-column: 1 / -1;
        }

        .card-title {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #f3f4f6;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-title i {
            color: #4f46e5;
        }

        /* Forms */
        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 13px;
            color: #374151;
            margin-bottom: 5px;
        }

        .form-group label i {
            margin-right: 6px;
            color: #6b7280;
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s;
            background: #f9fafb;
        }

        .form-control:focus {
            outline: none;
            border-color: #4f46e5;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .form-control[readonly] {
            background: #f3f4f6;
            color: #6b7280;
            cursor: not-allowed;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .hidden-file {
            display: none;
        }

        /* Buttons */
        .btn {
            padding: 10px 28px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .btn-primary {
            background: #4f46e5;
            color: #fff;
        }

        .btn-primary:hover {
            background: #4338ca;
        }

        .btn-success {
            background: #10b981;
            color: #fff;
        }

        .btn-success:hover {
            background: #059669;
        }

        .btn-danger {
            background: #ef4444;
            color: #fff;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #374151;
        }

        .btn-secondary:hover {
            background: #d1d5db;
        }

        .btn-outline {
            background: transparent;
            color: #4f46e5;
            border: 2px solid #4f46e5;
        }

        .btn-outline:hover {
            background: #4f46e5;
            color: #fff;
        }

        .btn-sm {
            padding: 6px 16px;
            font-size: 12px;
        }

        .text-muted {
            color: #6b7280;
            font-size: 12px;
        }

        .mt-3 {
            margin-top: 15px;
        }

        .pt-3 {
            padding-top: 15px;
        }

        .border-top {
            border-top: 2px solid #f3f4f6;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }
            .card.full {
                grid-column: 1;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            .profile-header {
                flex-direction: column;
                text-align: center;
                padding: 25px;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
            .stats-row {
                grid-template-columns: 1fr 1fr;
            }
            .top-nav {
                flex-direction: column;
                gap: 15px;
                padding: 15px 20px;
            }
            .top-nav .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .stats-row {
                grid-template-columns: 1fr;
            }
            .avatar-wrapper img {
                width: 100px;
                height: 100px;
            }
        }
    </style>
</head>
<body>

<div class="page-wrapper">
    <div class="container">

        <!-- Alerts -->
        <?php if($success): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
        <?php endif; ?>
        <?php if($pass_success): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $pass_success ?></div>
        <?php endif; ?>
        <?php if($pass_error): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $pass_error ?></div>
        <?php endif; ?>

        <!-- Profile Header -->
        <div class="profile-header">
            <div class="avatar-wrapper">
                <img src="<?= htmlspecialchars($profile_pic_path) ?>" id="profilePreview" alt="Profile Picture">
                <div class="upload-btn" onclick="document.getElementById('picInput').click();" title="Change Profile Picture">
                    <i class="fas fa-camera"></i>
                </div>
                <input type="file" id="picInput" class="hidden-file" accept="image/*" onchange="uploadPhoto(this)">
            </div>
            <div class="user-info">
                <h1><?= htmlspecialchars($user['full_name']) ?></h1>
                <span class="role-badge"><i class="fas <?= $role_icon ?>"></i> <?= $role_label ?></span>
                <div class="email-text"><i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></div>
                <span class="status-badge <?= $user['status'] ?? 'active' ?>"><?= ucfirst($user['status'] ?? 'Active') ?></span>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-row">
            
            <?php if($user_role == 'student'): ?>
            <div class="stat-box">
                <div class="stat-icon purple"><i class="fas fa-school"></i></div>
                <div class="stat-info">
                    <div class="label">Class</div>
                    <div class="value"><?= htmlspecialchars($role_data['class_name'] ?? 'Not Assigned') ?></div>
                </div>
            </div>
            <?php elseif($user_role == 'teacher' || $user_role == 'academic'): ?>
            <div class="stat-box">
                <div class="stat-icon purple"><i class="fas fa-building"></i></div>
                <div class="stat-info">
                    <div class="label">Department</div>
                    <div class="value"><?= htmlspecialchars($role_data['department_name'] ?? 'Not Assigned') ?></div>
                </div>
            </div>
            <?php else: ?>
            <?php endif; ?>
        </div>

        <!-- Main Grid -->
        <div class="grid-2">

            <!-- Personal Information -->
            <div class="card">
                <div class="card-title"><i class="fas fa-user-edit"></i> Personal Information</div>
                <form method="POST" enctype="multipart/form-data" id="profileForm">
                    <input type="hidden" name="save_profile" value="1">
                    
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email Address</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-phone"></i> Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="Enter phone number">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-venus-mars"></i> Gender</label>
                            <select name="gender" class="form-control">
                                <option value="">Select</option>
                                <option value="male" <?= ($user['gender'] ?? '') == 'male' ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= ($user['gender'] ?? '') == 'female' ? 'selected' : '' ?>>Female</option>
                            </select>
                        </div>
                    </div>
                     
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Profile</button>
                </form>
            </div>

            <!-- Change Password - Full Width -->
            <div class="card full">
                <div class="card-title"><i class="fas fa-key"></i> Change Password</div>
                <form method="POST">
                    <input type="hidden" name="change_password" value="1">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-lock"></i> Current Password</label>
                            <input type="password" name="current_password" class="form-control" placeholder="Enter current password" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-lock"></i> New Password</label>
                            <input type="password" name="new_password" class="form-control" placeholder="Enter new password (min 6 characters)" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-check-circle"></i> Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-danger"><i class="fas fa-key"></i> Change Password</button>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
    // Upload profile picture
    function uploadPhoto(input) {
        if (input.files && input.files[0]) {
            // Show preview
            let reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profilePreview').src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
            
            // Submit form
            const form = document.getElementById('profileForm');
            const formData = new FormData(form);
            formData.set('profile_pic', input.files[0]);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                window.location.reload();
            })
            .catch(() => {
                alert('Error uploading profile picture. Please try again.');
            });
        }
    }

    // Auto-hide alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }, 5000);
        });
    });
</script>

</body>
</html>