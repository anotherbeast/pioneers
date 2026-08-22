<?php
// reset.php — Handles password reset via token
require_once __DIR__ . '/config.php';
$error = '';
$success = '';
$token = $_GET['token'] ?? '';
if (!$token) {
    $error = 'Invalid or missing token.';
} else {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
    $stmt = $pdo->prepare('SELECT id, reset_token_expires FROM members WHERE reset_token = ?');
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    if (!$user || strtotime($user['reset_token_expires']) < time()) {
        $error = 'Reset link is invalid or expired.';
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm'] ?? '';
        if (!$password || !$confirm) {
            $error = 'All fields are required.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE members SET password=?, reset_token=NULL, reset_token_expires=NULL WHERE id=?');
            $stmt->execute([$hash, $user['id']]);
            $success = 'Password reset successful. You may now <a href="/login.php">login</a>.';
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password — SundayLaw.com</title>
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
      <div class="hero-badge">Reset Password</div>
      <h1>Set a New Password</h1>
      <?php if ($error) echo '<p style="color:#f87171;">' . htmlspecialchars($error) . '</p>'; ?>
      <?php if ($success) echo '<p style="color:#22c55e;">' . $success . '</p>'; ?>
      <?php if (!$success && !$error): ?>
      <form method="post" style="margin:24px 0;text-align:left;">
        <label>New Password:<br><input type="password" name="password" required></label><br><br>
        <label>Confirm Password:<br><input type="password" name="confirm" required></label><br><br>
        <button type="submit" class="btn-primary">Reset Password</button>
      </form>
      <?php endif; ?>
    </div>
  </section>
</body>
</html>