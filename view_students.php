<?php
session_start();

include "db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.php");
    exit();
}

if(!isset($_GET['class_id'])) {
    $result = mysqli_query($conn, "SELECT student.student_id, users.full_name, users.gender 
                                   FROM student 
                                   JOIN users student.user_id = users.user_id
                                   WHERE student.class_id = class.class_id");
}

$result = mysqli_query($conn, "SELECT student. *, users.full_name, users.gender, class.class_name 
                            FROM student
                            JOIN users ON student.user_id = users.user_id
                            JOIN class ON student.class_id = class.class_id");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>student view</title>
</head>
<body>

<?php include 'auth.php'; ?>

    <h2>Student List</h2>
    <table>
        <tr>
            <th>Full Name</th>
            <th>Gender</th>
            <th>Class</th>
            <th>Action</th>
        </tr>

        <?php while($row =mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo $row['full_name']; ?></td>
            <td><?php echo $row['Gender']; ?></td>
            <td><?php echo $row['class_name']; ?></td>
            <td>
                <a href="edit_student.php?id=<?php echo $row['studet_id']; ?>" class="btn-edit"></a>
                <a href="delete_student.php?id=<?php echo $row['student_id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this student')">Delete</a>
            </td>
        </tr>
        <?php } ?>
    
    </table>
    
</body>
</html>