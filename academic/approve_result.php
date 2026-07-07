<?php
session_start();
include '../db.php';

// When approving results
include '../auth/audit_functions.php';

logSystemAction(
    $_SESSION['user_id'],
    $_SESSION['role'],
    $_SESSION['full_name'],
    'approve',
    "Approved results for Class: $class_name, Subject: $subject_name",
    'results',
    'marks',
    $class_id,
    null,
    ['class' => $class_name, 'subject' => $subject_name]
);

/* ================= AUTH CHECK ================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'academic') {
    header("Location: ../auth/login.php");
    exit();
}

/* ================= GET CLASS ================= */
$class_id = isset($_GET['class_id'])
    ? (int)$_GET['class_id']
    : '';

/* ================= APPROVE CLASS RESULTS ================= */
if (isset($_GET['approve_class']) && !empty($class_id)) {
    // Check if there are pending marks
    $check_query = mysqli_query($conn,
        "SELECT COUNT(*) as pending_count
         FROM marks 
         WHERE class_id='$class_id' 
         AND status='pending'"
    );
    $status = mysqli_fetch_assoc($check_query);
    
    if ($status['pending_count'] > 0) {
        // Approve all pending marks for this class
        $update_query = mysqli_query($conn,
            "UPDATE marks
             SET status='published'
             WHERE class_id='$class_id'
             AND status='pending'"
        );
        
        if ($update_query) {
            $affected_rows = mysqli_affected_rows($conn);
            $_SESSION['success_msg'] = "Successfully approved $affected_rows results for the entire class!";
        } else {
            $_SESSION['error_msg'] = "Error approving results: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['warning_msg'] = "No pending results found for this class.";
    }
    
    header("Location: approve_results.php?class_id=" . $class_id);
    exit();
}

/* ================= APPROVE INDIVIDUAL SUBJECT ================= */
if (isset($_GET['approve_subject']) && !empty($class_id)) {
    $subject_id = (int)$_GET['approve_subject'];
    
    // Check if there are pending marks for this subject
    $check_query = mysqli_query($conn,
        "SELECT COUNT(*) as pending_count
         FROM marks 
         WHERE class_id='$class_id' 
         AND subject_id='$subject_id'
         AND status='pending'"
    );
    $status = mysqli_fetch_assoc($check_query);
    
    if ($status['pending_count'] > 0) {
        $update_query = mysqli_query($conn,
            "UPDATE marks
             SET status='published'
             WHERE class_id='$class_id'
             AND subject_id='$subject_id'
             AND status='pending'"
        );
        
        if ($update_query) {
            $affected_rows = mysqli_affected_rows($conn);
            $_SESSION['success_msg'] = "Successfully approved $affected_rows results for this subject!";
        } else {
            $_SESSION['error_msg'] = "Error approving results: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['warning_msg'] = "No pending results found for this subject.";
    }
    
    header("Location: approve_results.php?class_id=" . $class_id);
    exit();
}

/* ================= FETCH CLASSES WITH PENDING RESULTS ================= */
$classes = mysqli_query($conn,
    "SELECT DISTINCT
        c.class_id,
        c.class_name,
        COUNT(m.mark_id) AS total_marks,
        COUNT(DISTINCT m.subject_id) AS total_subjects,
        SUM(CASE WHEN m.status = 'pending' THEN 1 ELSE 0 END) AS pending_marks
     FROM marks m
     INNER JOIN class c ON m.class_id = c.class_id
     GROUP BY c.class_id
     HAVING pending_marks > 0
     ORDER BY c.class_name ASC"
);

/* ================= FETCH SUBJECTS WITH TEACHERS FOR SELECTED CLASS ================= */
$subjects = null;
$class_name = '';

if (!empty($class_id)) {
    // Get class name
    $class_query = mysqli_query($conn, "SELECT class_name FROM class WHERE class_id='$class_id'");
    $class_data = mysqli_fetch_assoc($class_query);
    $class_name = $class_data['class_name'] ?? 'Class';
    
    // Get all subjects taught in this class with their teachers
    $subjects_query = mysqli_query($conn,
        "SELECT DISTINCT
            s.subject_id,
            s.subject_name,
            u.full_name as teacher_name,
            t.teacher_id,
            CASE 
                WHEN EXISTS (
                    SELECT 1 FROM marks m 
                    WHERE m.subject_id = s.subject_id 
                    AND m.class_id = '$class_id'
                    AND m.status = 'pending'
                ) THEN 'pending'
                WHEN EXISTS (
                    SELECT 1 FROM marks m 
                    WHERE m.subject_id = s.subject_id 
                    AND m.class_id = '$class_id'
                    AND m.status = 'published'
                ) THEN 'published'
                ELSE 'not_submitted'
            END as status,
            COUNT(DISTINCT m2.mark_id) as marks_count
        FROM subject s
        INNER JOIN teacher_subject ts ON s.subject_id = ts.subject_id
        INNER JOIN teacher t ON ts.teacher_id = t.teacher_id
        INNER JOIN users u ON t.user_id = u.user_id
        INNER JOIN teacher_class tc ON t.teacher_id = tc.teacher_id AND tc.class_id = '$class_id'
        LEFT JOIN marks m2 ON s.subject_id = m2.subject_id AND m2.class_id = '$class_id'
        WHERE tc.class_id = '$class_id'
        GROUP BY s.subject_id, s.subject_name, u.full_name, t.teacher_id
        ORDER BY s.subject_name ASC"
    );
    
    $subjects = $subjects_query;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Approve Class Results</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f1f5f9;
            font-family: 'Segoe UI', sans-serif;
        }

        .container {
            margin-left: 260px;
            padding: 30px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 18px;
            margin-bottom: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        h2 {
            margin-bottom: 25px;
            color: #1e293b;
            font-size: 28px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        h3 {
            margin-bottom: 20px;
            color: #334155;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 20px;
            border-radius: 16px;
            transition: transform 0.2s;
        }

        .box:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .box h4 {
            margin-bottom: 10px;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .box p {
            color: #64748b;
            margin-bottom: 15px;
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border-left: 4px solid #16a34a;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #dc2626;
        }

        .alert-warning {
            background: #fef3c7;
            color: #92400e;
            border-left: 4px solid #d97706;
        }

        .btn {
            padding: 10px 18px;
            border-radius: 30px;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn:hover {
            opacity: 0.9;
            transform: scale(1.02);
        }

        .view-btn {
            background: #2563eb;
        }

        .approve-btn {
            background: #16a34a;
        }

        .approve-all-btn {
            background: #16a34a;
            padding: 12px 30px;
            font-size: 16px;
        }

        .approve-all-btn:disabled {
            background: #94a3b8;
            cursor: not-allowed;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            background: #1e293b;
            color: white;
            padding: 14px;
            text-align: left;
            font-weight: 600;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid #e2e8f0;
        }

        tr:hover {
            background: #f8fafc;
        }

        .badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-published {
            background: #dcfce7;
            color: #166534;
        }

        .badge-not-submitted {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-teacher {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .action-bar {
            margin-top: 20px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .empty {
            text-align: center;
            padding: 50px;
            color: #94a3b8;
        }

        .class-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-item {
            background: #f8fafc;
            padding: 15px;
            border-radius: 12px;
            text-align: center;
        }

        .stat-item .number {
            font-size: 24px;
            font-weight: bold;
            color: #1e293b;
        }

        .stat-item .label {
            color: #64748b;
            font-size: 13px;
        }

        .no-subjects {
            text-align: center;
            padding: 30px;
            color: #64748b;
        }

        @media(max-width: 768px) {
            .container {
                margin-left: 0;
                padding: 15px;
            }

            table {
                display: block;
                overflow-x: auto;
            }

            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<?php include 'academic_sidebar.php'; ?>
<?php include '../auth/topbar.php'; ?>

<div class="container">
    <h2>
        <i class="fas fa-check-circle"></i>
        Approve Class Results
    </h2>

    <!-- Display Session Messages -->
    <?php if(isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($_SESSION['success_msg']) ?>
            <?php unset($_SESSION['success_msg']); ?>
        </div>
    <?php endif; ?>

    <?php if(isset($_SESSION['error_msg'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($_SESSION['error_msg']) ?>
            <?php unset($_SESSION['error_msg']); ?>
        </div>
    <?php endif; ?>

    <?php if(isset($_SESSION['warning_msg'])): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <?= htmlspecialchars($_SESSION['warning_msg']) ?>
            <?php unset($_SESSION['warning_msg']); ?>
        </div>
    <?php endif; ?>

    <!-- CLASSES -->
    <div class="card">
        <h3>
            <i class="fas fa-school"></i>
            Classes with Pending Results
        </h3>

        <div class="grid">
            <?php if(mysqli_num_rows($classes) > 0): ?>
                <?php while($class = mysqli_fetch_assoc($classes)): ?>
                    <div class="box">
                        <h4>
                            <i class="fas fa-users"></i>
                            <?= htmlspecialchars($class['class_name']) ?>
                        </h4>
                        <p>
                            <?= $class['pending_marks'] ?> pending marks in <?= $class['total_subjects'] ?> subjects
                        </p>
                        <a class="btn view-btn" href="?class_id=<?= $class['class_id'] ?>">
                            <i class="fas fa-eye"></i>
                            View Class Details
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty" style="grid-column: 1/-1;">
                    <i class="fas fa-folder-open" style="font-size:50px;margin-bottom:15px;"></i>
                    <p>No pending results found</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- CLASS DETAILS WITH SUBJECTS -->
    <?php if(!empty($class_id)): ?>
        <div class="card">
            <?php if($subjects && mysqli_num_rows($subjects) > 0): ?>
                <div class="class-header">
                    <h3>
                        <i class="fas fa-chalkboard"></i>
                        <?= htmlspecialchars($class_name) ?> - Subject Details
                    </h3>
                    
                    <?php
                    // Check if there are any pending subjects
                    $has_pending = false;
                    mysqli_data_seek($subjects, 0);
                    while($subj = mysqli_fetch_assoc($subjects)) {
                        if($subj['status'] == 'pending') {
                            $has_pending = true;
                            break;
                        }
                    }
                    mysqli_data_seek($subjects, 0);
                    ?>
                    
                    <?php if($has_pending): ?>
                        <a class="btn approve-all-btn" 
                           href="?class_id=<?= $class_id ?>&approve_class=true"
                           onclick="return confirm('Are you sure you want to approve ALL results for <?= htmlspecialchars($class_name) ?>? This action cannot be undone.')">
                            <i class="fas fa-check-double"></i>
                            Approve All Class Results
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Summary Statistics -->
                <?php
                $total_subjects = 0;
                $published_count = 0;
                $pending_count = 0;
                $not_submitted_count = 0;
                
                mysqli_data_seek($subjects, 0);
                while($subj = mysqli_fetch_assoc($subjects)) {
                    $total_subjects++;
                    if($subj['status'] == 'published') $published_count++;
                    elseif($subj['status'] == 'pending') $pending_count++;
                    else $not_submitted_count++;
                }
                mysqli_data_seek($subjects, 0);
                ?>
                
                <div class="summary-stats">
                    <div class="stat-item">
                        <div class="number"><?= $total_subjects ?></div>
                        <div class="label">Total Subjects</div>
                    </div>
                    <div class="stat-item">
                        <div class="number" style="color:#16a34a;"><?= $published_count ?></div>
                        <div class="label">Published Subjects</div>
                    </div>
                    <div class="stat-item">
                        <div class="number" style="color:#d97706;"><?= $pending_count ?></div>
                        <div class="label">Pending Subjects</div>
                    </div>
                    <div class="stat-item">
                        <div class="number" style="color:#dc2626;"><?= $not_submitted_count ?></div>
                        <div class="label">Not Submitted</div>
                    </div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Subject</th>
                            <th>Teacher</th>
                            <th>Status</th>
                            <th>Students</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $count = 1;
                        while($subject = mysqli_fetch_assoc($subjects)):
                            $status_class = '';
                            $status_icon = '';
                            $status_text = '';
                            
                            if($subject['status'] == 'pending') {
                                $status_class = 'badge-pending';
                                $status_icon = 'fa-clock';
                                $status_text = 'Pending Approval';
                            } elseif($subject['status'] == 'published') {
                                $status_class = 'badge-published';
                                $status_icon = 'fa-check-circle';
                                $status_text = 'Published';
                            } else {
                                $status_class = 'badge-not-submitted';
                                $status_icon = 'fa-times-circle';
                                $status_text = 'Not Submitted';
                            }
                        ?>
                            <tr>
                                <td><?= $count++ ?></td>
                                <td><?= htmlspecialchars($subject['subject_name']) ?></td>
                                <td>
                                    <span class="badge badge-teacher">
                                        <i class="fas fa-user-tie"></i>
                                        <?= htmlspecialchars($subject['teacher_name']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?= $status_class ?>">
                                        <i class="fas <?= $status_icon ?>"></i>
                                        <?= $status_text ?>
                                    </span>
                                </td>
                                <td><?= $subject['marks_count'] ?? 0 ?> students</td>
                                <td>
                                    <?php if($subject['status'] == 'pending'): ?>
                                        <a class="btn approve-btn" 
                                           href="?class_id=<?= $class_id ?>&approve_subject=<?= $subject['subject_id'] ?>"
                                           onclick="return confirm('Approve <?= htmlspecialchars($subject['subject_name']) ?> results?')">
                                            <i class="fas fa-check"></i>
                                            Approve
                                        </a>
                                    <?php else: ?>
                                        <span style="color:#94a3b8;font-size:13px;">
                                            <?php if($subject['status'] == 'published'): ?>
                                                <i class="fas fa-check-circle" style="color:#16a34a;"></i> Approved
                                            <?php else: ?>
                                                <i class="fas fa-clock"></i> Awaiting Submission
                                            <?php endif; ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <!-- Class Actions -->
                <div class="action-bar">
                    <?php if($has_pending): ?>
                        <a class="btn approve-all-btn" 
                           href="?class_id=<?= $class_id ?>&approve_class=true"
                           onclick="return confirm('Are you sure you want to approve ALL pending results for <?= htmlspecialchars($class_name) ?>? This action cannot be undone.')">
                            <i class="fas fa-check-double"></i>
                            Approve All Class Results
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="no-subjects">
                    <i class="fas fa-info-circle" style="font-size:40px;margin-bottom:15px;color:#94a3b8;"></i>
                    <p>No subjects found for this class. Please ensure subjects are assigned to teachers for this class.</p>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
<?php include '../footer.php'; ?>
</body>
</html>