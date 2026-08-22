<?php
session_start();
if (!isset($_SESSION['member_id'])) {
    header('Location: /login.php');
    exit;
}
require_once __DIR__ . '/../config.php';
$member_id = $_SESSION['member_id'];
$pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
$stmt = $pdo->prepare('SELECT message, created_at, is_read FROM notifications WHERE member_id = ? ORDER BY created_at DESC LIMIT 20');
$stmt->execute([$member_id]);
$notes = $stmt->fetchAll();
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Notifications — SundayLaw.com</title>
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
        <a class="sn-link" href="/api/members/dashboard.php">Dashboard</a>
        <a class="sn-link" href="/api/members/notifications.php">Notifications</a>
        <a class="sn-link" href="/api/members/profile.php">Profile</a>
        <a class="sn-link" href="/logout.php">Logout</a>
      </div>
    </div>
  </nav>
  <section class="hero" style="min-height:40vh;display:flex;align-items:center;justify-content:center;">
    <div class="hero-content" style="text-align:center;max-width:700px;margin:auto;">
      <div class="hero-badge">Notifications</div>
      <h1>Your Notifications</h1>
      <ul style="margin:32px 0 0 0;text-align:left;display:inline-block;">
        <?php foreach ($notes as $note): ?>
          <li><strong><?php echo htmlspecialchars($note['message']); ?></strong> <span style="color:#fcd34d;font-size:0.9em;">(<?php echo htmlspecialchars($note['created_at']); ?>)</span> <?php if (!$note['is_read']) echo '<span style="color:#22c55e;">[NEW]</span>'; ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </section>
</body>
</html>