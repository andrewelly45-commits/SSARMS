<?php
session_start();
include '../db.php';


// CHECK ADMIN
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}



// ================= HANDLE AJAX & FORM REQUESTS =================
// ADD CLASS (AJAX or normal POST)
if (isset($_POST['add_class'])) {
   $class_name = mysqli_real_escape_string($conn, $_POST['class_name']);
   $stream = mysqli_real_escape_string($conn, $_POST['stream']);
   $reg_prefix = mysqli_real_escape_string($conn, $_POST['reg_prefix']);
   $level = intval($_POST['level']);

   $result = mysqli_query($conn, "INSERT INTO class
   (class_name, stream, reg_prefix, level)
   VALUES ('$class_name','$stream','$reg_prefix','$level')");

    // If AJAX request, return JSON response
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        if ($result) {
            $new_id = mysqli_insert_id($conn);
            echo json_encode([
                'success' => true, 
                'class_id' => $new_id, 
                'class_name' => $class_name, 
                'stream' => $stream,
                'reg_prefix' => $reg_prefix,
                'level' => $level
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }
        exit();
    }
}

// EDIT CLASS (AJAX)
if (isset($_POST['edit_class'])) {
    $class_id = intval($_POST['class_id']);
    $class_name = mysqli_real_escape_string($conn, $_POST['class_name']);
    $stream = mysqli_real_escape_string($conn, $_POST['stream']);
    $reg_prefix = mysqli_real_escape_string($conn, $_POST['reg_prefix']);
    $level = intval($_POST['level']);

    $result = mysqli_query($conn, "UPDATE class SET 
        class_name = '$class_name',
        stream = '$stream',
        reg_prefix = '$reg_prefix',
        level = '$level'
        WHERE class_id = '$class_id'");

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        if ($result) {
            echo json_encode([
                'success' => true,
                'class_id' => $class_id,
                'class_name' => $class_name,
                'stream' => $stream,
                'reg_prefix' => $reg_prefix,
                'level' => $level
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }
        exit();
    }
}

// DELETE CLASS (AJAX or normal GET)
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $result = mysqli_query($conn, "DELETE FROM class WHERE class_id='$id'");
    
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        if ($result && mysqli_affected_rows($conn) > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Class not found']);
        }
        exit();
    }
    
    header("Location: manage_classes.php");
    exit();
}

// ================= FETCH CLASSES =================
$classes = mysqli_query($conn, "SELECT * FROM class ORDER BY class_id DESC");
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>SSARMS - Manage Classes</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            background:#f1f5f9;
            font-family:'Inter',sans-serif;
            overflow-x:auto;
            min-width:1000px;
        }

        .container{
            margin-left:270px;
            margin-top:92px;
            padding:0 32px 48px 32px;
            transition:margin-left 0.3s ease;
            max-width:1400px;
        }

        .header{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:32px;
            flex-wrap:wrap;
            gap:16px;
        }

        .header h1{
            font-size:28px;
            font-weight:800;
            background:linear-gradient(135deg,#1e2a5e,#2c3e66);
            -webkit-background-clip:text;
            background-clip:text;
            color:transparent;
            display:flex;
            align-items:center;
            gap:12px;
        }

        .header h1 i{
            background:none;
            color: black
        }

        .stats{
            background:white;
            padding:8px 22px;
            border-radius:48px;font-size:14px;
            font-weight:600;
            color:#1e293b;
            border:1px solid #e2e8f0;
        }

        .card{
            background:white;
            border-radius:28px;
            border:1px solid #f0f2f9;
            margin-bottom:32px;
            box-shadow:0 12px 28px -12px rgba(0,0,0,0.06);
        }

        .card-header{
            padding:20px 28px;
            border-bottom:1px solid #eef2ff;
            display:flex;
            align-items:center;
            gap:12px;
        }

        .card-header i{
            font-size:20px;
            color: black;
        }

        .card-header h2{
            font-size:18px;
            font-weight:700;
            color:#0f172a;
        }

        .card-body{
            padding:24px 28px;
        }

        .form-group{
            display:flex;
            flex-wrap:wrap;
            gap:20px;
            align-items:flex-end;
        }

        .input-wrapper{
            flex:1;min-width:220px;
        }

        .input-wrapper label{
            display:block;
            font-size:12px;
            font-weight:700;
            text-transform:uppercase;
            letter-spacing:0.5px;
            color:#64748b;
            margin-bottom:6px;
        }

        .input-wrapper label i{
            margin-right:6px;
        }

        .input-wrapper input{
            width:100%;
            padding:11px 16px;
            border:1.5px solid #e2e8f0;
            border-radius:44px;
            font-size:14px;
            background:white;
        }

        .input-wrapper input:focus{
            border-color:#3b82f6;
            outline:none;
            box-shadow:0 0 0 3px rgba(59,130,246,0.12);
        }

        .btn-primary{
            background: blue;
            border:none;
            padding:11px 28px;
            border-radius:44px;
            font-weight:600;
            font-size:14px;
            color:white;
            cursor:pointer;
            display:inline-flex;
            align-items:center;
            gap:8px;
        }

        .btn-primary:hover{
            background:#1d4ed8;
        }

        .table-wrapper{
            overflow-x:auto;
        }

        table{
            width:100%;
            border-collapse:collapse;
            min-width:500px;
        }

        th{
            text-align:left;
            padding:14px 16px;
            background:#f8fafc;
            font-weight:700;
            font-size:13px;
            color:#475569;
            border-bottom:1px solid #e2e8f0;
        }

        td{
            padding:14px 16px;
            border-bottom:1px solid #f1f5f9;
            color:#1e293b;
            font-size:14px;
        }

        tr:hover td{
            background:#fefce8;
        }

        .class-badge{
            padding:5px 14px;
            border-radius:40px;
            font-size:13px;
            font-weight:600;
            color: black;
            display:inline-block;
        }

        .stream-text{
            background:#f1f5f9;
            padding:4px 12px;
            border-radius:20px;
            font-size:12px;
            font-weight:500;
            display:inline-block;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        .edit-btn{
            background:rgba(59,130,246,0.08);
            border:none;
            color:#2563eb;
            cursor:pointer;
            font-size:13px;
            font-weight:600;
            display:inline-flex;
            align-items:center;
            gap:6px;
            padding:7px 16px;
            border-radius:40px;
        }

        .edit-btn:hover{
            background:#dbeafe;
        }

        .delete-btn{
            background:rgba(239,68,68,0.08);
            border:none;
            color:#dc2626;
            cursor:pointer;
            font-size:13px;
            font-weight:600;
            display:inline-flex;
            align-items:center;
            gap:6px;
            padding:7px 16px;
            border-radius:40px;
        }

        .delete-btn:hover{
            background:#fee2e2;
        }

        .empty-row td{
            text-align:center;
            padding:48px;
            color:#94a3b8;
        }

        .empty-row i{
            font-size:40px;
            margin-bottom:12px;
            display:block;
        }

        .toast{
            position:fixed;
            bottom:28px;
            right:28px;
            background:#1e293b;
            color:white;
            padding:12px 24px;
            border-radius:60px;
            font-size:14px;
            z-index:1200;
            display:flex;
            align-items:center;
            gap:10px;
        }

        .toast.success{
            background:#10b981;
        }

        .toast.error{
            background:#ef4444;
        }

        .modal-overlay{
            position:fixed;
            top:0;
            left:0;
            width:100%;
            height:100%;
            background:rgba(0,0,0,0.5);
            backdrop-filter:blur(4px);
            display:flex;
            align-items:center;
            justify-content:center;
            z-index:2000;
        }

        .modal{
            background:white;
            border-radius:32px;
            padding:28px 32px;
            max-width:450px;
            width:90%;
        }

        .modal i{
            font-size:52px;
            color:#ef4444;
            margin-bottom:16px;
            display:block;
            text-align:center;
        }

        .modal h3{
            font-size:22px;
            margin-bottom:10px;
            color:#0f172a;
            text-align:center;
        }

        .modal p{
            color:#475569;
            margin-bottom:28px;
            text-align:center;
        }

        .modal-buttons{
            display:flex;
            gap:12px;
            justify-content:center;
        }

        .modal-btn{
            padding:10px 24px;
            border-radius:40px;
            font-weight:600;
            cursor:pointer;
            border:none;
            font-size:14px;
        }

        .modal-btn.cancel{
            background:#e2e8f0;
            color:#475569;
        }

        .modal-btn.confirm{
            background:#ef4444;
            color:white;
        }

        .modal-btn.confirm-edit{
            background:#2563eb;
            color:white;
        }

        .input-wrapper select{
            width:100%;
            padding:11px 16px;
            border:1.5px solid #e2e8f0;
            border-radius:44px;
            font-size:14px;
            background:white;
        }

        .input-wrapper select:focus{
            border-color:#3b82f6;
            outline:none;
            box-shadow:0 0 0 3px rgba(59,130,246,0.12);
        }

        .edit-modal .modal i {
            color: #2563eb;
        }

        .edit-modal .form-group {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin: 20px 0;
        }

        .edit-modal .input-wrapper {
            width: 100%;
        }

        .edit-modal .input-wrapper label {
            text-transform: none;
            font-weight: 600;
            color: #1e293b;
            font-size: 13px;
        }

        .edit-modal .input-wrapper input,
        .edit-modal .input-wrapper select {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
        }

        .edit-modal .input-wrapper input:focus,
        .edit-modal .input-wrapper select:focus {
            border-color: #3b82f6;
            outline: none;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
        }
        
        @media (max-width:768px) {
            body{min-width:auto;}
            .admin-topbar,.container{
                left:0!important;
                margin-left:0!important;
            }
            .container{
                margin-top:85px;
                padding:0 18px 30px;
                min-width:auto;
            }
            .card-header,.card-body{
                padding:16px 18px;
            }
            .form-group{
                flex-direction:column;
            }
            .input-wrapper{
                width:100%;
            }
            .btn-primary{
                width:100%;
                justify-content:center;
            }
            .action-buttons {
                flex-direction: column;
                gap: 4px;
            }
            .edit-btn, .delete-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>
<?php include '../auth/topbar.php'; ?>

<div class="container">
    <div class="header">
        <h1><i class="fas fa-chalkboard"></i> Manage Classes</h1>
        <div class="stats"><i class="fas fa-layer-group"></i> <span id="classCount">0</span> classes</div>
    </div>

    <!-- Add Class Card -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-plus-circle"></i>
            <h2>Add New Class</h2>
        </div>
        <div class="card-body">
            <form id="addClassForm">
                <div class="form-group">
                    <div class="input-wrapper">
                        <label><i class="fas fa-school"></i> Class Name <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="class_name" id="className" placeholder="e.g., Form 1, Grade 10" required>
                    </div>
                    <div class="input-wrapper">
                        <label><i class="fas fa-code-branch"></i> Stream (Optional)</label>
                        <input type="text" name="stream" id="stream" placeholder="e.g., A, B, Science">
                    </div>
                    <div class="input-wrapper">
                        <label><i class="fas fa-id-card"></i> Registration Prefix <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="reg_prefix" id="regPrefix" maxlength="2" placeholder="11" required>
                   </div>
                   <div class="input-wrapper">
                       <label><i class="fas fa-layer-group"></i> Level <span style="color:#ef4444;">*</span></label>
                       <select name="level" id="level" required>
                           <option value="">Select Level</option>
                           <option value="1">Form One</option>
                           <option value="2">Form Two</option>
                           <option value="3">Form Three</option>
                           <option value="4">Form Four</option>
                       </select>
                   </div>
                    <div class="input-wrapper">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Add Class</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Classes List Card -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-table-list"></i>
            <h2>Class Directory</h2>
        </div>
        <div class="card-body">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                           <th>No</th>
                           <th>Class Name</th>
                           <th>Stream</th>
                           <th>Reg Prefix</th>
                           <th>Level</th>
                           <th>Action</th>
                       </tr>
                    </thead>
                    <tbody id="tableBody">
                         <?php if (isset($classes) && mysqli_num_rows($classes) > 0): ?>
                         <?php $no = 1; ?>
                         <?php while ($row = mysqli_fetch_assoc($classes)): ?>
                         <tr data-id="<?= $row['class_id'] ?>">
                         <td><?= $no++ ?></td>
                         <td>
                           <span class="class-badge">
                             <?= htmlspecialchars($row['class_name']) ?>
                           </span>
                         </td>
                         <td>
                          <?= !empty($row['stream']) 
                          ? '<span class="stream-text">' . htmlspecialchars($row['stream']) . '</span>' 
                          : '<span style="color:#94a3b8;">—</span>' ?>
                          </td>
                          <td><?= htmlspecialchars($row['reg_prefix']) ?></td>
                          <td>
                             <?php switch($row['level']){
                             case 1: echo "Form One"; break;
                             case 2: echo "Form Two"; break;
                             case 3: echo "Form Three"; break;
                             case 4: echo "Form Four"; break;
                             }?>
                          </td>
                         <td>
                            <div class="action-buttons">
                                <button class="edit-btn" 
                                    data-id="<?= $row['class_id'] ?>"
                                    data-name="<?= htmlspecialchars($row['class_name']) ?>"
                                    data-stream="<?= htmlspecialchars($row['stream']) ?>"
                                    data-prefix="<?= htmlspecialchars($row['reg_prefix']) ?>"
                                    data-level="<?= $row['level'] ?>">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="delete-btn"
                                    data-id="<?= $row['class_id'] ?>"
                                    data-name="<?= htmlspecialchars($row['class_name'] . ($row['stream'] ? ' ' . $row['stream'] : '')) ?>">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                            </div>
                         </td>
                         </tr>
                            <?php endwhile; ?>
                              <?php else: ?>
                            <tr class="empty-row">
                            <td colspan="6">
                           <i class="fas fa-door-open"></i> No classes yet. Add your first class above.
                           </td>
                         </tr>
                           <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// ============ GLOBAL HELPER FUNCTIONS ============
window.showToast = function(msg, type) {
    let t = document.createElement('div');
    t.className = 'toast ' + (type === 'success' ? 'success' : type === 'error' ? 'error' : '');
    t.innerHTML = (type === 'success' ? '<i class="fas fa-check-circle"></i>' : 
                   type === 'error' ? '<i class="fas fa-exclamation-circle"></i>' : 
                   '<i class="fas fa-info-circle"></i>') + ' ' + msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 2500);
};

window.escapeHtml = function(s) {
    if (!s) return '';
    return s.replace(/[&<>]/g, m => m === '&' ? '&amp;' : m === '<' ? '&lt;' : '&gt;');
};

window.getLevelName = function(level) {
    return {1:'Form One',2:'Form Two',3:'Form Three',4:'Form Four'}[level] || level;
};

window.updateCount = function() {
    let r = document.querySelectorAll('#tableBody tr:not(.empty-row)');
    document.getElementById('classCount').innerText = r.length;
};

// ============ DELETE CLASS ============
window.deleteClass = function(id, name) {
    let overlay = document.createElement('div');
    overlay.className = 'modal-overlay';
    
    let modal = document.createElement('div');
    modal.className = 'modal';
    modal.innerHTML = `
        <i class="fas fa-trash-alt"></i>
        <h3>Delete Class?</h3>
        <p>Delete "<strong>${escapeHtml(name)}</strong>"? This cannot be undone.</p>
        <div class="modal-buttons">
            <button class="modal-btn cancel">Cancel</button>
            <button class="modal-btn confirm">Delete</button>
        </div>
    `;
    
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
    
    modal.querySelector('.cancel').onclick = () => overlay.remove();
    overlay.onclick = (e) => { if (e.target === overlay) overlay.remove(); };
    
    modal.querySelector('.confirm').onclick = function() {
        overlay.remove();
        fetch(window.location.pathname + '?delete=' + id, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                let row = document.querySelector(`tr[data-id="${id}"]`);
                if (row) row.remove();
                
                if (!document.querySelector('#tableBody tr:not(.empty-row)')) {
                    document.getElementById('tableBody').innerHTML = 
                        '<tr class="empty-row"><td colspan="6"><i class="fas fa-door-open"></i> No classes yet.</td></tr>';
                }
                
                // Re-number rows
                document.querySelectorAll('#tableBody tr:not(.empty-row)').forEach((tr, i) => {
                    tr.querySelector('td:first-child').innerText = i + 1;
                });
                
                updateCount();
                showToast('Class deleted', 'success');
            } else {
                showToast('Delete failed', 'error');
            }
        })
        .catch(() => showToast('Network error', 'error'));
    };
};

// ============ EDIT CLASS ============
window.editClass = function(id, name, stream, prefix, level) {
    let overlay = document.createElement('div');
    overlay.className = 'modal-overlay edit-modal';
    
    let modal = document.createElement('div');
    modal.className = 'modal';
    modal.innerHTML = `
        <i class="fas fa-edit"></i>
        <h3>Edit Class</h3>
        <div class="form-group">
            <div class="input-wrapper">
                <label>Class Name *</label>
                <input type="text" id="editName" value="${escapeHtml(name)}">
            </div>
            <div class="input-wrapper">
                <label>Stream (Optional)</label>
                <input type="text" id="editStream" value="${escapeHtml(stream)}">
            </div>
            <div class="input-wrapper">
                <label>Registration Prefix *</label>
                <input type="text" id="editPrefix" maxlength="2" value="${escapeHtml(prefix)}">
            </div>
            <div class="input-wrapper">
                <label>Level *</label>
                <select id="editLevel">
                    <option value="1" ${level==1?'selected':''}>Form One</option>
                    <option value="2" ${level==2?'selected':''}>Form Two</option>
                    <option value="3" ${level==3?'selected':''}>Form Three</option>
                    <option value="4" ${level==4?'selected':''}>Form Four</option>
                </select>
            </div>
        </div>
        <div class="modal-buttons">
            <button class="modal-btn cancel">Cancel</button>
            <button class="modal-btn confirm-edit">Update</button>
        </div>
    `;
    
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
    
    modal.querySelector('.cancel').onclick = () => overlay.remove();
    overlay.onclick = (e) => { if (e.target === overlay) overlay.remove(); };
    
    modal.querySelector('.confirm-edit').onclick = function() {
        let newName = document.getElementById('editName').value.trim();
        let newStream = document.getElementById('editStream').value.trim();
        let newPrefix = document.getElementById('editPrefix').value.trim();
        let newLevel = document.getElementById('editLevel').value;
        
        if (!newName || !newPrefix || !newLevel) {
            showToast('Please fill all required fields', 'error');
            return;
        }
        
        let fd = new FormData();
        fd.append('edit_class', '1');
        fd.append('class_id', id);
        fd.append('class_name', newName);
        fd.append('stream', newStream);
        fd.append('reg_prefix', newPrefix);
        fd.append('level', newLevel);
        
        fetch(window.location.pathname, {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                let row = document.querySelector(`tr[data-id="${id}"]`);
                if (row) {
                    row.querySelector('td:nth-child(2)').innerHTML = `<span class="class-badge">${escapeHtml(d.class_name)}</span>`;
                    row.querySelector('td:nth-child(3)').innerHTML = d.stream ? 
                        '<span class="stream-text">'+escapeHtml(d.stream)+'</span>' : 
                        '<span style="color:#94a3b8;">—</span>';
                    row.querySelector('td:nth-child(4)').innerHTML = escapeHtml(d.reg_prefix);
                    row.querySelector('td:nth-child(5)').innerHTML = getLevelName(d.level);
                    
                    // Update data attributes on buttons
                    let editBtn = row.querySelector('.edit-btn');
                    if (editBtn) {
                        editBtn.dataset.name = d.class_name;
                        editBtn.dataset.stream = d.stream;
                        editBtn.dataset.prefix = d.reg_prefix;
                        editBtn.dataset.level = d.level;
                    }
                    
                    let deleteBtn = row.querySelector('.delete-btn');
                    if (deleteBtn) {
                        deleteBtn.dataset.name = d.class_name + (d.stream ? ' ' + d.stream : '');
                    }
                }
                overlay.remove();
                showToast('Class updated', 'success');
            } else {
                showToast('Update failed', 'error');
            }
        })
        .catch(() => showToast('Network error', 'error'));
    };
};

// ============ ADD CLASS ============
document.getElementById('addClassForm').onsubmit = function(e) {
    e.preventDefault();
    
    let cn = document.getElementById('className').value.trim();
    let st = document.getElementById('stream').value.trim();
    let rp = document.getElementById('regPrefix').value.trim();
    let lv = document.getElementById('level').value;
    
    if (!cn || !rp || !lv) {
        showToast('Please fill all required fields', 'error');
        return;
    }
    
    let fd = new FormData();
    fd.append('add_class', '1');
    fd.append('class_name', cn);
    fd.append('stream', st);
    fd.append('reg_prefix', rp);
    fd.append('level', lv);
    
    fetch(window.location.pathname, {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            let empty = document.querySelector('#tableBody .empty-row');
            if (empty) empty.remove();
            
            let tbody = document.getElementById('tableBody');
            let row = document.createElement('tr');
            row.setAttribute('data-id', d.class_id);
            
            let count = document.querySelectorAll('#tableBody tr').length + 1;
            row.innerHTML = `
                <td>${count}</td>
                <td><span class="class-badge">${escapeHtml(d.class_name)}</span></td>
                <td>${d.stream ? '<span class="stream-text">'+escapeHtml(d.stream)+'</span>' : '<span style="color:#94a3b8;">—</span>'}</td>
                <td>${escapeHtml(d.reg_prefix)}</td>
                <td>${getLevelName(d.level)}</td>
                <td>
                    <div class="action-buttons">
                        <button class="edit-btn" 
                            data-id="${d.class_id}"
                            data-name="${escapeHtml(d.class_name)}"
                            data-stream="${escapeHtml(d.stream)}"
                            data-prefix="${escapeHtml(d.reg_prefix)}"
                            data-level="${d.level}">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="delete-btn" 
                            data-id="${d.class_id}" 
                            data-name="${escapeHtml(d.class_name + (d.stream ? ' ' + d.stream : ''))}">
                            <i class="fas fa-trash-alt"></i> Delete
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(row);
            
            document.getElementById('addClassForm').reset();
            updateCount();
            showToast('Class added', 'success');
        } else {
            showToast('Add failed', 'error');
        }
    })
    .catch(() => showToast('Network error', 'error'));
};

// ============ EVENT DELEGATION ============
// This handles clicks on dynamically created buttons (both edit and delete)
document.getElementById('tableBody').addEventListener('click', function(e) {
    // Delete button
    const deleteBtn = e.target.closest('.delete-btn');
    if (deleteBtn) {
        e.preventDefault();
        const id = deleteBtn.dataset.id;
        const name = deleteBtn.dataset.name;
        if (id && name) {
            window.deleteClass(id, name);
        } else {
            showToast('Invalid class data', 'error');
        }
        return;
    }
    
    // Edit button
    const editBtn = e.target.closest('.edit-btn');
    if (editBtn) {
        e.preventDefault();
        const id = editBtn.dataset.id;
        const name = editBtn.dataset.name;
        const stream = editBtn.dataset.stream || '';
        const prefix = editBtn.dataset.prefix;
        const level = editBtn.dataset.level;
        if (id && name) {
            window.editClass(id, name, stream, prefix, level);
        } else {
            showToast('Invalid class data', 'error');
        }
        return;
    }
});

// ============ SIDEBAR TOGGLE ============
let toggle = document.querySelector('.toggle-sidebar-btn');
if (toggle) toggle.onclick = () => document.body.classList.toggle('sidebar-collapsed');

let mobile = document.querySelector('.mobile-menu-btn');
if (mobile) mobile.onclick = () => document.body.classList.toggle('sidebar-mobile-open');

// Initialize counter
updateCount();
</script>

<?php include '../footer.php'; ?>

</body>
</html>