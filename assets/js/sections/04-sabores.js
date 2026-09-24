/* 04 · Sabores — abas acessíveis que trocam a fatia, a cor de fundo e o tema
   (Figma 7057:329, variantes 7057:519/566/613/660 · "Ao clicar, mudar a fatia").
   Padrão WAI-ARIA Tabs com ativação automática: setas ←/→, Home e End. */
(function () {
  'use strict';

  var sections = document.querySelectorAll('[data-sabores]');
  Array.prototype.forEach.call(sections, function (root) {
    var tabs = Array.prototype.slice.call(root.querySelectorAll('[role="tab"]'));
    if (!tabs.length) return;
    var links = root.querySelectorAll('.btn-link');

    function panelOf(tab) {
      return document.getElementById(tab.getAttribute('aria-controls'));
    }

    var tablist = root.querySelector('[role="tablist"]');
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Faixa rolável (tablet/mobile): traz a aba ativa inteira para a área visível,
    // rolando só a faixa na horizontal (nunca a página).
    function reveal(tab) {
      if (!tablist || tablist.scrollWidth <= tablist.clientWidth + 1) return;
      var list = tablist.getBoundingClientRect();
      var box = tab.getBoundingClientRect();
      var pad = parseFloat(getComputedStyle(tablist).scrollPaddingLeft) || 0;
      var delta = 0;
      if (box.left < list.left + pad) delta = box.left - list.left - pad;
      else if (box.right > list.right - pad) delta = box.right - list.right + pad;
      if (!delta) return;
      if (typeof tablist.scrollBy === 'function') {
        tablist.scrollBy({ left: delta, behavior: reduceMotion ? 'auto' : 'smooth' });
      } else {
        tablist.scrollLeft += delta;
      }
    }

    function activate(tab, moveFocus) {
      tabs.forEach(function (t) {
        var on = t === tab;
        t.setAttribute('aria-selected', on ? 'true' : 'false');
        t.tabIndex = on ? 0 : -1;
        var panel = panelOf(t);
        if (panel) panel.hidden = !on;
      });

      var bg = tab.getAttribute('data-bg');
      if (bg) root.style.setProperty('--sabores-bg', bg);

      var claro = tab.getAttribute('data-tema') === 'claro';
      root.classList.toggle('sabores--claro', claro);
      // Button Tertiary: Dark no fundo escuro, Light nos fundos claros
      Array.prototype.forEach.call(links, function (el) {
        el.classList.toggle('btn-link--dark', !claro);
      });

      if (moveFocus) {
        try { tab.focus({ preventScroll: true }); } catch (err) { tab.focus(); }
      }
      reveal(tab);
    }

    tabs.forEach(function (tab, i) {
      tab.addEventListener('click', function () { activate(tab, false); });
      tab.addEventListener('keydown', function (e) {
        var next = null;
        switch (e.key) {
          case 'ArrowRight': next = tabs[(i + 1) % tabs.length]; break;
          case 'ArrowLeft': next = tabs[(i - 1 + tabs.length) % tabs.length]; break;
          case 'Home': next = tabs[0]; break;
          case 'End': next = tabs[tabs.length - 1]; break;
          default: return;
        }
        e.preventDefault();
        activate(next, true);
      });
    });

    // Pré-carrega as fatias ocultas (loading="lazy") na intenção de uso das abas
    // (hover/foco/toque), para a troca acontecer sem esperar o download.
    var preloaded = false;
    var preload = function () {
      if (preloaded) return;
      preloaded = true;
      tabs.forEach(function (t) {
        var panel = panelOf(t);
        var img = panel && panel.querySelector('img');
        if (img && img.loading === 'lazy') img.loading = 'eager';
      });
    };
    if (tablist) {
      ['pointerenter', 'focusin', 'touchstart'].forEach(function (type) {
        tablist.addEventListener(type, preload, { once: true, passive: true });
      });
    }
  });
})();

/* ==========================================================================
   Motion (ver MOTION.md) — entrada, fatia "viva" e troca de sabor animada.
   Só opacity / translate / scale / rotate / clip-path: o estado final é o
   layout atual. Com prefers-reduced-motion nada disto roda (troca instantânea,
   como antes). Papéis separados para não disputar a mesma propriedade:
     .sabores__fatia        → float contínuo (translate, via CSS)
     .sabores__painel       → parallax (translate inline) + troca (keyframes)
     .sabores__painel > img → entrada pop (só a fatia visível)
   ========================================================================== */
(function () {
  'use strict';
  var M = window.cdcMotion;
  if (!M || M.reduced) return;

  var toArray = function (list) { return Array.prototype.slice.call(list); };

  toArray(document.querySelectorAll('[data-sabores]')).forEach(function (root) {
    var one = function (s) { return root.querySelector(s); };
    var all = function (s) { return toArray(root.querySelectorAll(s)); };
    var panels = all('[role="tabpanel"]');
    var fatia = one('.sabores__fatia');
    var shown = function () { return panels.filter(function (p) { return !p.hidden; })[0] || null; };

    // ---------- 1. Entrada ----------
    // Três grupos, cada um disparado ao aparecer: selo → palco (título + fatia)
    // → abas. Se a seção chega inteira de uma vez (link #sabores, tela alta),
    // vale a cascata dos atrasos abaixo; se os grupos entram rolando, cada um
    // desconta o tempo que já passou desde o primeiro (sem espera morta e sem
    // perder a ordem). O ajuste roda antes do show() do motor: mesmo elemento,
    // mesmo threshold, registrado antes → mesmo callback, em ordem.
    var TH = 0.2;
    var t0 = null;
    var hiddenNow = [];
    function stage(trigger, steps) {
      if (!trigger) return;
      var items = [];
      var base = Infinity;
      steps.forEach(function (s) { base = Math.min(base, s.delay || 0); });
      M.onEnter(trigger, function () {
        var now = window.performance.now();
        if (t0 === null) t0 = now;
        var shift = Math.min(now - t0, base);
        if (shift <= 0) return;
        items.forEach(function (it) {
          it.el.style.setProperty('--reveal-delay', Math.max(0, it.delay - shift) + 'ms');
        });
      }, { threshold: TH });
      steps.forEach(function (s) {
        var els = M.reveal(s.els.filter(Boolean), {
          variant: s.variant, duration: s.duration, delay: s.delay, stagger: s.stagger,
          scale: s.scale, trigger: trigger, threshold: TH, onReveal: s.onReveal
        });
        els.forEach(function (el, i) {
          items.push({ el: el, delay: (s.delay || 0) + i * (s.stagger || 0) });
          hiddenNow.push(el);
        });
      });
    }
    // O estado inicial (escondido) precisa valer JÁ: estes elementos já tinham
    // estilo calculado antes dos scripts defer, então sem isto o "esconder"
    // também seria uma transição (com o atraso da entrada) e, quando a seção
    // já está na tela (preview, recarregar no meio da página, link #sabores),
    // a entrada começaria de um estado quase visível.
    function applyInitialState() {
      hiddenNow.forEach(function (el) { el.style.setProperty('transition', 'none', 'important'); });
      if (hiddenNow.length) void window.getComputedStyle(hiddenNow[0]).opacity;   // 1 recálculo de estilo
      hiddenNow.forEach(function (el) { el.style.removeProperty('transition'); });
      hiddenNow = [];
    }

    // pop com giro próprio (o motor lê --reveal-rotate; limpo no fim)
    var spin = function (el, deg) { if (el) el.style.setProperty('--reveal-rotate', deg + 'deg'); return el; };
    var unspin = function (el) { el.style.removeProperty('--reveal-rotate'); };

    // Selo: "DESDE 2004" pop; os louros abrem como uma coroa (giram a partir
    // do cabo — origem no CSS da seção).
    stage(one('.sabores__selo'), [
      { els: [one('.sabores__selo-texto')], variant: 'pop', duration: 700, delay: 0 },
      { els: [spin(one('.sabores__decoracao--esq'), 28), spin(one('.sabores__decoracao--dir'), -28)],
        variant: 'pop', duration: 900, delay: 110, scale: 0.4, onReveal: unspin }
    ]);

    // Palco: título sobe da máscara; a fatia visível entra por cima dele.
    var visivel = shown();
    // (a fatia não tem entrada própria: ela cai de cima conforme a rolagem — ver 2.)
    stage(one('.sabores__palco'), [
      { els: [one('.sabores__titulo')], variant: 'mask', duration: 1000, delay: 100 }
    ]);

    // Abas + "Ver todas as opções" em cascata.
    stage(one('.sabores__nav'), [
      { els: all('.sabores__nav .sabores__aba'), variant: 'up', duration: 700, delay: 520, stagger: 80 }
    ]);
    applyInitialState();

    // ---------- 2. Fatia viva ----------
    // Pedido do cliente: a fatia (de qualquer sabor) cai de cima, devagar,
    // acompanhando a rolagem, e pousa na composição do Figma quando o topo da
    // seção chega a 12% da tela (a seção recorta o que está acima dela — CSS).
    // A queda é nos painéis (translate/rotate inline); o float sutil continua
    // no wrapper — papéis separados, sem disputar a mesma propriedade.
    if (fatia) {
      M.ambient(fatia, 'float');
      M.fall(panels, { section: root, measure: fatia, rotate: -18, endAt: 0.12 });
    }

    // ---------- 3. Troca de sabor ----------
    // O script das abas continua dono do estado (aria-selected, hidden, tema);
    // aqui só se observa o painel visível mudar e se anima a troca:
    // · a fatia anterior continua desenhada por cima (fora do fluxo) enquanto
    //   some girando/encolhendo; a nova entra girando no sentido da navegação;
    // · fundo e texto transicionam em 500ms (CSS); as abas acompanham; se o
    //   tema muda (claro ↔ escuro) os louros cruzam as duas cores.
    var timers = new WeakMap();
    function stop(el, cls) {
      var t = timers.get(el);
      if (t && t[cls]) window.clearTimeout(t[cls]);
      el.classList.remove(cls);
    }
    function play(el, cls, ms, after) {
      stop(el, cls);
      void el.offsetWidth;                       // reinicia a animação
      el.classList.add(cls);
      var t = timers.get(el) || {};
      t[cls] = window.setTimeout(function () { el.classList.remove(cls); if (after) after(); }, ms);
      timers.set(el, t);
    }

    var atual = visivel;
    var claro = root.classList.contains('sabores--claro');
    function swap(from, to) {
      root.style.setProperty('--sabores-dir', panels.indexOf(to) < panels.indexOf(from) ? '-1' : '1');
      if (from) {
        stop(from, 'is-entering');
        from.setAttribute('aria-hidden', 'true');
        play(from, 'is-leaving', 480, function () { from.removeAttribute('aria-hidden'); });
      }
      stop(to, 'is-leaving');
      to.removeAttribute('aria-hidden');
      var img = to.querySelector('img');
      var go = function () { if (!to.hidden) play(to, 'is-entering', 900); };
      if (img && !img.complete) {
        img.addEventListener('load', go, { once: true });
        img.addEventListener('error', go, { once: true });
      } else {
        go();
      }
      play(root, 'is-trocando', 600);
      var agora = root.classList.contains('sabores--claro');
      if (agora !== claro) play(root, 'is-trocando-tema', 560);
      claro = agora;
    }

    if (panels.length > 1 && 'MutationObserver' in window) {
      var mo = new MutationObserver(function () {
        var novo = shown();
        if (!novo || novo === atual) return;
        swap(atual, novo);
        atual = novo;
      });
      panels.forEach(function (p) { mo.observe(p, { attributes: true, attributeFilter: ['hidden'] }); });
    }
  });
})();

/* Mobile: a aba escolhida fica sempre visível na faixa horizontal de abas */
(function () {
  'use strict';
  document.querySelectorAll('[data-sabores] .sabores__abas').forEach(function (bar) {
    bar.addEventListener('click', function (e) {
      var tab = e.target.closest('[role="tab"]');
      if (!tab || bar.scrollWidth <= bar.clientWidth) return;
      var b = bar.getBoundingClientRect(), t = tab.getBoundingClientRect();
      if (t.left < b.left || t.right > b.right) {
        bar.scrollBy({ left: t.left - b.left - (b.width - t.width) / 2, behavior: 'smooth' });
      }
    });
  });
})();
