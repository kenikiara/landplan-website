<?php
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/_crud.php';
$pdo = db();

$id = (int)get('id');
$row = ['id'=>0,'title'=>'','slug'=>'','location'=>'','description'=>'','cover_image'=>null,'status'=>'completed','featured'=>0,'meta_title'=>'','meta_description'=>''];
if ($id) { $stmt=$pdo->prepare('SELECT * FROM projects WHERE id=?'); $stmt->execute([$id]); $row=$stmt->fetch(); if(!$row){http_response_code(404);exit('Project not found.');} }

if (is_post()) {
    csrf_check();
    $data = ['title'=>post('title'),'location'=>post('location'),'description'=>post('description'),
             'status'=>post('status'),'featured'=>isset($_POST['featured'])?1:0,
             'meta_title'=>post('meta_title'),'meta_description'=>post('meta_description')];
    $errors=[]; if ($data['title']==='') $errors[]='Title is required.';
    if (!$errors) {
        $slug = unique_slug(slugify(post('slug')!==''?post('slug'):$data['title']), 'projects', $id);
        if ($id) {
            $data['cover_image']=save_cover('cover_image','projects',$row['cover_image']);
            $pdo->prepare('UPDATE projects SET title=?,slug=?,location=?,description=?,cover_image=?,status=?,featured=?,meta_title=?,meta_description=? WHERE id=?')
                ->execute([$data['title'],$slug,$data['location'],$data['description'],$data['cover_image'],$data['status'],$data['featured'],$data['meta_title'],$data['meta_description'],$id]);
            activity_log('update','project',$id,$data['title']);
        } else {
            $cover=save_cover('cover_image','projects',null);
            $pdo->prepare('INSERT INTO projects (title,slug,location,description,cover_image,status,featured,meta_title,meta_description) VALUES (?,?,?,?,?,?,?,?,?)')
                ->execute([$data['title'],$slug,$data['location'],$data['description'],$cover,$data['status'],$data['featured'],$data['meta_title'],$data['meta_description']]);
            $id=(int)$pdo->lastInsertId(); activity_log('create','project',$id,$data['title']);
        }
        if (!empty($_POST['remove_images'])) remove_gallery('project_images','project_id',$id,(array)$_POST['remove_images']);
        add_gallery('project_images','project_id',$id,'gallery','projects');
        flash('Project saved.'); redirect('projects.php');
    }
    $row=array_merge($row,$data,['id'=>$id,'slug'=>post('slug')]);
}

$galleryRows = $id ? gallery_rows('project_images','project_id',$id) : [];
$title = $id?'Edit Project':'Add Project'; $active='projects';
require __DIR__ . '/_head.php';
?>
<div class="crumbs"><a href="projects.php">Projects</a> / <?= $id?'Edit':'Add new' ?></div>
<div class="page-head"><h2><?= $id?'Edit project':'Add a project' ?></h2></div>
<?php if (!empty($errors)): ?><div class="flash error"><?= e(implode(' ',$errors)) ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data"><?= csrf_field() ?>
  <div class="split-2">
    <div class="stack">
      <div class="card card-pad"><div class="form-grid">
        <div class="field full"><label>Title *</label><input type="text" name="title" value="<?= e($row['title']) ?>" data-slug-source required></div>
        <div class="field"><label>URL slug</label><input type="text" name="slug" value="<?= e($row['slug']) ?>" data-slug-target placeholder="auto"></div>
        <div class="field"><label>Location</label><input type="text" name="location" value="<?= e($row['location']) ?>"></div>
        <div class="field full"><label>Description</label><textarea name="description" class="tall"><?= e($row['description']) ?></textarea></div>
      </div></div>
      <div class="card card-pad"><h3 style="font-size:14px;margin-bottom:12px">Photos</h3>
        <div class="form-grid"><?= cover_field($row['cover_image']) ?><?= gallery_editor($galleryRows) ?></div></div>
      <div class="card card-pad"><h3 style="font-size:14px;margin-bottom:12px">SEO (optional)</h3>
        <div class="form-grid">
          <div class="field full"><label>Meta title</label><input type="text" name="meta_title" value="<?= e($row['meta_title']) ?>"></div>
          <div class="field full"><label>Meta description</label><textarea name="meta_description"><?= e($row['meta_description']) ?></textarea></div>
        </div></div>
    </div>
    <div class="stack">
      <div class="card card-pad"><h3 style="font-size:14px;margin-bottom:12px">Publish</h3>
        <div class="field"><label>Status</label><?= status_select($row['status'],['completed'=>'Completed','ongoing'=>'Ongoing'],'status') ?></div>
        <label class="check" style="margin-top:14px"><input type="checkbox" name="featured" <?= $row['featured']?'checked':'' ?>> Feature on homepage</label>
        <div class="form-actions"><button class="btn btn-primary" type="submit">Save project</button><a class="btn btn-ghost" href="projects.php">Cancel</a></div>
      </div>
      <?php if ($id && $row['slug']): ?>
      <div class="card card-pad mini"><span class="muted">Public link</span><br>
        <a href="../project-detail.php?slug=<?= e($row['slug']) ?>" target="_blank" style="color:var(--green);font-weight:600">View on site ↗</a></div>
      <?php endif; ?>
      <?= $id ? enquiries_panel('project', $id) : '' ?>
    </div>
  </div>
</form>
<?php require __DIR__ . '/_foot.php'; ?>
