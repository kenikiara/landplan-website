<?php
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/_crud.php';
$pdo = db();

$id = (int)get('id');
$row = ['id'=>0,'title'=>'','slug'=>'','body'=>'','meta_title'=>'','meta_description'=>'','status'=>'published'];
if ($id) { $stmt=$pdo->prepare('SELECT * FROM pages WHERE id=?'); $stmt->execute([$id]); $row=$stmt->fetch(); if(!$row){http_response_code(404);exit('Page not found.');} }

if (is_post()) {
    csrf_check();
    $data=['title'=>post('title'),'body'=>post('body'),'meta_title'=>post('meta_title'),
           'meta_description'=>post('meta_description'),'status'=>post('status')];
    $errors=[]; if ($data['title']==='') $errors[]='Title is required.';
    if (!$errors) {
        $slug = unique_slug(slugify(post('slug')!==''?post('slug'):$data['title']), 'pages', $id);
        if ($id) {
            $pdo->prepare('UPDATE pages SET title=?,slug=?,body=?,meta_title=?,meta_description=?,status=? WHERE id=?')
                ->execute([$data['title'],$slug,$data['body'],$data['meta_title'],$data['meta_description'],$data['status'],$id]);
            activity_log('update','page',$id,$data['title']);
        } else {
            $pdo->prepare('INSERT INTO pages (title,slug,body,meta_title,meta_description,status) VALUES (?,?,?,?,?,?)')
                ->execute([$data['title'],$slug,$data['body'],$data['meta_title'],$data['meta_description'],$data['status']]);
            $id=(int)$pdo->lastInsertId(); activity_log('create','page',$id,$data['title']);
        }
        flash('Page saved.'); redirect('pages.php');
    }
    $row=array_merge($row,$data,['id'=>$id,'slug'=>post('slug')]);
}

$title=$id?'Edit Page':'Add Page'; $active='pages';
require __DIR__ . '/_head.php';
?>
<div class="crumbs"><a href="pages.php">Pages</a> / <?= $id?'Edit':'Add new' ?></div>
<div class="page-head"><h2><?= $id?'Edit page':'Add a page' ?></h2></div>
<?php if (!empty($errors)): ?><div class="flash error"><?= e(implode(' ',$errors)) ?></div><?php endif; ?>
<form method="post"><?= csrf_field() ?>
  <div class="split-2">
    <div class="card card-pad"><div class="form-grid">
      <div class="field full"><label>Title *</label><input type="text" name="title" value="<?= e($row['title']) ?>" data-slug-source required></div>
      <div class="field full"><label>URL slug</label><input type="text" name="slug" value="<?= e($row['slug']) ?>" data-slug-target placeholder="auto"><span class="hint">Reachable at <code>/page.php?slug=…</code></span></div>
      <div class="field full"><label>Body</label><textarea name="body" class="tall" style="min-height:340px"><?= e($row['body']) ?></textarea><span class="hint">Basic HTML allowed.</span></div>
    </div></div>
    <div class="stack">
      <div class="card card-pad"><h3 style="font-size:14px;margin-bottom:12px">Publish</h3>
        <div class="field"><label>Status</label><?= status_select($row['status'],['published'=>'Published','draft'=>'Draft'],'status') ?></div>
        <div class="form-actions"><button class="btn btn-primary" type="submit">Save page</button><a class="btn btn-ghost" href="pages.php">Cancel</a></div>
      </div>
      <div class="card card-pad"><h3 style="font-size:14px;margin-bottom:12px">SEO</h3>
        <div class="field"><label>Meta title</label><input type="text" name="meta_title" value="<?= e($row['meta_title']) ?>"></div>
        <div class="field" style="margin-top:12px"><label>Meta description</label><textarea name="meta_description"><?= e($row['meta_description']) ?></textarea></div>
      </div>
    </div>
  </div>
</form>
<?php require __DIR__ . '/_foot.php'; ?>
