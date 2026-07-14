<?php
require_once __DIR__ . '/../app/auth.php';
boot_session();
$CLIENT = require_client();
$pdo = db();

$stmt = $pdo->prepare('SELECT id, interest, message, source, status, created_at FROM leads WHERE client_id=? ORDER BY created_at DESC');
$stmt->execute([$CLIENT['id']]);
$rows = $stmt->fetchAll();

$labels = ['new'=>'Received','contacted'=>'In progress','qualified'=>'In progress','won'=>'Completed','lost'=>'Closed'];

$title='My Enquiries'; $active='enquiries';
require __DIR__ . '/_head.php';
?>
<h1 class="page-title">My enquiries</h1>
<p class="page-sub">Track the enquiries you've made and their progress.</p>

<div class="card">
  <?php if ($rows): ?>
  <table class="data">
    <thead><tr><th>Interest</th><th>Message</th><th>Status</th><th class="right">Date</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><b><?= e($r['interest'] ?: 'General enquiry') ?></b></td>
        <td class="mini muted"><?= e(excerpt($r['message'] ?: '', 14)) ?></td>
        <td><span class="badge <?= e($r['status']) ?>"><?= e($labels[$r['status']] ?? ucfirst($r['status'])) ?></span></td>
        <td class="right mini muted nowrap"><?= e(nice_date($r['created_at'])) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
  <div class="empty" style="padding:44px"><p>You haven't made any enquiries yet.</p>
    <a class="btn btn-primary btn-sm" href="../contact.html" style="margin-top:12px">Make an enquiry</a></div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/_foot.php'; ?>
