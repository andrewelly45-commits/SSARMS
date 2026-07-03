<?php
session_start();
include '../db.php';

if (!isset($_GET['class_id'])) {
    die("Class not specified");
}

$class_id = (int)$_GET['class_id'];

$class = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT * FROM class WHERE class_id='$class_id'"
));

$students = mysqli_query(
    $conn,
    "SELECT
        s.student_id,
        u.full_name,
        u.gender,
        u.email
     FROM student s
     JOIN users u ON s.user_id = u.user_id
     WHERE s.class_id='$class_id'
     ORDER BY u.full_name"
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Students - <?= htmlspecialchars($class['class_name']) ?></title>
</head>
<body>

<h2>
    Students in <?= htmlspecialchars($class['class_name']) ?>
</h2>

<table border="1" cellpadding="10">
    <tr>
        <th>No</th>
        <th>Name</th>
        <th>Gender</th>
        <th>Email</th>
    </tr>

    <?php
    $no = 1;
    while($row = mysqli_fetch_assoc($students)):
    ?>
    <tr>
        <td><?= $no++ ?></td>
        <td><?= htmlspecialchars($row['full_name']) ?></td>
        <td><?= htmlspecialchars($row['gender']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>