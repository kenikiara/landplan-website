<?php
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/_crud.php';
$pdo = db();

$id = (int)get('id');
$stmt = $pdo->prepare('SELECT l.*, c.name AS client_name FROM leads l LEFT JOIN clients c ON c.id=l.client_id WHERE l.id=?');
$stmt->execute([$id]);
$lead = $stmt->fetch();
if (!$lead) { http_response_code(404); exit('Enquiry not found.'); }

if (is_post()) {
    csrf_check();
    $action = post('action');

    if ($action === 'update') {
        $pdo->prepare('UPDATE leads SET status=?, assigned_to=?, admin_notes=? WHERE id=?')
            ->execute([post('status'), post_int('assigned_to') ?: null, post('admin_notes'), $id]);
        activity_log('update','lead',$id);
        flash('Enquiry updated.'); redirect("lead-view.php?id=$id");
    }

    if ($action === 'convert') {
        if ($lead['client_id']) { flash('This enquiry is already linked to a client.','info'); redirect("lead-view.php?id=$id"); }
        // reuse existing client with same email if present
        $cid = null;
        if ($lead['email']) {
            $c = $pdo->prepare('SELECT id FROM clients WHERE email=?'); $c->execute([strtolower($lead['email'])]);
            $cid = $c->fetchColumn() ?: null;
        }
        if (!$cid) {
            $pdo->prepare('INSERT INTO clients (name,email,phone,notes) VALUES (?,?,?,?)')
                ->execute([$lead['name'], $lead['email'] ? strtolower($lead['email']) : null, $lead['phone'], 'Converted from enquiry #' . $id]);
            $cid = (int)$pdo->lastInsertId();
        }
        $pdo->prepare('UPDATE leads SET client_id=?, status=IF(status="new","qualified",status) WHERE id=?')->execute([$cid,$id]);
        activity_log('convert','lead',$id,'to client #' . $cid);
        flash('Client record created.'); redirect("client-view.php?id=$cid");
    }

    if ($action === 'delete') {
        $pdo->prepare('DELETE FROM leads WHERE id=?')->execute([$id]);
        activity_log('delete','lead',$id);
        flash('Enquiry deleted.'); redirect('leads.php');
    }
}

// resolve linked item
$item = null;
if ($lead['item_type'] && $lead['item_id']) {
    $map = ['land'=>['land_listings','land-detail.php'], 'house'=>['houses','house-detail.php'], 'project'=>['projects','project-detail.php']];
    if (isset($map[$lead['item_type']])) {
        [$tbl,$page] = $map[$lead['item_type']];
        $s=$pdo->prepare("SELECT title, slug FROM `$tbl` WHERE id=?"); $s->execute([$lead['item_id']]);
        if ($it=$s->fetch()) $item = ['title'=>$it['title'], 'url'=>'../'.$page.'?slug='.$it['slug']];
    }
}
$staff = $pdo->query('SELECT id,name FROM users ORDER BY name')->fetchAll();

$title='Enquiry from ' . $lead['name']; $active='leads';
require __DIR__ . '/_head.php';
?>
<div class="crumbs"><a href="leads.php">Leads</a> / <?= e($lead['name']) ?></div>
<div class="page-head">
  <div><h2><?= e($lead['name']) ?></h2><p class="sub">Received <?= e(nice_datetime($lead['created_at'])) ?></p></div>
  <div class="spacer"></div>
  <?php if ($lead['phone']): ?><a class="btn btn-light" href="tel:<?= e(preg_replace('/[^0-9+]/','',$lead['phone'])) ?>">Call</a><?php endif; ?>
  <?php if ($lead['phone']): ?><a class="btn btn-primary" href="https://wa.me/<?= e(ltrim(preg_replace('/[^0-9]/','',$lead['phone']),'0')) ?>" target="_blank">WhatsApp</a><?php endif; ?>
</div>

<div class="split-2">
  <div class="stack">
    <div class="card card-pad">
      <h3 style="font-size:14px;margin-bottom:14px">Enquiry details</h3>
      <table class="data">
        <tr><th style="width:130px">Name</th><td><?= e($lead['name']) ?></td></tr>
        <tr><th>Phone</th><td><?= e($lead['phone'] ?: '-') ?></td></tr>
        <tr><th>Email</th><td><?= $lead['email']?'<a href="mailto:'.e($lead['email']).'" style="color:var(--green)">'.e($lead['email']).'</a>':'-' ?></td></tr>
        <tr><th>Interested in</th><td><?= e($lead['interest'] ?: '-') ?></td></tr>
        <tr><th>Source</th><td><?= e($lead['source'] ?: '-') ?></td></tr>
        <?php if (!empty($lead['page_url'])): ?><tr><th>Page they were on</th><td><a href="<?= e($lead['page_url']) ?>" target="_blank" style="color:var(--green);font-weight:600"><?= e($lead['page_url']) ?> ↗</a></td></tr><?php endif; ?>
        <?php if ($item): ?><tr><th>Property</th><td><a href="<?= e($item['url']) ?>" target="_blank" style="color:var(--green)"><?= e($item['title']) ?> ↗</a></td></tr><?php endif; ?>
        <?php if ($lead['client_id']): ?><tr><th>Client</th><td><a href="client-view.php?id=<?= (int)$lead['client_id'] ?>" style="color:var(--green)"><?= e($lead['client_name']) ?> ↗</a></td></tr><?php endif; ?>
      </table>
      <?php if ($lead['message']): ?>
        <h4 style="font-size:13px;margin:18px 0 6px">Message</h4>
        <p style="background:var(--soft);padding:14px;border-radius:8px;white-space:pre-wrap"><?= e($lead['message']) ?></p>
      <?php endif; ?>
    </div>

    <div class="card card-pad">
      <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
        <?php if (!$lead['client_id']): ?>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="convert">
          <button class="btn btn-dark" data-confirm="Create a client record from this enquiry?">Convert to client</button>
        </form>
        <?php endif; ?>
        <a class="btn btn-light" href="appointments.php?lead=<?= $id ?>&name=<?= urlencode($lead['name']) ?>&phone=<?= urlencode((string)$lead['phone']) ?>">Book site visit</a>
        <div class="spacer" style="flex:1"></div>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="delete">
          <button class="btn btn-ghost" data-confirm="Delete this enquiry permanently?">Delete</button>
        </form>
      </div>
    </div>
  </div>

  <div class="card card-pad">
    <h3 style="font-size:14px;margin-bottom:14px">Manage</h3>
    <form method="post" class="stack"><?= csrf_field() ?>
      <input type="hidden" name="action" value="update">
      <div class="field"><label>Status</label>
        <?= status_select($lead['status'], ['new'=>'New','contacted'=>'Contacted','qualified'=>'Qualified','won'=>'Won','lost'=>'Lost'], 'status') ?>
      </div>
      <div class="field"><label>Assigned to</label>
        <select name="assigned_to">
          <option value="">Unassigned</option>
          <?php foreach ($staff as $u): ?>
            <option value="<?= (int)$u['id'] ?>" <?= $lead['assigned_to']==$u['id']?'selected':'' ?>><?= e($u['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field"><label>Internal notes</label><textarea name="admin_notes" class="tall"><?= e($lead['admin_notes']) ?></textarea></div>
      <button class="btn btn-primary btn-block" type="submit">Save changes</button>
    </form>
  </div>
</div>
<?php require __DIR__ . '/_foot.php'; ?>
