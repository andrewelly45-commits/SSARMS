<?php
session_start();
include '../db.php';



/* ================= AUTH CHECK ================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'academic') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ================= GET TEACHER ================= */
$teacher_query = mysqli_query($conn,
    "SELECT teacher_id
     FROM teacher
     WHERE user_id='$user_id'"
);

$teacher = mysqli_fetch_assoc($teacher_query);

if (!$teacher) {
    die("Teacher not found");
}

$teacher_id = $teacher['teacher_id'];

/* ================= GET TEACHER CLASSES ================= */
$classes = mysqli_query($conn,
    "SELECT
        c.class_id,
        c.class_name
     FROM teacher_class tc
     INNER JOIN class c ON tc.class_id = c.class_id
     WHERE tc.teacher_id='$teacher_id'
     ORDER BY c.class_name ASC"
);

/* ================= SELECTED CLASS ================= */
$class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : '';

/* ================= GET SUBJECTS ================= */
$subjects = null;
if (!empty($class_id)) {
    $subjects = mysqli_query($conn,
        "SELECT
            s.subject_id,
            s.subject_name
         FROM teacher_subject ts
         INNER JOIN subject s ON ts.subject_id = s.subject_id
         WHERE ts.teacher_id='$teacher_id'
         AND ts.class_id='$class_id'
         ORDER BY s.subject_name ASC"
    );
}

/* ================= GET STUDENTS COUNT ================= */
$student_count = 0;
if (!empty($class_id)) {
    $count_query = mysqli_query($conn,
        "SELECT COUNT(*) as total
         FROM student
         WHERE class_id='$class_id'"
    );
    $count_result = mysqli_fetch_assoc($count_query);
    $student_count = $count_result['total'];
}

/* ================= DISPLAY IMPORT RESULTS ================= */
if (isset($_SESSION['success_msg']) || isset($_SESSION['error_msg']) || isset($_SESSION['warning_msg'])) {
    // Messages will be displayed in the HTML
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Marks via Excel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f1f5f9; font-family: 'Segoe UI', sans-serif; }
        
        .container {
            margin-left: 270px;
            padding: 100px 30px 30px 30px;
        }
        
        .card {
            background: white;
            padding: 25px;
            border-radius: 22px;
            margin-bottom: 25px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.05);
        }
        
        h2 {
            font-size: 28px;
            color: #0f172a;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        h2 i { color: #f59e0b; }
        
        h3 {
            font-size: 18px;
            color: #1e293b;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        h3 i { color: #f59e0b; }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #475569;
            font-size: 13px;
            font-weight: 600;
        }
        
        select, input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #dbe2ea;
            border-radius: 12px;
            font-size: 14px;
            background: white;
        }
        
        select:focus, input:focus {
            outline: none;
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
        }
        
        .btn {
            border: none;
            padding: 12px 22px;
            border-radius: 40px;
            color: white;
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.2s;
        }
        
        .btn:hover { transform: translateY(-2px); }
        .btn-load { background: #f59e0b; color: black; }
        .btn-load:hover { background: #d97706; }
        .btn-save { background: #16a34a; }
        .btn-save:hover { background: #15803d; }
        .btn-info { background: #3b82f6; }
        .btn-info:hover { background: #2563eb; }
        
        .steps-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        
        .step-card {
            background: #f8fafc;
            padding: 25px;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            text-align: center;
            transition: 0.3s;
        }
        
        .step-card:hover {
            border-color: #f59e0b;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.1);
        }
        
        .step-number {
            background: #f59e0b;
            color: black;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 15px;
        }
        
        .step-card h4 {
            color: #0f172a;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .step-card p {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 15px;
            line-height: 1.5;
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
        
        .alert-warning {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            color: #92400e;
        }
        
        .alert-info {
            background: #dbeafe;
            border-left: 4px solid #3b82f6;
            color: #1e40af;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px;
            color: #94a3b8;
        }
        
        .empty-state i {
            font-size: 50px;
            margin-bottom: 15px;
            display: block;
        }
        
        .student-info {
            background: #f8fafc;
            padding: 15px 20px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .student-info i {
            color: #f59e0b;
            font-size: 20px;
        }
        
        .student-info .count {
            font-weight: bold;
            font-size: 18px;
            color: #0f172a;
        }
        
        .error-details {
            max-height: 200px;
            overflow-y: auto;
            margin-top: 10px;
        }
        
        .error-details ul {
            margin-left: 20px;
            margin-top: 5px;
        }
        
        .error-details li {
            color: #991b1b;
            font-size: 13px;
            padding: 3px 0;
        }
        
        @media(max-width: 768px) {
            .container {
                margin-left: 0;
                padding: 15px;
                margin-top: 70px;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
            .steps-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<?php include 'academic_sidebar.php'; ?>
<?php include '../auth/topbar.php'; ?>

<div class="container">
    <h2><i class="fas fa-file-excel"></i> Enter Marks via Excel</h2>
    
    <!-- ===== ALERT MESSAGES ===== -->
    <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success_msg']) ?>
            <?php unset($_SESSION['success_msg']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_msg'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($_SESSION['error_msg']) ?>
            <?php unset($_SESSION['error_msg']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['warning_msg'])): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['warning_msg']) ?>
            <?php unset($_SESSION['warning_msg']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_details']) && !empty($_SESSION['error_details'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-times-circle"></i>
            <div class="error-details">
                <strong>Import Errors Details:</strong>
                <ul>
                    <?php foreach ($_SESSION['error_details'] as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php unset($_SESSION['error_details']); ?>
    <?php endif; ?>

    <!-- ===== SELECT CLASS ===== -->
    <div class="card">
        <h3><i class="fas fa-school"></i> Select Class</h3>
        <form method="GET">
            <div class="form-row">
                <div>
                    <label>Select Class</label>
                    <select name="class_id" required>
                        <option value="">-- Select Class --</option>
                        <?php while($c = mysqli_fetch_assoc($classes)): ?>
                            <option value="<?= $c['class_id'] ?>" <?= ($class_id == $c['class_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['class_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-load">
                        <i class="fas fa-sync"></i> Load Class
                    </button>
                </div>
            </div>
        </form>
        
        <?php if($class_id): ?>
        <div class="student-info" style="margin-top: 15px;">
            <i class="fas fa-users"></i>
            <span>Total Students: <span class="count"><?= $student_count ?></span></span>
            <?php if($subjects && mysqli_num_rows($subjects) > 0): ?>
                <i class="fas fa-book" style="margin-left: 10px;"></i>
                <span>Subjects: <span class="count"><?= mysqli_num_rows($subjects) ?></span></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- ===== EXCEL WORKFLOW ===== -->
    <?php if($class_id && $subjects && mysqli_num_rows($subjects) > 0 && $student_count > 0): ?>
    <div class="card">
        <h3><i class="fas fa-upload"></i> Excel Marks Entry Workflow</h3>
        
        <div class="steps-container">
            <!-- Step 1: Download Template -->
            <div class="step-card">
                <div class="step-number">1</div>
                <h4><i class="fas fa-download"></i> Download Template</h4>
                <p>Download the Excel template with student names already loaded for your selected class and subject.</p>
                
                <form action="download_marks_template.php" method="POST">
                    <input type="hidden" name="class_id" value="<?= $class_id ?>">
                    
                    <div style="text-align: left; margin-bottom: 15px;">
                        <label style="font-size: 12px;">Subject <span style="color:red;">*</span></label>
                        <select name="subject_id" required style="width:100%; padding:10px; border-radius:10px; border:1px solid #dbe2ea;">
                            <option value="">-- Select Subject --</option>
                            <?php 
                            mysqli_data_seek($subjects, 0);
                            while($s = mysqli_fetch_assoc($subjects)): 
                            ?>
                                <option value="<?= $s['subject_id'] ?>">
                                    <?= htmlspecialchars($s['subject_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div style="text-align: left; margin-bottom: 15px;">
                        <label style="font-size: 12px;">Term <span style="color:red;">*</span></label>
                        <select name="term" required style="width:100%; padding:10px; border-radius:10px; border:1px solid #dbe2ea;">
                            <option value="">-- Select Term --</option>
                            <option value="Term 1">Term 1</option>
                            <option value="Term 2">Term 2</option>
                            <option value="Term 3">Term 3</option>
                        </select>
                    </div>
                    
                    <div style="text-align: left; margin-bottom: 15px;">
                        <label style="font-size: 12px;">Academic Year <span style="color:red;">*</span></label>
                        <input type="text" name="academic_year" value="<?= date('Y') ?>" required style="width:100%; padding:10px; border-radius:10px; border:1px solid #dbe2ea;">
                    </div>
                    
                    <button type="submit" class="btn btn-load" style="width:100%; justify-content: center;">
                        <i class="fas fa-download"></i> Download Template
                    </button>
                </form>
            </div>
            
            <!-- Step 2: Enter Marks in Excel -->
           <!-- Step 2: Enter Marks in Excel -->
<div class="step-card">
    <div class="step-number">2</div>
    <h4><i class="fas fa-edit"></i> Enter Marks in Excel</h4>
    <p>Open the downloaded Excel file and enter marks for each student. Save the file after entering marks.</p>
    
    <div style="background: #fef3c7; padding: 15px; border-radius: 10px; margin-top: 10px; text-align: left;">
        <i class="fas fa-info-circle" style="color: #d97706;"></i>
        <span style="font-size: 13px; color: #92400e;">
            <strong>Instructions:</strong>
            <ul style="margin-left: 20px; margin-top: 5px;">
                <li><strong>Column A:</strong> Registration No - <span style="color: #dc2626;">DO NOT CHANGE</span></li>
                <li><strong>Column B:</strong> Student Name - <span style="color: #dc2626;">DO NOT CHANGE</span></li>
                <li><strong>Column C:</strong> Enter Marks (0-100)</li>
                <li>Only enter marks in Column C</li>
                <li>Save the file before uploading</li>
            </ul>
        </span>
    </div>
</div>
            
            <!-- Step 3: Upload Completed Excel -->
            <div class="step-card">
                <div class="step-number">3</div>
                <h4><i class="fas fa-upload"></i> Upload Completed Excel</h4>
                <p>Upload the completed Excel file with marks. The system will automatically import all marks.</p>
                
                <form action="import_marks.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="class_id" value="<?= $class_id ?>">
                    
                    <div style="text-align: left; margin-bottom: 15px;">
                        <label style="font-size: 12px;">Subject <span style="color:red;">*</span></label>
                        <select name="subject_id" required style="width:100%; padding:10px; border-radius:10px; border:1px solid #dbe2ea;">
                            <option value="">-- Select Subject --</option>
                            <?php 
                            mysqli_data_seek($subjects, 0);
                            while($s = mysqli_fetch_assoc($subjects)): 
                            ?>
                                <option value="<?= $s['subject_id'] ?>">
                                    <?= htmlspecialchars($s['subject_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div style="text-align: left; margin-bottom: 15px;">
                        <label style="font-size: 12px;">Term <span style="color:red;">*</span></label>
                        <select name="term" required style="width:100%; padding:10px; border-radius:10px; border:1px solid #dbe2ea;">
                            <option value="">-- Select Term --</option>
                            <option value="Term 1">Term 1</option>
                            <option value="Term 2">Term 2</option>
                            <option value="Term 3">Term 3</option>
                        </select>
                    </div>
                    
                    <div style="text-align: left; margin-bottom: 15px;">
                        <label style="font-size: 12px;">Academic Year <span style="color:red;">*</span></label>
                        <input type="text" name="academic_year" value="<?= date('Y') ?>" required style="width:100%; padding:10px; border-radius:10px; border:1px solid #dbe2ea;">
                    </div>
                    
                    <div style="text-align: left; margin-bottom: 15px;">
                        <label style="font-size: 12px;">Select Excel File <span style="color:red;">*</span></label>
                        <input type="file" name="marks_file" accept=".xlsx,.xls,.csv" required style="width:100%; padding:10px; border:1px solid #dbe2ea; border-radius:10px;">
                    </div>
                    
                    <button type="submit" class="btn btn-save" style="width:100%; justify-content: center;">
                        <i class="fas fa-file-import"></i> Import Marks
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <?php elseif($class_id && $subjects && mysqli_num_rows($subjects) > 0 && $student_count == 0): ?>
        <div class="card">
            <div class="empty-state">
                <i class="fas fa-user-graduate"></i>
                <p>No students found in this class</p>
                <p style="font-size:14px; margin-top:10px; color:#94a3b8;">
                    Please add students to this class first
                </p>
            </div>
        </div>
    <?php elseif($class_id && $subjects && mysqli_num_rows($subjects) == 0): ?>
        <div class="card">
            <div class="empty-state">
                <i class="fas fa-book"></i>
                <p>No subjects assigned to you for this class</p>
                <p style="font-size:14px; margin-top:10px; color:#94a3b8;">
                    Please contact the administrator to assign subjects
                </p>
            </div>
        </div>
    <?php elseif(!$class_id): ?>
        <div class="card">
            <div class="empty-state">
                <i class="fas fa-school"></i>
                <p>Please select a class to get started</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../footer.php'; ?>
</body>
</html>