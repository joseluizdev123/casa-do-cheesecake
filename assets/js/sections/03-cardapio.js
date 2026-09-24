/* 03 — Cardápio (Figma 7057:175): carrossel de UM sabor por vez no mobile.
 * - ≥768px: grade (4 colunas ≥1200, 2 abaixo). O trilho não rola e o CSS
 *   esconde os controles → nada aqui tem efeito visível.
 * - <768px sem JS: o trilho rola na horizontal com scroll-snap (1 card por
 *   tela, swipe) e os controles continuam [hidden].
 * - <768px com JS: setas anterior/próximo, dots (markup do HTML/PHP, um por
 *   sabor) e setas ←/→ do teclado nos controles. Giro infinito (pedido do
 *   cliente): depois do último vem o primeiro e vice-versa — nas setas, no
 *   teclado e no swipe além da ponta.
 *   Uma região aria-live anuncia "Doce de Leite, 2 de 4" a cada troca.
 * - Durante a rolagem animada de seta/dot o alvo fica travado: os dots não
 *   "voltam" no meio da animação e cliques rápidos avançam um a um.
 */
(function () {
  'use strict';

  var roots = document.querySelectorAll('[data-cardapio-carousel]');
  if (!roots.length) return;

  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  Array.prototype.forEach.call(roots, function (root) {
    var track = root.querySelector('[data-cardapio-track]');
    var controls = root.querySelector('[data-cardapio-controls]');
    if (!track || !controls) return;

    var slides = Array.prototype.slice.call(track.children);
    var prev = controls.querySelector('[data-cardapio-prev]');
    var next = controls.querySelector('[data-cardapio-next]');
    var dots = Array.prototype.slice.call(controls.querySelectorAll('[data-cardapio-dot]'));
    var status = controls.querySelector('[data-cardapio-status]');
    if (slides.length < 2 || !prev || !next) return;

    var last = slides.length - 1;
    var current = 0;
    var target = -1;      // sabor-alvo de uma rolagem programática em curso
    var unlock = 0;
    var ticking = false;

    // Só é carrossel quando o trilho rola (mobile); na grade não faz nada.
    function isCarousel() {
      return track.scrollWidth - track.clientWidth > 2;
    }

    function maxScroll() {
      return Math.max(0, track.scrollWidth - track.clientWidth);
    }

    function offsetOf(i) {
      return Math.min(maxScroll(), Math.max(0, slides[i].offsetLeft - slides[0].offsetLeft));
    }

    function nearest() {
      var x = track.scrollLeft;
      var best = 0;
      var bestD = Infinity;
      for (var i = 0; i < slides.length; i++) {
        var d = Math.abs(offsetOf(i) - x);
        if (d < bestD) { bestD = d; best = i; }
      }
      return best;
    }

    function nameOf(i) {
      var el = slides[i].querySelector('.cardapio__name');
      return el ? el.textContent.trim() : 'Sabor';
    }

    function render() {
      dots.forEach(function (d, i) {
        if (i === current) d.setAttribute('aria-current', 'true');
        else d.removeAttribute('aria-current');
      });
      prev.setAttribute('aria-disabled', 'false');
      next.setAttribute('aria-disabled', 'false');
    }

    function announce() {
      if (status) status.textContent = nameOf(current) + ', ' + (current + 1) + ' de ' + slides.length;
    }

    function setCurrent(i) {
      if (i === current) return;
      current = i;
      render();
      announce();
    }

    function release() {
      target = -1;
      window.clearTimeout(unlock);
    }

    function goTo(i) {
      i = ((i % slides.length) + slides.length) % slides.length;   // infinito nos dois sentidos
      target = i;
      window.clearTimeout(unlock);
      unlock = window.setTimeout(function () { release(); sync(); }, 900);
      setCurrent(i);
      track.scrollTo({ left: offsetOf(i), behavior: reduceMotion ? 'auto' : 'smooth' });
    }

    // Sabor atual a partir da rolagem (swipe, trackpad, foco via Tab)
    function sync() {
      ticking = false;
      if (target > -1) {
        if (Math.abs(offsetOf(target) - track.scrollLeft) > 2) return;   // ainda animando
        release();
      }
      setCurrent(nearest());
    }

    function schedule() {
      if (!ticking) { ticking = true; window.requestAnimationFrame(sync); }
    }

    prev.addEventListener('click', function () { goTo(current - 1); });
    next.addEventListener('click', function () { goTo(current + 1); });

    // Swipe além da ponta dá a volta (o scroll nativo para nas extremidades)
    var startX = null;
    var startAt = 0;
    track.addEventListener('touchstart', function (e) {
      startX = e.touches[0].clientX;
      startAt = current;
    }, { passive: true });
    track.addEventListener('touchend', function (e) {
      if (startX === null || !isCarousel()) return;
      var dx = e.changedTouches[0].clientX - startX;
      startX = null;
      if (dx < -40 && startAt === last && track.scrollLeft >= maxScroll() - 2) goTo(0);
      else if (dx > 40 && startAt === 0 && track.scrollLeft <= 2) goTo(last);
    }, { passive: true });
    dots.forEach(function (d, i) {
      if (i > last) { d.hidden = true; return; }
      d.addEventListener('click', function () { goTo(i); });
    });

    // Setas do teclado nos controles (dots e setas). Dentro dos cards o foco
    // fica nos links "Pedir": ali o Tab já leva ao card seguinte (o trilho
    // rola até o link focado e o dot acompanha pelo evento de scroll).
    controls.addEventListener('keydown', function (e) {
      if (e.altKey || e.ctrlKey || e.metaKey || e.shiftKey || !isCarousel()) return;
      var dir = e.key === 'ArrowRight' ? 1 : e.key === 'ArrowLeft' ? -1 : 0;
      if (!dir) return;
      e.preventDefault();
      goTo(current + dir);
      // o foco num dot acompanha o sabor escolhido
      if (dots.indexOf(document.activeElement) > -1 && dots[current]) dots[current].focus();
    });

    track.addEventListener('scroll', schedule, { passive: true });

    // Ao mudar de largura (ex.: girar o celular), recoloca o sabor atual
    window.addEventListener('resize', function () {
      if (!isCarousel()) return;
      release();
      track.scrollTo({ left: offsetOf(current), behavior: 'auto' });
    });

    controls.hidden = false;
    current = isCarousel() ? nearest() : 0;
    render();
  });
})();
