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

// Set default logo path
$logo_path = '../images/tyler.jpg';
if (!file_exists($logo_path)) {
    $logo_path = '../uploads/logo.png';
}
if (!file_exists($logo_path)) {
    $logo_path = '../assets/logo.png';
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

/* ================= SIDEBAR BRAND (LOGO) ================= */
.sidebar-brand {
    text-align: center;
    padding: 25px 15px;
    margin-bottom: 20px;
    background: rgba(255,255,255,0.03);
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

.sidebar-brand h3 {
    color: #fff;
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 5px;
    letter-spacing: 1px;
}

.sidebar-brand h3 span {
    color: #f59e0b;
}

.sidebar-brand p {
    color: #94a3b8;
    font-size: 12px;
    line-height: 1.5;
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

    <!-- SCHOOL LOGO - Profile Removed -->
    <div class="sidebar-brand">
        <img src="<?= htmlspecialchars($logo_path); ?>" alt="School Logo" class="school-logo">
       
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