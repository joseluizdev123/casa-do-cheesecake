/* A Casa do Cheesecake — comportamento global */
(function () {
  'use strict';

  // Header: após o scroll fica fixo no topo com logo reduzido (anotação Figma 7073:305)
  var header = document.querySelector('[data-site-header]');
  if (header) {
    var ticking = false;
    var update = function () {
      header.classList.toggle('is-scrolled', window.scrollY > 8);
      ticking = false;
    };
    window.addEventListener('scroll', function () {
      if (!ticking) { window.requestAnimationFrame(update); ticking = true; }
    }, { passive: true });
    update();
  }

  // Viúvas (pedido do cliente): une as duas últimas palavras de cada texto
  // corrido com espaço não separável — a última linha nunca fica com 1 palavra,
  // em qualquer largura e navegador (o text-wrap: pretty do CSS sozinho não garante).
  // Títulos h2 ficam de fora: as quebras deles são as do Figma.
  (function () {
    document.querySelectorAll('main p, footer p, main li, main h1').forEach(function (el) {
      if (el.children.length > 3) return;
      var walker = document.createTreeWalker(el, NodeFilter.SHOW_TEXT);
      var nodes = [];
      while (walker.nextNode()) nodes.push(walker.currentNode);
      if (el.textContent.trim().split(/\s+/).length < 4) return;
      for (var i = nodes.length - 1; i >= 0; i--) {
        var t = nodes[i].nodeValue.replace(/\s+$/, '');
        var k = t.lastIndexOf(' ');
        if (k > -1) { nodes[i].nodeValue = t.slice(0, k) + '\u00a0' + t.slice(k + 1) + nodes[i].nodeValue.slice(t.length); break; }
        if (t.length && i < nodes.length - 1) break;
      }
    });
  })();

  // Menu mobile (< 1024px): hambúrguer abre drawer em tela cheia
  var toggle = document.querySelector('[data-menu-toggle]');
  var drawer = document.querySelector('[data-menu-drawer]');
  if (toggle && drawer) {
    var label = toggle.querySelector('.sr-only');
    var setOpen = function (open, viaKeyboard) {
      toggle.setAttribute('aria-expanded', String(open));
      if (label) label.textContent = open ? 'Fechar menu' : 'Abrir menu';
      drawer.hidden = !open;
      // foco no 1º link só quando aberto pelo teclado (no toque/mouse não aparece contorno)
      if (open && viaKeyboard) {
        var first = drawer.querySelector('a');
        if (first) first.focus({ preventScroll: true });
      }
    };
    toggle.addEventListener('click', function (e) {
      setOpen(toggle.getAttribute('aria-expanded') !== 'true', e.detail === 0);
    });
    drawer.addEventListener('click', function (e) {
      if (e.target.closest('a')) setOpen(false);
    });
    // clique fora do painel fecha (a página continua visível abaixo, como na referência)
    document.addEventListener('click', function (e) {
      if (toggle.getAttribute('aria-expanded') === 'true' && !drawer.contains(e.target) && !toggle.contains(e.target)) setOpen(false);
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
        setOpen(false);
        toggle.focus();
      }
    });
    window.matchMedia('(min-width: 1024px)').addEventListener('change', function (mq) {
      if (mq.matches) setOpen(false);
    });
  }
})();
