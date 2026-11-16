<?php
$host = "ballast.proxy.rlwy.net";
$port = "42846";
$db   = "railway";     
$user = "root";             
$pass = "WDMPFCXhKSKJQcXJcrQKDlMUvoIEyKpR";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
        $pdo->setAttribute(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>
