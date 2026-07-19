<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../db.php';

// ============================================
// INCLUDE AUDIT LOGGER
// ============================================
if (!function_exists('logAction')) {
    $audit_paths = [
        '../audit_logger.php',
        'audit_logger.php',
        '../includes/audit_logger.php',
        '../../audit_logger.php'
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
            if (isset($conn) && $conn) {
                $user_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'System';
                $user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'system';
                $query = "INSERT INTO audit_logs (user_name, user_role, action_type, module, action_description, status, affected_id, affected_table) 
                          VALUES ('$user_name', '$user_role', '$action_type', '$module', '$description', '$status', " . ($affected_id ? (int)$affected_id : 'NULL') . ", '$affected_table')";
                mysqli_query($conn, $query);
            }
            return true;
        }
    }
}

// CHECK ADMIN
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    if (function_exists('logAction')) {
        logAction('access_denied', 'settings', "Unauthorized access attempt to system settings", 'failed');
    }
    header("Location: ../auth/login.php");
    exit();
}

// Log view
if (function_exists('logAction')) {
    logAction('view', 'settings', "Admin viewed system settings", 'success');
}

$message = "";
$message_type = "";

// Create uploads/logo directory if not exists
if (!is_dir("../uploads/logo")) {
    mkdir("../uploads/logo", 0777, true);
}

// Fetch settings
$query = "SELECT * FROM school_settings LIMIT 1";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    mysqli_query($conn, "INSERT INTO school_settings () VALUES ()");
    $result = mysqli_query($conn, $query);
}

$settings = mysqli_fetch_assoc($result);

// ============================================
// REMOVE LOGO
// ============================================
if (isset($_GET['remove_logo'])) {
    if (!empty($settings['school_logo']) && file_exists("../uploads/logo/" . $settings['school_logo'])) {
        @unlink("../uploads/logo/" . $settings['school_logo']);
        mysqli_query($conn, "UPDATE school_settings SET school_logo = NULL WHERE id = " . $settings['id']);
        $message = "Logo removed successfully.";
        $message_type = "success";
        if (function_exists('logAction')) {
            logAction('delete', 'settings', "Removed school logo", 'success');
        }
        // Refresh settings
        $result = mysqli_query($conn, $query);
        $settings = mysqli_fetch_assoc($result);
    }
}

// ============================================
// SAVE SETTINGS
// ============================================
if (isset($_POST['save_settings'])) {
    // Get old settings for audit
    $old_settings = $settings;
    
    $school_name = mysqli_real_escape_string($conn, $_POST['school_name'] ?? '');
    $school_code = mysqli_real_escape_string($conn, $_POST['school_code'] ?? '');
    $address = mysqli_real_escape_string($conn, $_POST['address'] ?? '');
    $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $motto = mysqli_real_escape_string($conn, $_POST['motto'] ?? '');
    $academic_year = mysqli_real_escape_string($conn, $_POST['academic_year'] ?? date('Y'));
    $current_term = mysqli_real_escape_string($conn, $_POST['current_term'] ?? 'Term I');

    $logo = $settings['school_logo'] ?? null;

    // Handle logo upload
    if (isset($_FILES['school_logo']) && $_FILES['school_logo']['error'] == 0) {
        $targetDir = "../uploads/logo/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $ext = strtolower(pathinfo($_FILES['school_logo']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];

        if (in_array($ext, $allowed)) {
            $filename = "school_logo_" . time() . "." . $ext;
            if (move_uploaded_file($_FILES['school_logo']['tmp_name'], $targetDir . $filename)) {
                // Delete old logo
                if (!empty($settings['school_logo']) && file_exists($targetDir . $settings['school_logo'])) {
                    @unlink($targetDir . $settings['school_logo']);
                }
                $logo = $filename;
            }
        } else {
            $message = "Invalid logo file format. Allowed: JPG, JPEG, PNG, GIF, SVG, WEBP";
            $message_type = "danger";
        }
    }

    if (empty($message)) {
        $sql = "UPDATE school_settings SET
            school_name = '$school_name',
            school_code = '$school_code',
            address = '$address',
            phone = '$phone',
            email = '$email',
            motto = '$motto',
            current_academic_year = '$academic_year',
            current_term = '$current_term',
            school_logo = " . ($logo ? "'$logo'" : "NULL") . "
            WHERE id = " . $settings['id'];

        if (mysqli_query($conn, $sql)) {
            $message = "System settings updated successfully.";
            $message_type = "success";
            
            // Log the update
            if (function_exists('logAction')) {
                logAction('edit', 'settings', "Updated system settings", 'success', $settings['id'], 'school_settings', $old_settings, $_POST);
            }
            
            // Refresh settings
            $result = mysqli_query($conn, $query);
            $settings = mysqli_fetch_assoc($result);
        } else {
            $message = "Failed to save settings: " . mysqli_error($conn);
            $message_type = "danger";
            if (function_exists('logAction')) {
                logAction('error', 'settings', "Failed to save settings: " . mysqli_error($conn), 'failed');
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>System Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f1f5f9; font-family: 'Segoe UI', Tahoma, sans-serif; }
        .main-wrapper { display: flex; min-height: 100vh; }
        .main-content { flex: 1; margin-left: 270px; padding: 85px 30px 30px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .card { border: none; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
        .card-header { background: #1a1a2e !important; border-radius: 16px 16px 0 0 !important; padding: 18px 24px; }
        .card-header h4 { margin: 0; display: flex; align-items: center; gap: 10px; color: white; }
        .card-header h4 i { color: #f59e0b; }
        .form-label { font-weight: 600; font-size: 13px; color: #475569; }
        .form-control, .form-select { border: 2px solid #e2e8f0; border-radius: 8px; padding: 10px 14px; }
        .form-control:focus, .form-select:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
        .btn-primary { background: #2563eb; border: none; padding: 10px 30px; font-weight: 600; border-radius: 8px; }
        .btn-primary:hover { background: #1d4ed8; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(37,99,235,0.3); }
        .btn-danger { background: #dc2626; border: none; padding: 6px 16px; font-weight: 600; border-radius: 8px; font-size: 13px; }
        .btn-danger:hover { background: #b91c1c; }
        .btn-secondary { background: #64748b; border: none; padding: 10px 30px; font-weight: 600; border-radius: 8px; color: white; }
        .btn-secondary:hover { background: #475569; }
        .img-thumbnail { border: 2px solid #e2e8f0; border-radius: 8px; padding: 5px; }
        .section-title { font-size: 14px; font-weight: 700; color: #1e293b; padding: 10px 0; border-bottom: 2px solid #e2e8f0; margin-bottom: 15px; }
        .section-title i { color: #2563eb; margin-right: 8px; }
        .logo-container { display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }
        @media (max-width: 1024px) { .main-content { margin-left: 0; padding: 75px 15px 15px; } }
        @media (max-width: 768px) { .container { padding: 10px; } .logo-container { flex-direction: column; align-items: flex-start; } }
    </style>
</head>
<body>

<div class="main-wrapper">
    <?php include 'admin_sidebar.php'; ?>
    <?php include '../auth/topbar.php'; ?>
    
    <div class="main-content">
        <div class="container">
            <div class="card shadow">
                <div class="card-header">
                    <h4><i class="fas fa-cogs"></i> System Settings</h4>
                </div>
                <div class="card-body">
                    
                    <?php if ($message != "") { ?>
                        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php } ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <!-- School Information -->
                            <div class="col-12">
                                <div class="section-title"><i class="fas fa-school"></i> School Information</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">School Name <span class="text-danger">*</span></label>
                                <input type="text" name="school_name" class="form-control" 
                                    value="<?php echo htmlspecialchars($settings['school_name'] ?? ''); ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">School Code <span class="text-danger">*</span></label>
                                <input type="text" name="school_code" class="form-control" 
                                    value="<?php echo htmlspecialchars($settings['school_code'] ?? ''); ?>" required>
                                <small class="text-muted">Used for generating registration numbers</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" 
                                    value="<?php echo htmlspecialchars($settings['phone'] ?? ''); ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" 
                                    value="<?php echo htmlspecialchars($settings['email'] ?? ''); ?>">
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($settings['address'] ?? ''); ?></textarea>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">School Motto</label>
                                <input type="text" name="motto" class="form-control" 
                                    value="<?php echo htmlspecialchars($settings['motto'] ?? ''); ?>">
                            </div>

                            <!-- Academic Settings -->
                            <div class="col-12 mt-3">
                                <div class="section-title"><i class="fas fa-calendar-alt"></i> Academic Settings</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Academic Year <span class="text-danger">*</span></label>
                                <input type="text" name="academic_year" class="form-control" 
                                    value="<?php echo htmlspecialchars($settings['current_academic_year'] ?? date('Y')); ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Current Term <span class="text-danger">*</span></label>
                                <select name="current_term" class="form-select">
                                    <option value="Term I" <?php echo (($settings['current_term'] ?? 'Term I') == "Term I") ? "selected" : ""; ?>>Term I</option>
                                    <option value="Term II" <?php echo (($settings['current_term'] ?? 'Term I') == "Term II") ? "selected" : ""; ?>>Term II</option>
                                    <option value="Term III" <?php echo (($settings['current_term'] ?? 'Term I') == "Term III") ? "selected" : ""; ?>>Term III</option>
                                </select>
                            </div>

                            <!-- Logo -->
                            <div class="col-12 mt-3">
                                <div class="section-title"><i class="fas fa-image"></i> School Logo</div>
                            </div>

                            <div class="col-12 mb-3">
                                <div class="logo-container">
                                    <div>
                                        <label class="form-label">Upload Logo</label>
                                        <input type="file" name="school_logo" class="form-control" accept="image/*" style="width: 100%; max-width: 400px;">
                                        <small class="text-muted">Recommended: 200x200px. JPG, PNG, GIF, SVG, WEBP</small>
                                    </div>
                                    
                                    <?php if (!empty($settings['school_logo']) && file_exists("../uploads/logo/" . $settings['school_logo'])): ?>
                                        <div>
                                            <img src="../uploads/logo/<?php echo $settings['school_logo']; ?>" 
                                                 width="100" class="img-thumbnail">
                                            <br>
                                            <a href="?remove_logo=1" class="btn btn-danger btn-sm mt-2" onclick="return confirm('Remove logo?')">
                                                <i class="fas fa-trash"></i> Remove Logo
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-muted">
                                            <i class="fas fa-info-circle"></i> No logo uploaded yet
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-primary" name="save_settings">
                                <i class="fas fa-save"></i> Save Settings
                            </button>
                            <a href="system_backup.php" class="btn btn-secondary">
                                <i class="fas fa-database"></i> Backup System
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Auto-hide alerts
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