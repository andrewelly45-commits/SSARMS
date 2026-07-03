<?php
session_start();
include '../db.php';

$class_id = $_GET['class_id'];

$subjects = mysqli_query($conn, "
    SELECT DISTINCT
        s.subject_id,
        s.subject_name
    FROM marks m
    JOIN subject s ON m.subject_id = s.subject_id
    WHERE m.class_id = '$class_id'
    AND m.status = 'pending'
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Subjects</title>
<style>
body{font-family:Arial;background:#f4f6f9;}
.container{margin-left:260px;padding:90px 20px;}
.box{background:white;padding:15px;margin:10px;border-radius:10px;}
a{display:block;text-decoration:none;color:black;}
</style>
</head>
<body>

<?php include 'academic_sidebar.php'; ?>

<div class="container">

<h2>Subjects</h2>

<?php while($row = mysqli_fetch_assoc($subjects)): ?>

    <div class="box">
        <a href="approve_marks.php?class_id=<?= $class_id ?>&subject_id=<?= $row['subject_id'] ?>">
            <?= $row['subject_name'] ?>
        </a>
    </div>

<?php endwhile; ?>

</div>

</body>
</html>