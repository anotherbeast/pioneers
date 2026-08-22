<?php
// verify_email.php — Handles email verification links
require_once __DIR__ . '/config.php';
function respond($ok, $msg) {
    echo "<html><body style='font-family:sans-serif;text-align:center;padding:40px;'><h2>" . htmlspecialchars($msg) . "</h2></body></html>";
    exit;
}
$token = $_GET['token'] ?? '';
if (!$token) respond(false, 'Invalid verification link.');
try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->prepare('SELECT id FROM members WHERE email_verification_token = ? AND email_verified = 0');
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) respond(false, 'Invalid or expired verification link.');
    $stmt = $pdo->prepare('UPDATE members SET email_verified = 1, email_verification_token = NULL WHERE id = ?');
    $stmt->execute([$user['id']]);
    respond(true, 'Your email has been verified! You may now log in.');
} catch (PDOException $e) {
    respond(false, 'Database error.');
}
