/* 06 · Sobre ("Uma cozinha, não uma embalagem") — coreografia de entrada
   Ver MOTION.md. Só entradas (estado final = layout atual); sem markup novo.

   Desktop (>= 1024, foto | texto lado a lado)
     0ms    foto: moldura desliza da esquerda (left 1200) + zoom interno (1.12→1, 1400)
     120ms  título: mask (950)
     280ms  parágrafos: up, stagger 100 (800)
     520ms  especialista: foto pop (700) → 620ms texto up (750)
     650ms  selo "5 milhões de fatias": pop girando como um carimbo (800)
   Empilhado (< 1024: título → texto → especialista → foto)
     0ms título · 150ms parágrafos · 380ms especialista · 450ms foto (fade + zoom)
     · +400ms selo

   Cada grupo dispara pelo próprio elemento (nada anima fora da tela), mas os
   atrasos são contados a partir do início da seção: se tudo entra junto (seção
   inteira na tela), sai a cascata completa; se um grupo só aparece depois
   (rolagem lenta, mobile), ele entra na hora, sem espera, mantendo o ritmo
   interno do grupo. */
(function () {
  'use strict';

  var M = window.cdcMotion;
  if (!M || M.reduced) return;

  var section = document.querySelector('.sobre');
  if (!section) return;
  var q = function (sel) { return section.querySelector(sel); };
  var qa = function (sel) { return Array.prototype.slice.call(section.querySelectorAll(sel)); };

  var media = q('.sobre__media');
  var foto = q('.sobre__foto');
  var selo = q('.sobre__selo');
  var text = q('.sobre__text');
  var title = q('.sobre__title');
  var body = q('.sobre__body');
  var paras = qa('.sobre__body > p');
  var esp = q('.sobre__especialista');
  var espFoto = q('.sobre__especialista-foto');
  var espInfo = q('.sobre__especialista-info');

  var stacked = window.matchMedia && window.matchMedia('(max-width: 1023.98px)').matches;

  // ---------- Relógio da seção ----------
  // t0 = início "virtual" da coreografia. O 1º grupo que entra começa já
  // (t0 = agora − seu horário planejado); os seguintes descontam o que já
  // passou, sem nunca adiantar o ritmo interno do próprio grupo.
  var t0 = null;
  var now = function () { return window.performance && performance.now ? performance.now() : Date.now(); };

  // group(trigger, [{ el, at, stagger, opts }]) — `at` = ms desde o início da seção
  function group(trigger, items) {
    if (!trigger) return;
    items = items.filter(function (it) { return it.el && (!it.el.length || it.el.length > 0); });
    if (!items.length) return;
    var start = Math.min.apply(null, items.map(function (it) { return it.at; }));
    var registered = [];

    // registrado ANTES do reveal: no mesmo disparo roda primeiro e acerta os atrasos
    M.onEnter(trigger, function () {
      var t = now();
      if (t0 === null) t0 = t - start;
      var shift = Math.min(t - t0, start);
      registered.forEach(function (r) {
        r.el.style.setProperty('--reveal-delay', Math.max(0, Math.round(r.at - shift)) + 'ms');
      });
    });

    items.forEach(function (it) {
      var opts = { trigger: trigger, delay: it.at, stagger: it.stagger || 0 };
      Object.keys(it.opts || {}).forEach(function (k) { opts[k] = it.opts[k]; });
      M.reveal(it.el, opts).forEach(function (el, i) {
        registered.push({ el: el, at: it.at + i * (it.stagger || 0) });
      });
    });
  }

  // Grupos registrados na ordem planejada (mesmo observador → mesma ordem de disparo)
  var photo = function (at, frameVariant) {
    group(media, [
      { el: media, at: at, opts: { variant: frameVariant, duration: frameVariant === 'fade' ? 1000 : 1200 } },
      { el: foto, at: at, opts: { variant: 'zoom', duration: 1400, scale: 1.12 } },
      { el: selo, at: at + (stacked ? 400 : 650), opts: { variant: 'pop', duration: 800, scale: 0.55 } },
    ]);
  };
  var copy = function (at) {
    // o título com mask começa recortado (clip-path) e o IntersectionObserver do
    // Chrome não o vê: quem dispara é o bloco de texto que o contém
    group(text, [{ el: title, at: at, opts: { variant: 'mask', duration: 950 } }]);
    group(body, [{ el: paras, at: at + (stacked ? 150 : 160), stagger: 100, opts: { variant: 'up', duration: 800 } }]);
  };
  var expert = function (at) {
    group(esp, [
      { el: espFoto, at: at, opts: { variant: 'pop', duration: 700, scale: 0.7 } },
      { el: espInfo, at: at + 100, opts: { variant: 'up', duration: 750 } },
    ]);
  };

  if (stacked) {
    copy(0);
    expert(380);
    photo(450, 'fade');
  } else {
    photo(0, 'left');
    copy(120);
    expert(520);
  }
})();
