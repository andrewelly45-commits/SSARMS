<?php
session_start();
include '../db.php';

// ============================================
// INCLUDE AUDIT LOGGER - CHECK IF FILE EXISTS
// ============================================
$audit_paths = [
    '../audit_logger.php',
    'audit_logger.php',
    '../includes/audit_logger.php',
    '../../audit_logger.php',
    dirname(__DIR__) . '/audit_logger.php',
    __DIR__ . '/../audit_logger.php'
];

$audit_loaded = false;
foreach ($audit_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $audit_loaded = true;
        break;
    }
}

// If audit logger not found, define fallback functions
if (!$audit_loaded) {
    // Define fallback functions to prevent errors
    function getAuditLogs($limit = 50, $offset = 0, $filters = []) {
        global $conn;
        return mysqli_query($conn, "SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
    }
    
    function getAuditLogCount($filters = []) {
        global $conn;
        $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM audit_logs");
        $row = mysqli_fetch_assoc($result);
        return $row['total'] ?? 0;
    }
    
    function getAuditSummary() {
        return ['total' => 0, 'today' => 0, 'week' => 0, 'month' => 0, 'top_actions' => [], 'top_modules' => [], 'user_activity' => []];
    }
    
    function cleanAuditLogs($days = 365) {
        global $conn;
        return mysqli_query($conn, "DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL $days DAY)");
    }
    
    function logAction($action_type, $module, $description, $status = 'success', $affected_id = null, $affected_table = null, $old_values = null, $new_values = null) {
        global $conn;
        return true;
    }
}

// Check if functions exist after include
if (!function_exists('getAuditLogs')) {
    die("Error: getAuditLogs function not found. Please check audit_logger.php");
}

/* ================= AUTH CHECK ================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

/* ================= HANDLE FILTERS ================= */
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
$offset = ($page - 1) * $limit;

$filters = [
    'action_type' => isset($_GET['action_type']) ? $_GET['action_type'] : '',
    'module' => isset($_GET['module']) ? $_GET['module'] : '',
    'user_role' => isset($_GET['user_role']) ? $_GET['user_role'] : '',
    'status' => isset($_GET['status']) ? $_GET['status'] : '',
    'date_from' => isset($_GET['date_from']) ? $_GET['date_from'] : '',
    'date_to' => isset($_GET['date_to']) ? $_GET['date_to'] : '',
    'search' => isset($_GET['search']) ? $_GET['search'] : ''
];

// Remove empty filters
$filters = array_filter($filters);

/* ================= GET LOGS ================= */
$logs = getAuditLogs($limit, $offset, $filters);
$total_logs = getAuditLogCount($filters);
$total_pages = ceil($total_logs / $limit);

/* ================= GET SUMMARY ================= */
$summary = getAuditSummary();

/* ================= CLEAN LOGS ================= */
if (isset($_GET['clean'])) {
    $clean_type = $_GET['clean'];
    $deleted_count = 0;
    $message = '';
    
    switch($clean_type) {
        case 'today':
            $query = "DELETE FROM audit_logs WHERE DATE(created_at) = CURDATE()";
            mysqli_query($conn, $query);
            $deleted_count = mysqli_affected_rows($conn);
            $message = "Today's logs ($deleted_count records) have been cleaned.";
            break;
            
        case 'yesterday':
            $query = "DELETE FROM audit_logs WHERE DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
            mysqli_query($conn, $query);
            $deleted_count = mysqli_affected_rows($conn);
            $message = "Yesterday's logs ($deleted_count records) have been cleaned.";
            break;
            
        case 'week':
            $query = "DELETE FROM audit_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND DATE(created_at) < CURDATE()";
            mysqli_query($conn, $query);
            $deleted_count = mysqli_affected_rows($conn);
            $message = "Last 7 days logs ($deleted_count records) have been cleaned.";
            break;
            
        case 'month':
            $query = "DELETE FROM audit_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND DATE(created_at) < CURDATE()";
            mysqli_query($conn, $query);
            $deleted_count = mysqli_affected_rows($conn);
            $message = "Last 30 days logs ($deleted_count records) have been cleaned.";
            break;
            
        case 'old':
            $days = isset($_GET['days']) ? (int)$_GET['days'] : 365;
            $query = "DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL $days DAY)";
            mysqli_query($conn, $query);
            $deleted_count = mysqli_affected_rows($conn);
            $message = "Logs older than $days days ($deleted_count records) have been cleaned.";
            break;
            
        case 'all':
            if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
                $query = "TRUNCATE TABLE audit_logs";
                mysqli_query($conn, $query);
                $deleted_count = mysqli_affected_rows($conn);
                $message = "ALL logs ($deleted_count records) have been permanently deleted!";
            } else {
                $_SESSION['error_msg'] = "Please confirm deletion of ALL logs by adding &confirm=yes";
                header("Location: system_history.php");
                exit();
            }
            break;
            
        case 'filtered':
            $where_conditions = [];
            
            if (!empty($filters['action_type'])) {
                $where_conditions[] = "action_type = '" . mysqli_real_escape_string($conn, $filters['action_type']) . "'";
            }
            if (!empty($filters['module'])) {
                $where_conditions[] = "module = '" . mysqli_real_escape_string($conn, $filters['module']) . "'";
            }
            if (!empty($filters['user_role'])) {
                $where_conditions[] = "user_role = '" . mysqli_real_escape_string($conn, $filters['user_role']) . "'";
            }
            if (!empty($filters['status'])) {
                $where_conditions[] = "status = '" . mysqli_real_escape_string($conn, $filters['status']) . "'";
            }
            if (!empty($filters['date_from'])) {
                $where_conditions[] = "DATE(created_at) >= '" . mysqli_real_escape_string($conn, $filters['date_from']) . "'";
            }
            if (!empty($filters['date_to'])) {
                $where_conditions[] = "DATE(created_at) <= '" . mysqli_real_escape_string($conn, $filters['date_to']) . "'";
            }
            if (!empty($filters['search'])) {
                $search = mysqli_real_escape_string($conn, $filters['search']);
                $where_conditions[] = "(user_name LIKE '%$search%' OR action_description LIKE '%$search%' OR module LIKE '%$search%')";
            }
            
            if (!empty($where_conditions)) {
                $where_clause = "WHERE " . implode(" AND ", $where_conditions);
                $query = "DELETE FROM audit_logs $where_clause";
                mysqli_query($conn, $query);
                $deleted_count = mysqli_affected_rows($conn);
                $message = "Filtered logs ($deleted_count records) have been cleaned.";
            } else {
                $_SESSION['error_msg'] = "No filters applied. Please apply filters first.";
                header("Location: system_history.php");
                exit();
            }
            break;
            
        default:
            $_SESSION['error_msg'] = "Invalid clean option.";
            header("Location: system_history.php");
            exit();
    }
    
    logAction('clean', 'system', $message, 'success');
    $_SESSION['success_msg'] = $message;
    header("Location: system_history.php");
    exit();
}

// Log that user viewed the system history
logAction('view', 'system', 'Viewed system history page', 'success');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System History & Audit Log</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f1f5f9; }
        .main { margin-left: 270px; margin-top: 85px; padding: 30px; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px; }
        .page-header h1 { color: #1e293b; font-size: 28px; display: flex; align-items: center; gap: 12px; }
        .page-header h1 i { color: #074591; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); text-align: center; transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-2px); }
        .stat-card .number { font-size: 32px; font-weight: bold; color: #1e293b; }
        .stat-card .label { color: #64748b; font-size: 14px; margin-top: 5px; }
        .stat-card .icon { font-size: 28px; margin-bottom: 8px; }
        
        .card { background: white; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 25px; overflow: hidden; }
        .card-header { padding: 18px 24px; border-bottom: 1px solid #eef2f6; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
        .card-header h2 { font-size: 18px; color: #1e293b; display: flex; align-items: center; gap: 10px; }
        .card-body { padding: 24px; }
        
        .clean-section { background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; margin-top: 10px; }
        .clean-section .title { font-weight: 600; color: #1e293b; margin-bottom: 8px; font-size: 13px; }
        .clean-section .title i { color: #dc2626; }
        .clean-options { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
        .clean-options .btn { font-size: 12px; padding: 5px 14px; }
        
        .btn { padding: 8px 20px; border-radius: 30px; border: none; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s; text-decoration: none; font-size: 14px; }
        .btn-primary { background: #074591; color: white; }
        .btn-primary:hover { background: #05306a; }
        .btn-secondary { background: #e2e8f0; color: #1e293b; }
        .btn-secondary:hover { background: #cbd5e1; }
        .btn-danger { background: #dc2626; color: white; }
        .btn-danger:hover { background: #b91c1c; }
        .btn-success { background: #16a34a; color: white; }
        .btn-success:hover { background: #15803d; }
        .btn-warning { background: #d97706; color: white; }
        .btn-warning:hover { background: #b45309; }
        .btn-sm { padding: 4px 12px; font-size: 12px; }
        
        .alert { padding: 12px 18px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .alert-success { background: #dcfce7; color: #166534; border-left: 4px solid #22c55e; }
        .alert-error { background: #fee2e2; color: #991b1b; border-left: 4px solid #ef4444; }
        
        .badge { display: inline-flex; align-items: center; gap: 4px; padding: 4px 12px; border-radius: 30px; font-size: 11px; font-weight: 600; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-info { background: #dbeafe; color: #1d4ed8; }
        
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8fafc; padding: 12px 14px; text-align: left; font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; white-space: nowrap; }
        td { padding: 12px 14px; border-bottom: 1px solid #eef2f6; font-size: 13px; vertical-align: middle; }
        tr:hover { background: #f8fafc; }
        
        .pagination { display: flex; justify-content: center; gap: 8px; margin-top: 20px; flex-wrap: wrap; }
        .pagination a, .pagination span { padding: 8px 16px; border-radius: 8px; text-decoration: none; color: #1e293b; border: 1px solid #e2e8f0; transition: all 0.2s; }
        .pagination a:hover { background: #f1f5f9; }
        .pagination .active { background: #074591; color: white; border-color: #074591; }
        
        .filter-form { display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end; margin-bottom: 20px; }
        .filter-group { flex: 1; min-width: 150px; }
        .filter-group label { display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px; }
        .filter-group input, .filter-group select { width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; background: white; }
        
        @media (max-width: 768px) {
            .main { margin-left: 0; padding: 15px; }
            .filter-form { flex-direction: column; }
            .filter-group { min-width: 100%; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .clean-options { flex-direction: column; }
            .clean-options .btn { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>
<?php include '../auth/topbar.php'; ?>

<div class="main">
    <div class="page-header">
        <h1><i class="fas fa-history"></i> System History</h1>
        <div>
            <a href="system_history.php" class="btn btn-secondary btn-sm">
                <i class="fas fa-sync"></i> Refresh
            </a>
        </div>
    </div>

    <?php if(isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success_msg']) ?>
            <?php unset($_SESSION['success_msg']); ?>
        </div>
    <?php endif; ?>

    <?php if(isset($_SESSION['error_msg'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error_msg']) ?>
            <?php unset($_SESSION['error_msg']); ?>
        </div>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="icon"><i class="fas fa-list" style="color:#074591;"></i></div>
            <div class="number"><?= number_format($summary['total'] ?? 0) ?></div>
            <div class="label">Total Activities</div>
        </div>
        <div class="stat-card">
            <div class="icon"><i class="fas fa-calendar-day" style="color:#16a34a;"></i></div>
            <div class="number"><?= number_format($summary['today'] ?? 0) ?></div>
            <div class="label">Today</div>
        </div>
        <div class="stat-card">
            <div class="icon"><i class="fas fa-calendar-week" style="color:#d97706;"></i></div>
            <div class="number"><?= number_format($summary['week'] ?? 0) ?></div>
            <div class="label">This Week</div>
        </div>
        <div class="stat-card">
            <div class="icon"><i class="fas fa-users" style="color:#7c3aed;"></i></div>
            <div class="number"><?= number_format($total_logs) ?></div>
            <div class="label">Filtered Results</div>
        </div>
    </div>

    <!-- Clean Logs Section -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-broom"></i> Clean Logs</h2>
        </div>
        <div class="card-body">
            <div class="clean-section">
                <div class="title"><i class="fas fa-exclamation-triangle"></i> Clean Audit Logs</div>
                <p class="text-muted" style="font-size: 13px; margin-bottom: 10px;">
                    <i class="fas fa-info-circle"></i> Select which logs to delete. <span style="color: #991b1b; font-weight: 600;">Warning: This action cannot be undone!</span>
                </p>
                
                <div class="clean-options">
                    <a href="?clean=today" class="btn btn-warning btn-sm" onclick="return confirm('Delete ALL logs from today?')">
                        <i class="fas fa-calendar-day"></i> Today
                    </a>
                    <a href="?clean=yesterday" class="btn btn-warning btn-sm" onclick="return confirm('Delete ALL logs from yesterday?')">
                        <i class="fas fa-calendar-minus"></i> Yesterday
                    </a>
                    <a href="?clean=week" class="btn btn-warning btn-sm" onclick="return confirm('Delete ALL logs from the last 7 days?')">
                        <i class="fas fa-calendar-week"></i> Last 7 Days
                    </a>
                    <a href="?clean=month" class="btn btn-warning btn-sm" onclick="return confirm('Delete ALL logs from the last 30 days?')">
                        <i class="fas fa-calendar-alt"></i> Last 30 Days
                    </a>
                    <a href="?clean=old&days=365" class="btn btn-warning btn-sm" onclick="return confirm('Delete logs older than 365 days?')">
                        <i class="fas fa-history"></i> Older than 365 Days
                    </a>
                    <?php if(!empty($filters)): ?>
                        <a href="?clean=filtered" class="btn btn-primary btn-sm" onclick="return confirm('Delete ONLY the currently filtered logs?')">
                            <i class="fas fa-filter"></i> Filtered Logs
                        </a>
                    <?php endif; ?>
                    <a href="?clean=all&confirm=yes" class="btn btn-danger btn-sm" onclick="return confirm('⚠️ DELETE ALL LOGS? This will permanently remove ALL audit records! Are you sure?')">
                        <i class="fas fa-trash-alt"></i> Delete ALL Logs
                    </a>
                </div>
                
                <div style="margin-top: 10px; padding: 10px; background: #fef2f2; border-radius: 6px; border-left: 3px solid #dc2626;">
                    <small style="color: #991b1b;">
                        <i class="fas fa-exclamation-circle"></i>
                        <strong>Total logs available:</strong> <?= number_format($summary['total'] ?? 0) ?> records
                        <?php if(!empty($filters)): ?>
                            | <strong>Filtered logs:</strong> <?= number_format($total_logs) ?> records
                        <?php endif; ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-filter"></i> Filters</h2>
            <a href="system_history.php" class="btn btn-secondary btn-sm">Clear Filters</a>
        </div>
        <div class="card-body">
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label>Action Type</label>
                    <select name="action_type">
                        <option value="">All Actions</option>
                        <option value="add" <?= isset($_GET['action_type']) && $_GET['action_type'] == 'add' ? 'selected' : '' ?>>Add</option>
                        <option value="edit" <?= isset($_GET['action_type']) && $_GET['action_type'] == 'edit' ? 'selected' : '' ?>>Edit</option>
                        <option value="delete" <?= isset($_GET['action_type']) && $_GET['action_type'] == 'delete' ? 'selected' : '' ?>>Delete</option>
                        <option value="upload" <?= isset($_GET['action_type']) && $_GET['action_type'] == 'upload' ? 'selected' : '' ?>>Upload</option>
                        <option value="approve" <?= isset($_GET['action_type']) && $_GET['action_type'] == 'approve' ? 'selected' : '' ?>>Approve</option>
                        <option value="login" <?= isset($_GET['action_type']) && $_GET['action_type'] == 'login' ? 'selected' : '' ?>>Login</option>
                        <option value="logout" <?= isset($_GET['action_type']) && $_GET['action_type'] == 'logout' ? 'selected' : '' ?>>Logout</option>
                        <option value="view" <?= isset($_GET['action_type']) && $_GET['action_type'] == 'view' ? 'selected' : '' ?>>View</option>
                        <option value="assign" <?= isset($_GET['action_type']) && $_GET['action_type'] == 'assign' ? 'selected' : '' ?>>Assign</option>
                        <option value="remove" <?= isset($_GET['action_type']) && $_GET['action_type'] == 'remove' ? 'selected' : '' ?>>Remove</option>
                        <option value="clean" <?= isset($_GET['action_type']) && $_GET['action_type'] == 'clean' ? 'selected' : '' ?>>Clean</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Module</label>
                    <select name="module">
                        <option value="">All Modules</option>
                        <option value="auth" <?= isset($_GET['module']) && $_GET['module'] == 'auth' ? 'selected' : '' ?>>Authentication</option>
                        <option value="dashboard" <?= isset($_GET['module']) && $_GET['module'] == 'dashboard' ? 'selected' : '' ?>>Dashboard</option>
                        <option value="teachers" <?= isset($_GET['module']) && $_GET['module'] == 'teachers' ? 'selected' : '' ?>>Teachers</option>
                        <option value="students" <?= isset($_GET['module']) && $_GET['module'] == 'students' ? 'selected' : '' ?>>Students</option>
                        <option value="classes" <?= isset($_GET['module']) && $_GET['module'] == 'classes' ? 'selected' : '' ?>>Classes</option>
                        <option value="subjects" <?= isset($_GET['module']) && $_GET['module'] == 'subjects' ? 'selected' : '' ?>>Subjects</option>
                        <option value="marks" <?= isset($_GET['module']) && $_GET['module'] == 'marks' ? 'selected' : '' ?>>Marks</option>
                        <option value="results" <?= isset($_GET['module']) && $_GET['module'] == 'results' ? 'selected' : '' ?>>Results</option>
                        <option value="system" <?= isset($_GET['module']) && $_GET['module'] == 'system' ? 'selected' : '' ?>>System</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>User Role</label>
                    <select name="user_role">
                        <option value="">All Roles</option>
                        <option value="admin" <?= isset($_GET['user_role']) && $_GET['user_role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="teacher" <?= isset($_GET['user_role']) && $_GET['user_role'] == 'teacher' ? 'selected' : '' ?>>Teacher</option>
                        <option value="academic" <?= isset($_GET['user_role']) && $_GET['user_role'] == 'academic' ? 'selected' : '' ?>>Academic</option>
                        <option value="student" <?= isset($_GET['user_role']) && $_GET['user_role'] == 'student' ? 'selected' : '' ?>>Student</option>
                        <option value="system" <?= isset($_GET['user_role']) && $_GET['user_role'] == 'system' ? 'selected' : '' ?>>System</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="success" <?= isset($_GET['status']) && $_GET['status'] == 'success' ? 'selected' : '' ?>>Success</option>
                        <option value="failed" <?= isset($_GET['status']) && $_GET['status'] == 'failed' ? 'selected' : '' ?>>Failed</option>
                        <option value="pending" <?= isset($_GET['status']) && $_GET['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Date From</label>
                    <input type="date" name="date_from" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
                </div>

                <div class="filter-group">
                    <label>Date To</label>
                    <input type="date" name="date_to" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
                </div>

                <div class="filter-group" style="flex: 2;">
                    <label>Search</label>
                    <input type="text" name="search" placeholder="Search by user, description..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                </div>

                <div class="filter-group" style="flex: 0 0 auto;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card">
        <div class="card-header">
            <h2>
                <i class="fas fa-list-ul"></i> Activity Log
                <span class="badge badge-info"><?= number_format($total_logs) ?> records</span>
            </h2>
            <div>
                <span class="text-muted">Showing <?= min($limit, $total_logs - $offset) ?> of <?= number_format($total_logs) ?></span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Module</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Date/Time</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($logs && mysqli_num_rows($logs) > 0): ?>
                            <?php 
                            $counter = $offset + 1;
                            while($log = mysqli_fetch_assoc($logs)): 
                                $role_badge = 'badge-info';
                                switch($log['user_role']) {
                                    case 'admin': $role_badge = 'badge-admin'; break;
                                    case 'teacher': $role_badge = 'badge-teacher'; break;
                                    case 'academic': $role_badge = 'badge-academic'; break;
                                    case 'student': $role_badge = 'badge-student'; break;
                                }
                            ?>
                                <tr>
                                    <td><?= $counter++ ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($log['user_name']) ?></strong>
                                        <br>
                                        <span class="badge <?= $role_badge ?>"><?= ucfirst($log['user_role']) ?></span>
                                    </td>
                                    <td>
                                        <span style="font-weight: 500;"><?= ucfirst(str_replace('_', ' ', $log['action_type'])) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge badge-info"><?= ucfirst($log['module']) ?></span>
                                    </td>
                                    <td style="max-width: 250px;">
                                        <span title="<?= htmlspecialchars($log['action_description']) ?>">
                                            <?= htmlspecialchars(substr($log['action_description'], 0, 60)) ?>
                                            <?= strlen($log['action_description']) > 60 ? '...' : '' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $log['status'] == 'success' ? 'badge-success' : ($log['status'] == 'failed' ? 'badge-danger' : 'badge-warning') ?>">
                                            <i class="fas <?= $log['status'] == 'success' ? 'fa-check-circle' : ($log['status'] == 'failed' ? 'fa-exclamation-circle' : 'fa-clock') ?>"></i>
                                            <?= ucfirst($log['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= date('d M Y', strtotime($log['created_at'])) ?>
                                        <br>
                                        <span class="text-muted"><?= date('H:i:s', strtotime($log['created_at'])) ?></span>
                                    </td>
                                    <td>
                                        <button class="btn btn-primary btn-sm" onclick="viewDetails(<?= htmlspecialchars(json_encode($log)) ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 40px;">
                                    <i class="fas fa-inbox" style="font-size: 40px; color: #cbd5e1; display: block; margin-bottom: 10px;"></i>
                                    <p style="color: #94a3b8;">No activity logs found</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if($total_pages > 1): ?>
                <div class="pagination">
                    <?php if($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>&<?= http_build_query(array_diff_key($_GET, ['page' => ''])) ?>">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>

                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if($i == $page): ?>
                            <span class="active"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?page=<?= $i ?>&<?= http_build_query(array_diff_key($_GET, ['page' => ''])) ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?>&<?= http_build_query(array_diff_key($_GET, ['page' => ''])) ?>">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div id="detailsModal" class="modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
    <div class="modal-content" style="background:white;border-radius:16px;max-width:600px;width:90%;max-height:80vh;overflow-y:auto;padding:30px;">
        <div class="modal-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h3><i class="fas fa-info-circle"></i> Activity Details</h3>
            <button class="modal-close" onclick="closeModal()" style="background:none;border:none;font-size:24px;cursor:pointer;color:#94a3b8;">&times;</button>
        </div>
        <div id="modalBody"></div>
    </div>
</div>

<script>
function viewDetails(log) {
    const modal = document.getElementById('detailsModal');
    const body = document.getElementById('modalBody');
    
    let html = `
        <div class="detail-row" style="display:flex;padding:8px 0;border-bottom:1px solid #eef2f6;">
            <span class="detail-label" style="font-weight:600;color:#64748b;width:120px;flex-shrink:0;">ID</span>
            <span class="detail-value" style="color:#1e293b;word-break:break-word;">#${log.audit_id}</span>
        </div>
        <div class="detail-row" style="display:flex;padding:8px 0;border-bottom:1px solid #eef2f6;">
            <span class="detail-label" style="font-weight:600;color:#64748b;width:120px;flex-shrink:0;">User</span>
            <span class="detail-value" style="color:#1e293b;word-break:break-word;">${log.user_name} <span class="badge badge-info">${log.user_role}</span></span>
        </div>
        <div class="detail-row" style="display:flex;padding:8px 0;border-bottom:1px solid #eef2f6;">
            <span class="detail-label" style="font-weight:600;color:#64748b;width:120px;flex-shrink:0;">Action</span>
            <span class="detail-value" style="color:#1e293b;word-break:break-word;">${log.action_type.replace(/_/g, ' ').toUpperCase()}</span>
        </div>
        <div class="detail-row" style="display:flex;padding:8px 0;border-bottom:1px solid #eef2f6;">
            <span class="detail-label" style="font-weight:600;color:#64748b;width:120px;flex-shrink:0;">Module</span>
            <span class="detail-value" style="color:#1e293b;word-break:break-word;">${log.module}</span>
        </div>
        <div class="detail-row" style="display:flex;padding:8px 0;border-bottom:1px solid #eef2f6;">
            <span class="detail-label" style="font-weight:600;color:#64748b;width:120px;flex-shrink:0;">Description</span>
            <span class="detail-value" style="color:#1e293b;word-break:break-word;">${log.action_description}</span>
        </div>
        <div class="detail-row" style="display:flex;padding:8px 0;border-bottom:1px solid #eef2f6;">
            <span class="detail-label" style="font-weight:600;color:#64748b;width:120px;flex-shrink:0;">Status</span>
            <span class="detail-value" style="color:#1e293b;word-break:break-word;"><span class="badge ${log.status == 'success' ? 'badge-success' : (log.status == 'failed' ? 'badge-danger' : 'badge-warning')}">${log.status}</span></span>
        </div>
        <div class="detail-row" style="display:flex;padding:8px 0;border-bottom:1px solid #eef2f6;">
            <span class="detail-label" style="font-weight:600;color:#64748b;width:120px;flex-shrink:0;">Date/Time</span>
            <span class="detail-value" style="color:#1e293b;word-break:break-word;">${new Date(log.created_at).toLocaleString()}</span>
        </div>
        <div class="detail-row" style="display:flex;padding:8px 0;border-bottom:1px solid #eef2f6;">
            <span class="detail-label" style="font-weight:600;color:#64748b;width:120px;flex-shrink:0;">IP Address</span>
            <span class="detail-value" style="color:#1e293b;word-break:break-word;">${log.ip_address || 'N/A'}</span>
        </div>
    `;
    
    // Show affected table and ID if present
    if (log.affected_table) {
        html += `
            <div class="detail-row" style="display:flex;padding:8px 0;border-bottom:1px solid #eef2f6;">
                <span class="detail-label" style="font-weight:600;color:#64748b;width:120px;flex-shrink:0;">Affected Table</span>
                <span class="detail-value" style="color:#1e293b;word-break:break-word;">${log.affected_table}</span>
            </div>
        `;
    }
    if (log.affected_id) {
        html += `
            <div class="detail-row" style="display:flex;padding:8px 0;border-bottom:1px solid #eef2f6;">
                <span class="detail-label" style="font-weight:600;color:#64748b;width:120px;flex-shrink:0;">Affected ID</span>
                <span class="detail-value" style="color:#1e293b;word-break:break-word;">${log.affected_id}</span>
            </div>
        `;
    }
    
    // Show old values if present
    if (log.old_values && log.old_values != 'null') {
        try {
            const oldData = JSON.parse(log.old_values);
            html += `
                <div class="detail-row" style="display:flex;padding:8px 0;border-bottom:1px solid #eef2f6;">
                    <span class="detail-label" style="font-weight:600;color:#64748b;width:120px;flex-shrink:0;">Old Values</span>
                    <span class="detail-value" style="color:#1e293b;word-break:break-word;"><div class="json-view" style="background:#f8fafc;padding:10px;border-radius:8px;font-family:monospace;font-size:12px;overflow-x:auto;white-space:pre-wrap;max-height:200px;overflow-y:auto;">${JSON.stringify(oldData, null, 2)}</div></span>
                </div>
            `;
        } catch(e) {}
    }
    
    // Show new values if present
    if (log.new_values && log.new_values != 'null') {
        try {
            const newData = JSON.parse(log.new_values);
            html += `
                <div class="detail-row" style="display:flex;padding:8px 0;border-bottom:1px solid #eef2f6;">
                    <span class="detail-label" style="font-weight:600;color:#64748b;width:120px;flex-shrink:0;">New Values</span>
                    <span class="detail-value" style="color:#1e293b;word-break:break-word;"><div class="json-view" style="background:#f8fafc;padding:10px;border-radius:8px;font-family:monospace;font-size:12px;overflow-x:auto;white-space:pre-wrap;max-height:200px;overflow-y:auto;">${JSON.stringify(newData, null, 2)}</div></span>
                </div>
            `;
        } catch(e) {}
    }
    
    body.innerHTML = html;
    modal.style.display = 'flex';
}

function closeModal() {
    document.getElementById('detailsModal').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('detailsModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}

// Auto-hide alerts
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s ease';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
}, 500);
</script>

<?php include '../footer.php'; ?>
</body>
</html>