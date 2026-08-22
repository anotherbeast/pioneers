<?php
session_start();
if (!isset($_SESSION['member_id'])) {
    header('Location: /login.php');
    exit;
}
require_once __DIR__ . '/../config.php';
$member_id = $_SESSION['member_id'];
$pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
// Send message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recipient_id'], $_POST['message'])) {
    $recipient_id = (int)$_POST['recipient_id'];
    $message = trim($_POST['message']);
    if ($recipient_id && $message) {
        $stmt = $pdo->prepare('INSERT INTO member_messages (sender_id, recipient_id, message) VALUES (?, ?, ?)');
        $stmt->execute([$member_id, $recipient_id, $message]);
    }
}
// Fetch all members for dropdown
$members = $pdo->query('SELECT id, name FROM members WHERE id != ' . $member_id . ' ORDER BY name')->fetchAll();
// Fetch received messages
$stmt = $pdo->prepare('SELECT m.name AS sender, msg.message, msg.sent_at, msg.is_read FROM member_messages msg JOIN members m ON msg.sender_id = m.id WHERE msg.recipient_id = ? ORDER BY msg.sent_at DESC LIMIT 20');
$stmt->execute([$member_id]);
$messages = $stmt->fetchAll();
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Private Messages — SundayLaw.com</title>
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
        <a class="sn-link" href="/api/members/messages.php">Messages</a>
        <a class="sn-link" href="/api/members/profile.php">Profile</a>
        <a class="sn-link" href="/logout.php">Logout</a>
      </div>
    </div>
  </nav>
  <section class="hero" style="min-height:40vh;display:flex;align-items:center;justify-content:center;">
    <div class="hero-content" style="text-align:center;max-width:700px;margin:auto;">
      <div class="hero-badge">Private Messages</div>
      <h1>Send a Private Message</h1>
      <form method="post" style="margin:24px 0;">
        <select name="recipient_id" required>
          <option value="">Select recipient</option>
          <?php foreach ($members as $m): ?>
            <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['name']); ?></option>
          <?php endforeach; ?>
        </select><br><br>
        <textarea name="message" rows="3" style="width:100%;max-width:500px;" placeholder="Type your message..."></textarea><br>
        <button type="submit" class="btn-primary">Send</button>
      </form>
      <div style="margin-top:32px;text-align:left;max-width:500px;margin-left:auto;margin-right:auto;">
        <h2>Inbox</h2>
        <?php foreach ($messages as $msg): ?>
          <div style="border-bottom:1px solid #333;padding:10px 0;">
            <strong><?php echo htmlspecialchars($msg['sender']); ?></strong> <span style="color:#fcd34d;font-size:0.9em;">(<?php echo htmlspecialchars($msg['sent_at']); ?>)</span><br>
            <span><?php echo nl2br(htmlspecialchars($msg['message'])); ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</body>
</html>