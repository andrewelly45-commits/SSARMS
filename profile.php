<?php
// profile.php - Universal Profile for all roles

session_start();
require_once('config.php');
require_once('db.php');

// ============================================================
// ENSURE UPLOAD DIRECTORY EXISTS
// ============================================================
if (!is_dir("uploads")) {
    mkdir("uploads", 0777, true);
}
if (!is_dir("../uploads")) {
    mkdir("../uploads", 0777, true);
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
$is_modal = isset($_GET['modal']) && $_GET['modal'] == 'true';

// ============================================================
// GET USER DATA (UNIVERSAL)
// ============================================================
$query = mysqli_query($conn, "SELECT * FROM users WHERE user_id='$user_id'");
$user = mysqli_fetch_assoc($query);

if (!$user) {
    die("User not found.");
}

// ============================================================
// ROLE-SPECIFIC DATA - WITH ERROR HANDLING
// ============================================================
$role_data = [];
$role_label = ucfirst($user_role);
$role_icon = 'fa-user';
$dashboard = 'auth/login.php';

switch($user_role) {
    case 'admin':
        $q = mysqli_query($conn, "SELECT * FROM admin WHERE user_id='$user_id'");
        if ($q) {
            $role_data = mysqli_fetch_assoc($q);
            if (!$role_data) {
                $role_data = ['admin_id' => 'N/A'];
            }
        } else {
            $role_data = ['admin_id' => 'N/A'];
        }
        $role_label = 'Administrator';
        $role_icon = 'fa-user-shield';
        $dashboard = 'admin/admin_dashboard.php';
        break;
        
    case 'teacher':
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
        $role_label = 'Teacher';
        $role_icon = 'fa-chalkboard-teacher';
        $dashboard = 'teacher/teacher_dashboard.php';
        break;
        
    case 'academic':
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
        $role_label = 'Academic Staff';
        $role_icon = 'fa-user-graduate';
        $dashboard = 'academic/academic_dashboard.php';
        break;
        
    case 'student':
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
            if (!is_dir("uploads")) mkdir("uploads", 0777, true);
            
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
                            unlink($old_path);
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
    
    // Return JSON for modal
    if ($is_modal) {
        echo json_encode(['success' => !$error, 'message' => $error ?: $success]);
        exit();
    }
}

// ============================================================
// CHANGE PASSWORD
// ============================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $pass_error = '';
    $pass_success = '';
    
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
    
    if ($is_modal) {
        echo json_encode(['success' => isset($pass_success), 'message' => $pass_success ?? $pass_error]);
        exit();
    }
}

// ============================================================
// GET PROFILE PICTURE PATH (FIXED)
// ============================================================
function getProfilePicPath($user, $default = 'uploads/default.png') {
    if (!empty($user['profile_pic'])) {
        // Check multiple possible paths
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
    
    // Check default paths
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

// ============================================================
// IF MODAL - SHOW ONLY PROFILE CONTENT
// ============================================================
if ($is_modal) {
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
                background: rgba(0,0,0,0.6);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
                cursor: pointer;
            }

            .profile-wrapper {
                width: 100%;
                max-width: 800px;
                max-height: 95vh;
                cursor: default;
            }

            .profile-card {
                background: #fff;
                border-radius: 12px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                overflow: hidden;
                max-height: 95vh;
                overflow-y: auto;
                animation: slideIn 0.3s ease-out;
            }

            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(-20px) scale(0.95);
                }
                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }

            .profile-header {
                background: linear-gradient(135deg, #1a1a2e, #16213e);
                color: #fff;
                padding: 35px 30px 25px;
                text-align: center;
            }

            .profile-pic {
                width: 120px;
                height: 120px;
                border-radius: 50%;
                border: 4px solid #f59e0b;
                overflow: hidden;
                margin: 0 auto;
                position: relative;
            }

            .profile-pic img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

            .profile-pic .edit-badge {
                position: absolute;
                bottom: 5px;
                right: 5px;
                background: #f59e0b;
                width: 35px;
                height: 35px;
                border-radius: 50%;
                border: 3px solid #fff;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: transform 0.2s;
            }

            .profile-pic .edit-badge:hover {
                transform: scale(1.1);
            }

            .profile-header h2 {
                margin-top: 15px;
                font-size: 24px;
            }

            .role-badge {
                display: inline-block;
                background: #f59e0b;
                padding: 4px 18px;
                border-radius: 20px;
                font-size: 12px;
                font-weight: 600;
                text-transform: uppercase;
                margin-top: 8px;
            }

            .profile-header .email {
                margin-top: 8px;
                opacity: 0.8;
                font-size: 14px;
            }

            .profile-body {
                padding: 25px 30px;
            }

            .alert {
                padding: 10px 15px;
                border-radius: 6px;
                margin-bottom: 15px;
                font-weight: 500;
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

            .btn {
                padding: 8px 20px;
                border: none;
                border-radius: 6px;
                font-weight: 600;
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                font-size: 14px;
                transition: all 0.2s;
            }

            .btn-primary {
                background: #f59e0b;
                color: #fff;
            }

            .btn-primary:hover {
                background: #d97706;
                transform: translateY(-1px);
            }

            .btn-secondary {
                background: #e5e7eb;
                color: #374151;
            }

            .btn-secondary:hover {
                background: #d1d5db;
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

            .action-bar {
                display: flex;
                gap: 10px;
                margin-bottom: 20px;
                flex-wrap: wrap;
            }

            .info-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 12px;
            }

            .info-item {
                background: #f9fafb;
                padding: 12px 15px;
                border-radius: 6px;
                border: 1px solid #e5e7eb;
                display: flex;
                align-items: center;
                gap: 12px;
            }

            .info-item .icon {
                width: 38px;
                height: 38px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #fff;
                flex-shrink: 0;
                font-size: 14px;
            }

            .info-item .icon.blue { background: #4f46e5; }
            .info-item .icon.green { background: #10b981; }
            .info-item .icon.orange { background: #f59e0b; }
            .info-item .icon.pink { background: #ec4899; }
            .info-item .icon.purple { background: #8b5cf6; }
            .info-item .icon.red { background: #ef4444; }
            .info-item .icon.teal { background: #14b8a6; }

            .info-item .label {
                font-size: 11px;
                color: #6b7280;
                text-transform: uppercase;
                font-weight: 600;
            }

            .info-item .value {
                font-weight: 600;
                color: #1f2937;
                font-size: 14px;
            }

            .status-badge {
                padding: 2px 10px;
                border-radius: 12px;
                font-size: 11px;
                font-weight: 600;
                text-transform: uppercase;
            }

            .status-badge.active {
                background: #d1fae5;
                color: #065f46;
            }

            .status-badge.inactive {
                background: #fee2e2;
                color: #991b1b;
            }

            .edit-form {
                display: none;
                margin-top: 20px;
            }

            .edit-form.active {
                display: block;
            }

            .form-row {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 15px;
                margin-bottom: 15px;
            }

            .form-group {
                display: flex;
                flex-direction: column;
                gap: 4px;
            }

            .form-group.full {
                grid-column: 1 / -1;
            }

            .form-group label {
                font-weight: 600;
                font-size: 13px;
                color: #374151;
            }

            .form-group input,
            .form-group select {
                padding: 8px 12px;
                border: 2px solid #e5e7eb;
                border-radius: 6px;
                font-size: 14px;
                transition: border-color 0.2s;
            }

            .form-group input:focus,
            .form-group select:focus {
                outline: none;
                border-color: #4f46e5;
            }

            .form-group input[readonly] {
                background: #f9fafb;
                color: #6b7280;
            }

            .form-group input[type="file"] {
                padding: 8px;
                background: #f9fafb;
                border: 2px dashed #d1d5db;
                cursor: pointer;
            }

            .form-actions {
                display: flex;
                gap: 10px;
                margin-top: 15px;
                padding-top: 15px;
                border-top: 2px solid #f3f4f6;
            }

            .password-section {
                margin-top: 25px;
                padding-top: 20px;
                border-top: 2px solid #f3f4f6;
            }

            .password-section h3 {
                font-size: 16px;
                margin-bottom: 15px;
                color: #1f2937;
            }

            .password-section .form-row {
                margin-bottom: 0;
            }

            @media (max-width: 640px) {
                .info-grid,
                .form-row {
                    grid-template-columns: 1fr;
                }
                .profile-body {
                    padding: 15px;
                }
                .profile-header {
                    padding: 25px 15px;
                }
                .profile-pic {
                    width: 100px;
                    height: 100px;
                }
                .action-bar {
                    flex-direction: column;
                }
                .form-actions {
                    flex-direction: column;
                }
            }

            .profile-card::-webkit-scrollbar {
                width: 6px;
            }
            .profile-card::-webkit-scrollbar-thumb {
                background: #4f46e5;
                border-radius: 10px;
            }
        </style>
    </head>
    <body onclick="closeProfile(event)">
        <div class="profile-wrapper" onclick="event.stopPropagation()">
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-pic">
                        <img src="<?= htmlspecialchars($profile_pic_path) ?>" id="profilePreview" alt="Profile">
                        <div class="edit-badge" onclick="toggleEdit()">
                            <i class="fas fa-camera"></i>
                        </div>
                    </div>
                    <h2 id="displayName"><?= htmlspecialchars($user['full_name']) ?></h2>
                    <span class="role-badge">
                        <i class="fas <?= $role_icon ?>"></i> <?= $role_label ?>
                    </span>
                    <div class="email">
                        <i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email']) ?>
                    </div>
                </div>

                <div class="profile-body">
                    <div id="alertContainer"></div>

                    <div class="action-bar">
                        <button class="btn btn-primary" onclick="toggleEdit()">
                            <i class="fas fa-edit"></i> Edit Profile
                        </button>
                        <button class="btn btn-secondary" onclick="closeProfile()">
                            <i class="fas fa-times"></i> Close
                        </button>
                    </div>

                    <!-- View Mode -->
                    <div id="viewProfile">
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="icon blue"><i class="fas fa-user"></i></div>
                                <div>
                                    <div class="label">Full Name</div>
                                    <div class="value"><?= htmlspecialchars($user['full_name']) ?></div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="icon green"><i class="fas fa-envelope"></i></div>
                                <div>
                                    <div class="label">Email</div>
                                    <div class="value"><?= htmlspecialchars($user['email']) ?></div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="icon orange"><i class="fas fa-phone"></i></div>
                                <div>
                                    <div class="label">Phone</div>
                                    <div class="value"><?= !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'Not Set' ?></div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="icon pink"><i class="fas fa-venus-mars"></i></div>
                                <div>
                                    <div class="label">Gender</div>
                                    <div class="value"><?= ucfirst($user['gender'] ?? 'Not Set') ?></div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="icon purple"><i class="fas fa-user-tag"></i></div>
                                <div>
                                    <div class="label">Role</div>
                                    <div class="value"><?= $role_label ?></div>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="icon red"><i class="fas fa-circle"></i></div>
                                <div>
                                    <div class="label">Status</div>
                                    <div class="value">
                                        <span class="status-badge <?= $user['status'] ?? 'active' ?>"><?= ucfirst($user['status'] ?? 'Active') ?></span>
                                    </div>
                                </div>
                            </div>

                            <?php if($user_role == 'admin'): ?>
                                <div class="info-item">
                                    <div class="icon teal"><i class="fas fa-building"></i></div>
                                    <div>
                                        <div class="label">Department</div>
                                        <div class="value">Administration</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="icon blue"><i class="fas fa-id-badge"></i></div>
                                    <div>
                                        <div class="label">Admin ID</div>
                                        <div class="value"><?= htmlspecialchars($role_data['admin_id'] ?? 'N/A') ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if($user_role == 'teacher' || $user_role == 'academic'): ?>
                                <div class="info-item">
                                    <div class="icon teal"><i class="fas fa-building"></i></div>
                                    <div>
                                        <div class="label">Department</div>
                                        <div class="value"><?= htmlspecialchars($role_data['department_name'] ?? 'Not Assigned') ?></div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="icon blue"><i class="fas fa-id-badge"></i></div>
                                    <div>
                                        <div class="label">Teacher ID</div>
                                        <div class="value"><?= htmlspecialchars($role_data['teacher_id'] ?? 'N/A') ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if($user_role == 'student'): ?>
                                <div class="info-item">
                                    <div class="icon teal"><i class="fas fa-school"></i></div>
                                    <div>
                                        <div class="label">Class</div>
                                        <div class="value"><?= htmlspecialchars($role_data['class_name'] ?? 'Not Assigned') ?></div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="icon blue"><i class="fas fa-id-card"></i></div>
                                    <div>
                                        <div class="label">Registration No</div>
                                        <div class="value"><?= htmlspecialchars($role_data['registration_no'] ?? 'N/A') ?></div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="icon purple"><i class="fas fa-id-badge"></i></div>
                                    <div>
                                        <div class="label">Admission No</div>
                                        <div class="value"><?= htmlspecialchars($role_data['admission_no'] ?? 'N/A') ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Edit Mode -->
                    <div id="editProfile" class="edit-form">
                        <form id="profileForm" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="save_profile" value="1">
                            
                            <div class="form-row">
                                <div class="form-group full">
                                    <label><i class="fas fa-camera"></i> Profile Picture</label>
                                    <input type="file" name="profile_pic" accept="image/*" onchange="previewImage(this)">
                                    <small style="color: #6b7280; font-size: 12px;">JPG, PNG, GIF (Max 5MB)</small>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-user"></i> Full Name</label>
                                    <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label><i class="fas fa-envelope"></i> Email</label>
                                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-phone"></i> Phone</label>
                                    <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label><i class="fas fa-venus-mars"></i> Gender</label>
                                    <select name="gender">
                                        <option value="">Select</option>
                                        <option value="male" <?= ($user['gender'] ?? '') == 'male' ? 'selected' : '' ?>>Male</option>
                                        <option value="female" <?= ($user['gender'] ?? '') == 'female' ? 'selected' : '' ?>>Female</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-user-tag"></i> Role</label>
                                    <input type="text" value="<?= $role_label ?>" readonly>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="toggleEdit()">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            </div>
                        </form>

                        <!-- Change Password Section -->
                        <div class="password-section">
                            <h3><i class="fas fa-key"></i> Change Password</h3>
                            <form id="passwordForm" method="POST">
                                <input type="hidden" name="change_password" value="1">
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Current Password</label>
                                        <input type="password" name="current_password" placeholder="Enter current password" required>
                                    </div>
                                    <div class="form-group">
                                        <label>New Password</label>
                                        <input type="password" name="new_password" placeholder="Enter new password" required>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Confirm New Password</label>
                                        <input type="password" name="confirm_password" placeholder="Confirm new password" required>
                                    </div>
                                </div>
                                <div style="margin-top: 10px;">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-key"></i> Change Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            let isEditing = false;

            function previewImage(input) {
                if (input.files && input.files[0]) {
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('profilePreview').src = e.target.result;
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            }

            function toggleEdit() {
                const view = document.getElementById('viewProfile');
                const edit = document.getElementById('editProfile');
                const btn = document.querySelector('.action-bar .btn-primary');

                if (!isEditing) {
                    view.style.display = 'none';
                    edit.classList.add('active');
                    btn.innerHTML = '<i class="fas fa-eye"></i> View Profile';
                    isEditing = true;
                } else {
                    view.style.display = 'block';
                    edit.classList.remove('active');
                    btn.innerHTML = '<i class="fas fa-edit"></i> Edit Profile';
                    isEditing = false;
                }
            }

            function closeProfile(event) {
                if (event) {
                    const wrapper = document.querySelector('.profile-wrapper');
                    if (!wrapper || !wrapper.contains(event.target)) {
                        if (window.parent && window.parent.closeProfileModal) {
                            window.parent.closeProfileModal();
                        } else {
                            window.location.href = '<?= $dashboard ?>';
                        }
                    }
                } else {
                    if (window.parent && window.parent.closeProfileModal) {
                        window.parent.closeProfileModal();
                    } else {
                        window.location.href = '<?= $dashboard ?>';
                    }
                }
            }

            // Profile Form Submit
            document.getElementById('profileForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                
                fetch('profile.php?modal=true', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('alertContainer');
                    
                    if (data.success) {
                        container.innerHTML = `
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> ${data.message}
                            </div>
                        `;
                        document.getElementById('displayName').textContent = 
                            document.querySelector('input[name="full_name"]').value;
                        
                        setTimeout(() => {
                            if (window.parent && window.parent.closeProfileModal) {
                                window.parent.location.reload();
                            }
                        }, 1500);
                    } else {
                        container.innerHTML = `
                            <div class="alert alert-error">
                                <i class="fas fa-exclamation-circle"></i> ${data.message}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    document.getElementById('alertContainer').innerHTML = `
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i> Error: ${error}
                        </div>
                    `;
                });
            });

            // Password Form Submit
            document.getElementById('passwordForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                
                fetch('profile.php?modal=true', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('alertContainer');
                    
                    if (data.success) {
                        container.innerHTML = `
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> ${data.message}
                            </div>
                        `;
                        this.reset();
                    } else {
                        container.innerHTML = `
                            <div class="alert alert-error">
                                <i class="fas fa-exclamation-circle"></i> ${data.message}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    document.getElementById('alertContainer').innerHTML = `
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i> Error: ${error}
                        </div>
                    `;
                });
            });

            // Close on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeProfile();
                }
            });

            // Initialize
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('viewProfile').style.display = 'block';
            });
        </script>
    </body>
    </html>
    <?php
    exit();
}

// ============================================================
// FULL PAGE VIEW (WITH SIDEBAR & TOPBAR)
// ============================================================
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
        }

        .main-content {
            margin-left: 270px;
            margin-top: 85px;
            padding: 30px;
        }

        .profile-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto;
            overflow: hidden;
        }

        .profile-header {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            color: #fff;
            padding: 35px 30px 25px;
            text-align: center;
        }

        .profile-pic {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid #f59e0b;
            overflow: hidden;
            margin: 0 auto;
            position: relative;
        }

        .profile-pic img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-pic .edit-badge {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: #f59e0b;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            border: 3px solid #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .profile-pic .edit-badge:hover {
            transform: scale(1.1);
        }

        .profile-header h2 {
            margin-top: 15px;
            font-size: 24px;
        }

        .role-badge {
            display: inline-block;
            background: #f59e0b;
            padding: 4px 18px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-top: 8px;
        }

        .profile-header .email {
            margin-top: 8px;
            opacity: 0.8;
            font-size: 14px;
        }

        .profile-body {
            padding: 25px 30px;
        }

        .alert {
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-weight: 500;
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

        .btn {
            padding: 8px 20px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #f59e0b;
            color: #fff;
        }

        .btn-primary:hover {
            background: #d97706;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #374151;
        }

        .btn-secondary:hover {
            background: #d1d5db;
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

        .action-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .info-item {
            background: #f9fafb;
            padding: 12px 15px;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .info-item .icon {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            flex-shrink: 0;
            font-size: 14px;
        }

        .info-item .icon.blue { background: #4f46e5; }
        .info-item .icon.green { background: #10b981; }
        .info-item .icon.orange { background: #f59e0b; }
        .info-item .icon.pink { background: #ec4899; }
        .info-item .icon.purple { background: #8b5cf6; }
        .info-item .icon.red { background: #ef4444; }
        .info-item .icon.teal { background: #14b8a6; }

        .info-item .label {
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: 600;
        }

        .info-item .value {
            font-weight: 600;
            color: #1f2937;
            font-size: 14px;
        }

        .status-badge {
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-badge.active {
            background: #d1fae5;
            color: #065f46;
        }

        .status-badge.inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .edit-form {
            display: none;
            margin-top: 20px;
        }

        .edit-form.active {
            display: block;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-weight: 600;
            font-size: 13px;
            color: #374151;
        }

        .form-group input,
        .form-group select {
            padding: 8px 12px;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #4f46e5;
        }

        .form-group input[readonly] {
            background: #f9fafb;
            color: #6b7280;
        }

        .form-group input[type="file"] {
            padding: 8px;
            background: #f9fafb;
            border: 2px dashed #d1d5db;
            cursor: pointer;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #f3f4f6;
        }

        .password-section {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 2px solid #f3f4f6;
        }

        .password-section h3 {
            font-size: 16px;
            margin-bottom: 15px;
            color: #1f2937;
        }

        .password-section .form-row {
            margin-bottom: 0;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
                margin-top: 70px;
            }
        }

        @media (max-width: 640px) {
            .info-grid,
            .form-row {
                grid-template-columns: 1fr;
            }
            .profile-body {
                padding: 15px;
            }
            .profile-header {
                padding: 25px 15px;
            }
            .profile-pic {
                width: 100px;
                height: 100px;
            }
            .action-bar {
                flex-direction: column;
            }
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<?php 
// Include sidebar based on role
$sidebar_path = $user_role . '/' . $user_role . '_sidebar.php';
if (file_exists($sidebar_path)) {
    include($sidebar_path);
} elseif (file_exists('../' . $sidebar_path)) {
    include('../' . $sidebar_path);
} else {
    // Fallback: try to find sidebar
    $possible_paths = [
        $user_role . '_sidebar.php',
        '../' . $user_role . '/' . $user_role . '_sidebar.php',
        '../../' . $user_role . '/' . $user_role . '_sidebar.php'
    ];
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            include($path);
            break;
        }
    }
}

// Include topbar
if (file_exists('auth/topbar.php')) {
    include('auth/topbar.php');
} elseif (file_exists('../auth/topbar.php')) {
    include('../auth/topbar.php');
}
?>

<div class="main-content">
    <div class="profile-card">
        <div class="profile-header">
            <div class="profile-pic">
                <img src="<?= htmlspecialchars($profile_pic_path) ?>" id="profilePreview" alt="Profile">
                <div class="edit-badge" onclick="toggleEdit()">
                    <i class="fas fa-camera"></i>
                </div>
            </div>
            <h2 id="displayName"><?= htmlspecialchars($user['full_name']) ?></h2>
            <span class="role-badge">
                <i class="fas <?= $role_icon ?>"></i> <?= $role_label ?>
            </span>
            <div class="email">
                <i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email']) ?>
            </div>
        </div>

        <div class="profile-body">
            <?php if($success): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
            <?php endif; ?>
            <?php if(isset($pass_success)): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $pass_success ?></div>
            <?php endif; ?>
            <?php if(isset($pass_error)): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $pass_error ?></div>
            <?php endif; ?>

            <div class="action-bar">
                <button class="btn btn-primary" onclick="toggleEdit()">
                    <i class="fas fa-edit"></i> Edit Profile
                </button>
                <a href="<?= $dashboard ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Dashboard
                </a>
            </div>

            <!-- View Mode -->
            <div id="viewProfile">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="icon blue"><i class="fas fa-user"></i></div>
                        <div>
                            <div class="label">Full Name</div>
                            <div class="value"><?= htmlspecialchars($user['full_name']) ?></div>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="icon green"><i class="fas fa-envelope"></i></div>
                        <div>
                            <div class="label">Email</div>
                            <div class="value"><?= htmlspecialchars($user['email']) ?></div>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="icon orange"><i class="fas fa-phone"></i></div>
                        <div>
                            <div class="label">Phone</div>
                            <div class="value"><?= !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'Not Set' ?></div>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="icon pink"><i class="fas fa-venus-mars"></i></div>
                        <div>
                            <div class="label">Gender</div>
                            <div class="value"><?= ucfirst($user['gender'] ?? 'Not Set') ?></div>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="icon purple"><i class="fas fa-user-tag"></i></div>
                        <div>
                            <div class="label">Role</div>
                            <div class="value"><?= $role_label ?></div>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="icon red"><i class="fas fa-circle"></i></div>
                        <div>
                            <div class="label">Status</div>
                            <div class="value">
                                <span class="status-badge <?= $user['status'] ?? 'active' ?>"><?= ucfirst($user['status'] ?? 'Active') ?></span>
                            </div>
                        </div>
                    </div>

                    <?php if($user_role == 'admin'): ?>
                        <div class="info-item">
                            <div class="icon teal"><i class="fas fa-building"></i></div>
                            <div>
                                <div class="label">Department</div>
                                <div class="value">Administration</div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="icon blue"><i class="fas fa-id-badge"></i></div>
                            <div>
                                <div class="label">Admin ID</div>
                                <div class="value"><?= htmlspecialchars($role_data['admin_id'] ?? 'N/A') ?></div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if($user_role == 'teacher' || $user_role == 'academic'): ?>
                        <div class="info-item">
                            <div class="icon teal"><i class="fas fa-building"></i></div>
                            <div>
                                <div class="label">Department</div>
                                <div class="value"><?= htmlspecialchars($role_data['department_name'] ?? 'Not Assigned') ?></div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="icon blue"><i class="fas fa-id-badge"></i></div>
                            <div>
                                <div class="label">Teacher ID</div>
                                <div class="value"><?= htmlspecialchars($role_data['teacher_id'] ?? 'N/A') ?></div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if($user_role == 'student'): ?>
                        <div class="info-item">
                            <div class="icon teal"><i class="fas fa-school"></i></div>
                            <div>
                                <div class="label">Class</div>
                                <div class="value"><?= htmlspecialchars($role_data['class_name'] ?? 'Not Assigned') ?></div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="icon blue"><i class="fas fa-id-card"></i></div>
                            <div>
                                <div class="label">Registration No</div>
                                <div class="value"><?= htmlspecialchars($role_data['registration_no'] ?? 'N/A') ?></div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="icon purple"><i class="fas fa-id-badge"></i></div>
                            <div>
                                <div class="label">Admission No</div>
                                <div class="value"><?= htmlspecialchars($role_data['admission_no'] ?? 'N/A') ?></div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Edit Mode -->
            <div id="editProfile" class="edit-form">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="save_profile" value="1">
                    
                    <div class="form-row">
                        <div class="form-group full">
                            <label><i class="fas fa-camera"></i> Profile Picture</label>
                            <input type="file" name="profile_pic" accept="image/*" onchange="previewImage(this)">
                            <small style="color: #6b7280; font-size: 12px;">JPG, PNG, GIF (Max 5MB)</small>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-user"></i> Full Name</label>
                            <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-envelope"></i> Email</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-phone"></i> Phone</label>
                            <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-venus-mars"></i> Gender</label>
                            <select name="gender">
                                <option value="">Select</option>
                                <option value="male" <?= ($user['gender'] ?? '') == 'male' ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= ($user['gender'] ?? '') == 'female' ? 'selected' : '' ?>>Female</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-user-tag"></i> Role</label>
                            <input type="text" value="<?= $role_label ?>" readonly>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="toggleEdit()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                </form>

                <!-- Change Password Section -->
                <div class="password-section">
                    <h3><i class="fas fa-key"></i> Change Password</h3>
                    <form method="POST">
                        <input type="hidden" name="change_password" value="1">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Current Password</label>
                                <input type="password" name="current_password" placeholder="Enter current password" required>
                            </div>
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" name="new_password" placeholder="Enter new password" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Confirm New Password</label>
                                <input type="password" name="confirm_password" placeholder="Confirm new password" required>
                            </div>
                        </div>
                        <div style="margin-top: 10px;">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-key"></i> Change Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let isEditing = false;

    function previewImage(input) {
        if (input.files && input.files[0]) {
            let reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profilePreview').src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function toggleEdit() {
        const view = document.getElementById('viewProfile');
        const edit = document.getElementById('editProfile');
        const btn = document.querySelector('.action-bar .btn-primary');

        if (!isEditing) {
            view.style.display = 'none';
            edit.classList.add('active');
            btn.innerHTML = '<i class="fas fa-eye"></i> View Profile';
            isEditing = true;
        } else {
            view.style.display = 'block';
            edit.classList.remove('active');
            btn.innerHTML = '<i class="fas fa-edit"></i> Edit Profile';
            isEditing = false;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('viewProfile').style.display = 'block';
    });
</script>

</body>
</html>