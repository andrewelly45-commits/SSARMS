<?php
session_start();
include '../db.php';

// ============================================
// FIX: Include audit logger with correct path
// ============================================
// Try multiple paths to find the file
$audit_paths = [
    '../audit_logger.php',      // From auth/ go up to root
    'audit_logger.php',          // If in same folder
    '../includes/audit_logger.php',
    '../auth/audit_logger.php',
    '../../audit_logger.php'
];

$audit_loaded = false;
foreach ($audit_paths as $path) {
    if (file_exists($path)) {
        include $path;
        $audit_loaded = true;
        error_log("Audit logger loaded from: $path");
        break;
    }
}

if (!$audit_loaded) {
    error_log("WARNING: Audit logger not found in any path");
    // Define a fallback function so the page doesn't crash
    if (!function_exists('logAction')) {
        function logAction($action_type, $module, $description, $status = 'success', $affected_id = null, $affected_table = null, $old_values = null, $new_values = null) {
            // Fallback - just log to error log
            error_log("AUDIT: [$action_type] [$module] $description");
            return true;
        }
    }
}

// Check if function exists
if (!function_exists('logAction')) {
    error_log("ERROR: logAction function not defined after including audit_logger.php");
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize message
$error = "";

// Only run when form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please fill all fields!";
        // Log failed login attempt - missing fields
        if (function_exists('logAction')) {
            logAction('login', 'auth', "Login failed: Missing email or password", 'failed');
        }
    } else {

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {

            // Verify password
            if (password_verify($password, $row['password'])) {

                // Check account status
                if (isset($row['status']) && $row['status'] == 'suspended') {
                    $error = "Your account has been suspended. Please contact the administrator.";
                    // Log suspended account attempt
                    if (function_exists('logAction')) {
                        logAction('login', 'auth', "Login failed: Account suspended for user: " . $row['full_name'] . " (Email: $email)", 'failed');
                    }
                } else {

                    // Create session
                    $_SESSION['user_id'] = $row['user_id'];
                    $_SESSION['role'] = $row['role'];
                    $_SESSION['full_name'] = $row['full_name'];
                    $_SESSION['email'] = $row['email'];
                    
                    // LOG SUCCESSFUL LOGIN
                    if (function_exists('logAction')) {
                        logAction('login', 'auth', "User logged in: " . $row['full_name'] . " (Role: " . $row['role'] . ", Email: $email)", 'success', $row['user_id'], 'users');
                    }

                    // Redirect based on role
                    if ($row['role'] == 'admin') {
                        header("Location: ../admin/admin_dashboard.php");
                        exit();
                    } elseif ($row['role'] == 'teacher') {
                        header("Location: ../teacher/teacher_dashboard.php");
                        exit();
                    } elseif ($row['role'] == 'student') {
                        header("Location: ../student/student_dashboard.php");
                        exit();
                    } else {
                        header("Location: ../academic/academic_dashboard.php");
                        exit();
                    }
                }

            } else {
                $error = "Wrong password!";
                // Log failed login - wrong password
                if (function_exists('logAction')) {
                    logAction('login', 'auth', "Login failed: Wrong password for user: " . $row['full_name'] . " (Email: $email)", 'failed');
                }
            }

        } else {
            $error = "User not found!";
            // Log failed login - user not found
            if (function_exists('logAction')) {
                logAction('login', 'auth', "Login failed: User not found with email: $email", 'failed');
            }
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSARMS Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            background: #eef2f7;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            width: 100%;
            max-width: 400px;
            padding: 36px 32px 40px;
            border-radius: 28px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
            text-align: center;
            border: 1px solid #e2e8f0;
        }

        .logo-wrapper {
            margin-bottom: 20px;
        }

        .logo-wrapper img {
            width: 70px;
            height: auto;
            display: inline-block;
        }

        h2 {
            font-size: 28px;
            font-weight: 700;
            color: #0f2b3d;
            margin-bottom: 6px;
            letter-spacing: -0.3px;
        }

        .subhead {
            color: #5b6e8c;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 32px;
            border-bottom: 1.5px solid #e9edf2;
            display: inline-block;
            padding-bottom: 6px;
        }

        .error-message {
            background: #fee2e2;
            color: #b91c1c;
            padding: 10px 16px;
            border-radius: 60px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 24px;
            text-align: center;
            border-left: 3px solid #ef4444;
        }

        .input-group {
            margin-bottom: 24px;
            text-align: left;
        }

        .form-options {
            text-align: right;
            margin-top: 5px;
        }

        .forgot-link {
           font-size: 13px;
           color: #4f46e5;
           text-decoration: none;
           font-weight: 500;
           transition: all 0.3s ease;
        }

        .forgot-link:hover {
           color: #1e40af;
           text-decoration: underline;
        }

        .input-group input {
            width: 100%;
            padding: 12px 14px;
            font-size: 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 16px;
            background: white;
            font-family: inherit;
            outline: none;
            transition: 0.15s;
        }

        .input-group input:focus {
            border-color: #2c6e9e;
            box-shadow: 0 0 0 2px rgba(44, 110, 158, 0.1);
        }

        button {
            width: 100%;
            background: #1e4a6b;
            color: white;
            font-weight: 600;
            font-size: 15px;
            padding: 12px 16px;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            margin-top: 8px;
            transition: 0.15s;
        }

        button:hover {
            background: #0f3a54;
        }

        .footer {
            margin-top: 32px;
            padding-top: 16px;
            border-top: 1px solid #eef2f5;
            font-size: 11px;
            color: #7c8b9f;
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 28px 22px 32px;
            }
            h2 {
                font-size: 24px;
            }
        }

        .input-group label {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .input-group label i {
            font-size: 14px;
            width: 18px;
            color: #2c6e9e;
        }
    </style>
</head>
<body>
<div class="login-container">
    <div class="logo-wrapper">
        <img src="../images/tyler.jpg" alt="SSARMS Logo">
    </div>
    <h2>SSARMS</h2>
    <div class="subhead">Academic Record System</div>

    <?php if (!empty($error)): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="input-group">
            <label>
                <i class="fas fa-envelope" style="margin-right: 8px;"></i>
             Email Address
            </label>
            <input type="email" name="email" placeholder="user@example.com" required>
        </div>

        <div class="input-group">
            <label> 
                <i class="fas fa-lock" style="margin-right: 8px;"></i>
             Password
            </label>
            <input type="password" name="password" placeholder="•••" required>
        </div>

        <button type="submit">Login</button>

        <div class="form-options">
            <a href="forget_password.php" class="forgot-link">
                Forgot Password?
            </a>
        </div>
    </form>

    <div class="footer">
        <span>© 2026 SSARMS</span>
    </div>
</div>
</body>
</html>