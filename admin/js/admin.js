/* ============ LANDPLAN ADMIN — UI helpers ============ */
document.addEventListener('DOMContentLoaded', () => {

  /* Confirm before any destructive action */
  document.querySelectorAll('[data-confirm]').forEach((el) => {
    el.addEventListener('click', (e) => {
      if (!window.confirm(el.getAttribute('data-confirm') || 'Are you sure?')) {
        e.preventDefault();
      }
    });
  });

  /* Auto-slug: fill a [data-slug-target] from a [data-slug-source] unless edited */
  const src = document.querySelector('[data-slug-source]');
  const tgt = document.querySelector('[data-slug-target]');
  if (src && tgt) {
    let touched = tgt.value.trim() !== '';
    tgt.addEventListener('input', () => { touched = true; });
    src.addEventListener('input', () => {
      if (touched) return;
      tgt.value = src.value.toString().toLowerCase()
        .replace(/[^\w\s-]/g, '').trim().replace(/[\s_]+/g, '-').replace(/-+/g, '-');
    });
  }

  /* Live image preview for a cover-image file input -> [data-preview] */
  document.querySelectorAll('input[type=file][data-preview]').forEach((input) => {
    const target = document.querySelector(input.getAttribute('data-preview'));
    if (!target) return;
    input.addEventListener('change', () => {
      const f = input.files && input.files[0];
      if (f) target.src = URL.createObjectURL(f);
    });
  });

  /* Instant client-side table filter: [data-filter] input over [data-filterable] rows */
  document.querySelectorAll('[data-filter]').forEach((box) => {
    const table = document.querySelector(box.getAttribute('data-filter'));
    if (!table) return;
    box.addEventListener('input', () => {
      const q = box.value.toLowerCase();
      table.querySelectorAll('tbody tr').forEach((tr) => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  });

  /* Auto-submit filter selects */
  document.querySelectorAll('select[data-autosubmit]').forEach((sel) => {
    sel.addEventListener('change', () => sel.form && sel.form.submit());
  });

});
