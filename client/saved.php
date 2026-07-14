<?php
require_once __DIR__ . '/../app/auth.php';
boot_session();
$CLIENT = require_client();
$pdo = db();

if (is_post() && post('action') === 'unsave') {
    csrf_check();
    $pdo->prepare('DELETE FROM saved_listings WHERE id=? AND client_id=?')->execute([post_int('save_id'), $CLIENT['id']]);
    flash('Removed from saved.');
    redirect('saved.php');
}

$saved = client_saved_items((int)$CLIENT['id']);
$title='Saved Properties'; $active='saved';
require __DIR__ . '/_head.php';
?>
<h1 class="page-title">Saved properties</h1>
<p class="page-sub"><?= count($saved) ?> propert<?= count($saved)===1?'y':'ies' ?> you're keeping an eye on.</p>

<?php if ($saved): ?>
<div class="props">
  <?php foreach ($saved as $it): ?>
    <div class="prop">
      <a href="<?= e($it['url']) ?>" target="_blank"><img src="<?= $it['image']?e('../'.ltrim($it['image'],'/')):'' ?>" alt=""></a>
      <div class="b">
        <span class="badge <?= e($it['type']) ?>"><?= e(ucfirst($it['type'])) ?></span>
        <h4 style="margin-top:8px"><a href="<?= e($it['url']) ?>" target="_blank"><?= e($it['title']) ?></a></h4>
        <?php if ($it['price']!==null): ?><div class="price"><?= e(ksh($it['price'])) ?></div><?php endif; ?>
        <form method="post" style="margin-top:12px"><?= csrf_field() ?>
          <input type="hidden" name="action" value="unsave"><input type="hidden" name="save_id" value="<?= (int)$it['save_id'] ?>">
          <button class="btn btn-light btn-sm btn-block" data-confirm="Remove from saved?">Remove</button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php else: ?>
<div class="card card-pad"><div class="empty">
  <p>You haven't saved any properties yet. Tap the ♥ on any listing to save it here.</p>
  <a class="btn btn-primary btn-sm" href="../land.html" style="margin-top:12px">Browse land for sale</a>
</div></div>
<?php endif; ?>
<?php require __DIR__ . '/_foot.php'; ?>
