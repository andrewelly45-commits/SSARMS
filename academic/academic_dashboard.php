<?php
session_start();
include '../db.php';

// CHECK ACADEMIC ROLE
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'academic') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// GET ACADEMIC INFO
$teacher = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT t.teacher_id, u.full_name
    FROM teacher t
    JOIN users u ON t.user_id = u.user_id
    WHERE u.user_id = '$user_id'
"));

$teacher_id = $teacher['teacher_id'];

// COUNT CLASSES
$classes = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total_classes
    FROM teacher_class
    WHERE teacher_id = '$teacher_id'
"));

// COUNT STUDENTS
$students = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(DISTINCT s.student_id) AS total_students
    FROM student s
    JOIN teacher_class tc ON s.class_id = tc.class_id
    WHERE tc.teacher_id = '$teacher_id'
"));

// COUNT PENDING RESULTS
$pending_results = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total_pending
    FROM marks
    WHERE status = 'pending'
"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Academic Dashboard</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    body{
        margin:0;
        font-family:Segoe UI, Arial;
        background:#f4f6f9;
    }

    /* SIDEBAR IS INCLUDED ELSEWHERE */

    /* TOPBAR (adjusted like admin) */
    .topbar{
        position:fixed;
        top:0;
        left:270px;
        right:0;
        height:65px;
        background:#1a1a2e;
        color:white;
        display:flex;
        justify-content:space-between;
        align-items:center;
        padding:0 25px;
        z-index:1000;
    }

    .topbar h2{
        font-size:18px;
        margin:0;
    }

    /* MAIN CONTENT */
    .container{
        margin-left:270px;
        margin-top:85px;
        padding:20px;
    }

    /* CARDS */
    .cards{
        display:grid;
        grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
        gap:15px;
    }

    .card{
        background:white;
        padding:20px;
        border-radius:15px;
        box-shadow:0 2px 6px rgba(0,0,0,0.08);
        text-align:center;
    }

    .card h3{
        margin:0;
        font-size:14px;
        color:#555;
    }

    .card p{
        font-size:28px;
        font-weight:bold;
        color:#f59e0b;
        margin-top:10px;
    }

    /* ACTIONS */
    .actions{
        margin-top:25px;
        display:grid;
        grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
        gap:15px;
    }

    .action{
        background:#1a1a2e;
        color:white;
        padding:18px;
        border-radius:12px;
        text-align:center;
        cursor:pointer;
        transition:0.2s;
    }

    .process-btn{
    display: inline-block;
    padding: 12px 24px;
    background: linear-gradient(135deg, #28a745, #218838);
    color: #fff;
    text-decoration: none;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 10px rgba(40, 167, 69, 0.3);
}

.process-btn:hover{
    background: linear-gradient(135deg, #218838, #1e7e34);
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(40, 167, 69, 0.4);
}

.process-btn:active{
    transform: translateY(0);
}

    .action:hover{
        background:#f59e0b;
    }

    .logout a{
        color:white;
        text-decoration:none;
        background:#f59e0b;
        padding:8px 14px;
        border-radius:8px;
    }
</style>
</head>
<body>

<!-- INCLUDE SIDEBAR -->
<?php include 'academic_sidebar.php'; ?>
<?php include 'academic_topbar.php'; ?>

<!-- MAIN -->
<div class="container">

    <div class="cards">

    <div class="card">
        <h3>My Classes</h3>
        <p><?= $classes['total_classes'] ?></p>
    </div>

    <div class="card">
        <h3>My Students</h3>
        <p><?= $students['total_students'] ?></p>
    </div>

    <div class="card">
        <h3>Role</h3>
        <p>Academic</p>
    </div>

    <div class="card">
        <h3>Pending Results</h3>

        <?php if($pending_results['total_pending'] > 0){ ?>
            <p style="color:red;">
                <?= $pending_results['total_pending'] ?>
            </p>

            <a href="process_results.php" class="process-btn">
                Process Results
            </a>

        <?php } else { ?>

            <p style="color:green;">0</p>
            <small>All results processed</small>

        <?php } ?>
    </div>

</div>

    <div class="actions">
        <div class="action"><i class="fas fa-check"></i><br>Approve Marks</div>
        <div class="action"><i class="fas fa-school"></i><br>My Classes</div>
        <div class="action"><i class="fas fa-chart-line"></i><br>Reports</div>
        <div class="action"><i class="fas fa-users"></i><br>Students</div>
    </div>

</div>
<?php include '../footer.php'; ?>

</body>
</html>