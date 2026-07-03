<?php
session_start();
include '../db.php';

if ($_SESSION['role'] != 'academic') {
    header("Location: ../auth/login.php");
    exit();
}

/* GET CLASSES THAT HAVE PENDING MARKS */
$classes = mysqli_query($conn, "
    SELECT DISTINCT
        c.class_id,
        c.class_name
    FROM marks m
    JOIN class c ON m.class_id = c.class_id
    WHERE m.status = 'pending'
    ORDER BY c.class_name
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Approve Classes</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
body{font-family:Arial;background:#f5f6fa;}
.container{margin-left:260px;padding:90px 20px;}
.card{background:white;padding:20px;border-radius:12px;}
a.class-box{
    display:block;
    padding:15px;
    margin:10px 0;
    background:orange;
    color:black;
    text-decoration:none;
    border-radius:10px;
    font-weight:bold;
}
</style>
</head>

<body>

<?php include 'academic_sidebar.php'; ?>

<div class="container">

<div class="card">

<h2><i class="fas fa-school"></i> Select Class</h2>

<?php while($row = mysqli_fetch_assoc($classes)): ?>

    <a class="class-box"
       href="approve_subjects.php?class_id=<?= $row['class_id'] ?>">

        <i class="fas fa-door-open"></i>
        <?= $row['class_name'] ?>

    </a>

<?php endwhile; ?>

</div>

</div>

</body>
</html>