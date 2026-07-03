<?php
// ============================================================
// CONFIGURATION FILE
// ============================================================

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'maziku');
define('DB_PASS', 'maziku25');
define('DB_NAME', 'ssarms_db');

// Base URL - FIXED for your project structure
$base_url = '/projects/SSARMS/';
define('BASE_URL', $base_url);

// Function to get full URL for assets
function asset($path) {
    return BASE_URL . ltrim($path, '/');
}

// Function to redirect with base URL
function redirect($path) {
    header('Location: ' . BASE_URL . ltrim($path, '/'));
    exit();
}
?>