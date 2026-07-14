<?php
require_once __DIR__ . '/app/helpers.php';
$pdo = db();

$slug = get('slug');
$stmt = $pdo->prepare("SELECT * FROM land_listings WHERE slug=? AND status IN ('published','sold') LIMIT 1");
$stmt->execute([$slug]);
$L = $stmt->fetch();
if (!$L) { http_response_code(404); require __DIR__ . '/404.html'; exit; }

$imgs = $pdo->prepare('SELECT path FROM land_images WHERE listing_id=? ORDER BY sort, id');
$imgs->execute([$L['id']]);
$gallery = array_column($imgs->fetchAll(), 'path');
$hero = $L['cover_image'] ?: ($gallery[0] ?? '');
$side = array_values(array_filter($gallery, fn($p) => $p !== $L['cover_image']));

$features = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', (string)$L['features']))));

$similar = $pdo->prepare("SELECT title,slug,location,price,category,cover_image,title_status FROM land_listings
                          WHERE status='published' AND id<>? ORDER BY featured DESC, created_at DESC LIMIT 3");
$similar->execute([$L['id']]);
$similar = $similar->fetchAll();

$page_title = $L['meta_title'] ?: ($L['title'] . ' – ' . $L['location'] . ' — Landplan.co.ke');
$page_desc  = $L['meta_description'] ?: excerpt($L['description'] ?: $L['title'], 30);
$og_image   = $hero ? base_url(ltrim($hero,'/')) : '';
$active = 'land';
require __DIR__ . '/app/partials/head.php';
$img = fn($p) => e(ltrim((string)$p, '/'));
?>
<section class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="index.html">Home</a><span class="sep">/</span><a href="land.html">Land for Sale</a><span class="sep">/</span><span class="current"><?= e($L['title']) ?></span></div>
    <h1><?= e($L['title']) ?> – <?= e($L['location']) ?></h1>
    <?php if ($L['status'] === 'sold'): ?><p class="lead" style="color:#c0392b;font-weight:700">This plot has been SOLD.</p><?php endif; ?>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="detail-layout">
      <div class="detail-main">

        <div class="detail-gallery">
          <div class="g-main"><img src="<?= $img($hero) ?>" alt="<?= e($L['title']) ?>" data-lightbox></div>
          <?php foreach (array_slice($side, 0, 2) as $s): ?>
            <div class="g-side"><img src="<?= $img($s) ?>" alt="<?= e($L['title']) ?>" data-lightbox></div>
          <?php endforeach; ?>
        </div>

        <div class="detail-title-row">
          <h2><?= e($L['size'] ?: $L['title']) ?></h2>
          <span class="tag"><?= e(strtoupper($L['category'])) ?></span>
        </div>
        <p class="detail-loc">
          <svg viewBox="0 0 24 24"><path d="M12 2C8.1 2 5 5.1 5 9c0 5.2 7 13 7 13s7-7.8 7-13c0-3.9-3.1-7-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"/></svg>
          <?= e($L['location']) ?>
        </p>

        <div class="facts-grid">
          <div class="fact"><span class="f-label">Plot Size</span><span class="f-val"><?= e($L['size'] ?: '—') ?></span></div>
          <div class="fact"><span class="f-label">Price</span><span class="f-val"><?= e(ksh($L['price'])) ?></span></div>
          <div class="fact"><span class="f-label">Title Deed Status</span><span class="f-val"><?= e($L['title_status'] ?: 'Ready Title Deed') ?></span></div>
          <div class="fact"><span class="f-label">Category</span><span class="f-val"><?= e($L['category']) ?></span></div>
        </div>

        <?php if ($L['description']): ?>
        <div class="prose">
          <h3>Property Overview</h3>
          <?php foreach (preg_split('/\r?\n\r?\n/', $L['description']) as $para): if(trim($para)==='')continue; ?>
            <p><?= e(trim($para)) ?></p>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($features): ?>
        <h3>Key Features</h3>
        <ul class="check-list">
          <?php foreach ($features as $f): ?>
            <li><svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm-1 14-4-4 1.4-1.4L11 13.2l5.6-5.6L18 9l-7 7z"/></svg> <?= e($f) ?></li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <?php if ($L['map_embed']): ?>
        <h3>Location</h3>
        <div class="map-embed" style="border-radius:12px;overflow:hidden;margin-bottom:12px">
          <?php if (stripos($L['map_embed'],'<iframe')!==false): ?>
            <?= safe_html($L['map_embed']) ?>
          <?php else: ?>
            <iframe src="<?= e($L['map_embed']) ?>" width="100%" height="320" style="border:0" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
          <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($similar): ?>
        <h3>Similar Properties</h3>
        <div class="similar-grid">
          <?php foreach ($similar as $s): ?>
          <article class="land-card">
            <div class="land-media">
              <a href="land-detail.php?slug=<?= e($s['slug']) ?>"><img src="<?= $img($s['cover_image']) ?>" alt="<?= e($s['title']) ?>"></a>
              <span class="tag"><?= e(strtoupper($s['category'])) ?></span>
            </div>
            <a href="land-detail.php?slug=<?= e($s['slug']) ?>" class="land-body">
              <p class="land-loc"><?= e($s['location']) ?></p>
              <h3><?= e($s['title']) ?></h3>
              <p class="land-price"><?= e(ksh($s['price'])) ?></p>
              <p class="land-deed"><svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm-1 14-4-4 1.4-1.4L11 13.2l5.6-5.6L18 9l-7 7z"/></svg> <?= e($s['title_status'] ?: 'Ready Title Deed') ?></p>
            </a>
          </article>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <aside class="sidebar-card">
        <p class="side-price"><?= e(ksh($L['price'])) ?></p>
        <p class="side-sub"><?= e($L['size']) ?> &middot; <?= e($L['title_status'] ?: 'Ready Title Deed') ?> &middot; <?= e($L['location']) ?></p>
        <p style="margin:12px 0"><button class="btn btn-outline-dark btn-sm save-btn-lg" data-save-type="land" data-save-id="<?= (int)$L['id'] ?>">♥ Save this property</button></p>

        <form class="form-grid" data-enquiry>
          <input type="hidden" name="item_type" value="land">
          <input type="hidden" name="item_id" value="<?= (int)$L['id'] ?>">
          <input type="hidden" name="source" value="Land detail: <?= e($L['title']) ?>">
          <input type="hidden" name="interest" value="Buying Land">
          <input type="text" name="company" style="display:none" tabindex="-1" autocomplete="off">
          <div class="form-field full"><label>Full Name</label><input type="text" name="name" required></div>
          <div class="form-field full"><label>Email</label><input type="email" name="email"></div>
          <div class="form-field full"><label>Phone</label><input type="tel" name="phone" required></div>
          <div class="form-field full"><label>Message</label><textarea name="message">I'm interested in the <?= e($L['title']) ?> in <?= e($L['location']) ?>. Please send me more details.</textarea></div>
          <div class="form-field full"><button type="submit" class="btn btn-green">Send Inquiry</button></div>
        </form>
        <div class="form-success"><svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm-1 14-4-4 1.4-1.4L11 13.2l5.6-5.6L18 9l-7 7z"/></svg> Thanks! We'll be in touch within 24 hours.</div>
      </aside>
    </div>
  </div>
</section>

<div class="lightbox" id="lightbox"><span class="lightbox-close">&times;</span><img src="" alt=""></div>
<?php require __DIR__ . '/app/partials/footer.php'; ?>
