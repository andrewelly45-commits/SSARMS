<?php
session_start();
include '../db.php';

$class_id = $_GET['class_id'];
$subject_id = $_GET['subject_id'];

mysqli_query($conn, "
    UPDATE marks
    SET status = 'approved'
    WHERE class_id = '$class_id'
    AND subject_id = '$subject_id'
    AND status = 'pending'
");

header("Location: approve_classes.php");
exit();
?>