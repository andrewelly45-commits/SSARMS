<?php
// /auth/topbar.php - Universal Topbar for all users

// Get user info from session
$user_id = $_SESSION['user_id'] ?? 0;
$user_role = $_SESSION['role'] ?? 'academic';
$full_name = $_SESSION['full_name'] ?? 'User';

// Set default values if not set
if (empty($full_name)) {
    $full_name = 'User';
}

// Role badge class
$role_class = 'role-academic';
if ($user_role == 'admin') $role_class = 'role-admin';
elseif ($user_role == 'teacher') $role_class = 'role-teacher';
elseif ($user_role == 'student') $role_class = 'role-student';
else $role_class = 'role-academic';

// Get profile picture if exists
$profile_pic = $_SESSION['profile_pic'] ?? '';
$avatar_letter = strtoupper(substr($full_name, 0, 1));
?>

<!-- TOPBAR -->
<header class="topbar">
    <div class="topbar-left">
        <!-- Menu Toggle (Mobile) -->
        <button class="menu-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Brand/Logo -->
        <div class="brand">
            <div class="brand-logo">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <span class="brand-text">S<span>S</span>ARMS</span>
        </div>
    </div>

    <div class="topbar-right">
        <!-- User Profile -->
        <div class="user-profile" id="userProfile" onclick="toggleDropdown(event)">
            <div class="user-avatar">
                <?php if(!empty($profile_pic) && file_exists("../uploads/".$profile_pic)): ?>
                    <img src="../uploads/<?= htmlspecialchars($profile_pic) ?>" alt="Profile" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                <?php else: ?>
                    <?= $avatar_letter ?>
                <?php endif; ?>
            </div>
            <div class="user-info">
                <span class="user-name"><?= htmlspecialchars($full_name) ?></span>
                <span class="user-role">
                    <span class="role-badge <?= $role_class ?>">
                        <i class="fas fa-user"></i>
                        <?= ucfirst($user_role) ?>
                    </span>
                </span>
            </div>
            <i class="fas fa-chevron-down dropdown-arrow"></i>

            <!-- Dropdown -->
            <div class="dropdown-menu" id="dropdownMenu">
                <div class="dropdown-header">
                    <div class="name"><?= htmlspecialchars($full_name) ?></div>
                    <div class="email"><?= htmlspecialchars($_SESSION['email'] ?? '') ?></div>
                </div>
                
                <!-- Profile Link - Opens as Modal -->
                <a href="javascript:void(0)" class="dropdown-item" onclick="openProfileModal()">
                    <i class="fas fa-user-cog"></i> Profile
                </a>
                
                <div class="dropdown-divider"></div>
                <a href="../auth/logout.php" class="dropdown-item danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
</header>

<!-- Profile Modal Overlay -->
<div id="profileModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 99999; background: rgba(0,0,0,0.6);">
    <div style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; padding: 20px;">
        <div style="background: white; border-radius: 20px; max-width: 650px; width: 100%; max-height: 90vh; overflow: hidden; position: relative; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);">
            <button onclick="closeProfileModal()" style="position: absolute; top: 15px; right: 20px; background: rgba(0,0,0,0.1); border: none; font-size: 24px; color: #64748b; cursor: pointer; z-index: 10; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s;">
                <i class="fas fa-times"></i>
            </button>
            <iframe id="profileIframe" src="" style="width: 100%; height: 85vh; border: none; display: block;"></iframe>
        </div>
    </div>
</div>

<style>
.topbar {
    position: fixed;
    top: 0;
    left: 270px;
    right: 0;
    height: 65px;
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 30px;
    z-index: 999;
}

.topbar-left {
    display: flex;
    align-items: center;
    gap: 15px;
}

.menu-toggle {
    display: none;
    background: none;
    border: none;
    font-size: 22px;
    color: #1e293b;
    cursor: pointer;
    padding: 5px;
    transition: color 0.3s;
}

.menu-toggle:hover {
    color: #074591;
}

.brand {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
}

.brand-logo {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: black;
    font-size: 18px;
}

.brand-text {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
}

.brand-text span {
    color: #074591;
}

.topbar-right {
    display: flex;
    align-items: center;
    gap: 15px;
}

.user-profile {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    padding: 5px 12px 5px 5px;
    border-radius: 30px;
    transition: all 0.3s;
    position: relative;
}

.user-profile:hover {
    background: #f1f5f9;
}

.user-avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: linear-gradient(135deg, #074591, #0a5cb5);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 16px;
    overflow: hidden;
    flex-shrink: 0;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.user-name {
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-role {
    font-size: 11px;
    color: #94a3b8;
}

.role-badge {
    display: inline-block;
    padding: 1px 10px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
}

.role-admin {
    background: #ede9fe;
    color: #6b21a8;
}

.role-teacher {
    background: #dbeafe;
    color: #1d4ed8;
}

.role-academic {
    background: #d1fae5;
    color: #065f46;
}

.role-student {
    background: #fef3c7;
    color: #92400e;
}

.dropdown-arrow {
    color: #94a3b8;
    font-size: 12px;
    transition: transform 0.3s;
    flex-shrink: 0;
}

.user-profile.active .dropdown-arrow {
    transform: rotate(180deg);
}

.dropdown-menu {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    min-width: 220px;
    padding: 8px 0;
    display: none;
    z-index: 1000;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.dropdown-menu.show {
    display: block;
}

.dropdown-header {
    padding: 12px 20px;
    border-bottom: 1px solid #eef2f6;
}

.dropdown-header .name {
    font-weight: 600;
    color: #1e293b;
    font-size: 14px;
}

.dropdown-header .email {
    font-size: 12px;
    color: #94a3b8;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 20px;
    color: #334155;
    text-decoration: none;
    transition: all 0.2s;
    font-size: 14px;
    cursor: pointer;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
}

.dropdown-item:hover {
    background: #f8fafc;
    color: #074591;
}

.dropdown-item i {
    width: 18px;
    color: #94a3b8;
    text-align: center;
}

.dropdown-item:hover i {
    color: #074591;
}

.dropdown-divider {
    height: 1px;
    background: #eef2f6;
    margin: 4px 8px;
}

.dropdown-item.danger {
    color: #dc2626;
}

.dropdown-item.danger:hover {
    background: #fee2e2;
}

.dropdown-item.danger i {
    color: #dc2626;
}

/* Mobile Responsive */
@media (max-width: 992px) {
    .topbar {
        padding: 0 20px;
    }
    .brand-text {
        font-size: 18px;
    }
}

@media (max-width: 768px) {
    .topbar {
        left: 0;
        padding: 0 15px;
        height: 60px;
    }
    
    .menu-toggle {
        display: block;
    }
    
    .brand-text {
        font-size: 16px;
    }
    
    .brand-logo {
        width: 32px;
        height: 32px;
        font-size: 14px;
    }
    
    .user-info {
        display: none;
    }
    
    .user-profile {
        padding: 4px 8px 4px 4px;
    }
    
    .user-avatar {
        width: 32px;
        height: 32px;
        font-size: 13px;
    }
    
    .dropdown-menu {
        right: -10px;
        min-width: 200px;
    }
    
    #profileModal > div {
        padding: 10px;
    }
    
    #profileModal iframe {
        height: 70vh;
    }
}

@media (max-width: 480px) {
    .topbar {
        padding: 0 10px;
    }
    
    .brand-text {
        font-size: 14px;
    }
    
    .brand-logo {
        width: 28px;
        height: 28px;
        font-size: 12px;
    }
    
    .topbar-right {
        gap: 10px;
    }
    
    .dropdown-menu {
        min-width: 180px;
        right: -5px;
    }
}

/* Prevent body scroll when modal is open */
body.modal-open {
    overflow: hidden;
}
</style>

<script>
// ===== DROPDOWN FUNCTIONS =====

// Toggle Dropdown
function toggleDropdown(event) {
    if (event) {
        event.stopPropagation();
    }
    const dropdown = document.getElementById('dropdownMenu');
    const profile = document.getElementById('userProfile');
    
    if (dropdown) {
        dropdown.classList.toggle('show');
    }
    if (profile) {
        profile.classList.toggle('active');
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const profile = document.getElementById('userProfile');
    const dropdown = document.getElementById('dropdownMenu');
    
    if (profile && dropdown && !profile.contains(event.target)) {
        dropdown.classList.remove('show');
        profile.classList.remove('active');
    }
});

// Close dropdown on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const dropdown = document.getElementById('dropdownMenu');
        const profile = document.getElementById('userProfile');
        
        if (dropdown) dropdown.classList.remove('show');
        if (profile) profile.classList.remove('active');
        closeProfileModal();
    }
});

// ===== SIDEBAR FUNCTIONS =====

// Toggle Sidebar (Mobile)
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar') || document.querySelector('aside');
    if (sidebar) {
        sidebar.classList.toggle('active');
    }
}

// ===== PROFILE MODAL FUNCTIONS =====

// Open profile as modal
function openProfileModal() {
    const modal = document.getElementById('profileModal');
    const iframe = document.getElementById('profileIframe');
    
    // Use universal profile.php
    iframe.src = '../profile.php?modal=true';
    
    // Show modal
    modal.style.display = 'flex';
    document.body.classList.add('modal-open');
}

// Close profile modal
function closeProfileModal() {
    const modal = document.getElementById('profileModal');
    const iframe = document.getElementById('profileIframe');
    
    modal.style.display = 'none';
    iframe.src = '';
    document.body.classList.remove('modal-open');
}

// Close modal when clicking outside the content
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('profileModal');
    
    if (modal) {
        modal.addEventListener('click', function(event) {
            // Close only if clicking the backdrop (not the inner content)
            if (event.target === this || event.target === this.firstChild) {
                closeProfileModal();
            }
        });
    }
});

// Close modal on Escape key (already handled above)
// Expose close function to iframe
window.closeProfileModal = closeProfileModal;

// Handle iframe load (optional)
document.getElementById('profileIframe')?.addEventListener('load', function() {
    // You can add loading spinner logic here
    console.log('Profile loaded');
});

console.log('Topbar loaded successfully!');
console.log('User Role: <?= $user_role ?>');
console.log('User Name: <?= htmlspecialchars($full_name) ?>');
</script>