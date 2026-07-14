<?php
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/_crud.php';
$pdo = db();

if (is_post() && post('action') === 'delete') {
    csrf_check();
    $id = post_int('id');
    delete_entity('projects', $id, 'project_images', 'project_id');
    activity_log('delete','project',$id);
    flash('Project deleted.'); redirect('projects.php');
}

$q = get('q'); $fstatus = get('status');
$page = max(1,(int)get('page','1')); $per = 12;
$where=[]; $args=[];
if ($q!=='')       { $where[]='(title LIKE ? OR location LIKE ?)'; $like="%$q%"; array_push($args,$like,$like); }
if ($fstatus!=='') { $where[]='status=?'; $args[]=$fstatus; }
$wsql=$where?('WHERE '.implode(' AND ',$where)):'';
$total=(int)(function() use ($pdo,$wsql,$args){ $s=$pdo->prepare("SELECT COUNT(*) FROM projects $wsql"); $s->execute($args); return $s->fetchColumn(); })();
$pg=paginate($total,$per,$page);
$stmt=$pdo->prepare("SELECT * FROM projects $wsql ORDER BY featured DESC, created_at DESC LIMIT {$pg['perPage']} OFFSET {$pg['offset']}");
$stmt->execute($args); $rows=$stmt->fetchAll();

$title='Projects'; $active='projects';
require __DIR__ . '/_head.php';
?>
<div class="page-head">
  <div><h2>Projects</h2><p class="sub"><?= $total ?> project<?= $total===1?'':'s' ?></p></div>
  <div class="spacer"></div>
  <a class="btn btn-primary" href="project-form.php"><svg viewBox="0 0 24 24"><path d="M11 5h2v6h6v2h-6v6h-2v-6H5v-2h6V5z"/></svg> Add Project</a>
</div>
<form class="toolbar" method="get">
  <div class="search"><input type="search" name="q" value="<?= e($q) ?>" placeholder="Search…"></div>
  <select name="status" data-autosubmit>
    <option value="">All statuses</option>
    <?php foreach (['ongoing'=>'Ongoing','completed'=>'Completed'] as $v=>$l): ?>
      <option value="<?= $v ?>" <?= $fstatus===$v?'selected':'' ?>><?= $l ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn btn-light btn-sm">Filter</button>
</form>
<div class="card">
  <?php if ($rows): ?>
  <div class="table-wrap"><table class="data">
    <thead><tr><th></th><th>Title</th><th>Location</th><th>Status</th><th class="right">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><img class="t-thumb" src="<?= $r['cover_image']?e('../'.ltrim($r['cover_image'],'/')):'' ?>" alt=""></td>
        <td><div class="t-title"><?= e($r['title']) ?> <?= $r['featured']?'<span class="badge featured">★</span>':'' ?></div><div class="t-sub">/<?= e($r['slug']) ?></div></td>
        <td><?= e($r['location']) ?></td>
        <td><span class="badge <?= e($r['status']) ?>"><?= e(ucfirst($r['status'])) ?></span></td>
        <td><div class="row-actions">
          <a class="btn btn-light btn-sm" href="project-form.php?id=<?= (int)$r['id'] ?>">Edit</a>
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
    <div class="empty"><svg viewBox="0 0 24 24"><path d="M12 7V3H2v18h20V7H12z"/></svg>
      <p>No projects<?= $q||$fstatus?' match your filter':' yet' ?>.</p>
      <a class="btn btn-primary btn-sm" href="project-form.php" style="margin-top:12px">Add your first project</a></div>
  <?php endif; ?>
</div>
<?= pagination_html($pg,'projects.php?q='.urlencode($q).'&status='.urlencode($fstatus)) ?>
<?php require __DIR__ . '/_foot.php'; ?>
