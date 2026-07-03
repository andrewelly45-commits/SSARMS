<?php
if (!isset($_SESSION)) {
    session_start();
}
?>

<style>
/* ================= TOPBAR ================= */
.topbar {
    position: fixed;
    top: 0;
    left: 270px;
    right: 0;
    height: 65px;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 25px;
    border-bottom: 2px solid white;
    z-index: 1000;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

/* LEFT SIDE */
.topbar-left {
    display: flex;
    flex-direction: column;
}

.topbar-left h3 {
    font-size: 16px;
    color: #1f2937;
    margin: 0;
    font-weight: 700;
}

.topbar-left small {
    font-size: 11px;
    color: #6b7280;
}

/* RIGHT SIDE */
.topbar-right {
    display: flex;
    align-items: center;
    gap: 15px;
}

/* NOTIFICATION */
.notif {
    position: relative;
    font-size: 18px;
    color: #374151;
    cursor: pointer;
    padding: 6px 10px;
    border-radius: 10px;
    transition: 0.2s;
}

.notif:hover {
    background: #fef3c7;
    color: #b45309;
}

.notif span {
    position: absolute;
    top: 2px;
    right: 2px;
    background: red;
    color: white;
    font-size: 10px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* USER BOX */
.user-box {
    display: flex;
    align-items: center;
    gap: 10px;
    background: linear-gradient(180deg, #1f2937, #0f172a)
    padding: 6px 14px;
}

.user-box i {
    color: black;
}

.user-box span {
    font-size: 14px;
    font-weight: 600;
    color: black;
}

/* MOBILE */
@media (max-width: 768px) {
    .topbar {
        left: 0;
    }

    .topbar-left small {
        display: none;
    }
}
</style>

<div class="topbar">

    <!-- LEFT -->
    <div class="topbar-left">
        <h3>Academic Teacher Dashboard</h3>
    </div>

    <!-- RIGHT -->
    <div class="topbar-right">

        <!-- User -->
        <div class="user-box">
            <i class="fas fa-user-graduate"></i>
            <span style= "color: black;">
                <?= $_SESSION['full_name'] ?? 'Academic Teacher' ?>
            </span>
        </div>

    </div>

</div>