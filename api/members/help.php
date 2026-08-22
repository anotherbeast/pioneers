<?php
session_start();
if (!isset($_SESSION['member_id'])) {
    header('Location: /login.php');
    exit;
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Member Help — SundayLaw.com</title>
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
    <div class="hero-content" style="text-align:center;max-width:700px;margin:auto;">
      <div class="hero-badge">Member Help &amp; FAQ</div>
      <h1>Need Assistance?</h1>
      <ul style="margin:32px 0 0 0;text-align:left;display:inline-block;">
        <li>How do I update my profile? — Go to <a href="/api/members/profile.php">Profile</a> and edit your info.</li>
        <li>How do I access exclusive resources? — Visit <a href="/api/members/resources.php">Resources</a>.</li>
        <li>How do I join events? — See <a href="/api/members/events.php">Events</a> for upcoming meetings.</li>
        <li>How do I post a prayer request? — Use <a href="/api/members/discussion.php">Discussion</a>.</li>
        <li>Still need help? <a href="/contact">Contact us</a> for support.</li>
      </ul>
    </div>
  </section>
</body>
</html>