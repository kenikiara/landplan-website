<?php
require_once __DIR__ . '/_guard.php';
$pdo = db();

$fields = [
    'site_name','tagline','meta_description',
    'contact_phone','contact_email','contact_location','lead_notify_email',
    'social_facebook','social_instagram','social_linkedin','social_whatsapp',
    'hero_kicker','hero_title','hero_sub',
    'stat_years','stat_clients','stat_acres','stat_projects',
];

if (is_post()) {
    csrf_check();
    foreach ($fields as $f) save_setting($f, post($f));
    activity_log('update','settings');
    flash('Settings saved.');
    redirect('settings.php');
}

$s = settings();
$v = fn(string $k) => e($s[$k] ?? '');
$title='Settings'; $active='settings';
require __DIR__ . '/_head.php';
?>
<div class="page-head"><h2>Site Settings</h2><p class="sub">These control the details shown across your public website.</p></div>
<form method="post"><?= csrf_field() ?>
  <div class="stack">
    <div class="card card-pad">
      <h3 style="font-size:14px;margin-bottom:14px">General</h3>
      <div class="form-grid">
        <div class="field"><label>Site name</label><input type="text" name="site_name" value="<?= $v('site_name') ?>"></div>
        <div class="field"><label>Tagline</label><input type="text" name="tagline" value="<?= $v('tagline') ?>"></div>
        <div class="field full"><label>Default meta description</label><textarea name="meta_description"><?= $v('meta_description') ?></textarea></div>
      </div>
    </div>

    <div class="card card-pad">
      <h3 style="font-size:14px;margin-bottom:14px">Contact</h3>
      <div class="form-grid">
        <div class="field"><label>Phone</label><input type="text" name="contact_phone" value="<?= $v('contact_phone') ?>" placeholder="+254 705 121 788"></div>
        <div class="field"><label>Public email</label><input type="email" name="contact_email" value="<?= $v('contact_email') ?>"></div>
        <div class="field"><label>Location</label><input type="text" name="contact_location" value="<?= $v('contact_location') ?>"></div>
        <div class="field"><label>Send enquiries to</label><input type="email" name="lead_notify_email" value="<?= $v('lead_notify_email') ?>"><span class="hint">New enquiry emails go here.</span></div>
      </div>
    </div>

    <div class="card card-pad">
      <h3 style="font-size:14px;margin-bottom:14px">Social links</h3>
      <div class="form-grid">
        <div class="field"><label>Facebook URL</label><input type="url" name="social_facebook" value="<?= $v('social_facebook') ?>"></div>
        <div class="field"><label>Instagram URL</label><input type="url" name="social_instagram" value="<?= $v('social_instagram') ?>"></div>
        <div class="field"><label>LinkedIn URL</label><input type="url" name="social_linkedin" value="<?= $v('social_linkedin') ?>"></div>
        <div class="field"><label>WhatsApp link</label><input type="url" name="social_whatsapp" value="<?= $v('social_whatsapp') ?>" placeholder="https://wa.me/254705121788"></div>
      </div>
    </div>

    <div class="card card-pad">
      <h3 style="font-size:14px;margin-bottom:14px">Homepage hero</h3>
      <div class="form-grid">
        <div class="field full"><label>Kicker</label><input type="text" name="hero_kicker" value="<?= $v('hero_kicker') ?>"></div>
        <div class="field full"><label>Headline</label><input type="text" name="hero_title" value="<?= $v('hero_title') ?>"></div>
        <div class="field full"><label>Sub-text</label><textarea name="hero_sub"><?= $v('hero_sub') ?></textarea></div>
      </div>
    </div>

    <div class="card card-pad">
      <h3 style="font-size:14px;margin-bottom:14px">Homepage stats</h3>
      <div class="form-grid">
        <div class="field"><label>Years of experience</label><input type="number" name="stat_years" value="<?= $v('stat_years') ?>"></div>
        <div class="field"><label>Happy clients</label><input type="number" name="stat_clients" value="<?= $v('stat_clients') ?>"></div>
        <div class="field"><label>Acres sold</label><input type="number" name="stat_acres" value="<?= $v('stat_acres') ?>"></div>
        <div class="field"><label>Projects completed</label><input type="number" name="stat_projects" value="<?= $v('stat_projects') ?>"></div>
      </div>
    </div>

    <div class="form-actions"><button class="btn btn-primary" type="submit">Save settings</button></div>
  </div>
</form>
<?php require __DIR__ . '/_foot.php'; ?>
