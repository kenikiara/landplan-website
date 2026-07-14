<?php
require_once __DIR__ . '/../app/auth.php';
boot_session();
if (client_user()) redirect('dashboard.php');

$tab = 'login'; $err = '';
$next = get('next', 'dashboard.php');
if (!preg_match('#^[a-z0-9_\-.?=&]+$#i', $next)) $next = 'dashboard.php';

if (is_post()) {
    csrf_check();
    $action = post('action');
    if ($action === 'login') {
        if (client_login(post('email'), post('password'))) redirect(post('next') ?: 'dashboard.php');
        $err = 'Incorrect email or password.'; $tab = 'login';
    } elseif ($action === 'register') {
        $name=post('name'); $email=post('email'); $phone=post('phone'); $pass=post('password');
        if ($name===''||$email===''||$pass==='') { $err='Please fill in all required fields.'; }
        elseif (!filter_var($email,FILTER_VALIDATE_EMAIL)) { $err='Enter a valid email address.'; }
        elseif (strlen($pass)<8) { $err='Password must be at least 8 characters.'; }
        else {
            [$ok,$msg] = client_register($name,$email,$phone,$pass);
            if ($ok) { flash($msg); redirect('dashboard.php'); }
            $err = $msg;
        }
        $tab = 'register';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<title>My Account — Landplan</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/client.css">
</head>
<body>
<div class="auth-wrap">
  <div class="auth-card">
    <div class="brand"><span class="mark"><svg viewBox="0 0 24 24" width="18" height="18"><path d="M12 2 4 5v6c0 5 3.4 9.7 8 11 4.6-1.3 8-6 8-11V5l-8-3zm0 4 4.5 3.4v5.1L12 17.9l-4.5-3.4V9.4L12 6z"/></svg></span><span>LANDPLAN</span></div>
    <?php if ($err): ?><div class="flash error"><?= e($err) ?></div><?php endif; ?>
    <div class="tabs">
      <button type="button" id="tab-login" class="<?= $tab==='login'?'active':'' ?>" onclick="showTab('login')">Sign in</button>
      <button type="button" id="tab-register" class="<?= $tab==='register'?'active':'' ?>" onclick="showTab('register')">Create account</button>
    </div>

    <form method="post" class="auth-form <?= $tab==='login'?'active':'' ?>" id="form-login">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="login">
      <input type="hidden" name="next" value="<?= e($next) ?>">
      <div class="field"><label>Email</label><input type="email" name="email" required></div>
      <div class="field"><label>Password</label><input type="password" name="password" required></div>
      <button class="btn btn-primary btn-block" type="submit">Sign in</button>
    </form>

    <form method="post" class="auth-form <?= $tab==='register'?'active':'' ?>" id="form-register">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="register">
      <div class="field"><label>Full name</label><input type="text" name="name" required></div>
      <div class="field"><label>Email</label><input type="email" name="email" required></div>
      <div class="field"><label>Phone</label><input type="tel" name="phone" placeholder="+254 7XX XXX XXX"></div>
      <div class="field"><label>Password</label><input type="password" name="password" required minlength="8"><span class="hint">At least 8 characters.</span></div>
      <button class="btn btn-primary btn-block" type="submit">Create account</button>
    </form>

    <p class="back-site"><a href="../index.html">← Back to website</a></p>
  </div>
</div>
<script>
function showTab(t){
  document.getElementById('tab-login').classList.toggle('active',t==='login');
  document.getElementById('tab-register').classList.toggle('active',t==='register');
  document.getElementById('form-login').classList.toggle('active',t==='login');
  document.getElementById('form-register').classList.toggle('active',t==='register');
}
</script>
</body>
</html>
