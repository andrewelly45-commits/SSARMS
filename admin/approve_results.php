<?php
session_start();
include '../db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

/* ================= AUTH CHECK ================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

/* ================= GET CLASS ================= */
$class_id = isset($_GET['class_id'])
    ? (int)$_GET['class_id']
    : '';

/* ================= GET SUBJECT ================= */
$subject_id = isset($_GET['subject_id'])
    ? (int)$_GET['subject_id']
    : '';

/* ================= APPROVE SUBJECT RESULTS ================= */
if (isset($_GET['approve_subject'])) {

    $class_id = (int)$_GET['class_id'];

    $subject_id = (int)$_GET['approve_subject'];

    mysqli_query($conn,
        "UPDATE marks
         SET status='published'
         WHERE
            class_id='$class_id'
            AND subject_id='$subject_id'
            AND status='pending'"
    );

    echo "<script>
        alert('Subject results approved successfully');
        window.location.href='approve_results.php?class_id=$class_id';
    </script>";

    exit();
}

/* ================= REJECT SUBJECT RESULTS ================= */
if (isset($_GET['reject_subject'])) {

    $class_id = (int)$_GET['class_id'];

    $subject_id = (int)$_GET['reject_subject'];

    mysqli_query($conn,
        "UPDATE marks
         SET status='rejected'
         WHERE
            class_id='$class_id'
            AND subject_id='$subject_id'
            AND status='pending'"
    );

    echo "<script>
        alert('Subject results rejected');
        window.location.href='approve_results.php?class_id=$class_id';
    </script>";

    exit();
}

/* ================= FETCH CLASSES ================= */
$classes = mysqli_query($conn,
    "SELECT DISTINCT

        c.class_id,
        c.class_name,

        COUNT(m.mark_id) AS total_marks

    FROM marks m

    INNER JOIN class c
        ON m.class_id = c.class_id

    WHERE m.status='pending'

    GROUP BY c.class_id

    ORDER BY c.class_name ASC"
);

/* ================= FETCH SUBJECTS ================= */
$subjects = null;

if (!empty($class_id)) {

    $subjects = mysqli_query($conn,
        "SELECT DISTINCT

            s.subject_id,
            s.subject_name,

            COUNT(m.mark_id) AS total_marks

        FROM marks m

        INNER JOIN subject s
            ON m.subject_id = s.subject_id

        WHERE
            m.class_id='$class_id'
            AND m.status='pending'

        GROUP BY s.subject_id

        ORDER BY s.subject_name ASC"
    );
}

/* ================= FETCH STUDENTS MARKS ================= */
$results = null;

if (!empty($class_id) && !empty($subject_id)) {

    $results = mysqli_query($conn,
        "SELECT

            u.full_name,
            m.marks,
            m.term,
            m.academic_year,
            m.status

        FROM marks m

        INNER JOIN student st
            ON m.student_id = st.student_id

        INNER JOIN users u
            ON st.user_id = u.user_id

        WHERE
            m.class_id='$class_id'
            AND m.subject_id='$subject_id'
            AND m.status='pending'

        ORDER BY u.full_name ASC"
    );
}
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">

<title>
Approve Results
</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    background:#f1f5f9;
    font-family:'Segoe UI',sans-serif;
}

.container{
    margin-left:260px;
    padding:30px;
}

.card{
    background:white;
    padding:25px;
    border-radius:18px;
    margin-bottom:25px;
    box-shadow:0 4px 12px rgba(0,0,0,0.05);
}

h2{
    margin-bottom:25px;
    color:#1e293b;
    font-size:28px;
}

h3{
    margin-bottom:20px;
    color:#334155;
}

.grid{
    display:grid;
    grid-template-columns:
    repeat(auto-fit,minmax(250px,1fr));
    gap:20px;
}

.box{
    background:#f8fafc;
    border:1px solid #e2e8f0;
    padding:20px;
    border-radius:16px;
}

.box h4{
    margin-bottom:10px;
    color:#1e293b;
}

.box p{
    color:#64748b;
    margin-bottom:15px;
}

.btn{
    padding:10px 18px;
    border-radius:30px;
    color:white;
    text-decoration:none;
    display:inline-flex;
    align-items:center;
    gap:8px;
    font-size:14px;
}

.view-btn{
    background:#2563eb;
}

.approve-btn{
    background:#16a34a;
}

.reject-btn{
    background:#dc2626;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}

th{
    background:#1e293b;
    color:white;
    padding:14px;
    text-align:left;
}

td{
    padding:14px;
    border-bottom:1px solid #e2e8f0;
}

tr:hover{
    background:#f8fafc;
}

.badge{
    background:#dbeafe;
    color:#1d4ed8;
    padding:6px 14px;
    border-radius:20px;
}

.mark-badge{
    background:#dcfce7;
    color:#166534;
    padding:6px 12px;
    border-radius:20px;
    font-weight:bold;
}

.action-bar{
    margin-top:20px;
    display:flex;
    gap:15px;
}

.empty{
    text-align:center;
    padding:50px;
    color:#94a3b8;
}

.subject-title{
    margin-bottom:20px;
    padding:12px 18px;
    background:#ede9fe;
    color:#5b21b6;
    border-radius:12px;
    font-weight:600;
    display:inline-flex;
    align-items:center;
    gap:10px;
}

@media(max-width:768px){

    .container{
        margin-left:0;
        padding:15px;
    }

    table{
        display:block;
        overflow-x:auto;
    }
}

</style>

</head>

<body>

<?php include 'admin_sidebar.php'; ?>

<?php include 'admin_topbar.php'; ?>

<div class="container">

<h2>
<i class="fas fa-check-circle"></i>
Approve Results
</h2>

<!-- CLASSES -->
<div class="card">

<h3>
<i class="fas fa-school"></i>
Select Class
</h3>

<div class="grid">

<?php if(mysqli_num_rows($classes) > 0): ?>

<?php while($class = mysqli_fetch_assoc($classes)): ?>

<div class="box">

<h4>
<?= htmlspecialchars($class['class_name']) ?>
</h4>

<p>
<?= $class['total_marks'] ?> pending marks
</p>

<a class="btn view-btn"
href="?class_id=<?= $class['class_id'] ?>">

<i class="fas fa-eye"></i>
View Subjects
</a>

</div>

<?php endwhile; ?>

<?php else: ?>

<div class="empty">

<i class="fas fa-folder-open"
style="font-size:50px;margin-bottom:15px;"></i>

<p>
No pending results found
</p>

</div>

<?php endif; ?>

</div>

</div>

<!-- SUBJECTS -->
<?php if($subjects && mysqli_num_rows($subjects) > 0): ?>

<div class="card">

<h3>
<i class="fas fa-book"></i>
Select Subject
</h3>

<div class="grid">

<?php while($sub = mysqli_fetch_assoc($subjects)): ?>

<div class="box">

<h4>
<?= htmlspecialchars($sub['subject_name']) ?>
</h4>

<p>
<?= $sub['total_marks'] ?> pending marks
</p>

<a class="btn view-btn"
href="?class_id=<?= $class_id ?>&subject_id=<?= $sub['subject_id'] ?>">

<i class="fas fa-eye"></i>
View Students
</a>

</div>

<?php endwhile; ?>

</div>

</div>

<?php endif; ?>

<!-- STUDENTS -->
<?php if($results && mysqli_num_rows($results) > 0): ?>

<?php
/* FETCH SUBJECT NAME */
$subject_info = mysqli_query($conn,
    "SELECT subject_name
     FROM subject
     WHERE subject_id='$subject_id'"
);

$subject_data = mysqli_fetch_assoc($subject_info);
?>

<div class="card">

<h3>
<i class="fas fa-users"></i>
Students Marks
</h3>

<div class="subject-title">

<i class="fas fa-book"></i>

Subject:
<?= htmlspecialchars($subject_data['subject_name']) ?>

</div>

<table>

<thead>

<tr>

<th>No</th>
<th>Student</th>
<th>Marks</th>
<th>Term</th>
<th>Year</th>

</tr>

</thead>

<tbody>

<?php
$count = 1;

while($row = mysqli_fetch_assoc($results)):
?>

<tr>

<td>
<?= $count++ ?>
</td>

<td>

<span class="badge">

<?= htmlspecialchars($row['full_name']) ?>

</span>

</td>

<td>

<span class="mark-badge">

<?= htmlspecialchars($row['marks']) ?>

</span>

</td>

<td>
<?= htmlspecialchars($row['term']) ?>
</td>

<td>
<?= htmlspecialchars($row['academic_year']) ?>
</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

<!-- ACTIONS -->
<div class="action-bar">

<a class="btn approve-btn"
href="?class_id=<?= $class_id ?>&approve_subject=<?= $subject_id ?>"
onclick="return confirm('Approve all students results for this subject?')">

<i class="fas fa-check"></i>
Approve Subject Results

</a>

<a class="btn reject-btn"
href="?class_id=<?= $class_id ?>&reject_subject=<?= $subject_id ?>"
onclick="return confirm('Reject all students results for this subject?')">

<i class="fas fa-times"></i>
Reject Subject Results

</a>

</div>

</div>

<?php endif; ?>

</div>

</body>
</html>