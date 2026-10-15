/* Página Contato — carrossel das fotos ao lado da lista de canais.
 * Fade entre as fotos a cada 5 s (pausa com o mouse em cima, com foco nas
 * setas ou com prefers-reduced-motion), setas anterior/próxima e arrasto
 * lateral no toque. Sem JS fica só a primeira foto. */
(function () {
  'use strict';

  var root = document.querySelector('[data-contato-fotos]');
  if (!root) return;
  var slides = Array.prototype.slice.call(root.querySelectorAll('[data-contato-slide]'));
  var prev = root.querySelector('[data-contato-prev]');
  var next = root.querySelector('[data-contato-next]');
  var status = root.querySelector('[data-contato-status]');
  if (slides.length < 2 || !prev || !next) return;

  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var current = 0;
  var timer = null;
  var paused = false;

  function show(i) {
    current = (i + slides.length) % slides.length;
    slides.forEach(function (s, k) {
      var on = k === current;
      s.classList.toggle('is-active', on);
      if (on) { s.removeAttribute('aria-hidden'); s.loading = 'eager'; }
      else s.setAttribute('aria-hidden', 'true');
    });
    // a próxima já carrega antes de entrar
    slides[(current + 1) % slides.length].loading = 'eager';
    if (status) status.textContent = 'Foto ' + (current + 1) + ' de ' + slides.length;
  }

  function play() {
    stop();
    if (reduce || paused) return;
    timer = window.setInterval(function () { show(current + 1); }, 5000);
  }
  function stop() { if (timer) { window.clearInterval(timer); timer = null; } }

  prev.addEventListener('click', function () { show(current - 1); play(); });
  next.addEventListener('click', function () { show(current + 1); play(); });

  root.addEventListener('mouseenter', function () { paused = true; stop(); });
  root.addEventListener('mouseleave', function () { paused = false; play(); });
  root.addEventListener('focusin', function () { paused = true; stop(); });
  root.addEventListener('focusout', function () { paused = false; play(); });

  // arrasto lateral (toque)
  var x0 = null;
  root.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
  root.addEventListener('touchend', function (e) {
    if (x0 === null) return;
    var dx = e.changedTouches[0].clientX - x0;
    x0 = null;
    if (Math.abs(dx) > 40) { show(current + (dx < 0 ? 1 : -1)); play(); }
  });

  document.addEventListener('visibilitychange', function () { if (document.hidden) stop(); else play(); });

  show(0);
  play();
})();
