<?php
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/_crud.php';
$pdo = db();

$id = (int)get('id');
$row = ['id'=>0,'title'=>'','slug'=>'','excerpt'=>'','body'=>'','cover_image'=>null,'category'=>'','author_id'=>$ADMIN['id'],
        'status'=>'draft','meta_title'=>'','meta_description'=>'','published_at'=>null];
if ($id) { $stmt=$pdo->prepare('SELECT * FROM articles WHERE id=?'); $stmt->execute([$id]); $row=$stmt->fetch(); if(!$row){http_response_code(404);exit('Article not found.');} }

if (is_post()) {
    csrf_check();
    $data = ['title'=>post('title'),'excerpt'=>post('excerpt'),'body'=>post('body'),'category'=>post('category'),
             'status'=>post('status'),'meta_title'=>post('meta_title'),'meta_description'=>post('meta_description')];
    $errors=[]; if ($data['title']==='') $errors[]='Title is required.';
    if (!$errors) {
        $slug = unique_slug(slugify(post('slug')!==''?post('slug'):$data['title']), 'articles', $id);
        // publish date: set when publishing for the first time
        $publishedAt = $row['published_at'];
        if ($data['status']==='published' && !$publishedAt) $publishedAt = date('Y-m-d H:i:s');
        if (post('published_at') !== '') $publishedAt = date('Y-m-d H:i:s', strtotime(post('published_at')));

        if ($id) {
            $data['cover_image']=save_cover('cover_image','articles',$row['cover_image']);
            $pdo->prepare('UPDATE articles SET title=?,slug=?,excerpt=?,body=?,cover_image=?,category=?,status=?,meta_title=?,meta_description=?,published_at=? WHERE id=?')
                ->execute([$data['title'],$slug,$data['excerpt'],$data['body'],$data['cover_image'],$data['category'],$data['status'],$data['meta_title'],$data['meta_description'],$publishedAt,$id]);
            activity_log('update','article',$id,$data['title']);
        } else {
            $cover=save_cover('cover_image','articles',null);
            $pdo->prepare('INSERT INTO articles (title,slug,excerpt,body,cover_image,category,author_id,status,meta_title,meta_description,published_at) VALUES (?,?,?,?,?,?,?,?,?,?,?)')
                ->execute([$data['title'],$slug,$data['excerpt'],$data['body'],$cover,$data['category'],$ADMIN['id'],$data['status'],$data['meta_title'],$data['meta_description'],$publishedAt]);
            $id=(int)$pdo->lastInsertId(); activity_log('create','article',$id,$data['title']);
        }
        flash('Article saved.'); redirect('articles.php');
    }
    $row=array_merge($row,$data,['id'=>$id,'slug'=>post('slug')]);
}

$pubLocal = $row['published_at'] ? date('Y-m-d\TH:i', strtotime((string)$row['published_at'])) : '';
$title = $id?'Edit Article':'New Article'; $active='articles';
require __DIR__ . '/_head.php';
?>
<div class="crumbs"><a href="articles.php">Blog Articles</a> / <?= $id?'Edit':'New' ?></div>
<div class="page-head"><h2><?= $id?'Edit article':'Write an article' ?></h2></div>
<?php if (!empty($errors)): ?><div class="flash error"><?= e(implode(' ',$errors)) ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data"><?= csrf_field() ?>
  <div class="split-2">
    <div class="stack">
      <div class="card card-pad"><div class="form-grid">
        <div class="field full"><label>Title *</label><input type="text" name="title" value="<?= e($row['title']) ?>" data-slug-source required></div>
        <div class="field"><label>URL slug</label><input type="text" name="slug" value="<?= e($row['slug']) ?>" data-slug-target placeholder="auto"></div>
        <div class="field"><label>Category</label><input type="text" name="category" value="<?= e($row['category']) ?>" placeholder="Land Buying"></div>
        <div class="field full"><label>Excerpt</label><textarea name="excerpt" placeholder="Short summary shown on the blog listing"><?= e($row['excerpt']) ?></textarea></div>
        <div class="field full"><label>Body</label><textarea name="body" class="tall" style="min-height:360px"><?= e($row['body']) ?></textarea><span class="hint">Basic HTML is allowed: &lt;p&gt; &lt;h2&gt; &lt;ul&gt; &lt;li&gt; &lt;strong&gt; &lt;a&gt;. Leave a blank line between paragraphs.</span></div>
      </div></div>
      <div class="card card-pad"><h3 style="font-size:14px;margin-bottom:12px">SEO (optional)</h3>
        <div class="form-grid">
          <div class="field full"><label>Meta title</label><input type="text" name="meta_title" value="<?= e($row['meta_title']) ?>"></div>
          <div class="field full"><label>Meta description</label><textarea name="meta_description"><?= e($row['meta_description']) ?></textarea></div>
        </div></div>
    </div>
    <div class="stack">
      <div class="card card-pad"><h3 style="font-size:14px;margin-bottom:12px">Publish</h3>
        <div class="field"><label>Status</label><?= status_select($row['status'],['draft'=>'Draft','published'=>'Published'],'status') ?></div>
        <div class="field" style="margin-top:12px"><label>Publish date</label><input type="datetime-local" name="published_at" value="<?= e($pubLocal) ?>"><span class="hint">Leave blank to use now when publishing.</span></div>
        <div class="form-actions"><button class="btn btn-primary" type="submit">Save article</button><a class="btn btn-ghost" href="articles.php">Cancel</a></div>
      </div>
      <div class="card card-pad"><h3 style="font-size:14px;margin-bottom:12px">Cover image</h3><?= cover_field($row['cover_image']) ?></div>
      <?php if ($id && $row['slug']): ?>
      <div class="card card-pad mini"><span class="muted">Public link</span><br>
        <a href="../article.php?slug=<?= e($row['slug']) ?>" target="_blank" style="color:var(--green);font-weight:600">View on site ↗</a></div>
      <?php endif; ?>
    </div>
  </div>
</form>
<?php require __DIR__ . '/_foot.php'; ?>
