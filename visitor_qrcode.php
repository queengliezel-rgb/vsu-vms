<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['visitor_portal_id'])) {
    header("Location: visitor_login.php");
    exit;
}

$visitor_id = $_SESSION['visitor_portal_id'];
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
    <title>My QR Code - VSU VMS Portal</title>
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
        body { background-color: var(--bg-color); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background-color: var(--vsu-green); color: #fff; display: flex; flex-direction: column; position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; }
        .sidebar-brand { padding: 20px; font-weight: 800; font-size: 1.1rem; color: #fff; display: flex; align-items: center; gap: 10px; background-color: #0b3d25; }
        .sidebar-brand span { color: var(--vsu-gold); }
        .sidebar-menu { padding: 20px 15px; list-style: none; margin: 0; flex-grow: 1; }
        .sidebar-menu li { margin-bottom: 8px; }
        .sidebar-menu a { display: flex; align-items: center; gap: 12px; padding: 12px 15px; color: #cbd5e1; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 0.95rem; transition: all 0.2s; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background-color: var(--vsu-gold); color: #000; }
        .sidebar-footer { padding: 20px; border-top: 1px solid rgba(255, 255, 255, 0.1); }
        .btn-logout { background-color: #fff; color: var(--vsu-green); font-weight: 700; border: none; width: 100%; padding: 10px; border-radius: 8px; text-align: center; text-decoration: none; display: inline-block; }
        .btn-logout:hover { background-color: #f8f9fa; color: #0b3d25; }
        .main-content { margin-left: 260px; flex-grow: 1; padding: 30px; }
        .page-card { background: #ffffff; border-radius: 14px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid #e9ecef; padding: 30px; max-width: 500px; margin: 0 auto; text-align: center; }
        .btn-gold { background-color: var(--vsu-gold); color: #000; font-weight: 700; border: none; }
        .btn-gold:hover { background-color: var(--vsu-gold-hover); color: #000; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="fa-solid fa-qrcode text-warning"></i> VSU <span>VMS PORTAL</span>
        </div>
        <div class="px-3 pt-3 pb-2 text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Workspace</div>
        <ul class="sidebar-menu">
            <li><a href="visitor_profile.php"><i class="fa-solid fa-user"></i> My Profile</a></li>
            <li><a href="visitor_qrcode.php" class="active"><i class="fa-solid fa-qrcode"></i> My QR Code</a></li>
            <li><a href="visitor_history.php"><i class="fa-solid fa-clock-rotate-left"></i> Visit History</a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="logout_visitor.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket me-1"></i> Logout</a>
        </div>
    </div>

    <div class="main-content">
        <div class="mb-4">
            <small class="text-muted">Visayas State University • Visitor Dashboard</small>
            <h1 class="fw-bold text-dark">My QR Code</h1>
            <p class="text-muted small">Your official digital visitor pass for campus gate entry.</p>
        </div>

        <div class="page-card">
            <h4 class="fw-bold text-success mb-3"><?= htmlspecialchars($visitor['full_name']) ?></h4>
            <span class="badge bg-secondary font-monospace mb-4 px-3 py-2"><?= htmlspecialchars($visitor['visitor_id']) ?></span>
            
            <div class="bg-light p-3 rounded d-inline-block mb-3 border">
                <div id="qrcode" class="d-flex justify-content-center"></div>
            </div>
            
            <p class="text-muted small mb-4">Show this QR code to the security guard scanner at the campus gate.</p>
            <button onclick="window.print()" class="btn btn-gold w-100 py-2">
                <i class="fa-solid fa-print me-1"></i> Print QR Pass
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        new QRCode(document.getElementById("qrcode"), {
            text: "<?= $visitor['visitor_id'] ?>",
            width: 160,
            height: 160
        });
    </script>
</body>
</html>