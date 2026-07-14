<?php
/**
 * One-time setup — creates the first admin account.
 * Automatically disables itself once any user exists.
 * DELETE this file after you have created your account.
 */
require_once __DIR__ . '/_bootstrap.php';

if (admin_user()) redirect('dashboard.php');

if (admin_exists()) {
    http_response_code(403);
    exit('Setup is already complete. For security, delete admin/setup.php from your server.');
}

$err = '';
if (is_post()) {
    csrf_check();
    $name  = post('name');
    $email = strtolower(post('email'));
    $pass  = post('password');
    $pass2 = post('password2');

    if ($name === '' || $email === '' || $pass === '') {
        $err = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = 'Enter a valid email address.';
    } elseif (strlen($pass) < 8) {
        $err = 'Password must be at least 8 characters.';
    } elseif ($pass !== $pass2) {
        $err = 'Passwords do not match.';
    } else {
        $stmt = db()->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, "admin")');
        $stmt->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT)]);
        $_SESSION['admin_id'] = (int)db()->lastInsertId();
        session_regenerate_id(true);
        flash('Account created. IMPORTANT: delete admin/setup.php from your server now.', 'info');
        redirect('dashboard.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<title>Setup — Landplan Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<div class="auth-wrap">
  <div class="auth-card">
    <div class="brand"><span class="mark"><svg viewBox="0 0 24 24" width="20" height="20"><path d="M12 2 4 5v6c0 5 3.4 9.7 8 11 4.6-1.3 8-6 8-11V5l-8-3zm0 4 4.5 3.4v5.1L12 17.9l-4.5-3.4V9.4L12 6z"/></svg></span></div>
    <h1>Create your admin account</h1>
    <p class="lede">This one-time page sets up the first administrator.</p>
    <?php if ($err): ?><div class="flash error"><?= e($err) ?></div><?php endif; ?>
    <form method="post" class="stack">
      <?= csrf_field() ?>
      <div class="field"><label>Full name</label><input type="text" name="name" value="<?= e(post('name')) ?>" required autofocus></div>
      <div class="field"><label>Email</label><input type="email" name="email" value="<?= e(post('email')) ?>" required></div>
      <div class="field"><label>Password</label><input type="password" name="password" required minlength="8"><span class="hint">Minimum 8 characters.</span></div>
      <div class="field"><label>Confirm password</label><input type="password" name="password2" required minlength="8"></div>
      <button class="btn btn-primary btn-block" type="submit">Create account &amp; sign in</button>
    </form>
  </div>
</div>
</body>
</html>
