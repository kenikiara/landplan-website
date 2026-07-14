<?php
require_once __DIR__ . '/_guard.php';
require_admin('admin');   // only administrators manage staff
require_once __DIR__ . '/_crud.php';
$pdo = db();

if (is_post()) {
    csrf_check();
    if (post('action') === 'delete') {
        $id = post_int('id');
        if ($id === (int)$ADMIN['id']) { flash('You cannot delete your own account.','error'); redirect('users.php'); }
        $pdo->prepare('DELETE FROM users WHERE id=?')->execute([$id]);
        activity_log('delete','user',$id);
        flash('Staff member removed.'); redirect('users.php');
    }
    $id=post_int('id'); $name=post('name'); $email=strtolower(post('email'));
    $role=post('role')==='admin'?'admin':'editor'; $pass=post('password');
    $errors=[];
    if ($name==='' || $email==='') $errors[]='Name and email are required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[]='Enter a valid email.';
    if (!$id && strlen($pass) < 8) $errors[]='Password must be at least 8 characters.';
    // unique email
    $chk=$pdo->prepare('SELECT id FROM users WHERE email=? AND id<>?'); $chk->execute([$email,$id]);
    if ($chk->fetch()) $errors[]='That email is already in use.';

    if ($errors) { flash(implode(' ',$errors),'error'); redirect('users.php' . ($id?"?edit=$id":'')); }

    if ($id) {
        if ($pass !== '') {
            $pdo->prepare('UPDATE users SET name=?,email=?,role=?,password_hash=? WHERE id=?')
                ->execute([$name,$email,$role,password_hash($pass,PASSWORD_DEFAULT),$id]);
        } else {
            $pdo->prepare('UPDATE users SET name=?,email=?,role=? WHERE id=?')->execute([$name,$email,$role,$id]);
        }
        activity_log('update','user',$id,$name);
    } else {
        $pdo->prepare('INSERT INTO users (name,email,role,password_hash) VALUES (?,?,?,?)')
            ->execute([$name,$email,$role,password_hash($pass,PASSWORD_DEFAULT)]);
        activity_log('create','user',(int)$pdo->lastInsertId(),$name);
    }
    flash('Staff member saved.'); redirect('users.php');
}

$edit=null;
if ($eid=(int)get('edit')) { $s=$pdo->prepare('SELECT * FROM users WHERE id=?'); $s->execute([$eid]); $edit=$s->fetch() ?: null; }
$rows = $pdo->query('SELECT id,name,email,role,last_login,created_at FROM users ORDER BY created_at')->fetchAll();

$title='Staff Users'; $active='users';
require __DIR__ . '/_head.php';
?>
<div class="page-head"><h2>Staff Users</h2><p class="sub">People who can sign in to this dashboard.</p></div>
<div class="split-2">
  <div class="card">
    <div class="card-head"><h3>All staff</h3></div>
    <div class="table-wrap"><table class="data">
      <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Last login</th><th class="right">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td class="t-title"><?= e($r['name']) ?><?= $r['id']==$ADMIN['id']?' <span class="mini muted">(you)</span>':'' ?></td>
          <td class="mini"><?= e($r['email']) ?></td>
          <td><span class="badge <?= $r['role']==='admin'?'featured':'draft' ?>"><?= e(ucfirst($r['role'])) ?></span></td>
          <td class="mini muted nowrap"><?= $r['last_login']?e(nice_datetime($r['last_login'])):'—' ?></td>
          <td><div class="row-actions">
            <a class="btn btn-light btn-sm" href="?edit=<?= (int)$r['id'] ?>">Edit</a>
            <?php if ($r['id']!=$ADMIN['id']): ?>
            <form method="post" style="display:inline"><?= csrf_field() ?>
              <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <button class="btn btn-ghost btn-sm" data-confirm="Remove <?= e($r['name']) ?>?">Delete</button>
            </form>
            <?php endif; ?>
          </div></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  </div>

  <div class="card card-pad">
    <h3 style="font-size:14px;margin-bottom:14px"><?= $edit?'Edit staff member':'Add staff member' ?></h3>
    <form method="post" class="stack"><?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
      <div class="field"><label>Full name *</label><input type="text" name="name" value="<?= e($edit['name'] ?? '') ?>" required></div>
      <div class="field"><label>Email *</label><input type="email" name="email" value="<?= e($edit['email'] ?? '') ?>" required></div>
      <div class="field"><label>Role</label>
        <select name="role">
          <option value="editor" <?= ($edit['role'] ?? '')==='editor'?'selected':'' ?>>Editor — manage content</option>
          <option value="admin" <?= ($edit['role'] ?? '')==='admin'?'selected':'' ?>>Administrator — full access</option>
        </select>
      </div>
      <div class="field"><label>Password <?= $edit?'(leave blank to keep)':'*' ?></label><input type="password" name="password" <?= $edit?'':'required minlength="8"' ?>><span class="hint">Minimum 8 characters.</span></div>
      <div class="form-actions"><button class="btn btn-primary" type="submit"><?= $edit?'Update':'Add' ?></button><?php if($edit):?><a class="btn btn-ghost" href="users.php">Cancel</a><?php endif;?></div>
    </form>
  </div>
</div>
<?php require __DIR__ . '/_foot.php'; ?>
