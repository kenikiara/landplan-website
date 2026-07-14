<?php
/**
 * Admin layout — top half. Set $title and $active before including.
 * Requires $ADMIN (from _guard.php).
 */
$title  = $title  ?? 'Dashboard';
$active = $active ?? '';
$ADMIN  = $ADMIN  ?? admin_user();
$newLeads = new_leads_count();

/** Sidebar link helper. */
function nl(string $href, string $key, string $label, string $svg, string $activeKey, ?int $badge = null): void
{
    $cls = 'nav-link' . ($key === $activeKey ? ' active' : '');
    echo '<a class="' . $cls . '" href="' . e($href) . '">' . $svg . '<span>' . e($label) . '</span>';
    if ($badge) echo '<span class="nav-badge">' . $badge . '</span>';
    echo '</a>';
}

// Inline icon set
$I = [
  'dash'   => '<svg viewBox="0 0 24 24"><path d="M3 3h8v8H3V3zm10 0h8v5h-8V3zM3 13h8v8H3v-8zm10 3h8v5h-8v-5z"/></svg>',
  'land'   => '<svg viewBox="0 0 24 24"><path d="M3 5v14h18V5H3zm16 12H5V7h14v10zM7 9h4v2H7V9zm6 0h4v6h-4V9zm-6 4h4v2H7v-2z"/></svg>',
  'house'  => '<svg viewBox="0 0 24 24"><path d="M12 3 2 12h3v8h6v-6h2v6h6v-8h3L12 3z"/></svg>',
  'proj'   => '<svg viewBox="0 0 24 24"><path d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10zm-2-8h-2v2h2v-2zm0 4h-2v2h2v-2z"/></svg>',
  'blog'   => '<svg viewBox="0 0 24 24"><path d="M4 4h16v2H4V4zm0 5h16v2H4V9zm0 5h10v2H4v-2zm0 5h10v2H4v-2z"/></svg>',
  'svc'    => '<svg viewBox="0 0 24 24"><path d="m12 2 2.4 4.9 5.4.8-3.9 3.8.9 5.4L12 14.9 7.2 17l.9-5.4L4.2 7.7l5.4-.8L12 2z"/></svg>',
  'quote'  => '<svg viewBox="0 0 24 24"><path d="M7 7h5v6H9l-2 4V7zm7 0h5v6h-3l-2 4V7z"/></svg>',
  'faq'    => '<svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm1 15h-2v-2h2v2zm1.1-6.3-.9.9c-.6.6-.9 1.1-.9 2.4h-2v-.5c0-1 .4-1.8 1-2.4l1.2-1.2c.4-.4.5-1 .3-1.6-.2-.5-.7-.9-1.4-.9-.9 0-1.5.6-1.5 1.5H8.5C8.5 8.5 9.9 7 12 7c1.9 0 3.3 1.2 3.3 3 0 .7-.3 1.4-1.2 2.4z"/></svg>',
  'page'   => '<svg viewBox="0 0 24 24"><path d="M6 2h9l5 5v15H6V2zm8 1.5V8h4.5L14 3.5z"/></svg>',
  'leads'  => '<svg viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z"/></svg>',
  'client' => '<svg viewBox="0 0 24 24"><path d="M16 11c1.7 0 3-1.3 3-3s-1.3-3-3-3-3 1.3-3 3 1.3 3 3 3zm-8 0c1.7 0 3-1.3 3-3S9.7 5 8 5 5 6.3 5 8s1.3 3 3 3zm0 2c-2.3 0-7 1.2-7 3.5V19h14v-2.5C15 14.2 10.3 13 8 13zm8 0c-.3 0-.6 0-1 .1 1.2.8 2 2 2 3.4V19h6v-2.5c0-2.3-4.7-3.5-7-3.5z"/></svg>',
  'appt'   => '<svg viewBox="0 0 24 24"><path d="M19 4h-1V2h-2v2H8V2H6v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 16H5V10h14v10zM5 8V6h14v2H5z"/></svg>',
  'set'    => '<svg viewBox="0 0 24 24"><path d="M19.4 13a7.8 7.8 0 0 0 0-2l2-1.6-2-3.4-2.4 1a7.4 7.4 0 0 0-1.7-1l-.4-2.6H10l-.4 2.6c-.6.2-1.2.6-1.7 1l-2.4-1-2 3.4L3.6 11a7.8 7.8 0 0 0 0 2l-2 1.6 2 3.4 2.4-1c.5.4 1.1.8 1.7 1l.4 2.6h4l.4-2.6c.6-.2 1.2-.6 1.7-1l2.4 1 2-3.4-2-1.6zM12 15.5A3.5 3.5 0 1 1 12 8.5a3.5 3.5 0 0 1 0 7z"/></svg>',
  'user'   => '<svg viewBox="0 0 24 24"><path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10zm0 2c-4 0-9 2-9 6v2h18v-2c0-4-5-6-9-6z"/></svg>',
  'help'   => '<svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm1 15h-2v-2h2v2zm1.1-6.3-.9.9c-.6.6-.9 1.1-.9 2.4h-2v-.5c0-1 .4-1.8 1-2.4l1.2-1.2c.4-.4.5-1 .3-1.6-.2-.5-.7-.9-1.4-.9-.9 0-1.5.6-1.5 1.5H8.5C8.5 8.5 9.9 7 12 7c1.9 0 3.3 1.2 3.3 3 0 .7-.3 1.4-1.2 2.4z"/></svg>',
  'ext'    => '<svg viewBox="0 0 24 24"><path d="M14 3v2h3.6l-9.8 9.8 1.4 1.4L19 6.4V10h2V3h-7zM5 5h5V3H3v18h18v-7h-2v5H5V5z"/></svg>',
  'out'    => '<svg viewBox="0 0 24 24"><path d="M16 13v-2H7V8l-5 4 5 4v-3h9zM20 3h-8v2h8v14h-8v2h8a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z"/></svg>',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<title><?= e($title) ?> — Landplan Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<div class="admin">
  <div class="sidebar-backdrop" onclick="document.querySelector('.sidebar').classList.remove('open');this.classList.remove('show')"></div>
  <aside class="sidebar">
    <div class="sidebar-brand">
      <span class="mark"><svg viewBox="0 0 24 24"><path d="M12 2 4 5v6c0 5 3.4 9.7 8 11 4.6-1.3 8-6 8-11V5l-8-3zm0 4 4.5 3.4v5.1L12 17.9l-4.5-3.4V9.4L12 6z"/></svg></span>
      <span><b>LANDPLAN</b><small>ADMIN</small></span>
    </div>

    <div class="nav-group">
      <?php nl('dashboard.php','dash','Dashboard',$I['dash'],$active); ?>
    </div>

    <div class="nav-group">
      <div class="nav-group-label">Catalog</div>
      <?php
        nl('land.php','land','Land for Sale',$I['land'],$active);
        nl('houses.php','houses','Houses',$I['house'],$active);
        nl('projects.php','projects','Projects',$I['proj'],$active);
      ?>
    </div>

    <div class="nav-group">
      <div class="nav-group-label">Content</div>
      <?php
        nl('articles.php','articles','Blog Articles',$I['blog'],$active);
        nl('services.php','services','Services',$I['svc'],$active);
        nl('testimonials.php','testimonials','Testimonials',$I['quote'],$active);
        nl('faqs.php','faqs','FAQs',$I['faq'],$active);
        nl('pages.php','pages','Pages',$I['page'],$active);
      ?>
    </div>

    <div class="nav-group">
      <div class="nav-group-label">CRM</div>
      <?php
        nl('leads.php','leads','Leads',$I['leads'],$active,$newLeads ?: null);
        nl('clients.php','clients','Clients',$I['client'],$active);
        nl('appointments.php','appointments','Appointments',$I['appt'],$active);
      ?>
    </div>

    <div class="nav-group">
      <div class="nav-group-label">System</div>
      <?php
        nl('settings.php','settings','Settings',$I['set'],$active);
        if (($ADMIN['role'] ?? '') === 'admin') nl('users.php','users','Staff Users',$I['user'],$active);
        nl('help.php','help','Help & SOP',$I['help'],$active);
      ?>
      <a class="nav-link" href="../index.html" target="_blank"><?= $I['ext'] ?><span>View Website</span></a>
    </div>

    <div class="sidebar-foot">
      Signed in as<br><b style="color:#e6ece5"><?= e($ADMIN['name'] ?? '') ?></b>
    </div>
  </aside>

  <div class="main">
    <div class="topbar">
      <button class="hamburger" aria-label="Menu"
        onclick="document.querySelector('.sidebar').classList.toggle('open');document.querySelector('.sidebar-backdrop').classList.toggle('show')">
        <svg viewBox="0 0 24 24" width="22" height="22"><path d="M3 6h18M3 12h18M3 18h18" stroke="currentColor" stroke-width="2" fill="none"/></svg>
      </button>
      <h1><?= e($title) ?></h1>
      <div class="topbar-spacer"></div>
      <div class="topbar-user">
        <span class="nowrap muted mini"><?= e($ADMIN['email'] ?? '') ?></span>
        <span class="avatar"><?= e(strtoupper(substr($ADMIN['name'] ?? 'A', 0, 1))) ?></span>
        <a class="btn btn-light btn-sm" href="logout.php"><?= $I['out'] ?> Logout</a>
      </div>
    </div>
    <div class="content">
      <?php foreach (take_flashes() as $f): ?>
        <div class="flash <?= e($f['type']) ?>"><?= e($f['msg']) ?></div>
      <?php endforeach; ?>
