/* 07 — Depoimentos (Figma 7057:361): carrossel.
 * Dois modos, escolhidos pelo movimento do sistema:
 * - AUTOPLAY (padrão, pedido do cliente): os depoimentos correm para o lado
 *   sem parar, em loop infinito sem emenda — o JS clona o conjunto uma vez
 *   antes e uma vez depois dos originais (clones com aria-hidden + inert) e,
 *   ao passar de meio conjunto, reposiciona o scroll em 1 conjunto (visualmente
 *   idêntico). Freia suavemente com o mouse sobre o carrossel (o card cresce
 *   no hover), com foco de teclado, durante o toque/arraste e fora da tela;
 *   retoma depois. Setas avançam 1 card; dots = 1 por depoimento.
 *   Com poucos depoimentos (todos cabem na tela) cai no modo manual.
 * - MANUAL (prefers-reduced-motion: reduce, ou sem JS = CSS puro):
 *   · Base sem JS: a lista rola na horizontal com scroll-snap (CSS); as setas
 *     vêm com `disabled` no HTML (não fazem nada sem JS).
 *   · Setas prev/next por página, dots gerados a partir do número REAL de
 *     páginas. Cards por página vêm do CSS: 3 (≥1200) · 2 (≤1199) · 1 (≤767).
 *   · Com 1 página só, a raiz ganha [data-single-page].
 *   · Giro infinito: depois da última página vem a primeira e vice-versa.
 */
(function () {
  'use strict';

  var roots = document.querySelectorAll('[data-depoimentos-carousel]');
  if (!roots.length) return;

  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // ---------- modo manual (movimento reduzido / poucos depoimentos) ----------
  function manual(root) {
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
  }

  function gapOf(track) {
    var cs = window.getComputedStyle(track);
    return parseFloat(cs.columnGap || cs.gap) || 0;
  }

  // Cards inteiros que cabem na área útil do trilho (sem o padding).
  function perViewOf(track, card) {
    var cs = window.getComputedStyle(track);
    var inner = track.clientWidth - (parseFloat(cs.paddingLeft) || 0) - (parseFloat(cs.paddingRight) || 0);
    var w = card.getBoundingClientRect().width;
    if (!w) return 1;
    var g = gapOf(track);
    return Math.max(1, Math.floor((inner + g + 1) / (w + g)));
  }

  // ---------- modo autoplay (loop infinito) ----------
  function marquee(root) {
    var track = root.querySelector('[data-carousel-track]');
    var prev = root.querySelector('[data-carousel-prev]');
    var next = root.querySelector('[data-carousel-next]');
    var dotsWrap = root.querySelector('[data-carousel-dots]');
    if (!track || !dotsWrap) return;

    var originals = Array.prototype.slice.call(track.children);
    var n = originals.length;
    if (n < 2 || n <= perViewOf(track, originals[0])) { manual(root); return; }

    // clones: 1 conjunto antes + 1 depois (loop sem emenda nos dois sentidos)
    function cloneSet() {
      var frag = document.createDocumentFragment();
      originals.forEach(function (li) {
        var c = li.cloneNode(true);
        c.classList.add('is-clone');
        c.setAttribute('aria-hidden', 'true');
        c.setAttribute('inert', '');
        c.removeAttribute('data-figma-node');
        Array.prototype.forEach.call(c.querySelectorAll('[data-figma-node], [id]'), function (el) {
          el.removeAttribute('data-figma-node');
          el.removeAttribute('id');
        });
        frag.appendChild(c);
      });
      return frag;
    }
    track.insertBefore(cloneSet(), originals[0]);
    track.appendChild(cloneSet());
    root.classList.add('is-marquee');
    root.removeAttribute('data-single-page');

    // geometria
    var setW = 0;     // largura de 1 conjunto = n × (card + gap)
    var step = 0;     // 1 card + gap
    function measure() {
      setW = originals[0].offsetLeft - track.children[0].offsetLeft;
      step = setW / n;
    }
    // mantém a posição no conjunto do meio (os clones cobrem as duas pontas)
    function wrap(x) {
      if (setW <= 0) return x;
      while (x < setW * 0.5) x += setW;
      while (x >= setW * 1.5) x -= setW;
      return x;
    }

    var pos = 0;
    var lastWrite = -1;
    function write(x) {
      pos = x;
      lastWrite = x;
      track.scrollLeft = x;
    }

    measure();
    write(setW);      // originais alinhados como no layout

    // dots: 1 por depoimento
    dotsWrap.innerHTML = '';
    dotsWrap.setAttribute('role', 'group');
    dotsWrap.setAttribute('aria-label', 'Depoimentos');
    var dots = originals.map(function (li, i) {
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'depoimentos__dot';
      b.setAttribute('aria-controls', track.id);
      var nome = li.querySelector('.depoimentos__author-name');
      b.setAttribute('aria-label', 'Depoimento ' + (i + 1) + ' de ' + n + (nome ? ': ' + nome.textContent.trim() : ''));
      if (i === 0) b.setAttribute('aria-current', 'true');
      b.addEventListener('click', function () { glideToIndex(i); });
      dotsWrap.appendChild(b);
      return b;
    });
    var active = 0;
    function updateDots() {
      if (!step) return;
      var i = ((Math.round(track.scrollLeft / step) % n) + n) % n;
      if (i === active) return;
      dots[active].removeAttribute('aria-current');
      dots[i].setAttribute('aria-current', 'true');
      active = i;
    }

    // motivos para parar
    var holds = { boot: true, offscreen: true, hover: false, focus: false, touch: false, nav: false };
    function held() {
      for (var k in holds) { if (holds[k]) return true; }
      return document.hidden;
    }
    function cruise() {
      return window.matchMedia('(max-width: 767.98px)').matches ? 26 : 34;   // px/s
    }

    // loop
    var speed = 0;
    var last = 0;
    var raf = 0;
    var glide = null;     // deslize das setas/dots: { from, to, t0, dur }
    var navTimer = 0;
    function frame(t) {
      raf = 0;
      var dt = last ? Math.min(0.05, (t - last) / 1000) : 0;
      last = t;
      if (glide) {
        var p = Math.min(1, (t - glide.t0) / glide.dur);
        write(glide.from + (glide.to - glide.from) * (1 - Math.pow(1 - p, 3)));   // ease-out
        if (p >= 1) {
          glide = null;
          write(wrap(pos));
          speed = 0;
          window.clearTimeout(navTimer);
          navTimer = window.setTimeout(function () { hold('nav', false); }, 1200);   // tempo para ler
        }
      }
      var target = held() ? 0 : cruise();
      speed += (target - speed) * Math.min(1, dt * 5);                 // acelera/freia em ~0,3 s
      if (target === 0 && speed < 0.4) speed = 0;
      if (!glide && speed > 0 && !holds.touch && !holds.nav) write(wrap(pos + speed * dt));
      updateDots();
      if (glide || speed > 0 || target > 0) raf = window.requestAnimationFrame(frame);
      else last = 0;
    }
    function kick() {
      if (!raf) raf = window.requestAnimationFrame(frame);
    }
    function hold(name, on) {
      holds[name] = on;
      kick();
    }

    // setas / dots: desliza até o card alvo (animação própria, no mesmo loop)
    function glideTo(x) {
      window.clearTimeout(navTimer);
      holds.nav = true;
      glide = { from: pos, to: x, t0: window.performance.now(), dur: 520 };
      kick();
    }
    // ponto de partida: o destino do deslize em curso (cliques rápidos somam),
    // levado ao conjunto do meio junto com a posição atual (mesma imagem)
    function from() {
      measure();
      var base = glide ? glide.to : pos;
      var shift = wrap(base) - base;
      if (shift) {
        if (glide) { glide.from += shift; glide.to += shift; }
        write(pos + shift);
      }
      return base + shift;
    }
    // limiar de 35%: se o card mal começou a passar, a seta vai ao anterior/próximo
    // de verdade (e não só ao início do card que já está na frente)
    function glideBy(dir) {
      var x = from();
      var k = dir > 0 ? Math.floor(x / step + 0.35) + 1 : Math.ceil(x / step - 0.35) - 1;
      glideTo(k * step);
    }
    function glideToIndex(i) {
      var x = from();
      var k0 = Math.round(x / step);
      var d = (((i - k0) % n) + n) % n;
      if (d > n / 2) d -= n;
      glideTo((k0 + d) * step);
    }
    if (prev) { prev.disabled = false; prev.addEventListener('click', function () { glideBy(-1); }); }
    if (next) { next.disabled = false; next.addEventListener('click', function () { glideBy(1); }); }

    // mouse em cima / foco de teclado: freia
    root.addEventListener('pointerenter', function (e) { if (e.pointerType === 'mouse') hold('hover', true); });
    root.addEventListener('pointerleave', function (e) { if (e.pointerType === 'mouse') hold('hover', false); });
    root.addEventListener('focusin', function (e) {
      if (e.target.matches && e.target.matches(':focus-visible')) hold('focus', true);
    });
    root.addEventListener('focusout', function () { hold('focus', false); });

    // toque/arraste/trackpad: o usuário manda; retoma 2,5 s depois
    var touchTimer = 0;
    function userStart() {
      window.clearTimeout(touchTimer);
      holds.touch = true;
    }
    function userEnd() {
      window.clearTimeout(touchTimer);
      touchTimer = window.setTimeout(function () {
        write(wrap(track.scrollLeft));
        hold('touch', false);
      }, 2500);
    }
    track.addEventListener('touchstart', userStart, { passive: true });
    track.addEventListener('touchend', userEnd, { passive: true });
    track.addEventListener('touchcancel', userEnd, { passive: true });
    track.addEventListener('wheel', function (e) {
      if (Math.abs(e.deltaX) <= Math.abs(e.deltaY)) return;     // rolagem vertical da página: ignora
      userStart();
      userEnd();
    }, { passive: true });

    // rolagem que não veio do loop: acompanha e, parada, volta ao conjunto do meio
    var idleTimer = 0;
    track.addEventListener('scroll', function () {
      if (Math.abs(track.scrollLeft - lastWrite) < 1.5) return;          // foi o próprio loop
      pos = track.scrollLeft;
      updateDots();
      window.clearTimeout(idleTimer);
      idleTimer = window.setTimeout(function () {
        if (!holds.nav) write(wrap(track.scrollLeft));
      }, 180);
    }, { passive: true });

    // só roda na tela; espera a entrada dos cards antes de começar
    var started = false;
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (entries) {
        var on = entries[0].isIntersecting;
        holds.offscreen = !on;
        if (on && !started) {
          started = true;
          window.setTimeout(function () { hold('boot', false); }, 1200);
        }
        kick();
      }).observe(root);
    } else {
      holds.offscreen = false;
      holds.boot = false;
    }
    document.addEventListener('visibilitychange', function () { last = 0; kick(); });

    if ('ResizeObserver' in window) {
      new ResizeObserver(function () {
        var ratio = setW ? (pos - setW) / setW : 0;       // mesma fração do conjunto
        measure();
        write(wrap(setW + ratio * setW));
      }).observe(track);
    }
    kick();
  }

  Array.prototype.forEach.call(roots, function (root) {
    if (reduceMotion) manual(root);
    else marquee(root);
  });
})();

/* 07 — Depoimentos: coreografia de entrada (ver MOTION.md).
 * Estado final = layout atual; só opacity/translate/scale/rotate/clip-path.
 *  Header (dispara pelo header):
 *   título mask 950ms (0) → ★ do selo pop em cascata (180ms + 70ms/estrela)
 *   → "+354" sobe e conta (240ms, 1100ms) → rótulo do selo sobe (340ms).
 *  Carrossel (dispara pelo 1º card visível):
 *   só os cards VISÍVEIS no trilho sobem em cascata (200ms + 100ms/card;
 *   12px = a folga de hover do trilho, então nada é cortado pelo overflow
 *   durante a subida); cards fora da tela do carrossel não são escondidos.
 *   Setas/dots: fade. ≥1024 as setas ficam ao lado dos cards e entram com
 *   eles; ≤1023 setas + dots formam a linha de controles abaixo do card e
 *   entram quando ela aparece.
 *  As ★ do selo são um texto só ("★★★★★"): durante a entrada cada ★ vira um
 *  span (inline-block, mesmo avanço do glifo) e no fim o texto original volta. */
(function () {
  'use strict';
  if (!window.cdcMotion || window.cdcMotion.reduced) return;
  var M = window.cdcMotion;

  Array.prototype.forEach.call(document.querySelectorAll('.depoimentos'), function (section) {
    var header = section.querySelector('.depoimentos__header');
    var track = section.querySelector('[data-carousel-track]');

    // ---------- Header: título + selo de avaliação ----------
    if (header) {
      M.reveal(section.querySelector('.depoimentos__title'), { variant: 'mask', duration: 950, trigger: header });

      var stars = section.querySelector('.depoimentos__rating-stars');
      var text = stars && stars.childNodes.length === 1 && stars.firstChild.nodeType === 3 ? stars.firstChild : null;
      var chars = text ? Array.from(text.nodeValue) : [];
      if (chars.length > 1 && chars.length <= 6 && !/\s/.test(text.nodeValue)) {
        var spans = chars.map(function (c) {
          var s = document.createElement('span');
          s.className = 'depoimentos__rating-star';
          s.textContent = c;
          s.style.setProperty('--reveal-rotate', '-24deg');
          s.style.setProperty('--reveal-ease', 'cubic-bezier(.34, 1.56, .64, 1)');   // leve "estalo" no fim
          return s;
        });
        stars.textContent = '';
        spans.forEach(function (s) { stars.appendChild(s); });
        var done = 0;
        M.reveal(spans, {
          variant: 'pop', scale: 0.3, duration: 520, delay: 180, stagger: 70, trigger: header,
          onReveal: function () {
            if (++done < spans.length) return;
            stars.textContent = '';
            stars.appendChild(text);                          // texto original de volta
          },
        });
      } else if (stars) {
        M.reveal(stars, { variant: 'pop', duration: 600, delay: 180, trigger: header });
      }

      var count = section.querySelector('.depoimentos__rating-count');
      if (count) {
        M.reveal(count, { variant: 'up', distance: 10, duration: 600, delay: 240, trigger: header });
        // mede o número já na Anton (a largura reservada pelo contador tem de ser a final)
        var startCounter = function () {
          M.counter(count, { duration: 1100, delay: 260, trigger: header, threshold: 0.15 });
          var n = count.querySelector('.motion-count');
          if (n) n.style.width = n.style.minWidth;             // dígitos intermediários mais largos não empurram as ★
        };
        var font = window.getComputedStyle(count).fontFamily;
        if (document.fonts && document.fonts.load) {
          document.fonts.load('20px ' + font, count.textContent).then(startCounter, startCounter);
        } else {
          startCounter();
        }
      }

      M.reveal(section.querySelector('.depoimentos__badge-label'), { variant: 'up', distance: 12, duration: 700, delay: 340, trigger: header });
    }

    // ---------- Carrossel: cards visíveis + controles ----------
    if (!track) return;
    var box = track.getBoundingClientRect();
    var visible = Array.prototype.filter.call(track.children, function (card) {
      var r = card.getBoundingClientRect();
      return r.width > 0 && r.left < box.right - 1 && r.right > box.left + 1;
    });
    if (!visible.length) return;
    var first = visible[0];

    M.reveal(visible, { variant: 'up', distance: 12, duration: 800, delay: 200, stagger: 100, trigger: first });

    var navs = section.querySelectorAll('.depoimentos__nav');
    var dots = section.querySelector('.depoimentos__dots');
    var stacked = window.matchMedia('(max-width: 1023.98px)').matches;   // setas na linha dos dots
    if (stacked && dots) {
      M.reveal(dots, { variant: 'fade', duration: 450, delay: 60, trigger: dots });
      M.reveal(navs, { variant: 'fade', duration: 450, delay: 120, stagger: 60, trigger: dots });
    } else {
      M.reveal(navs, { variant: 'fade', duration: 450, delay: 520, trigger: first });
      if (dots) M.reveal(dots, { variant: 'fade', duration: 450, delay: 600, trigger: first });
    }
  });
})();
