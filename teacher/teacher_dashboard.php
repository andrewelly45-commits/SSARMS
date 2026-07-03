<?php
session_start();
include '../db.php';

/* ================= CHECK TEACHER ================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher') {
    header("Location: ../auth/login.php");
    exit();
}

/* ================= GET TEACHER INFO ================= */
$user_id = $_SESSION['user_id'];

$teacher = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT t.teacher_id, u.full_name
    FROM teacher t
    INNER JOIN users u ON t.user_id = u.user_id
    WHERE t.user_id = '$user_id'
"));

$teacher_id = $teacher['teacher_id'];
$teacher_name = $teacher['full_name'];

/* ================= COUNT SUBJECTS ================= */
$subjects = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM teacher_subject
    WHERE teacher_id = '$teacher_id'
"))['total'];

/* ================= COUNT CLASSES ================= */
$classes = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(DISTINCT class_id) AS total
    FROM teacher_class
    WHERE teacher_id = '$teacher_id'
"))['total'];

/* ================= COUNT STUDENTS ================= */
$students = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM student s
    INNER JOIN teacher_class tc ON s.class_id = tc.class_id
    WHERE tc.teacher_id = '$teacher_id'
"))['total'];
?>

<!DOCTYPE html>
<html>
<head>
<title>Teacher Dashboard</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body {
    display: flex;
    background: linear-gradient(135deg, #f5f7fa 0%, #e9edf2 100%);
    min-height: 100vh;
}

/* MAIN */
.main {
    margin-left: 260px;
    padding: 90px 30px 30px 30px; /* IMPORTANT FIX */
    width: 100%;
    animation: fadeIn 0.4s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* HEADER */
.header {
    background: linear-gradient(135deg, #ffffff 0%, #fefefe 100%);
    padding: 25px 28px;
    border-radius: 20px;
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 8px 20px rgba(0,0,0,0.06);
    border: 1px solid rgba(0,0,0,0.03);
}

.header h2 {
    color: #1a1a2e;
    font-size: 24px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.header h2::before {
    content: '👋';
    font-size: 28px;
}

.header a {
    text-decoration: none;
    background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);
    color: #fff;
    padding: 10px 22px;
    border-radius: 30px;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.header a:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    background: linear-gradient(135deg, #5a6578 0%, #3d4758 100%);
}

/* GRID */
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 24px;
    margin-bottom: 30px;
}

/* CARD */
.card {
    background: #fff;
    padding: 25px;
    border-radius: 24px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    border: 1px solid rgba(0,0,0,0.03);
    position: relative;
    overflow: hidden;
}

.card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, #667eea, #764ba2);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.card:hover::before {
    transform: scaleX(1);
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.1);
}

.card h3 {
    font-size: 15px;
    color: #6b7280;
    margin-bottom: 15px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.card h3 i {
    font-size: 18px;
    color: #0d0d0ee9;
}

/* STAT */
.stat {
    font-size: 42px;
    font-weight: 700;
    background: linear-gradient(135deg, #1a1a2e 0%, #2d3748 100%);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}

/* ACTIONS */
.actions {
    margin-top: 5px;
}

.actions a {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 12px 0;
    padding: 12px 15px;
    border-radius: 14px;
    background: #f8fafc;
    color: #2d3748;
    text-decoration: none;
    transition: all 0.3s ease;
    font-weight: 500;
    font-size: 14px;
}

.actions a i {
    font-size: 18px;
    width: 28px;
    color: #131415;
    transition: 0.3s;
}

.actions a:hover {
    background: #f59e0b;
    color: black;
    transform: translateX(5px);
}

.actions a:hover i {
    color: black;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .main {
        margin-left: 0;
        padding: 20px;
    }
    .header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    .stat {
        font-size: 34px;
    }
}
</style>
</head>
<body>

<?php include 'teacher_sidebar.php'; ?>

<!-- MAIN CONTENT -->
<div class="main">


    <!-- DASHBOARD -->
    <div class="grid">

        <!-- SUBJECTS -->
     <a href="my_subjects.php" style="text-decoration:none; color:inherit;">
     <div class="card">
        <h3><i class="fas fa-book-open"></i>Assigned Subjects</h3>
        <div class="stat"><?php echo $subjects; ?></div>
     </div>
    </a>

        <!-- CLASSES -->
        <a href="my_classes.php" style="text-decoration: none; color: inherit;">
        <div class="card">
            <h3><i class="fas fa-chalkboard"></i> My Classes</h3>
            <div class="stat"><?php echo $classes; ?></div>
        </div>
        </a> 

        <!-- STUDENTS -->
        <a href="view_students.php" style="text-decoration: none; color: inherit;">
        <div class="card">
            <h3><i class="fas fa-users"></i> Total Students</h3>
            <div class="stat"><?php echo $students; ?></div>
        </div>
        </a>

        <!-- QUICK ACTIONS -->
        <div class="card">
            <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
            <div class="actions">
                <a href="enter_marks.php"><i class="fas fa-pen-fancy"></i> Enter Marks</a>
                <a href="my_classes.php"><i class="fas fa-school"></i> View Classes</a>
                <a href="view_students.php"><i class="fas fa-user-graduate"></i> View Students</a>
                <a href="my_subjects.php"><i class="fas fa-layer-group"></i> My Subjects</a>
            </div>
        </div>

    </div>
</div>
<?php include '../footer.php'; ?>
</body>
</html>