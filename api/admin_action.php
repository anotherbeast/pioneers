<?php
// admin_action.php — Handles admin actions: reset password, ban/unban, delete
session_start();
require_once __DIR__ . '/config.php';
function respond($ok, $msg='') {
    header('Content-Type: application/json');
    echo json_encode(['ok'=>$ok, 'msg'=>$msg]);
    exit;
}
if (!isset($_SESSION['member_id']) || ($_SESSION['role'] ?? '') !== 'admin') respond(false, 'Not authorized.');
$data = json_decode(file_get_contents('php://input'), true);
$id = intval($data['id'] ?? 0);
$action = $data['action'] ?? '';
if (!$id || !$action) respond(false, 'Invalid input.');
try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if ($action === 'reset') {
        $newpw = bin2hex(random_bytes(4));
        $hash = password_hash($newpw, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE members SET password = ? WHERE id = ?');
        $stmt->execute([$hash, $id]);
        // Optionally email new password to user here
        respond(true, 'Password reset. New password: ' . $newpw);
    } elseif ($action === 'ban') {
        $stmt = $pdo->prepare('SELECT status FROM members WHERE id = ?');
        $stmt->execute([$id]);
        $status = $stmt->fetchColumn();
        $newStatus = ($status === 'banned') ? 'active' : 'banned';
        $stmt = $pdo->prepare('UPDATE members SET status = ? WHERE id = ?');
        $stmt->execute([$newStatus, $id]);
        respond(true, 'User status updated.');
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM members WHERE id = ?');
        $stmt->execute([$id]);
        respond(true, 'User deleted.');
    } else {
        respond(false, 'Unknown action.');
    }
} catch (PDOException $e) {
    respond(false, 'Database error.');
}
