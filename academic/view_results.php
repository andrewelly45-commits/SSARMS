<?php
session_start();
include '../db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

/* ================= AUTH CHECK ================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'academic') {
    header("Location: ../auth/login.php");
    exit();
}

$term = $_GET['term'] ?? '';
$year = $_GET['year'] ?? '';
$class_id = $_GET['class_id'] ?? '';

$where = "WHERE 1=1";

if (!empty($term)) {
    $where .= " AND sr.term='$term'";
}

if (!empty($year)) {
    $where .= " AND sr.academic_year='$year'";
}

if (!empty($class_id)) {
    $where .= " AND sr.class_id='$class_id'";
}

/* ================= MAIN QUERY ================= */
$query = mysqli_query($conn, "
    SELECT
        sr.*,
        u.full_name,
        c.class_name
    FROM student_results sr
    JOIN student s ON sr.student_id = s.student_id
    JOIN users u ON s.user_id = u.user_id
    JOIN class c ON sr.class_id = c.class_id
    $where
    ORDER BY sr.average DESC
");

$class_id = $_GET['class_id'] ?? '';

$where = "WHERE 1=1";


$stats = mysqli_query($conn, "
    SELECT 
        COUNT(*) as total_students,
        SUM(CASE WHEN division='Division I' THEN 1 ELSE 0 END) as div1,
        SUM(CASE WHEN division='Division II' THEN 1 ELSE 0 END) as div2,
        SUM(CASE WHEN division='Division III' THEN 1 ELSE 0 END) as div3,
        SUM(CASE WHEN division='Division IV' THEN 1 ELSE 0 END) as div4,
        SUM(CASE WHEN division='INC' THEN 1 ELSE 0 END) as inc
    FROM student_results
");

$stat = mysqli_fetch_assoc($stats);

?>

<!DOCTYPE html>
<html>
<head>
    <title>School Results</title>

    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>

body{
    font-family:Segoe UI, sans-serif;
    background:#f4f6f9;
    margin:0;
}

.main-content{
    margin-left:270px;
    padding:25px;
}

.card{
    background:#fff;
    border-radius:12px;
    padding:20px;
    box-shadow:0 2px 10px rgba(0,0,0,.08);
}

.inc{
    color:#f59e0b;
    font-weight:bold;
}

.page-title{
    margin-bottom:20px;
    color:#1e293b;
}

.filters{
    display:flex;
    gap:10px;
    margin-bottom:20px;
    flex-wrap:wrap;
}

.filters select,
.filters button{
    padding:10px;
    border:1px solid #ddd;
    border-radius:8px;
}

.filters button{
    background:#f59e0b;
    color:white;
    border:none;
    cursor:pointer;
}

.filters button:hover{
    opacity:.9;
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    background:#1e293b;
    color:white;
    padding:12px;
    text-align:left;
}

td{
    padding:12px;
    border-bottom:1px solid #eee;
}

tr:hover{
    background:#f8fafc;
}

.div1{
    color:green;
    font-weight:bold;
}

.div2{
    color:#2563eb;
    font-weight:bold;
}

.div3{
    color:#f59e0b;
    font-weight:bold;
}

.div4{
    color:#ef4444;
    font-weight:bold;
}

.fail{
    color:red;
    font-weight:bold;
}

</style>

</head>
<body>

<?php include 'academic_sidebar.php'; ?>

<?php include 'academic_topbar.php'; ?>

<div class="main-content">

    <h2 class="page-title">
        <i class="fas fa-chart-line"></i>
        School Results
    </h2>

    <div class="card">

        <form method="GET" class="filters">

            <select name="term">
                <option value="">All Terms</option>
                <option value="Term 1"
                <?= ($term=='Term 1')?'selected':'' ?>>
                Term 1
                </option>

                <option value="Term 2"
                <?= ($term=='Term 2')?'selected':'' ?>>
                Term 2
                </option>
            </select>

            <select name="year">
                <option value="">All Years</option>

                <?php
                $years = mysqli_query($conn,"
                    SELECT DISTINCT academic_year
                    FROM student_results
                    ORDER BY academic_year DESC
                ");

                while($y=mysqli_fetch_assoc($years)){
                ?>
                <option value="<?= $y['academic_year']; ?>"
                <?= ($year==$y['academic_year'])?'selected':'' ?>>
                    <?= $y['academic_year']; ?>
                </option>
                <?php } ?>

            </select>

            <select name="class_id">
    <option value="">All Classes</option>
    <?php
    $cls = mysqli_query($conn, "SELECT * FROM class");
    while($c = mysqli_fetch_assoc($cls)){
    ?>
        <option value="<?= $c['class_id'] ?>"
        <?= ($class_id ?? '') == $c['class_id'] ? 'selected' : '' ?>>
            <?= $c['class_name'] ?>
        </option>
    <?php } ?>
</select>

            <button type="submit">
                <i class="fas fa-search"></i>
                Filter
            </button>

        </form>

        <div style="overflow-x:auto">

        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:15px">

<div class="card">Total: <?= $stat['total_students'] ?></div>
<div class="card">DIV I: <?= $stat['div1'] ?></div>
<div class="card">DIV II: <?= $stat['div2'] ?></div>
<div class="card">DIV III: <?= $stat['div3'] ?></div>
<div class="card">DIV IV: <?= $stat['div4'] ?></div>
<div class="card inc">INC: <?= $stat['inc'] ?></div>

</div>

            <table>
            

                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student Name</th>
                        <th>Class</th>
                        <th>Term</th>
                        <th>Year</th>
                        <th>Total Marks</th>
                        <th>Points</th>
                        <th>Average</th>
                        <th>Division</th>
                    </tr>
                </thead>

                <tbody>

                <?php
                $no = 1;

                if(mysqli_num_rows($query) > 0){

                    while($row = mysqli_fetch_assoc($query)){

                        $divisionClass = '';

                       switch($row['division']){

                        case 'Division I':
                            $divisionClass='div1';
                            break;

                        case 'Division II':
                            $divisionClass='div2';
                            break;

                        case 'Division III':
                            $divisionClass='div3';
                            break;

                        case 'Division IV':
                            $divisionClass='div4';
                            break;

                        case 'INC':
                            $divisionClass='inc';
                            break;

                        default:
                             $divisionClass='fail';
                                }
                ?>

                    <tr>
                        <td><?= $no++; ?></td>

                        <td>
                            <?= htmlspecialchars($row['full_name']); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['class_name']); ?>
                        </td>

                        <td>
                            <?= $row['term']; ?>
                        </td>

                        <td>
                            <?= $row['academic_year']; ?>
                        </td>

                        <td>
                            <?= $row['total_marks']; ?>
                        </td>

                        <td>
                            <?= $row['total_points']; ?>
                        </td>

                        <td>
                            <?= number_format($row['average'],2); ?>%
                        </td>

                        <td class="<?= $divisionClass ?>">
                            <?= $row['division']; ?>
                        </td>
                    </tr>

                <?php
                    }

                } else {
                    echo "
                    <tr>
                        <td colspan='9' style='text-align:center'>
                            No results found
                        </td>
                    </tr>";
                }
                ?>

                </tbody>

            </table>

        </div>

    </div>

</div>
<?php include '../footer.php'; ?>
</body>
</html>