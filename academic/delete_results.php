<?php
session_start();
include("../db.php");
include("../auth/audit_functions.php");

/* ==========================================
   ACADEMIC AUTHENTICATION
========================================== */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'academic') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$current_year = date("Y");

/* ==========================================
   GET ACADEMIC USER
========================================== */
$user = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT * FROM users WHERE user_id='$user_id'")
);

if (!$user) {
    die("Academic user not found.");
}

/* ==========================================
   DELETE PROCESS
========================================== */
if (isset($_POST['delete_results'])) {
    $academic_year = mysqli_real_escape_string($conn, $_POST['academic_year']);
    $term          = mysqli_real_escape_string($conn, $_POST['term']);
    $password      = $_POST['password'];

    /* Only current year can be deleted */
    if ($academic_year != $current_year) {
        $_SESSION['error_message'] = "Only current academic year results can be deleted.";
    } elseif (empty($term)) {
        $_SESSION['error_message'] = "Please select the term.";
    } elseif (!password_verify($password, $user['password'])) {
        $_SESSION['error_message'] = "Incorrect password.";
    } else {
        mysqli_begin_transaction($conn);

        try {
            /* Count rows first */
            $count = mysqli_fetch_assoc(
                mysqli_query(
                    $conn,
                    "SELECT COUNT(*) total
                     FROM marks
                     WHERE academic_year='$academic_year'
                     AND term='$term'"
                )
            );

            $deleted = $count['total'];

            mysqli_query(
                $conn,
                "DELETE FROM marks
                 WHERE academic_year='$academic_year'
                 AND term='$term'"
            );

            logSystemAction(
                $_SESSION['user_id'],
                $_SESSION['role'],
                $_SESSION['full_name'],
                'delete',
                "Deleted all results for $academic_year ($term)",
                'marks',
                'marks',
                null,
                [
                    'academic_year' => $academic_year,
                    'term' => $term,
                    'records_deleted' => $deleted
                ],
                null
            );

            mysqli_commit($conn);

            $_SESSION['success_message'] = "$deleted result(s) deleted successfully.";
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $_SESSION['error_message'] = "Deletion failed.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete School Results</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* ==========================================
           GENERAL RESET
        ========================================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background: #f1f5f9;
        }

        /* ==========================================
           CONTAINER
        ========================================== */
        .container {
            margin-left: 270px;
            padding: 100px 30px 30px;
        }

        /* ==========================================
           CARD
        ========================================== */
        .card {
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            max-width: 650px;
        }

        h2 {
            margin-bottom: 25px;
            color: #1e293b;
        }

        /* ==========================================
           FORM ELEMENTS
        ========================================== */
        label {
            display: block;
            margin-top: 18px;
            margin-bottom: 8px;
            font-weight: 600;
            color: #334155;
        }

        input,
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.3s;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        input[readonly] {
            background: #f1f5f9;
            color: #475569;
        }

        /* ==========================================
           WARNING BOX
        ========================================== */
        .warning {
            margin-top: 20px;
            padding: 15px;
            background: #fef2f2;
            border-left: 5px solid #dc2626;
            color: #991b1b;
            border-radius: 8px;
            font-size: 14px;
            line-height: 1.7;
        }

        .warning b {
            font-size: 16px;
        }

        /* ==========================================
           BUTTONS
        ========================================== */
        button {
            margin-top: 25px;
            background: #dc2626;
            color: #fff;
            border: none;
            padding: 14px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        button:hover {
            background: #b91c1c;
        }

        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* ==========================================
           MESSAGES
        ========================================== */
        .success {
            background: #dcfce7;
            color: #166534;
            padding: 12px 16px;
            margin-bottom: 20px;
            border-radius: 8px;
            border-left: 4px solid #22c55e;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px 16px;
            margin-bottom: 20px;
            border-radius: 8px;
            border-left: 4px solid #dc2626;
        }

        /* ==========================================
           MODAL
        ========================================== */
        #deleteModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        #deleteModal .modal-content {
            background: #fff;
            width: 420px;
            max-width: 95%;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            animation: fadeIn 0.3s ease;
        }

        #deleteModal .modal-icon {
            font-size: 60px;
            color: #dc2626;
            margin-bottom: 15px;
        }

        #deleteModal h2 {
            color: #1e293b;
            margin-bottom: 15px;
        }

        #deleteModal p {
            color: #475569;
            line-height: 1.7;
            margin-bottom: 20px;
        }

        #deleteModal .modal-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        #deleteModal .btn-cancel {
            background: #64748b;
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            color: #fff;
            font-weight: 600;
            cursor: pointer;
            font-size: 15px;
            transition: background 0.3s;
        }

        #deleteModal .btn-cancel:hover {
            background: #475569;
        }

        #deleteModal .btn-delete {
            background: #dc2626;
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            color: #fff;
            font-weight: 600;
            cursor: pointer;
            font-size: 15px;
            transition: background 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        #deleteModal .btn-delete:hover {
            background: #b91c1c;
        }

        /* ==========================================
           ANIMATIONS
        ========================================== */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* ==========================================
           RESPONSIVE
        ========================================== */
        @media (max-width: 768px) {
            .container {
                margin-left: 0;
                padding: 90px 15px 15px;
            }

            .card {
                padding: 20px;
            }

            #deleteModal .modal-content {
                padding: 20px;
            }

            #deleteModal .modal-buttons {
                flex-direction: column;
            }

            #deleteModal .modal-buttons button {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body>

    <!-- ==========================================
    SIDEBAR & TOPBAR
    ========================================== -->
    <?php include 'academic_sidebar.php'; ?>
    <?php include '../auth/topbar.php'; ?>

    <!-- ==========================================
    MAIN CONTENT
    ========================================== -->
    <div class="container">
        <div class="card">

            <h2>
                <i class="fas fa-trash-alt"></i>
                Delete School Results
            </h2>

            <!-- ==========================================
            MESSAGES
            ========================================== -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="success">
                    <i class="fas fa-check-circle"></i>
                    <?= $_SESSION['success_message']; ?>
                    <?php unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= $_SESSION['error_message']; ?>
                    <?php unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <!-- ==========================================
            DELETE FORM
            ========================================== -->
            <form method="POST" id="deleteForm">

                <label>
                    <i class="fas fa-calendar"></i>
                    Academic Year
                </label>
                <input type="text" name="academic_year" value="<?= $current_year ?>" readonly>

                <label>
                    <i class="fas fa-list"></i>
                    Select Term
                </label>
                <select name="term" required>
                    <option value="">-- Select Term --</option>
                    <option value="Term 1">Term 1</option>
                    <option value="Term 2">Term 2</option>
                </select>

                <label>
                    <i class="fas fa-lock"></i>
                    Confirm Password
                </label>
                <input type="password" name="password" placeholder="Enter your account password" required>

                <!-- ==========================================
                WARNING
                ========================================== -->
                <div class="warning">
                    <b><i class="fas fa-exclamation-triangle"></i> WARNING!</b>
                    <br><br>
                    Only results for the current academic year
                    <b><?= $current_year ?></b>
                    will be deleted.
                    <br>
                    Results from previous academic years cannot be deleted.
                    <br><br>
                    <strong style="color:#dc2626;">This action is permanent!</strong>
                </div>

                <button type="button" onclick="confirmDelete()">
                    <i class="fas fa-trash"></i>
                    Delete Results
                </button>

                <input type="hidden" name="delete_results" value="1">

            </form>

        </div>
    </div>

    <!-- ==========================================
    DELETE CONFIRMATION MODAL
    ========================================== -->
    <div id="deleteModal">
        <div class="modal-content">
            <div class="modal-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>

            <h2>Delete Results?</h2>

            <p>
                You are about to delete <strong>all results</strong>
                for the selected term in <strong><?= $current_year ?></strong>.
                <br><br>
                <strong style="color:#dc2626;">This action CANNOT be undone.</strong>
            </p>

            <div class="modal-buttons">
                <button type="button" onclick="closeModal()" class="btn-cancel">
                    <i class="fas fa-times"></i>
                    Cancel
                </button>

                <button type="button" id="deleteBtn" onclick="submitDelete()" class="btn-delete">
                    <i class="fas fa-trash"></i>
                    Yes, Delete
                </button>
            </div>
        </div>
    </div>

    <!-- ==========================================
    JAVASCRIPT
    ========================================== -->
    <script>
        function confirmDelete() {
            let term = document.querySelector("[name='term']").value;

            if (term == "") {
                alert("Please select the term.");
                return;
            }

            document.getElementById("deleteModal").style.display = "flex";
        }

        function closeModal() {
            document.getElementById("deleteModal").style.display = "none";
        }

        window.onclick = function(e) {
            let modal = document.getElementById("deleteModal");
            if (e.target == modal) {
                modal.style.display = "none";
            }
        }

        function submitDelete() {
            let btn = document.getElementById("deleteBtn");
            btn.disabled = true;
            btn.innerHTML = "<i class='fas fa-spinner fa-spin'></i> Deleting...";

            document.getElementById("deleteForm").submit();
        }
    </script>

</body>
</html>