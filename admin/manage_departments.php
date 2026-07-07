<?php
session_start();
include '../db.php';

if(!isset($_SESSION['role']) || $_SESSION['role']!='admin'){
    header("Location: ../auth/login.php");
    exit();
}

// ADD DEPARTMENT
if(isset($_POST['add_department'])){
    $name = mysqli_real_escape_string($conn, $_POST['department_name']);
    
    $check=mysqli_query($conn, "SELECT * FROM department WHERE department_name='$name'");
    
    if(mysqli_num_rows($check)>0){
        $_SESSION['error']="Department already exists!";
    }else{
        mysqli_query($conn, "INSERT INTO department(department_name) VALUES('$name')");
        $_SESSION['success']="Department added successfully!";
    }
    
    header("Location: manage_departments.php");
    exit();
}

// UPDATE DEPARTMENT
if(isset($_POST['update_department'])){
    $id=$_POST['department_id'];
    $name=mysqli_real_escape_string($conn, $_POST['department_name']);
    
    mysqli_query($conn, "UPDATE department SET department_name='$name' WHERE department_id='$id'");
    $_SESSION['success']="Department updated successfully!";
    
    header("Location: manage_departments.php");
    exit();
}

// DELETE DEPARTMENT
if(isset($_GET['delete'])){
    $id=$_GET['delete'];
    
    mysqli_query($conn, "DELETE FROM department WHERE department_id='$id'");
    $_SESSION['success']="Department deleted successfully!";
    
    header("Location: manage_departments.php");
    exit();
}

// FETCH DEPARTMENTS
$departments=mysqli_query($conn, "SELECT * FROM department ORDER BY department_name ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Departments</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
        }

        /* Main layout */
        .main-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 80px 20px 20px 20px;
            transition: all 0.3s ease;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .header-section h2 {
            color: #1a2332;
            font-size: 24px;
            font-weight: 600;
        }

        .department-count {
            background: #e2e8f0;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            color: #1a2332;
            font-weight: 500;
        }

        .add-department-form {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border: 1px solid #e2e8f0;
        }

        .add-department-form form {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }

        .add-department-form input {
            flex: 1;
            min-width: 250px;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .add-department-form input:focus {
            outline: none;
            border-color: #2563eb;
        }

        .btn {
            padding: 12px 28px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-success {
            background: #16a34a;
            color: white;
        }

        .btn-success:hover {
            background: #15803d;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
        }

        .btn-danger {
            background: #dc2626;
            color: white;
            padding: 6px 16px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
            font-size: 13px;
            transition: all 0.3s;
            border: none;
        }

        .btn-danger:hover {
            background: #b91c1c;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        }

        .btn-edit {
            background: #2563eb;
            color: white;
            padding: 6px 16px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
            font-size: 13px;
            transition: all 0.3s;
        }

        .btn-edit:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .table-wrapper {
            overflow-x: auto;
            margin-top: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 500px;
        }

        th {
            background: #1a2332;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }

        tr:hover {
            background: #f8fafc;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
        }

        .empty-state .empty-icon {
            font-size: 64px;
            display: block;
            margin-bottom: 15px;
            color: #cbd5e1;
        }

        .empty-state h3 {
            color: #1a2332;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #64748b;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .main-content {
                margin-left: 0;
                padding: 80px 15px 15px 15px;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 70px 10px 10px 10px;
            }

            .container {
                padding: 20px;
            }

            .header-section {
                flex-direction: column;
                align-items: flex-start;
            }

            .add-department-form form {
                flex-direction: column;
                align-items: stretch;
            }

            .add-department-form input {
                min-width: unset;
                width: 100%;
            }

            .add-department-form .btn {
                width: 100%;
            }

            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }

            .btn-edit, .btn-danger {
                text-align: center;
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 15px;
            }

            table {
                font-size: 12px;
            }

            th, td {
                padding: 10px 8px;
            }

            .header-section h2 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>

<div class="main-wrapper">
    <?php include 'admin_sidebar.php'; ?>
    <?php include '../auth/topbar.php'; ?>

    <div class="main-content">
        <div class="container">
            <div class="header-section">
                <h2>Manage Departments</h2>
                <span class="department-count">Total: <?= mysqli_num_rows($departments) ?> Departments</span>
            </div>

            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?= $_SESSION['success'] ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?= $_SESSION['error'] ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="add-department-form">
                <form method="POST">
                    <input 
                        type="text"
                        name="department_name"
                        placeholder="Enter department name..."
                        required
                    >
                    <button type="submit" name="add_department" class="btn btn-primary">
                        Add Department
                    </button>
                </form>
            </div>

            <div class="table-wrapper">
                <?php if(mysqli_num_rows($departments) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Department Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $i = 1;
                            while($row = mysqli_fetch_assoc($departments)): 
                            ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($row['department_name']) ?></strong>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="edit_department.php?id=<?= $row['department_id'] ?>" 
                                           class="btn-edit">
                                            Edit
                                        </a>
                                        <a class="btn-danger"
                                           onclick="return confirm('Are you sure you want to delete this department? This action cannot be undone.')"
                                           href="?delete=<?= $row['department_id'] ?>">
                                            Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">📋</div>
                        <h3>No Departments Found</h3>
                        <p>Add your first department using the form above.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
</body>
</html>