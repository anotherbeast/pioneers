<?php
session_start();
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: /api/admin/login.php');
    exit;
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard — SundayLaw.com</title>
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
        <a class="sn-link" href="/api/admin/broadcast.php">Broadcast Email</a>
        <a class="sn-link" href="/api/admin/logout.php">Logout</a>
      </div>
    </div>
  </nav>
  <section class="hero" style="min-height:40vh;display:flex;align-items:center;justify-content:center;">
    <div class="hero-content" style="text-align:center;max-width:700px;margin:auto;">
      <div class="hero-badge">Admin Area</div>
      <h1>Welcome, Admin!</h1>
      <p class="hero-subtitle">Manage members, send broadcasts, and view analytics.</p>
      <div style="margin:24px 0; display: flex; flex-wrap: wrap; gap: 12px; justify-content: center;">
        <a href="/api/admin/broadcast.php" class="btn-primary">Send Broadcast Email</a>
        <!-- Add more admin links here as features grow -->
      </div>
    </div>
  </section>
</body>
</html>