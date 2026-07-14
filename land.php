<?php
require_once __DIR__ . '/app/helpers.php';
$pdo = db();

// ---- filters (GET) ----
$locsSel  = array_values(array_filter((array)($_GET['loc'] ?? []), 'strlen'));
$typesSel = array_values(array_filter((array)($_GET['type'] ?? []), 'strlen'));
$min      = $_GET['min'] !== '' && isset($_GET['min']) ? (float)$_GET['min'] : null;
$max      = $_GET['max'] !== '' && isset($_GET['max']) ? (float)$_GET['max'] : null;
$sizeSel  = get('size');
$deedOnly = isset($_GET['deed']);
$sort     = get('sort', 'newest');

$where = ["status='published'"]; $args = [];
if ($locsSel) {
    $ors = []; foreach ($locsSel as $l) { $ors[] = 'location LIKE ?'; $args[] = '%' . $l . '%'; }
    $where[] = '(' . implode(' OR ', $ors) . ')';
}
if ($typesSel) {
    $in = implode(',', array_fill(0, count($typesSel), '?'));
    $where[] = "category IN ($in)"; array_push($args, ...$typesSel);
}
if ($min !== null) { $where[] = 'price >= ?'; $args[] = $min; }
if ($max !== null) { $where[] = 'price <= ?'; $args[] = $max; }
if ($sizeSel !== '') { $where[] = 'size LIKE ?'; $args[] = '%' . str_replace('-', '/', $sizeSel) . '%'; }
if ($deedOnly) { $where[] = "title_status LIKE '%Ready%'"; }

$order = 'featured DESC, created_at DESC';
if ($sort === 'price_asc')  $order = 'price ASC';
if ($sort === 'price_desc') $order = 'price DESC';

$sql = 'SELECT * FROM land_listings WHERE ' . implode(' AND ', $where) . " ORDER BY $order";
$stmt = $pdo->prepare($sql);
$stmt->execute($args);
$rows = $stmt->fetchAll();

// distinct towns (first part of location) for the filter list
$allLocs = [];
foreach ($pdo->query("SELECT DISTINCT location FROM land_listings WHERE status='published'") as $r) {
    $town = trim(explode(',', $r['location'])[0]);
    if ($town !== '') $allLocs[$town] = true;
}
$allLocs = array_keys($allLocs); sort($allLocs);

$page_title = 'Land for Sale in Kenya | Landplan.co.ke';
$page_desc  = 'Verified plots with ready title deeds across Kenya\'s fastest-growing towns. Filter by location, size and budget to find your perfect plot.';
$active = 'land';
require __DIR__ . '/app/partials/head.php';
$img = fn($p) => e(ltrim((string)$p, '/'));
$cats = ['Residential','Commercial','Agricultural','Mixed Use'];
?>
<section class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="index.html">Home</a><span class="sep">/</span><span class="current">Land for Sale</span></div>
    <h1>Land for Sale</h1>
    <p class="lead">Verified plots with ready title deeds across Kenya's fastest-growing towns. Filter by location, size and budget to find a plot that fits.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <form class="listing-layout" method="get">
      <aside class="filter-box">
        <?php if ($allLocs): ?>
        <div class="filter-group">
          <label class="f-label">Location</label>
          <?php foreach ($allLocs as $town): ?>
            <label class="filter-check"><input type="checkbox" name="loc[]" value="<?= e($town) ?>" <?= in_array($town,$locsSel,true)?'checked':'' ?>> <?= e($town) ?></label>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="filter-group">
          <label class="f-label">Property Type</label>
          <?php foreach ($cats as $c): ?>
            <label class="filter-check"><input type="checkbox" name="type[]" value="<?= e($c) ?>" <?= in_array($c,$typesSel,true)?'checked':'' ?>> <?= e($c) ?></label>
          <?php endforeach; ?>
        </div>

        <div class="filter-group">
          <label class="f-label">Price Range (KSh)</label>
          <div class="filter-range">
            <input type="number" name="min" placeholder="Min" value="<?= $min!==null?e((string)(int)$min):'' ?>">
            <input type="number" name="max" placeholder="Max" value="<?= $max!==null?e((string)(int)$max):'' ?>">
          </div>
        </div>

        <div class="filter-group">
          <label class="f-label">Plot Size</label>
          <select class="filter-select" name="size">
            <?php foreach (['' => 'Any Size','1/8'=>'1/8 Acre','1/4'=>'1/4 Acre','1/2'=>'1/2 Acre','1 Acre'=>'1 Acre'] as $v=>$l): ?>
              <option value="<?= e($v) ?>" <?= $sizeSel===$v?'selected':'' ?>><?= e($l) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="filter-group">
          <label class="filter-check"><input type="checkbox" name="deed" value="1" <?= $deedOnly?'checked':'' ?>> Ready Title Deed only</label>
        </div>

        <button class="btn btn-green btn-sm" type="submit" style="width:100%">Apply Filters</button>
        <?php if ($locsSel||$typesSel||$min!==null||$max!==null||$sizeSel||$deedOnly): ?>
          <a href="land.php" class="btn btn-outline-dark btn-sm" style="width:100%;margin-top:8px;justify-content:center">Clear filters</a>
        <?php endif; ?>
      </aside>

      <div class="listing-content">
        <div class="results-bar">
          <p><strong><?= count($rows) ?></strong> Propert<?= count($rows)===1?'y':'ies' ?> Found</p>
          <select class="sort-select" name="sort" onchange="this.form.submit()">
            <?php foreach (['newest'=>'Newest','price_asc'=>'Price: Low to High','price_desc'=>'Price: High to Low'] as $v=>$l): ?>
              <option value="<?= $v ?>" <?= $sort===$v?'selected':'' ?>><?= $l ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <?php if ($rows): ?>
        <div class="land-grid">
          <?php foreach ($rows as $r): ?>
          <article class="land-card">
            <div class="land-media">
              <a href="land-detail.php?slug=<?= e($r['slug']) ?>"><img src="<?= $img($r['cover_image']) ?>" alt="<?= e($r['title']) ?>"></a>
              <span class="tag"><?= e(strtoupper($r['category'])) ?></span>
              <button class="save-btn" aria-label="Save" data-save-type="land" data-save-id="<?= (int)$r['id'] ?>"><svg viewBox="0 0 24 24"><path d="M17 3H7c-1.1 0-2 .9-2 2v16l7-3 7 3V5c0-1.1-.9-2-2-2z"/></svg></button>
            </div>
            <a href="land-detail.php?slug=<?= e($r['slug']) ?>" class="land-body">
              <p class="land-loc"><?= e($r['location']) ?></p>
              <h3><?= e($r['title']) ?></h3>
              <p class="land-price"><?= e(ksh($r['price'])) ?></p>
              <p class="land-deed"><svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm-1 14-4-4 1.4-1.4L11 13.2l5.6-5.6L18 9l-7 7z"/></svg> <?= e($r['title_status'] ?: 'Ready Title Deed') ?></p>
            </a>
          </article>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-listing" style="text-align:center;padding:60px 20px;color:var(--muted)">
          <p style="font-size:18px;margin-bottom:8px">No plots match your filters.</p>
          <p>Try widening your search, or <a href="land.php" style="color:var(--green)">clear the filters</a> to see everything.</p>
        </div>
        <?php endif; ?>
      </div>
    </form>
  </div>
</section>
<?php require __DIR__ . '/app/partials/footer.php'; ?>
