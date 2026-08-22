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
  <title>Member Events — SundayLaw.com</title>
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
      <div class="hero-badge">Upcoming Events</div>
      <h1>Member Events &amp; Meetings</h1>
      <ul style="margin:32px 0 0 0;text-align:left;display:inline-block;">
        <li><strong>April 22, 2026:</strong> Online Bible Study — 7:00 PM EST (Zoom link will be emailed to members)</li>
        <li><strong>May 5, 2026:</strong> Q&A with Adventist History Experts</li>
        <li><strong>June 1, 2026:</strong> Prayer Meeting — All Members Welcome</li>
      </ul>
      <p style="margin-top:24px;color:#fcd34d;">(More events and RSVP features coming soon!)</p>
    </div>
  </section>
</body>
</html>