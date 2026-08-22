<?php
// Admin delete article
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}
require '../config.php';
$id = intval($_GET['id'] ?? 0);
if ($id) {
    $stmt = $pdo->prepare('DELETE FROM articles WHERE id = ?');
    $stmt->execute([$id]);
}
header('Location: articles.php');
exit;
