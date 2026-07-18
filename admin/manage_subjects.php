<?php
session_start();
include '../db.php';

/* ================= CHECK ADMIN ================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

/* ================= ADD SUBJECT ================= */
if (isset($_POST['add_subject'])) {
    $subject_name = mysqli_real_escape_string($conn, trim($_POST['subject_name']));
    $department_id = (int)$_POST['department_id'];
    
    $check = mysqli_query($conn, "
        SELECT * FROM subject 
        WHERE subject_name = '$subject_name' 
        AND department_id = $department_id
    ");
    
    if (mysqli_num_rows($check) > 0) {
        $_SESSION['error_msg'] = "Subject already exists in this department!";
    } else {
        $insert = mysqli_query($conn, "
            INSERT INTO subject (subject_name, department_id) 
            VALUES ('$subject_name', $department_id)
        ");
        
        if ($insert) {
            $_SESSION['success_msg'] = "Subject added successfully!";
        } else {
            $_SESSION['error_msg'] = "Failed to add subject!";
        }
    }
    
    header("Location: manage_subjects.php");
    exit();
}

/* ================= UPDATE SUBJECT ================= */
if (isset($_POST['update_subject'])) {
    $subject_id = (int)$_POST['subject_id'];
    $subject_name = mysqli_real_escape_string($conn, trim($_POST['subject_name']));
    $department_id = (int)$_POST['department_id'];
    
    $update = mysqli_query($conn, "
        UPDATE subject 
        SET subject_name = '$subject_name', 
            department_id = $department_id
        WHERE subject_id = $subject_id
    ");
    
    if ($update) {
        $_SESSION['success_msg'] = "Subject updated successfully!";
    } else {
        $_SESSION['error_msg'] = "Failed to update subject!";
    }
    
    header("Location: manage_subjects.php");
    exit();
}

/* ================= DELETE SUBJECT ================= */
if (isset($_GET['delete_subject'])) {
    $subject_id = (int)$_GET['delete_subject'];
    
    $check = mysqli_query($conn, "
        SELECT * FROM teacher_subject WHERE subject_id = $subject_id
    ");
    
    if (mysqli_num_rows($check) > 0) {
        $_SESSION['error_msg'] = "Cannot delete subject! It is currently assigned to classes.";
    } else {
        $delete = mysqli_query($conn, "DELETE FROM subject WHERE subject_id = $subject_id");
        if ($delete) {
            $_SESSION['success_msg'] = "Subject deleted successfully!";
        } else {
            $_SESSION['error_msg'] = "Failed to delete subject!";
        }
    }
    
    header("Location: manage_subjects.php");
    exit();
}

/* ================= ASSIGN TEACHER TO SUBJECT FOR A CLASS ================= */
if (isset($_POST['assign_teacher_subject'])) {
    $teacher_id = (int)$_POST['teacher_id'];
    $class_id = (int)$_POST['class_id'];
    $subject_id = (int)$_POST['subject_id'];
    
    $check = mysqli_query($conn, "
        SELECT * FROM teacher_subject 
        WHERE teacher_id = $teacher_id 
        AND class_id = $class_id 
        AND subject_id = $subject_id
    ");
    
    if (mysqli_num_rows($check) > 0) {
        $_SESSION['error_msg'] = "This teacher is already assigned to this subject in this class!";
    } else {
        $insert = mysqli_query($conn, "
            INSERT INTO teacher_subject (teacher_id, class_id, subject_id) 
            VALUES ($teacher_id, $class_id, $subject_id)
        ");
        
        if ($insert) {
            $_SESSION['success_msg'] = "Teacher assigned to subject successfully!";
        } else {
            $_SESSION['error_msg'] = "Failed to assign teacher!";
        }
    }
    
    header("Location: manage_subjects.php");
    exit();
}

/* ================= REMOVE TEACHER FROM SUBJECT IN CLASS ================= */
if (isset($_GET['remove_assignment'])) {
    $teacher_subject_id = (int)$_GET['remove_assignment'];
    
    $delete = mysqli_query($conn, "
        DELETE FROM teacher_subject WHERE teacher_subject_id = $teacher_subject_id
    ");
    
    if ($delete) {
        $_SESSION['success_msg'] = "Assignment removed successfully!";
    } else {
        $_SESSION['error_msg'] = "Failed to remove assignment!";
    }
    
    header("Location: manage_subjects.php");
    exit();
}

/* ================= UPDATE TEACHER SUBJECT ASSIGNMENT ================= */
if (isset($_POST['update_assignment'])) {
    $teacher_subject_id = (int)$_POST['assignment_id'];
    $teacher_id = (int)$_POST['teacher_id'];
    $class_id = (int)$_POST['class_id'];
    $subject_id = (int)$_POST['subject_id'];
    
    $update = mysqli_query($conn, "
        UPDATE teacher_subject 
        SET teacher_id = $teacher_id, 
            class_id = $class_id, 
            subject_id = $subject_id
        WHERE teacher_subject_id = $teacher_subject_id
    ");
    
    if ($update) {
        $_SESSION['success_msg'] = "Assignment updated successfully!";
    } else {
        $_SESSION['error_msg'] = "Failed to update assignment!";
    }
    
    header("Location: manage_subjects.php");
    exit();
}

/* ================= FETCH DATA ================= */

// Fetch all departments
$departments = mysqli_query($conn, "
    SELECT * FROM department ORDER BY department_name ASC
");

// Fetch all teachers with user details
$teachers = mysqli_query($conn, "
    SELECT 
        t.teacher_id,
        u.full_name,
        t.phone_no,
        t.department_id,
        d.department_name,
        t.status
    FROM teacher t
    INNER JOIN users u ON t.user_id = u.user_id
    LEFT JOIN department d ON t.department_id = d.department_id
    WHERE t.status = 'active'
    ORDER BY u.full_name ASC
");

// Store teachers in array for JavaScript filtering
$teachers_array = [];
while ($teacher = mysqli_fetch_assoc($teachers)) {
    $teachers_array[] = $teacher;
}

// Fetch all classes
$classes = mysqli_query($conn, "
    SELECT * FROM class ORDER BY level ASC, class_name ASC
");

// Fetch all subjects with department info
$subjects = mysqli_query($conn, "
    SELECT 
        s.subject_id,
        s.subject_name,
        s.department_id,
        d.department_name
    FROM subject s
    LEFT JOIN department d ON s.department_id = d.department_id
    ORDER BY s.subject_name ASC
");

// Fetch all teacher-subject-class assignments with details
$assignments = mysqli_query($conn, "
    SELECT 
        ts.teacher_subject_id,
        ts.teacher_id,
        ts.class_id,
        ts.subject_id,
        u.full_name AS teacher_name,
        t.phone_no AS teacher_phone,
        t.department_id AS teacher_department_id,
        c.class_name,
        c.level,
        c.stream,
        s.subject_name,
        s.department_id AS subject_department_id,
        d.department_name
    FROM teacher_subject ts
    INNER JOIN teacher t ON ts.teacher_id = t.teacher_id
    INNER JOIN users u ON t.user_id = u.user_id
    INNER JOIN class c ON ts.class_id = c.class_id
    INNER JOIN subject s ON ts.subject_id = s.subject_id
    LEFT JOIN department d ON s.department_id = d.department_id
    ORDER BY c.level ASC, c.class_name ASC, s.subject_name ASC
");

// Group assignments by subject for display
$assignments_by_subject = [];
while ($row = mysqli_fetch_assoc($assignments)) {
    $subject_id = $row['subject_id'];
    if (!isset($assignments_by_subject[$subject_id])) {
        $assignments_by_subject[$subject_id] = [
            'subject_id' => $row['subject_id'],
            'subject_name' => $row['subject_name'],
            'department_id' => $row['subject_department_id'],
            'department_name' => $row['department_name'],
            'assignments' => []
        ];
    }
    $assignments_by_subject[$subject_id]['assignments'][] = [
        'teacher_subject_id' => $row['teacher_subject_id'],
        'teacher_id' => $row['teacher_id'],
        'teacher_name' => $row['teacher_name'],
        'teacher_phone' => $row['teacher_phone'],
        'teacher_department_id' => $row['teacher_department_id'],
        'class_id' => $row['class_id'],
        'class_name' => $row['class_name'],
        'level' => $row['level'],
        'stream' => $row['stream']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
<title>Manage Subjects | SSARMS</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: #f0f2f5;
    font-family: 'Segoe UI', Roboto, Arial, sans-serif;
}

.main-wrapper {
    display: flex;
    min-height: 100vh;
}

.content-wrapper {
    flex: 1;
    margin-left: 270px;
    margin-top: 85px;
    padding: 20px 30px;
    min-height: calc(100vh - 85px);
}

.container {
    max-width: 1400px;
    margin: 0 auto;
}

h2 {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 20px;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 10px;
}

h3 {
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 15px;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 8px;
}

.alert {
    padding: 14px 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 500;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-15px); }
    to { opacity: 1; transform: translateY(0); }
}

.alert-success {
    background: #dcfce7;
    border-left: 4px solid #22c55e;
    color: #166534;
}

.alert-error {
    background: #fee2e2;
    border-left: 4px solid #ef4444;
    color: #991b1b;
}

.card {
    background: white;
    padding: 24px;
    border-radius: 20px;
    margin-bottom: 25px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.card:last-child {
    margin-bottom: 0;
}

.grid-2 {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 25px;
}

@media (max-width: 1024px) {
    .grid-2 {
        grid-template-columns: 1fr;
    }
}

.input-group {
    display: flex;
    flex-direction: column;
    margin-bottom: 15px;
}

.input-group label {
    font-weight: 600;
    font-size: 12px;
    margin-bottom: 6px;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.input-group label .required {
    color: #dc2626;
}

input, select {
    padding: 10px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    font-size: 13px;
    transition: 0.2s;
    width: 100%;
    background: white;
}

input:focus, select:focus {
    border-color: #2563eb;
    outline: none;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

button {
    background: #1a2332;
    color: white;
    border: none;
    font-weight: 600;
    padding: 10px 22px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 13px;
    transition: 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

button:hover {
    background: #2563eb;
    transform: translateY(-1px);
}

.btn-success {
    background: #16a34a;
}

.btn-success:hover {
    background: #15803d;
}

.btn-danger {
    background: #dc2626;
}

.btn-danger:hover {
    background: #b91c1c;
}

.btn-sm {
    padding: 5px 14px;
    font-size: 11px;
    border-radius: 8px;
}

.table-wrapper {
    overflow-x: auto;
    margin-top: 10px;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 600px;
}

th {
    text-align: left;
    padding: 12px 14px;
    background: #1a1a2e;
    color: white;
    font-weight: 600;
    font-size: 12px;
    position: sticky;
    top: 0;
    z-index: 10;
}

td {
    padding: 12px 14px;
    border-bottom: 1px solid #e2e8f0;
    font-size: 13px;
    color: #334155;
    vertical-align: middle;
}

tr:hover td {
    background: #f8fafc;
}

.subject-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 15px;
    margin-bottom: 15px;
    transition: border-color 0.3s;
}

.subject-card:hover {
    border-color: #94a3b8;
}

.subject-card h4 {
    color: #1a2332;
    margin-bottom: 10px;
    font-size: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}

.subject-card .dept-badge {
    font-size: 11px;
    font-weight: normal;
    color: #64748b;
    background: #e2e8f0;
    padding: 2px 12px;
    border-radius: 12px;
}

.class-assignment {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 12px;
    background: white;
    border-radius: 8px;
    margin-bottom: 6px;
    border: 1px solid #e2e8f0;
    transition: border-color 0.2s;
}

.class-assignment:hover {
    border-color: #94a3b8;
}

.class-badge {
    background: #1a2332;
    color: white;
    padding: 2px 12px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 12px;
    min-width: 80px;
    text-align: center;
}

.teacher-name {
    font-weight: 500;
    color: #1a2332;
}

.teacher-phone {
    font-size: 11px;
    color: #94a3b8;
}

.unassigned {
    color: #94a3b8;
    font-style: italic;
    font-size: 12px;
}

.action-links {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}

.action-links a, .action-links button {
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    border: none;
    cursor: pointer;
}

.btn-edit {
    background: #dbeafe;
    color: #1d4ed8;
}

.btn-edit:hover {
    background: #bfdbfe;
}

.btn-remove {
    background: #fee2e2;
    color: #dc2626;
}

.btn-remove:hover {
    background: #fecaca;
}

.btn-delete {
    background: #fee2e2;
    color: #dc2626;
}

.btn-delete:hover {
    background: #fecaca;
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1100;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 24px;
    padding: 24px;
    max-width: 550px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e2e8f0;
}

.modal-header h3 {
    margin-bottom: 0;
}

.close {
    font-size: 28px;
    cursor: pointer;
    color: #94a3b8;
    line-height: 1;
    background: none;
    border: none;
    padding: 0 5px;
}

.close:hover {
    color: #000;
    transform: none;
}

.mobile-toggle {
    display: none;
    position: fixed;
    top: 15px;
    left: 15px;
    background: #1a1a2e;
    color: white;
    border: none;
    padding: 12px 15px;
    border-radius: 10px;
    cursor: pointer;
    z-index: 1100;
}

.mobile-toggle:hover {
    background: #2d2d44;
    transform: none;
}

.status-badge {
    padding: 2px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.status-active {
    background: #dcfce7;
    color: #166534;
}

.status-inactive {
    background: #fee2e2;
    color: #991b1b;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #94a3b8;
}

.empty-state i {
    font-size: 48px;
    display: block;
    margin-bottom: 15px;
    color: #cbd5e1;
}

.stream-badge {
    background: #e0f2fe;
    color: #0369a1;
    padding: 1px 8px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 600;
}

@media (max-width: 768px) {
    .content-wrapper {
        margin-left: 0;
        margin-top: 80px;
        padding: 15px;
    }
    
    .mobile-toggle {
        display: block;
    }
    
    .class-assignment {
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .action-links {
        margin-top: 5px;
        width: 100%;
        justify-content: flex-start;
    }
    
    .subject-card h4 {
        flex-direction: column;
        align-items: flex-start;
    }
}

@media (max-width: 480px) {
    .content-wrapper {
        padding: 10px;
    }
    
    .card {
        padding: 15px;
    }
    
    .grid-2 {
        gap: 15px;
    }
}
</style>
</head>
<body>

<button class="mobile-toggle" onclick="document.getElementById('sidebar').classList.toggle('active')">
    <i class="fas fa-bars"></i>
</button>

<div class="main-wrapper">
    <?php include 'admin_sidebar.php'; ?>
    <?php include '../auth/topbar.php'; ?>

    <div class="content-wrapper">
        <div class="container">
            <h2><i class="fas fa-book-open"></i> Manage Subjects & Teacher Assignments</h2>

            <!-- Alert Messages -->
            <?php if (isset($_SESSION['success_msg'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?= htmlspecialchars($_SESSION['success_msg']) ?></span>
                </div>
                <?php unset($_SESSION['success_msg']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_msg'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span><?= htmlspecialchars($_SESSION['error_msg']) ?></span>
                </div>
                <?php unset($_SESSION['error_msg']); ?>
            <?php endif; ?>

            <!-- MAIN GRID -->
            <div class="grid-2">
                <!-- LEFT COLUMN: Add Subject & Assign Teacher -->
                <div>
                    <!-- Add Subject Form -->
                    <div class="card">
                        <h3><i class="fas fa-plus-circle"></i> Add New Subject</h3>
                        <form method="POST">
                            <div class="input-group">
                                <label>Subject Name <span class="required">*</span></label>
                                <input type="text" name="subject_name" placeholder="e.g., Mathematics" required>
                            </div>
                            <div class="input-group">
                                <label>Department <span class="required">*</span></label>
                                <select name="department_id" required>
                                    <option value="">Select Department</option>
                                    <?php 
                                    mysqli_data_seek($departments, 0);
                                    while($dept = mysqli_fetch_assoc($departments)): 
                                    ?>
                                        <option value="<?= $dept['department_id'] ?>">
                                            <?= htmlspecialchars($dept['department_name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <button type="submit" name="add_subject">
                                <i class="fas fa-save"></i> Add Subject
                            </button>
                        </form>
                    </div>

                    <!-- Assign Teacher to Subject for a Class -->
                    <div class="card">
                        <h3><i class="fas fa-user-graduate"></i> Assign Teacher to Subject</h3>
                        <form method="POST">
                            <div class="input-group">
                                <label>Select Subject <span class="required">*</span></label>
                                <select name="subject_id" id="assign_subject_select" required onchange="filterTeachersBySubject()">
                                    <option value="">Choose Subject</option>
                                    <?php 
                                    mysqli_data_seek($subjects, 0);
                                    while($subject = mysqli_fetch_assoc($subjects)): 
                                    ?>
                                        <option value="<?= $subject['subject_id'] ?>" data-department="<?= $subject['department_id'] ?>">
                                            <?= htmlspecialchars($subject['subject_name']) ?>
                                            (<?= htmlspecialchars($subject['department_name'] ?? 'No Dept') ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="input-group">
                                <label>Select Teacher <span class="required">*</span></label>
                                <select name="teacher_id" id="assign_teacher_select" required>
                                    <option value="">Choose Teacher</option>
                                    <?php foreach($teachers_array as $teacher): ?>
                                        <option value="<?= $teacher['teacher_id'] ?>" data-department="<?= $teacher['department_id'] ?>">
                                            <?= htmlspecialchars($teacher['full_name']) ?>
                                            <?php if($teacher['phone_no']): ?>
                                                (<?= htmlspecialchars($teacher['phone_no']) ?>)
                                            <?php endif; ?>
                                            - <?= htmlspecialchars($teacher['department_name'] ?? 'No Dept') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="input-group">
                                <label>Select Class <span class="required">*</span></label>
                                <select name="class_id" required>
                                    <option value="">Choose Class</option>
                                    <?php 
                                    mysqli_data_seek($classes, 0);
                                    while($class = mysqli_fetch_assoc($classes)): 
                                    ?>
                                        <option value="<?= $class['class_id'] ?>">
                                            <?= htmlspecialchars($class['class_name']) ?>
                                            <?php if($class['stream']): ?>
                                                (<?= htmlspecialchars($class['stream']) ?>)
                                            <?php endif; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <button type="submit" name="assign_teacher_subject" class="btn-success">
                                <i class="fas fa-check-circle"></i> Assign Teacher
                            </button>
                        </form>
                    </div>
                </div>

                <!-- RIGHT COLUMN: Subject List with Assignments -->
                <div>
                    <div class="card">
                        <h3>
                            <i class="fas fa-list"></i> 
                            Subjects & Teacher Assignments
                            <span style="font-size:12px; background:#e2e8f0; padding:2px 10px; border-radius:20px; margin-left: 8px;">
                                <?= count($assignments_by_subject) ?> Subjects
                            </span>
                        </h3>

                        <?php if(empty($assignments_by_subject)): ?>
                            <div class="empty-state">
                                <i class="fas fa-book"></i>
                                <p>No subjects have been added yet.</p>
                                <p style="font-size: 12px; margin-top: 5px;">Add a subject using the form on the left.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach($assignments_by_subject as $subject_id => $subject_data): ?>
                                <div class="subject-card" data-subject-id="<?= $subject_id ?>" data-department-id="<?= $subject_data['department_id'] ?>">
                                    <h4>
                                        <span>
                                            <?= htmlspecialchars($subject_data['subject_name']) ?>
                                            <span class="dept-badge">
                                                <i class="fas fa-building"></i> 
                                                <?= htmlspecialchars($subject_data['department_name'] ?? 'No Department') ?>
                                            </span>
                                        </span>
                                        <span style="font-size: 11px; font-weight: normal; color: #94a3b8;">
                                            <i class="fas fa-users"></i> <?= count($subject_data['assignments']) ?> classes
                                        </span>
                                    </h4>
                                    
                                    <?php if(empty($subject_data['assignments'])): ?>
                                        <p class="unassigned">No teachers assigned to this subject yet.</p>
                                    <?php else: ?>
                                        <?php foreach($subject_data['assignments'] as $assignment): ?>
                                            <div class="class-assignment">
                                                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                                    <span class="class-badge">
                                                        <?= htmlspecialchars($assignment['class_name']) ?>
                                                        <?php if($assignment['stream']): ?>
                                                            <span class="stream-badge"><?= htmlspecialchars($assignment['stream']) ?></span>
                                                        <?php endif; ?>
                                                    </span>
                                                    <span class="teacher-name">
                                                        <i class="fas fa-user-tie"></i> 
                                                        <?= htmlspecialchars($assignment['teacher_name']) ?>
                                                    </span>
                                                    <?php if($assignment['teacher_phone']): ?>
                                                        <span class="teacher-phone">
                                                            <i class="fas fa-phone"></i> 
                                                            <?= htmlspecialchars($assignment['teacher_phone']) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="action-links">
                                                    <a href="#" class="btn-edit" onclick="openEditAssignmentModal(
                                                        <?= $assignment['teacher_subject_id'] ?>,
                                                        <?= $assignment['teacher_id'] ?>,
                                                        <?= $assignment['class_id'] ?>,
                                                        <?= $subject_id ?>,
                                                        <?= $subject_data['department_id'] ?>
                                                    )">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <a href="?remove_assignment=<?= $assignment['teacher_subject_id'] ?>" 
                                                       class="btn-remove"
                                                       onclick="return confirm('Remove this teacher from <?= htmlspecialchars($subject_data['subject_name']) ?> in <?= htmlspecialchars($assignment['class_name']) ?>?')">
                                                        <i class="fas fa-times"></i> Remove
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    
                                    <div style="margin-top: 10px; display: flex; gap: 5px; flex-wrap: wrap;">
                                        <a href="#" class="btn-edit btn-sm" onclick="openEditSubjectModal(<?= $subject_id ?>, '<?= htmlspecialchars($subject_data['subject_name']) ?>', <?= $subject_data['department_id'] ?? 0 ?>)">
                                            <i class="fas fa-edit"></i> Edit Subject
                                        </a>
                                        <a href="?delete_subject=<?= $subject_id ?>" class="btn-delete btn-sm" onclick="return confirm('Delete this subject? This will remove all assignments.')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- EDIT SUBJECT MODAL -->
<div id="editSubjectModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Edit Subject</h3>
            <button class="close" onclick="closeModal('editSubjectModal')">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="subject_id" id="edit_subject_id">
            <div class="input-group">
                <label>Subject Name <span class="required">*</span></label>
                <input type="text" name="subject_name" id="edit_subject_name" required>
            </div>
            <div class="input-group">
                <label>Department <span class="required">*</span></label>
                <select name="department_id" id="edit_department_id" required>
                    <?php 
                    mysqli_data_seek($departments, 0);
                    while($dept = mysqli_fetch_assoc($departments)): 
                    ?>
                        <option value="<?= $dept['department_id'] ?>">
                            <?= htmlspecialchars($dept['department_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" name="update_subject">
                <i class="fas fa-save"></i> Update Subject
            </button>
        </form>
    </div>
</div>

<!-- EDIT ASSIGNMENT MODAL -->
<div id="editAssignmentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Edit Assignment</h3>
            <button class="close" onclick="closeModal('editAssignmentModal')">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="assignment_id" id="edit_assignment_id">
            <input type="hidden" name="subject_id" id="edit_assignment_subject_id">
            <input type="hidden" name="class_id" id="edit_assignment_class_id">
            <div class="input-group" style="background: #f8fafc; padding: 12px; border-radius: 10px; margin-bottom: 15px;">
                <label style="text-transform: none; font-size: 13px; color: #1e293b;">
                    <span id="edit_assignment_info"></span>
                </label>
            </div>
            <div class="input-group">
                <label>Change Teacher <span class="required">*</span></label>
                <select name="teacher_id" id="edit_assignment_teacher_id" required>
                    <option value="">Choose Teacher</option>
                    <!-- Teachers will be populated by JavaScript based on subject department -->
                </select>
            </div>
            <button type="submit" name="update_assignment" class="btn-success">
                <i class="fas fa-save"></i> Update Assignment
            </button>
        </form>
    </div>
</div>

<script>
// Store teachers data for filtering
const allTeachers = <?= json_encode($teachers_array) ?>;

// Filter teachers by department in the assign form
function filterTeachersBySubject() {
    const subjectSelect = document.getElementById('assign_subject_select');
    const teacherSelect = document.getElementById('assign_teacher_select');
    const selectedOption = subjectSelect.options[subjectSelect.selectedIndex];
    const departmentId = selectedOption?.getAttribute('data-department');
    
    // Clear current options
    teacherSelect.innerHTML = '<option value="">Choose Teacher</option>';
    
    // Filter teachers by department
    const filteredTeachers = allTeachers.filter(teacher => {
        if (!departmentId) return true; // Show all if no department selected
        return teacher.department_id == departmentId;
    });
    
    // Add filtered teachers
    filteredTeachers.forEach(teacher => {
        const option = document.createElement('option');
        option.value = teacher.teacher_id;
        option.textContent = `${teacher.full_name} ${teacher.phone_no ? `(${teacher.phone_no})` : ''} - ${teacher.department_name || 'No Dept'}`;
        teacherSelect.appendChild(option);
    });
}

// Edit Subject Modal
function openEditSubjectModal(id, name, deptId) {
    document.getElementById('edit_subject_id').value = id;
    document.getElementById('edit_subject_name').value = name;
    document.getElementById('edit_department_id').value = deptId;
    document.getElementById('editSubjectModal').style.display = 'flex';
}

// Edit Assignment Modal
function openEditAssignmentModal(assignmentId, teacherId, classId, subjectId, departmentId) {
    document.getElementById('edit_assignment_id').value = assignmentId;
    document.getElementById('edit_assignment_subject_id').value = subjectId;
    document.getElementById('edit_assignment_class_id').value = classId;
    
    // Get subject and class names from the page
    const subjectCard = event?.target?.closest('.subject-card');
    const subjectName = subjectCard?.querySelector('h4 span')?.textContent?.trim() || 'Subject';
    const className = event?.target?.closest('.class-assignment')?.querySelector('.class-badge')?.textContent?.trim() || 'Class';
    
    document.getElementById('edit_assignment_info').innerHTML = `
        <strong>${subjectName}</strong> in <strong>${className}</strong>
    `;
    
    // Filter teachers by department
    const teacherSelect = document.getElementById('edit_assignment_teacher_id');
    teacherSelect.innerHTML = '<option value="">Choose Teacher</option>';
    
    // Filter teachers by the subject's department
    const filteredTeachers = allTeachers.filter(teacher => {
        return teacher.department_id == departmentId;
    });
    
    // Add filtered teachers
    filteredTeachers.forEach(teacher => {
        const option = document.createElement('option');
        option.value = teacher.teacher_id;
        option.textContent = `${teacher.full_name} ${teacher.phone_no ? `(${teacher.phone_no})` : ''}`;
        if (teacher.teacher_id == teacherId) {
            option.selected = true;
        }
        teacherSelect.appendChild(option);
    });
    
    document.getElementById('editAssignmentModal').style.display = 'flex';
}

// Close Modal
function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Close modals on outside click
window.addEventListener('click', function(e) {
    const modals = ['editSubjectModal', 'editAssignmentModal'];
    modals.forEach(id => {
        const modal = document.getElementById(id);
        if (e.target === modal) closeModal(id);
    });
});

// Close modals on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modals = ['editSubjectModal', 'editAssignmentModal'];
        modals.forEach(id => {
            if (document.getElementById(id).style.display === 'flex') {
                closeModal(id);
            }
        });
    }
});

// Auto-hide alerts
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});

// Mobile sidebar toggle
document.querySelector('.mobile-toggle')?.addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('active');
});
</script>

</body>
</html>