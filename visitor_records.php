<?php
require_once 'config.php';

$visitors = $pdo->query("SELECT * FROM visitors ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>VSU Visitor Records & QR Codes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            .card { border: 1px solid #ccc !important; page-break-inside: avoid; }
        }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-success mb-4 no-print">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">Visayas State University - VMS</a>
    <div>
        <a href="index.php" class="btn btn-outline-light btn-sm me-1">Dashboard</a>
        <a href="register.php" class="btn btn-light btn-sm me-1">Register Visitor</a>
        <a href="scan.php" class="btn btn-warning btn-sm">Scan Pass</a>
    </div>
  </div>
</nav>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h2>Registered Visitors & QR Codes</h2>
        <button onclick="window.print()" class="btn btn-secondary">Print All Passes</button>
    </div>

    <div class="row">
        <?php if (count($visitors) > 0): ?>
            <?php foreach ($visitors as $v): ?>
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm text-center p-3 h-100 bg-white">
                    <h6 class="fw-bold text-success mb-0">VISAYAS STATE UNIVERSITY</h6>
                    <small class="text-muted">VISITOR PASS</small>
                    
                    <!-- Profile Picture Display -->
                    <div class="d-flex justify-content-center my-2">
                        <?php if (!empty($v['profile_pic'])): ?>
                            <img src="<?= $v['profile_pic'] ?>" alt="Profile" class="rounded-circle border" width="70" height="70" style="object-fit: cover;">
                        <?php else: ?>
                            <div class="rounded-circle border bg-light text-success fw-bold d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; font-size: 1.5rem;">
                                <?= strtoupper(substr($v['full_name'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div id="qr-<?= $v['id'] ?>" class="d-flex justify-content-center my-2"></div>
                    
                    <h5 class="card-title fw-bold text-dark mb-1"><?= htmlspecialchars($v['full_name']) ?></h5>
                    <p class="mb-1 text-muted small"><strong>ID:</strong> <?= $v['visitor_id'] ?></p>
                    <p class="mb-1 small"><strong>Contact:</strong> <?= htmlspecialchars($v['contact_no']) ?></p>
                    <p class="mb-1 small"><strong>Office:</strong> <?= htmlspecialchars($v['office_to_visit']) ?></p>
                    <p class="mb-2 small"><strong>Purpose:</strong> <?= htmlspecialchars($v['purpose']) ?></p>
                    
                    <div class="mt-auto no-print">
                        <button onclick="window.print()" class="btn btn-sm btn-outline-success">Print Pass</button>
                    </div>
                </div>
            </div>

            <script>
                new QRCode(document.getElementById("qr-<?= $v['id'] ?>"), {
                    text: "<?= $v['visitor_id'] ?>",
                    width: 110,
                    height: 110
                });
            </script>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center">No visitor records found. Register a visitor first.</div>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
