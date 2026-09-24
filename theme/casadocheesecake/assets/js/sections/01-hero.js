/* 01 — Hero: coreografia de entrada no carregamento + parallax da foto (ver MOTION.md)
 *
 * Ordem = ordem visual (desktop e tablet/mobile): título → foto → recorte da
 * fatia → selo "Desde 2004" → 3 selos (com contadores) → botões → texto.
 * Tudo termina em ≤ 1.6s. O que já está na tela ao carregar entra junto
 * (disparado pelo próprio hero); o que estiver abaixo da dobra entra ao rolar,
 * sem o atraso da abertura.
 *
 * Recorte (≥ 1024): a fatia do PNG é a MESMA da foto de fundo. Para não
 * aparecer "fatia dupla" enquanto ela sobe, o wrapper só mostra a faixa acima
 * da moldura da foto (clip-path) durante a subida: a fatia nasce da borda da
 * foto e cresce sobre o título; no fim o recorte volta inteiro, alinhado.
 *
 * Parallax (≥ 1024): foto e recorte descem juntos (mesmo deslocamento, sempre
 * alinhados) conforme a página rola — zero no topo, então o estado parado é
 * idêntico ao layout aprovado.
 */
(function () {
  'use strict';
  if (!window.cdcMotion || window.cdcMotion.reduced) return;
  var M = window.cdcMotion;

  var hero = document.querySelector('.hero');
  if (!hero) return;
  var $ = function (s) { return hero.querySelector(s); };
  var $$ = function (s) { return Array.prototype.slice.call(hero.querySelectorAll(s)); };

  var title = $('.hero__title');
  var bgImg = $('.hero__bg-img');
  var cutout = $('.hero__cutout');
  var cutoutImg = $('.hero__cutout-img');
  var badge = $('.hero__since-badge');
  var laurels = $$('.hero__laurel');
  var info = $('.hero__info');
  var items = $$('.hero__info-item');
  var buttons = $$('.hero__actions .btn');
  var text = $('.hero__text');

  var mqDesk = window.matchMedia('(min-width: 1024px)');
  var desk = mqDesk.matches;

  // Está na tela ao carregar? (fontes ainda carregando = layout pode mudar → trata como visível)
  var vh = window.innerHeight || document.documentElement.clientHeight;
  var fontsReady = !document.fonts || document.fonts.status === 'loaded';
  var onScreen = function (el) {
    if (!el) return false;
    if (!fontsReady) return true;
    var r = el.getBoundingClientRect();
    return r.top < vh && r.bottom > 0;
  };

  // Registra a entrada já no estado escondido, SEM transição: o hero já foi
  // pintado quando os scripts defer rodam, e o [data-reveal] traz a transição
  // (com o atraso da coreografia) — sem isto o "esconder" seria animado e a
  // entrada no carregamento não aconteceria.
  var reveal = function (els, opts) {
    els = (els instanceof Element ? [els] : els).filter(Boolean);
    if (!els.length) return els;
    els.forEach(function (el) { el.style.setProperty('transition', 'none', 'important'); });
    M.reveal(els, opts);
    els.forEach(function (el) { void window.getComputedStyle(el).opacity; el.style.removeProperty('transition'); });
    return els;
  };

  // Grupo: no carregamento entra com o atraso da coreografia (disparo = hero);
  // abaixo da dobra entra ao rolar, com o próprio disparo e atraso curto.
  var group = function (els, load, scroll, opts) {
    els = els.filter(Boolean);
    if (!els.length) return;
    var o = {}; Object.keys(opts).forEach(function (k) { o[k] = opts[k]; });
    if (onScreen(els[0])) { o.delay = load; o.trigger = hero; }
    else { o.delay = scroll; o.trigger = els[0]; }
    reveal(els, o);
    return o;
  };

  // 1. Título: sobe de dentro da máscara (0ms)
  reveal(title, { variant: 'mask', delay: 0, duration: 1000, trigger: hero });

  // 2. Foto: zoom longo 1.08 → 1 (150ms). No tablet/mobile a faixa escura dos
  //    selos acompanha a foto (fade), para o bloco escuro nascer inteiro.
  reveal(bgImg, { variant: 'zoom', scale: 1.08, delay: 150, duration: 1400, trigger: hero });
  if (!desk && info) reveal(info, { variant: 'fade', delay: 250, duration: 900, trigger: hero });

  // 3. Recorte da fatia (só existe ≥ 1024): sobe 80px de dentro da moldura (350ms)
  if (cutout && cutoutImg) {
    var windowed = desk;
    if (windowed) cutout.style.clipPath = 'inset(0 0 78.7% 0)';   // 122 / 575.5 do wrapper = borda da foto
    reveal(cutoutImg, {
      variant: 'up', distance: 80, delay: 350, duration: 1100, trigger: hero,
      onReveal: function () { if (windowed) cutout.style.removeProperty('clip-path'); },
    });
  }

  // 4. Selo "Desde 2004": ano em pop, louros abraçam o selo (650ms)
  reveal(badge, { variant: 'pop', scale: .7, delay: 650, duration: 700, trigger: hero });
  reveal(laurels, { variant: 'fade', delay: 720, duration: 800, trigger: hero });

  // 5. Selos de informação: sobem em cascata (750ms, +100ms) e os números contam
  var infoGroup = group(items, 750, 0, { variant: 'up', stagger: 100, duration: 650 });
  if (infoGroup) {
    items.forEach(function (item, i) {
      var t = item.querySelector('.hero__info-text');
      if (!t || !/\d/.test(t.textContent)) return;
      // conta junto com a entrada do selo e termina com ela (≤ 1.6s no total)
      M.counter(t, { delay: infoGroup.delay + i * 100, duration: 650, trigger: infoGroup.trigger, threshold: .15 });
    });
  }

  // 6. Botões (850ms, +90ms) e texto (960ms)
  group(buttons, 850, 0, { variant: 'up', stagger: 90, duration: 650 });
  group([text], 960, 120, { variant: 'up', duration: 640 });

  // ---------- Parallax foto + recorte (≥ 1024, só depois da entrada da foto) ----------
  if (!bgImg || !cutout) return;
  var targets = [bgImg, cutout];
  var SPEED = 0.12, MAX = 40;
  var cur = 0, visible = false, live = false, raf = 0;
  var set = function (y) {
    var v = y ? '0 ' + y.toFixed(2) + 'px' : '';
    targets.forEach(function (el) { if (v) el.style.translate = v; else el.style.removeProperty('translate'); });
  };
  var tick = function () {
    raf = 0;
    var goal = (live && mqDesk.matches) ? Math.min(MAX, Math.max(0, window.scrollY * SPEED)) : 0;
    var next = cur + (goal - cur) * 0.18;               // leve inércia: nunca "pula"
    if (Math.abs(goal - next) < 0.1) next = goal;
    if (next !== cur) { cur = next; set(cur); }
    if (cur !== goal) raf = window.requestAnimationFrame(tick);
  };
  var request = function () { if (!raf) raf = window.requestAnimationFrame(tick); };

  new IntersectionObserver(function (entries) {
    visible = entries[entries.length - 1].isIntersecting;
    if (visible) request();
  }).observe(hero);
  window.addEventListener('scroll', function () { if (visible || cur) request(); }, { passive: true });
  if (mqDesk.addEventListener) mqDesk.addEventListener('change', request);   // < 1024: volta a 0

  // começa quando a foto termina a entrada (a transição de entrada não "arrasta" o parallax)
  M.onEnter(hero, function () {
    window.setTimeout(function () { live = true; request(); }, 150 + 1400 + 250);
  });
})();
