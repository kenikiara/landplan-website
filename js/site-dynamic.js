/* ============================================================
   Landplan — dynamic frontend glue (progressive enhancement)
   - Populates listing grids [data-catalog] from /api/catalog.php
   - Submits enquiry forms [data-enquiry] to /api/leads.php
   - Toggles wishlist via .save-btn / .save-btn-lg to /api/save.php
   If the API is unavailable (e.g. static hosting), the page keeps
   whatever static content it shipped with.
   ============================================================ */
(function () {
  'use strict';
  var esc = function (s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  };

  /* ---------- Card renderers ---------- */
  var bookmarkSvg = '<svg viewBox="0 0 24 24"><path d="M17 3H7c-1.1 0-2 .9-2 2v16l7-3 7 3V5c0-1.1-.9-2-2-2z"/></svg>';
  var deedSvg = '<svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm-1 14-4-4 1.4-1.4L11 13.2l5.6-5.6L18 9l-7 7z"/></svg>';

  function landCard(it) {
    return '<article class="land-card">' +
      '<div class="land-media">' +
        '<a href="' + esc(it.url) + '"><img src="' + esc(it.image) + '" alt="' + esc(it.title) + '"></a>' +
        '<span class="tag">' + esc(it.category || 'LAND') + '</span>' +
        '<button class="save-btn" aria-label="Save" data-save-type="land" data-save-id="' + it.id + '">' + bookmarkSvg + '</button>' +
      '</div>' +
      '<a href="' + esc(it.url) + '" class="land-body">' +
        '<p class="land-loc">' + esc(it.location) + '</p>' +
        '<h3>' + esc(it.title) + '</h3>' +
        '<p class="land-price">' + esc(it.price) + '</p>' +
        '<p class="land-deed">' + deedSvg + ' ' + esc(it.title_status || 'Ready Title Deed') + '</p>' +
      '</a></article>';
  }

  function houseCard(it) {
    return '<article class="land-card">' +
      '<div class="land-media">' +
        '<a href="' + esc(it.url) + '"><img src="' + esc(it.image) + '" alt="' + esc(it.title) + '"></a>' +
        '<span class="tag">HOUSE</span>' +
        '<button class="save-btn" aria-label="Save" data-save-type="house" data-save-id="' + it.id + '">' + bookmarkSvg + '</button>' +
      '</div>' +
      '<a href="' + esc(it.url) + '" class="land-body">' +
        '<p class="land-loc">' + esc(it.location) + '</p>' +
        '<h3>' + esc(it.title) + '</h3>' +
        '<p class="land-price">' + esc(it.price) + '</p>' +
        '<p class="land-deed">' + deedSvg + ' ' + it.bedrooms + ' bed · ' + it.bathrooms + ' bath</p>' +
      '</a></article>';
  }

  function projectCard(it) {
    return '<figure class="proj"><a href="' + esc(it.url) + '"><img src="' + esc(it.image) + '" alt="' + esc(it.title) + '"></a></figure>';
  }

  // Project rendered in the land-card style (used on projects.html .land-grid sections)
  function projectLandCard(it) {
    return '<article class="land-card">' +
      '<div class="land-media">' +
        '<a href="' + esc(it.url) + '"><img src="' + esc(it.image) + '" alt="' + esc(it.title) + '"></a>' +
        '<span class="tag">' + esc((it.status || 'project').toUpperCase()) + '</span>' +
      '</div>' +
      '<a href="' + esc(it.url) + '" class="land-body">' +
        '<p class="land-loc">' + esc(it.location || '') + '</p>' +
        '<h3>' + esc(it.title) + '</h3>' +
      '</a></article>';
  }

  function articleCard(it) {
    return '<a href="' + esc(it.url) + '" class="blog-card">' +
      '<img src="' + esc(it.image) + '" alt="' + esc(it.title) + '">' +
      '<div class="blog-body">' +
        '<p class="blog-cat">' + esc(it.category || 'Article') + '</p>' +
        '<h3>' + esc(it.title) + '</h3>' +
        '<p>' + esc(it.excerpt || '') + '</p>' +
        '<div class="blog-meta"><span>' + esc(it.date || '') + '</span></div>' +
      '</div></a>';
  }

  var renderers = { land: landCard, house: houseCard, project: projectCard, article: articleCard };

  /* ---------- Populate listing grids ---------- */
  document.querySelectorAll('[data-catalog]').forEach(function (grid) {
    var type = grid.getAttribute('data-catalog');
    var render = renderers[type];
    // projects shown in a .land-grid use the land-card layout instead of a figure
    if (type === 'project' && !grid.classList.contains('projects-grid')) render = projectLandCard;
    if (!render) return;
    var qs = 'type=' + encodeURIComponent(type);
    if (grid.getAttribute('data-featured') === '1') qs += '&featured=1';
    if (grid.getAttribute('data-status')) qs += '&pstatus=' + encodeURIComponent(grid.getAttribute('data-status'));
    if (grid.getAttribute('data-limit')) qs += '&limit=' + encodeURIComponent(grid.getAttribute('data-limit'));

    fetch('api/catalog.php?' + qs, { headers: { 'Accept': 'application/json' } })
      .then(function (r) { return r.ok ? r.json() : Promise.reject(r.status); })
      .then(function (data) {
        if (!data.ok || !data.items || !data.items.length) return; // keep static fallback
        grid.innerHTML = data.items.map(render).join('');
        bindSaveButtons(grid);
      })
      .catch(function () { /* offline / static host — leave static cards */ });
  });

  /* ---------- Enquiry forms ---------- */
  document.querySelectorAll('form[data-enquiry]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var btn = form.querySelector('button[type=submit]');
      var success = form.parentElement.querySelector('.form-success') || document.querySelector('.form-success');
      var fd = new FormData(form);
      if (btn) { btn.disabled = true; btn.dataset.label = btn.textContent; btn.textContent = 'Sending…'; }

      fetch('api/leads.php', { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.json().catch(function () { return { ok: false }; }); })
        .then(function (res) {
          if (res.ok) {
            form.reset();
            if (success) { success.classList.add('show'); success.textContent = res.message || 'Thank you! We will be in touch shortly.'; }
            else alert('Thank you! Your enquiry has been received.');
            form.style.display = 'none';
          } else {
            alert(res.error || 'Sorry, something went wrong. Please try again or call us.');
          }
        })
        .catch(function () { alert('Network error. Please check your connection and try again.'); })
        .finally(function () { if (btn) { btn.disabled = false; btn.textContent = btn.dataset.label || 'Send'; } });
    });
  });

  /* ---------- Save / wishlist ---------- */
  function bindSaveButtons(scope) {
    (scope || document).querySelectorAll('[data-save-type]').forEach(function (btn) {
      if (btn._bound) return; btn._bound = true;
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        var fd = new FormData();
        fd.append('type', btn.getAttribute('data-save-type'));
        fd.append('id', btn.getAttribute('data-save-id'));
        fetch('api/save.php', { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } })
          .then(function (r) { return r.json().catch(function () { return { ok: false }; }); })
          .then(function (res) {
            if (res.auth === false) { window.location.href = 'client/index.php?next=' + encodeURIComponent(location.pathname + location.search); return; }
            if (res.ok) {
              btn.classList.toggle('saved', !!res.saved);
              if (btn.classList.contains('save-btn-lg')) btn.textContent = res.saved ? '♥ Saved' : '♥ Save this property';
            }
          })
          .catch(function () { /* ignore */ });
      });
    });
  }
  bindSaveButtons(document);
})();
