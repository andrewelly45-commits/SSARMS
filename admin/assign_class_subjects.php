<?php
session_start();
include '../db.php';

// ============================================
// 1. AUTHENTICATION CHECK
// ============================================
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ============================================
// 2. PROCESS FORM SUBMISSIONS
// ============================================

// 2.1 ASSIGN SUBJECTS
if (isset($_POST['assign_subjects'])) {
    $class_id = (int)$_POST['class_id'];
    
    if (isset($_POST['subjects']) && !empty($_POST['subjects'])) {
        $saved = 0;
        
        foreach ($_POST['subjects'] as $subject_id) {
            $subject_id = (int)$subject_id;
            
            // Check if already assigned
            $check = mysqli_query($conn, "
                SELECT id 
                FROM class_subject 
                WHERE class_id = '$class_id' 
                AND subject_id = '$subject_id'
            ");
            
            if (mysqli_num_rows($check) == 0) {
                mysqli_query($conn, "
                    INSERT INTO class_subject (class_id, subject_id, status) 
                    VALUES ('$class_id', '$subject_id', 'active')
                ");
                $saved++;
            }
        }
        
        $_SESSION['success_msg'] = "$saved subject(s) assigned successfully.";
    } else {
        $_SESSION['error_msg'] = "Please select at least one subject.";
    }
    
    header("Location: assign_class_subjects.php?class_id=" . $class_id);
    exit();
}

// 2.2 REMOVE SUBJECT
if (isset($_GET['remove'])) {
    $id = (int)$_GET['remove'];
    $class_id = (int)$_GET['class_id'];
    
    mysqli_query($conn, "DELETE FROM class_subject WHERE id = '$id'");
    $_SESSION['success_msg'] = "Subject removed successfully.";
    
    header("Location: assign_class_subjects.php?class_id=" . $class_id);
    exit();
}

// ============================================
// 3. FETCH DATA
// ============================================

// 3.1 FETCH ALL CLASSES
$classes = mysqli_query($conn, "
    SELECT class_id, class_name 
    FROM class 
    ORDER BY class_name
");

// 3.2 GET SELECTED CLASS
$class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
$class_info = null;
$subjects = null;
$assigned_subjects = null;

if ($class_id > 0) {
    // Get class info
    $class_info = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT class_name 
        FROM class 
        WHERE class_id = '$class_id'
    "));
    
    // Get all subjects
    $subjects = mysqli_query($conn, "
        SELECT * 
        FROM subject 
        ORDER BY subject_name
    ");
    
    // Get assigned subjects
    $assigned_subjects = mysqli_query($conn, "
        SELECT 
            cs.id,
            s.subject_name,
            s.subject_id,
            d.department_name
        FROM class_subject cs
        INNER JOIN subject s ON cs.subject_id = s.subject_id
        LEFT JOIN department d ON s.department_id = d.department_id
        WHERE cs.class_id = '$class_id'
        ORDER BY s.subject_name
    ");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Subjects to Class</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #f3f4f6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .container {
            margin-left: 270px;
            margin-top: 85px;
            padding: 25px 30px;
        }
        
        .card {
            background: #fff;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        }
        
        .card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f5f9;
        }
        
        .card-header h2 {
            font-size: 20px;
            color: #1e293b;
            margin: 0;
        }
        
        .card-header i {
            color: #2563eb;
            font-size: 22px;
        }
        
        /* Alert Messages */
        .alert {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
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
        
        .alert-success {
            background: #dcfce7;
            color: #166534;
            border-left: 4px solid #22c55e;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        
        /* Form Elements */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #334155;
        }
        
        .form-group label i {
            margin-right: 8px;
            color: #64748b;
        }
        
        select,
        input[type="text"] {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #d1d5db;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.2s;
        }
        
        select:focus,
        input[type="text"]:focus {
            border-color: #2563eb;
            outline: none;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        /* Class Info */
        .class-info {
            background: #eff6ff;
            border-left: 5px solid #2563eb;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        
        .class-info b {
            color: #1e293b;
        }
        
        /* Subject List */
        .subject-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .subject-controls .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            color: #334155;
            cursor: pointer;
        }
        
        .subject-controls .search-box {
            flex: 1;
            min-width: 200px;
            max-width: 350px;
        }
        
        .subject-controls .search-box input {
            padding: 8px 14px;
        }
        
        .subject-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 10px;
            margin-top: 15px;
            max-height: 400px;
            overflow-y: auto;
            padding: 5px;
        }
        
        .subject-item {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 12px 15px;
            background: #fafafa;
            transition: all 0.2s;
        }
        
        .subject-item:hover {
            background: #eef6ff;
            border-color: #93c5fd;
        }
        
        .subject-item label {
            display: flex;
            flex-direction: column;
            gap: 4px;
            cursor: pointer;
        }
        
        .subject-item label input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            margin-bottom: 4px;
        }
        
        .subject-item label input[type="checkbox"]:disabled {
            cursor: not-allowed;
            opacity: 0.6;
        }
        
        .subject-item .subject-name {
            font-weight: 600;
            color: #1e293b;
        }
        
        .subject-item .subject-status {
            font-size: 12px;
            color: #64748b;
        }
        
        .subject-item .subject-status.assigned {
            color: #22c55e;
        }
        
        .subject-item .subject-status.available {
            color: #3b82f6;
        }
        
        /* Buttons */
        .btn {
            padding: 10px 24px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .btn-primary {
            background: #1d4ed8;
            color: white;
        }
        
        .btn-primary:hover {
            background: #1e40af;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(29, 78, 216, 0.3);
        }
        
        .btn-danger {
            background: #dc2626;
            color: white;
        }
        
        .btn-danger:hover {
            background: #b91c1c;
        }
        
        .btn-success {
            background: #16a34a;
            color: white;
        }
        
        .btn-success:hover {
            background: #15803d;
        }
        
        .form-actions {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        
        /* Table */
        .table-wrapper {
            overflow-x: auto;
            margin-top: 15px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 500px;
        }
        
        thead {
            background: #1e293b;
            color: white;
        }
        
        th {
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
        }
        
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        tr:hover td {
            background: #f8fafc;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-badge.active {
            background: #dcfce7;
            color: #166534;
        }
        
        .status-badge.inactive {
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
            margin-bottom: 15px;
            display: block;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .container {
                margin-left: 0;
                padding: 15px;
            }
            
            .subject-controls {
                flex-direction: column;
                align-items: stretch;
            }
            
            .subject-controls .search-box {
                max-width: 100%;
            }
            
            .subject-list {
                grid-template-columns: 1fr;
                max-height: 300px;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .form-actions .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>
<?php include '../auth/topbar.php'; ?>

<div class="container">
    
    <!-- ============================================ -->
    <!-- MAIN CARD - ASSIGN SUBJECTS -->
    <!-- ============================================ -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-book-open"></i>
            <h2>Assign Subjects to Class</h2>
        </div>
        
        <!-- Display Messages -->
        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($_SESSION['success_msg']) ?>
            </div>
            <?php unset($_SESSION['success_msg']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_msg'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($_SESSION['error_msg']) ?>
            </div>
            <?php unset($_SESSION['error_msg']); ?>
        <?php endif; ?>
        
        <!-- Select Class Form -->
        <form method="GET">
            <div class="form-group">
                <label><i class="fas fa-school"></i> Select Class</label>
                <select name="class_id" onchange="this.form.submit()">
                    <option value="">-- Choose Class --</option>
                    <?php 
                    mysqli_data_seek($classes, 0);
                    while ($c = mysqli_fetch_assoc($classes)): 
                    ?>
                        <option value="<?= $c['class_id'] ?>" <?= ($class_id == $c['class_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['class_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </form>
        
        <!-- Assign Subjects Form -->
        <?php if ($class_info): ?>
            <div class="class-info">
                <b><i class="fas fa-door-open"></i> Class:</b> 
                <?= htmlspecialchars($class_info['class_name']) ?>
            </div>
            
            <form method="POST">
                <input type="hidden" name="class_id" value="<?= $class_id ?>">
                
                <div class="subject-controls">
                    <label class="checkbox-label">
                        <input type="checkbox" id="checkAll">
                        <i class="fas fa-check-double"></i> Select All Subjects
                    </label>
                    
                    <div class="search-box">
                        <input type="text" id="search" placeholder="🔍 Search subjects...">
                    </div>
                </div>
                
                <div class="subject-list" id="subjectList">
                    <?php 
                    $has_available = false;
                    mysqli_data_seek($subjects, 0);
                    while ($subject = mysqli_fetch_assoc($subjects)): 
                        $subject_id = $subject['subject_id'];
                        
                        // Check if already assigned
                        $check = mysqli_query($conn, "
                            SELECT id 
                            FROM class_subject 
                            WHERE class_id = '$class_id' 
                            AND subject_id = '$subject_id'
                        ");
                        $assigned = mysqli_num_rows($check) > 0;
                        
                        if (!$assigned) $has_available = true;
                    ?>
                        <div class="subject-item">
                            <label>
                                <input 
                                    type="checkbox" 
                                    name="subjects[]" 
                                    value="<?= $subject['subject_id'] ?>"
                                    <?= $assigned ? 'disabled' : '' ?>
                                >
                                <span class="subject-name"><?= htmlspecialchars($subject['subject_name']) ?></span>
                                <span class="subject-status <?= $assigned ? 'assigned' : 'available' ?>">
                                    <?php if ($assigned): ?>
                                        <i class="fas fa-check-circle"></i> Already Assigned
                                    <?php else: ?>
                                        <i class="fas fa-plus-circle"></i> Available
                                    <?php endif; ?>
                                </span>
                            </label>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="assign_subjects" class="btn btn-primary" <?= !$has_available ? 'disabled' : '' ?>>
                        <i class="fas fa-save"></i> Assign Selected Subjects
                    </button>
                    <?php if (!$has_available): ?>
                        <span style="color:#94a3b8;font-size:13px;margin-left:10px;">
                            <i class="fas fa-info-circle"></i> All subjects are already assigned
                        </span>
                    <?php endif; ?>
                </div>
            </form>
        <?php endif; ?>
    </div>
    
    <!-- ============================================ -->
    <!-- SECOND CARD - ASSIGNED SUBJECTS LIST -->
    <!-- ============================================ -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-list"></i>
            <h2>Assigned Subjects</h2>
        </div>
        
        <?php if ($class_id > 0): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Subject</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($assigned_subjects) > 0): ?>
                            <?php $i = 1; while ($row = mysqli_fetch_assoc($assigned_subjects)): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><strong><?= htmlspecialchars($row['subject_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['department_name'] ?? '—') ?></td>
                                    <td>
                                        <span class="status-badge active">
                                            <i class="fas fa-check-circle"></i> Assigned
                                        </span>
                                    </td>
                                    <td>
                                        <a 
                                            href="?remove=<?= $row['id'] ?>&class_id=<?= $class_id ?>"
                                            onclick="return confirm('Remove this subject from the class?')"
                                            class="btn btn-danger"
                                            style="padding:6px 14px;font-size:12px;"
                                        >
                                            <i class="fas fa-trash"></i> Remove
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <i class="fas fa-folder-open"></i>
                                        <p>No subjects assigned to this class yet.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-hand-pointer"></i>
                <p>Please select a class to view assigned subjects.</p>
            </div>
        <?php endif; ?>
    </div>
    
</div>

<!-- ============================================ -->
<!-- JAVASCRIPT -->
<!-- ============================================ -->
<script>
// ============================================
// SELECT ALL SUBJECTS
// ============================================
document.getElementById('checkAll')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll(
        ".subject-item input[type='checkbox']:not(:disabled)"
    );
    checkboxes.forEach(cb => cb.checked = this.checked);
});

// ============================================
// SEARCH SUBJECTS
// ============================================
document.getElementById('search')?.addEventListener('keyup', function() {
    const keyword = this.value.toLowerCase().trim();
    const items = document.querySelectorAll('.subject-item');
    
    items.forEach(function(item) {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(keyword) ? 'block' : 'none';
    });
});

// ============================================
// AUTO HIDE ALERTS
// ============================================
setTimeout(function() {
    document.querySelectorAll('.alert').forEach(function(alert) {
        alert.style.transition = 'opacity 0.5s';
        alert.style.opacity = '0';
        setTimeout(function() {
            alert.remove();
        }, 500);
    });
}, 4000);
</script>

<?php include '../footer.php'; ?>

</body>
</html>