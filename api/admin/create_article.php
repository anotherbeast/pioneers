<?php
// Admin create article
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}
require '../config.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    if ($title && $content) {
        // Admin-created articles: set member_id to NULL
        $stmt = $pdo->prepare('INSERT INTO articles (member_id, title, content, is_published) VALUES (NULL,?,?,?)');
        $stmt->execute([$title, $content, $is_published]);
        header('Location: articles.php');
        exit;
    } else {
        $error = 'Title and content are required.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Article</title>
  <style>body{font-family:sans-serif;background:#181830;color:#fff;}form{background:#23234a;padding:32px 28px;border-radius:12px;box-shadow:0 2px 16px #0003;max-width:520px;margin:32px auto;}input,textarea{width:100%;padding:12px;margin-bottom:16px;border-radius:8px;border:1px solid #888;background:#222;color:#fff;}button{width:100%;padding:12px;border-radius:8px;background:#059669;color:#fff;font-weight:700;border:none;cursor:pointer;}h2{text-align:center;margin-bottom:18px;}label{font-weight:700;}p.error{color:#f87171;text-align:center;}</style>
</head>
<body>
  <form method="POST">
    <h2>Create Article</h2>
    <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <label>Title</label>
    <input type="text" name="title" required>
    <label>Content</label>
    <textarea name="content" rows="10" required></textarea>
    <label><input type="checkbox" name="is_published" checked> Published</label>
    <button type="submit">Create Article</button>
  </form>
</body>
</html>
