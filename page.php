<?php
require_once __DIR__ . '/app/helpers.php';
$pdo = db();

$slug = get('slug');
$stmt = $pdo->prepare("SELECT * FROM pages WHERE slug=? AND status='published' LIMIT 1");
$stmt->execute([$slug]);
$P = $stmt->fetch();
if (!$P) { http_response_code(404); require __DIR__ . '/404.html'; exit; }

$page_title = $P['meta_title'] ?: ($P['title'] . ' — Landplan.co.ke');
$page_desc  = $P['meta_description'] ?: excerpt($P['body'] ?: $P['title'], 30);
$active = '';
require __DIR__ . '/app/partials/head.php';
?>
<section class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="index.html">Home</a><span class="sep">/</span><span class="current"><?= e($P['title']) ?></span></div>
    <h1><?= e($P['title']) ?></h1>
  </div>
</section>
<section class="section">
  <div class="container" style="max-width:820px">
    <div class="prose"><?= safe_html($P['body']) ?></div>
  </div>
</section>
<?php require __DIR__ . '/app/partials/footer.php'; ?>
