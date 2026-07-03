<?php
session_start();
include 'db.php';

// ========== CREATE UPLOADS FOLDER AUTOMATICALLY ==========
$upload_dir = "uploads/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
    
    // Create a simple default image if it doesn't exist
    if (!file_exists($upload_dir . "default.png")) {
        // Create a simple colored square as default
        $img = imagecreate(200, 200);
        $bg = imagecolorallocate($img, 44, 62, 80);
        $text_color = imagecolorallocate($img, 255, 255, 255);
        imagefilledellipse($img, 100, 100, 180, 180, $bg);
        imagepng($img, $upload_dir . "default.png");
        imagedestroy($img);
    }
}

if (!isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ================= UPDATE PROFILE ================= */
if (isset($_POST['update_profile'])) {

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);

    if (!empty($_FILES['profile_pic']['name'])) {

        $file_name = time() . "_" . basename($_FILES["profile_pic"]["name"]);
        $target_file = $upload_dir . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if ($_FILES["profile_pic"]["size"] > 5 * 1024 * 1024) {
            $msg = "File too large! Maximum size is 5MB.";
        }
        elseif (!in_array($file_type, $allowed_types)) {
            $msg = "Only JPG, JPEG, PNG, GIF, WEBP files are allowed!";
        }
        elseif (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
            $profile_pic = $file_name;
            $update_query = "UPDATE users SET email='$email', phone='$phone', profile_pic='$profile_pic' WHERE user_id='$user_id'";
            if (mysqli_query($conn, $update_query)) {
                $msg = "Profile updated successfully!";
            }
        } else {
            $msg = "Failed to upload image! Check folder permissions.";
        }
    } else {
        $update_query = "UPDATE users SET email='$email', phone='$phone' WHERE user_id='$user_id'";
        if (mysqli_query($conn, $update_query)) {
            $msg = "Profile updated successfully!";
        }
    }
    
    // Refresh user data
    $user_query = mysqli_query($conn, "SELECT full_name, email, phone, gender, profile_pic FROM users WHERE user_id='$user_id'");
    $user = mysqli_fetch_assoc($user_query);
}

/* ================= CHANGE PASSWORD ================= */
if (isset($_POST['change_password'])) {

    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    $get = mysqli_query($conn, "SELECT password FROM users WHERE user_id='$user_id'");
    $row = mysqli_fetch_assoc($get);

    if (password_verify($current, $row['password'])) {
        if ($new === $confirm) {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE users SET password='$hashed' WHERE user_id='$user_id'");
            $msg2 = "Password changed successfully!";
        } else {
            $msg2 = "New passwords do not match!";
        }
    } else {
        $msg2 = "Current password is incorrect!";
    }
}

/* ================= FETCH USER ================= */
$user_query = mysqli_query($conn, "SELECT full_name, email, phone, gender, profile_pic FROM users WHERE user_id='$user_id'");
$user = mysqli_fetch_assoc($user_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Profile</title>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}
body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #f0f4f8;
    padding: 30px 20px;
}
.container {
    max-width: 550px;
    margin: auto;
}
h2 {
    text-align: center;
    color: #1e3a5f;
    margin-bottom: 25px;
}
.card {
    background: white;
    padding: 28px;
    border-radius: 20px;
    margin-bottom: 25px;
    border: 1px solid #e2e8f0;
}
.profile-box {
    text-align: center;
    margin-bottom: 25px;
}
.round-image {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #2c3e66;
    display: inline-block;
}
.input-group {
    margin-bottom: 18px;
}
label {
    font-size: 13px;
    font-weight: 600;
    display: block;
    margin-bottom: 6px;
}
input[type="text"],
input[type="email"],
input[type="password"] {
    width: 100%;
    padding: 12px;
    border: 1px solid #cbd5e1;
    border-radius: 12px;
}
button {
    width: 100%;
    padding: 12px;
    background: #2c3e66;
    color: white;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-weight: 600;
}
button:hover {
    background: #1e2f4a;
}
.file-input-label {
    display: inline-block;
    background: #eef2ff;
    padding: 8px 20px;
    border-radius: 30px;
    cursor: pointer;
    font-size: 13px;
}
input[type="file"] {
    display: none;
}
.msg-success {
    background: #dcfce7;
    color: #166534;
    padding: 12px;
    border-radius: 12px;
    margin-bottom: 20px;
    text-align: center;
}
.msg-error {
    background: #fee2e2;
    color: #991b1b;
    padding: 12px;
    border-radius: 12px;
    margin-bottom: 20px;
    text-align: center;
}
</style>
</head>
<body>

<div class="container">
    <h2>👤 My Profile</h2>

    <?php if(isset($msg)): ?>
        <div class="msg-success">✔ <?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <?php if(isset($msg2)): ?>
        <div class="msg-<?php echo strpos($msg2, 'success') !== false ? 'success' : 'error'; ?>">
            <?= htmlspecialchars($msg2) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="profile-box">
            <?php 
            $profile_image = !empty($user['profile_pic']) ? $user['profile_pic'] : 'default.png';
            ?>
            <img class="round-image" src="uploads/<?= htmlspecialchars($profile_image); ?>" id="previewImg">
            <div style="margin-top: 8px; font-size: 12px; color: gray;">Profile Picture</div>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <div class="input-group">
                <label>📷 Change Picture</label>
                <label class="file-input-label" for="profile_file">📁 Choose Image</label>
                <input type="file" name="profile_pic" id="profile_file" accept="image/*" onchange="previewImage(this)">
            </div>

            <div class="input-group">
                <label>📧 Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? ''); ?>" required>
            </div>

            <div class="input-group">
                <label>📞 Phone</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? ''); ?>">
            </div>

            <button type="submit" name="update_profile">💾 Update Profile</button>
        </form>
    </div>

    <div class="card">
        <h3>🔐 Change Password</h3>
        <form method="POST">
            <div class="input-group">
                <input type="password" name="current_password" placeholder="Current Password" required>
            </div>
            <div class="input-group">
                <input type="password" name="new_password" placeholder="New Password" required>
            </div>
            <div class="input-group">
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            </div>
            <button type="submit" name="change_password">🔄 Change Password</button>
        </form>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

</body>
</html>