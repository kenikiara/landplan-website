<?php
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/_crud.php';
$pdo = db();

if (is_post()) {
    csrf_check();
    if (post('action') === 'delete') {
        $pdo->prepare('DELETE FROM faqs WHERE id=?')->execute([post_int('id')]);
        flash('FAQ deleted.'); redirect('faqs.php');
    }
    $id=post_int('id'); $question=post('question'); $answer=post('answer');
    $category=post('category') ?: 'General'; $sort=post_int('sort'); $status=post('status');
    if ($question==='' || $answer==='') { flash('Question and answer are required.','error'); redirect('faqs.php'); }
    if ($id) {
        $pdo->prepare('UPDATE faqs SET question=?,answer=?,category=?,sort=?,status=? WHERE id=?')
            ->execute([$question,$answer,$category,$sort,$status,$id]);
    } else {
        $pdo->prepare('INSERT INTO faqs (question,answer,category,sort,status) VALUES (?,?,?,?,?)')
            ->execute([$question,$answer,$category,$sort,$status]);
    }
    flash('FAQ saved.'); redirect('faqs.php');
}

$edit=null;
if ($eid=(int)get('edit')) { $s=$pdo->prepare('SELECT * FROM faqs WHERE id=?'); $s->execute([$eid]); $edit=$s->fetch() ?: null; }
$rows = $pdo->query('SELECT * FROM faqs ORDER BY category, sort, id')->fetchAll();

$title='FAQs'; $active='faqs';
require __DIR__ . '/_head.php';
?>
<div class="page-head"><h2>Frequently Asked Questions</h2><p class="sub"><?= count($rows) ?> total</p></div>
<div class="split-2">
  <div class="card">
    <div class="card-head"><h3>All FAQs</h3></div>
    <?php if ($rows): ?>
    <div class="table-wrap"><table class="data">
      <thead><tr><th>Question</th><th>Category</th><th>Status</th><th class="right">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td class="t-title"><?= e($r['question']) ?></td>
          <td class="mini muted"><?= e($r['category']) ?></td>
          <td><span class="badge <?= e($r['status']) ?>"><?= e(ucfirst($r['status'])) ?></span></td>
          <td><div class="row-actions">
            <a class="btn btn-light btn-sm" href="?edit=<?= (int)$r['id'] ?>">Edit</a>
            <form method="post" style="display:inline"><?= csrf_field() ?>
              <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <button class="btn btn-ghost btn-sm" data-confirm="Delete this FAQ?">Delete</button>
            </form>
          </div></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
    <?php else: ?><div class="empty"><p>No FAQs yet.</p></div><?php endif; ?>
  </div>

  <div class="card card-pad">
    <h3 style="font-size:14px;margin-bottom:14px"><?= $edit?'Edit FAQ':'Add FAQ' ?></h3>
    <form method="post" class="stack"><?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
      <div class="field"><label>Question *</label><input type="text" name="question" value="<?= e($edit['question'] ?? '') ?>" required></div>
      <div class="field"><label>Answer *</label><textarea name="answer" required><?= e($edit['answer'] ?? '') ?></textarea></div>
      <div class="field"><label>Category</label><input type="text" name="category" value="<?= e($edit['category'] ?? 'General') ?>"></div>
      <div class="field"><label>Display order</label><input type="number" name="sort" value="<?= (int)($edit['sort'] ?? 0) ?>"></div>
      <div class="field"><label>Status</label><?= status_select($edit['status'] ?? 'published',['published'=>'Published','draft'=>'Draft'],'status') ?></div>
      <div class="form-actions"><button class="btn btn-primary" type="submit"><?= $edit?'Update':'Add' ?></button><?php if($edit):?><a class="btn btn-ghost" href="faqs.php">Cancel</a><?php endif;?></div>
    </form>
  </div>
</div>
<?php require __DIR__ . '/_foot.php'; ?>
