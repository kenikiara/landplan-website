<?php
require_once __DIR__ . '/../app/auth.php';
boot_session();
$CLIENT = require_client();
$pdo = db();

$savedCount = (int)(function() use ($pdo,$CLIENT){ $s=$pdo->prepare('SELECT COUNT(*) FROM saved_listings WHERE client_id=?'); $s->execute([$CLIENT['id']]); return $s->fetchColumn(); })();
$enqCount   = (int)(function() use ($pdo,$CLIENT){ $s=$pdo->prepare('SELECT COUNT(*) FROM leads WHERE client_id=?'); $s->execute([$CLIENT['id']]); return $s->fetchColumn(); })();
$docCount   = (int)(function() use ($pdo,$CLIENT){ $s=$pdo->prepare('SELECT COUNT(*) FROM client_documents WHERE client_id=?'); $s->execute([$CLIENT['id']]); return $s->fetchColumn(); })();
$saved = array_slice(client_saved_items((int)$CLIENT['id']), 0, 3);

$title='Dashboard'; $active='dash';
require __DIR__ . '/_head.php';
?>
<h1 class="page-title">Welcome back, <?= e(explode(' ',$CLIENT['name'])[0]) ?> 👋</h1>
<p class="page-sub">Manage your saved properties, enquiries and documents in one place.</p>

<div class="grid grid-3" style="margin-bottom:24px">
  <a class="stat" href="saved.php">
    <div class="ico"><svg viewBox="0 0 24 24" width="20"><path d="M17 3H7c-1.1 0-2 .9-2 2v16l7-3 7 3V5c0-1.1-.9-2-2-2z"/></svg></div>
    <div class="num"><?= $savedCount ?></div><div class="lbl">Saved properties</div>
  </a>
  <a class="stat" href="enquiries.php">
    <div class="ico"><svg viewBox="0 0 24 24" width="20"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z"/></svg></div>
    <div class="num"><?= $enqCount ?></div><div class="lbl">My enquiries</div>
  </a>
  <a class="stat" href="documents.php">
    <div class="ico"><svg viewBox="0 0 24 24" width="20"><path d="M6 2h9l5 5v15H6V2zm8 1.5V8h4.5L14 3.5z"/></svg></div>
    <div class="num"><?= $docCount ?></div><div class="lbl">Documents</div>
  </a>
</div>

<div class="card card-pad">
  <div style="display:flex;align-items:center;margin-bottom:16px"><h3 style="font-size:16px">Recently saved</h3><div style="flex:1"></div><a class="btn btn-light btn-sm" href="saved.php">View all</a></div>
  <?php if ($saved): ?>
    <div class="props">
      <?php foreach ($saved as $it): ?>
        <a class="prop" href="<?= e($it['url']) ?>" target="_blank">
          <img src="<?= $it['image']?e('../'.ltrim($it['image'],'/')):'' ?>" alt="">
          <div class="b"><span class="badge <?= e($it['type']) ?>"><?= e(ucfirst($it['type'])) ?></span>
            <h4 style="margin-top:8px"><?= e($it['title']) ?></h4>
            <?php if ($it['price']!==null): ?><div class="price"><?= e(ksh($it['price'])) ?></div><?php endif; ?>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="empty"><p>You haven't saved any properties yet.</p><a class="btn btn-primary btn-sm" href="../land.html" style="margin-top:12px">Browse land</a></div>
  <?php endif; ?>
</div>

<div class="card card-pad" style="margin-top:18px;background:var(--deep);color:#fff;border:none">
  <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap">
    <div style="flex:1;min-width:220px"><h3 style="font-size:17px">Need help or ready to buy?</h3><p style="color:#c4ccc2;margin-top:4px">Talk to our team — we're here to help you every step of the way.</p></div>
    <a class="btn btn-primary" href="../contact.html">Contact us</a>
  </div>
</div>
<?php require __DIR__ . '/_foot.php'; ?>
