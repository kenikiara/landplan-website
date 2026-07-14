<?php
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/_crud.php';
$pdo = db();

if (is_post() && post('action') === 'delete') {
    csrf_check();
    $id = post_int('id');
    delete_entity('articles', $id);
    activity_log('delete','article',$id);
    flash('Article deleted.'); redirect('articles.php');
}

$q = get('q'); $fstatus = get('status');
$page = max(1,(int)get('page','1')); $per = 15;
$where=[]; $args=[];
if ($q!=='')       { $where[]='(title LIKE ? OR category LIKE ?)'; $like="%$q%"; array_push($args,$like,$like); }
if ($fstatus!=='') { $where[]='status=?'; $args[]=$fstatus; }
$wsql=$where?('WHERE '.implode(' AND ',$where)):'';
$total=(int)(function() use ($pdo,$wsql,$args){ $s=$pdo->prepare("SELECT COUNT(*) FROM articles $wsql"); $s->execute($args); return $s->fetchColumn(); })();
$pg=paginate($total,$per,$page);
$stmt=$pdo->prepare("SELECT a.*, u.name AS author FROM articles a LEFT JOIN users u ON u.id=a.author_id $wsql ORDER BY COALESCE(a.published_at,a.created_at) DESC LIMIT {$pg['perPage']} OFFSET {$pg['offset']}");
$stmt->execute($args); $rows=$stmt->fetchAll();

$title='Blog Articles'; $active='articles';
require __DIR__ . '/_head.php';
?>
<div class="page-head">
  <div><h2>Blog Articles</h2><p class="sub"><?= $total ?> article<?= $total===1?'':'s' ?></p></div>
  <div class="spacer"></div>
  <a class="btn btn-primary" href="article-form.php"><svg viewBox="0 0 24 24"><path d="M11 5h2v6h6v2h-6v6h-2v-6H5v-2h6V5z"/></svg> New Article</a>
</div>
<form class="toolbar" method="get">
  <div class="search"><input type="search" name="q" value="<?= e($q) ?>" placeholder="Search title or category…"></div>
  <select name="status" data-autosubmit>
    <option value="">All statuses</option>
    <?php foreach (['published'=>'Published','draft'=>'Draft'] as $v=>$l): ?>
      <option value="<?= $v ?>" <?= $fstatus===$v?'selected':'' ?>><?= $l ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn btn-light btn-sm">Filter</button>
</form>
<div class="card">
  <?php if ($rows): ?>
  <div class="table-wrap"><table class="data">
    <thead><tr><th></th><th>Title</th><th>Category</th><th>Author</th><th>Status</th><th class="right">Date</th><th class="right">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><img class="t-thumb" src="<?= $r['cover_image']?e('../'.ltrim($r['cover_image'],'/')):'' ?>" alt=""></td>
        <td><div class="t-title"><?= e($r['title']) ?></div><div class="t-sub">/<?= e($r['slug']) ?></div></td>
        <td><?= e($r['category'] ?: '-') ?></td>
        <td class="mini muted"><?= e($r['author'] ?: '-') ?></td>
        <td><span class="badge <?= e($r['status']) ?>"><?= e(ucfirst($r['status'])) ?></span></td>
        <td class="right nowrap mini muted"><?= e(nice_date($r['published_at'] ?: $r['created_at'])) ?></td>
        <td><div class="row-actions">
          <a class="btn btn-light btn-sm" href="article-form.php?id=<?= (int)$r['id'] ?>">Edit</a>
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
    <div class="empty"><svg viewBox="0 0 24 24"><path d="M4 4h16v2H4V4zm0 5h16v2H4V9zm0 5h10v2H4v-2z"/></svg>
      <p>No articles<?= $q||$fstatus?' match your filter':' yet' ?>.</p>
      <a class="btn btn-primary btn-sm" href="article-form.php" style="margin-top:12px">Write your first article</a></div>
  <?php endif; ?>
</div>
<?= pagination_html($pg,'articles.php?q='.urlencode($q).'&status='.urlencode($fstatus)) ?>
<?php require __DIR__ . '/_foot.php'; ?>
