<?php
require_once __DIR__ . '/app/helpers.php';
$pdo = db();

$slug = get('slug');
$stmt = $pdo->prepare("SELECT a.*, u.name AS author FROM articles a LEFT JOIN users u ON u.id=a.author_id WHERE a.slug=? AND a.status='published' LIMIT 1");
$stmt->execute([$slug]);
$A = $stmt->fetch();
if (!$A) { http_response_code(404); require __DIR__ . '/404.html'; exit; }

$more = $pdo->prepare("SELECT title,slug,excerpt,cover_image,category,published_at,created_at FROM articles WHERE status='published' AND id<>? ORDER BY COALESCE(published_at,created_at) DESC LIMIT 3");
$more->execute([$A['id']]);
$more = $more->fetchAll();

$page_title = $A['meta_title'] ?: ($A['title'] . ' — Landplan.co.ke');
$page_desc  = $A['meta_description'] ?: ($A['excerpt'] ?: excerpt($A['body'] ?: $A['title'], 30));
$og_image   = $A['cover_image'] ? base_url(ltrim($A['cover_image'],'/')) : '';
$active = '';
require __DIR__ . '/app/partials/head.php';
$img = fn($p) => e(ltrim((string)$p, '/'));
?>
<section class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="index.html">Home</a><span class="sep">/</span><a href="blog.html">Blog</a><span class="sep">/</span><span class="current"><?= e($A['category'] ?: 'Article') ?></span></div>
    <h1><?= e($A['title']) ?></h1>
    <p class="lead"><?= e($A['category'] ? $A['category'] . ' · ' : '') ?><?= e(nice_date($A['published_at'] ?: $A['created_at'])) ?><?= $A['author'] ? ' · by ' . e($A['author']) : '' ?></p>
  </div>
</section>

<section class="section">
  <div class="container" style="max-width:820px">
    <?php if ($A['cover_image']): ?>
    <div style="border-radius:14px;overflow:hidden;margin-bottom:26px"><img src="<?= $img($A['cover_image']) ?>" alt="<?= e($A['title']) ?>" style="width:100%"></div>
    <?php endif; ?>
    <div class="prose article-body">
      <?= safe_html($A['body']) ?>
    </div>
  </div>
</section>

<?php if ($more): ?>
<section class="section" style="background:var(--bg-soft)">
  <div class="container">
    <div class="section-head"><h2>More from the blog</h2></div>
    <div class="blog-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:22px">
      <?php foreach ($more as $m): ?>
        <article class="land-card">
          <div class="land-media"><a href="article.php?slug=<?= e($m['slug']) ?>"><img src="<?= $img($m['cover_image']) ?>" alt="<?= e($m['title']) ?>"></a></div>
          <a href="article.php?slug=<?= e($m['slug']) ?>" class="land-body">
            <p class="land-loc"><?= e($m['category']) ?> · <?= e(nice_date($m['published_at'] ?: $m['created_at'])) ?></p>
            <h3><?= e($m['title']) ?></h3>
            <p class="muted"><?= e(excerpt($m['excerpt'] ?: '', 16)) ?></p>
          </a>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
<?php require __DIR__ . '/app/partials/footer.php'; ?>
