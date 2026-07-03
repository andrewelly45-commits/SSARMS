<?php
session_start();
include '../db.php';


/* ================= AUTH CHECK ================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}


/* ================= SUSPEND STUDENT ================= */
if (isset($_GET['suspend'])) {

    $student_id = (int)$_GET['suspend'];

    mysqli_query($conn,
        "UPDATE student
         SET status='suspended'
         WHERE student_id='$student_id'"
    );

    header("Location: view_students.php");
    exit();
}

/* ================= ACTIVATE STUDENT ================= */
if (isset($_GET['activate'])) {

    $student_id = (int)$_GET['activate'];

    mysqli_query($conn,
        "UPDATE student
         SET status='active'
         WHERE student_id='$student_id'"
    );

    header("Location: view_students.php");
    exit();
}


/* ================= FILTERS ================= */
$class_filter = $_GET['class_id'] ?? '';
$search = $_GET['search'] ?? '';

/* ================= GET CLASSES ================= */
$classes = mysqli_query($conn, "
    SELECT *
    FROM class
    ORDER BY class_name ASC
");

/* ================= STUDENTS QUERY ================= */
$query = "
    SELECT
    student.student_id,
    student.user_id,
    student.class_id,
    student.status,
    student.date_of_birth,
    student.academic_year,
    users.full_name,
    users.gender,
    users.phone,
    users.email,
    users.profile_pic,
    class.class_name
FROM student
LEFT JOIN users ON student.user_id = users.user_id
LEFT JOIN class ON student.class_id = class.class_id
WHERE 1=1
";

if (!empty($class_filter)) {
    $class_filter = mysqli_real_escape_string($conn, $class_filter);
    $query .= " AND student.class_id = '$class_filter'";
}

if (!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $query .= " AND (users.full_name LIKE '%$search%' OR users.email LIKE '%$search%' OR users.phone LIKE '%$search%')";
}

$query .= " ORDER BY users.full_name ASC";
$students = mysqli_query($conn, $query);

// Get admin info for sidebar
$admin_result = mysqli_query($conn, "SELECT full_name FROM users WHERE user_id = '{$_SESSION['user_id']}'");
$admin = mysqli_fetch_assoc($admin_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Students | SSARMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f0f2f5;
            overflow-x: hidden;
        }

        /* ========== SIDEBAR STYLES ========== */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 270px;
            height: 100vh;
            background: linear-gradient(180deg, #1a1a2e 0%, #0f0f23 100%);
            color: white;
            z-index: 1000;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: #2d3748;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #f59e0b;
            border-radius: 5px;
        }

        .sidebar h2 {
            text-align: center;
            margin: 20px 0 25px 0;
            font-size: 20px;
            font-weight: 600;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .sidebar h2 i {
            color: #f59e0b;
            font-size: 24px;
        }

        .profile {
            text-align: center;
            padding: 20px 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
            background: rgba(255,255,255,0.03);
        }

        .profile img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 12px;
            border: 3px solid #f59e0b;
            padding: 3px;
            background: white;
        }

        .profile h4 {
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 5px 0;
            color: white;
        }

        .profile p {
            font-size: 12px;
            color: #94a3b8;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .profile p i {
            color: #f59e0b;
        }

        .menu {
            padding: 0 15px;
        }

        .menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            margin-bottom: 8px;
            color: #cbd5e1;
            text-decoration: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .menu a i {
            width: 22px;
            font-size: 16px;
            text-align: center;
        }

        .menu a:hover {
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            transform: translateX(5px);
        }

        .menu a.active {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            box-shadow: 0 4px 10px rgba(245,158,11,0.3);
        }

        .menu a.active i {
            color: white;
        }

        .bottom {
            margin-top: 30px;
            padding: 15px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .bottom a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            margin-bottom: 8px;
            color: #cbd5e1;
            text-decoration: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .bottom a:hover {
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            transform: translateX(5px);
        }

        .logout {
            color: #f87171 !important;
        }

        .logout:hover {
            background: rgba(245, 158, 11, 0.15) !important;
            color: #fbbf24 !important;
        }

        /* ========== TOPBAR STYLES ========== */
        .topbar {
            position: fixed;
            top: 0;
            right: 0;
            left: 270px;
            height: 60px;
            background: white;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 25px;
            z-index: 999;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .topbar-title {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .topbar-title i {
            color: #f59e0b;
            font-size: 18px;
        }

        .topbar-title span {
            font-size: 15px;
            font-weight: 600;
            color: #1e293b;
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f8fafc;
            padding: 5px 16px;
            border-radius: 40px;
        }

        .admin-info i {
            color: #f59e0b;
            font-size: 14px;
        }

        .admin-info span {
            font-size: 13px;
            font-weight: 500;
            color: #334155;
        }

        /* ========== MAIN CONTENT ========== */
        .main-content {
            margin-left: 270px;
            margin-top: 60px;
            padding: 20px 25px;
            min-height: 100vh;
        }

        /* ========== PAGE HEADER ========== */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .page-title {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page-title i {
            font-size: 24px;
            color: #f59e0b;
        }

        .page-title h1 {
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
        }

        .stats-badge {
            display: inline-block;
            background: white;
            padding: 6px 18px;
            border-radius: 30px;
            font-size: 13px;
            color: #1e293b;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
        }

        .stats-badge i {
            color: #f59e0b;
            margin-right: 6px;
        }

        /* ========== FILTER CARD ========== */
        .filter-card {
            background: white;
            border-radius: 14px;
            padding: 18px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
        }

        .filter-form {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .filter-group {
            flex: 1;
            min-width: 180px;
        }

        .filter-group label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: #486947;
            margin-bottom: 5px;
        }

        .filter-group label i {
            color: #f59e0b;
            width: 16px;
        }

        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 13px;
            outline: none;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2);
        }

        .btn-filter {
            background: #f59e0b;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-filter:hover {
            background: #d97706;
        }

        .btn-reset {
            background: #64748b;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }

        .btn-reset:hover {
            background: #475569;
        }

        /* Active Filters */
        .active-filters {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        .filter-tag {
            background: #f1f5f9;
            padding: 4px 10px;
            border-radius: 16px;
            font-size: 11px;
            color: #1e293b;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .filter-tag i {
            color: #f59e0b;
        }

        .filter-tag .remove {
            color: #ef4444;
            text-decoration: none;
            font-weight: bold;
        }

        /* ========== TABLE CARD ========== */
        .table-card {
            background: white;
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 750px;
        }

        th {
            background: #1a1a2e;
            color: white;
            padding: 12px 14px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
        }

        td {
            padding: 12px 14px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 13px;
            color: #334155;
        }

        tr:hover {
            background: #f8fafc;
        }

        .student-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .student-info img {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #f59e0b;
        }

        .student-name {
            font-weight: 700;
            color: #0f172a;
            font-size: 13px;
        }

        .student-email {
            font-size: 10px;
            color: #64748b;
        }

        .gender-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 600;
        }

        .gender-badge.male {
            background: #dcfce7;
            color: #166534;
        }

        .gender-badge.female {
            background: #fce7f3;
            color: #9d174d;
        }

        .class-badge {
            background: #f1f5f9;
            padding: 3px 8px;
            border-radius: 16px;
            font-size: 11px;
            display: inline-block;
        }

        .action-buttons {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            text-decoration: none;
        }

        .btn-action.view {
            background: #3b82f6;
            color: white;
        }

        .btn-action.edit {
            background: #10b981;
            color: white;
        }

        .btn-action.delete {
            background: #ef4444;
            color: white;
        }

        .btn-action:hover {
            opacity: 0.85;
        }

        .empty-state {
            text-align: center;
            padding: 50px;
            color: #94a3b8;
        }

        .empty-state i {
            font-size: 40px;
            margin-bottom: 12px;
            display: block;
        }

        /* Mobile */
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 12px;
            left: 12px;
            background: #1a1a2e;
            color: white;
            border: none;
            padding: 10px 13px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            z-index: 1100;
        }

        @media (max-width: 768px) {
            .sidebar {
                left: -280px;
            }
            .sidebar.active {
                left: 0;
            }
            .topbar {
                left: 0;
            }
            .main-content {
                margin-left: 0;
                margin-top: 60px;
                padding: 15px;
            }
            .mobile-toggle {
                display: block;
            }
        }
    </style>
</head>
<body>

<button class="mobile-toggle" onclick="document.getElementById('sidebar').classList.toggle('active')">
    <i class="fas fa-bars"></i>
</button>


<?php include 'admin_topbar.php'; ?>
<?php include 'admin_sidebar.php'; ?>

<!-- MAIN CONTENT -->
<div class="main-content">
    
    <div class="page-header">
        <div class="page-title">
            <i class="fas fa-users-viewfinder"></i>
            <h1>View Students</h1>
        </div>
        <div class="stats-badge">
            <i class="fas fa-users"></i>
            Total: <?= mysqli_num_rows($students) ?>
        </div>
    </div>

    <div class="filter-card">
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <label><i class="fas fa-search"></i> Search</label>
                <input type="text" name="search" placeholder="Name, email or phone..." value="<?= htmlspecialchars($search ?? '') ?>">
            </div>
            <div class="filter-group">
                <label><i class="fas fa-layer-group"></i> Class</label>
                <select name="class_id">
                    <option value="">All Classes</option>
                    <?php 
                    if (isset($classes) && mysqli_num_rows($classes) > 0):
                        mysqli_data_seek($classes, 0);
                        while($class = mysqli_fetch_assoc($classes)): 
                    ?>
                        <option value="<?= $class['class_id'] ?>" <?= (($class_filter ?? '') == $class['class_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($class['class_name']) ?>
                        </option>
                    <?php endwhile; endif; ?>
                </select>
            </div>
            <div class="filter-group">
                <label>&nbsp;</label>
                <button type="submit" class="btn-filter"><i class="fas fa-filter"></i> Filter</button>
            </div>
            <?php if (!empty($search) || !empty($class_filter)): ?>
            <div class="filter-group">
                <label>&nbsp;</label>
                <a href="view_students.php" class="btn-reset"><i class="fas fa-times"></i> Reset</a>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th><th>Student</th><th>Gender</th><th>Phone</th><th>Class</th><th>Year</th><th>Status</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($students) && mysqli_num_rows($students) > 0): ?>
                        <?php $counter = 1; while($row = mysqli_fetch_assoc($students)): ?>
                            <tr>
                                <td><?= $counter++ ?></td>
                                <td>
                                    <div class="student-info">
                                        <img src="../uploads/<?= !empty($row['profile_pic']) ? htmlspecialchars($row['profile_pic']) : 'default.png' ?>" alt="Profile">
                                        <div>
                                            <div class="student-name"><?= htmlspecialchars($row['full_name']) ?></div>
                                            <div class="student-email"><?= htmlspecialchars($row['email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="gender-badge <?= $row['gender'] ?>"><i class="fas fa-<?= $row['gender'] == 'male' ? 'mars' : 'venus' ?>"></i> <?= ucfirst($row['gender']) ?></span></td>
                                <td><?= htmlspecialchars($row['phone'] ?? '-') ?></td>
                                <td><span class="class-badge"><?= htmlspecialchars($row['class_name']) ?></span></td>
                                <td><?= htmlspecialchars($row['academic_year']) ?></td>
                                <td>
                                  <?php if($row['status'] == 'active'): ?>
                                 <span style="
                                   background:#dcfce7;
                                   color:#166534;
                                   padding:5px 12px;
                                   border-radius:20px;
                                   font-size:11px;
                                   font-weight:bold;">
                                   Active
                                 </span>

                                    <?php else: ?>
                                 <span style="
                                   background:#fee2e2;
                                   color:#991b1b;
                                   padding:5px 12px;
                                   border-radius:20px;
                                   font-size:11px;
                                   font-weight:bold;">
                                   Suspended
                                 </span>
                                 <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="student_profile.php?id=<?= $row['student_id'] ?>" class="btn-action view"><i class="fas fa-eye"></i> View</a>
                                        <a href="edit_student.php?id=<?= $row['student_id'] ?>" class="btn-action edit"><i class="fas fa-edit"></i> Edit</a>
                                        <?php if($row['status'] == 'active'): ?>

                                      <a href="?suspend=<?= $row['student_id'] ?>"
                                        class="btn-action"
                                        style="background:#f59e0b;color:white;"
                                        onclick="return confirm('Suspend this student?')">
                                        <i class="fas fa-user-slash"></i>
                                        Suspend
                                      </a>

                                      <?php else: ?>

                                      <a href="?activate=<?= $row['student_id'] ?>"
                                          class="btn-action"
                                          style="background:#10b981;color:white;"
                                          onclick="return confirm('Activate this student?')">
                                          <i class="fas fa-user-check"></i>
                                          Activate
                                      </a>

                                       <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7"><div class="empty-state"><i class="fas fa-folder-open"></i><p>No students found</p></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Simple mobile sidebar toggle
document.querySelector('.mobile-toggle')?.addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('active');
});

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(e) {
    if (window.innerWidth <= 768) {
        const sidebar = document.getElementById('sidebar');
        const toggle = document.querySelector('.mobile-toggle');
        if (sidebar?.classList.contains('active') && !sidebar.contains(e.target) && !toggle?.contains(e.target)) {
            sidebar.classList.remove('active');
        }
    }
});
</script>

</body>
</html>