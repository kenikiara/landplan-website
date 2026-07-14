<?php
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/_crud.php';
$pdo = db();

if (is_post() && post('action') === 'delete') {
    csrf_check();
    $pdo->prepare('DELETE FROM leads WHERE id=?')->execute([post_int('id')]);
    activity_log('delete','lead',post_int('id'));
    flash('Enquiry deleted.'); redirect('leads.php');
}

$q = get('q'); $fstatus = get('status');
$page = max(1,(int)get('page','1')); $per = 20;
$where=[]; $args=[];
if ($q!=='')       { $where[]='(name LIKE ? OR email LIKE ? OR phone LIKE ?)'; $like="%$q%"; array_push($args,$like,$like,$like); }
if ($fstatus!=='') { $where[]='status=?'; $args[]=$fstatus; }
$wsql=$where?('WHERE '.implode(' AND ',$where)):'';
$total=(int)(function() use ($pdo,$wsql,$args){ $s=$pdo->prepare("SELECT COUNT(*) FROM leads $wsql"); $s->execute($args); return $s->fetchColumn(); })();
$pg=paginate($total,$per,$page);
$stmt=$pdo->prepare("SELECT l.*, u.name AS agent FROM leads l LEFT JOIN users u ON u.id=l.assigned_to $wsql ORDER BY l.created_at DESC LIMIT {$pg['perPage']} OFFSET {$pg['offset']}");
$stmt->execute($args); $rows=$stmt->fetchAll();

$counts = [];
foreach ($pdo->query("SELECT status, COUNT(*) c FROM leads GROUP BY status") as $r) $counts[$r['status']]=$r['c'];

$title='Leads'; $active='leads';
require __DIR__ . '/_head.php';
?>
<div class="page-head"><h2>Enquiries &amp; Leads</h2><p class="sub"><?= $total ?> total · <?= (int)($counts['new'] ?? 0) ?> new</p></div>

<form class="toolbar" method="get">
  <div class="search"><input type="search" name="q" value="<?= e($q) ?>" placeholder="Search name, email or phone…"></div>
  <select name="status" data-autosubmit>
    <option value="">All statuses</option>
    <?php foreach (['new'=>'New','contacted'=>'Contacted','qualified'=>'Qualified','won'=>'Won','lost'=>'Lost'] as $v=>$l): ?>
      <option value="<?= $v ?>" <?= $fstatus===$v?'selected':'' ?>><?= $l ?> (<?= (int)($counts[$v] ?? 0) ?>)</option>
    <?php endforeach; ?>
  </select>
  <button class="btn btn-light btn-sm">Filter</button>
</form>

<div class="card">
  <?php if ($rows): ?>
  <div class="table-wrap"><table class="data">
    <thead><tr><th>Name</th><th>Contact</th><th>Interest</th><th>Source</th><th>Agent</th><th>Status</th><th class="right">When</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr onclick="location='lead-view.php?id=<?= (int)$r['id'] ?>'" style="cursor:pointer">
        <td class="t-title"><?= e($r['name']) ?></td>
        <td class="mini"><?= e($r['phone']) ?><?php if($r['email']):?><br><span class="muted"><?= e($r['email']) ?></span><?php endif;?></td>
        <td class="mini"><?= e($r['interest'] ?: '-') ?></td>
        <td class="mini muted"><?= e($r['source'] ?: '-') ?></td>
        <td class="mini muted"><?= e($r['agent'] ?: '-') ?></td>
        <td><span class="badge <?= e($r['status']) ?>"><?= e(ucfirst($r['status'])) ?></span></td>
        <td class="right mini muted nowrap"><?= e(time_ago($r['created_at'])) ?></td>
        <td class="right"><span class="muted">›</span></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php else: ?>
    <div class="empty"><svg viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z"/></svg>
      <p>No enquiries<?= $q||$fstatus?' match your filter':' yet' ?>. They arrive here from your website contact forms.</p></div>
  <?php endif; ?>
</div>
<?= pagination_html($pg,'leads.php?q='.urlencode($q).'&status='.urlencode($fstatus)) ?>
<?php require __DIR__ . '/_foot.php'; ?>
