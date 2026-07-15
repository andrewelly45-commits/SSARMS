<?php
session_start();
include '../db.php';

/* AUTH CHECK (TEACHER ONLY) */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* GET TEACHER ID */
$teacher = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT teacher_id 
    FROM teacher 
    WHERE user_id = '$user_id'
"));

if (!$teacher) {
    die("Teacher not found");
}

$teacher_id = $teacher['teacher_id'];

/* CLASS ID (OPTIONAL) */
$class_id = isset($_GET['class_id']) ? $_GET['class_id'] : null;

/* =========================
   GET STUDENTS (SMART LOGIC)
========================= */

if ($class_id) {

    // VERIFY CLASS BELONGS TO TEACHER
    $check = mysqli_query($conn, "
        SELECT * 
        FROM teacher_class 
        WHERE teacher_id = '$teacher_id' 
        AND class_id = '$class_id'
    ");

    if (mysqli_num_rows($check) == 0) {
        die("You are not assigned to this class");
    }

    // CLASS INFO
    $class = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT class_name 
        FROM class 
        WHERE class_id = '$class_id'
    "));

    $title = $class['class_name'];

    // STUDENTS (ONE CLASS)
    $students = mysqli_query($conn, "
        SELECT 
            s.student_id,
            u.full_name,
            u.email,
            u.phone,
            u.gender,
            c.class_name
        FROM student s
        JOIN users u ON s.user_id = u.user_id
        JOIN class c ON s.class_id = c.class_id
        WHERE s.class_id = '$class_id'
        ORDER BY u.full_name ASC
    ");

} else {

    // ALL TEACHER CLASSES STUDENTS
    $title = "All My Classes Students";

    $students = mysqli_query($conn, "
        SELECT DISTINCT
            s.student_id,
            u.full_name,
            u.email,
            u.phone,
            u.gender,
            c.class_name
        FROM student s
        JOIN users u ON s.user_id = u.user_id
        JOIN class c ON s.class_id = c.class_id
        JOIN teacher_class tc ON s.class_id = tc.class_id
        WHERE tc.teacher_id = '$teacher_id'
        ORDER BY c.class_name, u.full_name
    ");
}

$total_students = mysqli_num_rows($students);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Students</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Arial;
}

body {
    background: #f0f2f5;
}

.container {
    margin-left: 260px;
    padding: 100px 30px;
}

.card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.card-header {
    background: white;
    padding: 25px;
    color: black;
}

.card-header h2 {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 22px;
}

.stats {
    margin-top: 8px;
    font-size: 14px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: #f8f9fa;
    padding: 14px;
    text-align: left;
}

td {
    padding: 14px;
    border-bottom: 1px solid #eee;
}

tr:hover td {
    background: #f9f9f9;
}

.badge {
    background: white;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 13px;
}

.male { color: blue; }
.female { color: deeppink; }

.empty {
    text-align: center;
    padding: 40px;
    color: gray;
}

@media(max-width:768px){
    .container{margin-left:0}
}
</style>
</head>

<body>

<?php include 'teacher_sidebar.php'; ?>
<?php include '../auth/topbar.php'; ?>

<div class="container">

<div class="card">

<div class="card-header">
    <h2>
        <i class="fas fa-users"></i>
        <?php echo htmlspecialchars($title); ?>
    </h2>
    <div class="stats">
        Total Students: <?php echo $total_students; ?>
    </div>
</div>

<?php if ($total_students > 0): ?>

<table>
<thead>
<tr>
    <th>No</th>
    <th>Name</th>
    <th>Email</th>
    <th>Phone</th>
    <th>Gender</th>
    <th>Class</th>
</tr>
</thead>

<tbody>
<?php $i = 1; while($row = mysqli_fetch_assoc($students)): ?>
<tr>
    <td><?php echo $i++; ?></td>
    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
    <td><?php echo htmlspecialchars($row['email']); ?></td>
    <td><?php echo htmlspecialchars($row['phone']); ?></td>
    <td>
        <?php if ($row['gender'] == 'male'): ?>
            <span class="male"><i class="fas fa-mars"></i> Male</span>
        <?php elseif ($row['gender'] == 'female'): ?>
            <span class="female"><i class="fas fa-venus"></i> Female</span>
        <?php else: ?>
            -
        <?php endif; ?>
    </td>
    <td>
        <span class="badge">
            <i class="fas fa-school"></i>
            <?php echo htmlspecialchars($row['class_name']); ?>
        </span>
    </td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

<?php else: ?>

<div class="empty">
    <i class="fas fa-user-slash"></i>
    No students found
</div>

<?php endif; ?>

</div>
</div>

</body>
</html>