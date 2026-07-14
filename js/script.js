document.addEventListener('DOMContentLoaded', () => {

  /* Sticky header shadow on scroll */
  const header = document.querySelector('.header');
  if (header) {
    const onScroll = () => header.classList.toggle('scrolled', window.scrollY > 10);
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  /* Scroll reveal */
  const revealEls = document.querySelectorAll('.reveal');
  if ('IntersectionObserver' in window && revealEls.length) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('in-view');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
    revealEls.forEach((el) => io.observe(el));
  } else {
    revealEls.forEach((el) => el.classList.add('in-view'));
  }

  /* Animated counters: <strong data-count="5000" data-suffix="+">0</strong> */
  const counters = document.querySelectorAll('[data-count]');
  if ('IntersectionObserver' in window && counters.length) {
    const counterIO = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        const el = entry.target;
        const target = parseInt(el.dataset.count, 10) || 0;
        const suffix = el.dataset.suffix || '';
        const duration = 1400;
        const start = performance.now();
        const step = (now) => {
          const progress = Math.min((now - start) / duration, 1);
          const eased = 1 - Math.pow(1 - progress, 3);
          el.textContent = Math.round(eased * target).toLocaleString() + suffix;
          if (progress < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
        counterIO.unobserve(el);
      });
    }, { threshold: 0.4 });
    counters.forEach((el) => counterIO.observe(el));
  }

  /* Nav dropdowns: hover on desktop (CSS), tap-toggle on touch/mobile */
  document.querySelectorAll('.nav-item').forEach((item) => {
    const toggle = item.querySelector('.dropdown-toggle');
    if (!toggle) return;
    toggle.addEventListener('click', (e) => {
      if (window.innerWidth <= 768) {
        e.preventDefault();
        document.querySelectorAll('.nav-item.open').forEach((other) => {
          if (other !== item) other.classList.remove('open');
        });
        item.classList.toggle('open');
      }
    });
  });
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.nav-item')) {
      document.querySelectorAll('.nav-item.open').forEach((el) => el.classList.remove('open'));
    }
  });

  /* Horizontal carousel scroll: wrap a row with [data-scroll] and buttons with [data-prev]/[data-next] */
  document.querySelectorAll('[data-scroll]').forEach((row) => {
    const wrap = row.closest('.head-actions') ? null : row.parentElement;
    const prevBtn = row.parentElement.querySelector('[data-prev]');
    const nextBtn = row.parentElement.querySelector('[data-next]');
    const scrollAmount = () => (row.firstElementChild ? row.firstElementChild.getBoundingClientRect().width + 20 : 300);
    if (prevBtn) prevBtn.addEventListener('click', () => row.scrollBy({ left: -scrollAmount(), behavior: 'smooth' }));
    if (nextBtn) nextBtn.addEventListener('click', () => row.scrollBy({ left: scrollAmount(), behavior: 'smooth' }));
  });

  /* Lightbox for gallery / detail images: any img with [data-lightbox] */
  const lightbox = document.getElementById('lightbox');
  if (lightbox) {
    const lightboxImg = lightbox.querySelector('img');
    document.querySelectorAll('[data-lightbox]').forEach((img) => {
      img.addEventListener('click', () => {
        lightboxImg.src = img.src;
        lightboxImg.alt = img.alt || '';
        lightbox.classList.add('open');
      });
    });
    lightbox.addEventListener('click', (e) => {
      if (e.target === lightbox || e.target.classList.contains('lightbox-close')) {
        lightbox.classList.remove('open');
      }
    });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') lightbox.classList.remove('open');
    });
  }

  /* Fake form submit: any <form data-fake-submit> shows a success message instead of posting */
  document.querySelectorAll('form[data-fake-submit]').forEach((form) => {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const success = form.querySelector('.form-success');
      if (success) success.classList.add('show');
      form.reset();
    });
  });

  /* Save/wishlist heart toggle (visual only) */
  document.querySelectorAll('.save-btn').forEach((btn) => {
    btn.addEventListener('click', () => btn.classList.toggle('saved'));
  });

});
