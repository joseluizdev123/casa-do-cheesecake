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
