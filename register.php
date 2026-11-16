<?php
session_start();
require_once 'config.php';

$error = '';$success = false;
$new_visitor = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name  = trim($_POST['full_name'] ?? '');
    $contact_no = trim($_POST['contact_no'] ?? '');
    $office     = trim($_POST['office'] ?? '');
    $purpose    = trim($_POST['purpose'] ?? '');
    $address    = trim($_POST['address'] ?? '');
    $sex        = trim($_POST['sex'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   =$_POST['password'] ?? '';
    $c_password =$_POST['confirm_password'] ?? '';

    if ($password !== $c_password) {$error = "Hindi nagtutugma ang password at confirm password!";
    } else {
        $visitor_id = "VSU-V-" . date('Ymd') . "-" . rand(100, 999);
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $profile_pic = '';
        if (!empty($_POST['profile_pic_base64'])) {
            $data =$_POST['profile_pic_base64'];
            if (strpos($data, 'data:image/') === 0) {
                $profile_pic =$data;
            } else {
                $profile_pic = 'data:image/png;base64,' .$data;
            }
        }

        $stmt =$pdo->prepare("INSERT INTO visitors (visitor_id, full_name, contact_no, purpose, office_to_visit, address, sex, email, password, profile_pic) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$visitor_id, $full_name,$contact_no, $purpose,$office, $address,$sex, $email,$hashed_password, $profile_pic])) {$success = true;
            $new_visitor = [
                'visitor_id' => $visitor_id,
                'full_name'  => $full_name,
                'office'     => $office,
                'purpose'    => $purpose,
                'email'      => $email,
                'profile_pic'=> $profile_pic
            ];
        } else {
            $error = "May naganap na error sa pagre-register. Subukang muli.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VSU Visitor Portal - Create Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
            margin: 0;
        }

        .register-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 650px;
            padding: 40px;
            border: 1px solid #e9ecef;
        }

        .brand-header {
            font-weight: 800;
            font-size: 0.95rem;
            color: var(--vsu-green);
            letter-spacing: 0.5px;
            margin-bottom: 20px;
        }

        .brand-header span {
            color: var(--vsu-gold);
        }

        .form-title {
            font-weight: 700;
            color: #1a1a1a;
            font-size: 1.8rem;
            margin-bottom: 6px;
        }

        .form-subtitle {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 25px;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            font-size: 0.85rem;
            margin-bottom: 6px;
        }

        .form-control, .form-select {
            padding: 11px 14px;
            border-radius: 8px;
            border: 1px solid #ced4da;
            font-size: 0.92rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--vsu-light-green);
            box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.15);
        }

        .btn-gold {
            background-color: var(--vsu-gold);
            color: #000;
            font-weight: 700;
            padding: 12px;
            border-radius: 8px;
            border: none;
            width: 100%;
            font-size: 0.95rem;
            transition: background-color 0.2s;
        }

        .btn-gold:hover {
            background-color: var(--vsu-gold-hover);
            color: #000;
        }

        .link-green {
            color: var(--vsu-light-green);
            font-weight: 600;
            text-decoration: none;
        }

        .link-green:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="register-card">
    <div class="brand-header">
        VSU <span>VISITOR TRACK</span>
    </div>

    <?php if (!$success): ?>
        <h1 class="form-title">Create your visitor account</h1>
        <p class="form-subtitle">Enter your visitor details and snap a profile picture to generate your official VSU digital QR pass.</p>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2 px-3 mb-3 text-center small border-0 rounded-3">
                <i class="fa-solid fa-triangle-exclamation me-1"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="registrationForm">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="contact_no" class="form-control" placeholder="09XXXXXXXXX" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Office / Department to Visit</label>
                    <input type="text" name="office" class="form-control" placeholder="e.g., Office of the Registrar" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Purpose of Visit</label>
                    <input type="text" name="purpose" class="form-control" placeholder="e.g., Document Submission" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="1" placeholder="Enter complete address" required></textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Sex</label>
                    <select name="sex" class="form-select" required>
                        <option value="">Select Sex</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Email address</label>
                <input type="email" name="email" class="form-control" placeholder="visitor@gmail.com" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Confirm password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Profile Picture (Camera Capture)</label>
                <div class="text-center border p-3 rounded bg-light">
                    <video id="video" width="100%" height="160" autoplay class="border rounded bg-dark mb-2" style="object-fit: cover;"></video>
                    <canvas id="canvas" width="320" height="240" style="display:none;"></canvas>
                    <div>
                        <button type="button" id="snap" class="btn btn-dark btn-sm px-4 mb-2"><i class="fa-solid fa-camera me-1"></i> Capture Photo</button>
                    </div>
                    <div id="snapshot-preview">
                        <img id="preview-img" src="" alt="Preview" class="rounded-circle border mt-1" style="width: 70px; height: 70px; object-fit: cover; display: none;" />
                    </div>
                </div>
                <input type="hidden" name="profile_pic_base64" id="profile_pic_base64" required>
            </div>

            <button type="submit" class="btn btn-gold mb-3">
                Create account
            </button>
        </form>
        
        <div class="text-center mt-3 pt-3 border-top">
            <span class="text-muted small">Already have an account?</span> 
            <a href="visitor_login.php" class="link-green small ms-1">Log in as a visitor</a>
        </div>

    <?php else: ?>
        <div class="text-center">
            <h3 class="text-success fw-bold mb-1">Account Created Successfully!</h3>
            <p class="text-muted small mb-4">Your official VSU visitor account, profile photo, and QR pass have been generated.</p>
            
            <div class="border p-4 rounded bg-light mb-4 text-start">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                    <div class="d-flex align-items-center">
                        <?php if (!empty($new_visitor['profile_pic'])): ?>
                            <img src="<?= $new_visitor['profile_pic'] ?>" class="rounded-circle border me-3" width="60" height="60" style="object-fit: cover;">
                        <?php else: ?>
                            <div class="rounded-circle border me-3 d-flex align-items-center justify-content-center bg-white text-success fw-bold" style="width:
