<?php
session_start();
include '../db.php';
include '../auth/audit_functions.php'; // Add this line to include the audit functions

error_reporting(E_ALL);
ini_set('display_errors', 1);

/* ================= AUTH CHECK ================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

/* ================= HANDLE FILTERS ================= */
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
$offset = ($page - 1) * $limit;

$filters = [
    'action_type' => isset($_GET['action_type']) ? $_GET['action_type'] : '',
    'module' => isset($_GET['module']) ? $_GET['module'] : '',
    'user_role' => isset($_GET['user_role']) ? $_GET['user_role'] : '',
    'status' => isset($_GET['status']) ? $_GET['status'] : '',
    'date_from' => isset($_GET['date_from']) ? $_GET['date_from'] : '',
    'date_to' => isset($_GET['date_to']) ? $_GET['date_to'] : '',
    'search' => isset($_GET['search']) ? $_GET['search'] : ''
];

// Remove empty filters
$filters = array_filter($filters);

/* ================= GET LOGS ================= */
$logs = getAuditLogs($limit, $offset, $filters);
$total_logs = getAuditLogCount($filters);
$total_pages = ceil($total_logs / $limit);

/* ================= GET SUMMARY ================= */
$summary = getAuditSummary();

/* ================= CLEAN LOGS (Optional) ================= */
if (isset($_GET['clean']) && $_GET['clean'] == 'old') {
    $days = isset($_GET['days']) ? (int)$_GET['days'] : 365;
    if (cleanAuditLogs($days)) {
        $_SESSION['success_msg'] = "Old audit logs (older than $days days) have been cleaned.";
    } else {
        $_SESSION['error_msg'] = "Failed to clean audit logs.";
    }
    header("Location: system_history.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System History & Audit Log</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f1f5f9;
        }

        .main {
            margin-left: 270px;
            margin-top: 85px;
            padding: 30px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .page-header h1 {
            color: #1e293b;
            font-size: 28px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-header h1 i {
            color: #074591;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #1e293b;
        }

        .stat-card .label {
            color: #64748b;
            font-size: 14px;
            margin-top: 5px;
        }

        .stat-card .icon {
            font-size: 28px;
            margin-bottom: 8px;
        }

        /* Cards */
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            overflow: hidden;
        }

        .card-header {
            padding: 18px 24px;
            border-bottom: 1px solid #eef2f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .card-header h2 {
            font-size: 18px;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-body {
            padding: 24px;
        }

        /* Filter Form */
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: flex-end;
            margin-bottom: 20px;
        }

        .filter-group {
            flex: 1;
            min-width: 150px;
        }

        .filter-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 4px;
        }

        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 13px;
            background: white;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #074591;
            box-shadow: 0 0 0 3px rgba(7, 69, 145, 0.1);
        }

        .btn {
            padding: 8px 20px;
            border-radius: 30px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-primary {
            background: #074591;
            color: white;
        }

        .btn-primary:hover {
            background: #05306a;
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #1e293b;
        }

        .btn-secondary:hover {
            background: #cbd5e1;
        }

        .btn-danger {
            background: #dc2626;
            color: white;
        }

        .btn-danger:hover {
            background: #b91c1c;
        }

        .btn-success {
            background: #16a34a;
            color: white;
        }

        .btn-success:hover {
            background: #15803d;
        }

        .btn-warning {
            background: #d97706;
            color: white;
        }

        .btn-warning:hover {
            background: #b45309;
        }

        .btn-sm {
            padding: 4px 12px;
            font-size: 12px;
        }

        /* Table */
        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f8fafc;
            padding: 12px 14px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            border-bottom: 2px solid #e2e8f0;
            white-space: nowrap;
        }

        td {
            padding: 12px 14px;
            border-bottom: 1px solid #eef2f6;
            font-size: 13px;
            vertical-align: middle;
        }

        tr:hover {
            background: #f8fafc;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-success {
            background: #dcfce7;
            color: #166534;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-info {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-admin {
            background: #ede9fe;
            color: #6b21a8;
        }

        .badge-teacher {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-academic {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-student {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-upload {
            background: #e0f2fe;
            color: #0369a1;
        }

        .badge-delete {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-approve {
            background: #dcfce7;
            color: #166534;
        }

        .badge-reject {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-edit {
            background: #f3e8ff;
            color: #6b21a8;
        }

        .badge-login {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-logout {
            background: #fce7f3;
            color: #9d174d;
        }

        .action-type-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .icon-upload {
            background: #e0f2fe;
            color: #0369a1;
        }

        .icon-delete {
            background: #fee2e2;
            color: #991b1b;
        }

        .icon-approve {
            background: #dcfce7;
            color: #166534;
        }

        .icon-reject {
            background: #fef3c7;
            color: #92400e;
        }

        .icon-edit {
            background: #f3e8ff;
            color: #6b21a8;
        }

        .icon-login {
            background: #d1fae5;
            color: #065f46;
        }

        .icon-logout {
            background: #fce7f3;
            color: #9d174d;
        }

        .text-muted {
            color: #94a3b8;
            font-size: 12px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .pagination a, .pagination span {
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            color: #1e293b;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
        }

        .pagination a:hover {
            background: #f1f5f9;
        }

        .pagination .active {
            background: #074591;
            color: white;
            border-color: #074591;
        }

        .pagination .disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Alert Messages */
        .alert {
            padding: 12px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
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

        /* Details Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            padding: 30px;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-header h3 {
            color: #1e293b;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #94a3b8;
        }

        .modal-close:hover {
            color: #1e293b;
        }

        .detail-row {
            display: flex;
            padding: 8px 0;
            border-bottom: 1px solid #eef2f6;
        }

        .detail-label {
            font-weight: 600;
            color: #64748b;
            width: 120px;
            flex-shrink: 0;
        }

        .detail-value {
            color: #1e293b;
            word-break: break-word;
        }

        .json-view {
            background: #f8fafc;
            padding: 10px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 12px;
            overflow-x: auto;
            white-space: pre-wrap;
            max-height: 200px;
            overflow-y: auto;
        }

        @media (max-width: 768px) {
            .main {
                margin-left: 0;
                padding: 15px;
            }

            .filter-form {
                flex-direction: column;
            }

            .filter-group {
                min-width: 100%;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .page-header {
                flex-direction: column;
                align-items: stretch;
            }
        }

        @media print {
            .stats-grid {
                break-inside: avoid;
            }
            .filter-form, .btn, .pagination {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>
<?php include 'admin_topbar.php'; ?>

<div class="main">
    <div class="page-header">
        <h1>
            <i class="fas fa-history"></i>
            System History
        </h1>
        <div>
            <a href="system_history.php?clean=old&days=365" class="btn btn-warning btn-sm" onclick="return confirm('Delete audit logs older than 365 days?')">
                <i class="fas fa-trash"></i> Clean Old Logs
            </a>
            <a href="system_history.php" class="btn btn-secondary btn-sm">
                <i class="fas fa-sync"></i> Refresh
            </a>
        </div>
    </div>

    <!-- Display Messages -->
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

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="icon"><i class="fas fa-list" style="color:#074591;"></i></div>
            <div class="number"><?= number_format($summary['total'] ?? 0) ?></div>
            <div class="label">Total Activities</div>
        </div>
        <div class="stat-card">
            <div class="icon"><i class="fas fa-calendar-day" style="color:#16a34a;"></i></div>
            <div class="number"><?= number_format($summary['today'] ?? 0) ?></div>
            <div class="label">Today</div>
        </div>
        <div class="stat-card">
            <div class="icon"><i class="fas fa-calendar-week" style="color:#d97706;"></i></div>
            <div class="number"><?= number_format($summary['week'] ?? 0) ?></div>
            <div class="label">This Week</div>
        </div>
        <div class="stat-card">
            <div class="icon"><i class="fas fa-users" style="color:#7c3aed;"></i></div>
            <div class="number"><?= number_format($total_logs) ?></div>
            <div class="label">Filtered Results</div>
        </div>
    </div>

    <!-- Top Actions Summary -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 25px;">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-tag"></i> Top Actions</h2>
            </div>
            <div class="card-body">
                <?php if(!empty($summary['top_actions'])): ?>
                    <?php foreach($summary['top_actions'] as $action): ?>
                        <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                            <span><?= ucfirst(str_replace('_', ' ', $action['action_type'])) ?></span>
                            <span class="badge badge-info"><?= $action['count'] ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">No data available</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-cube"></i> Top Modules</h2>
            </div>
            <div class="card-body">
                <?php if(!empty($summary['top_modules'])): ?>
                    <?php foreach($summary['top_modules'] as $module): ?>
                        <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                            <span><?= ucfirst($module['module']) ?></span>
                            <span class="badge badge-info"><?= $module['count'] ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">No data available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-filter"></i> Filters</h2>
            <a href="system_history.php" class="btn btn-secondary btn-sm">Clear Filters</a>
        </div>
        <div class="card-body">
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label>Action Type</label>
                    <select name="action_type">
                        <option value="">All Actions</option>
                        <option value="upload" <?= isset($_GET['action_type']) && $_GET['action_type'] == 'upload' ? 'selected' : '' ?>>Upload</option>
                        <option value="delete" <?= isset($_GET['action_type']) && $_GET['action_type'] == 'delete' ? 'selected' : '' ?>>Delete</option>
                        <option value="approve" <?= isset($_GET['action_type']) && $_GET['action_type'] == 'approve' ? 'selected' : '' ?>>Approve</option>
                        <option value="reject" <?= isset($_GET['action_type']) && $_GET['action_type'] == 'reject' ? 'selected' : '' ?>>Reject</option>
                        <option value="edit" <?= isset($_GET['action_type']) && $_GET['action_type'] == 'edit' ? 'selected' : '' ?>>Edit</option>
                        <option value="login" <?= isset($_GET['action_type']) && $_GET['action_type'] == 'login' ? 'selected' : '' ?>>Login</option>
                        <option value="logout" <?= isset($_GET['action_type']) && $_GET['action_type'] == 'logout' ? 'selected' : '' ?>>Logout</option>
                        <option value="assign" <?= isset($_GET['action_type']) && $_GET['action_type'] == 'assign' ? 'selected' : '' ?>>Assign</option>
                        <option value="remove" <?= isset($_GET['action_type']) && $_GET['action_type'] == 'remove' ? 'selected' : '' ?>>Remove</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Module</label>
                    <select name="module">
                        <option value="">All Modules</option>
                        <option value="marks" <?= isset($_GET['module']) && $_GET['module'] == 'marks' ? 'selected' : '' ?>>Marks</option>
                        <option value="results" <?= isset($_GET['module']) && $_GET['module'] == 'results' ? 'selected' : '' ?>>Results</option>
                        <option value="teachers" <?= isset($_GET['module']) && $_GET['module'] == 'teachers' ? 'selected' : '' ?>>Teachers</option>
                        <option value="students" <?= isset($_GET['module']) && $_GET['module'] == 'students' ? 'selected' : '' ?>>Students</option>
                        <option value="classes" <?= isset($_GET['module']) && $_GET['module'] == 'classes' ? 'selected' : '' ?>>Classes</option>
                        <option value="subjects" <?= isset($_GET['module']) && $_GET['module'] == 'subjects' ? 'selected' : '' ?>>Subjects</option>
                        <option value="auth" <?= isset($_GET['module']) && $_GET['module'] == 'auth' ? 'selected' : '' ?>>Authentication</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>User Role</label>
                    <select name="user_role">
                        <option value="">All Roles</option>
                        <option value="admin" <?= isset($_GET['user_role']) && $_GET['user_role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="teacher" <?= isset($_GET['user_role']) && $_GET['user_role'] == 'teacher' ? 'selected' : '' ?>>Teacher</option>
                        <option value="academic" <?= isset($_GET['user_role']) && $_GET['user_role'] == 'academic' ? 'selected' : '' ?>>Academic</option>
                        <option value="student" <?= isset($_GET['user_role']) && $_GET['user_role'] == 'student' ? 'selected' : '' ?>>Student</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="success" <?= isset($_GET['status']) && $_GET['status'] == 'success' ? 'selected' : '' ?>>Success</option>
                        <option value="failed" <?= isset($_GET['status']) && $_GET['status'] == 'failed' ? 'selected' : '' ?>>Failed</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Date From</label>
                    <input type="date" name="date_from" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
                </div>

                <div class="filter-group">
                    <label>Date To</label>
                    <input type="date" name="date_to" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
                </div>

                <div class="filter-group" style="flex: 2;">
                    <label>Search</label>
                    <input type="text" name="search" placeholder="Search by user, description..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                </div>

                <div class="filter-group" style="flex: 0 0 auto;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card">
        <div class="card-header">
            <h2>
                <i class="fas fa-list-ul"></i>
                Activity Log
                <span class="badge badge-info"><?= number_format($total_logs) ?> records</span>
            </h2>
            <div>
                <span class="text-muted">Showing <?= min($limit, $total_logs - $offset) ?> of <?= number_format($total_logs) ?></span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Module</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Date/Time</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($logs && mysqli_num_rows($logs) > 0): ?>
                            <?php 
                            $counter = $offset + 1;
                            while($log = mysqli_fetch_assoc($logs)): 
                                $icon_class = '';
                                $icon_icon = '';
                                $role_badge = 'badge-info';
                                
                                switch($log['action_type']) {
                                    case 'upload':
                                        $icon_class = 'icon-upload';
                                        $icon_icon = 'fa-upload';
                                        break;
                                    case 'delete':
                                        $icon_class = 'icon-delete';
                                        $icon_icon = 'fa-trash';
                                        break;
                                    case 'approve':
                                        $icon_class = 'icon-approve';
                                        $icon_icon = 'fa-check';
                                        break;
                                    case 'reject':
                                        $icon_class = 'icon-reject';
                                        $icon_icon = 'fa-times';
                                        break;
                                    case 'edit':
                                        $icon_class = 'icon-edit';
                                        $icon_icon = 'fa-edit';
                                        break;
                                    case 'login':
                                        $icon_class = 'icon-login';
                                        $icon_icon = 'fa-sign-in-alt';
                                        break;
                                    case 'logout':
                                        $icon_class = 'icon-logout';
                                        $icon_icon = 'fa-sign-out-alt';
                                        break;
                                    default:
                                        $icon_class = 'icon-edit';
                                        $icon_icon = 'fa-circle';
                                }
                                
                                switch($log['user_role']) {
                                    case 'admin':
                                        $role_badge = 'badge-admin';
                                        break;
                                    case 'teacher':
                                        $role_badge = 'badge-teacher';
                                        break;
                                    case 'academic':
                                        $role_badge = 'badge-academic';
                                        break;
                                    case 'student':
                                        $role_badge = 'badge-student';
                                        break;
                                }
                            ?>
                                <tr>
                                    <td><?= $counter++ ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($log['user_name']) ?></strong>
                                        <br>
                                        <span class="badge <?= $role_badge ?>"><?= ucfirst($log['user_role']) ?></span>
                                    </td>
                                    <td>
                                        <span class="action-type-icon <?= $icon_class ?>">
                                            <i class="fas <?= $icon_icon ?>"></i>
                                        </span>
                                        <span style="font-weight: 500;"><?= ucfirst(str_replace('_', ' ', $log['action_type'])) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge badge-info"><?= ucfirst($log['module']) ?></span>
                                    </td>
                                    <td style="max-width: 250px;">
                                        <span title="<?= htmlspecialchars($log['action_description']) ?>">
                                            <?= htmlspecialchars(substr($log['action_description'], 0, 60)) ?>
                                            <?= strlen($log['action_description']) > 60 ? '...' : '' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $log['status'] == 'success' ? 'badge-success' : 'badge-danger' ?>">
                                            <i class="fas <?= $log['status'] == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                                            <?= ucfirst($log['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= date('d M Y', strtotime($log['created_at'])) ?>
                                        <br>
                                        <span class="text-muted"><?= date('H:i:s', strtotime($log['created_at'])) ?></span>
                                    </td>
                                    <td>
                                        <button class="btn btn-primary btn-sm" onclick="viewDetails(<?= htmlspecialchars(json_encode($log)) ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 40px;">
                                    <i class="fas fa-inbox" style="font-size: 40px; color: #cbd5e1; display: block; margin-bottom: 10px;"></i>
                                    <p style="color: #94a3b8;">No activity logs found</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
                <div class="pagination">
                    <?php if($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>&<?= http_build_query(array_diff_key($_GET, ['page' => ''])) ?>">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>

                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if($i == $page): ?>
                            <span class="active"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?page=<?= $i ?>&<?= http_build_query(array_diff_key($_GET, ['page' => ''])) ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?>&<?= http_build_query(array_diff_key($_GET, ['page' => ''])) ?>">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div id="detailsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-info-circle"></i> Activity Details</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div id="modalBody">
            <!-- Dynamically populated -->
        </div>
    </div>
</div>

<script>
function viewDetails(log) {
    const modal = document.getElementById('detailsModal');
    const body = document.getElementById('modalBody');
    
    let html = `
        <div class="detail-row">
            <span class="detail-label">ID</span>
            <span class="detail-value">#${log.audit_id}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">User</span>
            <span class="detail-value">${log.user_name} <span class="badge badge-info">${log.user_role}</span></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Action</span>
            <span class="detail-value">${log.action_type.replace(/_/g, ' ').toUpperCase()}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Module</span>
            <span class="detail-value">${log.module}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Description</span>
            <span class="detail-value">${log.action_description}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Status</span>
            <span class="detail-value"><span class="badge ${log.status == 'success' ? 'badge-success' : 'badge-danger'}">${log.status}</span></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Date/Time</span>
            <span class="detail-value">${new Date(log.created_at).toLocaleString()}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">IP Address</span>
            <span class="detail-value">${log.ip_address || 'N/A'}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">User Agent</span>
            <span class="detail-value" style="font-size: 12px;">${log.user_agent || 'N/A'}</span>
        </div>
    `;
    
    // Show affected table and ID if present
    if (log.affected_table) {
        html += `
            <div class="detail-row">
                <span class="detail-label">Affected Table</span>
                <span class="detail-value">${log.affected_table}</span>
            </div>
        `;
    }
    if (log.affected_id) {
        html += `
            <div class="detail-row">
                <span class="detail-label">Affected ID</span>
                <span class="detail-value">${log.affected_id}</span>
            </div>
        `;
    }
    
    // Show old values if present
    if (log.old_values && log.old_values != 'null') {
        try {
            const oldData = JSON.parse(log.old_values);
            html += `
                <div class="detail-row">
                    <span class="detail-label">Old Values</span>
                    <span class="detail-value"><div class="json-view">${JSON.stringify(oldData, null, 2)}</div></span>
                </div>
            `;
        } catch(e) {}
    }
    
    // Show new values if present
    if (log.new_values && log.new_values != 'null') {
        try {
            const newData = JSON.parse(log.new_values);
            html += `
                <div class="detail-row">
                    <span class="detail-label">New Values</span>
                    <span class="detail-value"><div class="json-view">${JSON.stringify(newData, null, 2)}</div></span>
                </div>
            `;
        } catch(e) {}
    }
    
    body.innerHTML = html;
    modal.style.display = 'flex';
}

function closeModal() {
    document.getElementById('detailsModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('detailsModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}

// Auto-hide alerts
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