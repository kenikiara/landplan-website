<?php
require_once __DIR__ . '/app/helpers.php';
$pdo = db();

$locsSel = array_values(array_filter((array)($_GET['loc'] ?? []), 'strlen'));
$bedsMin = get('beds');
$min = isset($_GET['min']) && $_GET['min'] !== '' ? (float)$_GET['min'] : null;
$max = isset($_GET['max']) && $_GET['max'] !== '' ? (float)$_GET['max'] : null;
$sort = get('sort', 'newest');

$where = ["status='published'"]; $args = [];
if ($locsSel) {
    $ors = []; foreach ($locsSel as $l) { $ors[] = 'location LIKE ?'; $args[] = '%' . $l . '%'; }
    $where[] = '(' . implode(' OR ', $ors) . ')';
}
if ($bedsMin !== '') { $where[] = 'bedrooms >= ?'; $args[] = (int)$bedsMin; }
if ($min !== null) { $where[] = 'price >= ?'; $args[] = $min; }
if ($max !== null) { $where[] = 'price <= ?'; $args[] = $max; }

$order = 'featured DESC, created_at DESC';
if ($sort === 'price_asc')  $order = 'price ASC';
if ($sort === 'price_desc') $order = 'price DESC';

$stmt = $pdo->prepare('SELECT * FROM houses WHERE ' . implode(' AND ', $where) . " ORDER BY $order");
$stmt->execute($args);
$rows = $stmt->fetchAll();

$allLocs = [];
foreach ($pdo->query("SELECT DISTINCT location FROM houses WHERE status='published'") as $r) {
    $town = trim(explode(',', $r['location'])[0]); if ($town !== '') $allLocs[$town] = true;
}
$allLocs = array_keys($allLocs); sort($allLocs);

$page_title = 'Houses for Sale in Kenya | Landplan.co.ke';
$page_desc  = 'Move-in ready homes and off-plan houses in Kenya\'s fastest-growing residential neighbourhoods.';
$active = 'houses';
require __DIR__ . '/app/partials/head.php';
$img = fn($p) => e(ltrim((string)$p, '/'));
?>
<section class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="index.html">Home</a><span class="sep">/</span><span class="current">Houses for Sale</span></div>
    <h1>Houses for Sale</h1>
    <p class="lead">Move-in ready homes and off-plan houses in Kenya's fastest-growing residential neighbourhoods.</p>
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
          <label class="f-label">Bedrooms (minimum)</label>
          <select class="filter-select" name="beds">
            <?php foreach (['' => 'Any','1'=>'1+','2'=>'2+','3'=>'3+','4'=>'4+','5'=>'5+'] as $v=>$l): ?>
              <option value="<?= $v ?>" <?= $bedsMin===$v?'selected':'' ?>><?= $l ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="filter-group">
          <label class="f-label">Price Range (KSh)</label>
          <div class="filter-range">
            <input type="number" name="min" placeholder="Min" value="<?= $min!==null?e((string)(int)$min):'' ?>">
            <input type="number" name="max" placeholder="Max" value="<?= $max!==null?e((string)(int)$max):'' ?>">
          </div>
        </div>
        <button class="btn btn-green btn-sm" type="submit" style="width:100%">Apply Filters</button>
        <?php if ($locsSel||$bedsMin!==''||$min!==null||$max!==null): ?>
          <a href="houses.php" class="btn btn-outline-dark btn-sm" style="width:100%;margin-top:8px;justify-content:center">Clear filters</a>
        <?php endif; ?>
      </aside>

      <div class="listing-content">
        <div class="results-bar">
          <p><strong><?= count($rows) ?></strong> Home<?= count($rows)===1?'':'s' ?> Found</p>
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
              <a href="house-detail.php?slug=<?= e($r['slug']) ?>"><img src="<?= $img($r['cover_image']) ?>" alt="<?= e($r['title']) ?>"></a>
              <span class="tag">HOUSE</span>
              <button class="save-btn" aria-label="Save" data-save-type="house" data-save-id="<?= (int)$r['id'] ?>"><svg viewBox="0 0 24 24"><path d="M17 3H7c-1.1 0-2 .9-2 2v16l7-3 7 3V5c0-1.1-.9-2-2-2z"/></svg></button>
            </div>
            <a href="house-detail.php?slug=<?= e($r['slug']) ?>" class="land-body">
              <p class="land-loc"><?= e($r['location']) ?></p>
              <h3><?= e($r['title']) ?></h3>
              <p class="land-price"><?= e(ksh($r['price'])) ?></p>
              <p class="land-deed"><svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm-1 14-4-4 1.4-1.4L11 13.2l5.6-5.6L18 9l-7 7z"/></svg> <?= (int)$r['bedrooms'] ?> bed &middot; <?= (int)$r['bathrooms'] ?> bath</p>
            </a>
          </article>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-listing" style="text-align:center;padding:60px 20px;color:var(--muted)">
          <p style="font-size:18px;margin-bottom:8px">No homes match your filters.</p>
          <p><a href="houses.php" style="color:var(--green)">Clear the filters</a> to see all available homes.</p>
        </div>
        <?php endif; ?>
      </div>
    </form>
  </div>
</section>
<?php require __DIR__ . '/app/partials/footer.php'; ?>
