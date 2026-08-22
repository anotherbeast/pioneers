<?php
// Admin login page
session_start();
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require '../config.php';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT * FROM admins WHERE username = ?');
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Login</title>
  <style>body{font-family:sans-serif;background:#181830;color:#fff;display:flex;align-items:center;justify-content:center;height:100vh;}form{background:#23234a;padding:32px 28px;border-radius:12px;box-shadow:0 2px 16px #0003;min-width:320px;}input{width:100%;padding:12px;margin-bottom:16px;border-radius:8px;border:1px solid #888;background:#222;color:#fff;}button{width:100%;padding:12px;border-radius:8px;background:#b45309;color:#fff;font-weight:700;border:none;cursor:pointer;}h2{text-align:center;margin-bottom:18px;}p.error{color:#f87171;text-align:center;}</style>
</head>
<body>
  <form method="POST">
    <h2>Admin Login</h2>
    <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <input type="text" name="username" placeholder="Username" required autofocus>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
  </form>
</body>
</html>
