<?php
header('Content-Type: application/json');
require_once 'config.php';

$input = json_decode(file_get_contents('php://input'), true);
$visitor_id = $input['visitor_id'] ?? '';

if (empty($visitor_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid QR Code.']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM visitors WHERE visitor_id = ?");
$stmt->execute([$visitor_id]);
$visitor = $stmt->fetch();

if (!$visitor) {
    echo json_encode(['status' => 'error', 'message' => 'Visitor not found in VSU records.']);
    exit;
}

$log_stmt = $pdo->prepare("SELECT * FROM visitor_logs WHERE visitor_id = ? AND status = 'Inside' ORDER BY id DESC LIMIT 1");
$log_stmt->execute([$visitor_id]);
$active_log = $log_stmt->fetch();

if ($active_log) {
    $update = $pdo->prepare("UPDATE visitor_logs SET check_out = NOW(), status = 'Checked Out' WHERE id = ?");
    $update->execute([$active_log['id']]);
    
    echo json_encode([
        'status' => 'success',
        'action' => 'Check-Out',
        'name' => $visitor['full_name'],
        'time' => date('Y-m-d H:i:s')
    ]);
} else {
    $insert = $pdo->prepare("INSERT INTO visitor_logs (visitor_id, check_in, status) VALUES (?, NOW(), 'Inside')");
    $insert->execute([$visitor_id]);
    
    echo json_encode([
        'status' => 'success',
        'action' => 'Check-In',
        'name' => $visitor['full_name'],
        'time' => date('Y-m-d H:i:s')
    ]);
}
?>