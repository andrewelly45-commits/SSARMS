<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

/* GET TEACHER ID */
if (!isset($_GET['teacher_id'])) {
    die("Teacher ID Missing");
}

$teacher_id = $_GET['teacher_id'];

/* FETCH TEACHER DATA */
$query = mysqli_query($conn, "
    SELECT 
        teacher.teacher_id,
        teacher.phone_no,
        teacher.specialization,
        teacher.status,
        users.full_name,
        users.email
    FROM teacher
    JOIN users 
        ON teacher.user_id = users.user_id
    WHERE teacher.teacher_id = '$teacher_id'
");

$teacher = mysqli_fetch_assoc($query);

if (!$teacher) {
    die("Teacher not found");
}

/* FETCH CLASSES */
$classes = mysqli_query($conn, "
    SELECT * FROM class
");

/* UPDATE */
if (isset($_POST['update_teacher'])) {

    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone_no']);
    $specialization = mysqli_real_escape_string($conn, $_POST['specialization']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $class_id = $_POST['class_id'];

    /* UPDATE USERS */
    mysqli_query($conn, "
        UPDATE users
        JOIN teacher 
            ON users.user_id = teacher.user_id
        SET 
            users.full_name = '$full_name',
            users.email = '$email'
        WHERE teacher.teacher_id = '$teacher_id'
    ");

    /* UPDATE TEACHER */
    mysqli_query($conn, "
        UPDATE teacher
        SET
            phone_no = '$phone',
            specialization = '$specialization',
            status = '$status'
        WHERE teacher_id = '$teacher_id'
    ");

    /* UPDATE CLASS */
    mysqli_query($conn, "
        DELETE FROM teacher_class
        WHERE teacher_id = '$teacher_id'
    ");

    if (!empty($class_id)) {

        mysqli_query($conn, "
            INSERT INTO teacher_class (
                teacher_id,
                class_id
            ) VALUES (
                '$teacher_id',
                '$class_id'
            )
        ");
    }

    echo "
    <script>
        alert('Teacher Updated Successfully');
        window.location='manage_teachers.php';
    </script>
    ";
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Edit Teacher</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>

body{
    font-family:Arial;
    background:#eef2ff;
    padding:30px;
}

.card{
    max-width:700px;
    margin:auto;
    background:white;
    padding:25px;
    border-radius:15px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
}

h2{
    margin-bottom:20px;
    color:#1e293b;
}

.input-group{
    margin-bottom:15px;
}

label{
    display:block;
    margin-bottom:6px;
    font-weight:bold;
}

input,
select{
    width:100%;
    padding:12px;
    border:1px solid #cbd5e1;
    border-radius:10px;
}

button{
    background:#2563eb;
    color:white;
    border:none;
    padding:12px;
    width:100%;
    border-radius:10px;
    cursor:pointer;
    font-weight:bold;
}

button:hover{
    background:#1d4ed8;
}

</style>

</head>

<body>
    <?php include 'admin_sidebar.php'; ?>

    <?php include 'admin_topbar.php'; ?>

<div class="card">

<h2>
    <i class="fas fa-edit"></i>
    Edit Teacher
</h2>

<form method="POST">

    <div class="input-group">
        <label>Full Name</label>

        <input type="text"
        name="full_name"
        value="<?php echo htmlspecialchars($teacher['full_name']); ?>"
        required>
    </div>

    <div class="input-group">
        <label>Email</label>

        <input type="email"
        name="email"
        value="<?php echo htmlspecialchars($teacher['email']); ?>"
        required>
    </div>

    <div class="input-group">
        <label>Phone Number</label>

        <input type="text"
        name="phone_no"
        value="<?php echo htmlspecialchars($teacher['phone_no']); ?>">
    </div>

    <div class="input-group">
        <label>Specialization</label>

        <input type="text"
        name="specialization"
        value="<?php echo htmlspecialchars($teacher['specialization']); ?>">
    </div>

    <div class="input-group">
        <label>Assign Class</label>

        <select name="class_id">

            <option value="">Select Class</option>

            <?php
            while($class = mysqli_fetch_assoc($classes)){
            ?>

            <option value="<?php echo $class['class_id']; ?>">

                <?php echo $class['class_name']; ?>

            </option>

            <?php } ?>

        </select>

    </div>

    <div class="input-group">

        <label>Status</label>

        <select name="status">

            <option value="active"
            <?php if($teacher['status']=='active') echo 'selected'; ?>>
                Active
            </option>

            <option value="inactive"
            <?php if($teacher['status']=='inactive') echo 'selected'; ?>>
                Inactive
            </option>

        </select>

    </div>

    <button type="submit" name="update_teacher">

        <i class="fas fa-save"></i>
        Update Teacher

    </button>

</form>

</div>

</body>
</html>