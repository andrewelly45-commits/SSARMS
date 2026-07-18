<?php
include '../db.php';

$message = "";
$message_type = ""; // Added to track message type (success/error)

// Check token exists
if (!isset($_GET['token'])) {
    die("No token provided");
}

$token = mysqli_real_escape_string($conn, $_GET['token']);

// Verify token
$check = mysqli_query($conn, "SELECT * FROM users WHERE reset_token='$token'");

if (mysqli_num_rows($check) == 0) {
    die("Invalid or expired token");
}

// Update password
if (isset($_POST['update'])) {
    
    // Validate password (optional but recommended)
    if (empty($_POST['password'])) {
        $message = "Password cannot be empty!";
        $message_type = "error";
    } else {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $update = mysqli_query($conn, "
            UPDATE users 
            SET password='$password', reset_token=NULL 
            WHERE reset_token='$token'
        ");

        if ($update) {
            $message = "✅ Password updated successfully! You can now login with your new password.";
            $message_type = "success";
        } else {
            $message = "❌ Failed to update password! Please try again.";
            $message_type = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: #f2f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        form {
            background: #ffffff;
            padding: 25px;
            border-radius: 10px;
            width: 320px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            outline: none;
            font-size: 14px;
            box-sizing: border-box;
        }

        input:focus {
            border-color: #4caf50;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #4caf50;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            cursor: pointer;
            transition: background 0.3s;
        }

        button:hover {
            background: #45a049;
        }

        .hint {
            font-size: 12px;
            color: #777;
            margin-top: -5px;
            margin-bottom: 10px;
        }

        .msg {
            margin-top: 15px;
            padding: 12px;
            border-radius: 6px;
            font-size: 14px;
            display: <?php echo $message ? 'block' : 'none'; ?>;
        }

        .msg.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            display: block;
        }

        .msg.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            display: block;
        }

        footer {
            margin-top: 15px;
            font-size: 11px;
            color: #888;
        }

        .login-link {
            margin-top: 15px;
            font-size: 14px;
        }

        .login-link a {
            color: #4caf50;
            text-decoration: none;
            font-weight: bold;
        }

        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

<form method="POST">

    <h2>Reset Password</h2>

    <?php if ($message): ?>
        <div class="msg <?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <input type="password" name="password" placeholder="New password" required>

    <button type="submit" name="update">Update Password</button>

    <?php if ($message_type == "success"): ?>
        <div class="login-link">
            <a href="login.php"> Go to Login</a>
        </div>
    <?php endif; ?>

    <footer>Secure password reset</footer>

</form>

</body>
</html>