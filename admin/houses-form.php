<?php
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/_crud.php';
$pdo = db();

$id  = (int)get('id');
$row = ['id'=>0,'title'=>'','slug'=>'','location'=>'','bedrooms'=>'','bathrooms'=>'','size'=>'','price'=>'',
        'description'=>'','features'=>'','cover_image'=>null,'featured'=>0,'status'=>'published','meta_title'=>'','meta_description'=>''];
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM houses WHERE id=?'); $stmt->execute([$id]);
    $row = $stmt->fetch(); if (!$row) { http_response_code(404); exit('House not found.'); }
}

if (is_post()) {
    csrf_check();
    $data = [
        'title'=>post('title'),'location'=>post('location'),
        'bedrooms'=>post('bedrooms')===''?null:(int)post('bedrooms'),
        'bathrooms'=>post('bathrooms')===''?null:(int)post('bathrooms'),
        'size'=>post('size'),
        'price'=>post('price')===''?null:(float)preg_replace('/[^0-9.]/','',post('price')),
        'description'=>post('description'),'features'=>post('features'),
        'featured'=>isset($_POST['featured'])?1:0,'status'=>post('status'),
        'meta_title'=>post('meta_title'),'meta_description'=>post('meta_description'),
    ];
    $errors = [];
    if ($data['title']==='')    $errors[]='Title is required.';
    if ($data['location']==='') $errors[]='Location is required.';
    if (!$errors) {
        $slug = unique_slug(slugify(post('slug')!==''?post('slug'):$data['title'].'-'.$data['location']), 'houses', $id);
        if ($id) {
            $data['cover_image'] = save_cover('cover_image','houses',$row['cover_image']);
            $pdo->prepare('UPDATE houses SET title=?,slug=?,location=?,bedrooms=?,bathrooms=?,size=?,price=?,description=?,features=?,cover_image=?,featured=?,status=?,meta_title=?,meta_description=? WHERE id=?')
                ->execute([$data['title'],$slug,$data['location'],$data['bedrooms'],$data['bathrooms'],$data['size'],$data['price'],$data['description'],$data['features'],$data['cover_image'],$data['featured'],$data['status'],$data['meta_title'],$data['meta_description'],$id]);
            activity_log('update','house',$id,$data['title']);
        } else {
            $cover = save_cover('cover_image','houses',null);
            $pdo->prepare('INSERT INTO houses (title,slug,location,bedrooms,bathrooms,size,price,description,features,cover_image,featured,status,meta_title,meta_description) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)')
                ->execute([$data['title'],$slug,$data['location'],$data['bedrooms'],$data['bathrooms'],$data['size'],$data['price'],$data['description'],$data['features'],$cover,$data['featured'],$data['status'],$data['meta_title'],$data['meta_description']]);
            $id=(int)$pdo->lastInsertId(); activity_log('create','house',$id,$data['title']);
        }
        if (!empty($_POST['remove_images'])) remove_gallery('house_images','house_id',$id,(array)$_POST['remove_images']);
        add_gallery('house_images','house_id',$id,'gallery','houses');
        flash('House saved.'); redirect('houses.php');
    }
    $row = array_merge($row,$data,['id'=>$id,'slug'=>post('slug')]);
}

$galleryRows = $id ? gallery_rows('house_images','house_id',$id) : [];
$title = $id ? 'Edit House' : 'Add House'; $active='houses';
require __DIR__ . '/_head.php';
?>
<div class="crumbs"><a href="houses.php">Houses</a> / <?= $id?'Edit':'Add new' ?></div>
<div class="page-head"><h2><?= $id?'Edit house':'Add a house' ?></h2></div>
<?php if (!empty($errors)): ?><div class="flash error"><?= e(implode(' ',$errors)) ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data"><?= csrf_field() ?>
  <div class="split-2">
    <div class="stack">
      <div class="card card-pad"><div class="form-grid">
        <div class="field full"><label>Title *</label><input type="text" name="title" value="<?= e($row['title']) ?>" data-slug-source required></div>
        <div class="field"><label>URL slug</label><input type="text" name="slug" value="<?= e($row['slug']) ?>" data-slug-target placeholder="auto from title"></div>
        <div class="field"><label>Location *</label><input type="text" name="location" value="<?= e($row['location']) ?>" placeholder="Runda, Nairobi" required></div>
        <div class="field"><label>Bedrooms</label><input type="number" name="bedrooms" value="<?= e((string)$row['bedrooms']) ?>" min="0"></div>
        <div class="field"><label>Bathrooms</label><input type="number" name="bathrooms" value="<?= e((string)$row['bathrooms']) ?>" min="0"></div>
        <div class="field"><label>Size</label><input type="text" name="size" value="<?= e($row['size']) ?>" placeholder="e.g. 1/8 acre"></div>
        <div class="field"><label>Price (KSh)</label><input type="text" name="price" value="<?= $row['price']!==''&&$row['price']!==null?e((string)(int)$row['price']):'' ?>" placeholder="8900000"></div>
        <div class="field full"><label>Description</label><textarea name="description" class="tall"><?= e($row['description']) ?></textarea></div>
        <div class="field full"><label>Key features</label><textarea name="features" placeholder="One feature per line"><?= e($row['features']) ?></textarea></div>
      </div></div>
      <div class="card card-pad"><h3 style="font-size:14px;margin-bottom:12px">Photos</h3>
        <div class="form-grid"><?= cover_field($row['cover_image']) ?><?= gallery_editor($galleryRows) ?></div>
      </div>
      <div class="card card-pad"><h3 style="font-size:14px;margin-bottom:12px">SEO (optional)</h3>
        <div class="form-grid">
          <div class="field full"><label>Meta title</label><input type="text" name="meta_title" value="<?= e($row['meta_title']) ?>"></div>
          <div class="field full"><label>Meta description</label><textarea name="meta_description"><?= e($row['meta_description']) ?></textarea></div>
        </div>
      </div>
    </div>
    <div class="stack">
      <div class="card card-pad"><h3 style="font-size:14px;margin-bottom:12px">Publish</h3>
        <div class="field"><label>Status</label><?= status_select($row['status'],['published'=>'Published (live)','draft'=>'Draft (hidden)','sold'=>'Sold'],'status') ?></div>
        <label class="check" style="margin-top:14px"><input type="checkbox" name="featured" <?= $row['featured']?'checked':'' ?>> Feature on homepage</label>
        <div class="form-actions"><button class="btn btn-primary" type="submit">Save house</button><a class="btn btn-ghost" href="houses.php">Cancel</a></div>
      </div>
      <?php if ($id && $row['slug']): ?>
      <div class="card card-pad mini"><span class="muted">Public link</span><br>
        <a href="../house-detail.php?slug=<?= e($row['slug']) ?>" target="_blank" style="color:var(--green);font-weight:600">View on site ↗</a></div>
      <?php endif; ?>
      <?= $id ? enquiries_panel('house', $id) : '' ?>
    </div>
  </div>
</form>
<?php require __DIR__ . '/_foot.php'; ?>
