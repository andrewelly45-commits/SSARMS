<?php
session_start();
include '../db.php';

$admin = $_SESSION['admin'] ?? [];

// Check if admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}


$teachers = 0;
$subjects = 0;

// students
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM student");
if ($result) {
    $students = mysqli_fetch_assoc($result)['total'];
}

// teachers
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM teacher");
if ($result) {
    $teachers = mysqli_fetch_assoc($result)['total'];
}

// subjects
$result = mysqli_query($conn, "
    SELECT COUNT(DISTINCT subject_name) as total 
    FROM subject
");
if ($result) {
    $subjects = mysqli_fetch_assoc($result)['total'];
}

/* ================= CLASS STATISTICS ================= */

$class_stats = [];

$class_query = mysqli_query($conn, "
    SELECT 
        class.class_id,
        class.class_name,

        COUNT(student.student_id) AS total_students,

        SUM(CASE WHEN users.gender = 'Male' THEN 1 ELSE 0 END) AS total_males,

        SUM(CASE WHEN users.gender = 'Female' THEN 1 ELSE 0 END) AS total_females

    FROM class

    LEFT JOIN student 
        ON class.class_id = student.class_id

    LEFT JOIN users 
        ON student.user_id = users.user_id

    GROUP BY class.class_id, class.class_name

    ORDER BY class.class_name ASC
");

if ($class_query) {
    while ($row = mysqli_fetch_assoc($class_query)) {
        $class_stats[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SSARMS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', 'Inter', system-ui, Arial, sans-serif;
            background: #f0f4f8;
            min-height: 100vh;
        }

        .main {
            margin-left: 270px;
            padding: 85px 30px 30px;
        }

        .main h1 {
            font-size: 32px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 28px;
            border-left: 4px solid white;
            padding-left: 18px;
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 24px;
            margin-bottom: 30px;
        }

        /* Class Stats */
        .class-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .class-card {
            background: #ffffff;
            border-radius: 18px;
            padding: 22px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            transition: 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .class-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 18px rgba(0,0,0,0.08);
        }

        .class-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: ;
        }

        .class-header {
            display: flex;
            align-items: center;
            margin-bottom: 18px;
        }

        .class-header i {
            width: 50px;
            height: 50px;
            background: white;
            color: linear-gradient(180deg, #1a1a2e 0%, #0f0f23 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin-right: 14px;
        }

        .class-title {
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
        }

        .stats-row {
            display: flex;
            justify-content: space-between;
            margin-top: 14px;
            gap: 10px;
        }

        .stat-box {
            flex: 1;
            background: white;
            border-radius: 14px;
            padding: 14px 10px;
            text-align: center;
            border: 1px solid #e2e8f0;
        }

        .stat-box i {
            font-size: 18px;
            margin-bottom: 8px;
            display: block;
        }

        .stat-box.total i { color: linear-gradient(180deg, #1a1a2e 0%, #0f0f23 100%); }
        .stat-box.male i { color: #0f766e; }
        .stat-box.female i { color: #be185d; }

        .stat-number {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 13px;
            color: #64748b;
            font-weight: 600;
        }

        .card {
            background: white;
            padding: 22px 24px;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }

        .card h3 {
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 12px;
        }

        .card p {
            font-size: 42px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            line-height: 1.2;
        }

        .recent-section {
            margin-top: 30px;
        }

        .recent-section h2 {
            font-size: 20px;
            font-weight: 600;
            color: linear-gradient(180deg, #1a1a2e 0%, #0f0f23 100%);
            margin-bottom: 18px;
        }

        .recent-section h2 i {
            color: linear-gradient(180deg, #1a1a2e 0%, #0f0f23 100%);
            margin-right: 8px;
        }

        /* Mobile */
        .menu-toggle {
            display: none;
            position: fixed;
            top: 16px;
            left: 16px;
            z-index: 101;
            background: #1a1a2e;
            color: white;
            border: none;
            width: 44px;
            height: 44px;
            border-radius: 10px;
            font-size: 24px;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .main {
                margin-left: 0;
                padding: 80px 18px 20px;
            }
            
            .menu-toggle {
                display: block;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .main h1 {
                font-size: 26px;
                margin-top: 10px;
            }
            
            .stats-row {
                flex-direction: column;
            }
        }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #e2e8f0; }
        ::-webkit-scrollbar-thumb { background: #f59e0b; border-radius: 4px; }
    </style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>
<?php include 'admin_topbar.php'; ?>

<button class="menu-toggle" onclick="document.querySelector('.sidebar').classList.toggle('active')">☰</button>

<div class="main">
    <h1>Welcome Admin</h1>

    <div class="stats-container">
        <div class="card">
            <h3>Total Students</h3>
            <p><?php echo isset($students) ? number_format($students) : '0'; ?></p>
        </div>
        <div class="card">
            <h3>Total Teachers</h3>
            <p><?php echo isset($teachers) ? number_format($teachers) : '0'; ?></p>
        </div>
        <div class="card">
            <h3>Total Subjects</h3>
            <p><?php echo isset($subjects) ? number_format($subjects) : '0'; ?></p>
        </div>
    </div>

    <!-- Class Statistics -->
    <div class="recent-section">
        <h2><i class="fas fa-chart-pie"></i> Class Statistics</h2>
        <div class="class-stats-grid">
            <?php foreach ($class_stats as $class): ?>
            <div class="class-card">
                <div class="class-header">
                    <i class="fas fa-school"></i>
                    <div class="class-title"><?php echo htmlspecialchars($class['class_name']); ?></div>
                </div>
                <div class="stats-row">
                    <div class="stat-box total">
                        <i class="fas fa-users"></i>
                        <div class="stat-number"><?php echo $class['total_students']; ?></div>
                        <div class="stat-label">Students</div>
                    </div>
                    <div class="stat-box male">
                        <i class="fas fa-male"></i>
                        <div class="stat-number"><?php echo $class['total_males']; ?></div>
                        <div class="stat-label">Male</div>
                    </div>
                    <div class="stat-box female">
                        <i class="fas fa-female"></i>
                        <div class="stat-number"><?php echo $class['total_females']; ?></div>
                        <div class="stat-label">Female</div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>


</div>

<script>
// Simple sidebar toggle and active link
(function() {
    const sidebar = document.querySelector('.sidebar');
    const toggle = document.querySelector('.menu-toggle');
    const isMobile = () => window.innerWidth <= 768;
    
    // Toggle function
    if (toggle) {
        toggle.onclick = (e) => {
            e.stopPropagation();
            if (sidebar) sidebar.classList.toggle('active');
        };
    }
    
    // Close on outside click (mobile only)
    document.onclick = (e) => {
        if (isMobile() && sidebar?.classList.contains('active') && 
            !sidebar.contains(e.target) && !toggle?.contains(e.target)) {
            sidebar.classList.remove('active');
        }
    };
    
    // Active link highlight
    const current = window.location.pathname.split('/').pop() || 'admin_dashboard.php';
    document.querySelectorAll('.sidebar a').forEach(link => {
        if (link.getAttribute('href') === current) link.classList.add('active');
    });
})();
</script>


<?php include '../footer.php'; ?>

</body>
</html>