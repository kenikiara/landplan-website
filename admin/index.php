<?php
/** Admin login. */
require_once __DIR__ . '/_bootstrap.php';

if (admin_user()) redirect('dashboard.php');
if (!admin_exists()) redirect('setup.php');

$err  = '';
$next = get('next', 'dashboard.php');
if (is_post()) {
    csrf_check();
    $email = post('email');
    $pass  = post('password');
    if (admin_login($email, $pass)) {
        $dest = post('next') ?: 'dashboard.php';
        // only allow local relative redirects
        if (!preg_match('#^[a-z0-9_\-./?=&]+$#i', $dest) || strpos($dest, '//') !== false) $dest = 'dashboard.php';
        redirect($dest);
    }
    $err = 'Incorrect email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<title>Sign in | Landplan Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<div class="auth-wrap">
  <div class="auth-card">
    <div class="brand"><span class="mark"><svg viewBox="0 0 24 24" width="20" height="20"><path d="M12 2 4 5v6c0 5 3.4 9.7 8 11 4.6-1.3 8-6 8-11V5l-8-3zm0 4 4.5 3.4v5.1L12 17.9l-4.5-3.4V9.4L12 6z"/></svg></span></div>
    <h1>Landplan Admin</h1>
    <p class="lede">Sign in to manage your website.</p>
    <?php if ($err): ?><div class="flash error"><?= e($err) ?></div><?php endif; ?>
    <form method="post" class="stack">
      <?= csrf_field() ?>
      <input type="hidden" name="next" value="<?= e($next) ?>">
      <div class="field"><label>Email</label><input type="email" name="email" value="<?= e(post('email')) ?>" required autofocus></div>
      <div class="field"><label>Password</label><input type="password" name="password" required></div>
      <button class="btn btn-primary btn-block" type="submit">Sign in</button>
    </form>
  </div>
</div>
</body>
</html>
