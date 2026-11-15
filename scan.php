<?php
session_start();
require_once 'config.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>VSU Visitor Scanner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode"></script>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-success mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#">Visayas State University - Gate Scanner</a>
    <div>
        <span class="text-white me-3 small">User: <?= htmlspecialchars($_SESSION['username'] ?? '') ?></span>
        <?php if (isset($_SESSION['role']) &&$_SESSION['role'] === 'admin'): ?>
            <a href="index.php" class="btn btn-outline-light btn-sm me-1">Dashboard</a>
        <?php else: ?>
            <a href="visitor_profile.php" class="btn btn-outline-light btn-sm me-1">My Profile & History</a>
        <?php endif; ?>
        <a href="logout.php" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to log out?');">Logout</a>
    </div>
  </div>
</nav>

<div class="container text-center">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Scan Visitor QR Code</h5>
                </div>
                <div class="card-body">
                    <div id="reader" style="width: 100%;"></div>
                    <div id="result" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function onScanSuccess(decodedText, decodedResult) {
    document.getElementById('result').innerHTML = `<div class="alert alert-info">Processing ID: ${decodedText}...</div>`;
    
    fetch('log_visitor.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ visitor_id: decodedText })
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            document.getElementById('result').innerHTML = `
                <div class="alert alert-success">
                    <h4>${data.action.toUpperCase()} SUCCESSFUL</h4>
                    <p class="mb-0"><strong>Visitor:</strong> ${data.name}</p>
                    <p class="mb-0"><strong>Time:</strong> ${data.time}</p>
                </div>`;
        } else {
            document.getElementById('result').innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
        }
    })
    .catch(err => {
        document.getElementById('result').innerHTML = `<div class="alert alert-danger">Error processing request.</div>`;
    });
}

let html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 });
html5QrcodeScanner.render(onScanSuccess);
</script>
</body>
</html>