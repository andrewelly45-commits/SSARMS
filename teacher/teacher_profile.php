<?php
session_start();
include '../db.php';

/* ================= AUTH CHECK ================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ================= FETCH TEACHER ================= */
$teacher_query = mysqli_query($conn, "
    SELECT * FROM teacher WHERE user_id='$user_id'
");

$teacher = mysqli_fetch_assoc($teacher_query);

if (!$teacher) {
    die("Teacher not found");
}

/* ================= UPDATE PROFILE ================= */
if (isset($_POST['update_profile'])) {

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);

    $profile_pic = $teacher['profile_pic'];

    /* upload image */
    if (!empty($_FILES['profile_pic']['name'])) {

        $upload_dir = "../uploads/";
        $file_name = time() . "_" . basename($_FILES["profile_pic"]["name"]);
        $target_file = $upload_dir . $file_name;

        $allowed = ['jpg','jpeg','png','gif','webp'];

        $ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {

            move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file);
            $profile_pic = $file_name;
        }
    }

    mysqli_query($conn, "
        UPDATE teacher 
        SET email='$email', phone='$phone', profile_pic='$profile_pic'
        WHERE user_id='$user_id'
    ");

    header("Location: teacher_profile.php");
    exit();
}

/* ================= CHANGE PASSWORD ================= */
if (isset($_POST['change_password'])) {

    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    $get = mysqli_query($conn, "SELECT password FROM teacher WHERE user_id='$user_id'");
    $row = mysqli_fetch_assoc($get);

    if (password_verify($current, $row['password'])) {

        if ($new === $confirm) {

            $hashed = password_hash($new, PASSWORD_DEFAULT);

            mysqli_query($conn, "
                UPDATE teacher 
                SET password='$hashed' 
                WHERE user_id='$user_id'
            ");

            $msg = "Password changed successfully!";
        } else {
            $msg = "Passwords do not match!";
        }

    } else {
        $msg = "Current password is wrong!";
    }
}

/* ================= REFRESH DATA ================= */
$teacher_query = mysqli_query($conn, "
    SELECT 
        t.teacher_id,
        u.full_name,
        u.email,
        u.phone,
        u.profile_pic
    FROM teacher t
    JOIN users u ON t.user_id = u.user_id
    WHERE t.user_id = '$user_id'
");
$teacher = mysqli_fetch_assoc($teacher_query);

$name  = $teacher['full_name'];
$email = $teacher['email'] ?? '';
$phone = $teacher['phone'] ?? '';
$pic   = $teacher['profile_pic'] ?? 'default.png';
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Teacher Profile</title>

<style>
body{
    font-family:Arial;
    background:#f0f4f8;
    padding:30px;
}

.container{
    max-width:600px;
    margin:auto;
}

h2{
    text-align:center;
    margin-bottom:20px;
    color:#1e3a5f;
}

.card{
    background:white;
    padding:25px;
    border-radius:18px;
    margin-bottom:20px;
    border:1px solid #e2e8f0;
}

.profile-box{
    text-align:center;
    margin-bottom:20px;
}

.profile-box img{
    width:140px;
    height:140px;
    border-radius:50%;
    object-fit:cover;
    border:4px solid #2c3e66;
}

.input-group{
    margin-bottom:15px;
}

label{
    font-size:13px;
    font-weight:600;
}

input{
    width:100%;
    padding:12px;
    border:1px solid #cbd5e1;
    border-radius:10px;
}

button{
    width:100%;
    padding:12px;
    background:#2c3e66;
    color:white;
    border:none;
    border-radius:10px;
    cursor:pointer;
    font-weight:600;
}

button:hover{
    background:#1e2f4a;
}

.file-label{
    display:inline-block;
    padding:8px 16px;
    background:#eef2ff;
    border-radius:20px;
    cursor:pointer;
    font-size:13px;
}

input[type="file"]{
    display:none;
}

.msg{
    text-align:center;
    padding:10px;
    margin-bottom:15px;
    border-radius:10px;
    background:#dcfce7;
    color:#166534;
}
</style>
</head>

<body>

<?php include 'teacher_sidebar.php'; ?>

<div class="container">

<h2>Teacher Profile</h2>

<?php if(isset($msg)): ?>
<div class="msg"><?= htmlspecialchars($msg); ?></div>
<?php endif; ?>

<!-- ================= PROFILE UPDATE ================= -->
<div class="card">

<div class="profile-box">

<img src="../uploads/<?= htmlspecialchars($pic); ?>"
onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($name); ?>&background=334155&color=fff';">

</div>

<form method="POST" enctype="multipart/form-data">

<div class="input-group">
<label>Profile Picture</label><br>
<label class="file-label" for="pic">Choose Image</label>
<input type="file" name="profile_pic" id="pic" accept="image/*">
</div>

<div class="input-group">
<label>Name</label>
<input type="text" value="<?= htmlspecialchars($name); ?>" disabled>
</div>

<div class="input-group">
<label>Email</label>
<input type="email" name="email" value="<?= htmlspecialchars($email); ?>">
</div>

<div class="input-group">
<label>Phone</label>
<input type="text" name="phone" value="<?= htmlspecialchars($phone); ?>">
</div>

<button type="submit" name="update_profile">Update Profile</button>

</form>
</div>

<!-- ================= PASSWORD ================= -->
<div class="card">

<h3> Change Password</h3>

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

<button type="submit" name="change_password">Change Password</button>

</form>

</div>

</div>
<?php include '../footer.php'; ?>
</body>
</html>