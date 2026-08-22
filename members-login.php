<?php
// members-login.php — Modern styled login page for SundayLaw.com
session_start();
if (isset($_SESSION['member_id'])) {
    header('Location: /members-area.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Member Login — SundayLaw.com</title>
  <link rel="stylesheet" href="/css/styles.css">
  <style>
    .tabs { display: flex; gap: 6px; margin-bottom: 28px; background: rgba(26,26,78,0.4); padding: 6px; border-radius: 12px; }
    .tab { flex: 1; padding: 10px 8px; text-align: center; border-radius: 8px; font-weight: 600; font-size: 0.95rem; cursor: pointer; transition: all 0.2s; border: none; color: #fff; background: linear-gradient(to right,#7c3aed,#06b6d4); }
    .tab.join { background: linear-gradient(to right,#a21caf,#f472b6); }
    .tab.login { background: linear-gradient(to right,#2563eb,#06b6d4); }
    .tab.forgot { background: linear-gradient(to right,#059669,#10b981); }
    .tab.active { box-shadow: 0 2px 12px rgba(39,39,245,0.18); }
    .page-wrap { max-width: 420px; margin: 0 auto; padding: 56px 22px 80px; }
    .page-hero { text-align: center; padding: 0 0 36px; }
    .page-hero h1 { font-family: 'Crimson Text', serif; font-size: 2.2rem; font-weight: 600; color: var(--ink); margin-bottom: 10px; }
    .page-hero p { color: var(--ink-muted); font-size: 1.05rem; line-height: 1.7; }
    .form-card { background: linear-gradient(135deg, rgba(26,26,78,0.8), rgba(13,13,53,0.8)); border: 1px solid rgba(180,130,50,0.3); border-radius: 20px; padding: 32px 28px; }
    .form-group { margin-bottom: 18px; }
    .form-group label { color: #fcd34d; font-weight: 600; }
    .form-group input { background: rgba(10,10,46,0.6); color: #fff; border: 1.5px solid #d4a537; border-radius: 8px; padding: 12px 14px; font-size: 1rem; margin-bottom: 8px; width: 100%; }
    .form-group input:focus { outline: none; border-color: #7c3aed; }
    .btn-submit { width: 100%; padding: 14px; border-radius: 10px; font-weight: 700; font-size: 1rem; cursor: pointer; border: none; background: linear-gradient(to right,#b45309,#d97706); color: #fff; margin-top: 12px; }
    .btn-submit:hover { background: linear-gradient(to right,#d97706,#f59e0b); }
    .form-links { text-align: center; margin-top: 18px; }
    .form-links a { color: var(--gold); text-decoration: none; margin: 0 10px; font-size: 0.97rem; }
    .form-links a:hover { color: var(--gold-soft); }
    .error, .success { text-align: center; margin-bottom: 12px; font-size: 1rem; }
    .error { color: #f87171; }
    .success { color: #10b981; }
    @media (max-width: 600px) { .form-card { padding: 18px 8px; } }
  </style>
</head>
<body>
  <?php include_once __DIR__ . '/public_html/partials/nav.php'; ?>
  <div class="page-wrap">
    <div class="page-hero">
      <h1>Member Access</h1>
      <p>Sign in, join, or reset your password below. Enjoy exclusive resources and community features.</p>
    </div>
    <div class="tabs" role="tablist">
      <a class="tab join" href="/members.astro" role="tab">Join</a>
      <a class="tab login active" href="/members-login.php" role="tab" aria-selected="true">Login</a>
      <a class="tab forgot" href="/forgot.html" role="tab">Forgot Password</a>
    </div>
    <div class="form-card">
      <?php if (isset($_GET['error'])): ?>
        <div class="error" aria-live="polite"><?= htmlspecialchars($_GET['error']) ?></div>
      <?php endif; ?>
      <?php if (isset($_GET['success'])): ?>
        <div class="success" aria-live="polite"><?= htmlspecialchars($_GET['success']) ?></div>
      <?php endif; ?>
      <form method="POST" action="/api/login.php" id="loginForm" autocomplete="on">
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" required placeholder="your@email.com" autocomplete="email" autofocus />
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required placeholder="Your password" autocomplete="current-password" />
          <input type="checkbox" id="showpw" style="margin-top:8px;" onclick="togglePassword()"> <label for="showpw" style="color:#fcd34d;font-size:0.97rem;">Show password</label>
        </div>
        <button type="submit" class="btn-submit">Sign In</button>
      </form>
      <div class="form-links">
        <a href="/members.astro">Join</a> |
        <a href="/forgot.html">Forgot Password?</a>
      </div>
    </div>
  </div>
  <script>
    function togglePassword() {
      var pw = document.getElementById('password');
      pw.type = pw.type === 'password' ? 'text' : 'password';
    }
  </script>
  <?php include_once __DIR__ . '/public_html/partials/footer.php'; ?>
</body>
</html>
