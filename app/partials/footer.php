<?php
/** Public footer + closing scripts. Mirrors the static footer, driven by settings. */
$s        = settings();
$phone    = $s['contact_phone']    ?? '+254 705 121 788';
$phoneRaw = preg_replace('/[^0-9+]/', '', $phone);
$email    = $s['contact_email']    ?? 'info@landplan.co.ke';
$location = $s['contact_location'] ?? 'Nairobi, Kenya';
$fb       = $s['social_facebook']  ?? '#';
$ig       = $s['social_instagram'] ?? '#';
$li       = $s['social_linkedin']  ?? '#';
$wa       = $s['social_whatsapp']  ?? ('https://wa.me/' . ltrim($phoneRaw, '+'));
$year     = date('Y');
?>
<!-- ============ CTA ============ -->
<section class="cta reveal">
  <div class="container cta-inner">
    <div>
      <h2>Ready to Own Land or Build Your Dream?</h2>
      <p>Let's turn your vision into reality.</p>
    </div>
    <div class="cta-actions">
      <a href="contact.html" class="btn btn-green">Talk to Us <span class="arrow">&#8594;</span></a>
      <a href="<?= e($wa) ?>" class="btn btn-outline"><svg class="wa" viewBox="0 0 24 24"><path d="M12 2A10 10 0 0 0 3.5 17.3L2 22l4.8-1.4A10 10 0 1 0 12 2zm5.5 14.1c-.2.7-1.3 1.3-1.9 1.4-.5.1-1.1.1-1.8-.1-.4-.1-1-.3-1.7-.6-2.9-1.3-4.8-4.2-5-4.4-.1-.2-1.2-1.6-1.2-3s.7-2.1 1-2.4c.3-.3.6-.4.8-.4h.6c.2 0 .4 0 .7.5.2.6.8 2 .9 2.1.1.2.1.3 0 .5-.1.2-.1.3-.3.5l-.4.5c-.2.2-.3.3-.1.6.2.3.8 1.3 1.7 2.1 1.2 1.1 2.2 1.4 2.5 1.5.3.1.5.1.7-.1.2-.2.8-.9 1-1.2.2-.3.4-.3.7-.2.3.1 1.7.8 2 1 .3.2.5.2.6.4 0 .2 0 .8-.3 1.3z"/></svg> WhatsApp Us</a>
    </div>
  </div>
</section>

<!-- ============ FOOTER ============ -->
<footer class="footer">
  <div class="container footer-grid">
    <div class="footer-brand">
      <a href="index.html" class="brand">
        <span class="brand-mark">
          <svg viewBox="0 0 24 24"><path d="M12 2 4 5v6c0 5 3.4 9.7 8 11 4.6-1.3 8-6 8-11V5l-8-3zm0 4 4.5 3.4v5.1L12 17.9l-4.5-3.4V9.4L12 6z"/></svg>
        </span>
        <span class="brand-text">LANDPLAN<small>.CO.KE</small></span>
      </a>
      <p>Your one stop shop for land sales, architecture, construction, project development and property solutions in Kenya.</p>
      <div class="footer-socials">
        <a href="<?= e($fb) ?>" aria-label="Facebook"><svg viewBox="0 0 24 24"><path d="M13.5 9H16l-.5 3h-2v9h-3v-9H8V9h2.5V7.1C10.5 4.9 11.8 3.5 14 3.5c.9 0 1.8.1 2 .1v2.6h-1.3c-1 0-1.2.5-1.2 1.2V9z"/></svg></a>
        <a href="<?= e($ig) ?>" aria-label="Instagram"><svg viewBox="0 0 24 24"><path d="M12 8.8A3.2 3.2 0 1 0 12 15.2 3.2 3.2 0 0 0 12 8.8zM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10zm5.4-.2a1.2 1.2 0 1 1-2.4 0 1.2 1.2 0 0 1 2.4 0zM12 3c2.4 0 2.7 0 3.7.1 1 0 1.6.2 2.2.4.6.2 1.1.6 1.6 1.1.5.5.8 1 1.1 1.6.2.6.4 1.2.4 2.2.1 1 .1 1.3.1 3.7s0 2.7-.1 3.7c0 1-.2 1.6-.4 2.2-.2.6-.6 1.1-1.1 1.6-.5.5-1 .8-1.6 1.1-.6.2-1.2.4-2.2.4-1 .1-1.3.1-3.7.1s-2.7 0-3.7-.1c-1 0-1.6-.2-2.2-.4-.6-.2-1.1-.6-1.6-1.1-.5-.5-.8-1-1.1-1.6-.2-.6-.4-1.2-.4-2.2C3 14.7 3 14.4 3 12s0-2.7.1-3.7c0-1 .2-1.6.4-2.2.2-.6.6-1.1 1.1-1.6.5-.5 1-.8 1.6-1.1.6-.2 1.2-.4 2.2-.4C9.3 3 9.6 3 12 3z"/></svg></a>
        <a href="<?= e($li) ?>" aria-label="LinkedIn"><svg viewBox="0 0 24 24"><path d="M6.5 8.5H3.8V20h2.7V8.5zM5.1 7.3a1.6 1.6 0 1 0 0-3.3 1.6 1.6 0 0 0 0 3.3zM20.2 13.7c0-3-1.6-4.4-3.8-4.4-1.7 0-2.5 1-2.9 1.6V8.5H10.8V20h2.7v-6.2c0-1.2.2-2.4 1.7-2.4s1.6 1.4 1.6 2.5V20h2.7l.7-6.3z"/></svg></a>
        <a href="<?= e($wa) ?>" aria-label="WhatsApp"><svg viewBox="0 0 24 24"><path d="M12 2A10 10 0 0 0 3.5 17.3L2 22l4.8-1.4A10 10 0 1 0 12 2zm5.5 14.1c-.2.7-1.3 1.3-1.9 1.4-.5.1-1.1.1-1.8-.1-.4-.1-1-.3-1.7-.6-2.9-1.3-4.8-4.2-5-4.4-.1-.2-1.2-1.6-1.2-3s.7-2.1 1-2.4c.3-.3.6-.4.8-.4h.6c.2 0 .4 0 .7.5.2.6.8 2 .9 2.1.1.2.1.3 0 .5-.1.2-.1.3-.3.5l-.4.5c-.2.2-.3.3-.1.6.2.3.8 1.3 1.7 2.1 1.2 1.1 2.2 1.4 2.5 1.5.3.1.5.1.7-.1.2-.2.8-.9 1-1.2.2-.3.4-.3.7-.2.3.1 1.7.8 2 1 .3.2.5.2.6.4 0 .2 0 .8-.3 1.3z"/></svg></a>
      </div>
    </div>
    <div class="footer-col">
      <h4>Quick Links</h4>
      <a href="about.html">About Us</a>
      <a href="land.html">Land for Sale</a>
      <a href="houses.html">Houses for Sale</a>
      <a href="projects.html">Projects</a>
      <a href="contact.html">Contact Us</a>
    </div>
    <div class="footer-col">
      <h4>Services</h4>
      <a href="service-architecture.html">Architecture &amp; Design</a>
      <a href="service-construction.html">Building &amp; Construction</a>
      <a href="service-project-development.html">Project Development</a>
      <a href="service-due-diligence.html">Due Diligence</a>
      <a href="service-due-diligence.html">Property Management</a>
    </div>
    <div class="footer-col">
      <h4>Resources</h4>
      <a href="blog-land-buying-guide.html">Land Buying Guide</a>
      <a href="blog-building-guide.html">Building Guide</a>
      <a href="faqs.html">FAQs</a>
      <a href="blog.html">Blog</a>
    </div>
    <div class="footer-col footer-contact">
      <h4>Contact Us</h4>
      <a href="tel:<?= e($phoneRaw) ?>"><svg viewBox="0 0 24 24"><path d="M6.6 10.8c1.4 2.8 3.8 5.1 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1C10.6 21 3 13.4 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.2.2 2.4.6 3.6.1.3 0 .7-.2 1l-2.3 2.2z"/></svg> <?= e($phone) ?></a>
      <a href="mailto:<?= e($email) ?>"><svg viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z"/></svg> <?= e($email) ?></a>
      <span><svg viewBox="0 0 24 24"><path d="M12 2C8.1 2 5 5.1 5 9c0 5.2 7 13 7 13s7-7.8 7-13c0-3.9-3.1-7-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"/></svg> <?= e($location) ?></span>
    </div>
  </div>
  <div class="footer-bottom">
    <div class="container footer-bottom-inner">
      <p>&copy; <?= $year ?> Landplan.co.ke. All Rights Reserved.</p>
      <p><a href="terms.html">Terms &amp; Conditions</a> <a href="privacy.html">Privacy Policy</a></p>
    </div>
  </div>
</footer>

<script src="js/script.js" defer></script>
<script src="js/site-dynamic.js" defer></script>
</body>
</html>
