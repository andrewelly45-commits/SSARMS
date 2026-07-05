<?php
session_start();
include '../db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get teacher ID from URL
$teacher_id = isset($_GET['teacher_id']) ? (int)$_GET['teacher_id'] : 0;
if (!$teacher_id) {
    header("Location: manage_teachers.php");
    exit();
}

// Process assignment
if (isset($_POST['assign'])) {
    $class_id = (int)$_POST['class_id'];
    $subject_id = (int)$_POST['subject_id'];
    
    // Check if subject is already assigned to this teacher for this class
    $check = mysqli_query($conn, "SELECT * FROM teacher_subject WHERE teacher_id='$teacher_id' AND class_id='$class_id' AND subject_id='$subject_id'");
    
    if (mysqli_num_rows($check) > 0) {
        $_SESSION['msg'] = "⚠️ Subject already assigned to this teacher for this class!";
        $_SESSION['msg_type'] = 'warning';
    } else {
        // Check if subject is assigned to another teacher for this class
        $other_teacher_check = mysqli_query($conn, "
            SELECT ts.*, u.full_name as teacher_name 
            FROM teacher_subject ts
            JOIN teacher t ON ts.teacher_id = t.teacher_id
            JOIN users u ON t.user_id = u.user_id
            WHERE ts.class_id='$class_id' 
            AND ts.subject_id='$subject_id'
            AND ts.teacher_id != '$teacher_id'
        ");
        
        if (mysqli_num_rows($other_teacher_check) > 0) {
            $other_teacher = mysqli_fetch_assoc($other_teacher_check);
            $_SESSION['msg'] = "❌ This subject is already assigned to <strong>" . htmlspecialchars($other_teacher['teacher_name']) . "</strong> for this class!";
            $_SESSION['msg_type'] = 'error';
        } else {
            // Insert the assignment
            mysqli_query($conn, "INSERT INTO teacher_subject (teacher_id, subject_id, class_id) VALUES ('$teacher_id', '$subject_id', '$class_id')");
            $_SESSION['msg'] = "✅ Subject assigned successfully!";
            $_SESSION['msg_type'] = 'success';
        }
    }
    header("Location: assign_subject.php?teacher_id=$teacher_id");
    exit();
}

// Process remove
if (isset($_GET['remove'])) {
    $remove_id = (int)$_GET['remove'];
    mysqli_query($conn, "DELETE FROM teacher_subject WHERE teacher_subject_id='$remove_id'");
    $_SESSION['msg'] = "🗑️ Subject removed successfully!";
    $_SESSION['msg_type'] = 'success';
    header("Location: assign_subject.php?teacher_id=$teacher_id");
    exit();
}

// Get teacher info
$teacher_result = mysqli_query($conn, "
    SELECT u.full_name, u.email, u.status, t.phone_no, t.department_id, d.department_name
    FROM teacher t 
    JOIN users u ON t.user_id = u.user_id 
    LEFT JOIN department d ON t.department_id = d.department_id
    WHERE t.teacher_id = '$teacher_id'
");
$teacher = mysqli_fetch_assoc($teacher_result);

// Get assigned classes
$classes = mysqli_query($conn, "
    SELECT c.class_id, c.class_name 
    FROM class c 
    JOIN teacher_class tc ON c.class_id = tc.class_id 
    WHERE tc.teacher_id = '$teacher_id'
");

// Get current assignments
$assigned = mysqli_query($conn, "
    SELECT ts.teacher_subject_id, s.subject_name, c.class_name 
    FROM teacher_subject ts
    JOIN subject s ON ts.subject_id = s.subject_id
    JOIN class c ON ts.class_id = c.class_id
    WHERE ts.teacher_id = '$teacher_id'
");

// Get subjects for selected class (if any)
$selected_class = isset($_POST['class_id']) ? (int)$_POST['class_id'] : 0;
$subjects = null;
if ($selected_class > 0) {
    $department_id = (int)$teacher['department_id'];

    $subjects = mysqli_query($conn, "
        SELECT
            s.subject_id,
            s.subject_name,
            CASE 
                WHEN EXISTS (
                    SELECT 1 FROM teacher_subject ts2 
                    WHERE ts2.subject_id = s.subject_id 
                    AND ts2.class_id = '$selected_class'
                    AND ts2.teacher_id != '$teacher_id'
                ) THEN (
                    SELECT u.full_name 
                    FROM teacher_subject ts3
                    JOIN teacher t ON ts3.teacher_id = t.teacher_id
                    JOIN users u ON t.user_id = u.user_id
                    WHERE ts3.subject_id = s.subject_id 
                    AND ts3.class_id = '$selected_class'
                    AND ts3.teacher_id != '$teacher_id'
                    LIMIT 1
                )
                ELSE NULL
            END as assigned_to_other
        FROM subject s
        JOIN class_subject cs ON s.subject_id = cs.subject_id
        WHERE
            cs.class_id = '$selected_class'
            AND s.department_id = '$department_id'
            AND s.subject_id NOT IN (
                SELECT subject_id
                FROM teacher_subject
                WHERE teacher_id = '$teacher_id'
                AND class_id = '$selected_class'
            )
        ORDER BY s.subject_name
    ");
}

$msg = isset($_SESSION['msg']) ? $_SESSION['msg'] : '';
$msg_type = isset($_SESSION['msg_type']) ? $_SESSION['msg_type'] : 'success';
unset($_SESSION['msg']);
unset($_SESSION['msg_type']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Subject</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }

        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            background: #f0f2f5; 
        }
        
        .main { 
            margin-left: 270px; 
            margin-top: 85px; 
            padding: 20px 30px; 
        }
        
        .card { 
            background: white; 
            border-radius: 16px; 
            margin-bottom: 20px; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.1); 
        }
        
        .card-header { 
            padding: 18px 24px; 
            border-bottom: 1px solid #eef2f6; 
        }
        
        .card-header h2 { 
            font-size: 20px; 
            color: #1e293b; 
            display: flex; 
            align-items: center; 
            gap: 10px; 
        }
        
        .card-body { 
            padding: 24px; 
        }
        
        .info-grid { 
            display: flex; 
            flex-wrap: wrap; 
            gap: 15px; 
            margin-bottom: 25px; 
        }
        
        .info-box { 
            background: #f8fafc; 
            padding: 12px 18px; 
            border-radius: 12px; 
            border-left: 3px solid #074591; 
            flex: 1;
            min-width: 150px;
        }
        
        .info-box label { 
            font-size: 11px; 
            color: #64748b; 
            text-transform: uppercase; 
            display: block; 
        }
        
        .info-box span { 
            font-size: 15px; 
            font-weight: 600; 
            color: #0f172a; 
        }
        
        .section { 
            margin-bottom: 25px; 
        }
        
        .section h3 { 
            font-size: 16px; 
            margin-bottom: 12px; 
            color: #1e293b; 
            display: flex; 
            align-items: center; 
            gap: 8px; 
        }
        
        .badge { 
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
            padding: 6px 14px; 
            border-radius: 30px; 
            font-size: 13px; 
        }
        
        .class-badge { 
            background: #e0f2fe; 
            color: #0369a1; 
        }
        
        .subject-badge { 
            background: #f1f5f9; 
            border: 1px solid #e2e8f0; 
            margin: 5px; 
        }
        
        .remove-link { 
            color: #dc2626; 
            margin-left: 8px; 
            text-decoration: none; 
        }
        
        .remove-link:hover { 
            color: #b91c1c; 
        }
        
        .empty { 
            background: #fef9e3; 
            padding: 10px 16px; 
            border-radius: 12px; 
            color: #b45309; 
            font-size: 13px; 
            display: inline-block; 
        }
        
        .form-row { 
            display: flex; 
            gap: 15px; 
            flex-wrap: wrap; 
            align-items: flex-end; 
        }
        
        .form-group { 
            flex: 1; 
            min-width: 180px; 
        }
        
        .form-group label { 
            font-size: 12px; 
            font-weight: 600; 
            color: #334155; 
            display: block; 
            margin-bottom: 6px; 
        }
        
        select { 
            width: 100%; 
            padding: 10px 12px; 
            border: 1px solid #cbd5e1; 
            border-radius: 10px; 
            font-size: 14px; 
            background: white;
        }
        
        select:focus {
            outline: none;
            border-color: #074591;
            box-shadow: 0 0 0 3px rgba(7, 69, 145, 0.1);
        }
        
        select option.assigned-other {
            color: #dc2626;
            background-color: #fee2e2;
        }
        
        button { 
            background: #074591; 
            color: white; 
            border: none; 
            padding: 10px 24px; 
            border-radius: 30px; 
            font-weight: 600; 
            cursor: pointer; 
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
        }
        
        button:hover { 
            background: #05306a; 
        }
        
        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .alert { 
            padding: 12px 18px; 
            border-radius: 12px; 
            margin-bottom: 20px; 
            display: flex; 
            align-items: center; 
            gap: 10px; 
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
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
        
        .alert-warning { 
            background: #fef3c7; 
            color: #92400e; 
            border-left: 4px solid #f59e0b; 
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 20px;
        }
        
        .back-link:hover {
            color: #074591;
        }
        
        .assigned-info {
            font-size: 12px;
            color: #dc2626;
            margin-top: 4px;
            display: block;
        }
        
        @media (max-width: 768px) { 
            .main { 
                margin-left: 0; 
                padding: 15px; 
            }
            .info-grid {
                flex-direction: column;
            }
            .form-row {
                flex-direction: column;
            }
            .form-group {
                min-width: 100%;
            }
            button {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>
<?php include 'admin_topbar.php'; ?>

<div class="main">
    <a href="manage_teachers.php" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Teachers
    </a>
    
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-book-open"></i> Assign Subjects to Teacher</h2>
        </div>
        <div class="card-body">
            
            <?php if($msg): ?>
                <div class="alert alert-<?= $msg_type ?>">
                    <i class="fas <?= $msg_type == 'success' ? 'fa-check-circle' : ($msg_type == 'warning' ? 'fa-exclamation-triangle' : 'fa-exclamation-circle') ?>"></i> 
                    <?= htmlspecialchars_decode($msg) ?>
                </div>
            <?php endif; ?>
            
            <!-- Teacher Info -->
            <div class="info-grid">
                <div class="info-box">
                    <label><i class="fas fa-user"></i> Teacher</label>
                    <span><?= htmlspecialchars($teacher['full_name']) ?></span>
                </div>
                <div class="info-box">
                    <label><i class="fas fa-envelope"></i> Email</label>
                    <span><?= htmlspecialchars($teacher['email']) ?></span>
                </div>
                <div class="info-box">
                    <label><i class="fas fa-phone"></i> Phone</label>
                    <span><?= htmlspecialchars($teacher['phone_no'] ?: '—') ?></span>
                </div>
                <div class="info-box">
                    <label><i class="fas fa-building"></i> Department</label>
                    <span><?= htmlspecialchars($teacher['department_name'] ?: '—') ?></span>
                </div>
                <div class="info-box">
                    <label><i class="fas fa-circle"></i> Status</label>
                    <span>
                        <?php if($teacher['status'] == 'active'): ?>
                            <span style="color:#22c55e;">● Active</span>
                        <?php else: ?>
                            <span style="color:#ef4444;">● Inactive</span>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
            
            <!-- Assigned Classes -->
            <div class="section">
                <h3><i class="fas fa-chalkboard"></i> Assigned Classes</h3>
                <div>
                    <?php if(mysqli_num_rows($classes) > 0): ?>
                        <?php while($c = mysqli_fetch_assoc($classes)): ?>
                            <span class="badge class-badge"><i class="fas fa-door-open"></i> <?= htmlspecialchars($c['class_name']) ?></span>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <span class="empty"><i class="fas fa-info-circle"></i> No classes assigned</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Current Assignments -->
            <div class="section">
                <h3><i class="fas fa-tasks"></i> Currently Assigned Subjects</h3>
                <div>
                    <?php if(mysqli_num_rows($assigned) > 0): ?>
                        <?php while($a = mysqli_fetch_assoc($assigned)): ?>
                            <span class="badge subject-badge">
                                <i class="fas fa-book"></i> <?= htmlspecialchars($a['subject_name']) ?>
                                <span style="color:#64748b;">(<?= htmlspecialchars($a['class_name']) ?>)</span>
                                <a href="?remove=<?= $a['teacher_subject_id'] ?>&teacher_id=<?= $teacher_id ?>" class="remove-link" onclick="return confirm('Remove this subject assignment?')">
                                    <i class="fas fa-times-circle"></i>
                                </a>
                            </span>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <span class="empty"><i class="fas fa-info-circle"></i> No subjects assigned yet</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Assign New Subject Form -->
            <?php if(mysqli_num_rows($classes) > 0): ?>
            <div class="section">
                <h3><i class="fas fa-plus-circle"></i> Assign New Subject</h3>
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-school"></i> Select Class</label>
                            <select name="class_id" required onchange="this.form.submit()">
                                <option value="">-- Choose Class --</option>
                                <?php 
                                mysqli_data_seek($classes, 0);
                                while($c = mysqli_fetch_assoc($classes)): ?>
                                    <option value="<?= $c['class_id'] ?>" <?= ($selected_class == $c['class_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['class_name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-book"></i> Select Subject</label>
                            <select name="subject_id" required>
                                <option value="">-- Select Subject --</option>
                                <?php if($subjects && mysqli_num_rows($subjects) > 0): ?>
                                    <?php while($s = mysqli_fetch_assoc($subjects)): ?>
                                        <option value="<?= $s['subject_id'] ?>" <?= $s['assigned_to_other'] ? 'class="assigned-other"' : '' ?>>
                                            <?= htmlspecialchars($s['subject_name']) ?>
                                            <?php if($s['assigned_to_other']): ?>
                                                (Assigned to: <?= htmlspecialchars($s['assigned_to_other']) ?>)
                                            <?php endif; ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <option disabled><?= $selected_class ? 'All subjects already assigned to this class' : 'Select a class first' ?></option>
                                <?php endif; ?>
                            </select>
                            <?php if($subjects && mysqli_num_rows($subjects) > 0): ?>
                                <small style="color:#64748b;display:block;margin-top:4px;">
                                    <i class="fas fa-info-circle"></i> 
                                    Subjects marked in <span style="color:#dc2626;">red</span> are already assigned to another teacher
                                </small>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <button type="submit" name="assign" <?= (!$selected_class || !$subjects || mysqli_num_rows($subjects) == 0) ? 'disabled' : '' ?>>
                                <i class="fas fa-save"></i> Assign Subject
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <?php else: ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    This teacher has no classes assigned. Please assign a class first.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Auto-hide alerts after 5 seconds
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s ease';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
}, 500);
</script>

</body>
</html>