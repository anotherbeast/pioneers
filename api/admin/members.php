<?php
session_start();
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: /api/admin/login.php');
    exit;
}
$pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
// Handle ban/unban
if (isset($_GET['ban']) && is_numeric($_GET['ban'])) {
    $stmt = $pdo->prepare('UPDATE members SET banned = 1 WHERE id = ?');
    $stmt->execute([$_GET['ban']]);
}
if (isset($_GET['unban']) && is_numeric($_GET['unban'])) {
    $stmt = $pdo->prepare('UPDATE members SET banned = 0 WHERE id = ?');
    $stmt->execute([$_GET['unban']]);
}
// Search/filter
$where = '';
$params = [];
if (!empty($_GET['q'])) {
    $where = 'WHERE name LIKE ? OR email LIKE ?';
    $params = ["%".$_GET['q']."%", "%".$_GET['q']."%"];
}
$members = $pdo->prepare("SELECT id, name, email, created_at, banned FROM members $where ORDER BY created_at DESC LIMIT 100");
$members->execute($params);
$rows = $members->fetchAll();
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin — Manage Members</title>
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
        <a class="sn-link" href="/api/admin/members.php">Manage Members</a>
        <a class="sn-link" href="/api/admin/broadcast.php">Broadcast</a>
        <a class="sn-link" href="/api/admin/logout.php">Logout</a>
      </div>
    </div>
  </nav>
  <section class="hero" style="min-height:40vh;display:flex;align-items:center;justify-content:center;">
    <div class="hero-content" style="text-align:center;max-width:900px;margin:auto;">
      <div class="hero-badge">Member Management</div>
      <h1>All Members</h1>
      <form method="get" style="margin:18px 0;">
        <input type="text" name="q" placeholder="Search by name or email" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
        <button type="submit" class="btn-primary">Search</button>
      </form>
      <table style="width:100%;margin-top:18px;border-collapse:collapse;">
        <tr style="background:#222;color:#fcd34d;"><th>ID</th><th>Name</th><th>Email</th><th>Joined</th><th>Status</th><th>Action</th></tr>
        <?php foreach ($rows as $m): ?>
        <tr style="background:<?php echo $m['banned'] ? '#fee2e2' : '#fff'; ?>;color:#222;">
          <td><?php echo $m['id']; ?></td>
          <td><?php echo htmlspecialchars($m['name']); ?></td>
          <td><?php echo htmlspecialchars($m['email']); ?></td>
          <td><?php echo htmlspecialchars($m['created_at']); ?></td>
          <td><?php echo $m['banned'] ? 'Banned' : 'Active'; ?></td>
          <td>
            <?php if ($m['banned']): ?>
              <a href="?unban=<?php echo $m['id']; ?>" class="btn-outline">Unban</a>
            <?php else: ?>
              <a href="?ban=<?php echo $m['id']; ?>" class="btn-primary" style="background:#f87171;">Ban</a>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </table>
    </div>
  </section>
</body>
</html>