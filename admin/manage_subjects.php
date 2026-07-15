<?php
session_start();
include '../db.php';

/* ================= CHECK ADMIN ================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

/* ================= SAVE SUBJECTS ================= */
if (isset($_POST['save_subjects'])) {
    $department_id = mysqli_real_escape_string($conn, $_POST['department_id']);
    $subjects = $_POST['subjects'] ?? [];
    $teachers = $_POST['teachers'] ?? [];
    $saved_count = 0;
    $error_count = 0;

    for ($i = 0; $i < count($subjects); $i++) {
        $subject_name = mysqli_real_escape_string($conn, trim($subjects[$i]));
        $teacher_id = isset($teachers[$i]) ? mysqli_real_escape_string($conn, $teachers[$i]) : '';

        if (!empty($subject_name)) {
            $check_subject = mysqli_query($conn, 
                "SELECT subject_id FROM subject 
                 WHERE subject_name = '$subject_name' 
                 AND department_id = '$department_id'"
            );

            if (mysqli_num_rows($check_subject) == 0) {
                $teacher_id_val = !empty($teacher_id) ? "'$teacher_id'" : "NULL";
                
                $insert_subject = mysqli_query($conn,
                    "INSERT INTO subject (subject_name, department_id, teacher_id) 
                     VALUES ('$subject_name', '$department_id', $teacher_id_val)"
                );

                if ($insert_subject) {
                    $saved_count++;
                } else {
                    $error_count++;
                }
            } else {
                $error_count++;
            }
        }
    }

    if ($saved_count > 0) {
        $_SESSION['success_msg'] = "$saved_count subject(s) saved successfully!";
    }
    if ($error_count > 0) {
        $_SESSION['error_msg'] = "$error_count subject(s) failed or already exist!";
    }

    header("Location: manage_subjects.php");
    exit();
}

/* ================= UPDATE SUBJECT ================= */
if (isset($_POST['update_subject'])) {
    $subject_id = (int)$_POST['subject_id'];
    $subject_name = mysqli_real_escape_string($conn, $_POST['subject_name']);
    $department_id = mysqli_real_escape_string($conn, $_POST['department_id']);
    $teacher_id = !empty($_POST['teacher_id']) ? mysqli_real_escape_string($conn, $_POST['teacher_id']) : '';

    $old_subject = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT subject_name FROM subject WHERE subject_id='$subject_id'"
    ));

    $teacher_id_val = !empty($teacher_id) ? "'$teacher_id'" : "NULL";
    
    $update_subject = mysqli_query($conn,
        "UPDATE subject 
         SET subject_name='$subject_name',
         department_id='$department_id',
         teacher_id=$teacher_id_val
         WHERE subject_id='$subject_id'"
    );

    if ($update_subject) {
        $_SESSION['success_msg'] = "Subject '" . htmlspecialchars($old_subject['subject_name']) . "' updated successfully!";
    } else {
        $_SESSION['error_msg'] = "Failed to update subject!";
    }

    header("Location: manage_subjects.php");
    exit();
}

/* ================= DELETE SUBJECT ================= */
if (isset($_GET['delete'])) {
    $subject_id = (int)$_GET['delete'];
    
    $subject_info = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT subject_name FROM subject WHERE subject_id='$subject_id'"
    ));
    
    if ($subject_info) {
        $delete = mysqli_query($conn, "DELETE FROM subject WHERE subject_id='$subject_id'");
        
        if ($delete) {
            $_SESSION['success_msg'] = "Subject '" . htmlspecialchars($subject_info['subject_name']) . "' deleted successfully!";
        } else {
            $_SESSION['error_msg'] = "Failed to delete subject!";
        }
    } else {
        $_SESSION['error_msg'] = "Subject not found!";
    }
    
    header("Location: manage_subjects.php");
    exit();
}

/* ================= SEARCH ================= */
$search = isset($_GET['search'])
    ? mysqli_real_escape_string($conn, $_GET['search'])
    : '';

/* ================= FETCH DEPARTMENTS ================= */
$departments = mysqli_query($conn,
    "SELECT * FROM department ORDER BY department_name ASC"
);

$departments_array = [];
while ($dept = mysqli_fetch_assoc($departments)) {
    $departments_array[] = $dept;
}

/* ================= FETCH TEACHERS ================= */
$teachers_query = mysqli_query($conn, "
    SELECT 
        t.teacher_id,
        t.phone_no,
        t.department_id,
        t.status,
        u.full_name,
        d.department_name
    FROM teacher t
    INNER JOIN users u ON t.user_id = u.user_id
    LEFT JOIN department d ON t.department_id = d.department_id
    ORDER BY u.full_name ASC
");

$teachers_array = [];
while ($teacher = mysqli_fetch_assoc($teachers_query)) {
    $teachers_array[] = $teacher;
}

/* ================= FETCH SUBJECTS ================= */
$subjects_query = "
    SELECT 
        s.subject_id,
        s.subject_name,
        s.department_id,
        s.teacher_id,
        d.department_name,
        u.full_name AS teacher_name,
        t.phone_no AS teacher_phone,
        t.status AS teacher_status
    FROM subject s
    LEFT JOIN department d ON s.department_id = d.department_id
    LEFT JOIN teacher t ON s.teacher_id = t.teacher_id
    LEFT JOIN users u ON t.user_id = u.user_id
";

if (!empty($search)) {
    $subjects_query .= "
        WHERE s.subject_name LIKE '%$search%'
        OR d.department_name LIKE '%$search%'
        OR u.full_name LIKE '%$search%'
    ";
}

$subjects_query .= " ORDER BY s.subject_id DESC";

$subjects_list = mysqli_query($conn, $subjects_query);
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

html, body {
    height: 100%;
}

body {
    background: #f0f2f5;
    font-family: 'Segoe UI', Roboto, Arial, sans-serif;
    display: flex;
    flex-direction: column;
}

.main-wrapper {
    display: flex;
    flex: 1;
    min-height: 100vh;
}

.content-wrapper {
    flex: 1;
    display: flex;
    flex-direction: column;
    margin-left: 270px;
    margin-top: 85px;
    min-height: calc(100vh - 85px);
}

.container {
    flex: 1;
    padding: 20px 30px;
    padding-bottom: 30px;
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
    margin-bottom: 18px;
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
    from {
        opacity: 0;
        transform: translateY(-15px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
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
}

.card:last-child {
    margin-bottom: 0;
}

.input-group {
    display: flex;
    flex-direction: column;
    margin-bottom: 15px;
}

.input-group label {
    font-weight: 600;
    font-size: 11px;
    margin-bottom: 6px;
    color: #475569;
    text-transform: uppercase;
}

input, select {
    padding: 10px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    font-size: 13px;
    transition: 0.2s;
    width: 100%;
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

.btn-search {
    background: #64748b;
}

.btn-search:hover {
    background: #475569;
}

.btn-clear {
    background: #e2e8f0;
    color: #1e293b;
    text-decoration: none;
    padding: 10px 22px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    font-size: 13px;
}

.btn-clear:hover {
    background: #cbd5e1;
    color: #1e293b;
    transform: none;
}

.search-section {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 12px;
    margin-bottom: 20px;
}

.search-bar {
    flex: 3;
    min-width: 280px;
}

.subject-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 15px;
    border-radius: 12px;
    margin-bottom: 15px;
}

.subject-box label {
    font-weight: 600;
    font-size: 12px;
    margin-bottom: 5px;
    display: block;
    color: #334155;
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
    padding: 14px;
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
}

tr:hover td {
    background: #f8fafc;
}

.sn-column {
    width: 60px;
}

.sn-badge {
    background: #e2e8f0;
    color: #1a2332;
    width: 30px;
    height: 30px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-weight: bold;
    font-size: 13px;
}

.edit-btn {
    background: white;
    color: #2563eb;
    padding: 5px 14px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin-right: 5px;
    border: 1px solid #bfdbfe;
    cursor: pointer;
}

.edit-btn:hover {
    background: #eff6ff;
    transform: none;
}

.delete-btn {
    background: rgba(239, 68, 68, 0.1);
    color: #dc2626;
    padding: 5px 14px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    text-decoration: none;
    border: none;
    cursor: pointer;
}

.delete-btn:hover {
    background: #dc2626;
    color: white;
    transform: none;
}

.empty-row td {
    text-align: center;
    padding: 40px !important;
    color: #94a3b8;
}

.empty-row i {
    font-size: 40px;
    margin-bottom: 12px;
    display: block;
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
    max-width: 500px;
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
}

.close:hover {
    color: #000;
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

/* Footer Styles */
.site-footer {
    margin-left: 270px;
    background: white;
    border-top: 1px solid #e2e8f0;
    padding: 15px 30px;
    width: calc(100% - 270px);
}

@media (max-width: 768px) {
    .content-wrapper {
        margin-left: 0;
        margin-top: 80px;
        min-height: calc(100vh - 80px);
    }
    
    .container {
        padding: 15px;
    }
    
    .mobile-toggle {
        display: block;
    }
    
    .site-footer {
        margin-left: 0;
        width: 100%;
        padding: 12px 15px;
    }
    
    .search-section {
        flex-direction: column;
    }
    
    .search-bar {
        min-width: unset;
        width: 100%;
    }
    
    .search-section button,
    .search-section .btn-clear {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .container {
        padding: 10px;
    }
    
    .card {
        padding: 15px;
    }
    
    table {
        font-size: 12px;
        min-width: 400px;
    }
    
    th, td {
        padding: 8px 10px;
    }
    
    .sn-column {
        width: 40px;
    }
    
    .edit-btn, .delete-btn {
        padding: 4px 10px;
        font-size: 10px;
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
            <h2><i class="fas fa-book-open"></i> Manage Subjects</h2>

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

            <!-- ADD SUBJECTS CARD -->
            <div class="card">
                <h3><i class="fas fa-plus-circle"></i> Add New Subjects</h3>

                <form method="POST">
                    <div class="input-group">
                        <label><i class="fas fa-building"></i> Select Department</label>
                        <select name="department_id" id="department_id" required>
                            <option value="">Choose Department</option>
                            <?php foreach($departments_array as $dept): ?>
                                <option value="<?= $dept['department_id'] ?>"><?= htmlspecialchars($dept['department_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="input-group">
                        <label><i class="fas fa-hashtag"></i> Number of Subjects</label>
                        <input type="number" id="count" min="1" max="20" placeholder="Example: 5">
                    </div>

                    <div id="subjectContainer"></div>
                    <button type="submit" name="save_subjects">
                        <i class="fas fa-save"></i> Save Subjects
                    </button>
                </form>
            </div>

            <!-- SUBJECTS LIST CARD -->
            <div class="card">
                <h3>
                    <i class="fas fa-list"></i> 
                    Existing Subjects
                    <span style="font-size:12px; background:#e2e8f0; padding:2px 10px; border-radius:20px; margin-left: 8px;">
                        <?= mysqli_num_rows($subjects_list) ?>
                    </span>
                </h3>
                
                <!-- Search Bar - Only filters existing subjects -->
                <div class="search-section">
                    <form method="GET" action="" style="display: flex; gap: 10px; flex-wrap: wrap; width: 100%;">
                        <div class="search-bar">
                            <input type="text" 
                                   name="search" 
                                   placeholder="Search subjects, departments, or teachers..." 
                                   value="<?= htmlspecialchars($search) ?>"
                                   style="width: 100%;">
                        </div>
                        <button type="submit" class="btn-search">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <?php if (!empty($search)): ?>
                            <a href="manage_subjects.php" class="btn-clear">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th class="sn-column">No</th>
                                <th>Subject</th>
                                <th>Department</th>
                                <th>Teacher</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if(isset($subjects_list) && mysqli_num_rows($subjects_list) > 0): ?>
                                <?php $counter = 1; while($subject = mysqli_fetch_assoc($subjects_list)): ?>
                                    <tr>
                                        <td class="sn-column"><span class="sn-badge"><?= $counter++ ?></span></td>
                                        <td><strong><?= htmlspecialchars($subject['subject_name']) ?></strong></td>
                                        <td><?= htmlspecialchars($subject['department_name'] ?? 'Not Assigned') ?></td>
                                        <td>
                                            <?php if(!empty($subject['teacher_name'])): ?>
                                                <div>
                                                    <?= htmlspecialchars($subject['teacher_name']) ?>
                                                    <?php if(!empty($subject['teacher_phone'])): ?>
                                                        <br><small style="font-size:10px; color:#94a3b8;"><?= htmlspecialchars($subject['teacher_phone']) ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <span style="color:#94a3b8;">Not Assigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if(!empty($subject['teacher_status'])): ?>
                                                <span class="status-badge <?= $subject['teacher_status'] == 'active' ? 'status-active' : 'status-inactive' ?>">
                                                    <?= ucfirst($subject['teacher_status']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span style="color:#94a3b8;">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="edit-btn" 
                                                    onclick="openEditModal(
                                                        <?= $subject['subject_id'] ?>, 
                                                        '<?= addslashes($subject['subject_name']) ?>', 
                                                        <?= $subject['department_id'] ?>, 
                                                        <?= $subject['teacher_id'] ?? 'null' ?>
                                                    )">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <a class="delete-btn" 
                                               href="?delete=<?= $subject['subject_id'] ?>" 
                                               onclick="return confirm('Are you sure you want to delete this subject?')">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr class="empty-row">
                                    <td colspan="6">
                                        <i class="fas fa-folder-open"></i>
                                        <?= !empty($search) ? 'No subjects found matching your search.' : 'No subjects added yet.' ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Footer inside content wrapper -->
        <div class="site-footer">
            <?php include '../footer.php'; ?>
        </div>
    </div>
</div>

<!-- EDIT MODAL -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Edit Subject</h3>
            <span class="close" onclick="closeEditModal()">&times;</span>
        </div>
        <form method="POST">
            <input type="hidden" name="subject_id" id="edit_subject_id">
            <div class="input-group">
                <label>Subject Name</label>
                <input type="text" name="subject_name" id="edit_subject_name" required>
            </div>
            <div class="input-group">
                <label>Department</label>
                <select name="department_id" id="edit_department_id" required>
                    <option value="">Select Department</option>
                    <?php foreach($departments_array as $dept): ?>
                        <option value="<?= $dept['department_id'] ?>"><?= htmlspecialchars($dept['department_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="input-group">
                <label>Assign Teacher (Optional)</label>
                <select name="teacher_id" id="edit_teacher_id">
                    <option value="">Select Teacher</option>
                    <?php foreach($teachers_array as $teacher): ?>
                        <option value="<?= $teacher['teacher_id'] ?>">
                            <?= htmlspecialchars($teacher['full_name']) ?>
                            <?php if(!empty($teacher['phone_no'])): ?>
                                (<?= htmlspecialchars($teacher['phone_no']) ?>)
                            <?php endif; ?>
                            <?php if(!empty($teacher['department_name'])): ?>
                                - <?= htmlspecialchars($teacher['department_name']) ?>
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" name="update_subject">
                <i class="fas fa-save"></i> Update Subject
            </button>
        </form>
    </div>
</div>

<script>
// Generate subject input fields
const teachersData = <?= json_encode($teachers_array) ?>;

function generateSubjects() {
    const count = document.getElementById('count').value;
    const container = document.getElementById('subjectContainer');
    container.innerHTML = '';
    
    if (!count || count < 1) return;
    
    for (let i = 0; i < count; i++) {
        let teacherOptions = '<option value="">Select Teacher</option>';
        teachersData.forEach(teacher => {
            let display = teacher.full_name;
            if (teacher.phone_no) display += ` (${teacher.phone_no})`;
            if (teacher.department_name) display += ` - ${teacher.department_name}`;
            teacherOptions += `<option value="${teacher.teacher_id}">${display}</option>`;
        });
        
        container.innerHTML += `
            <div class="subject-box">
                <label>Subject ${i + 1}</label>
                <input type="text" name="subjects[]" placeholder="Enter subject name" required>
                <label style="margin-top:8px;">Assign Teacher (Optional)</label>
                <select name="teachers[]">${teacherOptions}</select>
            </div>
        `;
    }
}

// Auto-generate subjects on number input
document.getElementById('count').addEventListener('input', generateSubjects);

// Edit modal functions
function openEditModal(id, name, deptId, teacherId) {
    document.getElementById('edit_subject_id').value = id;
    document.getElementById('edit_subject_name').value = name;
    document.getElementById('edit_department_id').value = deptId;
    document.getElementById('edit_teacher_id').value = teacherId || '';
    document.getElementById('editModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Close modal on outside click
window.addEventListener('click', function(e) {
    const modal = document.getElementById('editModal');
    if (e.target === modal) closeEditModal();
});

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeEditModal();
});

// Auto-hide alerts after 5 seconds
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

// Close sidebar on outside click (mobile)
document.addEventListener('click', function(e) {
    if (window.innerWidth <= 768) {
        const sidebar = document.getElementById('sidebar');
        const toggle = document.querySelector('.mobile-toggle');
        if (sidebar?.classList.contains('active') && 
            !sidebar.contains(e.target) && 
            !toggle?.contains(e.target)) {
            sidebar.classList.remove('active');
        }
    }
});
</script>

</body>
</html>