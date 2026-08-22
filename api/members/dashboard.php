<?php
session_start();
if (!isset($_SESSION['member_id'])) {
    header('Location: /login.php');
    exit;
}
require_once __DIR__ . '/../config.php';
$member_id = $_SESSION['member_id'];
$pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
$stmt = $pdo->prepare('SELECT name FROM members WHERE id = ?');
$stmt->execute([$member_id]);
$user = $stmt->fetch();
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Member Dashboard — SundayLaw.com</title>
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
        <a class="sn-link" href="/api/members/profile.php">Profile</a>
        <a class="sn-link" href="/api/members/resources.php">Resources</a>
        <a class="sn-link" href="/api/members/events.php">Events</a>
        <a class="sn-link" href="/api/members/discussion.php">Discussion</a>
        <a class="sn-link" href="/api/members/help.php">Help</a>
        <a class="sn-link" href="/logout.php">Logout</a>
      </div>
    </div>
  </nav>
  <section class="hero" style="min-height:40vh;display:flex;align-items:center;justify-content:center;">
    <div class="hero-content" style="text-align:center;">
      <div class="hero-badge">Members Area</div>
      <h1>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h1>
      <p class="hero-subtitle">You now have access to exclusive resources and updates.</p>
      <div style="margin:24px 0; display: flex; flex-wrap: wrap; gap: 12px; justify-content: center;">
        <a href="/api/members/resources.php" class="btn-primary">Explore Resources</a>
        <a href="/api/members/profile.php" class="btn-outline">Edit Profile</a>
        <a href="/api/members/messages.php" class="btn-outline">Private Messages</a>
        <a href="/api/members/activity.php" class="btn-outline">Activity Log</a>
        <a href="/api/members/notifications.php" class="btn-outline">Notifications</a>
      </div>
      <p style="color:#fcd34d;font-size:1rem;">(More member features coming soon!)</p>
    </div>
  </section>
  <!-- Site Footer (reuse from index.html) -->
</body>
</html>