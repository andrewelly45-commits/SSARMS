<?php
session_start();
include '../db.php';

/* ================= AUTH CHECK ================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ================= UPDATE PROFILE ================= */
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if this is a profile update
    $is_update = isset($_POST['update_profile']) || isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0 && !empty($_FILES['profile_pic']['name']);
    
    if ($is_update) {
        // Update user information if fields exist
        if (isset($_POST['full_name']) && isset($_POST['email'])) {
            $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
            $email = mysqli_real_escape_string($conn, $_POST['email']);
            $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
            $date_of_birth = mysqli_real_escape_string($conn, $_POST['date_of_birth'] ?? '');
            
            // Update user information
            $update_query = "UPDATE users SET 
                full_name = '$full_name',
                email = '$email',
                phone = '$phone'
                WHERE user_id = '$user_id'";
            
            if (mysqli_query($conn, $update_query)) {
                // Update student date of birth
                if (!empty($date_of_birth)) {
                    mysqli_query($conn, "UPDATE student SET date_of_birth = '$date_of_birth' WHERE user_id = '$user_id'");
                }
                $success = "Profile updated successfully!";
            } else {
                $error = "Error updating profile: " . mysqli_error($conn);
            }
        }
        
        // Handle profile picture upload
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0 && !empty($_FILES['profile_pic']['name'])) {
            $target_dir = "../uploads/";
            
            // Create uploads directory if it doesn't exist
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            // Get file info
            $file_name = $_FILES['profile_pic']['name'];
            $file_tmp = $_FILES['profile_pic']['tmp_name'];
            $file_size = $_FILES['profile_pic']['size'];
            $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Validate file
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            $max_file_size = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($file_extension, $allowed_types)) {
                $error = "Only JPG, JPEG, PNG & GIF files are allowed.";
            } elseif ($file_size > $max_file_size) {
                $error = "File is too large. Maximum size is 5MB.";
            } else {
                // Generate unique filename
                $new_filename = time() . "_" . uniqid() . "." . $file_extension;
                $target_file = $target_dir . $new_filename;
                
                // Verify it's an image
                $check = getimagesize($file_tmp);
                if ($check !== false) {
                    // Move uploaded file
                    if (move_uploaded_file($file_tmp, $target_file)) {
                        // Delete old profile picture if not default
                        $old_pic_query = mysqli_query($conn, "SELECT profile_pic FROM users WHERE user_id = '$user_id'");
                        $old_pic_data = mysqli_fetch_assoc($old_pic_query);
                        
                        if ($old_pic_data && $old_pic_data['profile_pic'] && $old_pic_data['profile_pic'] != 'default.png') {
                            $old_pic_path = $target_dir . $old_pic_data['profile_pic'];
                            if (file_exists($old_pic_path)) {
                                unlink($old_pic_path);
                            }
                        }
                        
                        // Update profile pic in database
                        $update_pic_query = "UPDATE users SET profile_pic = '$new_filename' WHERE user_id = '$user_id'";
                        if (mysqli_query($conn, $update_pic_query)) {
                            $success = "Profile picture updated successfully!";
                        } else {
                            $error = "Failed to update profile picture in database.";
                        }
                    } else {
                        $error = "Sorry, there was an error uploading your file.";
                    }
                } else {
                    $error = "File is not a valid image.";
                }
            }
        }
        
        // Refresh student data
        $query = mysqli_query($conn, "
            SELECT
                s.student_id,
                s.date_of_birth,
                s.academic_year,
                c.class_name,
                u.full_name,
                u.email,
                u.phone,
                u.gender,
                u.profile_pic
            FROM student s
            JOIN users u ON s.user_id = u.user_id
            LEFT JOIN class c ON s.class_id = c.class_id
            WHERE s.user_id = '$user_id'
        ");
        $student = mysqli_fetch_assoc($query);
    }
}

/* ================= GET STUDENT INFO ================= */
$query = mysqli_query($conn, "
    SELECT
        s.student_id,
        s.date_of_birth,
        s.academic_year,
        c.class_name,
        u.full_name,
        u.email,
        u.phone,
        u.gender,
        u.profile_pic
    FROM student s
    JOIN users u ON s.user_id = u.user_id
    LEFT JOIN class c ON s.class_id = c.class_id
    WHERE s.user_id = '$user_id'
");

$student = mysqli_fetch_assoc($query);

if (!$student) {
    die("Student not found");
}

$profile_pic = !empty($student['profile_pic'])
    ? "../uploads/" . $student['profile_pic']
    : "../uploads/default.png";
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile | SSARMS</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', sans-serif;
    background: #f0f2f5;
}

/* ========== MAIN CONTENT ========== */
.main-content {
    margin-left: 270px;
    padding: 25px 30px;
    min-height: 100vh;
}

/* ========== PAGE TITLE ========== */
.page-title {
    font-size: 24px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.page-title i {
    color: #f59e0b;
    font-size: 28px;
}

/* ========== PROFILE CARD ========== */
.profile-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    border: 1px solid #e2e8f0;
}

/* ========== PROFILE HEADER ========== */
.profile-header {
    background: linear-gradient(135deg, #1a1a2e 0%, #0f0f23 100%);
    padding: 35px;
    text-align: center;
    color: white;
    position: relative;
}

.profile-img-container {
    width: 140px;
    height: 140px;
    margin: 0 auto 15px;
    border-radius: 50%;
    overflow: hidden;
    position: relative;
    border: 4px solid #f59e0b;
    background: white;
}

.profile-header img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    display: block;
    border: none;
    padding: 0;
}

.camera-icon {
    position: absolute;
    bottom: 10px;
    right: 5px;
    background: #f59e0b;
    border-radius: 50%;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid white;
}

.camera-icon i {
    color: white;
    font-size: 14px;
}

.camera-icon:hover {
    background: #d97706;
    transform: scale(1.05);
}

.profile-header h2 {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 5px;
}

.profile-header p {
    opacity: 0.8;
    font-size: 14px;
}

/* ========== PROFILE BODY ========== */
.profile-body {
    padding: 30px;
}

/* ========== EDITABLE FORM ========== */
.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: #64748b;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.form-group label i {
    color: #f59e0b;
    width: 16px;
}

.form-group input {
    padding: 10px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: #f8fafc;
}

.form-group input:focus {
    border-color: #f59e0b;
    outline: none;
    box-shadow: 0 0 0 3px rgba(245,158,11,0.2);
    background: white;
}

.form-group input:read-only {
    background: #f1f5f9;
    cursor: not-allowed;
}

/* ========== BUTTONS ========== */
.btn-update {
    background: #f59e0b;
    color: white;
    border: none;
    padding: 12px 28px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 20px;
}

.btn-update:hover {
    background: #d97706;
    transform: translateY(-2px);
}

/* ========== ALERTS ========== */
.alert {
    padding: 12px 16px;
    border-radius: 10px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
}

.alert.success {
    background: #dcfce7;
    color: #166534;
    border-left: 4px solid #22c55e;
}

.alert.error {
    background: #fee2e2;
    color: #991b1b;
    border-left: 4px solid #ef4444;
}

.alert i {
    font-size: 16px;
}

/* ========== HIDDEN FILE INPUT ========== */
#profile_pic_input {
    display: none;
}

/* ========== RESPONSIVE ========== */
@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        padding: 15px;
    }
    
    .profile-header {
        padding: 25px;
    }
    
    .profile-header img {
        width: 100px;
        height: 100px;
    }
    
    .profile-header h2 {
        font-size: 20px;
    }
    
    .profile-body {
        padding: 20px;
    }
    
    .form-grid {
        gap: 15px;
    }
    
    .camera-icon {
        width: 30px;
        height: 30px;
        bottom: 5px;
        right: 0;
    }
}
</style>
</head>

<body>

<?php include 'student_sidebar.php'; ?>

<div class="main-content">

    <h2 class="page-title">
        <i class="fas fa-user-circle"></i>
        My Profile
    </h2>

    <div class="profile-card">

        <div class="profile-header">
            <div class="profile-img-container">
                <img src="<?= $profile_pic ?>" alt="Profile Picture" id="profilePreview">
                <div class="camera-icon" onclick="document.getElementById('profile_pic_input').click()">
                    <i class="fas fa-camera"></i>
                </div>
            </div>
            <h2><?= htmlspecialchars($student['full_name']) ?></h2>
            <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($student['email']) ?></p>
        </div>

        <div class="profile-body">

            <!-- Success/Error Messages -->
            <?php if (!empty($success)): ?>
                <div class="alert success">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert error">
                    <i class="fas fa-times-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Edit Form -->
            <form method="POST" enctype="multipart/form-data" id="profileForm">
                <input type="hidden" name="update_profile" value="1">
                <input type="file" name="profile_pic" id="profile_pic_input" accept="image/*" style="display:none;" onchange="previewImage(this)">

                <div class="form-grid">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Full Name</label>
                        <input type="text" name="full_name" value="<?= htmlspecialchars($student['full_name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email Address</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($student['email']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Phone Number</label>
                        <input type="tel" name="phone" value="<?= htmlspecialchars($student['phone'] ?? '') ?>" placeholder="Enter phone number">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-cake-candles"></i> Date of Birth</label>
                        <input type="date" name="date_of_birth" value="<?= $student['date_of_birth'] ?? '' ?>">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-id-card"></i> Student ID (Read Only)</label>
                        <input type="text" value="<?= $student['student_id'] ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-school"></i> Class (Read Only)</label>
                        <input type="text" value="<?= htmlspecialchars($student['class_name']) ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-calendar-alt"></i> Academic Year (Read Only)</label>
                        <input type="text" value="<?= $student['academic_year'] ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-venus-mars"></i> Gender (Read Only)</label>
                        <input type="text" value="<?= ucfirst($student['gender']) ?>" readonly>
                    </div>
                </div>

                <button type="submit" name="update_profile" class="btn-update">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </form>

        </div>

    </div>

</div>

<script>
// Preview image before upload
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profilePreview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
        
        // Show a notification to click Save button
        var saveButton = document.querySelector('.btn-update');
        saveButton.style.animation = 'pulse 1s ease 3';
        saveButton.innerHTML = '<i class="fas fa-info-circle"></i> Click Save to update photo';
        
        // Auto-submit the form when image is selected
        // document.getElementById('profileForm').submit();
    }
}

// Auto-hide alerts after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 300);
    });
}, 5000);
</script>

<style>
@keyframes pulse {
    0% { transform: scale(1); background: #f59e0b; }
    50% { transform: scale(1.05); background: #d97706; }
    100% { transform: scale(1); background: #f59e0b; }
}
</style>
<?php include '../footer.php'; ?>
</body>
</html>