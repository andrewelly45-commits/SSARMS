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

/* PROFILE */
.profile {
    text-align: center;
    padding: 20px 15px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    margin-bottom: 20px;
    background: rgba(255,255,255,0.03);
}

.profile-img {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    object-fit: cover;
    display: block;
    margin: 0 auto 12px;
    border: 4px solid #f59e0b;
    background: white;
    padding: 3px;
    overflow: hidden;
}

.profile h4 {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 5px;
    color: white;
}

.profile p {
    font-size: 12px;
    color: #94a3b8;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 5px;
}

.profile p i {
    color: #f59e0b;
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

/* BOTTOM */
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
    transition: all 0.3s ease;
}

.bottom a:hover {
    background: rgba(245,158,11,0.15);
    color: #fbbf24;
    transform: translateX(5px);
}

.logout-link {
    color: #f87171 !important;
}

.logout-link:hover {
    background: rgba(248,113,113,0.15) !important;
    color: #f87171 !important;
}

        /* ========== TOPBAR ========== */
        .topbar {
            position: fixed;
            left: 270px;
            top: 0;
            right: 0;
            height: 64px;
            background: #ffffff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 32px;
            border-bottom: 1px solid #e9edf2;
            z-index: 99;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        }

        .topbar-title {
            font-weight: 600;
            font-size: 18px;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .topbar-title i {
            color: #030303ee;
            font-size: 20px;
        }

        .topbar-badge {
            padding: 6px 18px;
            border-radius: 40px;
            font-size: 13px;
            color: black;
            font-weight: 500;
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

    <div class="profile">
        <img class="profile-img"
           src="<?= htmlspecialchars($profile_pic); ?>"
           alt="Profile">
           
        <h4>
            <?= htmlspecialchars($student['full_name'] ?? 'Student'); ?>
        </h4>

        <p>
            <i class="fas fa-user-graduate"></i>
            Student
        </p>
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

    <div class="bottom">

        <a href="../view_profile.php">
            <i class="fas fa-user-cog"></i>
            View Profile
        </a>

        <a href="../auth/logout.php" class="logout-link">
            <i class="fas fa-sign-out-alt"></i>
            Logout
        </a>

    </div>

</div>

<!-- TOPBAR -->
<div class="topbar">
    <div class="topbar-title">
        <i class="fas fa-graduation-cap"></i> Student Portal
    </div>
    <div class="topbar-badge">
        <i class="fas fa-school"></i> <?= htmlspecialchars($student['class_name'] ?? 'Class'); ?>
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