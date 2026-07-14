<?php
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/_crud.php';
$pdo = db();

if (is_post()) {
    csrf_check();
    if (post('action') === 'delete') {
        $pdo->prepare('DELETE FROM appointments WHERE id=?')->execute([post_int('id')]);
        flash('Appointment deleted.'); redirect('appointments.php');
    }
    if (post('action') === 'status') {
        $pdo->prepare('UPDATE appointments SET status=? WHERE id=?')->execute([post('status'),post_int('id')]);
        flash('Appointment updated.'); redirect('appointments.php');
    }
    // create / update
    $id=post_int('id'); $name=post('name'); $phone=post('phone');
    $when=post('when_at'); $location=post('location'); $notes=post('notes'); $status=post('status') ?: 'scheduled';
    $leadId=post_int('lead_id') ?: null; $clientId=post_int('client_id') ?: null;
    if ($name==='' || $when==='') { flash('Name and date/time are required.','error'); redirect('appointments.php'); }
    $whenAt = date('Y-m-d H:i:s', strtotime($when));
    if ($id) {
        $pdo->prepare('UPDATE appointments SET name=?,phone=?,when_at=?,location=?,notes=?,status=? WHERE id=?')
            ->execute([$name,$phone,$whenAt,$location,$notes,$status,$id]);
    } else {
        $pdo->prepare('INSERT INTO appointments (client_id,lead_id,name,phone,when_at,location,notes,status) VALUES (?,?,?,?,?,?,?,?)')
            ->execute([$clientId,$leadId,$name,$phone,$whenAt,$location,$notes,$status]);
    }
    flash('Appointment saved.'); redirect('appointments.php');
}

$edit=null;
if ($eid=(int)get('edit')) { $s=$pdo->prepare('SELECT * FROM appointments WHERE id=?'); $s->execute([$eid]); $edit=$s->fetch() ?: null; }

$rows = $pdo->query("SELECT a.*, c.name AS client_name FROM appointments a LEFT JOIN clients c ON c.id=a.client_id
                     ORDER BY (a.status='scheduled') DESC, a.when_at DESC LIMIT 200")->fetchAll();

// prefill from lead-view link
$pf = ['name'=>get('name'),'phone'=>get('phone'),'lead_id'=>(int)get('lead')];

$title='Appointments'; $active='appointments';
require __DIR__ . '/_head.php';
?>
<div class="page-head"><h2>Appointments &amp; Site Visits</h2><p class="sub"><?= count($rows) ?> total</p></div>
<div class="split-2">
  <div class="card">
    <div class="card-head"><h3>Schedule</h3></div>
    <?php if ($rows): ?>
    <div class="table-wrap"><table class="data">
      <thead><tr><th>When</th><th>Who</th><th>Location</th><th>Status</th><th class="right">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td class="nowrap"><b><?= e(nice_datetime($r['when_at'])) ?></b></td>
          <td><?= e($r['name']) ?><?php if($r['phone']):?><div class="t-sub"><?= e($r['phone']) ?></div><?php endif;?></td>
          <td class="mini"><?= e($r['location'] ?: '—') ?></td>
          <td>
            <form method="post" style="display:inline"><?= csrf_field() ?>
              <input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <select name="status" data-autosubmit style="padding:4px 8px;border:1px solid var(--line);border-radius:6px;font-size:12px">
                <?php foreach (['scheduled'=>'Scheduled','done'=>'Done','cancelled'=>'Cancelled'] as $v=>$l): ?>
                  <option value="<?= $v ?>" <?= $r['status']===$v?'selected':'' ?>><?= $l ?></option>
                <?php endforeach; ?>
              </select>
            </form>
          </td>
          <td><div class="row-actions">
            <a class="btn btn-light btn-sm" href="?edit=<?= (int)$r['id'] ?>">Edit</a>
            <form method="post" style="display:inline"><?= csrf_field() ?>
              <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <button class="btn btn-ghost btn-sm" data-confirm="Delete this appointment?">✕</button>
            </form>
          </div></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
    <?php else: ?><div class="empty"><p>No appointments scheduled.</p></div><?php endif; ?>
  </div>

  <div class="card card-pad">
    <h3 style="font-size:14px;margin-bottom:14px"><?= $edit?'Edit appointment':'New appointment' ?></h3>
    <form method="post" class="stack"><?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
      <input type="hidden" name="lead_id" value="<?= (int)($edit['lead_id'] ?? $pf['lead_id']) ?>">
      <div class="field"><label>Name *</label><input type="text" name="name" value="<?= e($edit['name'] ?? $pf['name']) ?>" required></div>
      <div class="field"><label>Phone</label><input type="tel" name="phone" value="<?= e($edit['phone'] ?? $pf['phone']) ?>"></div>
      <div class="field"><label>Date &amp; time *</label><input type="datetime-local" name="when_at" value="<?= $edit?e(date('Y-m-d\TH:i',strtotime((string)$edit['when_at']))):'' ?>" required></div>
      <div class="field"><label>Location</label><input type="text" name="location" value="<?= e($edit['location'] ?? '') ?>" placeholder="Kitengela site office"></div>
      <div class="field"><label>Notes</label><textarea name="notes"><?= e($edit['notes'] ?? '') ?></textarea></div>
      <div class="field"><label>Status</label><?= status_select($edit['status'] ?? 'scheduled',['scheduled'=>'Scheduled','done'=>'Done','cancelled'=>'Cancelled'],'status') ?></div>
      <div class="form-actions"><button class="btn btn-primary" type="submit"><?= $edit?'Update':'Add' ?></button><?php if($edit):?><a class="btn btn-ghost" href="appointments.php">Cancel</a><?php endif;?></div>
    </form>
  </div>
</div>
<?php require __DIR__ . '/_foot.php'; ?>
