<?php
require_once __DIR__ . '/app/helpers.php';
$pdo = db();
$s = settings();

$services     = $pdo->query("SELECT * FROM services WHERE status='published' ORDER BY sort, id LIMIT 6")->fetchAll();
$featuredLand = $pdo->query("SELECT * FROM land_listings WHERE status='published' AND featured=1 ORDER BY created_at DESC LIMIT 4")->fetchAll();
if (!$featuredLand) $featuredLand = $pdo->query("SELECT * FROM land_listings WHERE status='published' ORDER BY created_at DESC LIMIT 4")->fetchAll();
$projects     = $pdo->query("SELECT * FROM projects ORDER BY featured DESC, created_at DESC LIMIT 4")->fetchAll();
$testimonials = $pdo->query("SELECT * FROM testimonials WHERE status='published' ORDER BY sort, id LIMIT 3")->fetchAll();

// service card -> destination page
$svcLink = [
    'land-for-sale' => 'land.html', 'houses-for-sale' => 'houses.html',
    'architecture-design' => 'service-architecture.html',
    'building-construction' => 'service-construction.html',
    'project-development' => 'service-project-development.html',
    'due-diligence' => 'service-due-diligence.html',
];

$page_title = ($s['site_name'] ?? 'Landplan.co.ke') . ', Your One Stop Shop for Land & Property Solutions';
$page_desc  = $s['meta_description'] ?? 'We sell land, design dream spaces, build quality homes and develop projects that last generations.';
$active = 'home';
require __DIR__ . '/app/partials/head.php';
$img = fn($p) => e(ltrim((string)$p, '/'));
$heroTitle = $s['hero_title'] ?? 'Your One Stop Shop for Land & Property Solutions';
?>
<!-- HERO -->
<section class="hero">
  <div class="hero-media" style="background-image:url('assets/img/hero.jpg')"></div>
  <div class="container hero-inner">
    <div class="hero-copy">
      <p class="hero-kicker"><?= e($s['hero_kicker'] ?? 'Trusted. Transparent. Professional.') ?></p>
      <h1><?= e($heroTitle) ?></h1>
      <p class="hero-sub"><?= e($s['hero_sub'] ?? 'We sell land, design dream spaces, build quality homes and develop projects that last generations.') ?></p>
      <div class="hero-actions">
        <a href="land.html" class="btn btn-green">Explore Land for Sale</a>
        <a href="services.html" class="btn btn-outline">Our Services <span class="arrow">&#8594;</span></a>
      </div>
    </div>
  </div>
  <div class="container">
    <div class="feature-strip reveal">
      <div class="feature"><span class="feature-ico"><svg viewBox="0 0 24 24"><path d="M12 2C8.1 2 5 5.1 5 9c0 5.2 7 13 7 13s7-7.8 7-13c0-3.9-3.1-7-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"/></svg></span><div><h4>Prime Locations</h4><p>Handpicked land in growing areas</p></div></div>
      <div class="feature"><span class="feature-ico"><svg viewBox="0 0 24 24"><path d="M12 2 4 5v6c0 5 3.4 9.7 8 11 4.6-1.3 8-6 8-11V5l-8-3zm-1 14-4-4 1.4-1.4L11 13.2l5.6-5.6L18 9l-7 7z"/></svg></span><div><h4>Secure Ownership</h4><p>Genuine title deeds and legal due diligence</p></div></div>
      <div class="feature"><span class="feature-ico"><svg viewBox="0 0 24 24"><path d="M16 11c1.7 0 3-1.3 3-3s-1.3-3-3-3-3 1.3-3 3 1.3 3 3 3zm-8 0c1.7 0 3-1.3 3-3S9.7 5 8 5 5 6.3 5 8s1.3 3 3 3zm0 2c-2.3 0-7 1.2-7 3.5V19h14v-2.5c0-2.3-4.7-3.5-7-3.5zm8 0c-.3 0-.6 0-1 .1 1.2.8 2 2 2 3.4V19h6v-2.5c0-2.3-4.7-3.5-7-3.5z"/></svg></span><div><h4>Expert Team</h4><p>Experienced professionals you can trust</p></div></div>
      <div class="feature"><span class="feature-ico"><svg viewBox="0 0 24 24"><path d="M12 3 2 12h3v8h6v-6h2v6h6v-8h3L12 3z"/></svg></span><div><h4>End-to-End Service</h4><p>From land to keys, we have you covered</p></div></div>
    </div>
  </div>
</section>

<!-- ABOUT + STATS -->
<section class="about section" id="about">
  <div class="container about-grid">
    <div class="about-copy reveal">
      <p class="eyebrow">ABOUT LANDPLAN</p>
      <h2>Building Better<br>Tomorrows, Today</h2>
      <p class="muted">Landplan.co.ke is a leading land and property solutions company in Kenya. We take you from acquiring land to designing, building and developing world-class projects, all under one roof.</p>
      <p style="margin-top:18px"><a href="about.html" class="svc-link">Learn More About Us <span class="arrow">&#8594;</span></a></p>
    </div>
    <div class="stats-card reveal reveal-1">
      <div class="stat"><span class="stat-ico"><svg viewBox="0 0 24 24"><path d="M12 2C8.1 2 5 5.1 5 9c0 5.2 7 13 7 13s7-7.8 7-13c0-3.9-3.1-7-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"/></svg></span><strong data-count="<?= (int)($s['stat_years']??10) ?>" data-suffix="+">0+</strong><span>Years of Experience</span></div>
      <div class="stat"><span class="stat-ico"><svg viewBox="0 0 24 24"><path d="M12 21s-6.7-4.3-9.3-8C.8 10.2 2 6.4 5.2 5.3 7.2 4.6 9.3 5.3 12 8c2.7-2.7 4.8-3.4 6.8-2.7 3.2 1.1 4.4 4.9 2.5 7.7C18.7 16.7 12 21 12 21z"/></svg></span><strong data-count="<?= (int)($s['stat_clients']??5000) ?>" data-suffix="+">0+</strong><span>Happy Clients</span></div>
      <div class="stat"><span class="stat-ico"><svg viewBox="0 0 24 24"><path d="M3 5v14h18V5H3zm16 12H5V7h14v10zM7 9h4v2H7V9zm6 0h4v6h-4V9zm-6 4h4v2H7v-2z"/></svg></span><strong data-count="<?= (int)($s['stat_acres']??1000) ?>" data-suffix="+">0+</strong><span>Acres Sold</span></div>
      <div class="stat"><span class="stat-ico"><svg viewBox="0 0 24 24"><path d="M22 9 12 2 2 9h3v11h6v-6h2v6h6V9h3z"/></svg></span><strong data-count="<?= (int)($s['stat_projects']??300) ?>" data-suffix="+">0+</strong><span>Projects Completed</span></div>
    </div>
  </div>
</section>

<!-- WHAT WE DO (services from DB) -->
<?php if ($services): ?>
<section class="services section" id="services">
  <div class="container">
    <div class="section-head center reveal"><p class="eyebrow">WHAT WE DO</p><h2>Comprehensive Land &amp; Property Solutions</h2></div>
    <div class="services-grid">
      <?php foreach ($services as $i => $sv): $link = $svcLink[$sv['slug']] ?? 'services.html'; ?>
      <article class="svc-card reveal reveal-<?= $i+1 ?>">
        <div class="svc-media"><img src="<?= $img($sv['cover_image']) ?>" alt="<?= e($sv['title']) ?>"><span class="svc-badge"><svg viewBox="0 0 24 24"><path d="m12 2 2.4 4.9 5.4.8-3.9 3.8.9 5.4L12 14.9 7.2 17l.9-5.4L4.2 7.7l5.4-.8L12 2z"/></svg></span></div>
        <div class="svc-body"><h3><?= e($sv['title']) ?></h3><p><?= e($sv['excerpt']) ?></p><a class="svc-link" href="<?= e($link) ?>">Learn More <span class="arrow">&#8594;</span></a></div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- FEATURED LAND (from DB) -->
<?php if ($featuredLand): ?>
<section class="land section" id="land">
  <div class="container">
    <div class="section-head split reveal">
      <div><p class="eyebrow">FEATURED LAND</p><h2>Prime Land. Strategic Locations.</h2></div>
      <div class="head-actions"><a href="land.html" class="btn btn-outline-dark btn-sm">View All Land <span class="arrow">&#8594;</span></a></div>
    </div>
    <div class="land-grid">
      <?php foreach ($featuredLand as $i => $r): ?>
      <article class="land-card reveal reveal-<?= $i+1 ?>">
        <div class="land-media">
          <a href="land-detail.php?slug=<?= e($r['slug']) ?>"><img src="<?= $img($r['cover_image']) ?>" alt="<?= e($r['title']) ?>"></a>
          <span class="tag"><?= e(strtoupper($r['category'])) ?></span>
          <button class="save-btn" aria-label="Save" data-save-type="land" data-save-id="<?= (int)$r['id'] ?>"><svg viewBox="0 0 24 24"><path d="M17 3H7c-1.1 0-2 .9-2 2v16l7-3 7 3V5c0-1.1-.9-2-2-2z"/></svg></button>
        </div>
        <a href="land-detail.php?slug=<?= e($r['slug']) ?>" class="land-body">
          <p class="land-loc"><?= e($r['location']) ?></p><h3><?= e($r['title']) ?></h3>
          <p class="land-price"><?= e(ksh($r['price'])) ?></p>
          <p class="land-deed"><svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm-1 14-4-4 1.4-1.4L11 13.2l5.6-5.6L18 9l-7 7z"/></svg> <?= e($r['title_status'] ?: 'Ready Title Deed') ?></p>
        </a>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- WHY CHOOSE US (static) -->
<section class="why section">
  <div class="container">
    <div class="why-band reveal">
      <div class="why-copy">
        <p class="eyebrow light">WHY CHOOSE US</p>
        <h2>The Most Reputable<br>Land Company in Kenya</h2>
        <p>We are committed to transparency, integrity and delivering real value to every client we work with.</p>
        <a href="about.html" class="btn btn-outline btn-sm">More About Us <span class="arrow">&#8594;</span></a>
      </div>
      <div class="why-grid">
        <div class="why-item"><span class="why-ico"><svg viewBox="0 0 24 24"><path d="M12 2 4 5v6c0 5 3.4 9.7 8 11 4.6-1.3 8-6 8-11V5l-8-3zm-1 14-4-4 1.4-1.4L11 13.2l5.6-5.6L18 9l-7 7z"/></svg></span><div><h4>Verified &amp; Genuine Deals</h4><p>Every parcel is verified with genuine titles and documents.</p></div></div>
        <div class="why-item"><span class="why-ico"><svg viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg></span><div><h4>Flexible Payment Plans</h4><p>Affordable payment options that work for you.</p></div></div>
        <div class="why-item"><span class="why-ico"><svg viewBox="0 0 24 24"><path d="M16 11c1.7 0 3-1.3 3-3s-1.3-3-3-3-3 1.3-3 3 1.3 3 3 3zm-8 0c1.7 0 3-1.3 3-3S9.7 5 8 5 5 6.3 5 8s1.3 3 3 3zm0 2c-2.3 0-7 1.2-7 3.5V19h14v-2.5c0-2.3-4.7-3.5-7-3.5zm8 0c-.3 0-.6 0-1 .1 1.2.8 2 2 2 3.4V19h6v-2.5c0-2.3-4.7-3.5-7-3.5z"/></svg></span><div><h4>Professional Team</h4><p>Surveyors, architects, engineers and legal experts.</p></div></div>
        <div class="why-item"><span class="why-ico"><svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg></span><div><h4>After Sales Support</h4><p>We walk with you every step of the way.</p></div></div>
      </div>
    </div>
  </div>
</section>

<!-- PROJECTS (from DB) -->
<?php if ($projects): ?>
<section class="projects section" id="projects">
  <div class="container">
    <div class="section-head split reveal">
      <div><p class="eyebrow">OUR PROJECTS</p><h2>Shaping communities. Building the future.</h2></div>
      <div class="head-actions"><a href="projects.html" class="btn btn-outline-dark btn-sm">View All Projects <span class="arrow">&#8594;</span></a></div>
    </div>
    <div class="projects-grid">
      <?php foreach ($projects as $i => $p): ?>
        <figure class="proj reveal reveal-<?= $i+1 ?>"><a href="project-detail.php?slug=<?= e($p['slug']) ?>"><img src="<?= $img($p['cover_image']) ?>" alt="<?= e($p['title']) ?>"></a></figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- TESTIMONIALS (from DB) -->
<?php if ($testimonials): ?>
<section class="testimonials section">
  <div class="container">
    <div class="section-head split reveal"><div><p class="eyebrow">WHAT CLIENTS SAY</p><h2>Trusted by Thousands of Happy Clients</h2></div></div>
    <div class="testi-wrap"><div class="testi-grid">
      <?php foreach ($testimonials as $i => $t): ?>
      <article class="testi-card reveal reveal-<?= $i+1 ?>">
        <span class="quote">&#8220;&#8220;</span>
        <p><?= e($t['quote']) ?></p>
        <div class="testi-foot">
          <span class="testi-name"><?= e($t['name']) ?><br><small><?= e($t['location']) ?></small></span>
          <span class="stars"><?= str_repeat('&#9733;', (int)$t['rating']) . str_repeat('&#9734;', 5 - (int)$t['rating']) ?></span>
        </div>
      </article>
      <?php endforeach; ?>
    </div></div>
  </div>
</section>
<?php endif; ?>
<?php require __DIR__ . '/app/partials/footer.php'; ?>
