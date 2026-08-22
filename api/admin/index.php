<?php
// Admin dashboard (requires login)
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <style>body{font-family:sans-serif;background:#181830;color:#fff;}nav{background:#23234a;padding:18px 24px;display:flex;gap:18px;}nav a{color:#fcd34d;text-decoration:none;font-weight:700;}nav a:hover{color:#fff;}h1{margin:24px 0 18px 24px;}section{margin:24px;}ul{margin:0;padding:0;list-style:none;}li{margin-bottom:10px;}</style>
</head>
<body>
  <nav>
    <a href="index.php">Dashboard</a>
    <a href="members.php">Members</a>
    <a href="articles.php">Articles</a>
    <a href="logout.php">Logout</a>
  </nav>
  <h1>Welcome, <?= htmlspecialchars($_SESSION['admin_username']) ?></h1>
  <section>
    <h2>Admin Tools</h2>
    <ul>
      <li><a href="members.php">Manage Members</a></li>
      <li><a href="articles.php">Manage Articles</a></li>
    </ul>
  </section>
</body>
</html>
