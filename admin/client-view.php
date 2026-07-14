<?php
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/_crud.php';
$pdo = db();

$id = (int)get('id');
$stmt = $pdo->prepare('SELECT * FROM clients WHERE id=?'); $stmt->execute([$id]);
$client = $stmt->fetch();
if (!$client) { http_response_code(404); exit('Client not found.'); }

if (is_post()) {
    csrf_check();
    $action = post('action');

    if ($action === 'update') {
        $email = strtolower(post('email'));
        $pdo->prepare('UPDATE clients SET name=?,email=?,phone=?,notes=? WHERE id=?')
            ->execute([post('name'), $email ?: null, post('phone'), post('notes'), $id]);
        activity_log('update','client',$id);
        flash('Client updated.'); redirect("client-view.php?id=$id");
    }

    if ($action === 'set_password') {
        $pass = post('password');
        if (strlen($pass) < 8) { flash('Password must be at least 8 characters.','error'); redirect("client-view.php?id=$id"); }
        if (!$client['email']) { flash('Add an email address before enabling portal access.','error'); redirect("client-view.php?id=$id"); }
        $pdo->prepare('UPDATE clients SET password_hash=? WHERE id=?')->execute([password_hash($pass,PASSWORD_DEFAULT),$id]);
        activity_log('update','client',$id,'portal password set');
        flash('Portal access enabled. Share the login details with your client.'); redirect("client-view.php?id=$id");
    }

    if ($action === 'upload_doc') {
        $docTitle = post('doc_title') ?: 'Document';
        $path = handle_upload('document','documents',true);
        if ($path) {
            $pdo->prepare('INSERT INTO client_documents (client_id,title,path,uploaded_by) VALUES (?,?,?,?)')
                ->execute([$id,$docTitle,$path,$ADMIN['id']]);
            flash('Document uploaded.');
        }
        redirect("client-view.php?id=$id");
    }

    if ($action === 'delete_doc') {
        $did = post_int('doc_id');
        $d=$pdo->prepare('SELECT path FROM client_documents WHERE id=? AND client_id=?'); $d->execute([$did,$id]);
        if ($p=$d->fetchColumn()) { delete_upload((string)$p); $pdo->prepare('DELETE FROM client_documents WHERE id=?')->execute([$did]); flash('Document removed.'); }
        redirect("client-view.php?id=$id");
    }

    if ($action === 'delete') {
        foreach ($pdo->query("SELECT path FROM client_documents WHERE client_id=$id") as $d) delete_upload($d['path']);
        $pdo->prepare('DELETE FROM clients WHERE id=?')->execute([$id]);
        activity_log('delete','client',$id);
        flash('Client deleted.'); redirect('clients.php');
    }
}

$docs = $pdo->prepare('SELECT * FROM client_documents WHERE client_id=? ORDER BY created_at DESC'); $docs->execute([$id]); $docs=$docs->fetchAll();
$enq  = $pdo->prepare('SELECT id,interest,status,created_at FROM leads WHERE client_id=? ORDER BY created_at DESC'); $enq->execute([$id]); $enq=$enq->fetchAll();
$appts= $pdo->prepare('SELECT * FROM appointments WHERE client_id=? ORDER BY when_at DESC'); $appts->execute([$id]); $appts=$appts->fetchAll();

// saved listings (land/house/project titles)
$saved = [];
$sv = $pdo->prepare('SELECT item_type,item_id FROM saved_listings WHERE client_id=?'); $sv->execute([$id]);
foreach ($sv as $row) {
    $map=['land'=>'land_listings','house'=>'houses','project'=>'projects'];
    if (!isset($map[$row['item_type']])) continue;
    $t=$pdo->prepare("SELECT title,slug FROM `{$map[$row['item_type']]}` WHERE id=?"); $t->execute([$row['item_id']]);
    if ($it=$t->fetch()) $saved[] = ['type'=>$row['item_type'],'title'=>$it['title']];
}

$title = $client['name']; $active='clients';
require __DIR__ . '/_head.php';
?>
<div class="crumbs"><a href="clients.php">Clients</a> / <?= e($client['name']) ?></div>
<div class="page-head">
  <div><h2><?= e($client['name']) ?></h2><p class="sub">Client since <?= e(nice_date($client['created_at'])) ?> · <?= $client['password_hash']?'<span style="color:var(--ok)">Portal active</span>':'No portal login' ?></p></div>
  <div class="spacer"></div>
  <?php if ($client['phone']): ?><a class="btn btn-primary" href="https://wa.me/<?= e(ltrim(preg_replace('/[^0-9]/','',$client['phone']),'0')) ?>" target="_blank">WhatsApp</a><?php endif; ?>
</div>

<div class="split-2">
  <div class="stack">
    <div class="card card-pad">
      <h3 style="font-size:14px;margin-bottom:14px">Details</h3>
      <form method="post" class="form-grid"><?= csrf_field() ?><input type="hidden" name="action" value="update">
        <div class="field"><label>Name</label><input type="text" name="name" value="<?= e($client['name']) ?>" required></div>
        <div class="field"><label>Phone</label><input type="tel" name="phone" value="<?= e($client['phone']) ?>"></div>
        <div class="field full"><label>Email</label><input type="email" name="email" value="<?= e($client['email']) ?>"></div>
        <div class="field full"><label>Notes</label><textarea name="notes"><?= e($client['notes']) ?></textarea></div>
        <div class="field full"><button class="btn btn-primary" type="submit">Save details</button></div>
      </form>
    </div>

    <div class="card">
      <div class="card-head"><h3>Documents</h3><div class="spacer"></div><span class="mini muted"><?= count($docs) ?> file(s)</span></div>
      <div class="card-pad">
        <?php if ($docs): ?>
          <table class="data" style="margin-bottom:14px">
            <?php foreach ($docs as $d): ?>
              <tr>
                <td><a href="../<?= e(ltrim($d['path'],'/')) ?>" target="_blank" style="color:var(--green);font-weight:600"><?= e($d['title']) ?></a><div class="t-sub"><?= e(nice_date($d['created_at'])) ?></div></td>
                <td class="right"><form method="post" style="display:inline"><?= csrf_field() ?><input type="hidden" name="action" value="delete_doc"><input type="hidden" name="doc_id" value="<?= (int)$d['id'] ?>"><button class="btn btn-ghost btn-sm" data-confirm="Remove this document?">Remove</button></form></td>
              </tr>
            <?php endforeach; ?>
          </table>
        <?php else: ?><p class="muted mini" style="margin-bottom:14px">No documents shared yet. Uploads here appear in the client's portal.</p><?php endif; ?>
        <form method="post" enctype="multipart/form-data" class="stack"><?= csrf_field() ?>
          <input type="hidden" name="action" value="upload_doc">
          <div class="field"><label>Document title</label><input type="text" name="doc_title" placeholder="Title Deed / Sale Agreement"></div>
          <div class="field"><label>File</label><input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required></div>
          <button class="btn btn-dark btn-sm" type="submit">Upload &amp; share</button>
        </form>
      </div>
    </div>
  </div>

  <div class="stack">
    <div class="card card-pad">
      <h3 style="font-size:14px;margin-bottom:12px">Portal access</h3>
      <?php if ($client['password_hash']): ?>
        <p class="mini muted" style="margin-bottom:10px">This client can sign in at <b>/client/</b>. You can reset their password below.</p>
      <?php else: ?>
        <p class="mini muted" style="margin-bottom:10px">Set a password to let this client log in to the portal and view their documents.</p>
      <?php endif; ?>
      <form method="post" class="stack"><?= csrf_field() ?><input type="hidden" name="action" value="set_password">
        <div class="field"><label><?= $client['password_hash']?'New password':'Password' ?></label><input type="text" name="password" minlength="8" placeholder="min 8 characters"></div>
        <button class="btn btn-primary btn-sm" type="submit"><?= $client['password_hash']?'Reset password':'Enable portal access' ?></button>
      </form>
    </div>

    <?php if ($saved): ?>
    <div class="card card-pad">
      <h3 style="font-size:14px;margin-bottom:10px">Saved properties</h3>
      <?php foreach ($saved as $sv): ?><div class="mini" style="padding:4px 0"><span class="badge <?= $sv['type']==='land'?'new':'featured' ?>"><?= e(ucfirst($sv['type'])) ?></span> <?= e($sv['title']) ?></div><?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($enq): ?>
    <div class="card card-pad">
      <h3 style="font-size:14px;margin-bottom:10px">Enquiries</h3>
      <?php foreach ($enq as $l): ?>
        <div class="mini" style="display:flex;justify-content:space-between;padding:4px 0">
          <a href="lead-view.php?id=<?= (int)$l['id'] ?>" style="color:var(--green)"><?= e($l['interest'] ?: 'Enquiry') ?></a>
          <span class="badge <?= e($l['status']) ?>"><?= e(ucfirst($l['status'])) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="card card-pad">
      <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="delete">
        <button class="btn btn-ghost" data-confirm="Delete this client and all their documents?">Delete client</button>
      </form>
    </div>
  </div>
</div>
<?php require __DIR__ . '/_foot.php'; ?>
