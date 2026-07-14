<?php
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/_crud.php';
$pdo = db();

if (is_post()) {
    csrf_check();
    if (post('action') === 'create') {
        $name=post('name'); $email=strtolower(post('email')); $phone=post('phone'); $notes=post('notes');
        if ($name==='') { flash('Name is required.','error'); redirect('clients.php'); }
        if ($email!=='' && !filter_var($email,FILTER_VALIDATE_EMAIL)) { flash('Enter a valid email.','error'); redirect('clients.php'); }
        if ($email!=='') {
            $c=$pdo->prepare('SELECT id FROM clients WHERE email=?'); $c->execute([$email]);
            if ($c->fetch()) { flash('A client with that email already exists.','error'); redirect('clients.php'); }
        }
        $pdo->prepare('INSERT INTO clients (name,email,phone,notes) VALUES (?,?,?,?)')
            ->execute([$name,$email ?: null,$phone,$notes]);
        $cid = (int)$pdo->lastInsertId();
        activity_log('create','client',$cid,$name);
        flash('Client added.'); redirect('client-view.php?id=' . $cid);
    }
}

$q = get('q');
$sql = 'SELECT c.*, (c.password_hash IS NOT NULL) AS has_login,
        (SELECT COUNT(*) FROM leads WHERE client_id=c.id) AS enquiries
        FROM clients c';
$args=[];
if ($q!=='') { $sql .= ' WHERE c.name LIKE ? OR c.email LIKE ? OR c.phone LIKE ?'; $like="%$q%"; $args=[$like,$like,$like]; }
$sql .= ' ORDER BY c.created_at DESC LIMIT 300';
$stmt=$pdo->prepare($sql); $stmt->execute($args); $rows=$stmt->fetchAll();

$title='Clients'; $active='clients';
require __DIR__ . '/_head.php';
?>
<div class="page-head"><h2>Clients</h2><p class="sub"><?= count($rows) ?> record<?= count($rows)===1?'':'s' ?></p></div>
<div class="split-2">
  <div class="card">
    <div class="card-head">
      <h3>All clients</h3><div class="spacer"></div>
      <form method="get" style="display:flex;gap:8px"><input type="search" name="q" value="<?= e($q) ?>" placeholder="Search…" style="padding:7px 11px;border:1.5px solid var(--line);border-radius:8px"></form>
    </div>
    <?php if ($rows): ?>
    <div class="table-wrap"><table class="data">
      <thead><tr><th>Name</th><th>Contact</th><th>Portal</th><th>Enquiries</th><th class="right"></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr onclick="location='client-view.php?id=<?= (int)$r['id'] ?>'" style="cursor:pointer">
          <td class="t-title"><?= e($r['name']) ?></td>
          <td class="mini"><?= e($r['phone'] ?: '') ?><?php if($r['email']):?><br><span class="muted"><?= e($r['email']) ?></span><?php endif;?></td>
          <td><?= $r['has_login']?'<span class="badge won">Active</span>':'<span class="badge draft">No login</span>' ?></td>
          <td><?= (int)$r['enquiries'] ?></td>
          <td class="right muted">›</td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
    <?php else: ?><div class="empty"><p>No clients<?= $q?' match your search':' yet' ?>.</p></div><?php endif; ?>
  </div>

  <div class="card card-pad">
    <h3 style="font-size:14px;margin-bottom:14px">Add a client</h3>
    <form method="post" class="stack"><?= csrf_field() ?>
      <input type="hidden" name="action" value="create">
      <div class="field"><label>Full name *</label><input type="text" name="name" required></div>
      <div class="field"><label>Email</label><input type="email" name="email"></div>
      <div class="field"><label>Phone</label><input type="tel" name="phone"></div>
      <div class="field"><label>Notes</label><textarea name="notes"></textarea></div>
      <button class="btn btn-primary btn-block" type="submit">Add client</button>
    </form>
  </div>
</div>
<?php require __DIR__ . '/_foot.php'; ?>
