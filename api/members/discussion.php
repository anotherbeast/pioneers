<?php
session_start();
if (!isset($_SESSION['member_id'])) {
    header('Location: /login.php');
    exit;
}
require_once __DIR__ . '/../config.php';
$pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $msg = trim($_POST['message'] ?? '');
    if ($msg) {
        $stmt = $pdo->prepare('INSERT INTO member_discussion (member_id, message, posted_at) VALUES (?, ?, NOW())');
        $stmt->execute([$_SESSION['member_id'], $msg]);
    }
}
$stmt = $pdo->prepare('SELECT m.name, d.message, d.posted_at FROM member_discussion d JOIN members m ON d.member_id = m.id ORDER BY d.posted_at DESC LIMIT 20');
$stmt->execute();
$posts = $stmt->fetchAll();
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Member Discussion — SundayLaw.com</title>
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
      <div class="hero-badge">Discussion &amp; Prayer Requests</div>
      <h1>Share with the Community</h1>
      <form method="post" style="margin:24px 0;">
        <textarea name="message" rows="3" style="width:100%;max-width:500px;" placeholder="Share a thought, question, or prayer request..."></textarea><br>
        <button type="submit" class="btn-primary">Post</button>
      </form>
      <div style="margin-top:32px;text-align:left;max-width:500px;margin-left:auto;margin-right:auto;">
        <h2>Recent Posts</h2>
        <?php foreach ($posts as $post): ?>
          <div style="border-bottom:1px solid #333;padding:10px 0;">
            <strong><?php echo htmlspecialchars($post['name']); ?></strong> <span style="color:#fcd34d;font-size:0.9em;">(<?php echo htmlspecialchars($post['posted_at']); ?>)</span><br>
            <span><?php echo nl2br(htmlspecialchars($post['message'])); ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</body>
</html>