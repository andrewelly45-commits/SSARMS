<?php
session_start();

// ============================================
// FIX: Include audit logger with correct path
// ============================================
// Try multiple paths to find the file
$audit_paths = [
    '../audit_logger.php',      // From auth/ go up to root
    'audit_logger.php',          // If in same folder
    '../includes/audit_logger.php',
    '../../audit_logger.php'
];

$audit_loaded = false;
foreach ($audit_paths as $path) {
    if (file_exists($path)) {
        include $path;
        $audit_loaded = true;
        break;
    }
}

// If audit logger not found, define fallback
if (!$audit_loaded && !function_exists('logAction')) {
    function logAction($action_type, $module, $description, $status = 'success', $affected_id = null, $affected_table = null, $old_values = null, $new_values = null) {
        error_log("AUDIT: [$action_type] [$module] $description");
        return true;
    }
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ============================================
// LOG THE LOGOUT BEFORE DESTROYING SESSION
// ============================================
if (isset($_SESSION['full_name']) && function_exists('logAction')) {
    $user_name = $_SESSION['full_name'];
    $user_id = $_SESSION['user_id'] ?? null;
    $user_role = $_SESSION['role'] ?? 'unknown';
    
    // Log the logout action
    logAction('logout', 'auth', "User logged out: $user_name (Role: $user_role)", 'success', $user_id, 'users');
}

// ============================================
// CLEAR SESSION
// ============================================
$_SESSION = [];

// Unset session variables
session_unset();

// Destroy the session
session_destroy();

// Delete session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirect to login
header("Location: login.php");
exit();
?>