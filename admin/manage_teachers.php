<?php
session_start();
include '../db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// CHECK ADMIN
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

/* =========================
   ADD TEACHER
========================= */
if (isset($_POST['add_teacher'])) {
    $name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = mysqli_real_escape_string($conn, $_POST['phone_no']);
    $department_id = (int)$_POST['department_id'];
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    $check_email = mysqli_query($conn, "SELECT user_id FROM users WHERE email = '$email'");
    if (mysqli_num_rows($check_email) > 0) {
        $_SESSION['error_msg'] = "Email already exists!";
    } else {
        $insert_user = mysqli_query($conn, "
        INSERT INTO users (full_name, email, password, role, gender)
        VALUES ('$name', '$email', '$password', '$role', '$gender')
        ");

        if ($insert_user) {
            $user_id = mysqli_insert_id($conn);
            mysqli_query($conn, "INSERT INTO teacher (user_id, phone_no, department_id, status) VALUES ('$user_id','$phone','$department_id','active')");
            $teacher_id = mysqli_insert_id($conn);

            if (!empty($_POST['class_ids'])) {
                foreach ($_POST['class_ids'] as $class_id) {
                    mysqli_query($conn, "INSERT INTO teacher_class (teacher_id, class_id) VALUES ('$teacher_id', '$class_id')");
                }
            }
            $_SESSION['success_msg'] = "Teacher added successfully!";
        } else {
            $_SESSION['error_msg'] = "Failed to add teacher!";
        }
    }
    header("Location: manage_teachers.php");
    exit();
}

/* =========================
   EDIT TEACHER
========================= */
if (isset($_POST['edit_teacher'])) {
    $teacher_id = (int)$_POST['teacher_id'];
    $name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone_no']);
    $department_id = (int)$_POST['department_id'];
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    $getUser = mysqli_query($conn, "SELECT user_id FROM teacher WHERE teacher_id='$teacher_id'");
    $user = mysqli_fetch_assoc($getUser);
    $user_id = $user['user_id'];

    $check_email = mysqli_query($conn, "SELECT user_id FROM users WHERE email = '$email' AND user_id != '$user_id'");
    if (mysqli_num_rows($check_email) > 0) {
        $_SESSION['error_msg'] = "Email already exists for another user!";
    } else {
        mysqli_query($conn, "UPDATE users SET full_name='$name', email='$email', gender='$gender', role='$role' WHERE user_id='$user_id'");
        mysqli_query($conn, "UPDATE teacher SET phone_no='$phone', department_id='$department_id' WHERE teacher_id='$teacher_id'");
        mysqli_query($conn, "DELETE FROM teacher_class WHERE teacher_id='$teacher_id'");

        if (!empty($_POST['class_ids'])) {
            foreach ($_POST['class_ids'] as $class_id) {
                mysqli_query($conn, "INSERT INTO teacher_class (teacher_id, class_id) VALUES ('$teacher_id', '$class_id')");
            }
        }
        $_SESSION['success_msg'] = $role == "academic" ? "Teacher updated and changed to Academic Teacher!" : "Teacher updated successfully!";
    }
    header("Location: manage_teachers.php");
    exit();
}

/* =========================
   SUSPEND/ACTIVATE TEACHER
========================= */
if (isset($_GET['suspend']) || isset($_GET['activate'])) {
    $action = isset($_GET['suspend']) ? 'suspend' : 'activate';
    $teacher_id = (int)$_GET[$action];
    $status = $action == 'suspend' ? 'suspended' : 'active';
    $msg = $action == 'suspend' ? 'suspended' : 'activated';
    
    $teacher = mysqli_fetch_assoc(mysqli_query($conn, "SELECT user_id, (SELECT full_name FROM users WHERE user_id = teacher.user_id) as name FROM teacher WHERE teacher_id='$teacher_id'"));
    
    if ($teacher) {
        mysqli_query($conn, "UPDATE users SET status='$status' WHERE user_id='{$teacher['user_id']}'");
        $_SESSION['success_msg'] = "Teacher '{$teacher['name']}' has been $msg!";
    } else {
        $_SESSION['error_msg'] = "Teacher not found!";
    }
    header("Location: manage_teachers.php");
    exit();
}

/* =========================
   FETCH DATA
========================= */
$search = isset($_GET['search']) ? trim(mysqli_real_escape_string($conn, $_GET['search'])) : '';
$classes = mysqli_query($conn, "SELECT * FROM class ORDER BY class_name ASC");
$departments = mysqli_query($conn, "SELECT * FROM department ORDER BY department_name ASC");

$query = "
   SELECT 
   t.teacher_id, u.full_name, u.email, u.role, u.gender, u.status,
   t.phone_no, t.department_id, d.department_name,
   GROUP_CONCAT(DISTINCT c.class_name SEPARATOR ', ') AS classes,
   GROUP_CONCAT(DISTINCT c.class_id SEPARATOR ',') AS class_ids
   FROM teacher t
   JOIN users u ON t.user_id = u.user_id
   LEFT JOIN department d ON t.department_id = d.department_id
   LEFT JOIN teacher_class tc ON t.teacher_id = tc.teacher_id
   LEFT JOIN class c ON tc.class_id = c.class_id
";

if (!empty($search)) {
    // Split search terms for better matching
    $search_terms = explode(' ', $search);
    $conditions = [];
    
    foreach ($search_terms as $term) {
        $term = mysqli_real_escape_string($conn, $term);
        if (strlen($term) > 0) {
            $conditions[] = "(u.full_name LIKE '%$term%' OR d.department_name LIKE '%$term%' OR c.class_name LIKE '%$term%' OR u.gender LIKE '%$term%' OR u.email LIKE '%$term%')";
        }
    }
    
    if (!empty($conditions)) {
        $query .= " WHERE " . implode(' AND ', $conditions);
    }
}

$query .= " GROUP BY t.teacher_id ORDER BY t.teacher_id DESC";
$teachers = mysqli_query($conn, $query);

/* =========================
   ASSIGN SUBJECT
========================= */
if(isset($_POST['assign_subject'])){
    $teacher_id = (int)$_POST['teacher_id'];
    $subject_id = (int)$_POST['subject_id'];

    $teacher_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT u.full_name FROM teacher t JOIN users u ON t.user_id = u.user_id WHERE t.teacher_id = '$teacher_id'"));
    $subject_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT subject_name FROM subject WHERE subject_id = '$subject_id'"));

    $check = mysqli_query($conn, "SELECT * FROM teacher_subject WHERE teacher_id='$teacher_id' AND subject_id='$subject_id'");

    if(mysqli_num_rows($check) == 0){
        mysqli_query($conn, "INSERT INTO teacher_subject (teacher_id, subject_id) VALUES ('$teacher_id','$subject_id')");
        $_SESSION['success_msg'] = "Subject '{$subject_info['subject_name']}' assigned to {$teacher_info['full_name']} successfully!";
    } else {
        $_SESSION['error_msg'] = "Subject '{$subject_info['subject_name']}' is already assigned to this teacher!";
    }
    header("Location: manage_teachers.php");
    exit();
}

$subjects = mysqli_query($conn, "SELECT * FROM subject ORDER BY subject_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Manage Teachers | SSARMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Reset & Base */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Roboto, Arial, sans-serif; background: #f0f2f5; color: #1e293b; }
        
        /* Layout */
        .container { margin-left: 270px; margin-top: 85px; padding: 20px 30px; min-height: 100vh; }
        .card { background: white; padding: 24px 28px; border-radius: 20px; margin-bottom: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); border: 1px solid #e2e8f0; }
        
        /* Typography */
        h2 { font-size: 24px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        h3 { font-size: 16px; font-weight: 700; margin-bottom: 18px; display: flex; align-items: center; gap: 8px; }
        
        /* Alerts */
        .alert { padding: 14px 20px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; font-weight: 500; animation: slideDown 0.3s ease; }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-15px); } to { opacity: 1; transform: translateY(0); } }
        .alert-success { background: #dcfce7; border-left: 4px solid #22c55e; color: #166534; }
        .alert-error { background: #fee2e2; border-left: 4px solid #ef4444; color: #991b1b; }
        
        /* Forms */
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 18px; }
        .input-group { display: flex; flex-direction: column; }
        .input-group label { font-weight: 600; font-size: 11px; margin-bottom: 6px; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; }
        input, select { padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 13px; transition: 0.2s; width: 100%; background: white; }
        input:focus, select:focus { border-color: #f59e0b; outline: none; box-shadow: 0 0 0 3px rgba(245,158,11,0.2); }
        
        /* Buttons */
        .btn-primary { background: #074591; color: white; border: none; font-weight: 600; padding: 10px 22px; border-radius: 10px; cursor: pointer; font-size: 13px; transition: 0.2s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-primary:hover { background: #05306a; }
        .btn-search { background: #64748b; color: white; border: none; font-weight: 600; padding: 10px 22px; border-radius: 10px; cursor: pointer; font-size: 13px; transition: 0.2s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-search:hover { background: #475569; }
        .btn-clear { background: #e2e8f0; color: #1e293b; border: none; font-weight: 600; padding: 10px 22px; border-radius: 10px; cursor: pointer; font-size: 13px; transition: 0.2s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-clear:hover { background: #cbd5e1; }
        .btn-edit { background: white; color: #2563eb; padding: 5px 14px; border-radius: 20px; font-size: 11px; font-weight: 600; border: 1px solid #bfdbfe; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; }
        .btn-edit:hover { background: #eff6ff; }
        .btn-suspend { background: #fee2e2; color: #dc2626; padding: 5px 14px; border-radius: 20px; font-size: 11px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
        .btn-suspend:hover { background: #fecaca; }
        .btn-activate { background: #dcfce7; color: #15803d; padding: 5px 14px; border-radius: 20px; font-size: 11px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
        .btn-activate:hover { background: #bbf7d0; }
        .btn-assign { background: #dbeafe; color: #1d4ed8; padding: 5px 14px; border-radius: 20px; font-size: 11px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
        .btn-assign:hover { background: #bfdbfe; }
        
        /* Checkbox Group */
        .checkbox-group { display: flex; flex-wrap: wrap; gap: 8px; max-height: 180px; overflow-y: auto; padding: 12px; border: 1px solid #e2e8f0; border-radius: 12px; background: #fafcff; }
        .checkbox-group label { display: inline-flex; align-items: center; gap: 6px; padding: 6px 10px; border-radius: 20px; background: #f1f5f9; border: 1px solid #e2e8f0; white-space: nowrap; cursor: pointer; }
        .checkbox-group label:hover { background: #e2e8f0; }
        .checkbox-group input[type="checkbox"] { width: 16px; height: 16px; margin: 0; cursor: pointer; }
        .full-width { grid-column: 1 / -1; }
        
        /* Table */
        .table-wrapper { overflow-x: auto; border-radius: 12px; width: 100%; }
        table { width: 100%; border-collapse: collapse; min-width: 800px; }
        th { text-align: left; padding: 14px; background: #1a1a2e; color: white; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
        td { padding: 12px 14px; border-bottom: 1px solid #e2e8f0; vertical-align: middle; font-size: 13px; color: #334155; }
        tr:hover td { background: #fefce8; }
        .sn-number { background: #1a1a2e; color: white; width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; font-weight: bold; font-size: 12px; }
        .empty-row td { text-align: center; padding: 40px !important; color: #94a3b8; }
        .classes-cell { max-width: 180px; white-space: normal; word-wrap: break-word; font-size: 12px; line-height: 1.5; }
        .action-buttons { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; }
        
        /* Status */
        .status-active { color: #15803d; font-weight: 600; }
        .status-suspended { color: #dc2626; font-weight: 600; }
        
        /* Search Section */
        .search-section { display: flex; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; align-items: flex-end; }
        .search-section .input-group { flex: 1; min-width: 250px; }
        
        /* Modal */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1100; align-items: center; justify-content: center; }
        .modal-content { background: white; border-radius: 24px; padding: 24px; max-width: 700px; width: 90%; max-height: 85vh; overflow-y: auto; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #e2e8f0; }
        .modal-header h3 { margin-bottom: 0; }
        .close { font-size: 28px; cursor: pointer; color: #94a3b8; line-height: 1; transition: 0.2s; }
        .close:hover { color: #000; }
        
        /* Mobile Toggle */
        .mobile-toggle { display: none; position: fixed; top: 15px; left: 15px; background: #1a1a2e; color: white; border: none; padding: 12px 15px; border-radius: 10px; cursor: pointer; z-index: 1100; }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar { left: -280px; }
            .sidebar.active { left: 0; }
            .topbar { left: 0; }
            .container { margin-left: 0; margin-top: 80px; padding: 15px; }
            .mobile-toggle { display: block; }
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<button class="mobile-toggle" onclick="document.getElementById('sidebar').classList.toggle('active')">
    <i class="fas fa-bars"></i>
</button>

<?php include 'admin_sidebar.php'; ?>
<?php include '../auth/topbar.php'; ?>

<div class="container">
    <h2><i class="fas fa-chalkboard-user" style="color: #074591;"></i> Manage Teachers</h2>
    
    <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success_msg']) ?>
        </div>
        <?php unset($_SESSION['success_msg']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_msg'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($_SESSION['error_msg']) ?>
        </div>
        <?php unset($_SESSION['error_msg']); ?>
    <?php endif; ?>

    <!-- ADD TEACHER -->
    <div class="card">
        <h3><i class="fas fa-user-plus" style="color: #074591;"></i> Add Teacher</h3>
        <form method="POST">
            <div class="form-grid">
                <div class="input-group">
                    <label><i class="fas fa-user"></i> Full Name</label>
                    <input type="text" name="full_name" placeholder="Full name" required>
                </div>
                <div class="input-group">
                    <label><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" name="email" placeholder="teacher@school.com" required>
                </div>
                <div class="input-group">
                    <label><i class="fas fa-lock"></i> Password</label>
                    <input type="password" name="password" placeholder="********" required>
                </div>
                <div class="input-group">
                    <label><i class="fas fa-phone"></i> Phone Number</label>
                    <input type="text" name="phone_no" placeholder="Phone number" required>
                </div>
                <div class="input-group">
                    <label><i class="fas fa-venus-mars"></i> Gender</label>
                    <select name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
                <div class="input-group">
                    <label><i class="fas fa-building"></i> Department</label>
                    <select name="department_id" required>
                        <option value="">Select Department</option>
                        <?php mysqli_data_seek($departments,0); while($dept=mysqli_fetch_assoc($departments)): ?>
                            <option value="<?= $dept['department_id'] ?>"><?= htmlspecialchars($dept['department_name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label><i class="fas fa-user-tag"></i> Role</label>
                    <select name="role" required>
                        <option value="teacher">Teacher</option>
                        <option value="academic">Academic Teacher</option>
                    </select>
                </div>
            </div>
            <div class="input-group full-width" style="margin-top:18px;">
                <label><i class="fas fa-school"></i> Assign Classes</label>
                <div class="checkbox-group">
                    <?php mysqli_data_seek($classes, 0); while($class = mysqli_fetch_assoc($classes)): ?>
                        <label>
                            <input type="checkbox" name="class_ids[]" value="<?= $class['class_id'] ?>">
                            <i class="fas fa-chalkboard"></i> <?= htmlspecialchars($class['class_name']) ?>
                        </label>
                    <?php endwhile; ?>
                </div>
            </div>
            <br>
            <button type="submit" name="add_teacher" class="btn-primary"><i class="fas fa-save"></i> Save Teacher</button>
        </form>
    </div>

    <!-- TEACHERS LIST -->
    <div class="card">
        <h3><i class="fas fa-users" style="color: #074591;"></i> Teachers List</h3>
        
        <!-- Search -->
        <div class="search-section">
            <div class="input-group">
                <label><i class="fas fa-search"></i> Search Teachers</label>
                <input type="text" id="searchInput" placeholder="Search by name, department, class, or gender" value="<?= htmlspecialchars($search) ?>">
            </div>
            <button id="searchBtn" class="btn-search"><i class="fas fa-search"></i> Search</button>
            <button id="clearBtn" class="btn-clear"><i class="fas fa-times"></i> Clear</button>
        </div>
        
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width:50px;">#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Gender</th>
                        <th>Department</th>
                        <th>Classes</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th style="width:220px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($teachers) > 0): ?>
                        <?php $counter = 1; while($row = mysqli_fetch_assoc($teachers)): ?>
                            <tr>
                                <td><span class="sn-number"><?= $counter++ ?></span></td>
                                <td><strong><?= htmlspecialchars($row['full_name']) ?></strong></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= ucfirst($row['gender'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['department_name'] ?? 'N/A') ?></td>
                                <td class="classes-cell"><?= !empty($row['classes']) ? htmlspecialchars($row['classes']) : '—' ?></td>
                                <td><?= ucfirst($row['role'] ?? 'teacher') ?></td>
                                <td>
                                    <span class="<?= $row['status'] == 'suspended' ? 'status-suspended' : 'status-active' ?>">
                                        <i class="fas <?= $row['status'] == 'suspended' ? 'fa-user-slash' : 'fa-user-check' ?>"></i>
                                        <?= ucfirst($row['status']) ?>
                                    </span>
                                </td>
                                <td class="action-buttons">
                                    <a class="btn-assign" href="assign_subject.php?teacher_id=<?= $row['teacher_id'] ?>">
                                        <i class="fas fa-book"></i> Assign
                                    </a>
                                    <button class="btn-edit" onclick="openEditModal(
                                        <?= $row['teacher_id'] ?>,
                                        '<?= addslashes($row['full_name']) ?>',
                                        '<?= addslashes($row['email']) ?>',
                                        '<?= addslashes($row['gender']) ?>',
                                        '<?= addslashes($row['phone_no']) ?>',
                                        '<?= addslashes($row['department_id']) ?>',
                                        '<?= addslashes($row['class_ids']) ?>',
                                        '<?= addslashes($row['role']) ?>'
                                    )">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <?php if($row['status'] == 'active'): ?>
                                        <a class="btn-suspend" href="?suspend=<?= $row['teacher_id'] ?>" onclick="return confirm('Suspend this teacher?')">
                                            <i class="fas fa-user-slash"></i> Suspend
                                        </a>
                                    <?php else: ?>
                                        <a class="btn-activate" href="?activate=<?= $row['teacher_id'] ?>" onclick="return confirm('Activate this teacher?')">
                                            <i class="fas fa-user-check"></i> Activate
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr class="empty-row"><td colspan="9"><i class="fas fa-folder-open"></i> No teachers found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- EDIT MODAL -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-edit" style="color: #074591;"></i> Edit Teacher</h3>
            <span class="close" onclick="closeEditModal()">&times;</span>
        </div>
        <form method="POST">
            <input type="hidden" name="teacher_id" id="edit_teacher_id">
            <div class="form-grid">
                <div class="input-group">
                    <label><i class="fas fa-user"></i> Full Name</label>
                    <input type="text" name="full_name" id="edit_full_name" required>
                </div>
                <div class="input-group">
                    <label><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" name="email" id="edit_email" required>
                </div>
                <div class="input-group">
                    <label><i class="fas fa-venus-mars"></i> Gender</label>
                    <select name="gender" id="edit_gender" required>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
                <div class="input-group">
                    <label><i class="fas fa-phone"></i> Phone Number</label>
                    <input type="text" name="phone_no" id="edit_phone_no">
                </div>
                <div class="input-group">
                    <label><i class="fas fa-building"></i> Department</label>
                    <select name="department_id" id="edit_department_id" required>
                        <?php mysqli_data_seek($departments, 0); while($dept = mysqli_fetch_assoc($departments)): ?>
                            <option value="<?= $dept['department_id'] ?>"><?= htmlspecialchars($dept['department_name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label><i class="fas fa-user-tag"></i> Role</label>
                    <select name="role" id="edit_role" required>
                        <option value="teacher">Teacher</option>
                        <option value="academic">Academic</option>
                    </select>
                </div>
            </div>
            <div class="input-group full-width" style="margin-top:18px;">
                <label><i class="fas fa-school"></i> Assign Classes</label>
                <div class="checkbox-group" id="edit_classes_container">
                    <?php mysqli_data_seek($classes, 0); while($class = mysqli_fetch_assoc($classes)): ?>
                        <label>
                            <input type="checkbox" name="class_ids[]" value="<?= $class['class_id'] ?>" class="class-checkbox">
                            <i class="fas fa-chalkboard"></i> <?= htmlspecialchars($class['class_name']) ?>
                        </label>
                    <?php endwhile; ?>
                </div>
            </div>
            <br>
            <button type="submit" name="edit_teacher" class="btn-primary"><i class="fas fa-save"></i> Update Teacher</button>
        </form>
    </div>
</div>

<script>
// Auto-hide alerts
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => { 
            alert.style.transition = 'opacity 0.3s';
            alert.style.opacity = '0'; 
            setTimeout(() => alert.remove(), 300); 
        }, 4000);
    });
}, 500);

// Edit Modal Functions
function openEditModal(id, name, email, gender, phone, departmentId, classIds, role) {
    document.getElementById('edit_teacher_id').value = id || '';
    document.getElementById('edit_full_name').value = name || '';
    document.getElementById('edit_email').value = email || '';
    document.getElementById('edit_gender').value = gender || 'male';
    document.getElementById('edit_phone_no').value = phone || '';
    document.getElementById('edit_department_id').value = departmentId || '';
    document.getElementById('edit_role').value = role || 'teacher';
    
    // Reset and check classes
    document.querySelectorAll('#edit_classes_container .class-checkbox').forEach(cb => cb.checked = false);
    if(classIds && classIds.trim() !== '') {
        let selectedIds = classIds.toString().split(',');
        document.querySelectorAll('#edit_classes_container .class-checkbox').forEach(cb => {
            if(selectedIds.includes(cb.value)) cb.checked = true;
        });
    }
    document.getElementById('editModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Close modal on outside click
window.onclick = function(event) {
    if (event.target == document.getElementById('editModal')) closeEditModal();
}

// Search Functions
document.getElementById('searchInput')?.addEventListener('keypress', function(e) {
    if(e.key === 'Enter') searchTeachers();
});

document.getElementById('searchBtn')?.addEventListener('click', searchTeachers);
document.getElementById('clearBtn')?.addEventListener('click', clearSearch);

function searchTeachers() {
    let term = document.getElementById('searchInput').value.trim();
    let url = new URL(window.location.href);
    if (term) {
        url.searchParams.set('search', term);
    } else {
        url.searchParams.delete('search');
    }
    window.location.href = url.toString();
}

function clearSearch() {
    let url = new URL(window.location.href);
    url.searchParams.delete('search');
    window.location.href = url.toString();
}

// Mobile sidebar toggle
document.querySelector('.mobile-toggle')?.addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('active');
});

document.addEventListener('click', function(e) {
    if (window.innerWidth <= 768) {
        const sidebar = document.getElementById('sidebar');
        const toggle = document.querySelector('.mobile-toggle');
        if (sidebar?.classList.contains('active') && !sidebar.contains(e.target) && !toggle?.contains(e.target)) {
            sidebar.classList.remove('active');
        }
    }
});
</script>

<?php include '../footer.php'; ?>
</body>
</html>