/* 07 — Depoimentos (Figma 7057:361): carrossel acessível.
 * - Base sem JS: a lista rola na horizontal com scroll-snap (CSS).
 * - Com JS: setas prev/next por página, dots gerados a partir do número REAL
 *   de páginas (o Figma mostra 4 dots de exemplo; com 3 depoimentos e 3 cards
 *   por página existe 1 página → 1 dot e setas desabilitadas).
 * - Dots = tablist (role tab + aria-selected), setas ←/→/Home/End entre dots.
 */
(function () {
  'use strict';

  var roots = document.querySelectorAll('[data-depoimentos-carousel]');
  if (!roots.length) return;

  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  Array.prototype.forEach.call(roots, function (root) {
    var track = root.querySelector('[data-carousel-track]');
    var prev = root.querySelector('[data-carousel-prev]');
    var next = root.querySelector('[data-carousel-next]');
    var dotsWrap = root.querySelector('[data-carousel-dots]');
    if (!track || !dotsWrap) return;

    var slides = track.children;
    var pages = 0;
    var current = 0;
    var dots = [];

    function gap() {
      var cs = window.getComputedStyle(track);
      return parseFloat(cs.columnGap || cs.gap) || 0;
    }

    function perView() {
      if (!slides.length) return 1;
      var w = slides[0].getBoundingClientRect().width;
      if (!w) return 1;
      return Math.max(1, Math.round((track.clientWidth + gap()) / (w + gap())));
    }

    function maxScroll() {
      return Math.max(0, track.scrollWidth - track.clientWidth);
    }

    function offsetOf(page) {
      var idx = Math.min(page * perView(), slides.length - 1);
      var left = slides[idx].offsetLeft - slides[0].offsetLeft;
      return Math.min(left, maxScroll());
    }

    function goTo(page, focusDot) {
      page = Math.max(0, Math.min(pages - 1, page));
      track.scrollTo({ left: offsetOf(page), behavior: reduceMotion ? 'auto' : 'smooth' });
      setCurrent(page);
      if (focusDot && dots[page]) dots[page].focus();
    }

    function setCurrent(page) {
      current = page;
      dots.forEach(function (d, i) {
        var on = i === page;
        d.setAttribute('aria-selected', on ? 'true' : 'false');
        d.tabIndex = on ? 0 : -1;
      });
      if (prev) prev.disabled = page <= 0;
      if (next) next.disabled = page >= pages - 1;
    }

    function onDotKey(e) {
      var i = dots.indexOf(e.currentTarget);
      var to = null;
      if (e.key === 'ArrowRight') to = (i + 1) % pages;
      else if (e.key === 'ArrowLeft') to = (i - 1 + pages) % pages;
      else if (e.key === 'Home') to = 0;
      else if (e.key === 'End') to = pages - 1;
      if (to === null) return;
      e.preventDefault();
      goTo(to, true);
    }

    function build() {
      var count = Math.max(1, Math.ceil(slides.length / perView()));
      if (count === pages && dots.length) { sync(); return; }
      pages = count;
      dotsWrap.innerHTML = '';
      dotsWrap.setAttribute('role', 'tablist');
      dotsWrap.setAttribute('aria-label', 'Páginas de depoimentos');
      dots = [];
      for (var i = 0; i < pages; i++) {
        var b = document.createElement('button');
        b.type = 'button';
        b.className = 'depoimentos__dot';
        b.setAttribute('role', 'tab');
        b.setAttribute('aria-controls', track.id);
        b.setAttribute('aria-label', 'Página ' + (i + 1) + ' de ' + pages);
        b.addEventListener('click', goTo.bind(null, i, false));
        b.addEventListener('keydown', onDotKey);
        dotsWrap.appendChild(b);
        dots.push(b);
      }
      sync();
    }

    // Página atual a partir da posição de rolagem (arraste, trackpad, teclado)
    function sync() {
      var left = track.scrollLeft;
      var best = 0;
      var bestDist = Infinity;
      for (var p = 0; p < pages; p++) {
        var d = Math.abs(offsetOf(p) - left);
        if (d < bestDist) { bestDist = d; best = p; }
      }
      setCurrent(best);
    }

    if (prev) prev.addEventListener('click', function () { goTo(current - 1); });
    if (next) next.addEventListener('click', function () { goTo(current + 1); });

    var raf = 0;
    track.addEventListener('scroll', function () {
      if (raf) return;
      raf = window.requestAnimationFrame(function () { raf = 0; sync(); });
    }, { passive: true });

    if ('ResizeObserver' in window) {
      new ResizeObserver(function () { build(); }).observe(track);
    } else {
      window.addEventListener('resize', build);
    }
    build();
  });
})();
