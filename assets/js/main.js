(function () {
  'use strict';

  window.showToast = function (message, type = 'primary') {
    const toastEl = document.getElementById('appToast');
    const body = document.getElementById('appToastBody');
    if (!toastEl || !body) return;
    toastEl.className = `toast align-items-center text-bg-${type} border-0`;
    body.textContent = message;
    bootstrap.Toast.getOrCreateInstance(toastEl).show();
  };

  // Navbar shrink on scroll
  const header = document.querySelector('.site-header .navbar');
  if (header) {
    window.addEventListener('scroll', () => {
      header.classList.toggle('navbar-scrolled', window.scrollY > 40);
    });
  }

  // Fade-up on scroll
  const fadeEls = document.querySelectorAll('.fade-up');
  if (fadeEls.length && 'IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach((e) => {
        if (e.isIntersecting) {
          e.target.classList.add('visible');
          io.unobserve(e.target);
        }
      });
    }, { threshold: 0.12 });
    fadeEls.forEach((el) => io.observe(el));
  }

  // Stat counters
  document.querySelectorAll('[data-count]').forEach((el) => {
    const target = parseInt(el.dataset.count, 10);
    const io = new IntersectionObserver((entries) => {
      if (!entries[0].isIntersecting) return;
      let current = 0;
      const step = Math.ceil(target / 60);
      const tick = () => {
        current += step;
        if (current >= target) {
          el.textContent = target.toLocaleString() + (el.dataset.suffix || '');
          return;
        }
        el.textContent = current.toLocaleString();
        requestAnimationFrame(tick);
      };
      tick();
      io.disconnect();
    });
    io.observe(el);
  });

  // Demo actions removed — features are now backed by the database/API.
})();