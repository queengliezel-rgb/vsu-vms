<?php
session_start();
require_once 'config.php';

$success_msg = '';
$error_msg = '';
$reset_link = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $error_msg = "Please enter your registered email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Please enter a valid email address.";
    } else {
        $stmt = $pdo->prepare("SELECT visitor_id, full_name, email FROM visitors WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $visitor = $stmt->fetch();

        if ($visitor) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+30 minutes'));

            $update = $pdo->prepare("UPDATE visitors SET reset_token = ?, reset_expires = ? WHERE visitor_id = ?");
            $update->execute([$token, $expires, $visitor['visitor_id']]);

            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $dir = rtrim(dirname($_SERVER['PHP_SELF'] ?? '/'), '/\\');
            $reset_link = $scheme . "://" . $host . ($dir ? $dir : '') . "/reset_password.php?token=" . urlencode($token);

            // Subukang magpadala ng email kung gumagana ang PHP mail() sa server
            $subject = "VSU VMS Portal - Password Reset Request";
            $message = "Hi " . $visitor['full_name'] . ",\n\n"
                     . "Click the link below to reset your password (valid for 30 minutes):\n"
                     . $reset_link . "\n\n"
                     . "If you did not request this, please ignore this email.";
            $headers = "From: no-reply@vsu.edu.ph\r\nContent-Type: text/plain; charset=UTF-8";

            @mail($visitor['email'], $subject, $message, $headers);

            $success_msg = "A password reset link has been generated for " . htmlspecialchars($visitor['email']) . " (valid for 30 minutes).";
        } else {
            $error_msg = "No visitor account found with that email address.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - VSU VMS Portal</title>
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
            <h5 class="fw-bold mb-1"><i class="fa-solid fa-key text-warning me-2"></i>VSU <span>VMS PORTAL</span></h5>
            <small class="opacity-75">Reset Your Visitor Account Password</small>
        </div>
        <div class="p-4">
            <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success small">
                    <i class="fa-solid fa-check-circle me-1"></i> <?= $success_msg ?>
                </div>
                <?php if (!empty($reset_link)): ?>
                    <div class="p-3 bg-light border rounded small mb-3">
                        <div class="fw-bold text-dark mb-1">Direct Reset Link:</div>
                        <a href="<?= htmlspecialchars($reset_link) ?>" class="text-success fw-semibold text-break">
                            <?= htmlspecialchars($reset_link) ?>
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger small">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> <?= htmlspecialchars($error_msg) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold small">Registered Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="name@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                <button type="submit" class="btn btn-success w-100 fw-bold py-2">
                    <i class="fa-solid fa-paper-plane me-1"></i> Send Reset Link
                </button>
            </form>

            <div class="text-center mt-3">
                <a href="visitor_login.php" class="text-decoration-none small text-success fw-semibold">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Visitor Login
                </a>
            </div>
        </div>
    </div>
</body>
</html>
