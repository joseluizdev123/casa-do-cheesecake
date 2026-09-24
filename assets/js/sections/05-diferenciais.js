/* 05 — Diferenciais: coreografia de entrada (ver MOTION.md)
 *
 *   título "O que faz um cheesecake…"  mask ................... 0ms      950ms
 *   texto de apoio                      up ..................... +160ms   800ms
 *   cards                               up, cascata ............ 0/90/180 800ms
 *   fotos dos cards                     zoom 1.1→1 + fade ...... cards +60ms, 1100ms
 *   tag "na quantidade certa"           pop (leve overshoot) ... foto +420ms, 600ms
 *   hover (mouse): zoom 1.05 na foto do card — sections/05-diferenciais.css
 *
 * Cada LINHA visual do layout dispara a própria entrada (medido no carregamento):
 *   ≥ 1024  título + texto numa linha · os 3 cards noutra;
 *   768–1023 título / texto / card largo / 2 cards;
 *   < 768   tudo empilhado — cada bloco entra quando chega na tela.
 * A foto entra quando ela mesma aparece (no desktop fica na base do card).
 *
 * "Relógio" da seção: linhas que disparam juntas (carregamento, âncora, rolagem
 * rápida) entram em cascata, na ordem da leitura; a que chega depois pela
 * rolagem entra na hora, sem espera. Ele usa o mesmo limiar das entradas (um
 * observador só) e é registrado antes delas → ajusta o --reveal-delay antes de
 * o motor revelar.
 *
 * Conteúdo sempre visível (nada de carrossel/aba aqui). Estado final = layout
 * atual; só opacity/translate/scale/rotate/clip-path.
 */
(function () {
  'use strict';

  var M = window.cdcMotion;
  if (!M || M.reduced) return;

  var section = document.querySelector('.diferenciais');
  if (!section) return;

  var head = section.querySelector('.diferenciais__head');
  var title = section.querySelector('.diferenciais__title');
  var lead = section.querySelector('.diferenciais__lead');
  var cards = Array.prototype.slice.call(section.querySelectorAll('.diferenciais__card'));

  var THRESHOLD = 0.15;
  var STAGGER = 90;
  var now = function () { return window.performance && performance.now ? performance.now() : Date.now(); };

  // Agrupa elementos que dividem a mesma faixa vertical (= mesma linha do layout).
  function rows(els) {
    var out = [];
    els.forEach(function (el) {
      var r = el.getBoundingClientRect();
      var cur = out[out.length - 1];
      if (cur && r.top < cur.bottom - 1) {
        cur.els.push(el);
        cur.bottom = Math.max(cur.bottom, r.bottom);
      } else {
        out.push({ els: [el], bottom: r.bottom });
      }
    });
    return out.map(function (g) { return g.els; });
  }

  // O motor observa com rootMargin -8% embaixo: o que está na tela ao carregar,
  // mas só "espiando" nessa faixa de baixo, não dispararia sozinho e ficaria
  // escondido até rolar. Essas linhas entram na cascata da linha anterior.
  var vh = window.innerHeight || document.documentElement.clientHeight;
  var band = vh * 0.92;
  function onScreen(el) {
    var r = el.getBoundingClientRect();
    return r.top < vh && r.bottom > 0;
  }
  function peeking(el) {
    var r = el.getBoundingClientRect();
    return onScreen(el) && r.top + THRESHOLD * r.height + 32 > band;   // 32 = deslocamento máx. da entrada
  }
  function inBand(el) {
    var r = el.getBoundingClientRect();
    return r.top < band && r.bottom > 0;
  }

  var clock = -Infinity;   // quando a próxima linha pode começar
  var lastTrigger = null;
  var registered = [];

  // cue(trigger, [{ el, opts }], gap): registra as entradas de uma linha;
  // `gap` = espera mínima até a linha seguinte começar, se disparar junto.
  function cue(trigger, items, gap) {
    items = items.filter(function (it) { return it && it.el; });
    if (!trigger || !items.length) return;
    if (lastTrigger && peeking(trigger) && inBand(lastTrigger)) trigger = lastTrigger;
    lastTrigger = trigger;

    M.onEnter(trigger, function () {
      var t = now();
      var offset = Math.max(0, clock - t);
      clock = t + offset + gap;
      if (!offset) return;
      items.forEach(function (it) {
        it.el.style.setProperty('--reveal-delay', Math.round((it.opts.delay || 0) + offset) + 'ms');
      });
    }, { threshold: THRESHOLD });

    items.forEach(function (it) {
      var opts = { trigger: trigger, threshold: THRESHOLD };
      Object.keys(it.opts).forEach(function (k) { opts[k] = it.opts[k]; });
      M.reveal(it.el, opts);
      registered.push(it.el);
    });
  }

  // Mede tudo antes de registrar (as entradas deslocam os elementos).
  var headRows = rows([title, lead].filter(Boolean));
  var cardRows = rows(cards);

  // ---- Cabeçalho: título (máscara) → texto ----
  // O título escondido pela máscara tem área visível 0 (o observador nunca o
  // veria entrar): quem dispara a linha dele é o cabeçalho.
  headRows.forEach(function (row) {
    var hasTitle = row.indexOf(title) > -1;
    var hasLead = row.indexOf(lead) > -1;
    cue(hasTitle ? head || section : row[0], row.map(function (el) {
      return el === title
        ? { el: el, opts: { variant: 'mask', duration: 950 } }
        : { el: el, opts: { variant: 'up', duration: 800, delay: hasTitle ? 160 : 0 } };
    }), hasTitle && hasLead ? 260 : hasTitle ? 160 : 100);
  });

  // ---- Cards (cascata por linha) → fotos (zoom) → tag (pop) ----
  cardRows.forEach(function (row) {
    cue(row[0], row.map(function (card, i) {
      return { el: card, opts: { variant: 'up', duration: 800, delay: i * STAGGER } };
    }), 60);

    var photos = [];
    var tags = [];
    row.forEach(function (card, i) {
      var img = card.querySelector('.diferenciais__img');
      var tag = card.querySelector('.diferenciais__tag');
      if (img) photos.push({ el: img, opts: { variant: 'zoom', scale: 1.1, duration: 1100, delay: i * STAGGER } });
      if (tag) tags.push({ el: tag, opts: { variant: 'pop', duration: 600, delay: i * STAGGER + 420 } });
    });
    if (!photos.length) return;

    // Foto na base do card (desktop/mobile): dispara quando ela aparece.
    // Foto ao lado do texto (card largo do tablet): dispara com o card.
    var first = photos[0].el;
    var below = first.getBoundingClientRect().top - row[0].getBoundingClientRect().top > 40;
    cue(below ? first : row[0], photos.concat(tags), 160);
  });

  // Estado inicial sem transição: se a seção já tinha sido pintada antes deste
  // script rodar (rede lenta), os elementos não "somem aos poucos" antes de entrar.
  registered.forEach(function (el) { el.style.setProperty('transition', 'none', 'important'); });
  void section.offsetHeight;   // aplica o estado escondido já, sem transição
  registered.forEach(function (el) { el.style.removeProperty('transition'); });
})();
