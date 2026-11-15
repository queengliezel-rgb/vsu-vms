<?php
session_start();
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM visitors WHERE email = ?");
        $stmt->execute([$email]);
        $visitor = $stmt->fetch();

        if ($visitor) {
            if (!empty($visitor['password'])) {
                if (password_verify($password, $visitor['password']) || $password === $visitor['password']) {
                    $_SESSION['visitor_portal_id'] = $visitor['visitor_id'];
                    header("Location: visitor_profile.php");
                    exit;
                } else {
                    $error = "Invalid email address or password!"; // Mali ang password
                }
            } else {
                $_SESSION['visitor_portal_id'] = $visitor['visitor_id'];
                header("Location: visitor_profile.php");
                exit;
            }
        } else {
            $error = "Invalid email address or password!"; // Walang nahanap na email
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VSU Visitor Portal - Login</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
            margin: 0;
        }

        .login-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 420px;
            padding: 35px 30px;
            border: 1px solid #e9ecef;
        }

        .brand-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .brand-icon {
            width: 38px;
            height: 38px;
            background-color: #e8f5e9;
            color: var(--vsu-green);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .brand-text {
            font-weight: 800;
            font-size: 0.95rem;
            color: var(--vsu-green);
            letter-spacing: 0.5px;
        }

        .brand-text span {
            color: var(--vsu-gold);
        }

        .login-title {
            font-weight: 700;
            color: #1a1a1a;
            font-size: 1.6rem;
            margin-bottom: 6px;
        }

        .login-subtitle {
            color: #6c757d;
            font-size: 0.88rem;
            margin-bottom: 25px;
            line-height: 1.4;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            font-size: 0.85rem;
            margin-bottom: 6px;
        }

        .form-control {
            padding: 11px 14px;
            border-radius: 8px;
            border: 1px solid #ced4da;
            font-size: 0.92rem;
            transition: all 0.2s ease-in-out;
        }

        .form-control:focus {
            border-color: var(--vsu-light-green);
            box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.15);
        }

        .input-group-text {
            background: transparent;
            border-left: none;
            color: #6c757d;
            cursor: pointer;
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .has-icon .form-control {
            border-right: none;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
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

        .btn-admin {
            background-color: #f8f9fa;
            color: var(--vsu-green);
            border: 1px solid #dcdcdc;
            font-weight: 600;
            padding: 11px;
            border-radius: 8px;
            width: 100%;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .btn-admin:hover {
            background-color: #e8f5e9;
            color: var(--vsu-green);
            border-color: var(--vsu-light-green);
        }

        .link-green {
            color: var(--vsu-light-green);
            font-weight: 600;
            text-decoration: none;
        }

        .link-green:hover {
            text-decoration: underline;
        }

        .footer-info {
            margin-top: 30px;
            text-align: center;
            font-size: 0.82rem;
            color: #6c757d;
        }

        .footer-info a {
            color: var(--vsu-green);
            text-decoration: underline;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="brand-header">
            <div class="brand-icon">
                <i class="fa-solid fa-qrcode"></i>
            </div>
            <div class="brand-text">
                VSU <span>VISITOR PASS</span>
            </div>
        </div>

        <h1 class="login-title">Visitor Portal Login</h1>
        <p class="login-subtitle">Enter your email address and password to access your digital QR pass and campus logs.</p>

        <!-- Dito lilitaw ang error sign o babala kapag nagkamali -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 px-3 mb-3 text-center small border-0 rounded-3">
                <i class="fa-solid fa-triangle-exclamation me-1"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="visitor@gmail.com" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group has-icon">
                    <input type="password" name="password" id="passwordInput" class="form-control" required>
                    <span class="input-group-text" onclick="togglePassword()">
                        <i class="fa-regular fa-eye-slash" id="toggleIcon"></i>
                    </span>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="rememberMe">
                    <label class="form-check-label text-muted small" for="rememberMe">Remember me</label>
                </div>
                <a href="#" class="link-green small">Forgot Password?</a>
            </div>

            <button type="submit" class="btn btn-gold mb-3">
                <i class="fa-solid fa-right-to-bracket me-1"></i> Log in as Visitor
            </button>
        </form>

        <div class="text-center mt-2 mb-4">
            <span class="text-muted small">Don't have a visitor pass yet?</span> 
            <a href="register.php" class="link-green small ms-1">Register Here</a>
        </div>

        <div class="pt-3 border-top text-center">
            <a href="login.php" class="btn btn-admin text-decoration-none d-block">
                <i class="fa-solid fa-user-shield me-1"></i> Log in as Admin / Guard
            </a>
        </div>
    </div>

    <div class="footer-info">
        <div class="fw-bold text-dark mb-1">Need Assistance?</div>
        <div>For inquiries regarding the VSU Visitor System:</div>
        <a href="mailto:vms-support@vsu.edu.ph">vms-support@vsu.edu.ph</a>
    </div>

    <script>
        function togglePassword() {
            const passInput = document.getElementById('passwordInput');
            const icon = document.getElementById('toggleIcon');
            
            if (passInput.type === 'password') {
                passInput.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                passInput.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        }
    </script>
</body>
</html>