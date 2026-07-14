<?php
require_once __DIR__ . '/app/helpers.php';
$pdo = db();

$rows = $pdo->query("SELECT * FROM articles WHERE status='published' ORDER BY COALESCE(published_at,created_at) DESC")->fetchAll();

$page_title = 'Blog | Landplan.co.ke';
$page_desc  = 'Insights, guides and updates on land, construction and property investment in Kenya.';
$active = '';
require __DIR__ . '/app/partials/head.php';
$img = fn($p) => e(ltrim((string)$p, '/'));
?>
<section class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="index.html">Home</a><span class="sep">/</span><span class="current">Blog</span></div>
    <h1>Blog</h1>
    <p class="lead">Insights, guides and updates on land, construction and property investment in Kenya.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <?php if ($rows): ?>
    <div class="blog-grid">
      <?php foreach ($rows as $r): ?>
        <a href="article.php?slug=<?= e($r['slug']) ?>" class="blog-card">
          <img src="<?= $img($r['cover_image']) ?>" alt="<?= e($r['title']) ?>">
          <div class="blog-body">
            <p class="blog-cat"><?= e($r['category'] ?: 'Article') ?></p>
            <h3><?= e($r['title']) ?></h3>
            <p><?= e(excerpt($r['excerpt'] ?: strip_tags($r['body'] ?? ''), 22)) ?></p>
            <div class="blog-meta"><span><?= e(nice_date($r['published_at'] ?: $r['created_at'])) ?></span></div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
      <p class="muted" style="text-align:center;padding:60px 0">No articles published yet. Check back soon.</p>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/app/partials/footer.php'; ?>
