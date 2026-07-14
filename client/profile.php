<?php
require_once __DIR__ . '/../app/auth.php';
boot_session();
$CLIENT = require_client();
$pdo = db();

if (is_post()) {
    csrf_check();
    if (post('action') === 'details') {
        $name=post('name'); $phone=post('phone'); $email=strtolower(post('email'));
        if ($name==='' || $email==='') { flash('Name and email are required.','error'); redirect('profile.php'); }
        if (!filter_var($email,FILTER_VALIDATE_EMAIL)) { flash('Enter a valid email.','error'); redirect('profile.php'); }
        // ensure email not used by another client
        $c=$pdo->prepare('SELECT id FROM clients WHERE email=? AND id<>?'); $c->execute([$email,$CLIENT['id']]);
        if ($c->fetch()) { flash('That email is already in use.','error'); redirect('profile.php'); }
        $pdo->prepare('UPDATE clients SET name=?,email=?,phone=? WHERE id=?')->execute([$name,$email,$phone,$CLIENT['id']]);
        flash('Profile updated.'); redirect('profile.php');
    }
    if (post('action') === 'password') {
        $cur=post('current'); $new=post('new');
        $h=$pdo->prepare('SELECT password_hash FROM clients WHERE id=?'); $h->execute([$CLIENT['id']]);
        if (!password_verify($cur, (string)$h->fetchColumn())) { flash('Current password is incorrect.','error'); redirect('profile.php'); }
        if (strlen($new)<8) { flash('New password must be at least 8 characters.','error'); redirect('profile.php'); }
        $pdo->prepare('UPDATE clients SET password_hash=? WHERE id=?')->execute([password_hash($new,PASSWORD_DEFAULT),$CLIENT['id']]);
        flash('Password changed.'); redirect('profile.php');
    }
}

// refresh from DB (name may have changed)
$c = $pdo->prepare('SELECT * FROM clients WHERE id=?'); $c->execute([$CLIENT['id']]); $me = $c->fetch();

$title='Profile'; $active='profile';
require __DIR__ . '/_head.php';
?>
<h1 class="page-title">My profile</h1>
<p class="page-sub">Update your contact details and password.</p>

<div class="grid grid-2">
  <div class="card card-pad">
    <h3 style="font-size:16px;margin-bottom:16px">Your details</h3>
    <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="details">
      <div class="field"><label>Full name</label><input type="text" name="name" value="<?= e($me['name']) ?>" required></div>
      <div class="field"><label>Email</label><input type="email" name="email" value="<?= e($me['email']) ?>" required></div>
      <div class="field"><label>Phone</label><input type="tel" name="phone" value="<?= e($me['phone']) ?>"></div>
      <button class="btn btn-primary" type="submit">Save details</button>
    </form>
  </div>

  <div class="card card-pad">
    <h3 style="font-size:16px;margin-bottom:16px">Change password</h3>
    <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="password">
      <div class="field"><label>Current password</label><input type="password" name="current" required></div>
      <div class="field"><label>New password</label><input type="password" name="new" required minlength="8"><span class="hint">At least 8 characters.</span></div>
      <button class="btn btn-primary" type="submit">Change password</button>
    </form>
  </div>
</div>
<?php require __DIR__ . '/_foot.php'; ?>
