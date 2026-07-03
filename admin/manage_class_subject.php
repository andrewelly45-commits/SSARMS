<?php
session_start();
include '../db.php';

// Fetch class-subject assignments
$sql = "
SELECT
    cs.id,
    c.class_name,
    s.subject_name,
    cs.status
FROM class_subject cs
JOIN class c ON cs.class_id = c.class_id
JOIN subject s ON cs.subject_id = s.subject_id
ORDER BY c.class_name
";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Class Subjects</title>
    <style>
        body{
            font-family: Arial, sans-serif;
            background:#f4f6f9;
            padding:20px;
        }

        h2{
            margin-bottom:20px;
        }

        table{
            width:100%;
            border-collapse:collapse;
            background:#fff;
        }

        th, td{
            padding:12px;
            border:1px solid #ddd;
            text-align:left;
        }

        th{
            background:#007bff;
            color:white;
        }

        .btn{
            padding:6px 12px;
            text-decoration:none;
            border-radius:4px;
            color:white;
        }

        .edit{
            background:green;
        }

        .delete{
            background:red;
        }

        .add{
            background:#007bff;
            padding:10px 15px;
            display:inline-block;
            margin-bottom:15px;
            color:white;
            text-decoration:none;
            border-radius:4px;
        }
    </style>
</head>
<body>

<h2>Manage Class Subjects</h2>

<a href="add_class_subject.php" class="add">+ Assign Subject to Class</a>

<table>
    <tr>
        <th>#</th>
        <th>Class</th>
        <th>Subject</th>
        <th>Action</th>
    </tr>

    <?php
    $count = 1;
    while($row = mysqli_fetch_assoc($result)){
    ?>
    <tr>
        <td><?= $count++ ?></td>
        <td><?= htmlspecialchars($row['class_name']) ?></td>
        <td><?= htmlspecialchars($row['subject_name']) ?></td>
        <td>
            <a class="btn edit"
               href="edit_class_subject.php?class_id=<?= $row['id'] ?>">
               Edit
            </a>

            <a class="btn delete"
               href="delete_class_subject.php?id=<?= $row['id'] ?>"
               onclick="return confirm('Delete this record?')">
               Delete
            </a>
        </td>
    </tr>
    <?php } ?>

</table>

</body>
</html>