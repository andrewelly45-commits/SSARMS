<?php
session_start();
require_once('config.php');
require_once('db.php');

// ============================================================
// LOGIN CHECK
// ============================================================
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$user_id   = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? '';

// ============================================================
// CHECK IF MODAL
// ============================================================
$is_modal = isset($_GET['modal']) && $_GET['modal'] == 'true';

// ============================================================
// GET USER INFORMATION
// ============================================================
$query = mysqli_query($conn, "SELECT * FROM users WHERE user_id='$user_id'");

if (mysqli_num_rows($query) == 0) {
    die("User not found.");
}

$user = mysqli_fetch_assoc($query);

// ============================================================
// SUCCESS / ERROR MESSAGE
// ============================================================
$success = "";
$error   = "";

// ============================================================
// UPDATE PROFILE
// ============================================================
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['save_profile'])) {

    $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $email     = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone     = mysqli_real_escape_string($conn, trim($_POST['phone']));

    $profile_pic = $user['profile_pic'];

    // Profile Image Upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {

        $allowed = ['jpg','jpeg','png','gif'];
        $extension = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));

        if(in_array($extension,$allowed)){

            if(!is_dir("uploads")){
                mkdir("uploads",0777,true);
            }

            $new_name = "PROFILE_".$user_id."_".time().".".$extension;
            $destination = "uploads/".$new_name;

            if(move_uploaded_file($_FILES['profile_pic']['tmp_name'],$destination)){

                // Delete old picture except default
                if(!empty($user['profile_pic']) &&
                    $user['profile_pic']!="default.png" &&
                    file_exists("uploads/".$user['profile_pic'])){

                    unlink("uploads/".$user['profile_pic']);
                }

                $profile_pic = $new_name;

            }else{
                $error="Unable to upload image.";
            }

        }else{
            $error="Only JPG, JPEG, PNG and GIF allowed.";
        }

    }

    // Update Database
    if(empty($error)){

        $update = mysqli_query($conn,"
            UPDATE users
            SET
                full_name='$full_name',
                email='$email',
                phone='$phone',
                profile_pic='$profile_pic'
            WHERE user_id='$user_id'
        ");

        if($update){

            $success="Profile updated successfully.";
            
            // Update session data
            $_SESSION['full_name'] = $full_name;
            $_SESSION['email'] = $email;

            // Refresh user data
            $query=mysqli_query($conn,
                "SELECT * FROM users WHERE user_id='$user_id'");

            $user=mysqli_fetch_assoc($query);

        }else{
            $error=mysqli_error($conn);
        }

    }

    // If modal, return JSON response
    if ($is_modal) {
        if(!empty($error)){
            echo json_encode(['success' => false, 'message' => $error]);
        } else {
            echo json_encode(['success' => true, 'message' => $success]);
        }
        exit();
    }
}

// ============================================================
// ROLE CONFIGURATION
// ============================================================
$sidebar = '';
$topbar = '';
$dashboard = '';

switch($user_role){
    case "admin":
        $sidebar   = "../admin/admin_sidebar.php";
        $topbar    = "../auth/topbar.php";
        $dashboard = "../admin/admin_dashboard.php";
        break;

    case "teacher":
        $sidebar   = "../teacher/teacher_sidebar.php";
        $topbar    = "../auth/topbar.php";
        $dashboard = "../teacher/teacher_dashboard.php";
        break;

    case "academic":
        $sidebar   = "../academic/academic_sidebar.php";
        $topbar    = "../auth/topbar.php";
        $dashboard = "../academic/academic_dashboard.php";
        break;

    case "student":
        $sidebar   = "../student/student_sidebar.php";
        $topbar    = "../auth/topbar.php";
        $dashboard = "../student/student_dashboard.php";
        break;

    default:
        die("Unknown user role.");
}

// If modal, show only profile content without sidebar/topbar
if ($is_modal) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>My Profile | SSARMS</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
        <style>
            /* Same styles as below - but without sidebar/topbar adjustments */
            :root {
                --primary: #4F46E5;
                --primary-dark: #4338CA;
                --primary-light: #818CF8;
                --secondary: #F59E0B;
                --success: #10B981;
                --danger: #EF4444;
                --dark: #1F2937;
                --gray-50: #F9FAFB;
                --gray-100: #F3F4F6;
                --gray-200: #E5E7EB;
                --gray-300: #D1D5DB;
                --gray-400: #9CA3AF;
                --gray-500: #6B7280;
                --gray-600: #4B5563;
                --gray-700: #374151;
                --gray-800: #1F2937;
                --gray-900: #111827;
                --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
                --shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
                --shadow-md: 0 10px 15px -3px rgba(0,0,0,0.1);
                --shadow-lg: 0 20px 25px -5px rgba(0,0,0,0.1);
                --shadow-xl: 0 25px 50px -12px rgba(0,0,0,0.25);
                --radius: 16px;
                --radius-sm: 8px;
                --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            }

            body {
                background: linear-gradient(135deg, #EEF2FF 0%, #E0E7FF 100%);
                min-height: 100vh;
                padding: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .profile-card {
                background: white;
                border-radius: var(--radius);
                box-shadow: var(--shadow-lg);
                overflow: hidden;
                max-width: 900px;
                width: 100%;
                margin: 0 auto;
                animation: slideUp 0.6s ease-out;
            }

            @keyframes slideUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .profile-header {
                background: linear-gradient(135deg, var(--gray-900) 0%, #1E293B 100%);
                color: white;
                padding: 50px 40px 40px;
                text-align: center;
                position: relative;
                overflow: hidden;
            }

            .profile-header::before {
                content: '';
                position: absolute;
                top: -50%;
                right: -20%;
                width: 500px;
                height: 500px;
                background: radial-gradient(circle, rgba(79, 70, 229, 0.1) 0%, transparent 70%);
                border-radius: 50%;
                pointer-events: none;
            }

            .profile-header::after {
                content: '';
                position: absolute;
                bottom: -30px;
                left: 0;
                width: 100%;
                height: 60px;
                background: white;
                border-radius: 50% 50% 0 0;
                pointer-events: none;
            }

            .profile-image-wrapper {
                position: relative;
                display: inline-block;
                margin-bottom: 20px;
            }

            .profile-image {
                width: 150px;
                height: 150px;
                margin: 0 auto;
                border-radius: 50%;
                overflow: hidden;
                border: 5px solid var(--secondary);
                cursor: pointer;
                box-shadow: var(--shadow-lg);
                transition: var(--transition);
                position: relative;
                z-index: 2;
            }

            .profile-image:hover {
                transform: scale(1.05);
                border-color: var(--primary-light);
            }

            .profile-image img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

            .profile-image-edit-badge {
                position: absolute;
                bottom: 5px;
                right: 5px;
                background: var(--secondary);
                color: white;
                width: 40px;
                height: 40px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 16px;
                border: 3px solid white;
                box-shadow: var(--shadow);
                z-index: 3;
                transition: var(--transition);
                cursor: pointer;
            }

            .profile-image-edit-badge:hover {
                transform: scale(1.1);
                background: var(--primary);
            }

            .profile-header h2 {
                font-size: 32px;
                font-weight: 800;
                margin-top: 5px;
                letter-spacing: -0.5px;
                position: relative;
                z-index: 2;
            }

            .role-badge {
                display: inline-block;
                margin-top: 12px;
                background: linear-gradient(135deg, var(--secondary), #D97706);
                padding: 8px 25px;
                border-radius: 50px;
                font-size: 13px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 1px;
                position: relative;
                z-index: 2;
                box-shadow: var(--shadow);
            }

            .profile-email {
                margin-top: 15px;
                opacity: 0.9;
                font-size: 15px;
                position: relative;
                z-index: 2;
            }

            .profile-email i {
                margin-right: 8px;
                color: var(--secondary);
            }

            .profile-body {
                padding: 40px 45px;
                background: white;
            }

            .message {
                padding: 16px 20px;
                margin-bottom: 25px;
                border-radius: var(--radius-sm);
                font-weight: 600;
                display: flex;
                align-items: center;
                gap: 12px;
                animation: slideDown 0.4s ease-out;
            }

            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .message i {
                font-size: 20px;
            }

            .message.success {
                background: linear-gradient(135deg, #ECFDF5, #D1FAE5);
                color: #065F46;
                border-left: 4px solid var(--success);
            }

            .message.error {
                background: linear-gradient(135deg, #FEF2F2, #FEE2E2);
                color: #991B1B;
                border-left: 4px solid var(--danger);
            }

            .action-bar {
                display: flex;
                gap: 15px;
                margin-bottom: 30px;
                flex-wrap: wrap;
                align-items: center;
            }

            .btn {
                padding: 12px 28px;
                border: none;
                border-radius: var(--radius-sm);
                cursor: pointer;
                font-weight: 600;
                transition: var(--transition);
                display: inline-flex;
                align-items: center;
                gap: 10px;
                font-size: 14px;
                text-decoration: none;
                letter-spacing: 0.3px;
                position: relative;
                overflow: hidden;
            }

            .btn::after {
                content: '';
                position: absolute;
                top: 50%;
                left: 50%;
                width: 0;
                height: 0;
                border-radius: 50%;
                background: rgba(255,255,255,0.2);
                transition: width 0.6s, height 0.6s, top 0.6s, left 0.6s;
            }

            .btn:active::after {
                width: 300px;
                height: 300px;
                top: -100px;
                left: -100px;
            }

            .btn-primary {
                background: linear-gradient(135deg, var(--secondary), #D97706);
                color: white;
                box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
            }

            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
            }

            .btn-secondary {
                background: var(--gray-100);
                color: var(--gray-700);
            }

            .btn-secondary:hover {
                background: var(--gray-200);
                transform: translateY(-2px);
                box-shadow: var(--shadow);
            }

            .btn-success {
                background: linear-gradient(135deg, var(--success), #059669);
                color: white;
                box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
            }

            .btn-success:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
            }

            .info-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 20px;
                margin-top: 20px;
            }

            .info-card {
                background: var(--gray-50);
                padding: 22px 25px;
                border-radius: var(--radius-sm);
                display: flex;
                align-items: center;
                gap: 18px;
                border: 1px solid var(--gray-200);
                transition: var(--transition);
                position: relative;
                overflow: hidden;
            }

            .info-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 4px;
                height: 100%;
                background: linear-gradient(180deg, var(--primary), var(--primary-light));
                opacity: 0;
                transition: var(--transition);
            }

            .info-card:hover {
                transform: translateY(-3px);
                box-shadow: var(--shadow-md);
                border-color: var(--primary-light);
            }

            .info-card:hover::before {
                opacity: 1;
            }

            .info-card .icon {
                width: 50px;
                height: 50px;
                background: linear-gradient(135deg, var(--primary), var(--primary-light));
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                flex-shrink: 0;
                font-size: 20px;
                box-shadow: 0 4px 10px rgba(79, 70, 229, 0.2);
            }

            .info-card .icon.green {
                background: linear-gradient(135deg, var(--success), #34D399);
                box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);
            }

            .info-card .icon.orange {
                background: linear-gradient(135deg, var(--secondary), #FBBF24);
                box-shadow: 0 4px 10px rgba(245, 158, 11, 0.2);
            }

            .info-card .icon.pink {
                background: linear-gradient(135deg, #EC4899, #F472B6);
                box-shadow: 0 4px 10px rgba(236, 72, 153, 0.2);
            }

            .info-card .icon.purple {
                background: linear-gradient(135deg, #8B5CF6, #A78BFA);
                box-shadow: 0 4px 10px rgba(139, 92, 246, 0.2);
            }

            .info-card .icon.red {
                background: linear-gradient(135deg, var(--danger), #F87171);
                box-shadow: 0 4px 10px rgba(239, 68, 68, 0.2);
            }

            .info-card .icon.teal {
                background: linear-gradient(135deg, #14B8A6, #5EEAD4);
                box-shadow: 0 4px 10px rgba(20, 184, 166, 0.2);
            }

            .info-card .details {
                display: flex;
                flex-direction: column;
                gap: 2px;
                flex: 1;
            }

            .info-card .label {
                font-size: 11px;
                color: var(--gray-500);
                text-transform: uppercase;
                letter-spacing: 0.8px;
                font-weight: 600;
            }

            .info-card .value {
                font-weight: 600;
                color: var(--gray-800);
                font-size: 15px;
            }

            .status-badge {
                padding: 5px 16px;
                border-radius: 50px;
                font-size: 12px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                display: inline-block;
            }

            .status-badge.active {
                background: linear-gradient(135deg, #D1FAE5, #A7F3D0);
                color: #065F46;
            }

            .status-badge.inactive {
                background: linear-gradient(135deg, #FEE2E2, #FCA5A5);
                color: #991B1B;
            }

            .edit-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 25px;
                margin-top: 25px;
            }

            .form-group {
                display: flex;
                flex-direction: column;
                gap: 8px;
            }

            .form-group.full {
                grid-column: 1 / -1;
            }

            .form-group label {
                font-weight: 600;
                color: var(--gray-700);
                font-size: 13px;
                letter-spacing: 0.3px;
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .form-group label i {
                color: var(--primary);
                width: 18px;
            }

            .form-group input,
            .form-group select,
            .form-group textarea {
                padding: 12px 16px;
                border: 2px solid var(--gray-200);
                border-radius: var(--radius-sm);
                font-size: 14px;
                transition: var(--transition);
                background: white;
                color: var(--gray-800);
                font-weight: 500;
            }

            .form-group input:focus,
            .form-group select:focus,
            .form-group textarea:focus {
                outline: none;
                border-color: var(--primary);
                box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
            }

            .form-group input[readonly],
            .form-group select[readonly] {
                background: var(--gray-50);
                color: var(--gray-600);
                cursor: not-allowed;
                border-color: var(--gray-200);
            }

            .form-group input[type="file"] {
                padding: 12px;
                background: var(--gray-50);
                border: 2px dashed var(--gray-300);
                cursor: pointer;
            }

            .form-group input[type="file"]:hover {
                border-color: var(--primary);
                background: var(--gray-100);
            }

            .buttons {
                display: flex;
                gap: 15px;
                margin-top: 30px;
                padding-top: 25px;
                border-top: 2px solid var(--gray-100);
            }

            #editProfile {
                display: none;
                animation: fadeIn 0.4s ease-out;
            }

            #viewProfile {
                animation: fadeIn 0.4s ease-out;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .close-modal-btn {
                position: absolute;
                top: 20px;
                right: 25px;
                background: rgba(255,255,255,0.2);
                border: none;
                color: white;
                font-size: 28px;
                cursor: pointer;
                z-index: 10;
                width: 45px;
                height: 45px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: var(--transition);
            }

            .close-modal-btn:hover {
                background: rgba(255,255,255,0.3);
                transform: rotate(90deg);
            }

            @media (max-width: 768px) {
                body { padding: 10px; }
                .profile-header { padding: 30px 20px 25px; }
                .profile-body { padding: 25px 20px; }
                .profile-header h2 { font-size: 24px; }
                .profile-image { width: 120px; height: 120px; }
                .info-grid { grid-template-columns: 1fr; gap: 15px; }
                .edit-grid { grid-template-columns: 1fr; gap: 20px; }
                .action-bar { flex-direction: column; align-items: stretch; }
                .buttons { flex-direction: column; }
                .btn { justify-content: center; }
                .close-modal-btn { top: 10px; right: 15px; width: 35px; height: 35px; font-size: 20px; }
            }

            @media (max-width: 480px) {
                .profile-header h2 { font-size: 20px; }
                .profile-image { width: 100px; height: 100px; }
                .profile-body { padding: 20px 15px; }
                .info-card { padding: 18px 20px; }
                .info-card .icon { width: 42px; height: 42px; font-size: 16px; }
            }

            ::-webkit-scrollbar { width: 8px; }
            ::-webkit-scrollbar-track { background: var(--gray-100); }
            ::-webkit-scrollbar-thumb { background: var(--primary-light); border-radius: 10px; }
            ::-webkit-scrollbar-thumb:hover { background: var(--primary); }
        </style>
    </head>
    <body>
        <div class="profile-card" style="position:relative;">
            <!-- Close Button -->
            <button class="close-modal-btn" onclick="closeProfile()">
                <i class="fas fa-times"></i>
            </button>

            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-image-wrapper">
                    <div class="profile-image">
                        <?php
                        if(!empty($user['profile_pic']) && file_exists("uploads/".$user['profile_pic'])){
                            $profilePic = "uploads/".htmlspecialchars($user['profile_pic']);
                        }else{
                            $profilePic = "uploads/default.png";
                        }
                        ?>
                        <img src="<?= $profilePic ?>" id="profilePreview" alt="Profile">
                    </div>
                    <div class="profile-image-edit-badge" onclick="toggleProfile()" title="Change Profile Picture">
                        <i class="fas fa-camera"></i>
                    </div>
                </div>

                <h2 id="displayName"><?= htmlspecialchars($user['full_name']); ?></h2>
                <span class="role-badge"><?= ucfirst($user['role']); ?></span>
                <div class="profile-email">
                    <i class="fas fa-envelope"></i>
                    <?= htmlspecialchars($user['email']); ?>
                </div>
            </div>

            <!-- Profile Body -->
            <div class="profile-body">
                <?php if(!empty($success)): ?>
                    <div class="message success">
                        <i class="fas fa-check-circle"></i>
                        <?= $success; ?>
                    </div>
                <?php endif; ?>

                <?php if(!empty($error)): ?>
                    <div class="message error">
                        <i class="fas fa-times-circle"></i>
                        <?= $error; ?>
                    </div>
                <?php endif; ?>

                <!-- Action Bar -->
                <div class="action-bar">
                    <button type="button" class="btn btn-primary" id="toggleBtn" onclick="toggleProfile()">
                        <i class="fas fa-edit"></i>
                        Edit Profile
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeProfile()">
                        <i class="fas fa-times"></i>
                        Close
                    </button>
                </div>

                <!-- View Profile -->
                <div id="viewProfile">
                    <div class="info-grid">
                        <div class="info-card">
                            <div class="icon"><i class="fas fa-user"></i></div>
                            <div class="details">
                                <span class="label">Full Name</span>
                                <span class="value"><?= htmlspecialchars($user['full_name']); ?></span>
                            </div>
                        </div>

                        <div class="info-card">
                            <div class="icon green"><i class="fas fa-envelope"></i></div>
                            <div class="details">
                                <span class="label">Email Address</span>
                                <span class="value"><?= htmlspecialchars($user['email']); ?></span>
                            </div>
                        </div>

                        <div class="info-card">
                            <div class="icon orange"><i class="fas fa-phone"></i></div>
                            <div class="details">
                                <span class="label">Phone Number</span>
                                <span class="value">
                                    <?= !empty($user['phone']) ? htmlspecialchars($user['phone']) : "Not Available"; ?>
                                </span>
                            </div>
                        </div>

                        <div class="info-card">
                            <div class="icon purple"><i class="fas fa-user-tag"></i></div>
                            <div class="details">
                                <span class="label">Username</span>
                                <span class="value"><?= htmlspecialchars($user['username'] ?? 'N/A'); ?></span>
                            </div>
                        </div>

                        <div class="info-card">
                            <div class="icon pink"><i class="fas fa-venus-mars"></i></div>
                            <div class="details">
                                <span class="label">Gender</span>
                                <span class="value"><?= ucfirst($user['gender'] ?? 'Not Set'); ?></span>
                            </div>
                        </div>

                        <div class="info-card">
                            <div class="icon"><i class="fas fa-user-shield"></i></div>
                            <div class="details">
                                <span class="label">Role</span>
                                <span class="value"><?= ucfirst($user['role']); ?></span>
                            </div>
                        </div>

                        <div class="info-card">
                            <div class="icon red"><i class="fas fa-circle"></i></div>
                            <div class="details">
                                <span class="label">Status</span>
                                <span class="value">
                                    <?php if($user['status']=="active"): ?>
                                        <span class="status-badge active">Active</span>
                                    <?php else: ?>
                                        <span class="status-badge inactive">Inactive</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>

                        <div class="info-card">
                            <div class="icon teal"><i class="fas fa-calendar"></i></div>
                            <div class="details">
                                <span class="label">Member Since</span>
                                <span class="value"><?= date("d M Y", strtotime($user['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Profile -->
                <div id="editProfile">
                    <form method="POST" enctype="multipart/form-data" id="profileForm">
                        <input type="hidden" name="save_profile" value="1">

                        <div class="edit-grid">
                            <div class="form-group full">
                                <label><i class="fas fa-camera"></i> Profile Picture</label>
                                <input type="file" name="profile_pic" id="profile_pic" accept="image/*" onchange="previewImage(this)">
                                <small style="color: var(--gray-500); font-size: 12px;">Allowed: JPG, JPEG, PNG, GIF (Max 5MB)</small>
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-user"></i> Full Name</label>
                                <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-envelope"></i> Email</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-phone"></i> Phone Number</label>
                                <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']); ?>" placeholder="Enter phone number">
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-user-tag"></i> Username</label>
                                <input type="text" value="<?= htmlspecialchars($user['username'] ?? 'N/A'); ?>" readonly>
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-venus-mars"></i> Gender</label>
                                <input type="text" value="<?= ucfirst($user['gender'] ?? 'Not Set'); ?>" readonly>
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-user-shield"></i> Role</label>
                                <input type="text" value="<?= ucfirst($user['role']); ?>" readonly>
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-circle"></i> Status</label>
                                <input type="text" value="<?= ucfirst($user['status']); ?>" readonly>
                            </div>
                        </div>

                        <div class="buttons">
                            <button class="btn btn-success" type="submit">
                                <i class="fas fa-save"></i>
                                Save Changes
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="toggleProfile()">
                                <i class="fas fa-times"></i>
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            let mode = 0;

            function previewImage(input) {
                if (input.files && input.files[0]) {
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById("profilePreview").src = e.target.result;
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }

            function toggleProfile() {
                let btn = document.getElementById("toggleBtn");
                let view = document.getElementById("viewProfile");
                let edit = document.getElementById("editProfile");

                if (mode == 0) {
                    view.style.display = "block";
                    edit.style.display = "none";
                    btn.innerHTML = '<i class="fas fa-edit"></i> Edit Profile';
                    mode = 1;
                } else if (mode == 1) {
                    view.style.display = "none";
                    edit.style.display = "block";
                    btn.innerHTML = '<i class="fas fa-eye"></i> View Profile';
                    mode = 2;
                } else {
                    edit.style.display = "none";
                    view.style.display = "block";
                    btn.innerHTML = '<i class="fas fa-edit"></i> Edit Profile';
                    mode = 1;
                }
            }

            function closeProfile() {
                if (window.parent && window.parent.closeProfileModal) {
                    window.parent.closeProfileModal();
                } else {
                    window.location.href = '<?= $dashboard ?>';
                }
            }

            // Initialize: Show view profile by default
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('viewProfile').style.display = 'block';
                document.getElementById('editProfile').style.display = 'none';
                mode = 1;
            });

            // Handle AJAX form submission for modal
            <?php if($is_modal): ?>
            document.getElementById('profileForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                
                fetch(window.location.href + '&modal=true', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    const container = document.querySelector('.profile-body');
                    
                    if (data.success) {
                        // Show success message
                        const msgDiv = document.createElement('div');
                        msgDiv.className = 'message success';
                        msgDiv.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
                        container.prepend(msgDiv);
                        
                        // Update display name
                        const name = document.getElementById('displayName');
                        name.textContent = document.querySelector('input[name="full_name"]').value;
                        
                        setTimeout(() => {
                            if (window.parent && window.parent.closeProfileModal) {
                                window.parent.location.reload();
                            }
                        }, 1500);
                    } else {
                        const msgDiv = document.createElement('div');
                        msgDiv.className = 'message error';
                        msgDiv.innerHTML = '<i class="fas fa-times-circle"></i> ' + data.message;
                        container.prepend(msgDiv);
                    }
                })
                .catch(error => {
                    alert('Error: ' + error);
                });
            });
            <?php endif; ?>
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
    <title>My Profile | SSARMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* Same styles as modal version, but with sidebar/topbar adjustments */
        :root {
            --primary: #4F46E5;
            --primary-dark: #4338CA;
            --primary-light: #818CF8;
            --secondary: #F59E0B;
            --success: #10B981;
            --danger: #EF4444;
            --dark: #1F2937;
            --gray-50: #F9FAFB;
            --gray-100: #F3F4F6;
            --gray-200: #E5E7EB;
            --gray-300: #D1D5DB;
            --gray-400: #9CA3AF;
            --gray-500: #6B7280;
            --gray-600: #4B5563;
            --gray-700: #374151;
            --gray-800: #1F2937;
            --gray-900: #111827;
            --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
            --shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            --shadow-md: 0 10px 15px -3px rgba(0,0,0,0.1);
            --shadow-lg: 0 20px 25px -5px rgba(0,0,0,0.1);
            --shadow-xl: 0 25px 50px -12px rgba(0,0,0,0.25);
            --radius: 16px;
            --radius-sm: 8px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #EEF2FF 0%, #E0E7FF 100%);
            min-height: 100vh;
        }

        .main-content {
            margin-left: 270px;
            margin-top: 85px;
            padding: 30px;
            transition: var(--transition);
        }

        .profile-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            max-width: 900px;
            margin: 0 auto;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .profile-header {
            background: linear-gradient(135deg, var(--gray-900) 0%, #1E293B 100%);
            color: white;
            padding: 50px 40px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(79, 70, 229, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .profile-header::after {
            content: '';
            position: absolute;
            bottom: -30px;
            left: 0;
            width: 100%;
            height: 60px;
            background: white;
            border-radius: 50% 50% 0 0;
            pointer-events: none;
        }

        .profile-image-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 20px;
        }

        .profile-image {
            width: 150px;
            height: 150px;
            margin: 0 auto;
            border-radius: 50%;
            overflow: hidden;
            border: 5px solid var(--secondary);
            cursor: pointer;
            box-shadow: var(--shadow-lg);
            transition: var(--transition);
            position: relative;
            z-index: 2;
        }

        .profile-image:hover {
            transform: scale(1.05);
            border-color: var(--primary-light);
        }

        .profile-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-image-edit-badge {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: var(--secondary);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            border: 3px solid white;
            box-shadow: var(--shadow);
            z-index: 3;
            transition: var(--transition);
            cursor: pointer;
        }

        .profile-image-edit-badge:hover {
            transform: scale(1.1);
            background: var(--primary);
        }

        .profile-header h2 {
            font-size: 32px;
            font-weight: 800;
            margin-top: 5px;
            letter-spacing: -0.5px;
            position: relative;
            z-index: 2;
        }

        .role-badge {
            display: inline-block;
            margin-top: 12px;
            background: linear-gradient(135deg, var(--secondary), #D97706);
            padding: 8px 25px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            z-index: 2;
            box-shadow: var(--shadow);
        }

        .profile-email {
            margin-top: 15px;
            opacity: 0.9;
            font-size: 15px;
            position: relative;
            z-index: 2;
        }

        .profile-email i {
            margin-right: 8px;
            color: var(--secondary);
        }

        .profile-body {
            padding: 40px 45px;
            background: white;
        }

        .message {
            padding: 16px 20px;
            margin-bottom: 25px;
            border-radius: var(--radius-sm);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideDown 0.4s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message i {
            font-size: 20px;
        }

        .message.success {
            background: linear-gradient(135deg, #ECFDF5, #D1FAE5);
            color: #065F46;
            border-left: 4px solid var(--success);
        }

        .message.error {
            background: linear-gradient(135deg, #FEF2F2, #FEE2E2);
            color: #991B1B;
            border-left: 4px solid var(--danger);
        }

        .action-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            align-items: center;
        }

        .btn {
            padding: 12px 28px;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            text-decoration: none;
            letter-spacing: 0.3px;
            position: relative;
            overflow: hidden;
        }

        .btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            transition: width 0.6s, height 0.6s, top 0.6s, left 0.6s;
        }

        .btn:active::after {
            width: 300px;
            height: 300px;
            top: -100px;
            left: -100px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--secondary), #D97706);
            color: white;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
        }

        .btn-secondary {
            background: var(--gray-100);
            color: var(--gray-700);
        }

        .btn-secondary:hover {
            background: var(--gray-200);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success), #059669);
            color: white;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .info-card {
            background: var(--gray-50);
            padding: 22px 25px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            gap: 18px;
            border: 1px solid var(--gray-200);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .info-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(180deg, var(--primary), var(--primary-light));
            opacity: 0;
            transition: var(--transition);
        }

        .info-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary-light);
        }

        .info-card:hover::before {
            opacity: 1;
        }

        .info-card .icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
            font-size: 20px;
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.2);
        }

        .info-card .icon.green {
            background: linear-gradient(135deg, var(--success), #34D399);
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);
        }

        .info-card .icon.orange {
            background: linear-gradient(135deg, var(--secondary), #FBBF24);
            box-shadow: 0 4px 10px rgba(245, 158, 11, 0.2);
        }

        .info-card .icon.pink {
            background: linear-gradient(135deg, #EC4899, #F472B6);
            box-shadow: 0 4px 10px rgba(236, 72, 153, 0.2);
        }

        .info-card .icon.purple {
            background: linear-gradient(135deg, #8B5CF6, #A78BFA);
            box-shadow: 0 4px 10px rgba(139, 92, 246, 0.2);
        }

        .info-card .icon.red {
            background: linear-gradient(135deg, var(--danger), #F87171);
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.2);
        }

        .info-card .icon.teal {
            background: linear-gradient(135deg, #14B8A6, #5EEAD4);
            box-shadow: 0 4px 10px rgba(20, 184, 166, 0.2);
        }

        .info-card .details {
            display: flex;
            flex-direction: column;
            gap: 2px;
            flex: 1;
        }

        .info-card .label {
            font-size: 11px;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 600;
        }

        .info-card .value {
            font-weight: 600;
            color: var(--gray-800);
            font-size: 15px;
        }

        .status-badge {
            padding: 5px 16px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }

        .status-badge.active {
            background: linear-gradient(135deg, #D1FAE5, #A7F3D0);
            color: #065F46;
        }

        .status-badge.inactive {
            background: linear-gradient(135deg, #FEE2E2, #FCA5A5);
            color: #991B1B;
        }

        .edit-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-top: 25px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-weight: 600;
            color: var(--gray-700);
            font-size: 13px;
            letter-spacing: 0.3px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group label i {
            color: var(--primary);
            width: 18px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 12px 16px;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-sm);
            font-size: 14px;
            transition: var(--transition);
            background: white;
            color: var(--gray-800);
            font-weight: 500;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .form-group input[readonly],
        .form-group select[readonly] {
            background: var(--gray-50);
            color: var(--gray-600);
            cursor: not-allowed;
            border-color: var(--gray-200);
        }

        .form-group input[type="file"] {
            padding: 12px;
            background: var(--gray-50);
            border: 2px dashed var(--gray-300);
            cursor: pointer;
        }

        .form-group input[type="file"]:hover {
            border-color: var(--primary);
            background: var(--gray-100);
        }

        .buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 2px solid var(--gray-100);
        }

        #editProfile {
            display: none;
            animation: fadeIn 0.4s ease-out;
        }

        #viewProfile {
            animation: fadeIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
                margin-top: 70px;
            }

            .profile-header {
                padding: 30px 20px 25px;
            }

            .profile-body {
                padding: 25px 20px;
            }

            .profile-header h2 {
                font-size: 24px;
            }

            .profile-image {
                width: 120px;
                height: 120px;
            }

            .info-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .edit-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .action-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .buttons {
                flex-direction: column;
            }

            .btn {
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .profile-header h2 {
                font-size: 20px;
            }

            .profile-image {
                width: 100px;
                height: 100px;
            }

            .profile-body {
                padding: 20px 15px;
            }

            .info-card {
                padding: 18px 20px;
            }

            .info-card .icon {
                width: 42px;
                height: 42px;
                font-size: 16px;
            }
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--gray-100);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-light);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary);
        }
    </style>
</head>

<body>

<?php 
// Include sidebar and topbar
if(file_exists($sidebar)){
    include($sidebar);
} else {
    $root_sidebar = dirname(__FILE__) . "/" . $sidebar;
    if(file_exists($root_sidebar)){
        include($root_sidebar);
    }
}

if(file_exists($topbar)){
    include($topbar);
} else {
    $root_topbar = dirname(__FILE__) . "/" . $topbar;
    if(file_exists($root_topbar)){
        include($root_topbar);
    }
}
?>

<div class="main-content">

    <div class="profile-card">

        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-image-wrapper">
                <div class="profile-image">
                    <?php
                    if(!empty($user['profile_pic']) && file_exists("uploads/".$user['profile_pic'])){
                        $profilePic = "uploads/".htmlspecialchars($user['profile_pic']);
                    }else{
                        $profilePic = "uploads/default.png";
                    }
                    ?>
                    <img src="<?= $profilePic ?>" id="profilePreview" alt="Profile">
                </div>
                <div class="profile-image-edit-badge" onclick="toggleProfile()" title="Change Profile Picture">
                    <i class="fas fa-camera"></i>
                </div>
            </div>

            <h2 id="displayName"><?= htmlspecialchars($user['full_name']); ?></h2>
            <span class="role-badge"><?= ucfirst($user['role']); ?></span>
            <div class="profile-email">
                <i class="fas fa-envelope"></i>
                <?= htmlspecialchars($user['email']); ?>
            </div>
        </div>

        <!-- Profile Body -->
        <div class="profile-body">

            <?php if(!empty($success)): ?>
                <div class="message success">
                    <i class="fas fa-check-circle"></i>
                    <?= $success; ?>
                </div>
            <?php endif; ?>

            <?php if(!empty($error)): ?>
                <div class="message error">
                    <i class="fas fa-times-circle"></i>
                    <?= $error; ?>
                </div>
            <?php endif; ?>

            <!-- Action Bar -->
            <div class="action-bar">
                <button type="button" class="btn btn-primary" id="toggleBtn" onclick="toggleProfile()">
                    <i class="fas fa-edit"></i>
                    Edit Profile
                </button>
                <a href="<?= $dashboard ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Dashboard
                </a>
            </div>

            <!-- View Profile -->
            <div id="viewProfile">
                <div class="info-grid">
                    <div class="info-card">
                        <div class="icon"><i class="fas fa-user"></i></div>
                        <div class="details">
                            <span class="label">Full Name</span>
                            <span class="value"><?= htmlspecialchars($user['full_name']); ?></span>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="icon green"><i class="fas fa-envelope"></i></div>
                        <div class="details">
                            <span class="label">Email Address</span>
                            <span class="value"><?= htmlspecialchars($user['email']); ?></span>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="icon orange"><i class="fas fa-phone"></i></div>
                        <div class="details">
                            <span class="label">Phone Number</span>
                            <span class="value">
                                <?= !empty($user['phone']) ? htmlspecialchars($user['phone']) : "Not Available"; ?>
                            </span>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="icon purple"><i class="fas fa-user-tag"></i></div>
                        <div class="details">
                            <span class="label">Username</span>
                            <span class="value"><?= htmlspecialchars($user['username'] ?? 'N/A'); ?></span>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="icon pink"><i class="fas fa-venus-mars"></i></div>
                        <div class="details">
                            <span class="label">Gender</span>
                            <span class="value"><?= ucfirst($user['gender'] ?? 'Not Set'); ?></span>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="icon"><i class="fas fa-user-shield"></i></div>
                        <div class="details">
                            <span class="label">Role</span>
                            <span class="value"><?= ucfirst($user['role']); ?></span>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="icon red"><i class="fas fa-circle"></i></div>
                        <div class="details">
                            <span class="label">Status</span>
                            <span class="value">
                                <?php if($user['status']=="active"): ?>
                                    <span class="status-badge active">Active</span>
                                <?php else: ?>
                                    <span class="status-badge inactive">Inactive</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="icon teal"><i class="fas fa-calendar"></i></div>
                        <div class="details">
                            <span class="label">Member Since</span>
                            <span class="value"><?= date("d M Y", strtotime($user['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Profile -->
            <div id="editProfile">
                <form method="POST" enctype="multipart/form-data" id="profileForm">
                    <input type="hidden" name="save_profile" value="1">

                    <div class="edit-grid">
                        <div class="form-group full">
                            <label><i class="fas fa-camera"></i> Profile Picture</label>
                            <input type="file" name="profile_pic" id="profile_pic" accept="image/*" onchange="previewImage(this)">
                            <small style="color: var(--gray-500); font-size: 12px;">Allowed: JPG, JPEG, PNG, GIF (Max 5MB)</small>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-user"></i> Full Name</label>
                            <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-envelope"></i> Email</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-phone"></i> Phone Number</label>
                            <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']); ?>" placeholder="Enter phone number">
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-user-tag"></i> Username</label>
                            <input type="text" value="<?= htmlspecialchars($user['username'] ?? 'N/A'); ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-venus-mars"></i> Gender</label>
                            <input type="text" value="<?= ucfirst($user['gender'] ?? 'Not Set'); ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-user-shield"></i> Role</label>
                            <input type="text" value="<?= ucfirst($user['role']); ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-circle"></i> Status</label>
                            <input type="text" value="<?= ucfirst($user['status']); ?>" readonly>
                        </div>
                    </div>

                    <div class="buttons">
                        <button class="btn btn-success" type="submit">
                            <i class="fas fa-save"></i>
                            Save Changes
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="toggleProfile()">
                            <i class="fas fa-times"></i>
                            Cancel
                        </button>
                    </div>
                </form>
            </div>

        </div>

    </div>

</div>

<script>
    let mode = 0;

    function previewImage(input) {
        if (input.files && input.files[0]) {
            let reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById("profilePreview").src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function toggleProfile() {
        let btn = document.getElementById("toggleBtn");
        let view = document.getElementById("viewProfile");
        let edit = document.getElementById("editProfile");

        if (mode == 0) {
            view.style.display = "block";
            edit.style.display = "none";
            btn.innerHTML = '<i class="fas fa-edit"></i> Edit Profile';
            mode = 1;
        } else if (mode == 1) {
            view.style.display = "none";
            edit.style.display = "block";
            btn.innerHTML = '<i class="fas fa-eye"></i> View Profile';
            mode = 2;
        } else {
            edit.style.display = "none";
            view.style.display = "block";
            btn.innerHTML = '<i class="fas fa-edit"></i> Edit Profile';
            mode = 1;
        }
    }

    // Initialize: Show view profile by default
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('viewProfile').style.display = 'block';
        document.getElementById('editProfile').style.display = 'none';
        mode = 1;
    });
</script>

</body>
</html>