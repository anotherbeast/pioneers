<?php
// forgot.php — Handles password reset requests
// Requires: PHPMailer, PDO
require_once __DIR__ . '/config.php';

function sendResetEmail($to, $name, $token) {
    require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/PHPMailer/src/SMTP.php';
    require_once __DIR__ . '/PHPMailer/src/Exception.php';
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = $MAIL_USER;
        $mail->Password = $MAIL_PASS;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->setFrom($MAIL_FROM, $MAIL_FROM_NAME);
        $mail->addAddress($to, $name);
        $mail->Subject = 'Password Reset — SundayLaw.com';
        $mail->Body = "Hi $name,\n\nTo reset your password, click the link below:\nhttps://sundaylaw.com/reset-password.php?token=$token\n\nIf you did not request this, ignore this email.";
        $mail->send();
    } catch (Exception $e) {
        // Log error or ignore
    }
}
function respond($ok, $msg) {
    header('Content-Type: application/json');
    echo json_encode(['ok'=>$ok, 'msg'=>$msg]);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(false, 'Invalid request.');
$email = trim($_POST['email'] ?? '');
if (!$email) respond(false, 'Email required.');
try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->prepare('SELECT id, name FROM members WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) respond(false, 'No account found.');
    // Generate token
    $token = bin2hex(random_bytes(32));
    $stmt = $pdo->prepare('UPDATE members SET reset_token = ?, reset_token_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = ?');
    $stmt->execute([$token, $user['id']]);
    sendResetEmail($email, $user['name'], $token);
    respond(true, 'Reset link sent. Check your email.');
} catch (PDOException $e) {
    respond(false, 'Database error.');
}
