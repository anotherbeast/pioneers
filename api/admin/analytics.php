<?php
session_start();
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: /api/admin/login.php');
    exit;
}
$pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
// Member stats
$total = $pdo->query('SELECT COUNT(*) FROM members')->fetchColumn();
$new_today = $pdo->query('SELECT COUNT(*) FROM members WHERE DATE(created_at) = CURDATE()')->fetchColumn();
$new_week = $pdo->query('SELECT COUNT(*) FROM members WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)')->fetchColumn();
$active = $pdo->query('SELECT COUNT(DISTINCT member_id) FROM member_activity WHERE activity_time > DATE_SUB(NOW(), INTERVAL 7 DAY)')->fetchColumn();
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Analytics — SundayLaw.com</title>
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
        <a class="sn-link" href="/api/admin/analytics.php">Analytics</a>
        <a class="sn-link" href="/api/admin/members.php">Manage Members</a>
        <a class="sn-link" href="/api/admin/logout.php">Logout</a>
      </div>
    </div>
  </nav>
  <section class="hero" style="min-height:40vh;display:flex;align-items:center;justify-content:center;">
    <div class="hero-content" style="text-align:center;max-width:700px;margin:auto;">
      <div class="hero-badge">Site Analytics</div>
      <h1>Analytics Overview</h1>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin:32px 0;">
        <div style="background:#222;padding:24px;border-radius:12px;color:#fcd34d;">
          <h2>Total Members</h2>
          <div style="font-size:2.2rem;font-weight:700;"> <?php echo $total; ?> </div>
        </div>
        <div style="background:#222;padding:24px;border-radius:12px;color:#fcd34d;">
          <h2>New Today</h2>
          <div style="font-size:2.2rem;font-weight:700;"> <?php echo $new_today; ?> </div>
        </div>
        <div style="background:#222;padding:24px;border-radius:12px;color:#fcd34d;">
          <h2>New This Week</h2>
          <div style="font-size:2.2rem;font-weight:700;"> <?php echo $new_week; ?> </div>
        </div>
        <div style="background:#222;padding:24px;border-radius:12px;color:#fcd34d;">
          <h2>Active This Week</h2>
          <div style="font-size:2.2rem;font-weight:700;"> <?php echo $active; ?> </div>
        </div>
      </div>
      <p style="color:#fcd34d;">For detailed traffic, connect Google Analytics or Plausible.</p>
    </div>
  </section>
</body>
</html>