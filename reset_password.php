<?php
session_start();
require_once 'config.php';

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$error_msg = '';
$success_msg = '';
$valid_token = false;
$visitor = null;

if (empty($token)) {
    $error_msg = "Missing password reset token.";
} else {
    $stmt = $pdo->prepare("SELECT visitor_id, full_name, reset_expires FROM visitors WHERE reset_token = ? LIMIT 1");
    $stmt->execute([$token]);
    $visitor = $stmt->fetch();

    if (!$visitor) {
        $error_msg = "Invalid password reset link.";
    } elseif (empty($visitor['reset_expires']) || strtotime($visitor['reset_expires']) < time()) {
        $error_msg = "This password reset link has expired. Please request a new one.";
    } else {
        $valid_token = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 6) {
        $error_msg = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $error_msg = "Passwords do not match.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $update = $pdo->prepare("UPDATE visitors SET password = ?, reset_token = NULL, reset_expires = NULL WHERE visitor_id = ?");
        if ($update->execute([$hashed_password, $visitor['visitor_id']])) {
            $success_msg = "Your password has been updated successfully! You can now log in.";
            $valid_token = false;
        } else {
            $error_msg = "Failed to update password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - VSU VMS Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --vsu-green: #0f5132;
            --vsu-gold: #ffc107;
            --bg-color: #f3f6f4;
        }
        body {
            background-color: var(--bg-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .auth-card {
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            border: 1px solid #e9ecef;
            max-width: 440px;
            width: 100%;
            overflow: hidden;
        }
        .auth-header {
            background-color: var(--vsu-green);
            color: #fff;
            padding: 20px;
            text-align: center;
        }
        .auth-header span {
            color: var(--vsu-gold);
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="auth-header">
            <h5 class="fw-bold mb-1"><i class="fa-solid fa-lock text-warning me-2"></i>VSU <span>VMS PORTAL</span></h5>
            <small class="opacity-75">Create New Password</small>
        </div>
        <div class="p-4">
            <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success small">
                    <i class="fa-solid fa-check-circle me-1"></i> <?= htmlspecialchars($success_msg) ?>
                </div>
                <a href="visitor_login.php" class="btn btn-success w-100 fw-bold py-2">
                    Go to Visitor Login
                </a>
            <?php else: ?>
                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger small">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> <?= htmlspecialchars($error_msg) ?>
                    </div>
                <?php endif; ?>

                <?php if ($valid_token): ?>
                    <p class="small text-muted mb-3">
                        Setting a new password for <strong><?= htmlspecialchars($visitor['full_name']) ?></strong>.
                    </p>
                    <form method="POST">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">New Password</label>
                            <input type="password" name="password" class="form-control" minlength="6" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" minlength="6" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100 fw-bold py-2">
                            <i class="fa-solid fa-check me-1"></i> Update Password
                        </button>
                    </form>
                <?php endif; ?>

                <div class="text-center mt-3">
                    <a href="forgot_password.php" class="text-decoration-none small text-success fw-semibold me-2">Request New Link</a>
                    <span class="text-muted small">•</span>
                    <a href="visitor_login.php" class="text-decoration-none small text-success fw-semibold ms-2">Back to Login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
