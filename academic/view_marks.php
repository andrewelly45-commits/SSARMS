<?php
session_start();
include '../db.php';

// When deleting marks
include '../auth/audit_functions.php';

logSystemAction(
    $_SESSION['user_id'],
    $_SESSION['role'],
    $_SESSION['full_name'],
    'delete',
    "Deleted mark ID: $mark_id for student: $student_name",
    'marks',
    'marks',
    $mark_id,
    ['marks' => $row['marks'], 'student' => $row['full_name']]
);

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
     WHERE user_id = '$user_id'"
);

$teacher = mysqli_fetch_assoc($teacher_query);

if (!$teacher) {
    die("Teacher not found");
}

$teacher_id = $teacher['teacher_id'];

/* ================= DELETE ALL MARKS FOR CLASS ================= */
if (isset($_GET['delete_class_marks'])) {
    $class_id = mysqli_real_escape_string($conn, $_GET['class_id']);
    
    // Check if the class belongs to this teacher
    $check_class_query = mysqli_query($conn,
        "SELECT class_id 
         FROM teacher_class 
         WHERE class_id = '$class_id' AND teacher_id = '$teacher_id'"
    );
    
    if (mysqli_num_rows($check_class_query) > 0) {
        // Check if there are any published marks in this class
        $check_published = mysqli_query($conn,
            "SELECT COUNT(*) as published_count 
             FROM marks 
             WHERE class_id = '$class_id' 
             AND teacher_id = '$teacher_id' 
             AND status = 'published'"
        );
        
        $published_data = mysqli_fetch_assoc($check_published);
        
        if ($published_data['published_count'] > 0) {
            $_SESSION['error_message'] = "Cannot delete class results because some marks are already published!";
        } else {
            // Delete all pending marks for this class
            $delete_query = "DELETE FROM marks 
                            WHERE class_id = '$class_id' 
                            AND teacher_id = '$teacher_id' 
                            AND status = 'pending'";
            
            if (mysqli_query($conn, $delete_query)) {
                $deleted_count = mysqli_affected_rows($conn);
                $_SESSION['success_message'] = "Successfully deleted $deleted_count pending result(s) for this class!";
            } else {
                $_SESSION['error_message'] = "Error deleting marks: " . mysqli_error($conn);
            }
        }
    } else {
        $_SESSION['error_message'] = "Unauthorized access to this class!";
    }
    
    // Redirect to remove delete parameter from URL
    header("Location: view_marks.php?class_id=" . $class_id);
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

/* ================= FETCH MARKS ================= */
$marks = null;
$has_published_marks = false;

if (!empty($class_id)) {
    // Check if there are any published marks in this class
    $check_published_query = mysqli_query($conn,
        "SELECT COUNT(*) as published_count 
         FROM marks 
         WHERE class_id = '$class_id' 
         AND teacher_id = '$teacher_id' 
         AND status = 'published'"
    );
    $published_result = mysqli_fetch_assoc($check_published_query);
    $has_published_marks = $published_result['published_count'] > 0;

    $marks = mysqli_query($conn,
        "SELECT
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

        ORDER BY
            u.full_name ASC,
            s.subject_name ASC"
    );
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
    padding: 100px 30px 30px 30px; /* FIX FOR TOPBAR */
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
    background: orange;
    color: black;
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
}

</style>
</head>
<body>

<?php include 'academic_sidebar.php'; ?>

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
 <!-- MARKS TABLE -->
<?php if($class_id && $marks && mysqli_num_rows($marks) > 0): ?>

<div class="card">

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
        <h3 style="margin-bottom:0;">
            <i class="fas fa-table"></i>
            Students Marks
        </h3>
        
        <?php if(!$has_published_marks): ?>
            <button 
                onclick="confirmDeleteClass()"
                style="background:#ef4444;color:white;border:none;padding:10px 20px;border-radius:8px;cursor:pointer;font-weight:600;display:flex;align-items:center;gap:8px;transition:background 0.3s;"
                onmouseover="this.style.background='#dc2626'"
                onmouseout="this.style.background='#ef4444'">
                <i class="fas fa-trash-alt"></i>
                Delete All Class Results
            </button>
        <?php else: ?>
            <span style="color:#94a3b8;font-size:14px;display:flex;align-items:center;gap:8px;">
                <i class="fas fa-lock"></i>
                Some results are published - cannot delete
            </span>
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
        <h3 style="text-align:center;margin-bottom:10px;color:#1e293b;">Confirm Delete All</h3>
        <p style="text-align:center;color:#475569;margin-bottom:20px;" id="deleteMessage">
            Are you sure you want to delete ALL pending results for this class?<br>
            <strong style="color:#dc2626;">This action cannot be undone!</strong>
        </p>
        <div style="display:flex;gap:10px;justify-content:center;">
            <button onclick="closeModal()" style="padding:10px 24px;border-radius:8px;border:1px solid #cbd5e1;background:white;cursor:pointer;">
                Cancel
            </button>
            <a href="view_marks.php?delete_class_marks=true&class_id=<?= $class_id ?>" id="deleteLink" style="padding:10px 24px;border-radius:8px;background:#ef4444;color:white;text-decoration:none;font-weight:bold;">
                Delete All
            </a>
        </div>
    </div>
</div>

<script>
function confirmDeleteClass() {
    const modal = document.getElementById('deleteModal');
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