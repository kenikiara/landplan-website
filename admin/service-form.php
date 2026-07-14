<?php
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/_crud.php';
$pdo = db();

$id = (int)get('id');
$row = ['id'=>0,'title'=>'','slug'=>'','icon'=>'','excerpt'=>'','body'=>'','cover_image'=>null,'sort'=>0,'status'=>'published'];
if ($id) { $stmt=$pdo->prepare('SELECT * FROM services WHERE id=?'); $stmt->execute([$id]); $row=$stmt->fetch(); if(!$row){http_response_code(404);exit('Service not found.');} }

if (is_post()) {
    csrf_check();
    $data=['title'=>post('title'),'icon'=>post('icon'),'excerpt'=>post('excerpt'),'body'=>post('body'),
           'sort'=>post_int('sort'),'status'=>post('status')];
    $errors=[]; if ($data['title']==='') $errors[]='Title is required.';
    if (!$errors) {
        $slug = unique_slug(slugify(post('slug')!==''?post('slug'):$data['title']), 'services', $id);
        if ($id) {
            $data['cover_image']=save_cover('cover_image','services',$row['cover_image']);
            $pdo->prepare('UPDATE services SET title=?,slug=?,icon=?,excerpt=?,body=?,cover_image=?,sort=?,status=? WHERE id=?')
                ->execute([$data['title'],$slug,$data['icon'],$data['excerpt'],$data['body'],$data['cover_image'],$data['sort'],$data['status'],$id]);
            activity_log('update','service',$id,$data['title']);
        } else {
            $cover=save_cover('cover_image','services',null);
            $pdo->prepare('INSERT INTO services (title,slug,icon,excerpt,body,cover_image,sort,status) VALUES (?,?,?,?,?,?,?,?)')
                ->execute([$data['title'],$slug,$data['icon'],$data['excerpt'],$data['body'],$cover,$data['sort'],$data['status']]);
            $id=(int)$pdo->lastInsertId(); activity_log('create','service',$id,$data['title']);
        }
        flash('Service saved.'); redirect('services.php');
    }
    $row=array_merge($row,$data,['id'=>$id,'slug'=>post('slug')]);
}

$title=$id?'Edit Service':'Add Service'; $active='services';
require __DIR__ . '/_head.php';
?>
<div class="crumbs"><a href="services.php">Services</a> / <?= $id?'Edit':'Add new' ?></div>
<div class="page-head"><h2><?= $id?'Edit service':'Add a service' ?></h2></div>
<?php if (!empty($errors)): ?><div class="flash error"><?= e(implode(' ',$errors)) ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data"><?= csrf_field() ?>
  <div class="split-2">
    <div class="stack">
      <div class="card card-pad"><div class="form-grid">
        <div class="field full"><label>Title *</label><input type="text" name="title" value="<?= e($row['title']) ?>" data-slug-source required></div>
        <div class="field"><label>URL slug</label><input type="text" name="slug" value="<?= e($row['slug']) ?>" data-slug-target placeholder="auto"></div>
        <div class="field"><label>Icon key</label><input type="text" name="icon" value="<?= e($row['icon']) ?>" placeholder="land / arch / house"></div>
        <div class="field full"><label>Short excerpt</label><textarea name="excerpt" placeholder="One sentence for the card"><?= e($row['excerpt']) ?></textarea></div>
        <div class="field full"><label>Full description</label><textarea name="body" class="tall"><?= e($row['body']) ?></textarea></div>
      </div></div>
    </div>
    <div class="stack">
      <div class="card card-pad"><h3 style="font-size:14px;margin-bottom:12px">Options</h3>
        <div class="field"><label>Status</label><?= status_select($row['status'],['published'=>'Published','draft'=>'Draft'],'status') ?></div>
        <div class="field" style="margin-top:12px"><label>Display order</label><input type="number" name="sort" value="<?= (int)$row['sort'] ?>"></div>
        <div class="form-actions"><button class="btn btn-primary" type="submit">Save</button><a class="btn btn-ghost" href="services.php">Cancel</a></div>
      </div>
      <div class="card card-pad"><h3 style="font-size:14px;margin-bottom:12px">Image</h3><?= cover_field($row['cover_image']) ?></div>
    </div>
  </div>
</form>
<?php require __DIR__ . '/_foot.php'; ?>
