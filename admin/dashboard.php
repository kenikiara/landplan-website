<?php
require_once __DIR__ . '/_guard.php';
$title = 'Dashboard'; $active = 'dash';
$pdo = db();

function count_rows(PDO $pdo, string $sql): int { try { return (int)$pdo->query($sql)->fetchColumn(); } catch (Throwable $e) { return 0; } }

$k = [
  'leads_new'   => count_rows($pdo, "SELECT COUNT(*) FROM leads WHERE status='new'"),
  'leads_total' => count_rows($pdo, "SELECT COUNT(*) FROM leads"),
  'land'        => count_rows($pdo, "SELECT COUNT(*) FROM land_listings WHERE status='published'"),
  'houses'      => count_rows($pdo, "SELECT COUNT(*) FROM houses WHERE status='published'"),
  'projects'    => count_rows($pdo, "SELECT COUNT(*) FROM projects"),
  'articles'    => count_rows($pdo, "SELECT COUNT(*) FROM articles WHERE status='published'"),
  'clients'     => count_rows($pdo, "SELECT COUNT(*) FROM clients"),
  'appts'       => count_rows($pdo, "SELECT COUNT(*) FROM appointments WHERE status='scheduled'"),
];

$recentLeads = [];
try {
    $recentLeads = $pdo->query("SELECT id,name,phone,interest,status,created_at FROM leads ORDER BY created_at DESC LIMIT 6")->fetchAll();
} catch (Throwable $e) {}

$recentActivity = [];
try {
    $recentActivity = $pdo->query(
      "SELECT a.action,a.entity,a.detail,a.created_at,u.name AS who
       FROM activity_log a LEFT JOIN users u ON u.id=a.user_id
       ORDER BY a.created_at DESC LIMIT 8")->fetchAll();
} catch (Throwable $e) {}

require __DIR__ . '/_head.php';
?>
<div class="page-head">
  <div>
    <h2>Welcome back, <?= e(explode(' ', $ADMIN['name'])[0]) ?> 👋</h2>
    <p class="sub"><?= date('l, j F Y') ?> · Here's what's happening on your site.</p>
  </div>
  <div class="spacer"></div>
  <a class="btn btn-primary" href="land-form.php"><svg viewBox="0 0 24 24"><path d="M11 5h2v6h6v2h-6v6h-2v-6H5v-2h6V5z"/></svg> Add Land</a>
  <a class="btn btn-light" href="articles.php"><svg viewBox="0 0 24 24"><path d="M4 4h16v2H4V4zm0 5h16v2H4V9zm0 5h10v2H4v-2z"/></svg> New Article</a>
</div>

<div class="kpi-grid">
  <div class="kpi green">
    <span class="ico"><svg viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z"/></svg></span>
    <div class="num"><?= $k['leads_new'] ?></div>
    <div class="lbl">New enquiries<?= $k['leads_new'] ? ' · <a href="leads.php" style="color:var(--green);font-weight:600">review</a>' : '' ?></div>
  </div>
  <div class="kpi blue">
    <span class="ico"><svg viewBox="0 0 24 24"><path d="M3 5v14h18V5H3zm16 12H5V7h14v10z"/></svg></span>
    <div class="num"><?= $k['land'] ?></div>
    <div class="lbl">Land listings live</div>
  </div>
  <div class="kpi amber">
    <span class="ico"><svg viewBox="0 0 24 24"><path d="M12 3 2 12h3v8h6v-6h2v6h6v-8h3L12 3z"/></svg></span>
    <div class="num"><?= $k['houses'] ?></div>
    <div class="lbl">Houses live</div>
  </div>
  <div class="kpi violet">
    <span class="ico"><svg viewBox="0 0 24 24"><path d="M16 11c1.7 0 3-1.3 3-3s-1.3-3-3-3-3 1.3-3 3 1.3 3 3 3zm-8 0c1.7 0 3-1.3 3-3S9.7 5 8 5 5 6.3 5 8s1.3 3 3 3zm0 2c-2.3 0-7 1.2-7 3.5V19h14v-2.5C15 14.2 10.3 13 8 13z"/></svg></span>
    <div class="num"><?= $k['clients'] ?></div>
    <div class="lbl">Registered clients</div>
  </div>
</div>

<div class="split-2">
  <div class="card">
    <div class="card-head">
      <h3>Recent enquiries</h3><div class="spacer"></div>
      <a class="btn btn-light btn-sm" href="leads.php">View all</a>
    </div>
    <?php if ($recentLeads): ?>
    <div class="table-wrap">
      <table class="data">
        <thead><tr><th>Name</th><th>Phone</th><th>Interest</th><th>Status</th><th class="right">When</th></tr></thead>
        <tbody>
        <?php foreach ($recentLeads as $l): ?>
          <tr onclick="location='lead-view.php?id=<?= (int)$l['id'] ?>'" style="cursor:pointer">
            <td class="t-title"><?= e($l['name']) ?></td>
            <td class="nowrap"><?= e($l['phone']) ?></td>
            <td><?= e($l['interest'] ?: '-') ?></td>
            <td><span class="badge <?= e($l['status']) ?>"><?= e(ucfirst($l['status'])) ?></span></td>
            <td class="right nowrap muted mini"><?= e(time_ago($l['created_at'])) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
      <div class="empty">
        <svg viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z"/></svg>
        <p>No enquiries yet. They'll appear here when visitors use your contact forms.</p>
      </div>
    <?php endif; ?>
  </div>

  <div class="stack">
    <div class="card card-pad">
      <h3 style="font-size:14px;margin-bottom:14px">At a glance</h3>
      <div class="stack mini">
        <div style="display:flex;justify-content:space-between"><span class="muted">Total enquiries</span><b><?= $k['leads_total'] ?></b></div>
        <div style="display:flex;justify-content:space-between"><span class="muted">Blog articles</span><b><?= $k['articles'] ?></b></div>
        <div style="display:flex;justify-content:space-between"><span class="muted">Projects</span><b><?= $k['projects'] ?></b></div>
        <div style="display:flex;justify-content:space-between"><span class="muted">Upcoming visits</span><b><?= $k['appts'] ?></b></div>
      </div>
    </div>
    <div class="card card-pad">
      <h3 style="font-size:14px;margin-bottom:12px">Quick actions</h3>
      <div class="stack">
        <a class="btn btn-light btn-block" href="houses-form.php">+ Add a house</a>
        <a class="btn btn-light btn-block" href="project-form.php">+ Add a project</a>
        <a class="btn btn-light btn-block" href="settings.php">Site settings</a>
      </div>
    </div>
  </div>
</div>

<?php if ($recentActivity): ?>
<div class="card" style="margin-top:20px">
  <div class="card-head"><h3>Recent activity</h3></div>
  <div class="table-wrap">
    <table class="data">
      <tbody>
      <?php foreach ($recentActivity as $a): ?>
        <tr>
          <td class="nowrap muted mini" style="width:140px"><?= e(time_ago($a['created_at'])) ?></td>
          <td><b><?= e($a['who'] ?: 'System') ?></b> <?= e($a['action']) ?> <?= e($a['entity']) ?><?= $a['detail'] ? ', ' . e($a['detail']) : '' ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/_foot.php'; ?>
