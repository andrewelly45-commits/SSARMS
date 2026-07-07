<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../auth/login.php");
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$user_id = $_SESSION['user_id'];

/* ================= GET STUDENT ================= */
$student_query = mysqli_query($conn, "
    SELECT 
        s.student_id,
        s.class_id,
        u.full_name,
        c.class_name
    FROM student s
    JOIN users u ON s.user_id = u.user_id
    JOIN class c ON s.class_id = c.class_id
    WHERE s.user_id = '$user_id'
");

$student = mysqli_fetch_assoc($student_query);

if (!$student) {
    die("Student not found");
}

$class_id = $student['class_id'];

/* ================= SUBJECTS + TEACHERS ================= */
$subjects = mysqli_query($conn, "
    SELECT
        s.subject_id,
        s.subject_name,
        u.full_name AS teacher_name
    FROM class_subject cs

    INNER JOIN subject s
        ON cs.subject_id = s.subject_id

    LEFT JOIN teacher_subject ts
        ON ts.subject_id = s.subject_id
        AND ts.class_id = cs.class_id

    LEFT JOIN teacher t
        ON ts.teacher_id = t.teacher_id

    LEFT JOIN users u
        ON t.user_id = u.user_id

    WHERE cs.class_id = '$class_id'
      AND cs.status = 'active'

    ORDER BY s.subject_name ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Subjects</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        * {margin:0; padding:0; box-sizing:border-box;}

        body {
            font-family: 'Segoe UI', Arial;
            background:#f2f4f8;
        }

        .main-content {
            margin-left:270px;
            padding:28px;
        }

        .card {
            background:white;
            border-radius:16px;
            padding:24px;
            border:1px solid #e2e8f0;
        }

        h2 {
            display:flex;
            gap:10px;
            font-size:22px;
            margin-bottom:20px;
        }

        .subject-item {
            display:flex;
            justify-content:space-between;
            align-items:center;
            padding:14px 10px;
            border-bottom:1px solid #eee;
        }

        .subject-name {
            font-size:16px;
            font-weight:600;
            color:#111;
            display:flex;
            align-items:center;
            gap:10px;
        }

        .teacher-info {
            margin-left:34px;
            font-size:13px;
            color:#64748b;
            margin-top:4px;
        }

       
        .empty {
            text-align:center;
            padding:50px;
            color:#94a3b8;
        }
    </style>
</head>

<body>

<?php include 'student_sidebar.php'; ?>

<?php include '../auth/topbar.php'; ?>

<div class="main-content">

    <div class="card">

        <h2>
            <i class="fas fa-book-open"></i>
            My Subjects
        </h2>

        <?php if (mysqli_num_rows($subjects) > 0): ?>

            <?php while ($row = mysqli_fetch_assoc($subjects)): ?>

                <div class="subject-item">

                    <div>
                        <div class="subject-name">
                            <i class="fas fa-book"></i>
                            <?= htmlspecialchars($row['subject_name']); ?>
                        </div>

                        <div class="teacher-info">
                            <i class="fas fa-user-tie"></i>
                            Teacher:
                            <?= !empty($row['teacher_name'])
                                ? htmlspecialchars($row['teacher_name'])
                                : 'Not Assigned'; ?>
                        </div>
                    </div>

                    

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="empty">
                <i class="fas fa-folder-open" style="font-size:40px;"></i>
                <p>No subjects assigned for your class yet</p>
            </div>

        <?php endif; ?>

    </div>

</div>
<?php include '../footer.php'; ?>
</body>
</html>