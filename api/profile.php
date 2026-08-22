<?php
// profile.php — Handles member profile updates (name, email, password, avatar)
session_start();
require_once __DIR__ . '/config.php';

function respond($ok, $msg) {
    header('Content-Type: application/json');
    echo json_encode(['ok'=>$ok, 'msg'=>$msg]);
    exit;
}
if (!isset($_SESSION['member_id'])) respond(false, 'Not logged in.');
$member_id = $_SESSION['member_id'];
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$avatar = $_FILES['avatar'] ?? null;
if (!$name || !$email) respond(false, 'Name and email required.');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) respond(false, 'Invalid email.');
try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Check for duplicate email (other users)
    $stmt = $pdo->prepare('SELECT id FROM members WHERE email = ? AND id != ?');
    $stmt->execute([$email, $member_id]);
    if ($stmt->fetch()) respond(false, 'Email already in use.');
    // Handle avatar upload
    $avatarPath = null;
    if ($avatar && $avatar['tmp_name']) {
        $ext = strtolower(pathinfo($avatar['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) respond(false, 'Invalid avatar format.');
        $avatarPath = '/uploads/avatars/' . $member_id . '-' . time() . '.' . $ext;
        $dest = __DIR__ . '/../public' . $avatarPath;
        if (!move_uploaded_file($avatar['tmp_name'], $dest)) respond(false, 'Avatar upload failed.');
    }
    // Update fields
    $fields = 'name = ?, email = ?';
    $params = [$name, $email];
    if ($password && strlen($password) >= 8) {
        $fields .= ', password = ?';
        $params[] = password_hash($password, PASSWORD_DEFAULT);
    }
    if ($avatarPath) {
        $fields .= ', avatar = ?';
        $params[] = $avatarPath;
    }
    $params[] = $member_id;
    $stmt = $pdo->prepare("UPDATE members SET $fields WHERE id = ?");
    $stmt->execute($params);
    respond(true, 'Profile updated.');
} catch (PDOException $e) {
    respond(false, 'Database error.');
}
