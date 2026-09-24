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

/* ---- Entrada (cdcMotion — ver MOTION.md) -----------------------------------
 * Cabeçalho: título sobe de dentro da máscara → texto (up, +150ms).
 * ≥768 (grade): cards em cascata (up, 100ms) POR LINHA da grade — 4 colunas
 *   ≥1200, 2 abaixo: cada linha dispara quando aparece. O bolo de cada card
 *   "cresce" da própria base (scale .86 → 1, a moldura inteira, para o recorte
 *   não ser cortado) e o selo "Mais pedido" chega logo depois do seu card.
 * ≤767 (carrossel de 1 por vez): entra só o que está na tela — o trilho sobe
 *   inteiro (nenhum card fica escondido fora da tela, o swipe continua livre),
 *   o bolo do card visível cresce e depois vêm os dots (fade) e as setas (up).
 * Quando cabeçalho e cards aparecem juntos (link "#cardapio", preview), os
 *   cards esperam o título: a cascata começa ~250ms depois dele.
 * "Ver cardápio completo": up quando aparece.
 * Estado final = layout atual (só opacity/translate/scale/clip-path).
 */
(function () {
  'use strict';

  var M = window.cdcMotion;
  if (!M || M.reduced) return;

  var MOBILE = '(max-width: 767.98px)';   // mesmo corte do CSS (carrossel)
  var STEP = 100;                         // stagger entre cards
  var AFTER_TITLE = 250;                  // cards só começam 250ms depois do título

  function each(list, fn) { Array.prototype.forEach.call(list, fn); }

  each(document.querySelectorAll('.cardapio'), function (section) {
    var title = section.querySelector('.cardapio__title');
    var lead = section.querySelector('.cardapio__lead');
    var body = section.querySelector('.cardapio__body');
    var track = section.querySelector('.cardapio__grid');
    var controls = section.querySelector('.cardapio__controls');
    var cta = body ? body.querySelector(':scope > .btn') : null;
    var cards = track ? Array.prototype.slice.call(track.children) : [];
    var mq = window.matchMedia ? window.matchMedia(MOBILE) : { matches: false };
    var vh = window.innerHeight;
    var titleAt = -Infinity;

    function inView(el) {
      var r = el.getBoundingClientRect();
      return r.width > 0 && r.top < vh && r.bottom > 0;
    }

    // Se o gatilho dispara logo depois do título (mesmo quadro ou quase),
    // empurra o atraso dos elementos para a cascata vir depois dele.
    // Registrado ANTES dos reveal() do mesmo gatilho → roda antes deles.
    function afterTitle(trigger, els) {
      M.onEnter(trigger, function () {
        var extra = Math.round(titleAt + AFTER_TITLE - performance.now());
        if (extra <= 0) return;
        els.forEach(function (el) {
          if (!el.hasAttribute('data-reveal') || el.classList.contains('is-revealed')) return;
          var d = parseFloat(el.style.getPropertyValue('--reveal-delay')) || 0;
          el.style.setProperty('--reveal-delay', (d + extra) + 'ms');
        });
      });
    }

    // Bolo (moldura) + selo de um card, a partir do atraso do card
    function cardExtras(card, delay, trigger, bucket) {
      var photo = card.querySelector('.cardapio__photo');
      var tag = card.querySelector('.cardapio__tag');
      if (photo) {
        M.reveal(photo, { variant: 'zoom', scale: 1.1, duration: 1100, delay: delay + 120, trigger: trigger });
        bucket.push(photo);
      }
      if (tag) {
        M.reveal(tag, { variant: 'pop', scale: 0.7, duration: 600, delay: delay + 460, trigger: trigger });
        bucket.push(tag);
      }
    }

    // ---- Cabeçalho ----
    // Gatilho = o <header>: o IntersectionObserver do Chrome aplica o
    // clip-path do próprio alvo, e a máscara fechada (inset 100%) nunca
    // "entraria" sozinha.
    var head = section.querySelector('.cardapio__head') || section;
    M.onEnter(head, function () { titleAt = performance.now(); });
    if (title) M.reveal(title, { variant: 'mask', duration: 950, trigger: head });
    if (lead) M.reveal(lead, { variant: 'up', delay: title ? 150 : 0, trigger: head });

    // ---- Cards ----
    var gridEls = [];
    if (cards.length && mq.matches) {
      // Carrossel: o trilho entra inteiro; o bolo só do card que está na tela
      var withTrack = [track];
      afterTitle(track, withTrack);
      M.reveal(track, { variant: 'up', duration: 850 });
      var t = track.getBoundingClientRect();
      cards.forEach(function (card) {
        var r = card.getBoundingClientRect();
        var seen = Math.min(r.right, t.right) - Math.max(r.left, t.left);
        if (r.width && seen > r.width / 2) cardExtras(card, 0, track, withTrack);
      });

      // Controles: juntos com o trilho se já estão na tela no carregamento,
      // senão quando aparecem
      if (controls && !controls.hidden) {
        var dots = controls.querySelector('.cardapio__dots');
        var arrows = controls.querySelector('.cardapio__arrows');
        var withCards = inView(controls);
        var ctlTrigger = withCards ? track : controls;
        var base = withCards ? 380 : 0;
        if (dots) {
          M.reveal(dots, { variant: 'fade', duration: 600, delay: base, trigger: ctlTrigger });
          if (withCards) withTrack.push(dots);
        }
        if (arrows) {
          M.reveal(arrows, { variant: 'up', duration: 600, delay: base + 80, distance: 12, trigger: ctlTrigger });
          if (withCards) withTrack.push(arrows);
        }
      }
    } else if (cards.length) {
      // Grade: uma cascata por linha (cards com o mesmo topo)
      var rows = [];
      cards.forEach(function (card) {
        var top = card.getBoundingClientRect().top;
        var row = rows[rows.length - 1];
        if (row && Math.abs(row.top - top) < 4) row.cards.push(card);
        else rows.push({ top: top, cards: [card] });
      });
      rows.forEach(function (row) {
        var first = row.cards[0];
        var els = row.cards.slice();
        afterTitle(first, els);
        M.reveal(row.cards, { variant: 'up', stagger: STEP, trigger: first });
        row.cards.forEach(function (card, i) {
          cardExtras(card, Math.min(i, 5) * STEP, first, els);
        });
        gridEls = gridEls.concat(els);
      });

      // Se a tela encolher até o carrossel antes da entrada, nada pode ficar
      // escondido fora da tela: solta os cards que ainda não entraram.
      if (mq.addEventListener) {
        mq.addEventListener('change', function (e) {
          if (!e.matches) return;
          gridEls.forEach(function (el) {
            if (!el.hasAttribute('data-reveal') || el.classList.contains('is-revealed')) return;
            el.removeAttribute('data-reveal');
            ['--reveal-delay', '--reveal-dur', '--reveal-distance', '--reveal-scale'].forEach(function (p) {
              el.style.removeProperty(p);
            });
          });
        });
      }
    }

    // ---- "Ver cardápio completo" ----
    if (cta) {
      var ctaWithCards = inView(cta) && track;
      M.reveal(cta, { variant: 'up', duration: 750, delay: ctaWithCards ? 520 : 0, trigger: ctaWithCards ? track : null });
    }

    snapHidden(section);
  });

  // O carrossel (bloco acima) e a detecção de linhas leem o layout antes de os
  // [data-reveal] entrarem → o navegador já tem o estilo "visível" desses
  // elementos e faria uma transição visível → escondido (com o atraso de cada
  // um), e quem já está na tela nunca chegaria a sumir/animar. Aqui o estado
  // inicial é aplicado sem transição; a entrada (is-revealed) anima normal.
  function snapHidden(scope) {
    var els = scope.querySelectorAll('[data-reveal]:not(.is-revealed)');
    each(els, function (el) { el.style.setProperty('transition', 'none', 'important'); });
    each(els, function (el) { void window.getComputedStyle(el).opacity; });
    each(els, function (el) { el.style.removeProperty('transition'); });
  }
})();
