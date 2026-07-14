<?php
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/_crud.php';
$pdo = db();

$id  = (int)get('id');
$row = ['id'=>0,'title'=>'','slug'=>'','location'=>'','size'=>'','price'=>'','category'=>'Residential',
        'title_status'=>'Ready Title Deed','description'=>'','features'=>'','map_embed'=>'',
        'cover_image'=>null,'featured'=>0,'status'=>'published','meta_title'=>'','meta_description'=>''];
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM land_listings WHERE id=?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) { http_response_code(404); exit('Listing not found.'); }
}

if (is_post()) {
    csrf_check();
    $data = [
        'title'        => post('title'),
        'location'     => post('location'),
        'size'         => post('size'),
        'price'        => post('price') === '' ? null : (float)preg_replace('/[^0-9.]/','',post('price')),
        'category'     => post('category'),
        'title_status' => post('title_status'),
        'description'  => post('description'),
        'features'     => post('features'),
        'map_embed'    => post('map_embed'),
        'featured'     => isset($_POST['featured']) ? 1 : 0,
        'status'       => post('status'),
        'meta_title'   => post('meta_title'),
        'meta_description' => post('meta_description'),
    ];
    $errors = [];
    if ($data['title'] === '')    $errors[] = 'Title is required.';
    if ($data['location'] === '') $errors[] = 'Location is required.';

    if (!$errors) {
        $slugBase = slugify(post('slug') !== '' ? post('slug') : $data['title'] . '-' . $data['location']);
        $slug     = unique_slug($slugBase, 'land_listings', $id);

        if ($id) {
            $data['cover_image'] = save_cover('cover_image', 'land', $row['cover_image']);
            $sql = 'UPDATE land_listings SET title=?,slug=?,location=?,size=?,price=?,category=?,title_status=?,
                    description=?,features=?,map_embed=?,cover_image=?,featured=?,status=?,meta_title=?,meta_description=? WHERE id=?';
            $pdo->prepare($sql)->execute([
                $data['title'],$slug,$data['location'],$data['size'],$data['price'],$data['category'],$data['title_status'],
                $data['description'],$data['features'],$data['map_embed'],$data['cover_image'],$data['featured'],$data['status'],
                $data['meta_title'],$data['meta_description'],$id,
            ]);
            activity_log('update','land',$id,$data['title']);
        } else {
            $cover = save_cover('cover_image', 'land', null);
            $sql = 'INSERT INTO land_listings (title,slug,location,size,price,category,title_status,description,features,map_embed,cover_image,featured,status,meta_title,meta_description)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
            $pdo->prepare($sql)->execute([
                $data['title'],$slug,$data['location'],$data['size'],$data['price'],$data['category'],$data['title_status'],
                $data['description'],$data['features'],$data['map_embed'],$cover,$data['featured'],$data['status'],
                $data['meta_title'],$data['meta_description'],
            ]);
            $id = (int)$pdo->lastInsertId();
            activity_log('create','land',$id,$data['title']);
        }

        if (!empty($_POST['remove_images'])) remove_gallery('land_images','listing_id',$id,(array)$_POST['remove_images']);
        add_gallery('land_images','listing_id',$id,'gallery','land');

        flash('Listing saved.');
        redirect('land.php');
    }
    $row = array_merge($row, $data, ['id'=>$id,'slug'=>post('slug')]);
}

$galleryRows = $id ? gallery_rows('land_images','listing_id',$id) : [];
$title = $id ? 'Edit Land Listing' : 'Add Land Listing'; $active = 'land';
require __DIR__ . '/_head.php';
?>
<div class="crumbs"><a href="land.php">Land for Sale</a> / <?= $id ? 'Edit' : 'Add new' ?></div>
<div class="page-head"><h2><?= $id ? 'Edit listing' : 'Add a land listing' ?></h2></div>

<?php if (!empty($errors)): ?><div class="flash error"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>

<form method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <div class="split-2">
    <div class="stack">
      <div class="card card-pad">
        <div class="form-grid">
          <div class="field full"><label>Title *</label><input type="text" name="title" value="<?= e($row['title']) ?>" data-slug-source required></div>
          <div class="field"><label>URL slug</label><input type="text" name="slug" value="<?= e($row['slug']) ?>" data-slug-target placeholder="auto from title"><span class="hint">Web address, e.g. <code>kitengela-1-8-acre</code></span></div>
          <div class="field"><label>Location *</label><input type="text" name="location" value="<?= e($row['location']) ?>" placeholder="Kitengela, Kajiado" required></div>
          <div class="field"><label>Size</label><input type="text" name="size" value="<?= e($row['size']) ?>" placeholder="1/8 Acre Plot"></div>
          <div class="field"><label>Price (KSh)</label><input type="text" name="price" value="<?= $row['price']!==''&&$row['price']!==null ? e((string)(int)$row['price']) : '' ?>" placeholder="1250000"></div>
          <div class="field"><label>Category</label>
            <?= status_select($row['category'], ['Residential'=>'Residential','Commercial'=>'Commercial','Agricultural'=>'Agricultural','Mixed Use'=>'Mixed Use'], 'category') ?>
          </div>
          <div class="field"><label>Title status</label><input type="text" name="title_status" value="<?= e($row['title_status']) ?>" placeholder="Ready Title Deed"></div>
          <div class="field full"><label>Description</label><textarea name="description" class="tall"><?= e($row['description']) ?></textarea></div>
          <div class="field full"><label>Key features</label><textarea name="features" placeholder="One feature per line"><?= e($row['features']) ?></textarea><span class="hint">One per line — shown as a checklist on the listing page.</span></div>
          <div class="field full"><label>Map embed (optional)</label><textarea name="map_embed" placeholder="Paste a Google Maps embed URL or iframe"><?= e($row['map_embed']) ?></textarea></div>
        </div>
      </div>

      <div class="card card-pad">
        <h3 style="font-size:14px;margin-bottom:12px">Photos</h3>
        <div class="form-grid">
          <?= cover_field($row['cover_image']) ?>
          <?= gallery_editor($galleryRows) ?>
        </div>
      </div>

      <div class="card card-pad">
        <h3 style="font-size:14px;margin-bottom:12px">SEO (optional)</h3>
        <div class="form-grid">
          <div class="field full"><label>Meta title</label><input type="text" name="meta_title" value="<?= e($row['meta_title']) ?>"></div>
          <div class="field full"><label>Meta description</label><textarea name="meta_description"><?= e($row['meta_description']) ?></textarea></div>
        </div>
      </div>
    </div>

    <div class="stack">
      <div class="card card-pad">
        <h3 style="font-size:14px;margin-bottom:12px">Publish</h3>
        <div class="field"><label>Status</label>
          <?= status_select($row['status'], ['published'=>'Published (live)','draft'=>'Draft (hidden)','sold'=>'Sold'], 'status') ?>
        </div>
        <label class="check" style="margin-top:14px"><input type="checkbox" name="featured" <?= $row['featured']?'checked':'' ?>> Feature on homepage</label>
        <div class="form-actions">
          <button class="btn btn-primary" type="submit">Save listing</button>
          <a class="btn btn-ghost" href="land.php">Cancel</a>
        </div>
      </div>
      <?php if ($id && $row['slug']): ?>
      <div class="card card-pad mini">
        <span class="muted">Public link</span><br>
        <a href="../land-detail.php?slug=<?= e($row['slug']) ?>" target="_blank" style="color:var(--green);font-weight:600">View on site ↗</a>
      </div>
      <?php endif; ?>
      <?= $id ? enquiries_panel('land', $id) : '' ?>
    </div>
  </div>
</form>
<?php require __DIR__ . '/_foot.php'; ?>
