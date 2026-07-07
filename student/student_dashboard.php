<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ================= GET STUDENT INFO ================= */
$student_query = mysqli_query($conn, "
    SELECT 
        s.student_id, 
        s.registration_no,
        s.admission_no,
        s.date_of_birth,
        s.academic_year,
        s.status,
        u.full_name, 
        u.email,
        u.profile_pic,
        c.class_name
    FROM student s
    JOIN users u ON s.user_id = u.user_id
    JOIN class c ON s.class_id = c.class_id
    WHERE s.user_id = '$user_id'
");

$student = mysqli_fetch_assoc($student_query);
$profile_pic = $student['profile_pic'] ?? 'default.png';
$current_date = date('l, F j, Y');

// Include sidebar (this will output the sidebar and topbar)
include 'student_sidebar.php';
?>

<?php include '../auth/topbar.php'; ?>
<!-- MAIN CONTENT (only the content that changes) -->
<div class="main-content">

    <!-- Stats Grid - Only Registration Number, Class & Academic Year -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
        <!-- Registration Number Card -->
        <div style="background: #ffffff; border-radius: 20px; padding: 24px; border: 1px solid #eef2f8;">
            <div style="font-size: 12px; font-weight: 600; color: #5b6e8c; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-registered" style="color: #0e0d0def;"></i> Registration No.
            </div>
            <div style="font-size: 22px; font-weight: 700; color: #0f172a;">
                <?= !empty($student['registration_no']) ? htmlspecialchars($student['registration_no']) : '<span style="font-size: 14px; color: #94a3b8;">Not assigned</span>'; ?>
            </div>
            <div style="font-size: 13px; color: #62748c; margin-top: 10px; padding-top: 8px; border-top: 1px solid #ecf3fa;">
                <i class="fas fa-hashtag"></i> Registration Number
            </div>
        </div>

        <!-- Class Card -->
        <div style="background: #ffffff; border-radius: 20px; padding: 24px; border: 1px solid #eef2f8;">
            <div style="font-size: 12px; font-weight: 600; color: #5b6e8c; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-school" style="color: #141414f3;"></i> Class
            </div>
            <div style="font-size: 22px; font-weight: 700; color: #0f172a;"><?= htmlspecialchars($student['class_name']); ?></div>
            <div style="font-size: 13px; color: #62748c; margin-top: 10px; padding-top: 8px; border-top: 1px solid #ecf3fa;">
                <i class="fas fa-users"></i> Current Class
            </div>
        </div>

        <!-- Academic Year Card -->
        <div style="background: #ffffff; border-radius: 20px; padding: 24px; border: 1px solid #eef2f8;">
            <div style="font-size: 12px; font-weight: 600; color: #5b6e8c; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-calendar-alt" style="color: #0f0f0ff1;"></i> Academic Year
            </div>
            <div style="font-size: 22px; font-weight: 700; color: #0f172a;">
                <?= htmlspecialchars($student['academic_year'] ?? 'N/A'); ?>
            </div>
            <div style="font-size: 13px; color: #62748c; margin-top: 10px; padding-top: 8px; border-top: 1px solid #ecf3fa;">
                <i class="fas fa-calendar-check"></i> Current Academic Year
            </div>
        </div>
    </div>
</div>
<?php include '../footer.php'; ?>
</body>
</html>