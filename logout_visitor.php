<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config.php';

// 1. If a visitor checkout ID is provided, check them out of the campus
if (isset($_GET['log_id'])) {
    $log_id = intval($_GET['log_id']);
    
    $stmt = $pdo->prepare("UPDATE visitor_logs SET check_out = NOW(), status = 'Checked Out' WHERE id = ? AND status = 'Inside'");
    $stmt->execute([$log_id]);
    
    ob_end_clean();
    header("Location: register.php");
    echo "<script>window.location.href='register.php';</script>";
    exit;
}

// 2. Otherwise, perform a full system account logout (Destroys user session)
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

ob_end_clean();
header("Location: login.php");
echo "<script>window.location.href='login.php';</script>";
exit;
?>