/* 07 — Depoimentos (Figma 7057:361): carrossel acessível.
 * - Base sem JS: a lista rola na horizontal com scroll-snap (CSS); as setas
 *   vêm com `disabled` no HTML (não fazem nada sem JS).
 * - Com JS: setas prev/next por página, dots gerados a partir do número REAL
 *   de páginas (o Figma mostra 4 dots de exemplo). Cards por página vêm do CSS:
 *   3 (≥1200) · 2 (≤1199) · 1 por vez, largura total (≤767) → 1, 2 e 3
 *   páginas com os 3 depoimentos do layout.
 * - Dots = botões simples; o da página atual recebe aria-current="true".
 * - Com 1 página só, a raiz ganha [data-single-page] (o CSS esconde setas e
 *   dots abaixo de 1200; no desktop ficam como no Figma).
 * - Giro infinito (pedido do cliente): depois da última página vem a primeira
 *   e vice-versa — nas setas e no swipe além da ponta.
 * - Durante a rolagem animada de uma seta/dot, a página-alvo fica travada
 *   (os dots não "voltam" no meio da animação e cliques rápidos avançam 1 a 1).
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
    var target = -1;        // página-alvo de uma rolagem programática em curso
    var unlock = 0;

    function gap() {
      var cs = window.getComputedStyle(track);
      return parseFloat(cs.columnGap || cs.gap) || 0;
    }

    // Largura útil do trilho (sem o padding: no mobile/tablet o trilho é
    // sangrado até a borda da tela e o padding alinha o 1º card ao conteúdo).
    function innerWidth() {
      var cs = window.getComputedStyle(track);
      return track.clientWidth - (parseFloat(cs.paddingLeft) || 0) - (parseFloat(cs.paddingRight) || 0);
    }

    // Cards inteiros que cabem na área útil (o "peek" do próximo não conta).
    function perView() {
      if (!slides.length) return 1;
      var w = slides[0].getBoundingClientRect().width;
      if (!w) return 1;
      var g = gap();
      return Math.max(1, Math.floor((innerWidth() + g + 1) / (w + g)));
    }

    function maxScroll() {
      return Math.max(0, track.scrollWidth - track.clientWidth);
    }

    function offsetOf(page) {
      var idx = Math.min(page * perView(), slides.length - 1);
      var left = slides[idx].offsetLeft - slides[0].offsetLeft;
      return Math.min(left, maxScroll());
    }

    function release() {
      target = -1;
      window.clearTimeout(unlock);
    }

    function goTo(page) {
      page = ((page % pages) + pages) % pages;               // infinito nos dois sentidos
      target = page;
      window.clearTimeout(unlock);
      unlock = window.setTimeout(function () { release(); sync(); }, 800);
      track.scrollTo({ left: offsetOf(page), behavior: reduceMotion ? 'auto' : 'smooth' });
      setCurrent(page);
    }

    function setCurrent(page) {
      current = page;
      dots.forEach(function (d, i) {
        if (i === page) d.setAttribute('aria-current', 'true');
        else d.removeAttribute('aria-current');
      });
      if (prev) prev.disabled = pages <= 1;
      if (next) next.disabled = pages <= 1;
    }

    function build() {
      var count = Math.max(1, Math.ceil(slides.length / perView()));
      if (count === pages && dots.length) { sync(); return; }
      pages = count;
      // ≤1199 o CSS esconde setas/dots quando tudo cabe numa página
      if (pages === 1) root.setAttribute('data-single-page', '');
      else root.removeAttribute('data-single-page');
      dotsWrap.innerHTML = '';
      dotsWrap.setAttribute('role', 'group');
      dotsWrap.setAttribute('aria-label', 'Páginas de depoimentos');
      dots = [];
      for (var i = 0; i < pages; i++) {
        var b = document.createElement('button');
        b.type = 'button';
        b.className = 'depoimentos__dot';
        b.setAttribute('aria-controls', track.id);
        b.setAttribute('aria-label', 'Página ' + (i + 1) + ' de ' + pages);
        // já nasce marcado (evita a transição "apagado → ativo" no carregamento)
        if (i === Math.min(current, pages - 1)) b.setAttribute('aria-current', 'true');
        b.addEventListener('click', goTo.bind(null, i));
        dotsWrap.appendChild(b);
        dots.push(b);
      }
      sync();
    }

    // Página atual a partir da posição de rolagem (swipe, trackpad, teclado)
    function sync() {
      var left = track.scrollLeft;
      if (target > -1) {
        if (Math.abs(offsetOf(target) - left) > 2) return;   // ainda animando até o alvo
        release();
      }
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
    // o usuário assumiu o gesto (swipe/trackpad): a posição real volta a mandar
    track.addEventListener('pointerdown', release, { passive: true });

    // Swipe além da ponta dá a volta (o scroll nativo para nas extremidades)
    var startX = null;
    var startAt = 0;
    track.addEventListener('touchstart', function (e) {
      startX = e.touches[0].clientX;
      startAt = current;
    }, { passive: true });
    track.addEventListener('touchend', function (e) {
      if (startX === null || pages <= 1) return;
      var dx = e.changedTouches[0].clientX - startX;
      startX = null;
      if (dx < -40 && startAt === pages - 1 && track.scrollLeft >= maxScroll() - 2) goTo(0);
      else if (dx > 40 && startAt === 0 && track.scrollLeft <= 2) goTo(pages - 1);
    }, { passive: true });
    track.addEventListener('wheel', release, { passive: true });

    if ('ResizeObserver' in window) {
      new ResizeObserver(function () { build(); }).observe(track);
    } else {
      window.addEventListener('resize', build);
    }
    build();
  });
})();
