<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include '../db.php';

$user_id = $_SESSION['user_id'] ?? 0;

$admin = [];

if ($user_id) {

    $query = mysqli_query($conn, "
        SELECT *
        FROM users
        WHERE user_id = '$user_id'
    ");

    $admin = mysqli_fetch_assoc($query);
}
?>

<style>

.topbar {
    position: fixed;
    top: 0;
    left: 270px;
    width: calc(100% - 270px);
    height: 65px;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 30px;
    border-bottom: 2px solid white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    z-index: 100;
}

/* ================= LEFT SECTION ================= */

.topbar-left {
    display: flex;
    align-items: center;
    gap: 15px;
}

.menu-btn {
    width: 40px;
    height: 40px;
    border: none;
    border-radius: 10px;
    background: linear-gradient(180deg, #1a1a2e 0%, #0f0f23 100%);
    color: white;
    font-size: 18px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.menu-btn:hover {
    background: linear-gradient(180deg, #1a1a2e 0%, #0f0f23 100%);
    color: white;
    transform: scale(1.02);
}

.topbar-left h2 {
    font-size: 18px;
    font-weight: 700;
    color: linear-gradient(180deg, #1a1a2e 0%, #0f0f23 100%);
    margin: 0 0 3px 0;
}

.topbar-left p {
    font-size: 14px;
    color: linear-gradient(180deg, #1a1a2e 0%, #0f0f23 100%);
    margin: 0;
}


.topbar-right {
    display: flex;
    align-items: center;
}

.topbar-profile {
    display: flex;
    align-items: center;
    gap: 12px;
    background: white;
    padding: 8px 18px;
}

.topbar-profile h4 {
    font-size: 13px;
    font-weight: 700;
    color: linear-gradient(180deg, #1a1a2e 0%, #0f0f23 100%);
    margin: 0 0 2px 0;
}

.topbar-profile span {
    font-size: 10px;
    color: linear-gradient(180deg, #1a1a2e 0%, #0f0f23 100%);
    font-weight: 500;
}

.topbar-profile i {
    font-size: 20px;
    color: linear-gradient(180deg, #1a1a2e 0%, #0f0f23 100%);
}


@media (max-width: 768px) {
    .topbar {
        left: 0;
        width: 100%;
        height: 60px;
        padding: 0 15px;
    }

    .topbar-left h2 {
        font-size: 15px;
    }

    .topbar-left p {
        display: none;
    }

    .topbar-profile span {
        display: none;
    }
    
    .topbar-profile {
        padding: 5px 12px;
    }
    
    .menu-btn {
        width: 36px;
        height: 36px;
        font-size: 16px;
    }
}


</style>

<div class="topbar">

    <!-- LEFT SECTION -->
    <div class="topbar-left">

        <div>
            <h2>
                <i class="fas fa-school" style="color: linear-gradient(180deg, #1a1a2e 0%, #0f0f23 100%); font-size:16px; margin-right:6px;"></i>
                SSARMS Admin Panel
            </h2>
            <p>School Academic Record Management System</p>
        </div>

    </div>

    <!-- RIGHT SECTION -->
    <div class="topbar-right">

        <div class="topbar-profile">
            <i class="fas fa-user-shield"></i>
            <div>
                <h4>
                    <?php echo htmlspecialchars($admin['full_name'] ?? 'Administrator'); ?>
                </h4>
                <span>Administrator</span>
            </div>
        </div>

    </div>

</div>

<script>
// Ensure sidebar toggle works with this topbar
document.addEventListener('DOMContentLoaded', function() {
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            const sidebar = document.querySelector('.sidebar');
            const toggleBtn = document.querySelector('.menu-btn');
            if (sidebar && toggleBtn) {
                if (sidebar.classList.contains('active') && 
                    !sidebar.contains(e.target) && 
                    !toggleBtn.contains(e.target)) {
                    sidebar.classList.remove('active');
                }
            }
        }
    });
});
</script>