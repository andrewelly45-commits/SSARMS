<?php
if (!isset($_SESSION)) {
    session_start();
}

// ============================================================
// FIX: Get the latest profile picture for academic officer
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
        $query = mysqli_query($conn, "SELECT profile_pic FROM users WHERE user_id = '$user_id'");
        if ($query && mysqli_num_rows($query) > 0) {
            $row = mysqli_fetch_assoc($query);
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
?>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

/* ================= SIDEBAR ================= */
.sidebar{
    position:fixed;
    left:0;
    top:0;
    width:270px;
    height:100vh;
    background:linear-gradient(180deg,#1a1a2e 0%,#0f0f23 100%);
    color:white;
    z-index:1000;
    transition:all .3s ease;
    overflow-y:auto;
    box-shadow:2px 0 10px rgba(0,0,0,.15);
    padding-top:20px;
}

/* Scrollbar */
.sidebar::-webkit-scrollbar{
    width:5px;
}

.sidebar::-webkit-scrollbar-track{
    background:#2d3748;
}

.sidebar::-webkit-scrollbar-thumb{
    background:#f59e0b;
    border-radius:5px;
}

/* ================= PROFILE ================= */
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
    object-position: center;
    display: block;
    margin: 0 auto 12px auto;
    border: 3px solid #f59e0b;
    background: white;
}

.profile h4{
    color:white;
    font-size:16px;
    margin-bottom:5px;
}

.profile p{
    color:#94a3b8;
    font-size:12px;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:5px;
}

.profile p i{
    color:#f59e0b;
}

/* ================= MENU ================= */
.menu{
    padding:0 15px;
}

.menu a{
    display:flex;
    align-items:center;
    gap:12px;
    padding:12px 15px;
    margin-bottom:8px;
    color:#cbd5e1;
    text-decoration:none;
    border-radius:12px;
    font-size:14px;
    font-weight:500;
    transition:.3s;
}

.menu a i{
    width:22px;
    text-align:center;
}

.menu a:hover{
    background:linear-gradient(180deg,#1a1a2e 0%,#0f0f23 100%);
    color:white;
    transform:translateX(5px);
}

.menu a.active{
    background:linear-gradient(180deg,#1a1a2e 0%,#0f0f23 100%);
    color:white;
    box-shadow:0 4px 10px rgba(245,158,11,.3);
}

/* ================= BOTTOM ================= */
.bottom{
    margin-top:30px;
    padding:15px;
    border-top:1px solid rgba(255,255,255,.1);
}

.bottom a{
    display:flex;
    align-items:center;
    gap:12px;
    padding:12px 15px;
    margin-bottom:8px;
    color:#cbd5e1;
    text-decoration:none;
    border-radius:12px;
    font-size:14px;
    transition:.3s;
}

.bottom a:hover{
    background:rgba(245,158,11,.15);
    color:#fbbf24;
    transform:translateX(5px);
}

.logout{
    color:#f87171 !important;
}

.logout:hover{
    background:rgba(248,113,113,.15) !important;
    color:#f87171 !important;
}

/* ================= TOGGLE BUTTON ================= */
.toggle-btn{
    display:none;
    position:fixed;
    top:15px;
    left:15px;
    background:#1a1a2e;
    color:white;
    border:none;
    padding:12px 15px;
    border-radius:10px;
    font-size:18px;
    cursor:pointer;
    z-index:1100;
    box-shadow:0 2px 10px rgba(0,0,0,.2);
}

.toggle-btn:hover{
    background:#f59e0b;
}

/* ================= RESPONSIVE ================= */
@media(max-width:768px){

    .sidebar{
        left:-280px;
        width:280px;
    }

    .sidebar.active{
        left:0;
    }

    .toggle-btn{
        display:block;
    }
}

/* ================= CONTENT ================= */
.main-content{
    margin-left:270px;
    transition:.3s;
}

@media(max-width:768px){
    .main-content{
        margin-left:0;
    }
}
</style>

<!-- Mobile Button -->
<button class="toggle-btn">
    <i class="fas fa-bars"></i>
</button>

<!-- Sidebar -->
<div class="sidebar">

    <!-- Profile -->
    <div class="profile">
        <img src="<?= htmlspecialchars($profile_pic); ?>" alt="Academic Officer" id="sidebarProfileImage">

        <h4>
            <?= htmlspecialchars($_SESSION['full_name'] ?? 'Academic Teacher'); ?>
        </h4>

        <p>
            <i class="fas fa-user-tie"></i>
            Academic teacher
        </p>
    </div>

    <!-- Menu -->
    <div class="menu">

        <a href="academic_dashboard.php">
            <i class="fas fa-tachometer-alt"></i>
            Dashboard
        </a>

        <a href="my_classes.php">
            <i class="fas fa-school"></i>
            My Classes
        </a>

        <a href="my_subjects.php">
            <i class="fas fa-book-open"></i>
            Subjects
        </a>

        <a href="enter_marks.php">
            <i class="fas fa-pen"></i>
            Enter Marks
        </a>

        <a href="view_marks.php">
            <i class="fas fa-chart-line"></i>
            View Marks
        </a>

        <a href="view_results.php">
            <i class="fas fa-chart-line"></i>
            View Results
        </a>

        <a href="approve_result.php">
            <i class="fas fa-check-circle"></i>
            Approve Results
        </a>

    </div>

    <!-- Bottom -->
    <div class="bottom">

        <a href="../view_profile.php">
            <i class="fas fa-user-cog"></i>
            View Profile
        </a>

        <a href="../auth/logout.php" class="logout">
            <i class="fas fa-sign-out-alt"></i>
            Logout
        </a>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function(){

    const toggleBtn = document.querySelector('.toggle-btn');
    const sidebar = document.querySelector('.sidebar');

    if(toggleBtn && sidebar){

        toggleBtn.addEventListener('click', function(){
            sidebar.classList.toggle('active');
        });

        document.addEventListener('click', function(e){

            if(window.innerWidth <= 768){

                if(!sidebar.contains(e.target) &&
                   !toggleBtn.contains(e.target)){

                    sidebar.classList.remove('active');
                }
            }
        });
    }

    /* Active Menu */
    const currentPage =
        window.location.pathname.split('/').pop();

    document.querySelectorAll('.menu a').forEach(link => {

        const href = link.getAttribute('href');

        if(href === currentPage){
            link.classList.add('active');
        }
    });

    // Function to update sidebar profile image
    window.updateAcademicSidebarProfile = function(imageUrl) {
        const profileImg = document.getElementById('sidebarProfileImage');
        if (profileImg) {
            profileImg.src = imageUrl + '?t=' + new Date().getTime();
            // Add a small animation
            profileImg.style.transform = 'scale(0.9)';
            setTimeout(() => {
                profileImg.style.transform = 'scale(1)';
            }, 200);
        }
    };

    // Handle image loading errors
    const profileImg = document.getElementById('sidebarProfileImage');
    if (profileImg) {
        profileImg.onerror = function() {
            // If image fails to load, use default
            this.src = '../assets/user.png';
        };
    }
});
</script>