<?php
session_start();
include '../db.php';

/* =========================
   CHECK ADMIN
========================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

/* =========================
   GET IDS
========================= */
if (
    !isset($_GET['class_id']) ||
    !isset($_GET['subject_id'])
) {
    header("Location: manage_class_subject.php");
    exit();
}

$class_id = (int)$_GET['class_id'];
$subject_id = (int)$_GET['subject_id'];

/* =========================
   FETCH CLASS
========================= */
$class_query = mysqli_query($conn, "
    SELECT *
    FROM class
    WHERE class_id='$class_id'
");

if (mysqli_num_rows($class_query) == 0) {
    die("Class not found");
}

$class = mysqli_fetch_assoc($class_query);

/* =========================
   FETCH SUBJECT
========================= */
$subject_query = mysqli_query($conn, "
    SELECT *
    FROM subject
    WHERE subject_id='$subject_id'
");

if (mysqli_num_rows($subject_query) == 0) {
    die("Subject not found");
}

$subject = mysqli_fetch_assoc($subject_query);

/* =========================
   ASSIGN TEACHER
========================= */
if (isset($_POST['assign_teacher'])) {

    $teacher_id = (int)$_POST['teacher_id'];

    /* CHECK EXISTING */
    $check = mysqli_query($conn, "
        SELECT *
        FROM teacher_subject
        WHERE class_id='$class_id'
        AND subject_id='$subject_id'
    ");

    if (mysqli_num_rows($check) > 0) {

        mysqli_query($conn, "
            UPDATE teacher_subject
            SET teacher_id='$teacher_id'
            WHERE class_id='$class_id'
            AND subject_id='$subject_id'
        ");

    } else {

        mysqli_query($conn, "
            INSERT INTO teacher_subject
            (
                teacher_id,
                class_id,
                subject_id
            )
            VALUES
            (
                '$teacher_id',
                '$class_id',
                '$subject_id'
            )
        ");
    }

    header("Location: view_class_subjects.php?class_id=$class_id");
    exit();
}

/* =========================
   FETCH TEACHERS
========================= */
$teachers = mysqli_query($conn, "
    SELECT

        t.teacher_id,
        t.specialization,

        u.full_name

    FROM teacher t

    JOIN users u
        ON t.user_id = u.user_id

    ORDER BY u.full_name ASC
");

/* =========================
   CURRENT ASSIGNED TEACHER
========================= */
$current_teacher = mysqli_query($conn, "
    SELECT teacher_id
    FROM teacher_subject
    WHERE class_id='$class_id'
    AND subject_id='$subject_id'
");

$current_teacher_id = '';

if (mysqli_num_rows($current_teacher) > 0) {

    $current = mysqli_fetch_assoc($current_teacher);

    $current_teacher_id = $current['teacher_id'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
<title>Assign Teacher | SSARMS</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:'Segoe UI', Arial, sans-serif;
    background:#eef2ff;
    overflow-x:auto;
    min-width:1000px;
}

/* ========== SIDEBAR ========== */
.sidebar{
    position:fixed;
    top:0;
    left:0;
    width:270px;
    height:100%;
    background:linear-gradient(180deg,#0f172a 0%,#111827 100%);
    z-index:1050;
    transition:all 0.3s ease;
    box-shadow:8px 0 28px -12px rgba(0,0,0,0.25);
    overflow-y:auto;
}
body.sidebar-collapsed .sidebar{width:88px;}
body.sidebar-collapsed .sidebar .sidebar-nav span,
body.sidebar-collapsed .sidebar .logo-text{display:none;}
body.sidebar-collapsed .sidebar .nav-item{justify-content:center;padding:12px 0;}
.sidebar-header{
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:1.6rem 1.2rem;
    border-bottom:1px solid rgba(255,255,255,0.08);
}
.logo-area{
    display:flex;
    align-items:center;
    gap:10px;
    color:white;
    font-weight:700;
    font-size:1.3rem;
}
.logo-area i{
    font-size:1.8rem;
    background:linear-gradient(135deg,#60a5fa,#a78bfa);
    -webkit-background-clip:text;
    background-clip:text;
    color:transparent;
}
.toggle-sidebar-btn{
    background:rgba(255,255,255,0.08);
    border:none;
    color:#cbd5e1;
    width:34px;
    height:34px;
    border-radius:12px;
    cursor:pointer;
    font-size:1rem;
}
.toggle-sidebar-btn:hover{background:#3b82f6;color:white;}
.sidebar-nav{margin-top:2rem;padding:0 1rem;}
.nav-item{
    display:flex;
    align-items:center;
    gap:14px;
    padding:12px 18px;
    margin-bottom:8px;
    border-radius:20px;
    color:#e2e8f0;
    cursor:pointer;
    font-weight:500;
    white-space:nowrap;
}
.nav-item i{width:24px;font-size:1.2rem;}
.nav-item.active,.nav-item:hover{background:rgba(59,130,246,0.2);color:white;}

/* ========== TOPBAR ========== */
.admin-topbar{
    position:fixed;
    top:0;
    right:0;
    left:270px;
    height:72px;
    background:white;
    border-bottom:1px solid #eef2ff;
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:0 2rem;
    z-index:1040;
    transition:left 0.3s ease;
    box-shadow:0 2px 12px rgba(0,0,0,0.04);
}
body.sidebar-collapsed .admin-topbar{left:88px;}
.topbar-left{display:flex;align-items:center;gap:18px;}
.mobile-menu-btn{
    display:none;
    background:#f1f5f9;
    border:none;
    font-size:1.3rem;
    width:40px;
    height:40px;
    border-radius:30px;
    cursor:pointer;
}
.admin-profile{
    display:flex;
    align-items:center;
    gap:12px;
    background:#f8fafc;
    padding:6px 20px;
    border-radius:60px;
}

/* ========== MAIN CONTAINER ========== */
.main-container{
    margin-left:270px;
    margin-top:92px;
    padding:0 32px 48px 32px;
    transition:margin-left 0.3s ease;
    display:flex;
    justify-content:center;
}
body.sidebar-collapsed .main-container{margin-left:88px;}

/* ========== CARD ========== */
.card{
    background:white;
    padding:32px;
    border-radius:28px;
    box-shadow:0 12px 28px -12px rgba(0,0,0,0.06);
    border:1px solid #f0f2f9;
    width:100%;
    max-width:650px;
}

/* ========== TITLE ========== */
.title{
    font-size:26px;
    font-weight:800;
    background:linear-gradient(135deg,#1e2a5e,#2c3e66);
    -webkit-background-clip:text;
    background-clip:text;
    color:transparent;
    margin-bottom:25px;
    display:flex;
    align-items:center;
    gap:12px;
}
.title i{
    background:none;
    color:#3b82f6;
    -webkit-background-clip:unset;
}

/* ========== INFO BOX ========== */
.info-box{
    background:#f8fafc;
    border-left:5px solid #3b82f6;
    padding:18px 20px;
    border-radius:20px;
    margin-bottom:28px;
}
.info-box p{
    margin:10px 0;
    color:#334155;
    font-size:15px;
}
.info-box p strong{
    color:#0f172a;
    width:70px;
    display:inline-block;
}
.info-box i{
    color:#3b82f6;
    margin-right:8px;
}

/* ========== FORM ========== */
.input-group{
    margin-bottom:25px;
}
label{
    display:block;
    margin-bottom:8px;
    font-weight:700;
    font-size:13px;
    text-transform:uppercase;
    letter-spacing:0.5px;
    color:#475569;
}
label i{
    margin-right:6px;
    color:#3b82f6;
}
select{
    width:100%;
    padding:12px 16px;
    border:1.5px solid #e2e8f0;
    border-radius:44px;
    font-size:14px;
    background:#fefefe;
    cursor:pointer;
    transition:0.2s;
}
select:focus{
    outline:none;
    border-color:#3b82f6;
    box-shadow:0 0 0 3px rgba(59,130,246,0.12);
}
select:hover{
    border-color:#cbd5e1;
}

/* ========== BUTTONS ========== */
.button-group{
    display:flex;
    gap:12px;
    flex-wrap:wrap;
    margin-top:10px;
}
.btn{
    background:#2563eb;
    color:white;
    border:none;
    padding:12px 24px;
    border-radius:44px;
    cursor:pointer;
    font-size:14px;
    font-weight:600;
    text-decoration:none;
    display:inline-flex;
    align-items:center;
    gap:8px;
    transition:0.2s;
}
.btn:hover{
    background:#1d4ed8;
    transform:translateY(-1px);
}
.back-btn{
    background:#4b5563;
}
.back-btn:hover{
    background:#374151;
}

/* ========== ALERT ========== */
.alert{
    padding:14px 18px;
    border-radius:16px;
    margin-bottom:20px;
    display:flex;
    align-items:center;
    gap:10px;
    font-size:14px;
}
.alert.success{
    background:#dcfce7;
    color:#166534;
    border-left:4px solid #16a34a;
}
.alert.error{
    background:#fee2e2;
    color:#991b1b;
    border-left:4px solid #dc2626;
}
.alert i{font-size:18px;}

/* ========== RESPONSIVE ========== */
@media (max-width:768px){
    body{min-width:auto;}
    .sidebar{
        transform:translateX(-100%);
        width:260px;
    }
    body.sidebar-mobile-open .sidebar{transform:translateX(0);}
    .admin-topbar,.main-container{
        left:0!important;
        margin-left:0!important;
    }
    .mobile-menu-btn{
        display:flex;
        align-items:center;
        justify-content:center;
    }
    .main-container{
        margin-top:85px;
        padding:0 18px 30px;
    }
    .card{
        padding:20px;
    }
    .title{
        font-size:22px;
    }
    .info-box p strong{
        width:auto;
        display:block;
        margin-bottom:5px;
    }
    .button-group{
        flex-direction:column;
    }
    .btn{
        width:100%;
        justify-content:center;
    }
}
</style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>
<?php include 'admin_topbar.php'; ?>

<div class="main-container">
    <div class="card">

        <div class="title">
            <i class="fas fa-user-plus"></i>
            Assign Teacher
        </div>

        <!-- SUCCESS ALERT -->
        <?php if(!empty($success)): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <!-- ERROR ALERT -->
        <?php if(!empty($error)): ?>
            <div class="alert error">
                <i class="fas fa-times-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="info-box">
            <p>
                <i class="fas fa-school"></i>
                <strong>Class:</strong> 
                <?= htmlspecialchars($class['class_name'] ?? 'N/A') ?>
            </p>
            <p>
                <i class="fas fa-book"></i>
                <strong>Subject:</strong> 
                <?= htmlspecialchars($subject['subject_name'] ?? 'N/A') ?>
            </p>
            <?php if(!empty($current_teacher_name)): ?>
            <p>
                <i class="fas fa-chalkboard-user"></i>
                <strong>Current Teacher:</strong> 
                <?= htmlspecialchars($current_teacher_name) ?>
            </p>
            <?php endif; ?>
        </div>

        <form method="POST">
            <div class="input-group">
                <label><i class="fas fa-chalkboard-user"></i> Select Teacher</label>
                <select name="teacher_id" required>
                    <option value="">-- Select Teacher --</option>
                    <?php if(isset($teachers) && mysqli_num_rows($teachers) > 0): ?>
                        <?php while($teacher = mysqli_fetch_assoc($teachers)): ?>
                            <option 
                                value="<?= $teacher['teacher_id'] ?>"
                                <?= (isset($current_teacher_id) && $teacher['teacher_id'] == $current_teacher_id) ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($teacher['full_name']) ?>
                                <?php if (!empty($teacher['specialization'])): ?>
                                    (<?= htmlspecialchars($teacher['specialization']) ?>)
                                <?php endif; ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="button-group">
                <button type="submit" name="assign_teacher" class="btn">
                    <i class="fas fa-save"></i> Save Assignment
                </button>
                <a href="view_class_subjects.php?class_id=<?= $class_id ?? '' ?>" class="btn back-btn">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </form>

    </div>
</div>

<script>
// Sidebar toggle functionality
let toggleSidebar = document.querySelector('.toggle-sidebar-btn');
if(toggleSidebar) {
    toggleSidebar.onclick = function() {
        document.body.classList.toggle('sidebar-collapsed');
    };
}

let mobileToggle = document.querySelector('.mobile-menu-btn');
if(mobileToggle) {
    mobileToggle.onclick = function() {
        document.body.classList.toggle('sidebar-mobile-open');
    };
}
</script>

</body>
</html>