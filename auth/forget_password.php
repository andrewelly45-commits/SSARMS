<?php
session_start();
include '../db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

$message = "";

if (isset($_POST['reset'])) {

    $email = trim($_POST['email']);
    $email = mysqli_real_escape_string($conn, $email);
    // Check if email exists
    $check = mysqli_query($conn, "SELECT * FROM users WHERE LOWER(email)=LOWER('$email')"
                           );

    echo $email . "<br>";
    echo mysqli_num_rows($check);                       

    if (mysqli_num_rows($check) > 0) {

        // Generate secure token
        $token = bin2hex(random_bytes(50));

        // Save token in database
        mysqli_query(
            $conn,
            "UPDATE users SET reset_token='$token' WHERE email='$email'"
        );

        // CHANGE 'school_management' TO YOUR PROJECT FOLDER NAME
        $link = "http://localhost/projects/SSARMS/auth/reset_password.php?token=$token";

        // Create PHPMailer
        $mail = new PHPMailer(true);

        try {

            // SMTP Settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;

            // Your Gmail
            $mail->Username   = 'mazikuelia2@gmail.com';

            // Gmail App Password
            $mail->Password   = 'xxzbnsvkjjcvqiad';

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Sender
            $mail->setFrom(
                'mazikuelia2@gmail.com',
                'School System'
            );

            // Receiver
            $mail->addAddress($email);

            // Email Content
            $mail->isHTML(true);

            $mail->Subject = 'Password Reset';

            $mail->Body = "
                <h2>Password Reset</h2>

                <p>Click the link below to reset your password:</p>

                <a href='$link'>$link</a>
            ";

            // Send Email
            $mail->send();

            $message = "Reset link sent successfully!";

        } catch (Exception $e) {

            $message = "Mailer Error: {$mail->ErrorInfo}";
        }

    } else {

        $message = "Email not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>forget password</title>
</head>
<style>

    body{
        background: #f4f6f9;
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
    }

    .reset {
        max-width: 400px;
        margin: 80px auto;
        padding: 35px;
        background: #ffffff;
        border-radius: 14px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .reset h2 {
        margin-bottom: 10px;
        font-size: 28px;
        color: #222;
        text-align: center;
    }

    .subtitle {
        margin-bottom: 25px;
        color: #666;
        font-size: 14px;
        text-align: center;
        line-height: 1.5;
    }

    .resetForm .form-group {
        margin-bottom: 18px;
    }

    .resetForm input{
        width: 100%;
        padding: 14px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 15px;
        outline: none;
        transition: 0.3s;
        box-sizing: border-box;
    }

    .resetForm input:focus{
        border-color: #007bff;
        box-shadow: 0 0 6px rgba(0,123,255,0.3);
    }

    .resetForm button{
        width: 100%;
        padding: 14px;
        background: #007bff;
        border: none;
        border-radius: 8px;
        color: white;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: 0.3s;
    }

    .resetForm button:hover{
        background: #0056b3;
    }

    .message{
        text-align: center;
        margin-top: 15px;
        font-size: 14px;
        color: green;
    }

</style>

<body>

    <div class="reset">
        <h2>Reset Password</h2>

        <p class="subtitle">
            Enter your registered email address and we will send you a password reset link.
        </p>

        <form method="POST" class="resetForm">

            <div class="form-group">
                <input type="email" name="email" placeholder="Enter your email"required>
            </div>

            <button type="submit" name="reset">
                Send Reset Link
            </button>

        </form>

        <p class="message">
            <?php echo $message; ?>
        </p>

    </div>

</body>
</html>