<?php
/** Client portal layout — top. Requires $CLIENT and set $title,$active. */
require_once __DIR__ . '/../app/auth.php';
date_default_timezone_set((string)(config('site.timezone') ?: 'Africa/Nairobi'));
boot_session();
$CLIENT = $CLIENT ?? require_client();
$title  = $title  ?? 'My Account';
$active = $active ?? '';
function pnav(string $href,string $key,string $label,string $active){ echo '<a href="'.e($href).'"'.($key===$active?' class="active"':'').'>'.e($label).'</a>'; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<title><?= e($title) ?> — Landplan</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/client.css">
</head>
<body>
<header class="portal-header">
  <div class="bar">
    <a href="../index.html" class="brand"><span class="mark"><svg viewBox="0 0 24 24" width="17" height="17"><path d="M12 2 4 5v6c0 5 3.4 9.7 8 11 4.6-1.3 8-6 8-11V5l-8-3zm0 4 4.5 3.4v5.1L12 17.9l-4.5-3.4V9.4L12 6z"/></svg></span><span>LANDPLAN<small>MY ACCOUNT</small></span></a>
    <nav class="portal-nav">
      <?php
        pnav('dashboard.php','dash','Dashboard',$active);
        pnav('saved.php','saved','Saved',$active);
        pnav('enquiries.php','enquiries','My Enquiries',$active);
        pnav('documents.php','documents','Documents',$active);
        pnav('profile.php','profile','Profile',$active);
      ?>
    </nav>
    <div class="spacer"></div>
    <div class="who"><span class="name">Hi, <?= e(explode(' ',$CLIENT['name'])[0]) ?></span><a href="logout.php">Log out</a></div>
  </div>
</header>
<div class="wrap">
<?php foreach (take_flashes() as $f): ?><div class="flash <?= e($f['type']) ?>"><?= e($f['msg']) ?></div><?php endforeach; ?>
