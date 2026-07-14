<?php
require_once __DIR__ . '/_guard.php';
$title = 'Help & SOP'; $active = 'help';
require __DIR__ . '/_head.php';
?>
<div class="page-head"><div><h2>Help &amp; Standard Operating Procedures</h2><p class="sub">How to run the Landplan website day-to-day.</p></div></div>

<div class="stack">

  <div class="card card-pad">
    <h3 style="font-size:15px;margin-bottom:10px">🚀 Daily routine (5 minutes)</h3>
    <ol style="padding-left:20px;line-height:1.9">
      <li>Open <a href="dashboard.php" style="color:var(--green)">Dashboard</a>, check the <b>New enquiries</b> count.</li>
      <li>Open <a href="leads.php" style="color:var(--green)">Leads</a>, click each new enquiry, call/WhatsApp the person, then set its status to <b>Contacted</b>.</li>
      <li>Check <a href="appointments.php" style="color:var(--green)">Appointments</a> for today's site visits.</li>
    </ol>
  </div>

  <div class="card card-pad">
    <h3 style="font-size:15px;margin-bottom:10px">🏞️ Add a land listing</h3>
    <ol style="padding-left:20px;line-height:1.9">
      <li>Go to <b>Land for Sale → Add Land</b>.</li>
      <li>Fill in Title, Location, Size, Price and Category. The URL slug fills itself.</li>
      <li>Write a Description (leave a blank line between paragraphs) and add Key features (one per line).</li>
      <li>Upload a <b>Cover image</b> (the main photo) and add more to the <b>Photo gallery</b>.</li>
      <li>Set Status to <b>Published</b>, tick <b>Feature on homepage</b> if it should stand out, then <b>Save</b>.</li>
      <li>It appears instantly on <a href="../land.html" target="_blank" style="color:var(--green)">the Land page</a>. Use “View on site” to check it.</li>
    </ol>
    <p class="mini muted">Houses and Projects work exactly the same way.</p>
  </div>

  <div class="card card-pad">
    <h3 style="font-size:15px;margin-bottom:10px">✍️ Publish a blog article</h3>
    <ol style="padding-left:20px;line-height:1.9">
      <li>Go to <b>Blog Articles → New Article</b>.</li>
      <li>Add Title, Category, a short Excerpt and the Body. You can use basic HTML (<code>&lt;p&gt; &lt;h2&gt; &lt;ul&gt; &lt;li&gt; &lt;strong&gt; &lt;a&gt;</code>).</li>
      <li>Upload a cover image, set Status to <b>Published</b> and Save.</li>
    </ol>
  </div>

  <div class="card card-pad">
    <h3 style="font-size:15px;margin-bottom:10px">📇 Handle a new enquiry (lead → client)</h3>
    <ol style="padding-left:20px;line-height:1.9">
      <li>Open the lead from <a href="leads.php" style="color:var(--green)">Leads</a>. Call or WhatsApp using the buttons.</li>
      <li>Update the <b>Status</b> as it progresses: New → Contacted → Qualified → Won/Lost.</li>
      <li>When they're serious, click <b>Convert to client</b>, this creates a client record.</li>
      <li>Add internal notes so your team stays aligned.</li>
    </ol>
  </div>

  <div class="card card-pad">
    <h3 style="font-size:15px;margin-bottom:10px">👤 Give a client portal access &amp; share documents</h3>
    <ol style="padding-left:20px;line-height:1.9">
      <li>Open the client from <a href="clients.php" style="color:var(--green)">Clients</a>. Make sure they have an <b>email</b> saved.</li>
      <li>Under <b>Portal access</b>, set a password and click <b>Enable portal access</b>.</li>
      <li>Share the email + password with your client, they log in at <a href="../client/" target="_blank" style="color:var(--green)">/client/</a>.</li>
      <li>Under <b>Documents</b>, upload title deeds or agreements, they appear in the client's portal to download.</li>
    </ol>
  </div>

  <div class="card card-pad">
    <h3 style="font-size:15px;margin-bottom:10px">⚙️ Update contact details &amp; social links</h3>
    <p>Go to <a href="settings.php" style="color:var(--green)">Settings</a>. Phone, email, location, social links, homepage hero text and stats all update across the whole website when you save.</p>
  </div>

  <div class="card card-pad">
    <h3 style="font-size:15px;margin-bottom:10px">🔐 Security checklist</h3>
    <ul style="padding-left:20px;line-height:1.9">
      <li>Delete <code>admin/setup.php</code> from the server after creating your account.</li>
      <li>Use a strong, unique password. Add staff under <b>Staff Users</b> with the <b>Editor</b> role (they can't manage other users).</li>
      <li>Keep <code>app/config.php</code> private, never share it.</li>
      <li>Turn on <b>HTTPS</b> (SSL) in cPanel, then enable the HTTPS redirect in <code>.htaccess</code>.</li>
      <li>Back up your database from cPanel → phpMyAdmin regularly (Export).</li>
    </ul>
  </div>

  <div class="card card-pad" style="background:var(--deep);color:#fff">
    <h3 style="font-size:15px;margin-bottom:6px">Need the full deployment guide?</h3>
    <p style="color:#c4ccc2">See <b>DEPLOYMENT.md</b> and <b>SOP.md</b> in your project folder for the complete cPanel upload steps and operating procedures.</p>
  </div>

</div>
<?php require __DIR__ . '/_foot.php'; ?>
