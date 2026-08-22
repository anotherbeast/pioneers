<?php
// Admin article management: list, edit, delete, create
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}
require '../config.php';
$stmt = $pdo->query('SELECT a.*, m.name AS author FROM articles a LEFT JOIN members m ON a.member_id = m.id ORDER BY a.created_at DESC');
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Articles</title>
  <style>body{font-family:sans-serif;background:#181830;color:#fff;}table{width:100%;border-collapse:collapse;margin:24px 0;}th,td{padding:10px 8px;border-bottom:1px solid #333;}th{background:#23234a;}tr:nth-child(even){background:#23234a22;}a.btn{padding:6px 14px;border-radius:6px;text-decoration:none;font-weight:700;margin-right:6px;}a.edit{background:#7c3aed;color:#fff;}a.delete{background:#ef4444;color:#fff;}a.create{background:#059669;color:#fff;margin-bottom:18px;display:inline-block;}h1{margin:24px;}nav{background:#23234a;padding:18px 24px;display:flex;gap:18px;}nav a{color:#fcd34d;text-decoration:none;font-weight:700;}nav a:hover{color:#fff;}</style>
</head>
<body>
  <nav>
    <a href="index.php">Dashboard</a>
    <a href="members.php">Members</a>
    <a href="articles.php">Articles</a>
    <a href="logout.php">Logout</a>
  </nav>
  <h1>Manage Articles</h1>
  <a href="create_article.php" class="btn create">+ Create New Article</a>
  <table>
    <tr><th>ID</th><th>Title</th><th>Author</th><th>Created</th><th>Status</th><th>Actions</th></tr>
    <?php foreach ($articles as $article): ?>
      <tr>
        <td><?= $article['id'] ?></td>
        <td><?= htmlspecialchars($article['title']) ?></td>
        <td><?= htmlspecialchars($article['author'] ?? 'N/A') ?></td>
        <td><?= $article['created_at'] ?></td>
        <td><?= $article['is_published'] ? 'Published' : 'Draft' ?></td>
        <td>
          <a href="edit_article.php?id=<?= $article['id'] ?>" class="btn edit">Edit</a>
          <a href="delete_article.php?id=<?= $article['id'] ?>" class="btn delete" onclick="return confirm('Delete this article?');">Delete</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</body>
</html>
