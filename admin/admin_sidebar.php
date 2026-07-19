<?php
if (!isset($_SESSION)) {
    session_start();
}

// ============================================================
// FIX: Get the latest profile picture for admin
// ============================================================
// Default fallback
$profile_pic = '../assets/user.png';

// Check if we have user data with profile_pic in session
if (isset($_SESSION['profile_pic']) && !empty($_SESSION['profile_pic'])) {
    // Check if the file exists in various locations
    $possible_paths = [
        '../uploads/' . $_SESSION['profile_pic'],
        'uploads/' . $_SESSION['profile_pic'],
        '../../uploads/' . $_SESSION['profile_pic'],
        $_SESSION['profile_pic'] // direct path if stored
    ];
    
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            $profile_pic = $path;
            break;
        }
    }
}

// If still default, try to get from database
if ($profile_pic == '../assets/user.png' && isset($_SESSION['user_id'])) {
    // Include db connection if not already included
    if (!isset($conn)) {
        include_once '../db.php';
    }
    
    if (isset($conn)) {
        $user_id = $_SESSION['user_id'];
        $query = mysqli_query($conn, "SELECT profile_pic, full_name FROM users WHERE user_id = '$user_id'");
        if ($query && mysqli_num_rows($query) > 0) {
            $row = mysqli_fetch_assoc($query);
            // Store full name in session if not already set
            if (!isset($_SESSION['full_name']) && !empty($row['full_name'])) {
                $_SESSION['full_name'] = $row['full_name'];
            }
            if (!empty($row['profile_pic'])) {
                // Check if file exists
                $paths = [
                    '../uploads/' . $row['profile_pic'],
                    'uploads/' . $row['profile_pic'],
                    '../../uploads/' . $row['profile_pic']
                ];
                foreach ($paths as $path) {
                    if (file_exists($path)) {
                        $profile_pic = $path;
                        // Store in session for next time
                        $_SESSION['profile_pic'] = $row['profile_pic'];
                        break;
                    }
                }
            }
        }
    }
}

// Set default logo path
$logo_path = '../images/tyler.jpg';
if (!file_exists($logo_path)) {
    $logo_path = '../uploads/logo.png';
}
if (!file_exists($logo_path)) {
    $logo_path = '../assets/logo.png';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SSARMS Admin Panel</title>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #f0f2f5;
}

/* SIDEBAR - Same as Teacher Sidebar */
.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 270px;
    height: 100vh;
    background: linear-gradient(180deg, #1a1a2e 0%, #0f0f23 100%);
    color: white;
    z-index: 1000;
    transition: all 0.3s ease;
    overflow-y: auto;
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    padding-top: 20px;
}

/* Custom Scrollbar - Amber */
.sidebar::-webkit-scrollbar {
    width: 5px;
}

.sidebar::-webkit-scrollbar-track {
    background: #2d3748;
}

.sidebar::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, #1a1a2e 0%, #0f0f23 100%);;
    border-radius: 5px;
}

/* ================= SIDEBAR BRAND (LOGO) ================= */
.sidebar-brand {
    text-align: center;
    padding: 19px 15px;
    margin-bottom: 20px;
    background: linear-gradient(180deg, #1a1a2e 0%, #0f0f23 100%);;
}

.school-logo {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 50%;
    margin-bottom: 15px;
    border: 3px solid #f59e0b;
    padding: 4px;
    background: white;
    transition: transform 0.3s ease;
}

.school-logo:hover {
    transform: scale(1.05);
}

/* MENU LINKS */
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

/* Hover effect - AMBER color */
.menu a:hover {
    background: linear-gradient(180deg, #1a1a2e 0%, #0f0f23 100%);
    color: white;
    transform: translateX(5px);
}

.menu a:hover i {
    color: linear-gradient(180deg, #1a1a2e 0%, #0f0f23 100%);
}

/* ACTIVE LINK - AMBER gradient */
.menu a.active {
    background: linear-gradient(180deg, #1a1a2e 0%, #0f0f23 100%);
    color: white;
    box-shadow: 0 4px 10px rgba(245,158,11,0.3);
}

.menu a.active i {
    color: white;
}


/* TOGGLE BUTTON */
.toggle-btn {
    display: none;
    position: fixed;
    top: 15px;
    left: 15px;
    background: #1a1a2e;
    color: white;
    border: none;
    padding: 12px 15px;
    border-radius: 10px;
    font-size: 18px;
    cursor: pointer;
    z-index: 1100;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    transition: 0.3s;
}

.toggle-btn:hover {
    background: #f59e0b;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .sidebar {
        left: -280px;
        width: 280px;
    }
    
    .sidebar.active {
        left: 0;
    }
    
    .toggle-btn {
        display: block;
    }
}

.main-content {
    margin-left: 270px;
    transition: 0.3s;
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
    }
}

/*  GLOBAL BUTTON STYLES (for consistency) */
.btn-primary {
    background: #f59e0b;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: #d97706;
    transform: translateY(-2px);
}

.btn-outline {
    background: transparent;
    border: 1px solid #f59e0b;
    color: #f59e0b;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-outline:hover {
    background: #f59e0b;
    color: white;
}

/* Card Styles */
.card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid #e2e8f0;
}

.card-header {
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 15px;
    margin-bottom: 15px;
    font-weight: 600;
    color: #1e293b;
}

/* Form Inputs */
input, select, textarea {
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 10px 12px;
    width: 100%;
    font-family: inherit;
    transition: all 0.3s ease;
}

input:focus, select:focus, textarea:focus {
    outline: none;
    border-color: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2);
}

/* Alert Messages */
.alert-success {
    background: #ecfdf5;
    border-left: 4px solid #10b981;
    color: #065f46;
    padding: 12px 16px;
    border-radius: 8px;
}

.alert-warning {
    background: #fffbeb;
    border-left: 4px solid #f59e0b;
    color: #92400e;
    padding: 12px 16px;
    border-radius: 8px;
}

.alert-danger {
    background: #fef2f2;
    border-left: 4px solid #ef4444;
    color: #991b1b;
    padding: 12px 16px;
    border-radius: 8px;
}

/* Table Styles */
.data-table {
    width: 100%;
    background: white;
    border-radius: 16px;
    overflow: hidden;
    border-collapse: collapse;
}

.data-table th {
    background: #f8fafc;
    padding: 12px 16px;
    text-align: left;
    font-weight: 600;
    color: #1e293b;
    border-bottom: 1px solid #e2e8f0;
}

.data-table td {
    padding: 12px 16px;
    border-bottom: 1px solid #e2e8f0;
    color: #334155;
}

.data-table tr:hover {
    background: #f8fafc;
}

/* Pagination */
.pagination {
    display: flex;
    gap: 8px;
    margin-top: 20px;
}

.pagination a {
    padding: 8px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    color: #334155;
    text-decoration: none;
    transition: all 0.3s ease;
}

.pagination a.active {
    background: #f59e0b;
    color: white;
    border-color: #f59e0b;
}

.pagination a:hover:not(.active) {
    background: #fffbeb;
    border-color: #f59e0b;
    color: #f59e0b;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile toggle
    const toggleBtn = document.querySelector('.toggle-btn');
    const sidebar = document.querySelector('.sidebar');
    
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
        
        document.addEventListener('click', function(event) {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });
    }
    
    // Active link highlighting
    const currentPage = window.location.pathname.split('/').pop();
    const allLinks = document.querySelectorAll('.menu a');
    
    allLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage) {
            link.classList.add('active');
        } else if (currentPage === '' && href === 'admin_dashboard.php') {
            link.classList.add('active');
        }
    });

    // Handle logo loading errors
    const logoImg = document.querySelector('.school-logo');
    if (logoImg) {
        logoImg.onerror = function() {
            // If logo fails to load, use default
            this.src = '../assets/logo.png';
        };
    }
});
</script>
</head>
<body>

<button class="toggle-btn">
    <i class="fas fa-bars"></i>
</button>

<div class="sidebar">

    <!-- SCHOOL LOGO - Profile Removed -->
    <div class="sidebar-brand">
        <img src="<?= htmlspecialchars($logo_path); ?>" alt="School Logo" class="school-logo">
    </div>


    <div class="menu">
        <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="manage_students.php"><i class="fas fa-user-graduate"></i> Manage Students</a>
        <a href="manage_teachers.php"><i class="fas fa-chalkboard-user"></i> Manage Teachers</a>
        <a href="view_teachers.php"><i class="fas fa-user"></i> View Teachers</a>
        <a href="manage_classes.php"><i class="fas fa-layer-group"></i> Manage Classes</a>
        <a href="manage_subjects.php"><i class="fas fa-book-open"></i> Manage Subjects</a>
        <a href="assign_class_subjects.php"><i class="fas fa-book"></i> Assign Class Subjects</a>
        <a href="manage_departments.php"><i class="fas fa-school"></i> Manage Departments</a>
        <a href="system_history.php"><i class="fas fa-history"></i> System History</a>
         <a href="system_settings.php"><i class="fas fa-cogs"></i> System Settings</a>
         <a href="system_backup.php"><i class="fas fa-database"></i> System Backup</a>
    </div>

</div>

</body>
</html>