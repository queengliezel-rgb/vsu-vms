<?php
session_start();
require_once 'config.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = 'guard'; // Naka-set automatically bilang guard

    if (!empty($username) && !empty($password)) {
        // I-hash ang password para ligtas
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Suriin muna kung may umiiral na kaparehong username
            $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $check->execute([$username]);
            
            if ($check->rowCount() > 0) {
                $error = "Username already exists. Please choose another one.";
            } else {
                // I-save sa database
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                if ($stmt->execute([$username, $hashed_password, $role])) {
                    $message = "Guard account successfully created!";
                } else {
                    $error = "Failed to create guard account.";
                }
            }
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
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
    <title>Create Guard Account - VSU VMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="card shadow border-0 rounded-4 p-4" style="width: 100%; max-width: 400px;">
        <div class="text-center mb-4">
            <h4 class="fw-bold text-success"><i class="fa-solid fa-user-shield me-2"></i>Create Guard Account</h4>
            <p class="text-muted small">Add a new security guard staff credentials</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success py-2 text-center small"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2 text-center small"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-bold small">Guard Username</label>
                <input type="text" name="username" class="form-control" placeholder="e.g., guard_main_gate" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold small">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter secure password" required>
            </div>
            <button type="submit" class="btn btn-success w-100 fw-bold py-2">Create Account</button>
        </form>

        <div class="text-center mt-3">
            <a href="login.php" class="text-decoration-none small text-muted"><i class="fa-solid fa-arrow-left me-1"></i>Back to Login</a>
        </div>
    </div>
</body>
</html>