<?php
// register.php — Handles member registration (user-facing)
session_start();
require_once __DIR__ . '/config.php';
if (isset($_SESSION['member_id'])) {
    header('Location: /api/members/dashboard.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    if (!$name || !$email || !$password || !$confirm) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
        $stmt = $pdo->prepare('SELECT id FROM members WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $token = bin2hex(random_bytes(32));
            $stmt = $pdo->prepare('INSERT INTO members (name, email, password, joined_at, email_verification_token) VALUES (?, ?, ?, NOW(), ?)');
            $stmt->execute([$name, $email, $hash, $token]);
            // Send verification email
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
                $mail->addAddress($email, $name);
                $mail->Subject = 'Verify your email — SundayLaw.com';
                $link = 'https://sundaylaw.com/api/verify_email.php?token=' . urlencode($token);
                $mail->Body = "Hi $name,\n\nPlease verify your email by clicking the link below:\n$link\n\nIf you did not register, ignore this email.";
                $mail->send();
            } catch (Exception $e) {}
            header('Location: /signup-success.html');
            exit;
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register — SundayLaw.com</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="/_astro/index@_@astro.-cMZqUxg.css">
  <link rel="stylesheet" href="/_astro/ShareBar.DN2cI-55.css">
  <link rel="icon" href="/favicon.ico">
</head>
<body>
  <nav class="sn" id="navbar">
    <div class="sn-wrap">
      <div class="sn-row sn-top">
        <a class="sn-brand" href="/">
          <img src="/images/shield_sundaylaw.com_the_final_warning_logo.jpg" alt="SundayLaw.com — The Final Warning">
        </a>
        <a class="sn-link" href="/login.php">Login</a>
        <a class="sn-link" href="/register.php">Register</a>
        <a class="sn-link" href="/contact">Contact</a>
      </div>
    </div>
  </nav>
  <section class="hero" style="min-height:40vh;display:flex;align-items:center;justify-content:center;">
    <div class="hero-content" style="text-align:center;max-width:400px;margin:auto;">
      <div class="hero-badge">Create Account</div>
      <h1>Register</h1>
      <?php if ($error) echo '<p style="color:#f87171;">' . htmlspecialchars($error) . '</p>'; ?>
      <form method="post" style="margin:24px 0;text-align:left;">
        <label>Name:<br><input type="text" name="name" required></label><br><br>
        <label>Email:<br><input type="email" name="email" required></label><br><br>
        <label>Password:<br><input type="password" name="password" required></label><br><br>
        <label>Confirm Password:<br><input type="password" name="confirm" required></label><br><br>
        <button type="submit" class="btn-primary">Register</button>
      </form>
      <p>Already have an account? <a href="/login.php">Login here</a>.</p>
    </div>
  </section>
</body>
</html>