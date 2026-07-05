<?php
session_start();

$_SESSION = [];

session_unset();
session_destroy();

// In logout.php
include 'audit_functions.php';

logSystemAction(
    $_SESSION['user_id'],
    $_SESSION['role'],
    $_SESSION['full_name'],
    'logout',
    "User logged out",
    'auth',
    'users',
    $_SESSION['user_id']
);

// delete session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

header("Location: login.php");
exit();
?>