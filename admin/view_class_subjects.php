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
   GET CLASS ID
========================= */
if (!isset($_GET['class_id'])) {
    header("Location: manage_class_subject.php");
    exit();
}

$class_id = (int)$_GET['class_id'];

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
   DELETE SUBJECT
========================= */
if (isset($_GET['delete'])) {

    $id = (int)$_GET['delete'];

    mysqli_query($conn, "
        DELETE FROM class_subject
        WHERE id='$id'
    ");

    header("Location: view_class_subjects.php?class_id=$class_id");
    exit();
}

/* =========================
   FETCH SUBJECTS
========================= */
$subjects = mysqli_query($conn, "
    SELECT

        cs.id,

        s.subject_id,
        s.subject_name,

        t.teacher_id,
        u.full_name

    FROM class_subject cs

    JOIN subject s
        ON cs.subject_id = s.subject_id

    LEFT JOIN teacher_subject ts
        ON s.subject_id = ts.subject_id
        AND ts.class_id = cs.class_id

    LEFT JOIN teacher t
        ON ts.teacher_id = t.teacher_id

    LEFT JOIN users u
        ON t.user_id = u.user_id

    WHERE cs.class_id='$class_id'

    ORDER BY s.subject_name ASC
");
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
<title><?= htmlspecialchars($class['class_name'] ?? 'Class') ?> Subjects | SSARMS</title>
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
    width:auto;
}
body.sidebar-collapsed .main-container{margin-left:88px;}

/* ========== CARD ========== */
.card{
    background:white;
    padding:28px;
    border-radius:28px;
    box-shadow:0 12px 28px -12px rgba(0,0,0,0.06);
    border:1px solid #f0f2f9;
    width:100%;
}

/* ========== HEADER ========== */
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;
    gap:15px;
    margin-bottom:25px;
}
.title{
    font-size:26px;
    font-weight:800;
    background:linear-gradient(135deg,#1e2a5e,#2c3e66);
    -webkit-background-clip:text;
    background-clip:text;
    color:transparent;
    display:flex;
    align-items:center;
    gap:12px;
}
.title i{
    background:none;
    color:#3b82f6;
    -webkit-background-clip:unset;
}
.back-btn{
    background:#4b5563;
    color:white;
    padding:10px 20px;
    border-radius:44px;
    text-decoration:none;
    display:inline-flex;
    align-items:center;
    gap:8px;
    font-weight:600;
    font-size:14px;
    transition:0.2s;
}
.back-btn:hover{
    background:#374151;
    transform:translateY(-1px);
}

/* ========== TABLE ========== */
.table-wrapper{
    overflow-x:auto;
    width:100%;
}
table{
    width:100%;
    border-collapse:collapse;
    min-width:600px;
}
th{
    background:#1e293b;
    color:white;
    padding:14px 16px;
    text-align:left;
    font-weight:600;
    font-size:13px;
}
td{
    padding:14px 16px;
    border-bottom:1px solid #eef2ff;
    font-size:14px;
    color:#334155;
}
tr:hover td{
    background:#fefce8;
}

/* ========== BADGES ========== */
.subject-badge{
    background:#eff6ff;
    color:#1d4ed8;
    padding:6px 14px;
    border-radius:40px;
    font-size:13px;
    font-weight:600;
    display:inline-block;
}
.teacher-badge{
    background:#ecfdf5;
    color:#047857;
    padding:6px 14px;
    border-radius:40px;
    font-size:13px;
    font-weight:600;
    display:inline-flex;
    align-items:center;
    gap:6px;
}
.not-assigned{
    color:#94a3b8;
    font-style:italic;
    background:#f1f5f9;
    padding:6px 14px;
    border-radius:40px;
    display:inline-block;
}

/* ========== BUTTONS ========== */
.action-buttons{
    display:flex;
    gap:8px;
    flex-wrap:wrap;
}
.delete-btn{
    background:#ef4444;
    color:white;
    padding:8px 16px;
    border-radius:40px;
    text-decoration:none;
    display:inline-flex;
    align-items:center;
    gap:6px;
    font-size:12px;
    font-weight:600;
    transition:0.2s;
}
.delete-btn:hover{
    background:#dc2626;
    transform:translateY(-1px);
}
.assign-btn{
    background:#2563eb;
    color:white;
    padding:8px 16px;
    border-radius:40px;
    text-decoration:none;
    display:inline-flex;
    align-items:center;
    gap:6px;
    font-size:12px;
    font-weight:600;
    transition:0.2s;
}
.assign-btn:hover{
    background:#1d4ed8;
    transform:translateY(-1px);
}
.edit-btn{
    background:#0f766e;
    color:white;
    padding:8px 16px;
    border-radius:40px;
    text-decoration:none;
    display:inline-flex;
    align-items:center;
    gap:6px;
    font-size:12px;
    font-weight:600;
    transition:0.2s;
}
.edit-btn:hover{
    background:#0d5c56;
    transform:translateY(-1px);
}

/* ========== EMPTY ========== */
.empty{
    text-align:center;
    padding:48px;
    color:#94a3b8;
}
.empty i{
    font-size:48px;
    margin-bottom:15px;
    display:block;
}

/* ========== INFO BOX ========== */
.info-box{
    background:#f8fafc;
    padding:12px 20px;
    border-radius:16px;
    margin-bottom:20px;
    display:inline-flex;
    align-items:center;
    gap:10px;
    font-size:14px;
    color:#475569;
}
.info-box i{
    color:#3b82f6;
}

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
        padding:18px;
    }
    .title{
        font-size:22px;
    }
    .header{
        flex-direction:column;
        align-items:flex-start;
    }
    .action-buttons{
        flex-direction:column;
    }
    .action-buttons a{
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
        <div class="header">
            <div class="title">
                <i class="fas fa-book"></i>
                <?= htmlspecialchars($class['class_name'] ?? 'Class') ?> Subjects
            </div>
        </div>

        <div class="info-box">
            <i class="fas fa-info-circle"></i>
            Manage subjects assigned to this class and assign teachers
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Subject</th>
                        <th>Assigned Teacher</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($subjects) && mysqli_num_rows($subjects) > 0): ?>
                        <?php $count = 1; while($row = mysqli_fetch_assoc($subjects)): ?>
                        <tr>
                            <td><?= $count++ ?></td>
                            <td>
                                <span class="subject-badge">
                                    <i class="fas fa-book-open"></i>
                                    <?= htmlspecialchars($row['subject_name']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($row['full_name'])): ?>
                                    <span class="teacher-badge">
                                        <i class="fas fa-chalkboard-user"></i>
                                        <?= htmlspecialchars($row['full_name']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="not-assigned">
                                        <i class="fas fa-user-slash"></i> Not Assigned
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a class="assign-btn" href="assign_teacher.php?class_id=<?= $class_id ?>&subject_id=<?= $row['subject_id'] ?>">
                                        <i class="fas fa-user-plus"></i> Assign
                                    </a>
                                    <a class="edit-btn" href="edit_class_subject.php?id=<?= $row['id'] ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a class="delete-btn" href="?class_id=<?= $class_id ?>&delete=<?= $row['id'] ?>" onclick="return confirm('Delete this subject from class?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="empty">
                                <i class="fas fa-folder-open"></i>
                                No subjects assigned to this class.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
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