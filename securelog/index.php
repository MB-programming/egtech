<?php
require_once dirname(__DIR__) . '/includes/admin-auth.php';

if (admin_is_logged()) {
    header('Location: slides.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';
    if (admin_try_login($user, $pass)) {
        header('Location: slides.php');
        exit;
    }
    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Login – DGTEC</title>
  <link rel="stylesheet" href="assets/admin.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />
</head>
<body class="login-page">
  <div class="login-card">
    <div class="login-brand">
      <img src="../assets/images/logo.webp" alt="DGTEC" />
      <p>Control Panel</p>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-error"><i class="fas fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" class="login-form">
      <div class="form-group">
        <label><i class="fas fa-user"></i> Username</label>
        <input type="text" name="username" placeholder="Enter your username" autocomplete="username" required autofocus />
      </div>
      <div class="form-group">
        <label><i class="fas fa-lock"></i> Password</label>
        <input type="password" name="password" placeholder="••••••••••" autocomplete="current-password" required />
      </div>
      <button type="submit" class="btn-login">
        Sign In <i class="fas fa-arrow-right"></i>
      </button>
    </form>
  </div>
</body>
</html>
