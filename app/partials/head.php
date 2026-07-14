<?php
/**
 * Public page head + topbar + nav. Include from server-rendered PHP pages.
 * Set these before including:
 *   $page_title, $page_desc, $active (nav key), $og_image (optional)
 */
require_once __DIR__ . '/../helpers.php';

$s        = settings();
$phone    = $s['contact_phone']    ?? '+254 705 121 788';
$phoneRaw = preg_replace('/[^0-9+]/', '', $phone);
$email    = $s['contact_email']    ?? 'info@landplan.co.ke';
$location = $s['contact_location'] ?? 'Nairobi, Kenya';
$fb       = $s['social_facebook']  ?? '#';
$ig       = $s['social_instagram'] ?? '#';
$li       = $s['social_linkedin']  ?? '#';
$wa       = $s['social_whatsapp']  ?? ('https://wa.me/' . ltrim($phoneRaw, '+'));

$title  = $page_title ?? 'Landplan.co.ke, Land & Property Solutions';
$desc   = $page_desc  ?? 'We sell land, design dream spaces, build quality homes and develop projects that last generations.';
$active = $active ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:type" content="website">
<?php if (!empty($og_image)): ?><meta property="og:image" content="<?= e($og_image) ?>"><?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- ============ TOP BAR ============ -->
<div class="topbar">
  <div class="container topbar-inner">
    <div class="topbar-contacts">
      <a href="tel:<?= e($phoneRaw) ?>" class="topbar-item">
        <svg viewBox="0 0 24 24"><path d="M6.6 10.8c1.4 2.8 3.8 5.1 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1C10.6 21 3 13.4 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.2.2 2.4.6 3.6.1.3 0 .7-.2 1l-2.3 2.2z"/></svg>
        <?= e($phone) ?>
      </a>
      <a href="mailto:<?= e($email) ?>" class="topbar-item">
        <svg viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z"/></svg>
        <?= e($email) ?>
      </a>
      <span class="topbar-item">
        <svg viewBox="0 0 24 24"><path d="M12 2C8.1 2 5 5.1 5 9c0 5.2 7 13 7 13s7-7.8 7-13c0-3.9-3.1-7-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"/></svg>
        <?= e($location) ?>
      </span>
    </div>
    <div class="topbar-socials">
      <a href="<?= e($fb) ?>" aria-label="Facebook"><svg viewBox="0 0 24 24"><path d="M13.5 9H16l-.5 3h-2v9h-3v-9H8V9h2.5V7.1C10.5 4.9 11.8 3.5 14 3.5c.9 0 1.8.1 2 .1v2.6h-1.3c-1 0-1.2.5-1.2 1.2V9z"/></svg></a>
      <a href="<?= e($ig) ?>" aria-label="Instagram"><svg viewBox="0 0 24 24"><path d="M12 8.8A3.2 3.2 0 1 0 12 15.2 3.2 3.2 0 0 0 12 8.8zM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10zm5.4-.2a1.2 1.2 0 1 1-2.4 0 1.2 1.2 0 0 1 2.4 0zM12 4.6c-2 0-2.3 0-3.1.1-.8 0-1.3.2-1.6.3-.4.2-.7.4-1 .7-.3.3-.5.6-.7 1-.1.3-.3.8-.3 1.6C5.2 9 5.2 9.3 5.2 12s0 3 .1 3.7c0 .8.2 1.3.3 1.6.2.4.4.7.7 1 .3.3.6.5 1 .7.3.1.8.3 1.6.3.8.1 1.1.1 3.1.1s2.3 0 3.1-.1c.8 0 1.3-.2 1.6-.3.4-.2.7-.4 1-.7.3-.3.5-.6.7-1 .1-.3.3-.8.3-1.6.1-.8.1-1.1.1-3.7s0-3-.1-3.7c0-.8-.2-1.3-.3-1.6-.2-.4-.4-.7-.7-1-.3-.3-.6-.5-1-.7-.3-.1-.8-.3-1.6-.3C14.3 4.6 14 4.6 12 4.6z"/></svg></a>
      <a href="<?= e($li) ?>" aria-label="LinkedIn"><svg viewBox="0 0 24 24"><path d="M6.5 8.5H3.8V20h2.7V8.5zM5.1 7.3a1.6 1.6 0 1 0 0-3.3 1.6 1.6 0 0 0 0 3.3zM20.2 13.7c0-3-1.6-4.4-3.8-4.4-1.7 0-2.5 1-2.9 1.6V8.5H10.8V20h2.7v-6.2c0-1.2.2-2.4 1.7-2.4s1.6 1.4 1.6 2.5V20h2.7l.7-6.3z"/></svg></a>
    </div>
  </div>
</div>

<!-- ============ HEADER / NAV ============ -->
<header class="header">
  <div class="container header-inner">
    <a href="index.html" class="brand">
      <span class="brand-mark">
        <svg viewBox="0 0 24 24"><path d="M12 2 4 5v6c0 5 3.4 9.7 8 11 4.6-1.3 8-6 8-11V5l-8-3zm0 4 4.5 3.4v5.1L12 17.9l-4.5-3.4V9.4L12 6z"/></svg>
      </span>
      <span class="brand-text">LANDPLAN<small>.CO.KE</small></span>
    </a>
    <nav class="nav">
      <a href="index.html"<?= $active === 'home' ? ' class="active"' : '' ?>>Home</a>
      <a href="land.html"<?= $active === 'land' ? ' class="active"' : '' ?>>Land for Sale</a>
      <a href="houses.html"<?= $active === 'houses' ? ' class="active"' : '' ?>>Houses for Sale</a>
      <div class="nav-item">
        <a href="services.html" class="dropdown-toggle">Services</a>
        <div class="dropdown-menu">
          <a href="service-architecture.html">Architecture &amp; Design</a>
          <a href="service-construction.html">Building &amp; Construction</a>
          <a href="service-project-development.html">Project Development</a>
          <a href="service-due-diligence.html">Due Diligence &amp; Property Management</a>
        </div>
      </div>
      <div class="nav-item">
        <a href="projects.html" class="dropdown-toggle">Projects</a>
        <div class="dropdown-menu">
          <a href="projects.html">All Projects</a>
          <a href="projects.html#ongoing">Ongoing Projects</a>
          <a href="projects.html#completed">Completed Projects</a>
        </div>
      </div>
      <a href="about.html"<?= $active === 'about' ? ' class="active"' : '' ?>>About Us</a>
      <div class="nav-item">
        <a href="blog.html" class="dropdown-toggle">Resources</a>
        <div class="dropdown-menu">
          <a href="blog.html">Blog</a>
          <a href="blog-land-buying-guide.html">Land Buying Guide</a>
          <a href="blog-building-guide.html">Building Guide</a>
          <a href="faqs.html">FAQs</a>
        </div>
      </div>
      <a href="contact.html"<?= $active === 'contact' ? ' class="active"' : '' ?>>Contact Us</a>
    </nav>
    <a href="client/" class="btn btn-outline-dark btn-sm">My Account</a>
    <a href="contact.html" class="btn btn-green btn-sm">Get Started</a>
    <button class="nav-toggle" aria-label="Menu" onclick="document.querySelector('.nav').classList.toggle('open')">
      <span></span><span></span><span></span>
    </button>
  </div>
</header>
