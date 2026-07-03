<?php
session_start();
include '../db.php';

$class_id = $_GET['class_id'];
$subject_id = $_GET['subject_id'];

/* GET MARKS */
$marks = mysqli_query($conn, "
    SELECT 
        m.mark_id,
        m.marks,
        m.teacher_id,
        u.full_name AS student_name,
        t_user.full_name AS teacher_name
    FROM marks m
    JOIN student s ON m.student_id = s.student_id
    JOIN users u ON s.user_id = u.user_id
    JOIN teacher t ON m.teacher_id = t.teacher_id
    JOIN users t_user ON t.user_id = t_user.user_id
    WHERE m.class_id = '$class_id'
    AND m.subject_id = '$subject_id'
    AND m.status = 'pending'
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Approve Marks</title>
<style>
body{font-family:Arial;background:#f4f6f9;}
.container{margin-left:260px;padding:90px 20px;}
table{width:100%;background:white;border-collapse:collapse;}
th{background:orange;padding:10px;}
td{padding:10px;border-bottom:1px solid #ddd;}
.btn{
    padding:10px 15px;
    background:green;
    color:white;
    border:none;
    border-radius:6px;
    text-decoration:none;
}
</style>
</head>

<body>

<?php include 'academic_sidebar.php'; ?>

<div class="container">

<h2>Approve Subject Marks</h2>

<table>
<tr>
    <th>Student</th>
    <th>Marks</th>
    <th>Teacher</th>
</tr>

<?php while($row = mysqli_fetch_assoc($marks)): ?>

<tr>
    <td><?= $row['student_name'] ?></td>
    <td><?= $row['marks'] ?></td>
    <td><?= $row['teacher_name'] ?></td>
</tr>

<?php endwhile; ?>

</table>

<br>

<!-- APPROVE ALL BUTTON -->
<a class="btn"
   href="approve_all.php?class_id=<?= $class_id ?>&subject_id=<?= $subject_id ?>">
   Approve All Students
</a>

</div>

</body>
</html>