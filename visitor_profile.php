<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['visitor_portal_id'])) {
    header("Location: visitor_login.php");
    exit;
}

$visitor_id = $_SESSION['visitor_portal_id'];
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name      = trim($_POST['full_name'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $contact_no     = trim($_POST['contact_no'] ?? '');
    $address        = trim($_POST['address'] ?? '');
    $sex            = trim($_POST['sex'] ?? '');
    $office         = trim($_POST['office_to_visit'] ?? '');
    $purpose        = trim($_POST['purpose'] ?? '');

    // Kunin ang kasalukuyang profile pic sa database bilang fallback kung walang bagong upload
    $stmt_old = $pdo->prepare("SELECT profile_pic FROM visitors WHERE visitor_id = ?");
    $stmt_old->execute([$visitor_id]);
    $current_data = $stmt_old->fetch();
    $profile_pic = $current_data['profile_pic'] ?? null;

    // Suriin kung may in-upload na bagong larawan mula sa file input
    if (isset($_FILES['profile_pic'])) {
        if ($_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            $file_tmp_path = $_FILES['profile_pic']['tmp_name'];
            $file_name = $_FILES['profile_pic']['name'];
            $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($file_extension, $allowed_extensions)) {
                $img_binary = file_get_contents($file_tmp_path);
                if ($img_binary !== false) {
                    // I-convert agad sa Base64 data URI string
                    $profile_pic = 'data:image/' . ($file_extension === 'jpg' ? 'jpeg' : $file_extension) . ';base64,' . base64_encode($img_binary);
                }
            } else {
                $error_msg = "Invalid file extension. Please upload JPG, JPEG, PNG, or GIF.";
            }
        } elseif ($_FILES['profile_pic']['error'] !== UPLOAD_ERR_NO_FILE) {
            $error_msg = "May error sa pag-upload ng file (Error Code: " . $_FILES['profile_pic']['error'] . "). Maaaring masyadong malaki ang larawan.";
        }
    }

    if (empty($error_msg)) {
        $update_stmt = $pdo->prepare("UPDATE visitors SET full_name = ?, email = ?, contact_no = ?, address = ?, sex = ?, office_to_visit = ?, purpose = ?, profile_pic = ? WHERE visitor_id = ?");
        if ($update_stmt->execute([$full_name, $email, $contact_no, $address, $sex, $office, $purpose, $profile_pic, $visitor_id])) {
            $success_msg = "Profile updated successfully!";
        } else {
            $error_msg = "Failed to update profile in database.";
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM visitors WHERE visitor_id = ?");
$stmt->execute([$visitor_id]);
$visitor = $stmt->fetch();

if (!$visitor) {
    header("Location: visitor_login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VSU VMS Portal - Visitor Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --vsu-green: #0f5132;
            --vsu-light-green: #198754;
            --vsu-gold: #ffc107;
            --vsu-gold-hover: #e0a800;
            --bg-color: #f3f6f4;
        }

        body {
            background-color: var(--bg-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 260px;
            background-color: var(--vsu-green);
            color: #fff;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
        }

        .sidebar-brand {
            padding: 20px;
            font-weight: 800;
            font-size: 1.1rem;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
            background-color: #0b3d25;
        }

        .sidebar-brand span {
            color: var(--vsu-gold);
        }

        .sidebar-menu {
            padding: 20px 15px;
            list-style: none;
            margin: 0;
            flex-grow: 1;
        }

        .sidebar-menu li {
            margin-bottom: 8px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            color: #cbd5e1;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: var(--vsu-gold);
            color: #000;
        }

        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn-logout {
            background-color: #fff;
            color: var(--vsu-green);
            font-weight: 700;
            border: none;
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
        }

        .btn-logout:hover {
            background-color: #f8f9fa;
            color: #0b3d25;
        }

        .main-content {
            margin-left: 260px;
            flex-grow: 1;
            padding: 30px;
        }

        .breadcrumb-text {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 5px;
        }

        .page-title {
            font-weight: 800;
            color: #1a1a1a;
            font-size: 1.8rem;
            margin-bottom: 4px;
        }

        .page-subtitle {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 25px;
        }

        .profile-card {
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            border: 1px solid #e9ecef;
            padding: 30px;
            margin-bottom: 25px;
        }

        .avatar-circle {
            width: 70px;
            height: 70px;
            background-color: #e8f5e9;
            color: var(--vsu-green);
            font-weight: 700;
            font-size: 1.6rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            object-fit: cover;
        }

        .avatar-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="fa-solid fa-qrcode text-warning"></i> VSU <span>VMS PORTAL</span>
        </div>
        <div class="px-3 pt-3 pb-2 text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Workspace</div>
        <ul class="sidebar-menu">
            <li><a href="visitor_profile.php" class="active"><i class="fa-solid fa-user"></i> My Profile</a></li>
            <li><a href="visitor_qrcode.php"><i class="fa-solid fa-qrcode"></i> My QR Code</a></li>
            <li><a href="visitor_history.php"><i class="fa-solid fa-clock-rotate-left"></i> Visit History</a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="logout_visitor.php" class="btn-logout">
                <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="breadcrumb-text">Visayas State University • Visitor Dashboard • <?= htmlspecialchars($visitor['full_name']) ?></div>
        <h1 class="page-title">My Profile</h1>
        <div class="page-subtitle">Your registered visitor account and credentials.</div>

        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success py-2 px-3 mb-3 text-center small border-0 rounded-3">
                <i class="fa-solid fa-check me-1"></i> <?= $success_msg ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger py-2 px-3 mb-3 text-center small border-0 rounded-3">
                <i class="fa-solid fa-triangle-exclamation me-1"></i> <?= $error_msg ?>
            </div>
        <?php endif; ?>

        <!-- Profile Card -->
        <div class="profile-card">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-circle">
                        <?php if (!empty($visitor['profile_pic'])): ?>
                            <img src="<?= $visitor['profile_pic'] ?>" alt="Profile">
                        <?php else: ?>
                            <?= strtoupper(substr($visitor['full_name'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($visitor['full_name']) ?></h3>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span class="badge bg-warning text-dark px-2 py-1">Visitor</span>
                            <span class="text-muted small font-monospace"><?= htmlspecialchars($visitor['visitor_id']) ?></span>
                        </div>
                    </div>
                </div>
                <div>
                    <button class="btn btn-outline-success btn-sm px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                        <i class="fa-solid fa-pen-to-square me-1"></i> Edit Profile
                    </button>
                </div>
            </div>

            <hr class="my-4 text-muted opacity-25">

            <!-- Profile Details Grid -->
            <div class="row g-4">
                <div class="col-md-6">
                    <span class="text-muted small d-block mb-1">Visitor ID Number</span>
                    <strong class="text-dark font-monospace"><?= htmlspecialchars($visitor['visitor_id']) ?></strong>
                </div>
                <div class="col-md-6">
                    <span class="text-muted small d-block mb-1">Email Address</span>
                    <strong class="text-dark"><?= htmlspecialchars($visitor['email'] ?? 'N/A') ?></strong>
                </div>
                <div class="col-md-6">
                    <span class="text-muted small d-block mb-1">Contact Number</span>
                    <strong class="text-dark"><?= htmlspecialchars($visitor['contact_no'] ?? 'N/A') ?></strong>
                </div>
                <div class="col-md-6">
                    <span class="text-muted small d-block mb-1">Sex</span>
                    <strong class="text-dark"><?= htmlspecialchars($visitor['sex'] ?? 'N/A') ?></strong>
                </div>
                <div class="col-md-12">
                    <span class="text-muted small d-block mb-1">Address</span>
                    <strong class="text-dark"><?= htmlspecialchars($visitor['address'] ?? 'N/A') ?></strong>
                </div>
                <div class="col-md-6">
                    <span class="text-muted small d-block mb-1">Office / Department to Visit</span>
                    <strong class="text-dark"><?= htmlspecialchars($visitor['office_to_visit'] ?? 'N/A') ?></strong>
                </div>
                <div class="col-md-6">
                    <span class="text-muted small d-block mb-1">Purpose of Visit</span>
                    <strong class="text-dark"><?= htmlspecialchars($visitor['purpose'] ?? 'N/A') ?></strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-pen me-2"></i> Edit Profile & Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body p-4">
                        <div class="mb-3 text-center">
                            <label class="form-label d-block fw-bold text-muted small">Profile Picture</label>
                            <input type="file" name="profile_pic" class="form-control form-control-sm" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Full Name</label>
                            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($visitor['full_name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Email Address</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($visitor['email'] ?? '') ?>" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small">Contact Number</label>
                                <input type="text" name="contact_no" class="form-control" value="<?= htmlspecialchars($visitor['contact_no'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small">Sex</label>
                                <select name="sex" class="form-select">
                                    <option value="">Select Sex</option>
                                    <option value="Male" <?= ($visitor['sex'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                                    <option value="Female" <?= ($visitor['sex'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                                    <option value="Other" <?= ($visitor['sex'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Address</label>
                            <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($visitor['address'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Office / Department to Visit</label>
                            <input type="text" name="office_to_visit" class="form-control" value="<?= htmlspecialchars($visitor['office_to_visit']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Purpose of Visit</label>
                            <input type="text" name="purpose" class="form-control" value="<?= htmlspecialchars($visitor['purpose']) ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_profile" class="btn btn-success btn-sm px-4 fw-bold">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
