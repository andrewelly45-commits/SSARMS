<?php
include '../db.php';

session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'academic') {
    die("Unauthorized");
}

$id = $_GET['id'];
$action = $_GET['action'];

if ($action == "approve") {

    mysqli_query($conn, "
        UPDATE marks
        SET status = 'approved'
        WHERE mark_id = '$id'
    ");

} elseif ($action == "reject") {

    mysqli_query($conn, "
        UPDATE marks
        SET status = 'rejected'
        WHERE mark_id = '$id'
    ");
}

header("Location: approve_marks.php");
exit();
?>