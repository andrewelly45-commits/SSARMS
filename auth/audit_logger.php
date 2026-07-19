<?php
// audit_logger.php - Standalone audit logger
// Location: /var/www/html/projects/SSARMS/audit_logger.php

// Prevent multiple inclusions
if (defined('AUDIT_LOGGER_LOADED')) {
    return;
}
define('AUDIT_LOGGER_LOADED', true);

// ============================================
// DATABASE CONNECTION
// ============================================
// Check if $conn exists, if not, try to include db.php
if (!isset($conn)) {
    // Try different paths
    $paths = ['../db.php', 'db.php', 'config/db.php', '../config/db.php'];
    $found = false;
    foreach ($paths as $path) {
        if (file_exists($path)) {
            include $path;
            $found = true;
            break;
        }
    }
    // If still no connection, try manual connection
    if (!$found || !isset($conn)) {
        $conn = mysqli_connect('localhost', 'root', '', 'school_db');
        if (!$conn) {
            die("Database connection failed: " . mysqli_connect_error());
        }
    }
}

// ============================================
// CREATE TABLE IF NOT EXISTS
// ============================================
$create_table = "CREATE TABLE IF NOT EXISTS audit_logs (
    audit_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL,
    user_name VARCHAR(100) NOT NULL,
    user_role VARCHAR(50) NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    module VARCHAR(50) NOT NULL,
    action_description TEXT NOT NULL,
    status VARCHAR(20) DEFAULT 'success',
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    affected_id INT NULL,
    affected_table VARCHAR(100) NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_user_role (user_role),
    INDEX idx_action_type (action_type),
    INDEX idx_module (module),
    INDEX idx_created_at (created_at),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

mysqli_query($conn, $create_table);

// ============================================
// MAIN FUNCTIONS
// ============================================

function logAction($action_type, $module, $description, $status = 'success', $affected_id = null, $affected_table = null, $old_values = null, $new_values = null) {
    global $conn;
    
    if (!$conn) {
        error_log("Audit Logger: Database connection not available");
        return false;
    }
    
    // Get user information
    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    $user_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : (isset($_SESSION['username']) ? $_SESSION['username'] : 'System');
    $user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'system';
    
    // Get IP address
    $ip_address = $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
    if ($ip_address && strpos($ip_address, ',') !== false) {
        $ip_address = explode(',', $ip_address)[0];
    }
    $ip_address = trim($ip_address);
    
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    // Escape strings
    $action_type = mysqli_real_escape_string($conn, $action_type);
    $module = mysqli_real_escape_string($conn, $module);
    $description = mysqli_real_escape_string($conn, $description);
    $status = mysqli_real_escape_string($conn, $status);
    $user_name = mysqli_real_escape_string($conn, $user_name);
    $user_role = mysqli_real_escape_string($conn, $user_role);
    $ip_address = mysqli_real_escape_string($conn, $ip_address);
    $user_agent = mysqli_real_escape_string($conn, $user_agent);
    
    // Prepare JSON data
    $old_values_json = $old_values ? json_encode($old_values) : null;
    $new_values_json = $new_values ? json_encode($new_values) : null;
    
    $affected_id = $affected_id ? (int)$affected_id : 'NULL';
    $affected_table = $affected_table ? "'" . mysqli_real_escape_string($conn, $affected_table) . "'" : 'NULL';
    $old_values_json = $old_values_json ? "'" . mysqli_real_escape_string($conn, $old_values_json) . "'" : 'NULL';
    $new_values_json = $new_values_json ? "'" . mysqli_real_escape_string($conn, $new_values_json) . "'" : 'NULL';
    
    $query = "INSERT INTO audit_logs (
        user_id, user_name, user_role, action_type, module, 
        action_description, status, ip_address, user_agent,
        affected_id, affected_table, old_values, new_values
    ) VALUES (
        " . ($user_id ?: 'NULL') . ", 
        '$user_name', 
        '$user_role', 
        '$action_type', 
        '$module', 
        '$description', 
        '$status', 
        '$ip_address', 
        '$user_agent',
        $affected_id,
        $affected_table,
        $old_values_json,
        $new_values_json
    )";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        error_log("Audit Logger Error: " . mysqli_error($conn));
        return false;
    }
    
    return true;
}

function getAuditLogs($limit = 50, $offset = 0, $filters = []) {
    global $conn;
    
    if (!$conn) {
        error_log("getAuditLogs: Database connection not available");
        return false;
    }
    
    $where = [];
    if (!empty($filters['action_type'])) {
        $where[] = "action_type = '" . mysqli_real_escape_string($conn, $filters['action_type']) . "'";
    }
    if (!empty($filters['module'])) {
        $where[] = "module = '" . mysqli_real_escape_string($conn, $filters['module']) . "'";
    }
    if (!empty($filters['user_role'])) {
        $where[] = "user_role = '" . mysqli_real_escape_string($conn, $filters['user_role']) . "'";
    }
    if (!empty($filters['user_id'])) {
        $where[] = "user_id = " . (int)$filters['user_id'];
    }
    if (!empty($filters['status'])) {
        $where[] = "status = '" . mysqli_real_escape_string($conn, $filters['status']) . "'";
    }
    if (!empty($filters['date_from'])) {
        $where[] = "DATE(created_at) >= '" . mysqli_real_escape_string($conn, $filters['date_from']) . "'";
    }
    if (!empty($filters['date_to'])) {
        $where[] = "DATE(created_at) <= '" . mysqli_real_escape_string($conn, $filters['date_to']) . "'";
    }
    if (!empty($filters['search'])) {
        $search = mysqli_real_escape_string($conn, $filters['search']);
        $where[] = "(user_name LIKE '%$search%' OR action_description LIKE '%$search%' OR module LIKE '%$search%')";
    }
    
    $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
    
    $query = "SELECT * FROM audit_logs 
              $where_clause 
              ORDER BY created_at DESC 
              LIMIT $limit OFFSET $offset";
    
    return mysqli_query($conn, $query);
}

function getAuditLogCount($filters = []) {
    global $conn;
    
    if (!$conn) {
        error_log("getAuditLogCount: Database connection not available");
        return 0;
    }
    
    $where = [];
    if (!empty($filters['action_type'])) {
        $where[] = "action_type = '" . mysqli_real_escape_string($conn, $filters['action_type']) . "'";
    }
    if (!empty($filters['module'])) {
        $where[] = "module = '" . mysqli_real_escape_string($conn, $filters['module']) . "'";
    }
    if (!empty($filters['user_role'])) {
        $where[] = "user_role = '" . mysqli_real_escape_string($conn, $filters['user_role']) . "'";
    }
    if (!empty($filters['user_id'])) {
        $where[] = "user_id = " . (int)$filters['user_id'];
    }
    if (!empty($filters['status'])) {
        $where[] = "status = '" . mysqli_real_escape_string($conn, $filters['status']) . "'";
    }
    if (!empty($filters['date_from'])) {
        $where[] = "DATE(created_at) >= '" . mysqli_real_escape_string($conn, $filters['date_from']) . "'";
    }
    if (!empty($filters['date_to'])) {
        $where[] = "DATE(created_at) <= '" . mysqli_real_escape_string($conn, $filters['date_to']) . "'";
    }
    if (!empty($filters['search'])) {
        $search = mysqli_real_escape_string($conn, $filters['search']);
        $where[] = "(user_name LIKE '%$search%' OR action_description LIKE '%$search%' OR module LIKE '%$search%')";
    }
    
    $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
    
    $query = "SELECT COUNT(*) as total FROM audit_logs $where_clause";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['total'] ?? 0;
}

function getAuditSummary() {
    global $conn;
    
    if (!$conn) {
        error_log("getAuditSummary: Database connection not available");
        return [
            'total' => 0,
            'today' => 0,
            'week' => 0,
            'month' => 0,
            'top_actions' => [],
            'top_modules' => [],
            'user_activity' => []
        ];
    }
    
    $summary = [
        'total' => 0,
        'today' => 0,
        'week' => 0,
        'month' => 0,
        'top_actions' => [],
        'top_modules' => [],
        'user_activity' => []
    ];
    
    // Total
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM audit_logs");
    $row = mysqli_fetch_assoc($result);
    $summary['total'] = $row['total'] ?? 0;
    
    // Today
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM audit_logs WHERE DATE(created_at) = CURDATE()");
    $row = mysqli_fetch_assoc($result);
    $summary['today'] = $row['total'] ?? 0;
    
    // This week
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM audit_logs WHERE YEARWEEK(created_at) = YEARWEEK(CURDATE())");
    $row = mysqli_fetch_assoc($result);
    $summary['week'] = $row['total'] ?? 0;
    
    // This month
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM audit_logs WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
    $row = mysqli_fetch_assoc($result);
    $summary['month'] = $row['total'] ?? 0;
    
    // Top actions
    $result = mysqli_query($conn, "SELECT action_type, COUNT(*) as count FROM audit_logs GROUP BY action_type ORDER BY count DESC LIMIT 10");
    $summary['top_actions'] = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
    // Top modules
    $result = mysqli_query($conn, "SELECT module, COUNT(*) as count FROM audit_logs GROUP BY module ORDER BY count DESC LIMIT 10");
    $summary['top_modules'] = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
    // User activity
    $result = mysqli_query($conn, "SELECT user_name, user_role, COUNT(*) as count FROM audit_logs GROUP BY user_id ORDER BY count DESC LIMIT 10");
    $summary['user_activity'] = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
    return $summary;
}

function cleanAuditLogs($days = 365) {
    global $conn;
    $days = (int)$days;
    $query = "DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL $days DAY)";
    return mysqli_query($conn, $query);
}

// ============================================
// HELPER FUNCTIONS
// ============================================

function logSuccess($action_type, $module, $description, $affected_id = null, $affected_table = null) {
    return logAction($action_type, $module, $description, 'success', $affected_id, $affected_table);
}

function logFailure($action_type, $module, $description, $affected_id = null, $affected_table = null) {
    return logAction($action_type, $module, $description, 'failed', $affected_id, $affected_table);
}

function logLogin($user_name, $success = true) {
    $status = $success ? 'success' : 'failed';
    $description = $success ? "User logged in: $user_name" : "Failed login attempt: $user_name";
    return logAction('login', 'auth', $description, $status);
}

function logLogout($user_name) {
    return logAction('logout', 'auth', "User logged out: $user_name", 'success');
}

function logUpload($module, $description, $affected_id = null, $affected_table = null, $data = null) {
    return logAction('upload', $module, $description, 'success', $affected_id, $affected_table, null, $data);
}

function logExport($module, $description, $data = null) {
    return logAction('export', $module, $description, 'success', null, null, null, $data);
}

function logImport($module, $description, $data = null) {
    return logAction('import', $module, $description, 'success', null, null, null, $data);
}

function logError($module, $message, $data = null) {
    return logAction('error', $module, $message, 'failed', null, null, null, $data);
}

function logWarning($module, $message, $data = null) {
    return logAction('warning', $module, $message, 'pending', null, null, null, $data);
}

function logSystem($action, $description) {
    return logAction($action, 'system', $description, 'success');
}

// ============================================
// END OF FILE
// ============================================
?>