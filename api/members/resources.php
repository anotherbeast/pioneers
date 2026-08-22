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
  <title>Member Resources — SundayLaw.com</title>
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
      <div class="hero-badge">Member Resources</div>
      <h1>Exclusive Downloads &amp; Study Guides</h1>
      <ul style="margin:32px 0 0 0;text-align:left;display:inline-block;">
        <li><a href="/books/ellen-white-great-controversy.pdf" target="_blank">The Great Controversy (PDF)</a></li>
        <li><a href="/pioneers/images/1850-law-of-god-chart.jpg.jpg" target="_blank">1850 Law of God Chart (Image)</a></li>
        <li><a href="/books/joseph-bates-seal.pdf" target="_blank">A Seal of the Living God (PDF)</a></li>
        <li><a href="/library" target="_blank">Full Pioneer Library</a></li>
      </ul>
    </div>
  </section>
</body>
</html>