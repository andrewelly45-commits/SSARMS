<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get teacher ID
$teacher_query = mysqli_query($conn, "
    SELECT teacher_id
    FROM teacher
    WHERE user_id = '$user_id'
");

$teacher = mysqli_fetch_assoc($teacher_query);

if (!$teacher) {
    die("Teacher not found");
}

$teacher_id = $teacher['teacher_id'];

// Get classes and subjects
$class_query = mysqli_query($conn, "
    SELECT
        c.class_id,
        c.class_name,
        GROUP_CONCAT(
            DISTINCT s.subject_name
            SEPARATOR ', '
        ) AS subjects
    FROM teacher_class tc
    INNER JOIN class c ON tc.class_id = c.class_id
    LEFT JOIN teacher_subject ts ON c.class_id = ts.class_id AND ts.teacher_id = '$teacher_id'
    LEFT JOIN subject s ON ts.subject_id = s.subject_id
    WHERE tc.teacher_id = '$teacher_id'
    GROUP BY c.class_id, c.class_name
    ORDER BY c.class_name ASC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Classes</title>
    <meta charset="UTF-8">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        
        body {
            background: #f0f2f5;
            color: #333;
        }
        
        .container {
    margin-left: 280px;
    padding: 90px 30px 30px 30px; /* FIX FOR TOPBAR */
}

}
        
        .card {
            background: #ffffff;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        h2 {
            margin-bottom: 25px;
            font-size: 24px;
            color: #2c3e50;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        h2 i {
            color: #34495e;
            font-size: 28px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 12px;
            overflow: hidden;
        }
        
        th {
            background: orange;
            color: black;
            padding: 14px 16px;
            text-align: left;
            font-size: 14px;
            font-weight: 600;
        }
        
        td {
            padding: 14px 16px;
            border-bottom: 1px solid #ecf0f1;
        }
        
        tr:hover td {
            background: #f8f9fa;
        }
        
        .sn-badge {
            background: orange;
            color: black;
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: bold;
            font-size: 14px;
        }
        
        .badge {
            background: white;
            color: black;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 14px;
            margin: 3px;
            display: inline-block;
        }
        
        .badge i {
            margin-right: 5px;
            font-size: 12px;
        }
        
        .btn {
            background: orange;
            color: black;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn:hover {
            background: orange;
            transform: translateY(-2px);
        }
        
        .empty {
            color: #7f8c8d;
            font-size: 13px;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #95a5a6;
        }
        
        .no-data i {
            font-size: 48px;
            margin-bottom: 15px;
            display: block;
        }
        
        @media (max-width: 768px) {
            .container {
                margin-left: 0;
                padding: 15px;
            }
            table {
                display: block;
                overflow-x: auto;
            }
            th, td {
                white-space: nowrap;
            }
        }
    </style>
</head>
<body>

<?php include 'teacher_sidebar.php'; ?>

<?php include '../auth/topbar.php'; ?>

<div class="container">
    <div class="card">
        <h2>
            <i class="fas fa-chalkboard"></i>
            My Classes & Subjects
        </h2>
        
        <table>
            <thead>
                <tr>
                    <th style="width: 60px;">No</th>
                    <th>Class</th>
                    <th>Subjects</th>
                    <th style="width: 140px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($class_query) > 0): ?>
                    <?php $counter = 1; while($row = mysqli_fetch_assoc($class_query)): ?>
                        <tr>
                            <td>
                                <span class="sn-badge"><?php echo $counter++; ?></span>
                            </td>
                            <td>
                                <strong><i class="fas fa-school"></i> <?php echo htmlspecialchars($row['class_name']); ?></strong>
                            </td>
                            <td>
                                <?php if (!empty($row['subjects'])): ?>
                                    <?php 
                                    $subjects = explode(',', $row['subjects']);
                                    foreach ($subjects as $subject): 
                                    ?>
                                        <span class="badge">
                                            <strong><i class="fas fa-book"></i>
                                            <?php echo htmlspecialchars(trim($subject)); ?></strong>
                                        </span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="empty"><i class="fas fa-info-circle"></i> No subjects assigned yet</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a class="btn" href="view_students.php?class_id=<?php echo $row['class_id']; ?>">
                                    <i class="fas fa-users"></i> View Students
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="no-data">
                            <i class="fas fa-folder-open"></i>
                            No classes assigned to you yet
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../footer.php'; ?>
</body>

</html>