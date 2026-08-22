<?php
session_start();
require_once __DIR__ . '/config.php';
if (isset($_SESSION['member_id'])) {
    header('Location: /api/members/dashboard.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
    $stmt = $pdo->prepare('SELECT id, password, email_verified FROM members WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        if (isset($user['email_verified']) && !$user['email_verified']) {
            $error = 'Please verify your email before logging in.';
        } else {
            $_SESSION['member_id'] = $user['id'];
            header('Location: /api/members/dashboard.php');
            exit;
        }
    } else {
        $error = 'Invalid email or password.';
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Member Login — SundayLaw.com</title>
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
        <a class="sn-link" href="/members">Members</a>
        <a class="sn-link" href="/contact">Contact</a>
      </div>
    </div>
  </nav>
  <section class="hero" style="min-height:40vh;display:flex;align-items:center;justify-content:center;">
    <div class="hero-content" style="text-align:center;max-width:400px;margin:auto;">
      <div class="hero-badge">Member Login</div>
      <h1>Sign In</h1>
      <?php if ($error) echo '<p style="color:#f87171;">' . htmlspecialchars($error) . '</p>'; ?>
      <form method="post" style="margin:24px 0;text-align:left;">
        <label>Email:<br><input type="email" name="email" required></label><br><br>
        <label>Password:<br><input type="password" name="password" required></label><br><br>
        <button type="submit" class="btn-primary">Login</button>
      </form>
      <p><a href="/forgot.php">Forgot your password?</a></p>
    </div>
  </section>
</body>
</html>
