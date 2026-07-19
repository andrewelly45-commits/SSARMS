<?php
session_start();
include '../db.php';

// ============================================
// INCLUDE AUDIT LOGGER
// ============================================
if (!function_exists('logAction')) {
    $audit_paths = [
        '../audit_logger.php',
        'audit_logger.php',
        '../includes/audit_logger.php',
        '../../audit_logger.php',
        dirname(__DIR__) . '/audit_logger.php'
    ];
    
    $audit_loaded = false;
    foreach ($audit_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            $audit_loaded = true;
            break;
        }
    }
    
    if (!$audit_loaded) {
        function logAction($action_type, $module, $description, $status = 'success', $affected_id = null, $affected_table = null, $old_values = null, $new_values = null) {
            global $conn;
            error_log("AUDIT FALLBACK: [$action_type] [$module] $description - Status: $status");
            
            if (isset($conn) && $conn) {
                $user_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'System';
                $user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'system';
                $ip = $_SERVER['REMOTE_ADDR'] ?? '';
                $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                
                $query = "INSERT INTO audit_logs (user_name, user_role, action_type, module, action_description, status, ip_address, user_agent, affected_id, affected_table) 
                          VALUES (
                              '" . mysqli_real_escape_string($conn, $user_name) . "',
                              '" . mysqli_real_escape_string($conn, $user_role) . "',
                              '" . mysqli_real_escape_string($conn, $action_type) . "',
                              '" . mysqli_real_escape_string($conn, $module) . "',
                              '" . mysqli_real_escape_string($conn, $description) . "',
                              '" . mysqli_real_escape_string($conn, $status) . "',
                              '" . mysqli_real_escape_string($conn, $ip) . "',
                              '" . mysqli_real_escape_string($conn, $agent) . "',
                              " . ($affected_id ? (int)$affected_id : 'NULL') . ",
                              '" . mysqli_real_escape_string($conn, $affected_table) . "'
                          )";
                mysqli_query($conn, $query);
            }
            return true;
        }
    }
}

if(!isset($_SESSION['role']) || $_SESSION['role']!='admin'){
    if (function_exists('logAction')) {
        logAction('access_denied', 'departments', "Unauthorized access attempt to manage departments by: " . ($_SESSION['full_name'] ?? 'Unknown'), 'failed');
    }
    header("Location: ../auth/login.php");
    exit();
}

// ============================================
// LOG MANAGE DEPARTMENTS PAGE VIEW
// ============================================
if (function_exists('logAction')) {
    logAction('view', 'departments', "Admin viewed manage departments page", 'success', null, 'departments');
}

// ADD DEPARTMENT
if(isset($_POST['add_department'])){
    $name = mysqli_real_escape_string($conn, $_POST['department_name']);
    
    $check=mysqli_query($conn, "SELECT * FROM department WHERE department_name='$name'");
    
    if(mysqli_num_rows($check)>0){
        $_SESSION['error']="Department already exists!";
        if (function_exists('logAction')) {
            logAction('error', 'departments', "Failed to add department: $name already exists", 'failed');
        }
    }else{
        $insert = mysqli_query($conn, "INSERT INTO department(department_name) VALUES('$name')");
        if($insert){
            $_SESSION['success']="Department added successfully!";
            
            // Log successful department addition
            if (function_exists('logAction')) {
                logAction(
                    'add', 
                    'departments', 
                    "Added new department: $name", 
                    'success', 
                    mysqli_insert_id($conn), 
                    'departments',
                    null,
                    ['department_name' => $name]
                );
            }
        } else {
            $_SESSION['error']="Failed to add department!";
            if (function_exists('logAction')) {
                logAction('error', 'departments', "Failed to add department: $name - Database error", 'failed');
            }
        }
    }
    
    header("Location: manage_departments.php");
    exit();
}

// UPDATE DEPARTMENT
if(isset($_POST['update_department'])){
    $id=$_POST['department_id'];
    $name=mysqli_real_escape_string($conn, $_POST['department_name']);
    
    // Get old data for audit
    $old_query = mysqli_query($conn, "SELECT * FROM department WHERE department_id='$id'");
    $old_data = mysqli_fetch_assoc($old_query);
    
    $update = mysqli_query($conn, "UPDATE department SET department_name='$name' WHERE department_id='$id'");
    
    if($update){
        $_SESSION['success']="Department updated successfully!";
        
        // Log successful update
        if (function_exists('logAction')) {
            logAction(
                'edit', 
                'departments', 
                "Updated department: $name (ID: $id)", 
                'success', 
                $id, 
                'departments',
                $old_data,
                ['department_name' => $name]
            );
        }
    } else {
        $_SESSION['error']="Failed to update department!";
        if (function_exists('logAction')) {
            logAction('error', 'departments', "Failed to update department: $name - Database error", 'failed', $id, 'departments');
        }
    }
    
    header("Location: manage_departments.php");
    exit();
}

// DELETE DEPARTMENT
if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    
    // Get department info for logging
    $dept_query = mysqli_query($conn, "SELECT * FROM department WHERE department_id='$id'");
    $dept_info = mysqli_fetch_assoc($dept_query);
    
    // Check if department has teachers assigned
    $check_teachers = mysqli_query($conn, "SELECT COUNT(*) as count FROM teacher WHERE department_id='$id'");
    $teacher_count = mysqli_fetch_assoc($check_teachers);
    
    if($teacher_count['count'] > 0){
        $_SESSION['error'] = "Cannot delete department! It has " . $teacher_count['count'] . " teachers assigned.";
        if (function_exists('logAction') && $dept_info) {
            logAction('error', 'departments', "Cannot delete department: {$dept_info['department_name']} - Has " . $teacher_count['count'] . " teachers assigned", 'failed', $id, 'departments');
        }
    } else {
        // Check if department has subjects
        $check_subjects = mysqli_query($conn, "SELECT COUNT(*) as count FROM subject WHERE department_id='$id'");
        $subject_count = mysqli_fetch_assoc($check_subjects);
        
        if($subject_count['count'] > 0){
            $_SESSION['error'] = "Cannot delete department! It has " . $subject_count['count'] . " subjects assigned.";
            if (function_exists('logAction') && $dept_info) {
                logAction('error', 'departments', "Cannot delete department: {$dept_info['department_name']} - Has " . $subject_count['count'] . " subjects assigned", 'failed', $id, 'departments');
            }
        } else {
            $delete = mysqli_query($conn, "DELETE FROM department WHERE department_id='$id'");
            
            if($delete){
                $_SESSION['success']="Department deleted successfully!";
                
                // Log deletion
                if (function_exists('logAction') && $dept_info) {
                    logAction(
                        'delete', 
                        'departments', 
                        "Deleted department: {$dept_info['department_name']} (ID: $id)", 
                        'success', 
                        $id, 
                        'departments',
                        $dept_info,
                        null
                    );
                }
            } else {
                $_SESSION['error']="Failed to delete department!";
                if (function_exists('logAction') && $dept_info) {
                    logAction('error', 'departments', "Failed to delete department: {$dept_info['department_name']} - Database error", 'failed', $id, 'departments');
                }
            }
        }
    }
    
    header("Location: manage_departments.php");
    exit();
}

// FETCH DEPARTMENTS
$departments=mysqli_query($conn, "SELECT * FROM department ORDER BY department_name ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Departments</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
        }

        /* Main layout */
        .main-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 80px 20px 20px 20px;
            transition: all 0.3s ease;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .header-section h2 {
            color: #1a2332;
            font-size: 24px;
            font-weight: 600;
        }

        .department-count {
            background: #e2e8f0;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            color: #1a2332;
            font-weight: 500;
        }

        .add-department-form {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border: 1px solid #e2e8f0;
        }

        .add-department-form form {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }

        .add-department-form input {
            flex: 1;
            min-width: 250px;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .add-department-form input:focus {
            outline: none;
            border-color: #2563eb;
        }

        .btn {
            padding: 12px 28px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-success {
            background: #16a34a;
            color: white;
        }

        .btn-success:hover {
            background: #15803d;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
        }

        .btn-danger {
            background: #dc2626;
            color: white;
            padding: 6px 16px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
            font-size: 13px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }

        .btn-danger:hover {
            background: #b91c1c;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        }

        .btn-edit {
            background: #2563eb;
            color: white;
            padding: 6px 16px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
            font-size: 13px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }

        .btn-edit:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-cancel {
            background: #64748b;
            color: white;
            padding: 6px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.3s;
        }

        .btn-cancel:hover {
            background: #475569;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .table-wrapper {
            overflow-x: auto;
            margin-top: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 500px;
        }

        th {
            background: #1a2332;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }

        tr:hover {
            background: #f8fafc;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
        }

        .empty-state .empty-icon {
            font-size: 64px;
            display: block;
            margin-bottom: 15px;
            color: #cbd5e1;
        }

        .empty-state h3 {
            color: #1a2332;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #64748b;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s;
        }

        .modal-content {
            background: white;
            margin: 10% auto;
            padding: 30px;
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
            position: relative;
            animation: slideDown 0.3s;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
        }

        .modal-header h3 {
            color: #1a2332;
            font-size: 20px;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #64748b;
            transition: color 0.3s;
        }

        .modal-close:hover {
            color: #1a2332;
        }

        .modal-body {
            margin-bottom: 20px;
        }

        .modal-body label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1a2332;
        }

        .modal-body input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .modal-body input:focus {
            outline: none;
            border-color: #2563eb;
        }

        .modal-footer {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
        }

        /* Delete Modal specific */
        .delete-icon {
            text-align: center;
            font-size: 48px;
            margin-bottom: 15px;
        }

        .delete-modal-body {
            text-align: center;
        }

        .delete-modal-body p {
            color: #64748b;
            margin-bottom: 5px;
        }

        .delete-modal-body .department-name {
            font-weight: 600;
            color: #1a2332;
            font-size: 18px;
        }

        .btn-danger-modal {
            background: #dc2626;
            color: white;
            padding: 10px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-danger-modal:hover {
            background: #b91c1c;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        }

        /* Toast Notification */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 2000;
            animation: slideInRight 0.5s;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .toast-success {
            background: #16a34a;
        }

        .toast-error {
            background: #dc2626;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .main-content {
                margin-left: 0;
                padding: 80px 15px 15px 15px;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 70px 10px 10px 10px;
            }

            .container {
                padding: 20px;
            }

            .header-section {
                flex-direction: column;
                align-items: flex-start;
            }

            .add-department-form form {
                flex-direction: column;
                align-items: stretch;
            }

            .add-department-form input {
                min-width: unset;
                width: 100%;
            }

            .add-department-form .btn {
                width: 100%;
            }

            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }

            .btn-edit, .btn-danger, .btn-cancel {
                text-align: center;
                width: 100%;
            }

            .modal-content {
                margin: 20% auto;
                padding: 20px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 15px;
            }

            table {
                font-size: 12px;
            }

            th, td {
                padding: 10px 8px;
            }

            .header-section h2 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>

<div class="main-wrapper">
    <?php include 'admin_sidebar.php'; ?>
    <?php include '../auth/topbar.php'; ?>

    <div class="main-content">
        <div class="container">
            <div class="header-section">
                <h2>Manage Departments</h2>
                <span class="department-count">Total: <?= mysqli_num_rows($departments) ?> Departments</span>
            </div>

            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success" id="successAlert">
                    <?= $_SESSION['success'] ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-error" id="errorAlert">
                    <?= $_SESSION['error'] ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="add-department-form">
                <form method="POST" id="addDepartmentForm">
                    <input 
                        type="text"
                        name="department_name"
                        id="department_name"
                        placeholder="Enter department name..."
                        required
                    >
                    <button type="submit" name="add_department" class="btn btn-primary">
                        Add Department
                    </button>
                </form>
            </div>

            <div class="table-wrapper">
                <?php if(mysqli_num_rows($departments) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Department Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $i = 1;
                            while($row = mysqli_fetch_assoc($departments)): 
                            ?>
                            <tr id="row-<?= $row['department_id'] ?>">
                                <td><?= $i++ ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($row['department_name']) ?></strong>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-edit" onclick="openEditModal(<?= $row['department_id'] ?>, '<?= htmlspecialchars($row['department_name']) ?>')">
                                            Edit
                                        </button>
                                        <button class="btn-danger" onclick="openDeleteModal(<?= $row['department_id'] ?>, '<?= htmlspecialchars($row['department_name']) ?>')">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">📋</div>
                        <h3>No Departments Found</h3>
                        <p>Add your first department using the form above.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Department</h3>
            <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form method="POST" id="editForm">
            <input type="hidden" name="department_id" id="edit_department_id">
            <div class="modal-body">
                <label for="edit_department_name">Department Name</label>
                <input 
                    type="text" 
                    name="department_name" 
                    id="edit_department_name" 
                    required
                >
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" name="update_department" class="btn btn-success">Update Department</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirm Delete</h3>
            <button class="modal-close" onclick="closeModal('deleteModal')">&times;</button>
        </div>
        <div class="delete-modal-body">
            <div class="delete-icon"></div>
            <p>Are you sure you want to delete the department:</p>
            <p class="department-name" id="delete_department_name"></p>
            <p style="margin-top: 10px; color: #991b1b; font-size: 13px;">This action cannot be undone!</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeModal('deleteModal')">Cancel</button>
            <a href="#" id="deleteConfirmLink" class="btn-danger-modal">Delete Department</a>
        </div>
    </div>
</div>

<script>
// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const successAlert = document.getElementById('successAlert');
    const errorAlert = document.getElementById('errorAlert');
    
    if (successAlert) {
        setTimeout(() => {
            successAlert.style.transition = 'opacity 0.5s';
            successAlert.style.opacity = '0';
            setTimeout(() => successAlert.remove(), 500);
        }, 5000);
    }
    
    if (errorAlert) {
        setTimeout(() => {
            errorAlert.style.transition = 'opacity 0.5s';
            errorAlert.style.opacity = '0';
            setTimeout(() => errorAlert.remove(), 500);
        }, 5000);
    }
});

// Open Edit Modal
function openEditModal(id, name) {
    document.getElementById('edit_department_id').value = id;
    document.getElementById('edit_department_name').value = name;
    document.getElementById('editModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    // Focus the input after a small delay
    setTimeout(() => {
        document.getElementById('edit_department_name').focus();
        document.getElementById('edit_department_name').select();
    }, 100);
}

// Open Delete Modal
function openDeleteModal(id, name) {
    document.getElementById('delete_department_name').textContent = `"${name}"`;
    document.getElementById('deleteConfirmLink').href = `?delete=${id}`;
    document.getElementById('deleteModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

// Close Modal
function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const editModal = document.getElementById('editModal');
    const deleteModal = document.getElementById('deleteModal');
    
    if (event.target === editModal) {
        closeModal('editModal');
    }
    if (event.target === deleteModal) {
        closeModal('deleteModal');
    }
}

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const editModal = document.getElementById('editModal');
        const deleteModal = document.getElementById('deleteModal');
        
        if (editModal.style.display === 'block') {
            closeModal('editModal');
        }
        if (deleteModal.style.display === 'block') {
            closeModal('deleteModal');
        }
    }
});

// Form validation for add department
document.getElementById('addDepartmentForm').addEventListener('submit', function(e) {
    const input = document.getElementById('department_name');
    const value = input.value.trim();
    
    if (value === '') {
        e.preventDefault();
        input.style.borderColor = '#dc2626';
        showToast('Please enter a department name.', 'error');
        return false;
    }
    
    if (value.length < 2) {
        e.preventDefault();
        input.style.borderColor = '#dc2626';
        showToast('Department name must be at least 2 characters long.', 'error');
        return false;
    }
});

// Form validation for edit department
document.getElementById('editForm').addEventListener('submit', function(e) {
    const input = document.getElementById('edit_department_name');
    const value = input.value.trim();
    
    if (value === '') {
        e.preventDefault();
        input.style.borderColor = '#dc2626';
        showToast('Please enter a department name.', 'error');
        return false;
    }
    
    if (value.length < 2) {
        e.preventDefault();
        input.style.borderColor = '#dc2626';
        showToast('Department name must be at least 2 characters long.', 'error');
        return false;
    }
});

// Show toast notification
function showToast(message, type = 'success') {
    const existingToast = document.querySelector('.toast');
    if (existingToast) {
        existingToast.remove();
    }
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.transition = 'opacity 0.5s, transform 0.5s';
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100px)';
        setTimeout(() => toast.remove(), 500);
    }, 3000);
}

// Remove border color on input focus
document.querySelectorAll('input').forEach(input => {
    input.addEventListener('focus', function() {
        this.style.borderColor = '#2563eb';
    });
});
</script>

<?php include '../footer.php'; ?>
</body>
</html>