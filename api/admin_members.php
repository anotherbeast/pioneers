<?php
// admin_members.php — Returns all members for admin dashboard
session_start();
require_once __DIR__ . '/config.php';
function respond($ok, $members=[]) {
    header('Content-Type: application/json');
    echo json_encode(['ok'=>$ok, 'members'=>$members]);
    exit;
}
// Only allow admin (role=admin)
if (!isset($_SESSION['member_id']) || ($_SESSION['role'] ?? '') !== 'admin') respond(false);
try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query('SELECT id, name, email, joined_at, role, status FROM members ORDER BY joined_at DESC');
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    respond(true, $members);
} catch (PDOException $e) {
    respond(false);
}
