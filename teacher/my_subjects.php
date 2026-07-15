<?php
session_start();
include '../db.php';

// CHECK ROLE
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* GET TEACHER INFO */
$teacher_query = mysqli_query($conn, "
    SELECT teacher_id 
    FROM teacher
    WHERE user_id = '$user_id'
");

$teacher = mysqli_fetch_assoc($teacher_query);

if (!$teacher) {
    die("Teacher record not found.");
}

$teacher_id = $teacher['teacher_id'];

/* FETCH SUBJECTS ASSIGNED TO THIS TEACHER */
$subjects_query = mysqli_query($conn, "
    SELECT DISTINCT
        s.subject_name,
        c.class_name
    FROM teacher_subject ts
    JOIN subject s ON ts.subject_id = s.subject_id
    JOIN class c ON ts.class_id = c.class_id
    WHERE ts.teacher_id = '$teacher_id'
    ORDER BY c.class_name ASC, s.subject_name ASC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Subjects</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f0f2f5;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        .container {
    margin-left: 260px;
    padding: 90px 30px 30px 30px; /* FIX FOR TOPBAR */
}

        .card {
            background: white;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        h2 {
            color: black;
            margin-bottom: 25px;
            font-size: 26px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        h2 i {
            color: black;
            font-size: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: linear-gradient(180deg, #1a1a2e 0%, #0f0f23 100%);
            color: white;
            padding: 14px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #ecf0f1;
        }

        tr:hover td {
            background: #f8f9fa;
        }

        .sn-badge {
            color: black;
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: bold;
            font-size: 14px;
        }

        .subject-badge {
            background: white;
            color: black;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .class-badge {
            background: white;
            color: black;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .empty-state {
            text-align: center;
            padding: 50px;
            color: #95a5a6;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            display: block;
        }

        @media (max-width: 768px) {
            .container {
                margin-left: 0;
                padding: 15px;
            }
            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>

<?php include 'teacher_sidebar.php'; ?>

<?php include '../auth/topbar.php'; ?>

<div class="container">
    <div class="card">
        <h2>
            <i class="fas fa-book-open"></i>
            My Subjects
        </h2>

        <table>
            <thead>
                <tr>
                    <th style="width: 60px;">No</th>
                    <th>Subject Name</th>
                    <th>Class</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($subjects_query) > 0): ?>
                    <?php $counter = 1; while ($row = mysqli_fetch_assoc($subjects_query)): ?>
                        <tr>
                            <td><span class="sn-badge"><?= $counter++ ?></span></td>
                            <td>
                                <span class="subject-badge">
                                    <i class="fas fa-book"></i>
                                    <?= htmlspecialchars($row['subject_name']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="class-badge">
                                    <i class="fas fa-school"></i>
                                    <?= htmlspecialchars($row['class_name']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="empty-state">
                            <i class="fas fa-folder-open"></i>
                            No subjects assigned to you yet.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../footer.php'; ?>
</body>
</html>