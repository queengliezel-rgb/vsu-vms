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
                    
                    <div id="qr-<?= $v['id'] ?>" class="d-flex justify-content-center my-3"></div>
                    
                    <h5 class="card-title fw-bold text-dark"><?= htmlspecialchars($v['full_name']) ?></h5>
                    <p class="mb-1 text-muted"><strong>ID:</strong> <?= $v['visitor_id'] ?></p>
                    <p class="mb-1"><strong>Contact:</strong> <?= htmlspecialchars($v['contact_no']) ?></p>
                    <p class="mb-1"><strong>Office:</strong> <?= htmlspecialchars($v['office_to_visit']) ?></p>
                    <p class="mb-2"><strong>Purpose:</strong> <?= htmlspecialchars($v['purpose']) ?></p>
                    
                    <div class="mt-auto no-print">
                        <button onclick="printSinglePass('qr-pass-<?= $v['id'] ?>')" class="btn btn-sm btn-outline-success">Print Pass</button>
                    </div>
                </div>
            </div>

            <script>
                new QRCode(document.getElementById("qr-<?= $v['id'] ?>"), {
                    text: "<?= $v['visitor_id'] ?>",
                    width: 130,
                    height: 130
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