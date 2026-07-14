<?php
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/_crud.php';
$pdo = db();

if (is_post() && post('action') === 'delete') {
    csrf_check();
    $id = post_int('id');
    delete_entity('services', $id);
    activity_log('delete','service',$id);
    flash('Service deleted.'); redirect('services.php');
}

$rows = $pdo->query('SELECT * FROM services ORDER BY sort, id')->fetchAll();
$title='Services'; $active='services';
require __DIR__ . '/_head.php';
?>
<div class="page-head">
  <div><h2>Services</h2><p class="sub"><?= count($rows) ?> service<?= count($rows)===1?'':'s' ?> — the “What We Do” cards</p></div>
  <div class="spacer"></div>
  <a class="btn btn-primary" href="service-form.php"><svg viewBox="0 0 24 24"><path d="M11 5h2v6h6v2h-6v6h-2v-6H5v-2h6V5z"/></svg> Add Service</a>
</div>
<div class="card">
  <?php if ($rows): ?>
  <div class="table-wrap"><table class="data">
    <thead><tr><th></th><th>Title</th><th>Excerpt</th><th>Order</th><th>Status</th><th class="right">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><img class="t-thumb" src="<?= $r['cover_image']?e('../'.ltrim($r['cover_image'],'/')):'' ?>" alt=""></td>
        <td class="t-title"><?= e($r['title']) ?></td>
        <td class="mini muted"><?= e(excerpt($r['excerpt'] ?: '', 12)) ?></td>
        <td><?= (int)$r['sort'] ?></td>
        <td><span class="badge <?= e($r['status']) ?>"><?= e(ucfirst($r['status'])) ?></span></td>
        <td><div class="row-actions">
          <a class="btn btn-light btn-sm" href="service-form.php?id=<?= (int)$r['id'] ?>">Edit</a>
          <form method="post" style="display:inline"><?= csrf_field() ?>
            <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn btn-ghost btn-sm" data-confirm="Delete “<?= e($r['title']) ?>”?">Delete</button>
          </form>
        </div></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php else: ?>
    <div class="empty"><p>No services yet.</p><a class="btn btn-primary btn-sm" href="service-form.php" style="margin-top:12px">Add a service</a></div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/_foot.php'; ?>
