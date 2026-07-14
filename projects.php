<?php
require_once __DIR__ . '/app/helpers.php';
$pdo = db();

$ongoing   = $pdo->query("SELECT * FROM projects WHERE status='ongoing'   ORDER BY featured DESC, created_at DESC")->fetchAll();
$completed = $pdo->query("SELECT * FROM projects WHERE status='completed' ORDER BY featured DESC, created_at DESC")->fetchAll();

$page_title = 'Our Projects | Landplan.co.ke';
$page_desc  = 'From master-planned estates to gated communities, explore the developments Landplan is building across Kenya.';
$active = 'projects';
require __DIR__ . '/app/partials/head.php';
$img = fn($p) => e(ltrim((string)$p, '/'));

function project_cards(array $rows, callable $img): void {
    if (!$rows) { echo '<p class="muted" style="padding:8px 0 24px">Nothing to show here yet.</p>'; return; }
    echo '<div class="land-grid">';
    foreach ($rows as $r) {
        echo '<article class="land-card"><div class="land-media">'
           . '<a href="project-detail.php?slug=' . e($r['slug']) . '"><img src="' . $img($r['cover_image']) . '" alt="' . e($r['title']) . '"></a>'
           . '<span class="tag">' . e(strtoupper($r['status'])) . '</span></div>'
           . '<a href="project-detail.php?slug=' . e($r['slug']) . '" class="land-body">'
           . '<p class="land-loc">' . e($r['location']) . '</p><h3>' . e($r['title']) . '</h3>'
           . '<p class="muted">' . e(excerpt($r['description'] ?: '', 16)) . '</p></a></article>';
    }
    echo '</div>';
}
?>
<section class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="index.html">Home</a><span class="sep">/</span><span class="current">Projects</span></div>
    <h1>Our Projects</h1>
    <p class="lead">From master-planned estates to gated communities, explore the developments Landplan is building across Kenya.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div id="ongoing"></div>
    <div class="section-head split"><div><p class="eyebrow">IN PROGRESS</p><h2>Ongoing Projects</h2></div></div>
    <?php project_cards($ongoing, $img); ?>

    <div id="completed" style="margin-top:48px"></div>
    <div class="section-head split"><div><p class="eyebrow">DELIVERED</p><h2>Completed Projects</h2></div></div>
    <?php project_cards($completed, $img); ?>
  </div>
</section>

<section class="cta reveal">
  <div class="container cta-inner">
    <div><h2>Interested in Investing in a Project?</h2><p>Talk to our team about upcoming developments and off-plan opportunities.</p></div>
    <div class="cta-actions"><a href="contact.html" class="btn btn-green">Get in Touch <span class="arrow">&#8594;</span></a></div>
  </div>
</section>
<?php require __DIR__ . '/app/partials/footer.php'; ?>
