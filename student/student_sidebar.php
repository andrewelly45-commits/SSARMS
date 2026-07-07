<?php
// This file should be included in all student pages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================
// FIX: Get the latest profile picture
// ============================================================
// Check if we have student data with profile_pic
if (isset($student) && !empty($student['profile_pic'])) {
    // Check if the file exists in uploads folder
    if (file_exists('uploads/' . $student['profile_pic'])) {
        $profile_pic = 'uploads/' . $student['profile_pic'];
    } 
    // Check in parent directory (if sidebar is in student folder)
    elseif (file_exists('../uploads/' . $student['profile_pic'])) {
        $profile_pic = '../uploads/' . $student['profile_pic'];
    }
    else {
        $profile_pic = 'uploads/default.png';
    }
}
// If student data doesn't have profile_pic, check session
elseif (isset($_SESSION['profile_pic']) && !empty($_SESSION['profile_pic'])) {
    if (file_exists('uploads/' . $_SESSION['profile_pic'])) {
        $profile_pic = 'uploads/' . $_SESSION['profile_pic'];
    } elseif (file_exists('../uploads/' . $_SESSION['profile_pic'])) {
        $profile_pic = '../uploads/' . $_SESSION['profile_pic'];
    } else {
        $profile_pic = 'uploads/default.png';
    }
}
// Default fallback
else {
    $profile_pic = 'uploads/default.png';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        body {
            background: #f1f5f9;
        }

        /* ========== SIDEBAR ========== */
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

        /* ========== SIDEBAR BRAND ========== */
        .sidebar-brand {
            text-align: center;
            padding: 25px 15px;
            margin-bottom: 20px;
            background: rgba(255,255,255,.03);
        }

        .school-logo {
            width: 90px;
            height: 90px;
            object-fit: cover; /* Changed from 'contain' to 'cover' */
            border-radius: 50%; /* Added to make it round */
            margin-bottom: 15px;
            border: 3px solid #f59e0b; /* Optional: adds a golden border */
            padding: 3px; /* Optional: creates space between image and border */
            background: white; /* Optional: ensures clean background */
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

        /* MENU */
        .nav-links {
            padding: 0 15px;
        }

        .nav-links a {
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

        .nav-links a i {
            width: 22px;
            text-align: center;
        }

        .nav-links a:hover {
            background: linear-gradient(180deg,#1a1a2e 0%,#0f0f23 100%);
            color: white;
            transform: translateX(5px);
        }

        .nav-links a.active {
            background: linear-gradient(180deg,#1a1a2e 0%,#0f0f23 100%);
            color: white;
            box-shadow: 0 4px 10px rgba(245,158,11,0.3);
        }

        /* ========== MAIN CONTENT ========== */
        .main-content {
            margin-left: 270px;
            padding: 88px 32px 40px;
        }

        /* Mobile Toggle */
        .toggle-btn-mobile {
            display: none;
            position: fixed;
            top: 16px;
            left: 16px;
            background: #0f172a;
            color: white;
            border: none;
            padding: 12px 16px;
            border-radius: 40px;
            z-index: 1000;
            cursor: pointer;
            font-size: 16px;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: 0.3s;
        }

        .toggle-btn-mobile:hover {
            background: #f59e0b;
        }

        @media (max-width: 768px) {
            .sidebar {
                left: -300px;
            }
            .sidebar.active {
                left: 0;
            }
            .topbar {
                left: 0;
            }
            .main-content {
                margin-left: 0;
                padding: 80px 20px 30px;
            }
            .toggle-btn-mobile {
                display: flex;
            }
        }
    </style>
</head>
<body>

<!-- Mobile Menu Toggle -->
<button class="toggle-btn-mobile" id="mobileMenuToggle">
    <i class="fas fa-bars"></i> Menu
</button>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebarMain">

    <div class="sidebar-brand">
        <img src="../images/tyler.jpg" alt="School Logo" class="school-logo">
        <!-- Optional: Add school name below logo -->
        <!-- <h3>My School</h3> -->
        <!-- <p>Est. 2024</p> -->
    </div>

    <div class="nav-links">
        <a href="student_dashboard.php">
            <i class="fas fa-tachometer-alt"></i>
            Dashboard
        </a>

        <a href="my_subjects.php">
            <i class="fas fa-book-open"></i>
            My Subjects
        </a>

        <a href="my_result.php">
            <i class="fas fa-chart-line"></i>
            Results
        </a>
    </div>

</div>

<script>
(function() {
    const toggleBtn = document.getElementById('mobileMenuToggle');
    const sidebar = document.getElementById('sidebarMain');
    
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('active');
        });

        document.addEventListener('click', function(event) {
            const isMobile = window.innerWidth <= 768;
            if (isMobile && sidebar.classList.contains('active')) {
                if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });
    }

    window.addEventListener('resize', function() {
        if (window.innerWidth > 768 && sidebar) {
            sidebar.classList.remove('active');
        }
    });

    // Set active link based on current page
    const currentPage = window.location.pathname.split('/').pop();
    const allLinks = document.querySelectorAll('.nav-links a');
    
    allLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
})();
</script>

</body>
</html>