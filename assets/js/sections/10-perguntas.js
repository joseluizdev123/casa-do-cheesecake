/* 10 · Perguntas frequentes — acordeão acessível (Figma 7057:763)
   - Progressive enhancement: sem JS todas as respostas ficam visíveis.
   - Estado inicial vem do markup (aria-expanded): 1ª aberta, demais fechadas.
   - Cada pergunta abre/fecha de forma independente (frame 7057:1039 mostra todas abertas).
   - Teclado: Enter/Espaço (nativo do <button>), ↑/↓ entre perguntas, Home/End. */
(function () {
  'use strict';

  var roots = document.querySelectorAll('[data-faq]');

  Array.prototype.forEach.call(roots, function (root) {
    var triggers = Array.prototype.slice.call(root.querySelectorAll('.faq__trigger'));
    if (!triggers.length) return;

    var setOpen = function (trigger, open) {
      var item = trigger.closest('.faq__item');
      trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (item) item.classList.toggle('is-open', open);
    };

    triggers.forEach(function (trigger, index) {
      setOpen(trigger, trigger.getAttribute('aria-expanded') === 'true');

      trigger.addEventListener('click', function () {
        setOpen(trigger, trigger.getAttribute('aria-expanded') !== 'true');
      });

      trigger.addEventListener('keydown', function (event) {
        var target = null;
        switch (event.key) {
          case 'ArrowDown': target = triggers[(index + 1) % triggers.length]; break;
          case 'ArrowUp': target = triggers[(index - 1 + triggers.length) % triggers.length]; break;
          case 'Home': target = triggers[0]; break;
          case 'End': target = triggers[triggers.length - 1]; break;
          default: return;
        }
        event.preventDefault();
        target.focus();
      });
    });

    root.classList.add('is-enhanced');
    // Liga a animação só depois do primeiro layout (evita animar o colapso inicial).
    window.requestAnimationFrame(function () {
      window.requestAnimationFrame(function () { root.classList.add('is-animated'); });
    });
  });
})();
