<?php
require_once __DIR__ . '/app/helpers.php';
$pdo = db();

$slug = get('slug');
$stmt = $pdo->prepare("SELECT * FROM projects WHERE slug=? LIMIT 1");
$stmt->execute([$slug]);
$P = $stmt->fetch();
if (!$P) { http_response_code(404); require __DIR__ . '/404.html'; exit; }

$imgs = $pdo->prepare('SELECT path FROM project_images WHERE project_id=? ORDER BY sort, id');
$imgs->execute([$P['id']]);
$gallery = array_column($imgs->fetchAll(), 'path');
$hero = $P['cover_image'] ?: ($gallery[0] ?? '');

$more = $pdo->prepare("SELECT title,slug,location,cover_image,status FROM projects WHERE id<>? ORDER BY featured DESC, created_at DESC LIMIT 3");
$more->execute([$P['id']]);
$more = $more->fetchAll();

$page_title = $P['meta_title'] ?: ($P['title'] . ' | Landplan.co.ke');
$page_desc  = $P['meta_description'] ?: excerpt($P['description'] ?: $P['title'], 30);
$og_image   = $hero ? base_url(ltrim($hero,'/')) : '';
$active = 'projects';
require __DIR__ . '/app/partials/head.php';
$img = fn($p) => e(ltrim((string)$p, '/'));
?>
<section class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="index.html">Home</a><span class="sep">/</span><a href="projects.html">Projects</a><span class="sep">/</span><span class="current"><?= e($P['title']) ?></span></div>
    <h1><?= e($P['title']) ?></h1>
    <p class="lead"><?= e($P['location']) ?> · <?= e(ucfirst($P['status'])) ?> project</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <?php if ($hero): ?>
    <div style="border-radius:14px;overflow:hidden;margin-bottom:24px"><img src="<?= $img($hero) ?>" alt="<?= e($P['title']) ?>" data-lightbox style="width:100%"></div>
    <?php endif; ?>

    <div class="prose" style="max-width:820px">
      <?php foreach (preg_split('/\r?\n\r?\n/', (string)$P['description']) as $para): if(trim($para)==='')continue; ?>
        <p><?= e(trim($para)) ?></p>
      <?php endforeach; ?>
    </div>

    <?php if ($gallery): ?>
    <h3 style="margin:30px 0 14px">Gallery</h3>
    <div class="projects-grid">
      <?php foreach ($gallery as $g): ?>
        <figure class="proj"><img src="<?= $img($g) ?>" alt="<?= e($P['title']) ?>" data-lightbox></figure>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="card" style="margin-top:34px;max-width:560px;border:1px solid var(--line);border-radius:14px;padding:22px">
      <h3 style="margin-bottom:4px">Enquire about this project</h3>
      <p class="muted" style="margin-bottom:16px">Send us a message and our team will get back to you.</p>
      <form class="form-grid" data-enquiry>
        <input type="hidden" name="item_type" value="project">
        <input type="hidden" name="item_id" value="<?= (int)$P['id'] ?>">
        <input type="hidden" name="source" value="Project: <?= e($P['title']) ?>">
        <input type="hidden" name="interest" value="A Project / Estate">
        <input type="text" name="company" style="display:none" tabindex="-1" autocomplete="off" aria-hidden="true">
        <div class="form-field"><label>Full Name</label><input type="text" name="name" required></div>
        <div class="form-field"><label>Phone</label><input type="tel" name="phone" required></div>
        <div class="form-field full"><label>Email</label><input type="email" name="email"></div>
        <div class="form-field full"><label>Message</label><textarea name="message">Hi Landplan, I'm interested in the <?= e($P['title']) ?> project<?= $P['location'] ? ' in ' . e($P['location']) : '' ?>. Please send me more details. Thank you.</textarea></div>
        <div class="form-field full"><button type="submit" class="btn btn-green">Send Enquiry</button></div>
      </form>
      <div class="form-success"><svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm-1 14-4-4 1.4-1.4L11 13.2l5.6-5.6L18 9l-7 7z"/></svg> Thanks! We'll be in touch within 24 hours.</div>
    </div>

    <?php if ($more): ?>
    <h3 style="margin:44px 0 14px">More Projects</h3>
    <div class="projects-grid">
      <?php foreach ($more as $m): ?>
        <figure class="proj"><a href="project-detail.php?slug=<?= e($m['slug']) ?>"><img src="<?= $img($m['cover_image']) ?>" alt="<?= e($m['title']) ?>"></a></figure>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
<div class="lightbox" id="lightbox"><span class="lightbox-close">&times;</span><img src="" alt=""></div>
<?php require __DIR__ . '/app/partials/footer.php'; ?>
