<?php
session_start();
include '../db.php';

// ============================================
// INCLUDE AUDIT LOGGER WITH BETTER ERROR HANDLING
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

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ============================================
// AUTH CHECK - SUPPORT BOTH TEACHER AND ACADEMIC
// ============================================
$allowed_roles = ['teacher', 'academic'];
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
    if (function_exists('logAction')) {
        logAction('access_denied', 'marks', "Unauthorized access attempt to view marks by: " . ($_SESSION['full_name'] ?? 'Unknown'), 'failed');
    }
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

/* ================= GET TEACHER ================= */
$teacher_query = mysqli_query($conn,
    "SELECT teacher_id
     FROM teacher
     WHERE user_id = '$user_id'"
);

$teacher = mysqli_fetch_assoc($teacher_query);

if (!$teacher) {
    if (function_exists('logAction')) {
        logAction('error', 'marks', "Teacher not found for user_id: $user_id", 'failed');
    }
    die("Teacher not found");
}

$teacher_id = $teacher['teacher_id'];

// ============================================
// LOG VIEW MARKS PAGE ACCESS
// ============================================
if (function_exists('logAction')) {
    logAction('view', 'marks', ucfirst($user_role) . " viewed marks page: " . ($_SESSION['full_name'] ?? 'Unknown'), 'success', $teacher_id, 'users');
}

/* ================= DELETE MARKS FOR SPECIFIC TERM ================= */
if (isset($_GET['delete_term_marks'])) {
    $class_id = mysqli_real_escape_string($conn, $_GET['class_id']);
    $term = mysqli_real_escape_string($conn, $_GET['term']);
    
    // Get class name for logging
    $class_query = mysqli_query($conn, "SELECT class_name FROM class WHERE class_id='$class_id'");
    $class_row = mysqli_fetch_assoc($class_query);
    $class_name = $class_row['class_name'] ?? 'Unknown Class';
    
    // Log delete attempt
    if (function_exists('logAction')) {
        logAction('delete', 'marks', "Attempting to delete $term marks for $class_name", 'pending', $class_id, 'marks');
    }
    
    // Check if the class belongs to this teacher
    $check_class_query = mysqli_query($conn,
        "SELECT class_id 
         FROM teacher_class 
         WHERE class_id = '$class_id' AND teacher_id = '$teacher_id'"
    );
    
    if (mysqli_num_rows($check_class_query) > 0) {
        // Check if there are any published marks in this class for this term
        $check_published = mysqli_query($conn,
            "SELECT COUNT(*) as published_count 
             FROM marks 
             WHERE class_id = '$class_id' 
             AND teacher_id = '$teacher_id' 
             AND term = '$term'
             AND status = 'published'"
        );
        
        $published_data = mysqli_fetch_assoc($check_published);
        
        if ($published_data['published_count'] > 0) {
            $_SESSION['error_message'] = "Cannot delete $term results because some marks are already published!";
            
            if (function_exists('logAction')) {
                logAction('delete', 'marks', "Failed to delete $term marks for $class_name - Contains published marks", 'failed', $class_id, 'marks');
            }
        } else {
            // Get count of marks to be deleted
            $count_query = mysqli_query($conn,
                "SELECT COUNT(*) as total 
                 FROM marks 
                 WHERE class_id = '$class_id' 
                 AND teacher_id = '$teacher_id' 
                 AND term = '$term'
                 AND status = 'pending'"
            );
            $count_data = mysqli_fetch_assoc($count_query);
            $deleted_count = $count_data['total'] ?? 0;
            
            // Delete all pending marks for this class and term
            $delete_query = "DELETE FROM marks 
                            WHERE class_id = '$class_id' 
                            AND teacher_id = '$teacher_id' 
                            AND term = '$term'
                            AND status = 'pending'";
            
            if (mysqli_query($conn, $delete_query)) {
                $actual_deleted = mysqli_affected_rows($conn);
                
                // Log successful deletion
                if (function_exists('logAction')) {
                    $log_data = [
                        'deleted_records' => $actual_deleted,
                        'class_id' => $class_id,
                        'class_name' => $class_name,
                        'term' => $term
                    ];
                    
                    logAction(
                        'delete', 
                        'marks', 
                        "Deleted $actual_deleted pending marks for $class_name - $term", 
                        'success', 
                        $class_id, 
                        'marks',
                        null,
                        $log_data
                    );
                }
                
                $_SESSION['success_message'] = "Successfully deleted $actual_deleted pending result(s) for $term!";
            } else {
                $_SESSION['error_message'] = "Error deleting marks: " . mysqli_error($conn);
                
                if (function_exists('logAction')) {
                    logAction('error', 'marks', "Failed to delete marks: " . mysqli_error($conn), 'failed', $class_id, 'marks');
                }
            }
        }
    } else {
        $_SESSION['error_message'] = "Unauthorized access to this class!";
        
        if (function_exists('logAction')) {
            logAction('access_denied', 'marks', "Unauthorized delete attempt for class: $class_name by user: " . ($_SESSION['full_name'] ?? 'Unknown'), 'failed');
        }
    }
    
    // Redirect to remove delete parameter from URL
    header("Location: view_marks.php?class_id=" . $class_id . "&term=" . urlencode($term));
    exit();
}

/* ================= FETCH CLASSES ================= */
$classes = mysqli_query($conn,
    "SELECT DISTINCT
        c.class_id,
        c.class_name
     FROM teacher_class tc
     INNER JOIN class c
        ON tc.class_id = c.class_id
     WHERE tc.teacher_id = '$teacher_id'
     ORDER BY c.class_name ASC"
);

/* ================= SELECTED CLASS ================= */
$class_id = isset($_GET['class_id'])
    ? $_GET['class_id']
    : '';

$term = isset($_GET['term'])
    ? $_GET['term']
    : 'all';

// Log class selection if a class is selected
if (!empty($class_id)) {
    $class_query = mysqli_query($conn, "SELECT class_name FROM class WHERE class_id='$class_id'");
    $class_row = mysqli_fetch_assoc($class_query);
    $class_name = $class_row['class_name'] ?? 'Unknown Class';
    
    if (function_exists('logAction')) {
        logAction('view', 'marks', ucfirst($user_role) . " viewed marks for class: $class_name (Term: $term)", 'success', $class_id, 'classes');
    }
}

/* ================= FETCH MARKS ================= */
$marks = null;
$has_published_marks = false;
$has_published_term1 = false;
$has_published_term2 = false;

if (!empty($class_id)) {
    // Check if there are any published marks in this class for Term 1
    $check_published_term1 = mysqli_query($conn,
        "SELECT COUNT(*) as published_count 
         FROM marks 
         WHERE class_id = '$class_id' 
         AND teacher_id = '$teacher_id' 
         AND term = 'Term 1'
         AND status = 'published'"
    );
    $published_result_term1 = mysqli_fetch_assoc($check_published_term1);
    $has_published_term1 = $published_result_term1['published_count'] > 0;
    
    // Check if there are any published marks in this class for Term 2
    $check_published_term2 = mysqli_query($conn,
        "SELECT COUNT(*) as published_count 
         FROM marks 
         WHERE class_id = '$class_id' 
         AND teacher_id = '$teacher_id' 
         AND term = 'Term 2'
         AND status = 'published'"
    );
    $published_result_term2 = mysqli_fetch_assoc($check_published_term2);
    $has_published_term2 = $published_result_term2['published_count'] > 0;

    // Build marks query
    $query = "
    SELECT
        m.mark_id,
        m.marks,
        m.term,
        m.academic_year,
        m.status,

        s.subject_name,

        st.student_id,

        u.full_name

    FROM marks m

    INNER JOIN student st
        ON m.student_id = st.student_id

    INNER JOIN users u
        ON st.user_id = u.user_id

    INNER JOIN subject s
        ON m.subject_id = s.subject_id

    WHERE
        m.teacher_id = '$teacher_id'
        AND m.class_id = '$class_id'
    ";

    // Filter by term
    if($term != 'all'){
        $query .= " AND m.term='$term'";
    }

    // Order results
    $query .= "
    ORDER BY
        u.full_name ASC,
        s.subject_name ASC
    ";

    // Execute query
    $marks = mysqli_query($conn, $query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>View Marks</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    background:#f1f5f9;
    font-family:'Segoe UI',sans-serif;
}

.container {
    margin-left: 270px;
    padding: 100px 30px 30px 30px;
}
.card{
    background:white;
    padding:25px;
    border-radius:18px;
    margin-bottom:25px;
    box-shadow:0 4px 10px rgba(0,0,0,0.05);
}

h2{
    font-size:28px;
    margin-bottom:25px;
    color:#1e293b;
    display:flex;
    align-items:center;
    gap:12px;
}

h3{
    font-size:20px;
    margin-bottom:20px;
    color:#334155;
    display:flex;
    align-items:center;
    gap:10px;
}

.form-row{
    display:grid;
    grid-template-columns:1fr auto;
    gap:15px;
    align-items:end;
}

label{
    font-size:13px;
    font-weight:600;
    margin-bottom:8px;
    display:block;
    color:#475569;
}

select{
    width:100%;
    padding:12px;
    border:1px solid #cbd5e1;
    border-radius:12px;
    font-size:14px;
}

button{
    background: orange;
    color: black;
    border:none;
    padding:12px 24px;
    border-radius:30px;
    cursor:pointer;
    font-weight:600;
    display:flex;
    align-items:center;
    gap:8px;
}

button:hover{
    background: orange;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}

th{
    background: linear-gradient(180deg, #1a1a2e 0%, #0f0f23 100%);
    color: white;
    padding:14px;
    text-align:left;
    font-size:14px;
}

td{
    padding:14px;
    border-bottom:1px solid #e2e8f0;
}

tr:hover{
    background:#f8fafc;
}

.badge{
    background: white;
    color: black;
    padding:6px 14px;
    border-radius:30px;
    font-size:13px;
    display:inline-flex;
    align-items:center;
    gap:6px;
}

.mark-badge{
    background:#dcfce7;
    color:#15803d;
    padding:6px 14px;
    border-radius:30px;
    font-weight:bold;
}

.empty{
    text-align:center;
    padding:50px;
    color:#94a3b8;
}

.term-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.term-buttons a {
    padding: 8px 18px;
    border-radius: 20px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s;
}

.term-buttons a:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}

.term-buttons a.active {
    border: 2px solid;
}

.btn-all {
    background: #1e293b;
    color: white;
}

.btn-all:hover {
    background: #0f172a;
}

.btn-term1 {
    background: #2563eb;
    color: white;
}

.btn-term1:hover {
    background: #1d4ed8;
}

.btn-term2 {
    background: #16a34a;
    color: white;
}

.btn-term2:hover {
    background: #15803d;
}

.btn-delete-term {
    background: #ef4444;
    color: white;
    border: none;
    padding: 8px 18px;
    border-radius: 20px;
    cursor: pointer;
    font-weight: 600;
    font-size: 13px;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.btn-delete-term:hover {
    background: #dc2626;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);
}

.btn-delete-term:disabled {
    background: #94a3b8;
    cursor: not-allowed;
}

.btn-delete-term:disabled:hover {
    transform: none;
    box-shadow: none;
}

.header-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 10px;
}

.header-actions-left {
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}

@media(max-width:768px){

    .container{
        margin-left:0;
        padding:15px;
    }

    table{
        display:block;
        overflow-x:auto;
    }

    .form-row{
        grid-template-columns:1fr;
    }
    
    .header-actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .header-actions-left {
        flex-direction: column;
        align-items: stretch;
    }
    
    .term-buttons {
        justify-content: center;
    }
}

</style>
</head>
<body>

<?php
// ============================================
// INCLUDE CORRECT SIDEBAR BASED ON ROLE
// ============================================
if ($user_role == 'academic') {
    // Try multiple paths for academic sidebar
    $sidebar_paths = [
        'academic_sidebar.php',
        '../academic_sidebar.php',
        '../academic/academic_sidebar.php',
        '../../academic/academic_sidebar.php',
        '../admin/academic_sidebar.php'
    ];
    $found = false;
    foreach ($sidebar_paths as $path) {
        if (file_exists($path)) {
            include $path;
            $found = true;
            break;
        }
    }
    if (!$found) {
        // Fallback - try to include from admin folder
        if (file_exists('../admin/admin_sidebar.php')) {
            include '../admin/admin_sidebar.php';
        }
    }
} else {
    // For teacher users - try multiple paths
    $sidebar_paths = [
        'teacher_sidebar.php',
        '../teacher_sidebar.php',
        '../teacher/teacher_sidebar.php',
        '../../teacher/teacher_sidebar.php'
    ];
    $found = false;
    foreach ($sidebar_paths as $path) {
        if (file_exists($path)) {
            include $path;
            $found = true;
            break;
        }
    }
    if (!$found) {
        // Fallback to admin sidebar if teacher sidebar not found
        if (file_exists('../admin/admin_sidebar.php')) {
            include '../admin/admin_sidebar.php';
        }
    }
}
?>

<?php include '../auth/topbar.php'; ?>

<div class="container">

    <h2>
        <i class="fas fa-chart-line"></i>
        View Student Marks
    </h2>

    <!-- SELECT CLASS -->
    <div class="card">

        <h3>
            <i class="fas fa-school"></i>
            Select Class
        </h3>

        <form method="GET">

            <div class="form-row">

                <div>
                    <label>
                        Choose Class
                    </label>

                    <select name="class_id" required>

                        <option value="">
                            -- Select Class --
                        </option>

                        <?php while($c = mysqli_fetch_assoc($classes)): ?>

                            <option
                                value="<?= $c['class_id'] ?>"
                                <?= ($class_id == $c['class_id'])
                                    ? 'selected'
                                    : '' ?>>

                                <?= htmlspecialchars($c['class_name']) ?>

                            </option>

                        <?php endwhile; ?>

                    </select>
                </div>

                <div>
                    <button type="submit">
                        <i class="fas fa-eye"></i>
                        View Marks
                    </button>
                </div>

            </div>

        </form>

    </div>

    <!-- MARKS TABLE -->
<?php if($class_id && $marks && mysqli_num_rows($marks) > 0): ?>

<div class="card">

    <div class="header-actions">
        <div class="header-actions-left">
            <h3 style="margin-bottom:0;">
                <i class="fas fa-table"></i>
                Students Marks 
                <?= ($term != 'all') ? "- ".$term : "" ?>
            </h3>
            
            <!-- Term Filter Buttons -->
            <div class="term-buttons">
                <a href="view_marks.php?class_id=<?= $class_id ?>&term=all" class="btn-all <?= ($term == 'all') ? 'active' : '' ?>">
                    All Terms
                </a>
                <a href="view_marks.php?class_id=<?= $class_id ?>&term=Term 1" class="btn-term1 <?= ($term == 'Term 1') ? 'active' : '' ?>">
                    Term 1
                </a>
                <a href="view_marks.php?class_id=<?= $class_id ?>&term=Term 2" class="btn-term2 <?= ($term == 'Term 2') ? 'active' : '' ?>">
                    Term 2
                </a>
            </div>
        </div>
        
        <!-- Delete Button for Current Term -->
        <?php if($term != 'all'): ?>
            <?php 
            $term_has_published = ($term == 'Term 1') ? $has_published_term1 : $has_published_term2;
            ?>
            <?php if(!$term_has_published): ?>
                <button 
                    onclick="confirmDeleteTerm('<?= $term ?>')"
                    class="btn-delete-term">
                    <i class="fas fa-trash-alt"></i>
                    Delete <?= $term ?> Results
                </button>
            <?php else: ?>
                <button class="btn-delete-term" disabled>
                    <i class="fas fa-lock"></i>
                    <?= $term ?> - Published
                </button>
            <?php endif; ?>
        <?php else: ?>
            <!-- Show delete buttons for each term when viewing all -->
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <?php if(!$has_published_term1): ?>
                    <button onclick="confirmDeleteTerm('Term 1')" class="btn-delete-term" style="background:#2563eb;">
                        <i class="fas fa-trash-alt"></i> Delete Term 1
                    </button>
                <?php else: ?>
                    <button class="btn-delete-term" disabled style="background:#2563eb;">
                        <i class="fas fa-lock"></i> Term 1 Published
                    </button>
                <?php endif; ?>
                
                <?php if(!$has_published_term2): ?>
                    <button onclick="confirmDeleteTerm('Term 2')" class="btn-delete-term" style="background:#16a34a;">
                        <i class="fas fa-trash-alt"></i> Delete Term 2
                    </button>
                <?php else: ?>
                    <button class="btn-delete-term" disabled style="background:#16a34a;">
                        <i class="fas fa-lock"></i> Term 2 Published
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if(isset($_SESSION['success_message'])): ?>
        <div style="background:#dcfce7;color:#15803d;padding:12px 20px;border-radius:8px;margin-bottom:15px;display:flex;align-items:center;gap:10px;">
            <i class="fas fa-check-circle"></i>
            <?= $_SESSION['success_message']; ?>
            <?php unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <?php if(isset($_SESSION['error_message'])): ?>
        <div style="background:#fee2e2;color:#dc2626;padding:12px 20px;border-radius:8px;margin-bottom:15px;display:flex;align-items:center;gap:10px;">
            <i class="fas fa-exclamation-circle"></i>
            <?= $_SESSION['error_message']; ?>
            <?php unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <table>

        <thead>
            <tr>
                <th>No</th>
                <th>Student</th>
                <th>Subject</th>
                <th>Marks</th>
                <th>Term</th>
                <th>Year</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody>

            <?php
            $counter = 1;

            while($row = mysqli_fetch_assoc($marks)):
            ?>

            <tr>

                <td>
                    <?= $counter++ ?>
                </td>

                <td>
                    <span class="badge">
                        <i class="fas fa-user-graduate"></i>

                        <?= htmlspecialchars($row['full_name']) ?>
                    </span>
                </td>

                <td>
                    <?= htmlspecialchars($row['subject_name']) ?>
                </td>

                <td>
                    <span class="mark-badge">
                        <?= htmlspecialchars($row['marks']) ?>
                    </span>
                </td>

                <td>
                    <?= htmlspecialchars($row['term']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($row['academic_year']) ?>
                </td>

                <td>

                    <?php if($row['status'] == 'published'): ?>

                        <span style="color:green;font-weight:bold;">
                            <i class="fas fa-check-circle"></i> Published
                        </span>

                    <?php else: ?>

                        <span style="color:orange;font-weight:bold;">
                            <i class="fas fa-clock"></i> Pending
                        </span>

                    <?php endif; ?>

                </td>

            </tr>

            <?php endwhile; ?>

        </tbody>

    </table>

</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:white;padding:30px;border-radius:12px;max-width:400px;width:90%;">
        <div style="text-align:center;margin-bottom:20px;">
            <i class="fas fa-exclamation-triangle" style="font-size:48px;color:#ef4444;"></i>
        </div>
        <h3 style="text-align:center;margin-bottom:10px;color:#1e293b;">Confirm Delete</h3>
        <p style="text-align:center;color:#475569;margin-bottom:20px;" id="deleteMessage">
            Are you sure you want to delete ALL pending results for <strong id="termName">Term 1</strong>?<br>
            <strong style="color:#dc2626;">This action cannot be undone!</strong>
        </p>
        <div style="display:flex;gap:10px;justify-content:center;">
            <button onclick="closeModal()" style="padding:10px 24px;border-radius:8px;border:1px solid #cbd5e1;background:white;cursor:pointer;">
                Cancel
            </button>
            <a href="#" id="deleteLink" style="padding:10px 24px;border-radius:8px;background:#ef4444;color:white;text-decoration:none;font-weight:bold;">
                Delete All
            </a>
        </div>
    </div>
</div>

<script>
function confirmDeleteTerm(term) {
    const modal = document.getElementById('deleteModal');
    const termName = document.getElementById('termName');
    const deleteLink = document.getElementById('deleteLink');
    
    termName.textContent = term;
    deleteLink.href = `view_marks.php?delete_term_marks=true&class_id=<?= $class_id ?>&term=${encodeURIComponent(term)}`;
    
    modal.style.display = 'flex';
}

function closeModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('deleteModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

<?php elseif($class_id): ?>

<div class="card">

    <div class="empty">

        <i class="fas fa-folder-open"
           style="font-size:50px;margin-bottom:15px;"></i>

        <p>
            No marks found for this class.
        </p>

    </div>

</div>

<?php endif; ?>

</div>
<?php include '../footer.php'; ?>
</body>
</html>