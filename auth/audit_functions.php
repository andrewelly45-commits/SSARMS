<?php
/**
 * System Audit Log Functions
 * This file contains functions for logging system activities
 */

/**
 * Log an action to the system audit log
 */
function logSystemAction($user_id, $user_role, $user_name, $action_type, $action_description, $module, $affected_table = null, $affected_id = null, $old_values = null, $new_values = null, $status = 'success') {
    global $conn;
    
    // Check if user_id is set and valid
    if (empty($user_id)) {
        error_log("Audit log error: User ID is empty");
        return false;
    }
    
    // Sanitize inputs with null checking
    $user_id = mysqli_real_escape_string($conn, $user_id ?? 0);
    $user_role = mysqli_real_escape_string($conn, $user_role ?? 'unknown');
    $user_name = mysqli_real_escape_string($conn, $user_name ?? 'Unknown User');
    $action_type = mysqli_real_escape_string($conn, $action_type ?? 'unknown');
    $action_description = mysqli_real_escape_string($conn, $action_description ?? '');
    $module = mysqli_real_escape_string($conn, $module ?? 'unknown');
    
    // Fix: Properly handle affected_table as a string value
    $affected_table = $affected_table ? "'" . mysqli_real_escape_string($conn, $affected_table) . "'" : 'NULL';
    $affected_id = $affected_id ? (int)$affected_id : 'NULL';
    $old_values = $old_values ? "'" . mysqli_real_escape_string($conn, json_encode($old_values)) . "'" : 'NULL';
    $new_values = $new_values ? "'" . mysqli_real_escape_string($conn, json_encode($new_values)) . "'" : 'NULL';
    $status = mysqli_real_escape_string($conn, $status ?? 'success');
    
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    // Prepare the query with proper quoting
    $query = "INSERT INTO system_audit_log 
              (user_id, user_role, user_name, action_type, action_description, module, ip_address, user_agent, affected_table, affected_id, old_values, new_values, status) 
              VALUES 
              ('$user_id', '$user_role', '$user_name', '$action_type', '$action_description', '$module', '$ip_address', '$user_agent', $affected_table, $affected_id, $old_values, $new_values, '$status')";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        error_log("Audit log error: " . mysqli_error($conn));
        error_log("Query: " . $query);
        return false;
    }
    
    return true;
}

/**
 * Get recent audit logs with filters
 */
function getAuditLogs($limit = 100, $offset = 0, $filters = []) {
    global $conn;
    
    $where = [];
    
    if (!empty($filters['action_type'])) {
        $where[] = "action_type = '" . mysqli_real_escape_string($conn, $filters['action_type']) . "'";
    }
    if (!empty($filters['module'])) {
        $where[] = "module = '" . mysqli_real_escape_string($conn, $filters['module']) . "'";
    }
    if (!empty($filters['user_id'])) {
        $where[] = "user_id = " . (int)$filters['user_id'];
    }
    if (!empty($filters['user_role'])) {
        $where[] = "user_role = '" . mysqli_real_escape_string($conn, $filters['user_role']) . "'";
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
    
    $query = "SELECT * FROM system_audit_log 
              $where_clause 
              ORDER BY created_at DESC 
              LIMIT $limit OFFSET $offset";
    
    return mysqli_query($conn, $query);
}

/**
 * Get audit log count for pagination
 */
function getAuditLogCount($filters = []) {
    global $conn;
    
    $where = [];
    
    if (!empty($filters['action_type'])) {
        $where[] = "action_type = '" . mysqli_real_escape_string($conn, $filters['action_type']) . "'";
    }
    if (!empty($filters['module'])) {
        $where[] = "module = '" . mysqli_real_escape_string($conn, $filters['module']) . "'";
    }
    if (!empty($filters['user_id'])) {
        $where[] = "user_id = " . (int)$filters['user_id'];
    }
    if (!empty($filters['user_role'])) {
        $where[] = "user_role = '" . mysqli_real_escape_string($conn, $filters['user_role']) . "'";
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
    
    $query = "SELECT COUNT(*) as total FROM system_audit_log $where_clause";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['total'] ?? 0;
}

/**
 * Get action types for filter dropdown
 */
function getActionTypes() {
    global $conn;
    $query = "SELECT DISTINCT action_type FROM system_audit_log ORDER BY action_type ASC";
    return mysqli_query($conn, $query);
}

/**
 * Get modules for filter dropdown
 */
function getModules() {
    global $conn;
    $query = "SELECT DISTINCT module FROM system_audit_log ORDER BY module ASC";
    return mysqli_query($conn, $query);
}

/**
 * Clean old audit logs (older than specified days)
 */
function cleanAuditLogs($days = 365) {
    global $conn;
    $query = "DELETE FROM system_audit_log WHERE created_at < DATE_SUB(NOW(), INTERVAL $days DAY)";
    return mysqli_query($conn, $query);
}

/**
 * Get summary statistics for dashboard
 */
function getAuditSummary() {
    global $conn;
    
    $stats = [];
    
    // Total logs
    $query = "SELECT COUNT(*) as total FROM system_audit_log";
    $result = mysqli_query($conn, $query);
    $stats['total'] = mysqli_fetch_assoc($result)['total'] ?? 0;
    
    // Today's logs
    $query = "SELECT COUNT(*) as today FROM system_audit_log WHERE DATE(created_at) = CURDATE()";
    $result = mysqli_query($conn, $query);
    $stats['today'] = mysqli_fetch_assoc($result)['today'] ?? 0;
    
    // This week
    $query = "SELECT COUNT(*) as week FROM system_audit_log WHERE YEARWEEK(created_at) = YEARWEEK(CURDATE())";
    $result = mysqli_query($conn, $query);
    $stats['week'] = mysqli_fetch_assoc($result)['week'] ?? 0;
    
    // By action type
    $query = "SELECT action_type, COUNT(*) as count FROM system_audit_log GROUP BY action_type ORDER BY count DESC LIMIT 5";
    $result = mysqli_query($conn, $query);
    $stats['top_actions'] = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $stats['top_actions'][] = $row;
    }
    
    // By module
    $query = "SELECT module, COUNT(*) as count FROM system_audit_log GROUP BY module ORDER BY count DESC LIMIT 5";
    $result = mysqli_query($conn, $query);
    $stats['top_modules'] = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $stats['top_modules'][] = $row;
    }
    
    return $stats;
}
?>