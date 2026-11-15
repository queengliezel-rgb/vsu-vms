<?php
session_start();
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && (password_verify($password, $user['password']) || $password === $user['password'])) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'];

        if ($_SESSION['role'] === 'guard') {
            header("Location: guard_scan.php");
        } else {
            header("Location: index.php");
        }
        exit;
    } else {
        $error = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VSU VMS - Admin & Guard Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f3f6f4; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; }
        .login-card { background: #ffffff; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); width: 100%; max-width: 420px; padding: 35px 30px; border: 1px solid #e9ecef; }
        .brand-text { font-weight: 800; font-size: 0.95rem; color: #0f5132; letter-spacing: 0.5px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
        .brand-text span { color: #ffc107; }
        .login-title { font-weight: 700; color: #1a1a1a; font-size: 1.6rem; margin-bottom: 6px; }
        .login-subtitle { color: #6c757d; font-size: 0.88rem; margin-bottom: 25px; }
        .form-label { font-weight: 600; color: #333; font-size: 0.85rem; margin-bottom: 6px; }
        .form-control { padding: 11px 14px; border-radius: 8px; border: 1px solid #ced4da; font-size: 0.92rem; }
        .form-control:focus { border-color: #198754; box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.15); }
        .btn-green { background-color: #0f5132; color: #fff; font-weight: 700; padding: 12px; border-radius: 8px; border: none; width: 100%; transition: background-color 0.2s; }
        .btn-green:hover { background-color: #157347; color: #fff; }
        .link-green { color: #198754; font-weight: 600; text-decoration: none; }
        .link-green:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="brand-text">
            <i class="fa-solid fa-shield-halved text-success fs-5"></i> VSU <span>STAFF PORTAL</span>
        </div>
        <h1 class="login-title">Guard & Admin Login</h1>
        <p class="login-subtitle">Sign in with your system account credentials to access gate scanner and management portal.</p>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2 px-3 mb-3 text-center small border-0 rounded-3">
                <i class="fa-solid fa-triangle-exclamation me-1"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Enter username" required>
            </div>
            
            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-green mb-3">
                <i class="fa-solid fa-right-to-bracket me-1"></i> Log in as Guard / Admin
            </button>
        </form>

        <div class="pt-3 border-top text-center">
            <span class="text-muted small">Are you a visitor?</span>
            <a href="visitor_login.php" class="link-green small ms-1">Go to Visitor Login</a>
        </div>
    </div>
</body>
</html>