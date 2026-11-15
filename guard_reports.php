<?php
session_start();
require_once 'config.php';

// 1. Siguraduhing naka-login at guard ang user (o admin)
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'guard' && $_SESSION['role'] !== 'admin')) {
    header("Location: login.php");
    exit;
}

// Handle quick check-out action from the guard report page if needed
if (isset($_GET['checkout_id'])) {
    $log_id = intval($_GET['checkout_id']);
    $stmt = $pdo->prepare("UPDATE visitor_logs SET check_out = NOW(), status = 'Checked Out' WHERE id = ? AND status = 'Inside'");
    $stmt->execute([$log_id]);
    header("Location: guard_reports.php");
    exit;
}

// Kunin ang lahat ng visitor logs mula sa database para sa gate report
$query = "SELECT l.id AS log_id, v.visitor_id, v.full_name, v.office_to_visit, v.purpose, l.check_in, l.check_out, l.status 
          FROM visitor_logs l 
          JOIN visitors v ON l.visitor_id = v.visitor_id 
          ORDER BY l.id DESC";
$stmt = $pdo->query($query);
$logs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Guard Gate Logs Report - VSU VMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f3f6f4; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    </style>
</head>
<body>

<!-- NAVIGATION BAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-success mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold" href="guard_scan.php"><i class="fa-solid fa-shield-halved me-2"></i> VSU Guard Portal</a>
    <div>
        <span class="text-white me-3 small"><i class="fa-solid fa-user-shield me-1"></i> Guard: <?= htmlspecialchars($_SESSION['username'] ?? '') ?></span>
        <a href="guard_scan.php" class="btn btn-outline-light btn-sm me-1"><i class="fa-solid fa-qrcode me-1"></i> Scanner</a>
        <a href="logout.php" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to log out?');"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>
  </div>
</nav>

<div class="container mb-5">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-success"><i class="fa-solid fa-clipboard-user me-2"></i> Gate Access & Visitor Logs Report</h5>
            <span class="badge bg-success px-3 py-2">Live Gate Record</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Visitor ID</th>
                            <th>Visitor Name</th>
                            <th>Office Visited</th>
                            <th>Purpose</th>
                            <th>Check-In Time</th>
                            <th>Check-Out Time</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($logs) > 0): ?>
                            <?php $no = 1; foreach ($logs as $log): ?>
                            <tr>
                                <td class="fw-bold"><?= $no++ ?></td>
                                <td class="font-monospace small"><?= htmlspecialchars($log['visitor_id']) ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($log['full_name']) ?></td>
                                <td><?= htmlspecialchars($log['office_to_visit']) ?></td>
                                <td><?= htmlspecialchars($log['purpose']) ?></td>
                                <td><?= htmlspecialchars($log['check_in']) ?></td>
                                <td><?= $log['check_out'] ? htmlspecialchars($log['check_out']) : '<span class="text-muted small italic">Still Inside</span>' ?></td>
                                <td>
                                    <?php if ($log['status'] === 'Inside'): ?>
                                        <span class="badge bg-success">Inside</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Checked Out</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($log['status'] === 'Inside'): ?>
                                        <a href="guard_reports.php?checkout_id=<?= $log['log_id'] ?>" 
                                           class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Check out <?= htmlspecialchars($log['full_name']) ?>?');">
                                           Check Out
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">Done</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center p-4 text-muted">No gate access logs found yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>