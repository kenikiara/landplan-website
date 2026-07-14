<?php
require_once __DIR__ . '/../app/auth.php';
boot_session();
$CLIENT = require_client();
$pdo = db();

$stmt = $pdo->prepare('SELECT title, path, created_at FROM client_documents WHERE client_id=? ORDER BY created_at DESC');
$stmt->execute([$CLIENT['id']]);
$docs = $stmt->fetchAll();

$title='Documents'; $active='documents';
require __DIR__ . '/_head.php';
?>
<h1 class="page-title">My documents</h1>
<p class="page-sub">Title deeds, agreements and other files shared with you by Landplan.</p>

<div class="card">
  <?php if ($docs): ?>
  <table class="data">
    <thead><tr><th>Document</th><th>Shared</th><th class="right">Download</th></tr></thead>
    <tbody>
    <?php foreach ($docs as $d): ?>
      <tr>
        <td><b><?= e($d['title']) ?></b></td>
        <td class="mini muted nowrap"><?= e(nice_date($d['created_at'])) ?></td>
        <td class="right"><a class="btn btn-light btn-sm" href="../<?= e(ltrim($d['path'],'/')) ?>" target="_blank" download>Download</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
  <div class="empty" style="padding:44px">
    <svg viewBox="0 0 24 24" width="44" style="opacity:.35;margin-bottom:8px"><path d="M6 2h9l5 5v15H6V2zm8 1.5V8h4.5L14 3.5z"/></svg>
    <p>No documents have been shared with you yet.<br>When Landplan uploads a document to your account, it will appear here.</p>
  </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/_foot.php'; ?>
