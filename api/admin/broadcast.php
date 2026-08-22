<?php
session_start();
require_once __DIR__ . '/../config.php';
// Simple admin check (replace with your real admin session logic)
if (!isset($_SESSION['admin_id'])) {
    header('Location: /api/admin/login.php');
    exit;
}
$pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $body = trim($_POST['body'] ?? '');
    if ($subject && $body) {
        $stmt = $pdo->prepare('INSERT INTO admin_broadcasts (subject, body) VALUES (?, ?)');
        $stmt->execute([$subject, $body]);
        // Send to all members (simplified)
        $members = $pdo->query('SELECT email, name FROM members WHERE email_verified = 1')->fetchAll();
        require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
        require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
        require_once __DIR__ . '/../PHPMailer/src/Exception.php';
        foreach ($members as $m) {
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
                $mail->addAddress($m['email'], $m['name']);
                $mail->Subject = $subject;
                $mail->Body = $body;
                $mail->send();
            } catch (Exception $e) {}
        }
        $success = 'Broadcast sent to all verified members.';
    } else {
        $error = 'Subject and body required.';
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Broadcast — SundayLaw.com</title>
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
        <a class="sn-link" href="/api/admin/dashboard.php">Dashboard</a>
        <a class="sn-link" href="/api/admin/broadcast.php">Broadcast</a>
        <a class="sn-link" href="/api/admin/logout.php">Logout</a>
      </div>
    </div>
  </nav>
  <section class="hero" style="min-height:40vh;display:flex;align-items:center;justify-content:center;">
    <div class="hero-content" style="text-align:center;max-width:700px;margin:auto;">
      <div class="hero-badge">Admin Broadcast</div>
      <h1>Send Email to All Members</h1>
      <?php if ($error) echo '<p style="color:#f87171;">' . htmlspecialchars($error) . '</p>'; ?>
      <?php if ($success) echo '<p style="color:#22c55e;">' . htmlspecialchars($success) . '</p>'; ?>
      <form method="post" style="margin:24px 0;text-align:left;">
        <label>Subject:<br><input type="text" name="subject" required></label><br><br>
        <label>Body:<br><textarea name="body" rows="5" style="width:100%;max-width:500px;" required></textarea></label><br><br>
        <button type="submit" class="btn-primary">Send Broadcast</button>
      </form>
    </div>
  </section>
</body>
</html>