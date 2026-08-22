<?php
// members-area.php — Secure member-only content page for SundayLaw.com
session_start();
if (!isset($_SESSION['member_id'])) {
    header('Location: /login.html');
    exit;
}
// Optionally, fetch user info from DB if needed
// require_once __DIR__ . '/api/config.php';
// ...
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Members Area — SundayLaw.com</title>
  <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
  <!-- Site Navigation (reuse your nav include or copy markup from index.html) -->
  <?php include_once __DIR__ . '/public_html/partials/nav.php'; ?>
  <div class="page-wrap">
    <div class="page-hero">
      <h1>Welcome, Member!</h1>
      <p>This content is only visible to logged-in members.</p>
    </div>
    <div class="content">
      <!-- Place your member-only content here -->
      <h2>Exclusive Resources</h2>
      <ul>
        <li>Downloadable books and charts</li>
        <li>Special research reports</li>
        <li>Community forums (coming soon)</li>
      </ul>
    </div>
  </div>
  <!-- Site Footer (reuse your footer include or copy markup from index.html) -->
  <?php include_once __DIR__ . '/public_html/partials/footer.php'; ?>
</body>
</html>
