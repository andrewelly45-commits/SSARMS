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

/* ================= FETCH ALL TEACHERS WITH THEIR ASSIGNMENTS ================= */
$teachers_query = mysqli_query($conn, "
    SELECT 
        t.teacher_id,
        u.full_name,
        u.email,
        t.phone_no,
        u.status,
        d.department_name,
        d.department_id
    FROM teacher t
    INNER JOIN users u ON t.user_id = u.user_id
    LEFT JOIN department d ON t.department_id = d.department_id
    ORDER BY u.full_name ASC
");

/* ================= FETCH ASSIGNMENTS FOR EACH TEACHER ================= */
$teacher_assignments = [];

while ($teacher = mysqli_fetch_assoc($teachers_query)) {
    $teacher_id = $teacher['teacher_id'];
    
    // Fetch assigned classes with subjects for this teacher
    $assignments_query = mysqli_query($conn, "
        SELECT 
            c.class_id,
            c.class_name,
            GROUP_CONCAT(
                CONCAT(s.subject_name, ' (', ts.teacher_subject_id, ')') 
                ORDER BY s.subject_name ASC 
                SEPARATOR '|||'
            ) as subjects,
            COUNT(DISTINCT ts.subject_id) as subject_count
        FROM teacher_class tc
        INNER JOIN class c ON tc.class_id = c.class_id
        LEFT JOIN teacher_subject ts ON ts.teacher_id = tc.teacher_id AND ts.class_id = tc.class_id
        LEFT JOIN subject s ON ts.subject_id = s.subject_id
        WHERE tc.teacher_id = '$teacher_id'
        GROUP BY c.class_id, c.class_name
        ORDER BY c.class_name ASC
    ");
    
    $classes = [];
    while ($assignment = mysqli_fetch_assoc($assignments_query)) {
        $subjects = [];
        if ($assignment['subjects']) {
            $subject_list = explode('|||', $assignment['subjects']);
            foreach ($subject_list as $subject_item) {
                // Extract subject name (remove the ID in parentheses)
                $subject_name = preg_replace('/\s*\([^)]*\)\s*/', '', $subject_item);
                $subjects[] = $subject_name;
            }
        }
        
        $classes[] = [
            'class_name' => $assignment['class_name'],
            'subject_count' => $assignment['subject_count'],
            'subjects' => $subjects
        ];
    }
    
    $teacher['classes'] = $classes;
    $teacher_assignments[] = $teacher;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Assignments</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f1f5f9;
        }

        .main {
            margin-left: 270px;
            margin-top: 85px;
            padding: 30px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .page-header h1 {
            color: #1e293b;
            font-size: 28px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-header h1 i {
            color: #074591;
        }

        .search-box {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .search-box input {
            padding: 10px 16px;
            border: 1px solid #cbd5e1;
            border-radius: 30px;
            font-size: 14px;
            width: 250px;
            transition: all 0.3s;
        }

        .search-box input:focus {
            outline: none;
            border-color: #074591;
            box-shadow: 0 0 0 3px rgba(7, 69, 145, 0.1);
        }

        .search-box button {
            background: #074591;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .search-box button:hover {
            background: #05306a;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #1e293b;
        }

        .stat-card .label {
            color: #64748b;
            font-size: 14px;
            margin-top: 5px;
        }

        .stat-card .icon {
            font-size: 24px;
            margin-bottom: 8px;
        }

        /* Filter Buttons */
        .filter-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .filter-btn {
            padding: 8px 18px;
            border: 2px solid #e2e8f0;
            border-radius: 30px;
            background: white;
            color: #64748b;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .filter-btn:hover {
            border-color: #074591;
            color: #074591;
            transform: translateY(-2px);
        }

        .filter-btn.active {
            background: #074591;
            border-color: #074591;
            color: white;
        }

        .filter-btn.active i {
            color: white !important;
        }

        /* Teacher Cards */
        .teacher-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .teacher-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.3s;
        }

        .teacher-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .teacher-header {
            padding: 20px 24px;
            background: linear-gradient(135deg, #f8fafc 0%, #eef2f6 100%);
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .teacher-info {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .teacher-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #074591;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: bold;
        }

        .teacher-details h3 {
            color: #1e293b;
            font-size: 18px;
        }

        .teacher-details .sub-info {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 4px;
        }

        .teacher-details .sub-info span {
            color: #64748b;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .teacher-details .sub-info span i {
            color: #074591;
            width: 16px;
        }

        .teacher-status {
            padding: 6px 16px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .status-active {
            background: #dcfce7;
            color: #166534;
        }

        .status-suspended {
            background: #fef3c7;
            color: #92400e;
        }

        .teacher-body {
            padding: 20px 24px;
        }

        .class-section {
            margin-bottom: 15px;
        }

        .class-section:last-child {
            margin-bottom: 0;
        }

        .class-title {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            background: #f8fafc;
            border-radius: 10px;
            margin-bottom: 8px;
            border-left: 3px solid #074591;
        }

        .class-title h4 {
            color: #1e293b;
            font-size: 15px;
        }

        .class-title .subject-count {
            background: #e0f2fe;
            color: #0369a1;
            padding: 2px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            margin-left: auto;
        }

        .subject-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding-left: 20px;
            padding-bottom: 5px;
        }

        .subject-tag {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 13px;
            color: #334155;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s;
        }

        .subject-tag:hover {
            background: #e0f2fe;
            border-color: #074591;
            transform: translateY(-1px);
        }

        .subject-tag i {
            color: #074591;
            font-size: 12px;
        }

        .no-subjects {
            color: #94a3b8;
            font-size: 13px;
            font-style: italic;
            padding-left: 20px;
        }

        .no-classes {
            text-align: center;
            padding: 20px;
            color: #94a3b8;
        }

        .no-classes i {
            font-size: 40px;
            display: block;
            margin-bottom: 10px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 16px;
        }

        .empty-state i {
            font-size: 60px;
            color: #cbd5e1;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: #1e293b;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #64748b;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main {
                margin-left: 0;
                padding: 15px;
            }

            .page-header {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                flex-direction: column;
            }

            .search-box input {
                width: 100%;
            }

            .teacher-header {
                flex-direction: column;
                align-items: stretch;
            }

            .teacher-info {
                flex-direction: column;
                align-items: flex-start;
            }

            .teacher-status {
                align-self: flex-start;
            }

            .subject-list {
                padding-left: 10px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media print {
            .search-box, .filter-buttons {
                display: none;
            }
            .teacher-card {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>
<?php include '../auth/topbar.php'; ?>

<div class="main">
    <div class="page-header">
        <h1>
            <i class="fas fa-chalkboard-teacher"></i>
            Teacher Assignments
        </h1>
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search teacher, class, or subject..." onkeyup="filterTeachers()">
            <button onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>

    <!-- Statistics -->
    <?php
    $total_teachers = count($teacher_assignments);
    $total_classes = 0;
    $total_subjects = 0;
    $active_teachers = 0;
    $suspended_teachers = 0;

    foreach ($teacher_assignments as $teacher) {
        $status = strtolower($teacher['status'] ?? 'active');
        
        if ($status == 'active') {
            $active_teachers++;
        } elseif ($status == 'suspended') {
            $suspended_teachers++;
        } else {
            $active_teachers++; // default to active if status is null or something else
        }
        
        $total_classes += count($teacher['classes']);
        foreach ($teacher['classes'] as $class) {
            $total_subjects += $class['subject_count'];
        }
    }
    ?>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="icon"><i class="fas fa-users" style="color:#074591;"></i></div>
            <div class="number"><?= $total_teachers ?></div>
            <div class="label">Total Teachers</div>
        </div>
        <div class="stat-card">
            <div class="icon"><i class="fas fa-user-check" style="color:#16a34a;"></i></div>
            <div class="number"><?= $active_teachers ?></div>
            <div class="label">Active Teachers</div>
        </div>
        <div class="stat-card" style="border-left: 3px solid #f59e0b;">
            <div class="icon"><i class="fas fa-user-slash" style="color:#f59e0b;"></i></div>
            <div class="number"><?= $suspended_teachers ?></div>
            <div class="label">Suspended Teachers</div>
        </div>
        <div class="stat-card">
            <div class="icon"><i class="fas fa-school" style="color:#d97706;"></i></div>
            <div class="number"><?= $total_classes ?></div>
            <div class="label">Total Class Assignments</div>
        </div>
    </div>

    <!-- Filter Buttons -->
    <div class="filter-buttons">
        <button class="filter-btn active" data-filter="all" onclick="filterByStatus('all')">
            <i class="fas fa-users"></i> All Teachers
        </button>
        <button class="filter-btn" data-filter="active" onclick="filterByStatus('active')">
            <i class="fas fa-check-circle" style="color:#16a34a;"></i> Active
        </button>
        <button class="filter-btn" data-filter="suspended" onclick="filterByStatus('suspended')">
            <i class="fas fa-exclamation-triangle" style="color:#f59e0b;"></i> Suspended
        </button>
    </div>

    <!-- Teacher Cards -->
    <div class="teacher-grid" id="teacherGrid">
        <?php if(count($teacher_assignments) > 0): ?>
            <?php foreach($teacher_assignments as $teacher): ?>
                <div class="teacher-card" data-teacher="<?= strtolower($teacher['full_name']) ?>">
                    <div class="teacher-header">
                        <div class="teacher-info">
                            <div class="teacher-avatar">
                                <?= strtoupper(substr($teacher['full_name'], 0, 1)) ?>
                            </div>
                            <div class="teacher-details">
                                <h3><?= htmlspecialchars($teacher['full_name']) ?></h3>
                                <div class="sub-info">
                                    <span>
                                        <i class="fas fa-envelope"></i>
                                        <?= htmlspecialchars($teacher['email']) ?>
                                    </span>
                                    <?php if($teacher['phone_no']): ?>
                                        <span>
                                            <i class="fas fa-phone"></i>
                                            <?= htmlspecialchars($teacher['phone_no']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <span>
                                        <i class="fas fa-building"></i>
                                        <?= htmlspecialchars($teacher['department_name'] ?? 'No Department') ?>
                                    </span>
                                    <span>
                                        <i class="fas fa-id-badge"></i>
                                        ID: <?= htmlspecialchars($teacher['teacher_id']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php
                        // Determine status class and icon based on users table (only active or suspended)
                        $status_class = 'status-active';
                        $status_icon = 'fa-check-circle';
                        $status_text = ucfirst($teacher['status'] ?? 'Active');
                        
                        if (strtolower($teacher['status'] ?? 'active') == 'suspended') {
                            $status_class = 'status-suspended';
                            $status_icon = 'fa-exclamation-triangle';
                        }
                        ?>
                        <span class="teacher-status <?= $status_class ?>">
                            <i class="fas <?= $status_icon ?>"></i>
                            <?= $status_text ?>
                        </span>
                    </div>

                    <div class="teacher-body">
                        <?php if(count($teacher['classes']) > 0): ?>
                            <?php foreach($teacher['classes'] as $class): ?>
                                <div class="class-section">
                                    <div class="class-title">
                                        <i class="fas fa-door-open" style="color:#074591;"></i>
                                        <h4><?= htmlspecialchars($class['class_name']) ?></h4>
                                        <span class="subject-count">
                                            <i class="fas fa-book"></i>
                                            <?= $class['subject_count'] ?> Subject<?= $class['subject_count'] != 1 ? 's' : '' ?>
                                        </span>
                                    </div>
                                    <div class="subject-list">
                                        <?php if(count($class['subjects']) > 0): ?>
                                            <?php foreach($class['subjects'] as $subject): ?>
                                                <span class="subject-tag">
                                                    <i class="fas fa-book-open"></i>
                                                    <?= htmlspecialchars($subject) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="no-subjects">
                                                <i class="fas fa-info-circle"></i>
                                                No subjects assigned
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-classes">
                                <i class="fas fa-school" style="color:#cbd5e1;"></i>
                                <p style="color:#64748b;">No classes assigned to this teacher</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <h3>No Teachers Found</h3>
                <p>There are no teachers in the system yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
let currentFilter = 'all';

function filterByStatus(status) {
    currentFilter = status;
    
    // Update active button
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.dataset.filter === status) {
            btn.classList.add('active');
        }
    });
    
    // Apply filters
    applyFilters();
}

function applyFilters() {
    const input = document.getElementById('searchInput');
    const searchTerm = input.value.toLowerCase();
    const cards = document.getElementsByClassName('teacher-card');
    
    for (let i = 0; i < cards.length; i++) {
        const card = cards[i];
        const text = card.textContent.toLowerCase();
        const statusElement = card.querySelector('.teacher-status');
        const statusText = statusElement ? statusElement.textContent.trim().toLowerCase() : '';
        
        // Check if matches search
        const matchesSearch = text.includes(searchTerm);
        
        // Check if matches status filter
        let matchesStatus = true;
        if (currentFilter !== 'all') {
            matchesStatus = statusText.includes(currentFilter);
        }
        
        card.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
    }
}

function filterTeachers() {
    applyFilters();
}

// Real-time search with debounce
let searchTimeout;
document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(filterTeachers, 300);
});

// Initialize - show all
document.addEventListener('DOMContentLoaded', function() {
    applyFilters();
});
</script>

</body>
</html>