<?php
session_start();
if (!isset($_SESSION['member_id'])) {
    header('Location: /login.php');
    exit;
}
require_once __DIR__ . '/../config.php';
$member_id = $_SESSION['member_id'];
$pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address1 = trim($_POST['address1'] ?? '');
    $address2 = trim($_POST['address2'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $zip = trim($_POST['zip'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $stmt = $pdo->prepare('UPDATE members SET name=?, email=?, phone=?, address1=?, address2=?, city=?, state=?, zip=?, country=? WHERE id=?');
    $stmt->execute([$name, $email, $phone, $address1, $address2, $city, $state, $zip, $country, $member_id]);
    $msg = 'Profile updated!';
}
// Avatar removal
if (isset($_POST['remove_avatar'])) {
    if (!empty($user['avatar'])) {
        $avatarFile = __DIR__ . '/../../' . ltrim($user['avatar'], '/');
        if (file_exists($avatarFile)) {
            unlink($avatarFile);
        }
        $stmt = $pdo->prepare('UPDATE members SET avatar=NULL WHERE id=?');
        $stmt->execute([$member_id]);
        $msg = 'Avatar removed.';
        $user['avatar'] = '';
    }
}
// Avatar upload with validation, resizing, and cropping
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $maxSize = 2 * 1024 * 1024; // 2MB
    $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
    $fileSize = $_FILES['avatar']['size'];
    $tmpPath = $_FILES['avatar']['tmp_name'];
    if (!in_array($ext, $allowed)) {
        $msg = 'Invalid file type. Only JPG, PNG, GIF allowed.';
    } elseif ($fileSize > $maxSize) {
        $msg = 'File too large. Max 2MB.';
    } else {
        // Crop to center square, then resize
        list($w, $h, $type) = getimagesize($tmpPath);
        $side = min($w, $h);
        $srcX = (int)(($w - $side) / 2);
        $srcY = (int)(($h - $side) / 2);
        $maxDim = 256;
        $dst = imagecreatetruecolor($maxDim, $maxDim);
        switch ($type) {
            case IMAGETYPE_JPEG:
                $src = imagecreatefromjpeg($tmpPath);
                break;
            case IMAGETYPE_PNG:
                $src = imagecreatefrompng($tmpPath);
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
                break;
            case IMAGETYPE_GIF:
                $src = imagecreatefromgif($tmpPath);
                break;
            default:
                $src = null;
        }
        if ($src) {
            imagecopyresampled($dst, $src, 0, 0, $srcX, $srcY, $maxDim, $maxDim, $side, $side);
            $avatarPath = '/uploads/avatars/member_' . $member_id . '.' . $ext;
            $savePath = __DIR__ . '/../../uploads/avatars/member_' . $member_id . '.' . $ext;
            switch ($type) {
                case IMAGETYPE_JPEG:
                    imagejpeg($dst, $savePath, 90);
                    break;
                case IMAGETYPE_PNG:
                    imagepng($dst, $savePath);
                    break;
                case IMAGETYPE_GIF:
                    imagegif($dst, $savePath);
                    break;
            }
            imagedestroy($src);
            imagedestroy($dst);
            $stmt = $pdo->prepare('UPDATE members SET avatar=? WHERE id=?');
            $stmt->execute([$avatarPath, $member_id]);
            $msg = 'Profile and avatar updated!';
            $user['avatar'] = $avatarPath;
        } else {
            $msg = 'Image processing failed.';
        }
    }
}
$stmt = $pdo->prepare('SELECT * FROM members WHERE id = ?');
$stmt->execute([$member_id]);
$user = $stmt->fetch();
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Profile — SundayLaw.com</title>
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
    <div class="hero-content" style="text-align:center;max-width:600px;margin:auto;">
      <div class="hero-badge">Edit Profile</div>
      <h1>Profile</h1>
      <?php if (!empty($msg)) echo '<p style="color:#fcd34d;">' . htmlspecialchars($msg) . '</p>'; ?>
      <form method="post" enctype="multipart/form-data" style="margin:24px 0;text-align:left;" id="profileForm">
        <label>Name:<br><input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required></label><br><br>
        <label>Email:<br><input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required></label><br><br>
        <label>Phone:<br><input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>"></label><br><br>
        <label>Address 1:<br><input type="text" name="address1" value="<?php echo htmlspecialchars($user['address1']); ?>"></label><br><br>
        <label>Address 2:<br><input type="text" name="address2" value="<?php echo htmlspecialchars($user['address2']); ?>"></label><br><br>
        <label>City:<br><input type="text" name="city" value="<?php echo htmlspecialchars($user['city']); ?>"></label><br><br>
        <label>State:<br><input type="text" name="state" value="<?php echo htmlspecialchars($user['state']); ?>"></label><br><br>
        <label>ZIP:<br><input type="text" name="zip" value="<?php echo htmlspecialchars($user['zip']); ?>"></label><br><br>
        <label>Country:<br><input type="text" name="country" value="<?php echo htmlspecialchars($user['country']); ?>"></label><br><br>
        <label>Avatar:<br><input type="file" name="avatar" id="avatarInput" accept="image/*"></label><br>
        <div id="avatarPreviewContainer" style="display:none;margin:10px 0;">
          <canvas id="avatarPreview" width="256" height="256" style="border-radius:50%;border:1px solid #ccc;cursor:move;"></canvas><br>
          <input type="range" id="avatarZoom" min="1" max="3" step="0.01" value="1" style="width:180px;"> Zoom<br>
          <small>Drag to move crop area. Adjust zoom. Only the selected square will be used.</small>
        </div>
        <?php if (!empty($user['avatar'])): ?>
          <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar" style="max-width:120px;border-radius:50%;margin-top:10px;"><br>
          <button type="submit" name="remove_avatar" value="1" onclick="return confirm('Remove avatar?');">Remove Avatar</button><br>
        <?php endif; ?>
        <br>
        <button type="submit" class="btn-primary">Update Profile</button>
      </form>
    </div>
  </section>
</body>
</html>