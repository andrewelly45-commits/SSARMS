<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// ============================================================
// FIX: Get the latest profile picture for teacher
// ============================================================
// Default fallback
$profile_pic = '../uploads/default.png';

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
if ($profile_pic == '../uploads/default.png' && isset($_SESSION['user_id'])) {
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

// If still default, try to get teacher data
if ($profile_pic == '../uploads/default.png' && isset($teacher['profile_pic']) && !empty($teacher['profile_pic'])) {
    $paths = [
        '../uploads/' . $teacher['profile_pic'],
        'uploads/' . $teacher['profile_pic'],
        '../../uploads/' . $teacher['profile_pic']
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            $profile_pic = $path;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Teacher Panel</title>

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

/* SIDEBAR */
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

/* Custom Scrollbar */
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

/* TITLE */
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

/* ========== SIDEBAR BRAND (Logo) ========== */
.sidebar-brand {
    text-align: center;
    padding: 25px 15px;
    margin-bottom: 20px;
    background: rgba(255,255,255,.03);
}

.school-logo {
    width: 90px;
    height: 90px;
    object-fit: cover;
    border-radius: 50%;
    margin-bottom: 15px;
    border: 3px solid #f59e0b;
    padding: 3px;
    background: white;
}

.sidebar-brand h3 {
    color: #fff;
    font-size: 22px;
    font-weight: 700;
    margin-bottom: 8px;
    letter-spacing: 1px;
}

.sidebar-brand p {
    color: #94a3b8;
    font-size: 12px;
    line-height: 1.5;
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

.menu a:hover {
    background: linear-gradient(180deg,#1a1a2e 0%,#0f0f23 100%);
    color: white;
    transform: translateX(5px);
}

.menu a:hover i {
    color: white;
}

.menu a.active {
    background: linear-gradient(180deg,#1a1a2e 0%,#0f0f23 100%);
    color: white;
    box-shadow: 0 4px 10px rgba(245,158,11,.3);
}

.menu a.active i {
    color: white;
}

/* TOGGLE BUTTON FOR MOBILE */
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
    
    body.sidebar-open .main-content {
        margin-left: 0;
    }
}

.main-content{
    margin-left:270px;
    padding-top:80px;
}

@media(max-width:768px){
    .main-content{
        margin-left:0;
    }
}
</style>

<script>
// Toggle sidebar on mobile
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.querySelector('.toggle-btn');
    const sidebar = document.querySelector('.sidebar');
    
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        if (window.innerWidth <= 768) {
            if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
                sidebar.classList.remove('active');
            }
        }
    });
    
    // Set active link based on current page
    const currentPage = window.location.pathname.split('/').pop();
    const allLinks = document.querySelectorAll('.menu a');
    
    allLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage) {
            link.classList.add('active');
        } else if (currentPage === '' && href === 'teacher_dashboard.php') {
            link.classList.add('active');
        }
    });

    // Handle image loading errors
    const profileImg = document.querySelector('.profile img');
    if (profileImg) {
        profileImg.onerror = function() {
            // If image fails to load, use default
            this.src = '../uploads/default.png';
        };
    }

    // Function to update sidebar profile image
    window.updateTeacherSidebarProfile = function(imageUrl) {
        const profileImg = document.querySelector('.profile img');
        if (profileImg) {
            profileImg.src = imageUrl + '?t=' + new Date().getTime();
            // Add a small animation
            profileImg.style.transform = 'scale(0.9)';
            setTimeout(() => {
                profileImg.style.transform = 'scale(1)';
            }, 200);
        }
    };
});
</script>
</head>
<body>

<button class="toggle-btn">
    <i class="fas fa-bars"></i>
</button>

<!-- SIDEBAR -->
<div class="sidebar">

    <!-- SCHOOL LOGO -->
    <div class="sidebar-brand">
        <img src="../images/tyler.jpg" alt="School Logo" class="school-logo">
    </div>


    <!-- MENU -->
    <div class="menu">
        <a href="teacher_dashboard.php">
            <i class="fas fa-tachometer-alt"></i>
            Dashboard
        </a>

        <a href="my_classes.php">
            <i class="fas fa-school"></i>
            My Classes
        </a>

        <a href="my_subjects.php">
            <i class="fas fa-book-open"></i>
            My Subjects
        </a>

        <a href="enter_marks.php">
            <i class="fas fa-pen-fancy"></i>
            Enter Marks
        </a>

        <a href="view_marks.php">
            <i class="fas fa-chart-line"></i>
            View Marks
        </a>
    </div>

</div>

</body>
</html>