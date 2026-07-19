<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../db.php";

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
        logAction('access_denied', 'backup', "Unauthorized access attempt to backup system", 'failed');
    }
    header("Location: ../auth/login.php");
    exit();
}

// Log view
if (function_exists('logAction')) {
    logAction('view', 'backup', "Admin viewed backup page", 'success');
}

$backupDir = "../backups/";

if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

$message = "";
$type = "";

// ============================================
// CREATE BACKUP
// ============================================
if (isset($_POST['create_backup'])) {
    // Get database credentials from db.php
    // If you have a config file, use those values
    $dbHost = "localhost";
    $dbUser = "maziku";
    $dbPass = "maziku25";
    $dbName = "ssarms_db";
    
    $filename = "SSARMS_Backup_" . date("Y-m-d_H-i-s") . ".sql";
    $filepath = $backupDir . $filename;
    
    // Build mysqldump command
    $command = "mysqldump --host={$dbHost} --user={$dbUser} ";
    
    if (!empty($dbPass)) {
        $command .= "--password={$dbPass} ";
    }
    
    $command .= "--add-drop-table --complete-insert --skip-comments ";
    $command .= "{$dbName} > " . escapeshellarg($filepath);
    
    // Execute the command
    exec($command . " 2>&1", $output, $result);
    
    if ($result === 0 && file_exists($filepath) && filesize($filepath) > 0) {
        $backup_size = filesize($filepath);
        $size_formatted = formatSize($backup_size);
        
        $message = "Backup created successfully: $filename ($size_formatted)";
        $type = "success";
        
        // Log backup
        if (function_exists('logAction')) {
            logAction('backup', 'system', "Created database backup: $filename ($size_formatted)", 'success');
        }
        
        // Download the file after creation
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit();
    } else {
        $message = "Backup failed! Exit code: " . $result . "<br>";
        $message .= "Error: " . implode("<br>", $output);
        $type = "danger";
        
        if (function_exists('logAction')) {
            logAction('error', 'backup', "Failed to create backup: " . implode(" ", $output), 'failed');
        }
    }
}

// ============================================
// RESTORE BACKUP
// ============================================
if (isset($_POST['restore_backup'])) {
    if (isset($_FILES['backup_file']) && $_FILES['backup_file']['error'] == 0) {
        $tmp = $_FILES['backup_file']['tmp_name'];
        $filename = $_FILES['backup_file']['name'];
        
        // Get database credentials
        $dbHost = "localhost";
        $dbUser = "maziku";
        $dbPass = "maziku25";
        $dbName = "ssarms_db";
        
        // Build mysql command
        $command = "mysql --host={$dbHost} --user={$dbUser} ";
        
        if (!empty($dbPass)) {
            $command .= "--password={$dbPass} ";
        }
        
        $command .= "{$dbName} < " . escapeshellarg($tmp);
        
        exec($command . " 2>&1", $output, $status);
        
        if ($status === 0) {
            $message = "Database restored successfully from: " . $filename;
            $type = "success";
            
            if (function_exists('logAction')) {
                logAction('restore', 'system', "Restored database from: $filename", 'success');
            }
        } else {
            $message = "Restore failed! Error: " . implode("<br>", $output);
            $type = "danger";
            
            if (function_exists('logAction')) {
                logAction('error', 'backup', "Failed to restore from: $filename - " . implode(" ", $output), 'failed');
            }
        }
    } else {
        $message = "Please select a backup file to restore.";
        $type = "danger";
    }
}

// ============================================
// DELETE BACKUP
// ============================================
if (isset($_GET['delete'])) {
    $file = basename($_GET['delete']);
    $filepath = $backupDir . $file;
    
    if (file_exists($filepath)) {
        if (unlink($filepath)) {
            $message = "Backup deleted: $file";
            $type = "success";
            
            if (function_exists('logAction')) {
                logAction('delete', 'system', "Deleted backup: $file", 'success');
            }
        } else {
            $message = "Failed to delete backup!";
            $type = "danger";
        }
    } else {
        $message = "Backup file not found!";
        $type = "danger";
    }
}

// ============================================
// DOWNLOAD BACKUP
// ============================================
if (isset($_GET['download'])) {
    $file = basename($_GET['download']);
    $filepath = $backupDir . $file;
    
    if (file_exists($filepath)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit();
    }
}

// ============================================
// HELPER FUNCTIONS
// ============================================
function formatSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' B';
    }
}

// ============================================
// GET BACKUPS LIST
// ============================================
$backup_files = glob($backupDir . "*.sql");
rsort($backup_files);
?>

<!DOCTYPE html>
<html>
<head>
    <title>System Backup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f1f5f9; font-family: 'Segoe UI', Tahoma, sans-serif; }
        
        .main-wrapper { display: flex; min-height: 100vh; }
        .main-content { flex: 1; margin-left: 270px; padding: 85px 30px 30px; }
        .container { max-width: 1200px; margin: 0 auto; }
        
        .card { border: none; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
        .card-header { background: #1a1a2e !important; border-radius: 16px 16px 0 0 !important; padding: 18px 24px; }
        .card-header h4 { margin: 0; display: flex; align-items: center; gap: 10px; color: white; }
        .card-header h4 i { color: #f59e0b; }
        
        .btn { border: none; padding: 10px 24px; font-weight: 600; border-radius: 8px; transition: 0.3s; }
        .btn:hover { transform: translateY(-2px); }
        .btn-success { background: #16a34a; color: white; }
        .btn-success:hover { background: #15803d; box-shadow: 0 4px 12px rgba(22,163,74,0.3); }
        .btn-warning { background: #f59e0b; color: white; }
        .btn-warning:hover { background: #d97706; box-shadow: 0 4px 12px rgba(245,158,11,0.3); }
        .btn-primary { background: #2563eb; color: white; }
        .btn-primary:hover { background: #1d4ed8; box-shadow: 0 4px 12px rgba(37,99,235,0.3); }
        .btn-danger { background: #dc2626; color: white; }
        .btn-danger:hover { background: #b91c1c; box-shadow: 0 4px 12px rgba(220,38,38,0.3); }
        .btn-sm { padding: 6px 14px; font-size: 12px; }
        
        .alert { padding: 14px 20px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; font-weight: 500; }
        .alert-success { background: #dcfce7; color: #166534; border-left: 4px solid #22c55e; }
        .alert-danger { background: #fee2e2; color: #991b1b; border-left: 4px solid #dc2626; }
        
        .form-control { border: 2px solid #e2e8f0; border-radius: 8px; padding: 10px 14px; }
        .form-control:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
        
        table { width: 100%; border-collapse: collapse; }
        th { background: #1a1a2e; color: white; padding: 12px; text-align: left; font-size: 13px; }
        td { padding: 12px; border-bottom: 1px solid #e2e8f0; font-size: 13px; vertical-align: middle; }
        tr:hover td { background: #f8fafc; }
        
        .empty-state { text-align: center; padding: 40px; color: #94a3b8; }
        .empty-state i { font-size: 48px; display: block; margin-bottom: 15px; color: #cbd5e1; }
        
        .backup-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-top: 15px; }
        .stat-box { background: #f8fafc; padding: 15px; border-radius: 8px; text-align: center; border: 1px solid #e2e8f0; }
        .stat-box .number { font-size: 24px; font-weight: 700; color: #1e293b; }
        .stat-box .label { font-size: 12px; color: #64748b; margin-top: 3px; }
        
        .action-buttons { display: flex; gap: 5px; flex-wrap: wrap; }
        
        @media (max-width: 1024px) { .main-content { margin-left: 0; padding: 75px 15px 15px; } }
        @media (max-width: 768px) { .backup-stats { grid-template-columns: 1fr 1fr; } table { font-size: 12px; } th, td { padding: 8px; } }
        @media (max-width: 480px) { .backup-stats { grid-template-columns: 1fr; } }
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
                    <h4><i class="fas fa-database"></i> System Backup</h4>
                </div>
                <div class="card-body">
                    
                    <?php if ($message != "") { ?>
                        <div class="alert alert-<?php echo $type; ?> alert-dismissible fade show">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php } ?>
                    
                    <!-- Backup Stats -->
                    <div class="backup-stats">
                        <div class="stat-box">
                            <div class="number"><?php echo count($backup_files); ?></div>
                            <div class="label">Total Backups</div>
                        </div>
                        <div class="stat-box">
                            <div class="number">
                                <?php 
                                $last_backup = !empty($backup_files) ? date("d M Y H:i", filemtime($backup_files[0])) : 'Never';
                                echo $last_backup;
                                ?>
                            </div>
                            <div class="label">Last Backup</div>
                        </div>
                        <div class="stat-box">
                            <div class="number">
                                <?php 
                                $total_size = 0;
                                foreach ($backup_files as $file) {
                                    $total_size += filesize($file);
                                }
                                echo formatSize($total_size);
                                ?>
                            </div>
                            <div class="label">Total Size</div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Backup Actions -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <form method="POST">
                                <button class="btn btn-success w-100" name="create_backup">
                                    <i class="fas fa-database"></i> Create & Download Backup
                                </button>
                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-info-circle"></i> Creates a full database backup and downloads it
                                </small>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-2">
                                    <input type="file" name="backup_file" class="form-control" accept=".sql" required>
                                </div>
                                <button class="btn btn-warning w-100" name="restore_backup">
                                    <i class="fas fa-undo"></i> Restore Database
                                </button>
                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-exclamation-triangle" style="color: #f59e0b;"></i> 
                                    <span style="color: #dc2626;">WARNING:</span> This will overwrite ALL current data!
                                </small>
                            </form>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Available Backups -->
                    <h5 class="mb-3"><i class="fas fa-list"></i> Available Backups</h5>
                    
                    <?php if (!empty($backup_files)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Backup File</th>
                                    <th>Size</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $count = 1;
                                foreach ($backup_files as $file) {
                                    $name = basename($file);
                                    $size = filesize($file);
                                    $size_formatted = formatSize($size);
                                    $date = date("d M Y H:i", filemtime($file));
                                ?>
                                    <tr>
                                        <td><?php echo $count++; ?></td>
                                        <td><strong><?php echo $name; ?></strong></td>
                                        <td><?php echo $size_formatted; ?></td>
                                        <td><?php echo $date; ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a class="btn btn-primary btn-sm" href="?download=<?php echo urlencode($name); ?>">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <a class="btn btn-danger btn-sm" 
                                                   onclick="return confirm('Delete this backup: <?php echo $name; ?>?')" 
                                                   href="?delete=<?php echo urlencode($name); ?>">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-database"></i>
                        <p>No backups found. Create your first backup using the button above.</p>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Backup Information -->
                    <div class="mt-4 p-3" style="background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <i class="fas fa-check-circle" style="color: #16a34a;"></i>
                                <strong>What's Backed Up?</strong>
                                <p class="text-muted small">All database tables including users, students, teachers, marks, and results.</p>
                            </div>
                            <div class="col-md-3">
                                <i class="fas fa-folder" style="color: #2563eb;"></i>
                                <strong>Storage Location</strong>
                                <p class="text-muted small">Backups are saved in the <code>/backups/</code> directory.</p>
                            </div>
                            <div class="col-md-3">
                                <i class="fas fa-exclamation-triangle" style="color: #f59e0b;"></i>
                                <strong>Restore Warning</strong>
                                <p class="text-muted small">Restoring will overwrite ALL current data. Always backup first!</p>
                            </div>
                            <div class="col-md-3">
                                <i class="fas fa-shield-alt" style="color: #7c3aed;"></i>
                                <strong>Security</strong>
                                <p class="text-muted small">Only admins can access, create, restore, or delete backups.</p>
                            </div>
                        </div>
                    </div>
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

// Confirm restore
document.querySelector('form[name="restore_form"]')?.addEventListener('submit', function(e) {
    const file = this.querySelector('input[type="file"]');
    if (file.files.length === 0) {
        e.preventDefault();
        alert('Please select a backup file to restore.');
    } else {
        return confirm('WARNING: This will overwrite ALL current data in the database. Are you sure you want to continue?');
    }
});
</script>

</body>
</html>