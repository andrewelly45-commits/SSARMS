<?php
session_start();
include '../db.php';

// CHECK ADMIN
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// ================= HANDLE FORM SUBMISSION =================

// ADD/UPDATE SCHOOL SETTINGS
if (isset($_POST['save_school'])) {
    $school_name = mysqli_real_escape_string($conn, $_POST['school_name']);
    $school_code = mysqli_real_escape_string($conn, $_POST['school_code']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Handle logo upload
    $logo_path = null;
    if (isset($_FILES['school_logo']) && $_FILES['school_logo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['school_logo']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            // Create uploads directory if not exists
            $upload_dir = '../uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $new_filename = 'logo_' . time() . '.' . $ext;
            $target_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['school_logo']['tmp_name'], $target_path)) {
                $logo_path = 'uploads/' . $new_filename;
            }
        }
    }
    
    // Check if record exists
    $check = mysqli_query($conn, "SELECT id FROM school_settings LIMIT 1");
    
    if (mysqli_num_rows($check) > 0) {
        // UPDATE
        if ($logo_path) {
            // Get old logo to delete
            $old = mysqli_query($conn, "SELECT school_logo FROM school_settings LIMIT 1");
            $old_data = mysqli_fetch_assoc($old);
            if ($old_data['school_logo'] && file_exists('../' . $old_data['school_logo'])) {
                unlink('../' . $old_data['school_logo']);
            }
            $query = "UPDATE school_settings SET 
                school_name = '$school_name',
                school_code = '$school_code',
                school_logo = '$logo_path',
                address = '$address',
                phone = '$phone',
                email = '$email'
                WHERE id = 1";
        } else {
            $query = "UPDATE school_settings SET 
                school_name = '$school_name',
                school_code = '$school_code',
                address = '$address',
                phone = '$phone',
                email = '$email'
                WHERE id = 1";
        }
    } else {
        // INSERT
        $query = "INSERT INTO school_settings 
            (school_name, school_code, school_logo, address, phone, email) 
            VALUES 
            ('$school_name', '$school_code', '$logo_path', '$address', '$phone', '$email')";
    }
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "School settings saved successfully!";
    } else {
        $_SESSION['error'] = "Error: " . mysqli_error($conn);
    }
    
    header("Location: manage_school.php");
    exit();
}

// ================= DELETE LOGO =================
if (isset($_GET['remove_logo'])) {
    $result = mysqli_query($conn, "SELECT school_logo FROM school_settings LIMIT 1");
    $data = mysqli_fetch_assoc($result);
    
    if ($data['school_logo'] && file_exists('../' . $data['school_logo'])) {
        unlink('../' . $data['school_logo']);
    }
    
    mysqli_query($conn, "UPDATE school_settings SET school_logo = NULL WHERE id = 1");
    header("Location: manage_school.php");
    exit();
}

// ================= FETCH SCHOOL SETTINGS =================
$settings = mysqli_query($conn, "SELECT * FROM school_settings LIMIT 1");
$school = mysqli_fetch_assoc($settings);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSARMS - School Settings</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f1f5f9;
            font-family: 'Inter', sans-serif;
        }

        .container {
            margin-left: 270px;
            margin-top: 92px;
            padding: 0 32px 48px 32px;
            transition: margin-left 0.3s ease;
            max-width: 1000px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(135deg, #1e2a5e, #2c3e66);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .alert i {
            font-size: 20px;
        }

        .card {
            background: white;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .card-header {
            padding: 20px 28px;
            border-bottom: 1px solid #eef2ff;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card-header i {
            font-size: 20px;
            color: #1e2a5e;
        }

        .card-header h2 {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
        }

        .card-body {
            padding: 28px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 6px;
        }

        .form-group label i {
            margin-right: 8px;
            color: #64748b;
            width: 18px;
        }

        .form-group label .required {
            color: #ef4444;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #3b82f6;
            outline: none;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 70px;
        }

        .form-group .help-text {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 4px;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 24px;
            padding: 16px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .logo-preview {
            width: 100px;
            height: 100px;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            overflow: hidden;
            flex-shrink: 0;
        }

        .logo-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .logo-preview .no-logo {
            font-size: 40px;
            color: #cbd5e1;
        }

        .logo-actions {
            flex: 1;
        }

        .logo-actions .file-input-wrapper {
            position: relative;
            display: inline-block;
        }

        .logo-actions .file-input-wrapper input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .btn {
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #475569;
        }

        .btn-secondary:hover {
            background: #cbd5e1;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #eef2ff;
        }

        @media (max-width: 768px) {
            .container {
                margin-left: 0 !important;
                margin-top: 85px;
                padding: 0 16px 30px;
            }
            
            .logo-section {
                flex-direction: column;
                text-align: center;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .form-actions .btn {
                width: 100%;
                justify-content: center;
            }
            
            .header h1 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>
<?php include 'admin_topbar.php'; ?>

<div class="container">
    <div class="header">
        <h1><i class="fas fa-school"></i> School Settings</h1>
    </div>

    <!-- Display Messages -->
    <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?= $_SESSION['success'] ?>
    </div>
    <?php unset($_SESSION['success']); endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <?= $_SESSION['error'] ?>
    </div>
    <?php unset($_SESSION['error']); endif; ?>

    <!-- School Settings Form -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-cog"></i>
            <h2>School Information</h2>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <!-- School Name -->
                <div class="form-group">
                    <label><i class="fas fa-school"></i> School Name <span class="required">*</span></label>
                    <input type="text" name="school_name" 
                           value="<?= htmlspecialchars($school['school_name'] ?? '') ?>" 
                           placeholder="Enter school name" required>
                </div>

                <!-- School Code -->
                <div class="form-group">
                    <label><i class="fas fa-code"></i> School Code <span class="required">*</span></label>
                    <input type="text" name="school_code" 
                           value="<?= htmlspecialchars($school['school_code'] ?? '') ?>" 
                           placeholder="e.g., SCH001" required>
                    <div class="help-text">Unique identifier for your school</div>
                </div>

                <!-- Address -->
                <div class="form-group">
                    <label><i class="fas fa-map-marker-alt"></i> Address</label>
                    <textarea name="address" placeholder="Enter school address"><?= htmlspecialchars($school['address'] ?? '') ?></textarea>
                </div>

                <!-- Phone -->
                <div class="form-group">
                    <label><i class="fas fa-phone"></i> Phone Number</label>
                    <input type="tel" name="phone" 
                           value="<?= htmlspecialchars($school['phone'] ?? '') ?>" 
                           placeholder="e.g., +255 712 345 678">
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email Address</label>
                    <input type="email" name="email" 
                           value="<?= htmlspecialchars($school['email'] ?? '') ?>" 
                           placeholder="school@example.com">
                </div>

                <!-- Logo -->
                <div class="form-group">
                    <label><i class="fas fa-image"></i> School Logo</label>
                    
                    <div class="logo-section">
                        <div class="logo-preview">
                            <?php if (!empty($school['school_logo']) && file_exists('../' . $school['school_logo'])): ?>
                                <img src="../<?= htmlspecialchars($school['school_logo']) ?>" alt="School Logo">
                            <?php else: ?>
                                <i class="fas fa-school no-logo"></i>
                            <?php endif; ?>
                        </div>
                        
                        <div class="logo-actions">
                            <?php if (!empty($school['school_logo']) && file_exists('../' . $school['school_logo'])): ?>
                                <p style="margin-bottom:8px;font-size:14px;color:#1e293b;">
                                    <i class="fas fa-check-circle" style="color:#10b981;"></i>
                                    <?= basename($school['school_logo']) ?>
                                </p>
                                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                    <div class="file-input-wrapper">
                                        <button class="btn btn-secondary">
                                            <i class="fas fa-upload"></i> Change Logo
                                        </button>
                                        <input type="file" name="school_logo" accept="image/*">
                                    </div>
                                    <a href="?remove_logo=1" class="btn btn-danger" 
                                       onclick="return confirm('Remove the current logo?')">
                                        <i class="fas fa-trash"></i> Remove
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="file-input-wrapper">
                                    <button class="btn btn-primary">
                                        <i class="fas fa-upload"></i> Upload Logo
                                    </button>
                                    <input type="file" name="school_logo" accept="image/*">
                                </div>
                            <?php endif; ?>
                            <div class="help-text" style="margin-top:8px;">
                                <i class="fas fa-info-circle"></i> 
                                Supported: JPG, PNG, GIF (Max 2MB)
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="form-actions">
                    <button type="submit" name="save_school" class="btn btn-success">
                        <i class="fas fa-save"></i> Save Settings
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ============ FILE INPUT LABEL UPDATE ============
document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', function() {
        const label = this.closest('.file-input-wrapper').querySelector('.btn');
        if (this.files && this.files[0]) {
            const fileName = this.files[0].name;
            const fileSize = (this.files[0].size / 1024 / 1024).toFixed(2);
            
            // Check file size (2MB limit)
            if (this.files[0].size > 2 * 1024 * 1024) {
                alert('File size exceeds 2MB limit. Please choose a smaller file.');
                this.value = '';
                return;
            }
            
            // Check file type
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(this.files[0].type)) {
                alert('Please upload a valid image file (JPG, PNG, or GIF).');
                this.value = '';
                return;
            }
            
            label.innerHTML = `<i class="fas fa-check"></i> ${fileName} (${fileSize}MB)`;
        }
    });
});

// ============ SIDEBAR TOGGLE ============
let toggle = document.querySelector('.toggle-sidebar-btn');
if (toggle) toggle.onclick = () => document.body.classList.toggle('sidebar-collapsed');

let mobile = document.querySelector('.mobile-menu-btn');
if (mobile) mobile.onclick = () => document.body.classList.toggle('sidebar-mobile-open');
</script>

<?php include '../footer.php'; ?>

</body>
</html>