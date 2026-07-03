<?php
session_start();
include '../db.php';

/* ================= AUTH CHECK ================= */

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

/* ================= GET ADMIN ================= */

$user_id = $_SESSION['user_id'];

$query = mysqli_query($conn, "
    SELECT *
    FROM users
    WHERE user_id = '$user_id'
    AND role = 'admin'
");

$admin = mysqli_fetch_assoc($query);

if (!$admin) {
    die("Admin not found");
}

/* ================= UPDATE PROFILE ================= */

$success = "";
$error = "";

if (isset($_POST['update_profile'])) {

    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);

    /* ================= PROFILE IMAGE ================= */

    $profile_pic = $admin['profile_pic'];

    if (!empty($_FILES['profile_pic']['name'])) {

        $file_name = time() . "_" . $_FILES['profile_pic']['name'];
        $tmp_name = $_FILES['profile_pic']['tmp_name'];

        move_uploaded_file($tmp_name, "../uploads/" . $file_name);

        $profile_pic = $file_name;
    }

    /* ================= UPDATE QUERY ================= */

    $update = mysqli_query($conn, "
        UPDATE users SET

            full_name = '$full_name',
            email = '$email',
            phone = '$phone',
            profile_pic = '$profile_pic'

        WHERE user_id = '$user_id'
    ");

    if ($update) {

        $success = "Profile updated successfully";

        /* REFRESH DATA */

        $query = mysqli_query($conn, "
            SELECT *
            FROM users
            WHERE user_id = '$user_id'
        ");

        $admin = mysqli_fetch_assoc($query);

    } else {
        $error = "Failed to update profile";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Admin Profile | SSARMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family:'Segoe UI', sans-serif;
            background:#f0f2f5;
        }

        .main{
            margin-left:270px;
            margin-top:85px;
            padding:20px 30px;
            min-height:100vh;
        }

        /* ========== PAGE TITLE ========== */
        .page-title{
            font-size:24px;
            font-weight:700;
            margin-bottom:25px;
            display:flex;
            align-items:center;
            gap:12px;
            color:#1e293b;
        }
        .page-title i{
            color: black;
            font-size:28px;
        }

        /* ========== PROFILE CARD ========== */
        .profile-card{
            max-width:700px;
            background:#fff;
            border-radius:20px;
            padding:28px;
            border:1px solid #e2e8f0;
            box-shadow:0 1px 3px rgba(0,0,0,0.08);
        }

        /* ========== PROFILE IMAGE ========== */
        .profile-image{
            text-align:center;
            margin-bottom:25px;
        }
        .profile-image img{
            width:120px;
            height:120px;
            border-radius:50%;
            object-fit:cover;
            border:4px solid #f59e0b;
            background:#f1f5f9;
            padding:3px;
        }
        .profile-image h2{
            margin-top:15px;
            color:#0f172a;
            font-size:20px;
        }
        .profile-role{
            color: black;
            font-size:13px;
            margin-top:5px;
            display:inline-flex;
            align-items:center;
            gap:6px;
            padding:5px 15px;
            border-radius:30px;
        }

        /* ========== FORM ========== */
        .form-group{
            margin-bottom:20px;
        }
        .form-group label{
            display:block;
            margin-bottom:6px;
            font-size:11px;
            font-weight:700;
            text-transform:uppercase;
            letter-spacing:0.5px;
            color:#475569;
        }
        .form-group label i{
            margin-right:6px;
            color: black;
        }
        .form-group input{
            width:100%;
            padding:10px 14px;
            border-radius:10px;
            border:1px solid #cbd5e1;
            font-size:13px;
            outline:none;
            transition:0.2s;
        }
        .form-group input:focus{
            border-color:#f59e0b;
            box-shadow:0 0 0 3px rgba(245,158,11,0.2);
        }
        input[type="file"]{
            padding:8px 0;
            border:none;
        }

        /* ========== BUTTON ========== */
        .update-btn{
            width:100%;
            background:#f59e0b;
            color:white;
            border:none;
            border-radius:10px;
            padding:12px;
            font-size:14px;
            font-weight:600;
            cursor:pointer;
            transition:0.2s;
            display:flex;
            align-items:center;
            justify-content:center;
            gap:8px;
        }
        .update-btn:hover{
            background:#d97706;
        }

        /* ========== ALERTS ========== */
        .alert{
            padding:12px 16px;
            border-radius:12px;
            margin-bottom:20px;
            display:flex;
            align-items:center;
            gap:10px;
            font-size:13px;
            font-weight:500;
        }
        .alert.success{
            background:#dcfce7;
            color:#166534;
            border-left:4px solid #22c55e;
        }
        .alert.error{
            background:#fee2e2;
            color:#991b1b;
            border-left:4px solid #ef4444;
        }
        .alert i{font-size:16px;}

        small{
            color:#64748b;
            font-size:11px;
            margin-top:5px;
            display:block;
        }
        small i{
            color:#f59e0b;
        }

        /* ========== MOBILE ========== */
        .mobile-toggle{
            display:none;
            position:fixed;
            top:15px;
            left:15px;
            background:#1a1a2e;
            color:white;
            border:none;
            padding:12px 15px;
            border-radius:10px;
            cursor:pointer;
            z-index:1100;
        }
        @media (max-width:768px){
            .sidebar{
                left:-280px;
            }
            .sidebar.active{
                left:0;
            }
            .topbar{
                left:0;
            }
            .main{
                margin-left:0;
                margin-top:80px;
                padding:15px;
            }
            .mobile-toggle{
                display:block;
            }
            .profile-card{
                padding:20px;
            }
        }
    </style>
</head>
<body>

<button class="mobile-toggle" onclick="document.getElementById('sidebar').classList.toggle('active')">
    <i class="fas fa-bars"></i>
</button>

<?php include 'admin_sidebar.php'; ?>
<?php include 'admin_topbar.php'; ?>

<div class="main">

    <!-- ================= TITLE ================= -->
    <div class="page-title">
        <i class="fas fa-user-shield"></i>
        Admin Profile
    </div>

    <!-- ================= PROFILE CARD ================= -->
    <div class="profile-card">

        <!-- SUCCESS ALERT -->
        <?php if(!empty($success)): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- ERROR ALERT -->
        <?php if(!empty($error)): ?>
            <div class="alert error">
                <i class="fas fa-times-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- PROFILE IMAGE -->
        <div class="profile-image">
            <img 
                src="../uploads/<?php echo !empty($admin['profile_pic']) ? htmlspecialchars($admin['profile_pic']) : 'default.png'; ?>"
                alt="Admin Profile"
                onerror="this.src='https://via.placeholder.com/120?text=Admin'"
            >
            <h2><?php echo htmlspecialchars($admin['full_name'] ?? 'Admin User'); ?></h2>
            <div class="profile-role">
                <i class="fas fa-crown"></i> System Administrator
            </div>
        </div>

        <!-- FORM -->
        <form method="POST" enctype="multipart/form-data">

            <!-- FULL NAME -->
            <div class="form-group">
                <label><i class="fas fa-user"></i> Full Name</label>
                <input 
                    type="text"
                    name="full_name"
                    required
                    placeholder="Enter your full name"
                    value="<?php echo htmlspecialchars($admin['full_name'] ?? ''); ?>"
                >
            </div>

            <!-- EMAIL -->
            <div class="form-group">
                <label><i class="fas fa-envelope"></i> Email Address</label>
                <input 
                    type="email"
                    name="email"
                    required
                    placeholder="admin@example.com"
                    value="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>"
                >
            </div>

            <!-- PHONE -->
            <div class="form-group">
                <label><i class="fas fa-phone-alt"></i> Phone Number</label>
                <input 
                    type="text"
                    name="phone"
                    placeholder="+255 XXX XXX XXX"
                    value="<?php echo htmlspecialchars($admin['phone'] ?? ''); ?>"
                >
            </div>

            <!-- PROFILE IMAGE UPLOAD -->
            <div class="form-group">
                <label><i class="fas fa-camera"></i> Change Profile Picture</label>
                <input 
                    type="file"
                    name="profile_pic"
                    accept="image/*"
                >
                <small>
                    <i class="fas fa-info-circle"></i> Allowed: JPG, PNG, GIF (Max 2MB)
                </small>
            </div>

            <!-- BUTTON -->
            <button type="submit" name="update_profile" class="update-btn">
                <i class="fas fa-save"></i>
                Update Profile
            </button>

        </form>
    </div>

</div>

<script>
// Sidebar mobile toggle
document.querySelector('.mobile-toggle')?.addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('active');
});

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(e) {
    if(window.innerWidth <= 768) {
        const sidebar = document.getElementById('sidebar');
        const toggle = document.querySelector('.mobile-toggle');
        if(sidebar?.classList.contains('active') && !sidebar.contains(e.target) && !toggle?.contains(e.target)) {
            sidebar.classList.remove('active');
        }
    }
});
</script>
<?php include '../footer.php'; ?>
</body>
</html>