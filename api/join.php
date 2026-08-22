<?php
// join.php — Handles new member registration
// Requires: PHPMailer (for email), PDO (for MySQL)

require_once __DIR__ . '/config.php';

// --- UTILITIES ---
function sendVerificationEmail($to, $name, $token) {
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
        $mail->Subject = 'Verify your email — SundayLaw.com';
        $link = 'https://sundaylaw.com/api/verify_email.php?token=' . urlencode($token);
        $mail->Body = "Hi $name,\n\nPlease verify your email by clicking the link below:\n$link\n\nIf you did not register, ignore this email.";
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

// --- MAIN ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(false, 'Invalid request.');
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
if (!$name || !$email || !$password) respond(false, 'All fields are required.');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) respond(false, 'Invalid email address.');
if (strlen($password) < 8) respond(false, 'Password must be at least 8 characters.');

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Check for duplicate email
    $stmt = $pdo->prepare('SELECT id FROM members WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) respond(false, 'Email already registered.');
    // Insert new member with verification token
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $token = bin2hex(random_bytes(32));
    $stmt = $pdo->prepare('INSERT INTO members (name, email, password, joined_at, email_verification_token) VALUES (?, ?, ?, NOW(), ?)');
    $stmt->execute([$name, $email, $hash, $token]);
    sendVerificationEmail($email, $name, $token);
    header('Location: /signup-success.html');
    exit;
} catch (PDOException $e) {
    respond(false, 'Database error.');
}
