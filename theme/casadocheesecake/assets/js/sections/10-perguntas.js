/* 10 · Perguntas frequentes — acordeão acessível (Figma 7057:763)
   - Progressive enhancement: sem JS todas as respostas ficam visíveis.
   - Estado inicial vem da classe .is-open do markup: 1ª aberta, demais fechadas
     (aria-expanded é "true" em todas no HTML porque, sem JS, tudo fica visível).
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
      // Sem JS todas ficam abertas (aria-expanded="true" no markup); o estado
      // inicial do Figma vem da classe .is-open (só a 1ª pergunta).
      var item = trigger.closest('.faq__item');
      setOpen(trigger, !!item && item.classList.contains('is-open'));

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

/* ---- Motion (ver MOTION.md) ----
   Entrada: título sobe da máscara → perguntas em cascata (60ms, da 7ª em diante
   junto com a 6ª) → rodapé "Ainda tem dúvidas?" + botão logo depois da última.
   Só itens visíveis entram; respostas fechadas continuam só por interação
   (a abertura/fechamento já anima a altura via grid-template-rows).
   Interação: o círculo do + / − dá um quarto de volta na troca (sentido
   horário ao abrir, anti-horário ao fechar). Estado final = layout atual. */
(function () {
  'use strict';

  var M = window.cdcMotion;
  if (!M || M.reduced) return;

  var EASE = 'cubic-bezier(.16, 1, .3, 1)';   // --motion-ease
  var ITEMS_AT = 150;                         // 1ª pergunta entra 150ms depois do título
  var STAGGER = 60;
  var CAP = 5;                                // índice 5 = 6ª pergunta; as seguintes entram junto
  var CHAIN = ITEMS_AT + CAP * STAGGER + 70;  // rodapé: logo depois da última pergunta (520ms)

  Array.prototype.forEach.call(document.querySelectorAll('[data-faq]'), function (root) {
    var title = root.querySelector('.faq__title');
    var items = Array.prototype.slice.call(root.querySelectorAll('.faq__item'));
    var footer = root.querySelector('.faq__footer');
    var last = items[items.length - 1];
    var parts = footer ? [footer.querySelector('.faq__footer-text'), footer.querySelector('.btn')].filter(Boolean) : [];

    // O ponto de partida (escondido) entra sem transição: se o navegador já
    // calculou o estilo antes deste script (seção na tela ao carregar — preview
    // /s/, recarregar no meio da página, rede lenta), a transição do motor
    // levaria visível → escondido e "comeria" a entrada.
    var registered = [title].concat(items, parts).filter(Boolean);
    registered.forEach(function (el) { el.style.setProperty('transition', 'none', 'important'); });

    // Título Anton: sobe de dentro da máscara. O gatilho é a coluna do título:
    // com clip-path inset(0 0 100% 0) o próprio h2 tem área visível zero e o
    // IntersectionObserver do Chrome nunca o dá como visível.
    if (title) {
      M.reveal(title, {
        variant: 'mask', duration: 950,
        trigger: title.parentElement || root, threshold: 0.05,
      });
    }

    // Perguntas: a 1ª que aparecer dispara a lista inteira em cascata
    var cascadeStart = 0;
    items.forEach(function (item) {
      M.onEnter(item, function () { if (!cascadeStart) cascadeStart = Date.now(); });
    });
    M.reveal(items, { variant: 'up', duration: 700, delay: ITEMS_AT, stagger: STAGGER });
    items.slice(CAP + 1).forEach(function (item) {
      item.style.setProperty('--reveal-delay', (ITEMS_AT + CAP * STAGGER) + 'ms');
    });

    // Rodapé: disparado pela última pergunta (logo acima dele — assim ele também
    // entra quando está na faixa de baixo da tela no carregamento). Se a última
    // pergunta apareceu junto com a lista (tela alta / link #perguntas), o rodapé
    // espera a cascata; se apareceu rolando depois, entra quase de imediato.
    // Este onEnter é registrado antes do reveal no mesmo elemento e com o mesmo
    // threshold, então ajusta o atraso antes de a entrada começar.
    if (parts.length && last) {
      M.onEnter(last, function () {
        var since = cascadeStart ? Date.now() - cascadeStart : 0;
        var base = Math.max(60, CHAIN - since);
        parts.forEach(function (el, i) { el.style.setProperty('--reveal-delay', (base + i * 70) + 'ms'); });
      });
      M.reveal(parts, { variant: 'up', duration: 700, delay: CHAIN, stagger: 70, trigger: last });
    }

    void root.offsetHeight;   // aplica já o estado escondido, sem transição
    registered.forEach(function (el) { el.style.removeProperty('transition'); });

    // + / −: quarto de volta no círculo a cada troca (clique, Enter ou Espaço).
    // Roda depois do handler do acordeão (bolha), então aria-expanded já é o novo.
    root.addEventListener('click', function (event) {
      var trigger = event.target.closest && event.target.closest('.faq__trigger');
      if (!trigger || !root.contains(trigger)) return;
      var icon = trigger.querySelector('.faq__icon');
      if (!icon || typeof icon.animate !== 'function') return;
      var opened = trigger.getAttribute('aria-expanded') === 'true';
      try {
        icon.animate(
          [{ rotate: opened ? '-90deg' : '90deg' }, { rotate: '0deg' }],
          { duration: 480, easing: EASE }
        );
      } catch (e) { /* navegador sem a propriedade rotate: só troca o ícone */ }
    });
  });
})();
