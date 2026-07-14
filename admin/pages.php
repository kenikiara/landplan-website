<?php
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/_crud.php';
$pdo = db();

if (is_post() && post('action') === 'delete') {
    csrf_check();
    $pdo->prepare('DELETE FROM pages WHERE id=?')->execute([post_int('id')]);
    activity_log('delete','page',post_int('id'));
    flash('Page deleted.'); redirect('pages.php');
}

$rows = $pdo->query('SELECT * FROM pages ORDER BY title')->fetchAll();
$title='Pages'; $active='pages';
require __DIR__ . '/_head.php';
?>
<div class="page-head">
  <div><h2>Content Pages</h2><p class="sub">Editable pages such as About, Terms and Privacy</p></div>
  <div class="spacer"></div>
  <a class="btn btn-primary" href="page-form.php"><svg viewBox="0 0 24 24"><path d="M11 5h2v6h6v2h-6v6h-2v-6H5v-2h6V5z"/></svg> Add Page</a>
</div>
<div class="card">
  <?php if ($rows): ?>
  <div class="table-wrap"><table class="data">
    <thead><tr><th>Title</th><th>Slug</th><th>Status</th><th class="right">Updated</th><th class="right">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td class="t-title"><?= e($r['title']) ?></td>
        <td class="mini muted">/<?= e($r['slug']) ?></td>
        <td><span class="badge <?= e($r['status']) ?>"><?= e(ucfirst($r['status'])) ?></span></td>
        <td class="right mini muted nowrap"><?= e(nice_date($r['updated_at'])) ?></td>
        <td><div class="row-actions">
          <a class="btn btn-light btn-sm" href="page-form.php?id=<?= (int)$r['id'] ?>">Edit</a>
          <form method="post" style="display:inline"><?= csrf_field() ?>
            <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn btn-ghost btn-sm" data-confirm="Delete “<?= e($r['title']) ?>”?">Delete</button>
          </form>
        </div></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php else: ?><div class="empty"><p>No pages yet.</p></div><?php endif; ?>
</div>
<?php require __DIR__ . '/_foot.php'; ?>
