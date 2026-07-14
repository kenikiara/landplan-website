<?php
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/_crud.php';
$pdo = db();

if (is_post()) {
    csrf_check();
    if (post('action') === 'delete') {
        $id = post_int('id');
        $stmt=$pdo->prepare('SELECT photo FROM testimonials WHERE id=?'); $stmt->execute([$id]);
        if ($p=$stmt->fetchColumn()) delete_upload((string)$p);
        $pdo->prepare('DELETE FROM testimonials WHERE id=?')->execute([$id]);
        flash('Testimonial deleted.'); redirect('testimonials.php');
    }
    // create / update
    $id = post_int('id');
    $name=post('name'); $location=post('location'); $quote=post('quote');
    $rating=max(1,min(5,post_int('rating',5))); $sort=post_int('sort'); $status=post('status');
    if ($name==='' || $quote==='') { flash('Name and quote are required.','error'); redirect('testimonials.php'); }
    if ($id) {
        $photo = save_cover('photo','testimonials', (function() use ($pdo,$id){ $s=$pdo->prepare('SELECT photo FROM testimonials WHERE id=?'); $s->execute([$id]); return $s->fetchColumn() ?: null; })());
        $pdo->prepare('UPDATE testimonials SET name=?,location=?,quote=?,rating=?,photo=?,sort=?,status=? WHERE id=?')
            ->execute([$name,$location,$quote,$rating,$photo,$sort,$status,$id]);
    } else {
        $photo = save_cover('photo','testimonials',null);
        $pdo->prepare('INSERT INTO testimonials (name,location,quote,rating,photo,sort,status) VALUES (?,?,?,?,?,?,?)')
            ->execute([$name,$location,$quote,$rating,$photo,$sort,$status]);
    }
    flash('Testimonial saved.'); redirect('testimonials.php');
}

$edit = null;
if ($eid = (int)get('edit')) { $s=$pdo->prepare('SELECT * FROM testimonials WHERE id=?'); $s->execute([$eid]); $edit=$s->fetch() ?: null; }
$rows = $pdo->query('SELECT * FROM testimonials ORDER BY sort, id')->fetchAll();

$title='Testimonials'; $active='testimonials';
require __DIR__ . '/_head.php';
?>
<div class="page-head"><h2>Testimonials</h2><p class="sub"><?= count($rows) ?> total</p></div>
<div class="split-2">
  <div class="card">
    <div class="card-head"><h3>All testimonials</h3></div>
    <?php if ($rows): ?>
    <div class="table-wrap"><table class="data">
      <thead><tr><th>Name</th><th>Quote</th><th>Rating</th><th>Status</th><th class="right">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><div class="t-title"><?= e($r['name']) ?></div><div class="t-sub"><?= e($r['location']) ?></div></td>
          <td class="mini muted"><?= e(excerpt($r['quote'],14)) ?></td>
          <td class="nowrap" style="color:var(--warn)"><?= str_repeat('★',(int)$r['rating']) . str_repeat('☆',5-(int)$r['rating']) ?></td>
          <td><span class="badge <?= e($r['status']) ?>"><?= e(ucfirst($r['status'])) ?></span></td>
          <td><div class="row-actions">
            <a class="btn btn-light btn-sm" href="?edit=<?= (int)$r['id'] ?>">Edit</a>
            <form method="post" style="display:inline"><?= csrf_field() ?>
              <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <button class="btn btn-ghost btn-sm" data-confirm="Delete this testimonial?">Delete</button>
            </form>
          </div></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
    <?php else: ?><div class="empty"><p>No testimonials yet.</p></div><?php endif; ?>
  </div>

  <div class="card card-pad">
    <h3 style="font-size:14px;margin-bottom:14px"><?= $edit?'Edit testimonial':'Add testimonial' ?></h3>
    <form method="post" enctype="multipart/form-data" class="stack"><?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
      <div class="field"><label>Name *</label><input type="text" name="name" value="<?= e($edit['name'] ?? '') ?>" required></div>
      <div class="field"><label>Location</label><input type="text" name="location" value="<?= e($edit['location'] ?? '') ?>" placeholder="Nairobi"></div>
      <div class="field"><label>Quote *</label><textarea name="quote" required><?= e($edit['quote'] ?? '') ?></textarea></div>
      <div class="field"><label>Rating</label>
        <?= status_select((string)($edit['rating'] ?? 5), ['5'=>'★★★★★','4'=>'★★★★','3'=>'★★★','2'=>'★★','1'=>'★'], 'rating') ?>
      </div>
      <div class="field"><label>Photo (optional)</label><input type="file" name="photo" accept="image/*"></div>
      <div class="field"><label>Display order</label><input type="number" name="sort" value="<?= (int)($edit['sort'] ?? 0) ?>"></div>
      <div class="field"><label>Status</label><?= status_select($edit['status'] ?? 'published', ['published'=>'Published','draft'=>'Draft'],'status') ?></div>
      <div class="form-actions"><button class="btn btn-primary" type="submit"><?= $edit?'Update':'Add' ?></button><?php if($edit):?><a class="btn btn-ghost" href="testimonials.php">Cancel</a><?php endif;?></div>
    </form>
  </div>
</div>
<?php require __DIR__ . '/_foot.php'; ?>
