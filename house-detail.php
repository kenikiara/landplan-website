<?php
require_once __DIR__ . '/app/helpers.php';
$pdo = db();

$slug = get('slug');
$stmt = $pdo->prepare("SELECT * FROM houses WHERE slug=? AND status IN ('published','sold') LIMIT 1");
$stmt->execute([$slug]);
$H = $stmt->fetch();
if (!$H) { http_response_code(404); require __DIR__ . '/404.html'; exit; }

$imgs = $pdo->prepare('SELECT path FROM house_images WHERE house_id=? ORDER BY sort, id');
$imgs->execute([$H['id']]);
$gallery = array_column($imgs->fetchAll(), 'path');
$hero = $H['cover_image'] ?: ($gallery[0] ?? '');
$side = array_values(array_filter($gallery, fn($p) => $p !== $H['cover_image']));
$features = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', (string)$H['features']))));

$similar = $pdo->prepare("SELECT title,slug,location,price,bedrooms,cover_image FROM houses WHERE status='published' AND id<>? ORDER BY featured DESC, created_at DESC LIMIT 3");
$similar->execute([$H['id']]);
$similar = $similar->fetchAll();

$page_title = $H['meta_title'] ?: ($H['title'] . ', ' . $H['location'] . ' | Landplan.co.ke');
$page_desc  = $H['meta_description'] ?: excerpt($H['description'] ?: $H['title'], 30);
$og_image   = $hero ? base_url(ltrim($hero,'/')) : '';
$active = '';
require __DIR__ . '/app/partials/head.php';
$img = fn($p) => e(ltrim((string)$p, '/'));
?>
<section class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="index.html">Home</a><span class="sep">/</span><a href="houses.html">Houses for Sale</a><span class="sep">/</span><span class="current"><?= e($H['title']) ?></span></div>
    <h1><?= e($H['title']) ?>, <?= e($H['location']) ?></h1>
    <?php if ($H['status'] === 'sold'): ?><p class="lead" style="color:#c0392b;font-weight:700">This home has been SOLD.</p><?php endif; ?>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="detail-layout">
      <div class="detail-main">
        <div class="detail-gallery">
          <div class="g-main"><img src="<?= $img($hero) ?>" alt="<?= e($H['title']) ?>" data-lightbox></div>
          <?php foreach (array_slice($side, 0, 2) as $s): ?>
            <div class="g-side"><img src="<?= $img($s) ?>" alt="<?= e($H['title']) ?>" data-lightbox></div>
          <?php endforeach; ?>
        </div>

        <div class="detail-title-row"><h2><?= e($H['title']) ?></h2><span class="tag">HOUSE</span></div>
        <p class="detail-loc"><svg viewBox="0 0 24 24"><path d="M12 2C8.1 2 5 5.1 5 9c0 5.2 7 13 7 13s7-7.8 7-13c0-3.9-3.1-7-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"/></svg> <?= e($H['location']) ?></p>

        <div class="facts-grid">
          <div class="fact"><span class="f-label">Bedrooms</span><span class="f-val"><?= (int)$H['bedrooms'] ?></span></div>
          <div class="fact"><span class="f-label">Bathrooms</span><span class="f-val"><?= (int)$H['bathrooms'] ?></span></div>
          <div class="fact"><span class="f-label">Price</span><span class="f-val"><?= e(ksh($H['price'])) ?></span></div>
          <div class="fact"><span class="f-label">Size</span><span class="f-val"><?= e($H['size'] ?: '-') ?></span></div>
        </div>

        <?php if ($H['description']): ?>
        <div class="prose"><h3>Overview</h3>
          <?php foreach (preg_split('/\r?\n\r?\n/', $H['description']) as $para): if(trim($para)==='')continue; ?><p><?= e(trim($para)) ?></p><?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($features): ?>
        <h3>Key Features</h3>
        <ul class="check-list">
          <?php foreach ($features as $f): ?><li><svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm-1 14-4-4 1.4-1.4L11 13.2l5.6-5.6L18 9l-7 7z"/></svg> <?= e($f) ?></li><?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <?php if ($similar): ?>
        <h3>Similar Homes</h3>
        <div class="similar-grid">
          <?php foreach ($similar as $s): ?>
          <article class="land-card">
            <div class="land-media"><a href="house-detail.php?slug=<?= e($s['slug']) ?>"><img src="<?= $img($s['cover_image']) ?>" alt="<?= e($s['title']) ?>"></a><span class="tag">HOUSE</span></div>
            <a href="house-detail.php?slug=<?= e($s['slug']) ?>" class="land-body">
              <p class="land-loc"><?= e($s['location']) ?></p><h3><?= e($s['title']) ?></h3><p class="land-price"><?= e(ksh($s['price'])) ?></p>
              <p class="land-deed"><?= (int)$s['bedrooms'] ?> bed</p>
            </a>
          </article>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <aside class="sidebar-card">
        <p class="side-price"><?= e(ksh($H['price'])) ?></p>
        <p class="side-sub"><?= (int)$H['bedrooms'] ?> bed &middot; <?= (int)$H['bathrooms'] ?> bath &middot; <?= e($H['location']) ?></p>
        <p style="margin:12px 0"><button class="btn btn-outline-dark btn-sm save-btn-lg" data-save-type="house" data-save-id="<?= (int)$H['id'] ?>">♥ Save this home</button></p>
        <form class="form-grid" data-enquiry>
          <input type="hidden" name="item_type" value="house">
          <input type="hidden" name="item_id" value="<?= (int)$H['id'] ?>">
          <input type="hidden" name="source" value="House detail: <?= e($H['title']) ?>">
          <input type="hidden" name="interest" value="Buying a House">
          <input type="text" name="company" style="display:none" tabindex="-1" autocomplete="off">
          <div class="form-field full"><label>Full Name</label><input type="text" name="name" required></div>
          <div class="form-field full"><label>Email</label><input type="email" name="email"></div>
          <div class="form-field full"><label>Phone</label><input type="tel" name="phone" required></div>
          <div class="form-field full"><label>Message</label><textarea name="message">I'm interested in the <?= e($H['title']) ?> in <?= e($H['location']) ?>. Please send me more details.</textarea></div>
          <div class="form-field full"><button type="submit" class="btn btn-green">Send Inquiry</button></div>
        </form>
        <div class="form-success"><svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm-1 14-4-4 1.4-1.4L11 13.2l5.6-5.6L18 9l-7 7z"/></svg> Thanks! We'll be in touch within 24 hours.</div>
      </aside>
    </div>
  </div>
</section>
<div class="lightbox" id="lightbox"><span class="lightbox-close">&times;</span><img src="" alt=""></div>
<?php require __DIR__ . '/app/partials/footer.php'; ?>
