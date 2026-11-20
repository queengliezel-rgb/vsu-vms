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

$log_stmt = $pdo->prepare("SELECT check_in, check_out, status FROM visitor_logs WHERE visitor_id = ? ORDER BY id DESC");
$log_stmt->execute([$visitor_id]);
$logs = $log_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visit History - VSU VMS Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --vsu-green: #0f5132;
            --vsu-light-green: #198754;
            --vsu-gold: #ffc107;
            --bg-color: #f3f6f4;
        }
        body { background-color: var(--bg-color); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background-color: var(--vsu-green); color: #fff; display: flex; flex-direction: column; position: fixed; top: 0; bottom: 0; left: 0; z-index: 1050; transition: transform 0.3s ease-in-out; }
        .sidebar-brand { padding: 20px; font-weight: 800; font-size: 1.1rem; color: #fff; display: flex; align-items: center; justify-content: space-between; background-color: #0b3d25; }
        .sidebar-brand span { color: var(--vsu-gold); }
        .sidebar-menu { padding: 20px 15px; list-style: none; margin: 0; flex-grow: 1; }
        .sidebar-menu li { margin-bottom: 8px; }
        .sidebar-menu a { display: flex; align-items: center; gap: 12px; padding: 12px 15px; color: #cbd5e1; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 0.95rem; transition: all 0.2s; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background-color: var(--vsu-gold); color: #000; }
        .sidebar-footer { padding: 20px; border-top: 1px solid rgba(255, 255, 255, 0.1); }
        .btn-logout { background-color: #fff; color: var(--vsu-green); font-weight: 700; border: none; width: 100%; padding: 10px; border-radius: 8px; text-align: center; text-decoration: none; display: inline-block; }
        .btn-logout:hover { background-color: #f8f9fa; color: #0b3d25; }
        .mobile-header { display: none; background-color: var(--vsu-green); color: white; padding: 12px 20px; align-items: center; justify-content: space-between; position: fixed; top: 0; left: 0; right: 0; z-index: 1040; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); z-index: 1045; }
        .main-content { margin-left: 260px; flex-grow: 1; padding: 30px; width: calc(100% - 260px); }
        .page-card { background: #ffffff; border-radius: 14px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid #e9ecef; padding: 25px; }

        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .mobile-header { display: flex; }
            .sidebar-overlay.show { display: block; }
            .main-content { margin-left: 0; width: 100%; padding: 20px; padding-top: 80px; }
        }
    </style>
</head>
<body>

    <div class="mobile-header">
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn text-white p-1" id="sidebarToggle" aria-label="Toggle Menu">
                <i class="fa-solid fa-bars fa-lg"></i>
            </button>
            <span class="fw-bold"><i class="fa-solid fa-qrcode text-warning me-1"></i> VSU VMS PORTAL</span>
        </div>
        <span class="small text-warning bg-dark px-2 py-1 rounded">Visitor</span>
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div>
                <i class="fa-solid fa-qrcode text-warning"></i> VSU <span>VMS PORTAL</span>
            </div>
            <button type="button" class="btn text-white d-lg-none p-1" id="sidebarClose" aria-label="Close Menu">
                <i class="fa-solid fa-xmark fa-lg"></i>
            </button>
        </div>
        <div class="px-3 pt-3 pb-2 text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Workspace</div>
        <ul class="sidebar-menu">
            <li><a href="visitor_profile.php"><i class="fa-solid fa-user"></i> My Profile</a></li>
            <li><a href="visitor_qrcode.php"><i class="fa-solid fa-qrcode"></i> My QR Code</a></li>
            <li><a href="visitor_history.php" class="active"><i class="fa-solid fa-clock-rotate-left"></i> Visit History</a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="logout_visitor.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket me-1"></i> Logout</a>
        </div>
    </div>

    <div class="main-content">
        <div class="mb-4">
            <small class="text-muted">Visayas State University • Visitor Dashboard</small>
            <h1 class="fw-bold text-dark">Visit History</h1>
            <p class="text-muted small">Log records of your campus gate check-ins and check-outs.</p>
        </div>

        <div class="page-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small">
                        <tr>
                            <th>Check-In Time</th>
                            <th>Check-Out Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($logs) > 0): ?>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= $log['check_in'] ? htmlspecialchars($log['check_in']) : '<span class="text-muted">N/A</span>' ?></td>
                                <td><?= $log['check_out'] ? htmlspecialchars($log['check_out']) : '<span class="text-muted">Still Inside</span>' ?></td>
                                <td>
                                    <?php if ($log['status'] === 'Inside'): ?>
                                        <span class="badge bg-success">Inside</span>
                                    <?php elseif ($log['status'] === 'Checked Out'): ?>
                                        <span class="badge bg-secondary">Checked Out</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark"><?= htmlspecialchars($log['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4 small">No gate access logs recorded yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarClose = document.getElementById('sidebarClose');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function closeDrawer() {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        }

        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.add('show');
            sidebarOverlay.classList.add('show');
        });

        sidebarClose.addEventListener('click', closeDrawer);
        sidebarOverlay.addEventListener('click', closeDrawer);
    </script>
</body>
</html>
