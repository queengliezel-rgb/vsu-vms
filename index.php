<?php
ob_start();
session_start();
require_once 'config.php';

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// 2. Restrict access: Only admins can view the dashboard
if ($_SESSION['role'] !== 'admin') {
    header("Location: scan.php");
    exit;
}

if (isset($_GET['logout_id'])) {
    $log_id = intval($_GET['logout_id']);
    
    $stmt = $pdo->prepare("UPDATE visitor_logs SET check_out = NOW(), status = 'Checked Out' WHERE id = ? AND status = 'Inside'");
    $stmt->execute([$log_id]);
    
    ob_end_clean();
    header("Location: index.php");
    exit;
}

// Search and filter inputs
$search = trim($_GET['search'] ?? '');
$status_filter = trim($_GET['status_filter'] ?? '');

$total_registered = $pdo->query("SELECT COUNT(*) FROM visitors")->fetchColumn();
$currently_inside = $pdo->query("SELECT COUNT(*) FROM visitor_logs WHERE status = 'Inside'")->fetchColumn();

// Build query with search and filters
$query = "SELECT v.visitor_id, v.full_name, v.contact_no, v.address, v.sex, v.email, v.profile_pic, v.office_to_visit, v.purpose, l.id AS log_id, l.check_in, l.check_out, l.status 
          FROM visitors v 
          LEFT JOIN visitor_logs l ON v.visitor_id = l.visitor_id WHERE 1=1";

$params = [];

if ($search !== '') {
    $query .= " AND (v.full_name LIKE ? OR v.visitor_id LIKE ? OR v.office_to_visit LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status_filter === 'Inside') {
    $query .= " AND l.status = 'Inside'";
} elseif ($status_filter === 'Checked Out') {
    $query .= " AND l.status = 'Checked Out'";
} elseif ($status_filter === 'Registered Only') {
    $query .= " AND l.status IS NULL";
}

$query .= " ORDER BY v.id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>VSU Visitor Management System - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background-color: #fff !important; }
            .card { border: none !important; box-shadow: none !important; }
        }
    </style>
</head>
<body class="bg-light">

<!-- NAVIGATION BAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-success mb-4 no-print">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php"><i class="fa-solid fa-university me-2"></i> Visayas State University - Visitor System</a>
    <div class="d-flex align-items-center">
        <span class="text-white me-3 small"><i class="fa-solid fa-user-shield me-1"></i> Admin: <?= htmlspecialchars($_SESSION['username'] ?? '') ?></span>
        <a href="visitor_records.php" class="btn btn-outline-light btn-sm me-1"><i class="fa-solid fa-qrcode"></i> QR Directory</a>
        <a href="register.php" class="btn btn-light btn-sm me-1"><i class="fa-solid fa-user-plus"></i> Register Visitor</a>
        <a href="logout.php" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to log out?');"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>
  </div>
</nav>

<div class="container mb-5">
    <div class="d-none d-print-block text-center mb-4">
        <h3 class="fw-bold mb-0">VISAYAS STATE UNIVERSITY</h3>
        <p class="text-muted">Visitor Log Summary & Official Report</p>
        <hr>
    </div>

    <!-- METRIC CARDS -->
    <div class="row mb-4 no-print">
        <div class="col-md-6">
            <div class="card bg-primary text-white p-3 shadow-sm border-0 rounded-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="fw-bold mb-0"><?= $total_registered ?></h3>
                        <p class="mb-0 small opacity-75">Total Registered Visitors</p>
                    </div>
                    <i class="fa-solid fa-users fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-success text-white p-3 shadow-sm border-0 rounded-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="fw-bold mb-0"><?= $currently_inside ?></h3>
                        <p class="mb-0 small opacity-75">Visitors Currently Inside Campus</p>
                    </div>
                    <i class="fa-solid fa-building-user fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLE SECTION WITH SEARCH & FILTER -->
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="mb-0 fw-bold text-success"><i class="fa-solid fa-clipboard-list me-2"></i> Visitor Logs & Reports</h5>
            
            <!-- Search & Filter Form -->
            <form method="GET" class="d-flex gap-2 no-print">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name, ID, office..." value="<?= htmlspecialchars($search) ?>">
                <select name="status_filter" class="form-select form-select-sm" style="width: 140px;">
                    <option value="">All Status</option>
                    <option value="Inside" <?= $status_filter === 'Inside' ? 'selected' : '' ?>>Inside</option>
                    <option value="Checked Out" <?= $status_filter === 'Checked Out' ? 'selected' : '' ?>>Checked Out</option>
                    <option value="Registered Only" <?= $status_filter === 'Registered Only' ? 'selected' : '' ?>>Registered Only</option>
                </select>
                <button type="submit" class="btn btn-success btn-sm"><i class="fa-solid fa-filter"></i></button>
                <a href="index.php" class="btn btn-outline-secondary btn-sm" title="Reset"><i class="fa-solid fa-rotate"></i></a>
            </form>

            <div class="no-print">
                <a href="visitor_records.php" class="btn btn-outline-primary btn-sm me-1"><i class="fa-solid fa-qrcode"></i> View QR Passes</a>
                <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-print"></i> Print Report</button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Photo</th>
                            <th>Visitor ID</th>
                            <th>Name</th>
                            <th>Office Visited</th>
                            <th>Purpose</th>
                            <th>Check-In Time</th>
                            <th>Status</th>
                            <th class="no-print text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($logs) > 0): ?>
                            <?php 
                            $no = 1;
                            foreach ($logs as $log): 
                                // Fixed profile picture path resolution
                                $raw_pic = $log['profile_pic'] ?? '';
                                if (!empty($raw_pic)) {
                                    $pic_path = (strpos($raw_pic, 'uploads/') === 0) ? $raw_pic : "uploads/" . $raw_pic;
                                    $pic = file_exists($pic_path) ? $pic_path : "uploads/default.png";
                                } else {
                                    $pic = "uploads/default.png";
                                }
                                
                                // Safe ID variables for modal attributes
                                $modal_id = 'profileModal' . preg_replace('/[^a-zA-Z0-9_-]/', '', $log['visitor_id']);
                            ?>
                            <tr>
                                <td class="fw-bold"><?= $no++ ?></td>
                                <td>
                                    <img src="<?= htmlspecialchars($pic) ?>" class="rounded-circle border" width="38" height="38" style="object-fit: cover;">
                                </td>
                                <td class="font-monospace small"><?= htmlspecialchars($log['visitor_id']) ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($log['full_name']) ?></td>
                                <td><?= htmlspecialchars($log['office_to_visit']) ?></td>
                                <td><?= htmlspecialchars($log['purpose']) ?></td>
                                <td><?= $log['check_in'] ? $log['check_in'] : '<span class="text-muted small italic">Not yet checked in</span>' ?></td>
                                <td>
                                    <?php if ($log['status'] === 'Inside'): ?>
                                        <span class="badge bg-success">Inside</span>
                                    <?php elseif ($log['status'] === 'Checked Out'): ?>
                                        <span class="badge bg-secondary">Checked Out</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Registered Only</span>
                                    <?php endif; ?>
                                </td>
                                <td class="no-print text-center">
                                    <!-- View Profile Button Modal Trigger -->
                                    <button type="button" class="btn btn-info btn-sm text-white me-1" data-bs-toggle="modal" data-bs-target="#<?= $modal_id ?>">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>

                                    <?php if ($log['status'] === 'Inside'): ?>
                                        <a href="index.php?logout_id=<?= $log['log_id'] ?>" 
                                           class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Log out <?= htmlspecialchars($log['full_name']) ?>?');">
                                           Log Out
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <!-- Modal for Visitor Profile Details -->
                            <div class="modal fade" id="<?= $modal_id ?>" tabindex="-1" aria-hidden="true">
                              <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                  <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title"><i class="fa-solid fa-id-card me-2"></i> Visitor Profile Details</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body text-center p-4">
                                      <img src="<?= htmlspecialchars($pic) ?>" class="rounded-circle border mb-3 shadow-sm" width="110" height="110" style="object-fit: cover;">
                                      <h4 class="fw-bold mb-1"><?= htmlspecialchars($log['full_name']) ?></h4>
                                      <p class="text-muted font-monospace small mb-3"><?= htmlspecialchars($log['visitor_id']) ?></p>
                                      
                                      <ul class="list-group list-group-flush text-start small">
                                          <li class="list-group-item"><strong>Contact Number:</strong> <?= htmlspecialchars($log['contact_no'] ?? 'N/A') ?></li>
                                          <li class="list-group-item"><strong>Email Address:</strong> <?= htmlspecialchars($log['email'] ?? 'N/A') ?></li>
                                          <li class="list-group-item"><strong>Sex:</strong> <?= htmlspecialchars($log['sex'] ?? 'N/A') ?></li>
                                          <li class="list-group-item"><strong>Address:</strong> <?= htmlspecialchars($log['address'] ?? 'N/A') ?></li>
                                          <li class="list-group-item"><strong>Office to Visit:</strong> <?= htmlspecialchars($log['office_to_visit']) ?></li>
                                          <li class="list-group-item"><strong>Purpose:</strong> <?= htmlspecialchars($log['purpose']) ?></li>
                                      </ul>
                                  </div>
                                  <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <!-- End Modal -->

                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="9" class="text-center p-4 text-muted">No visitor records found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>