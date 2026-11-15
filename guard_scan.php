<?php
session_start();
require_once 'config.php';

// 1. Suriin kung naka-login at kung Guard/Admin ang papel
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$message = '';
$msg_type = '';
$scanned_visitor = null;

// 2. Aksyon kapag may nag-scan o nag-submit ng Visitor ID
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['visitor_id'])) {
    $visitor_id = trim($_POST['visitor_id']);

    if (!empty($visitor_id)) {
        // Hanapin ang bisita sa database
        $stmt = $pdo->prepare("SELECT * FROM visitors WHERE visitor_id = ?");
        $stmt->execute([$visitor_id]);
        $visitor = $stmt->fetch();

        if ($visitor) {
            // Suriin kung ang bisita ay kasalukuyang 'Inside'
            $check_status = $pdo->prepare("SELECT * FROM visitor_logs WHERE visitor_id = ? AND status = 'Inside' ORDER BY id DESC LIMIT 1");
            $check_status->execute([$visitor_id]);
            $active_log = $check_status->fetch();

            if ($active_log) {
                // Kung nasa loob na, i-check-out na siya
                $update_log = $pdo->prepare("UPDATE visitor_logs SET check_out = NOW(), status = 'Checked Out' WHERE id = ?");
                $update_log->execute([$active_log['id']]);
                
                $message = "Successfully Checked Out: " . htmlspecialchars($visitor['full_name']);
                $msg_type = "warning";
                $scanned_visitor = $visitor;
            } else {
                // Kung wala sa loob, gawan siya ng bagong Check-In log
                $insert_log = $pdo->prepare("INSERT INTO visitor_logs (visitor_id, check_in, status) VALUES (?, NOW(), 'Inside')");
                $insert_log->execute([$visitor_id]);
                
                $message = "Successfully Checked In: " . htmlspecialchars($visitor['full_name']);
                $msg_type = "success";
                $scanned_visitor = $visitor;
            }
        } else {
            $message = "Error: Visitor ID not found in VSU records.";
            $msg_type = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VSU Guard Portal - Gate Scanner</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- ZXing Library para sa QR Code Camera Scanning -->
    <script type="text/javascript" src="https://unpkg.com/@zxing/library@latest"></script>
    
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
            margin: 0;
        }

        .navbar-vsu {
            background-color: var(--vsu-green);
        }

        .scanner-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            padding: 30px;
            border: 1px solid #e9ecef;
        }

        .btn-gold {
            background-color: var(--vsu-gold);
            color: #000;
            font-weight: 700;
            border: none;
            transition: background-color 0.2s;
        }

        .btn-gold:hover {
            background-color: var(--vsu-gold-hover);
            color: #000;
        }

        #video {
            width: 100%;
            max-width: 400px;
            border-radius: 12px;
            border: 3px solid var(--vsu-green);
            background: #000;
        }
    </style>
</head>
<body>

    <!-- Guard Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-vsu shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="guard_scan.php">
                <i class="fa-solid fa-shield-halved text-warning me-2"></i>VSU Gate Security Portal
            </a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3 small"><i class="fa-solid fa-user-shield me-1"></i> Guard: <?= htmlspecialchars($_SESSION['username'] ?? 'Security') ?></span>
                <a href="guard_reports.php" class="btn btn-outline-light btn-sm me-2"><i class="fa-solid fa-clipboard-list me-1"></i> Gate Logs Report</a>
                <a href="logout.php" class="btn btn-danger btn-sm" onclick="return confirm('Log out from Guard Portal?');">
                    <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container my-auto py-4" style="max-width: 800px;">
        
        <div class="scanner-card text-center">
            <h2 class="fw-bold text-success mb-2">Gate QR Pass Scanner</h2>
            <p class="text-muted small mb-4">Scan the visitor's QR code or manually enter their Visitor ID to process entry/exit.</p>

            <?php if ($message): ?>
                <div class="alert alert-<?= $msg_type ?> py-3 px-3 mb-4 fw-bold rounded-3 shadow-sm">
                    <i class="fa-solid fa-circle-info me-2"></i> <?= $message ?>
                </div>
            <?php endif; ?>

            <!-- Camera View -->
            <div class="mb-4">
                <video id="video" class="mx-auto shadow-sm"></video>
                <div class="mt-2">
                    <button id="startButton" class="btn btn-outline-success btn-sm px-4 me-2"><i class="fa-solid fa-camera me-1"></i> Start Camera</button>
                    <button id="resetButton" class="btn btn-outline-secondary btn-sm px-4"><i class="fa-solid fa-camera-slash me-1"></i> Stop Camera</button>
                </div>
            </div>

            <!-- Manual Form Fallback -->
            <form method="POST" id="scanForm" class="row g-2 justify-content-center">
                <div class="col-md-8">
                    <input type="text" name="visitor_id" id="visitor_id" class="form-control form-control-lg text-center font-monospace" placeholder="VSU-V-YYYYMMDD-XXX" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-gold btn-lg w-100 fw-bold">
                        <i class="fa-solid fa-check me-1"></i> Submit
                    </button>
                </div>
            </form>
        </div>

        <?php if ($scanned_visitor): ?>
            <!-- Recent Scanned Details -->
            <div class="scanner-card mt-4 bg-light">
                <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-id-card text-success me-2"></i> Last Scanned Visitor Details</h5>
                <div class="row text-start small">
                    <div class="col-md-6 mb-2"><strong>Name:</strong> <?= htmlspecialchars($scanned_visitor['full_name']) ?></div>
                    <div class="col-md-6 mb-2"><strong>ID:</strong> <span class="font-monospace"><?= htmlspecialchars($scanned_visitor['visitor_id']) ?></span></div>
                    <div class="col-md-6 mb-2"><strong>Office Visited:</strong> <?= htmlspecialchars($scanned_visitor['office_to_visit']) ?></div>
                    <div class="col-md-6 mb-2"><strong>Purpose:</strong> <?= htmlspecialchars($scanned_visitor['purpose']) ?></div>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <!-- ZXing Camera Scanner Logic -->
    <script>
        window.addEventListener('load', function () {
            let selectedDeviceId;
            const codeReader = new ZXing.BrowserQRCodeReader();
            
            codeReader.listVideoInputDevices()
                .then((videoInputDevices) => {
                    selectedDeviceId = videoInputDevices[0].deviceId;
                    
                    document.getElementById('startButton').addEventListener('click', () => {
                        codeReader.decodeOnceFromVideoDevice(selectedDeviceId, 'video').then((result) => {
                            document.getElementById('visitor_id').value = result.text;
                            document.getElementById('scanForm').submit();
                        }).catch((err) => {
                            console.error(err);
                        });
                    });

                    document.getElementById('resetButton').addEventListener('click', () => {
                        codeReader.reset();
                    });
                })
                .catch((err) => {
                    console.error(err);
                });
        });
    </script>
</body>
</html>