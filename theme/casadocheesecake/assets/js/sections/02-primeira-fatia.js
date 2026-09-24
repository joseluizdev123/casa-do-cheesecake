/* 02 — Primeira fatia: coreografia de entrada (ver MOTION.md)
 *
 *   banner amarelo  scale .96 + fade ........ 0ms   900ms
 *   título          mask .................... 120ms  950ms
 *   fatia de trás   pop, sobe da esquerda ... 220ms  1050ms  → float 8px / 6.8s
 *   fatia da frente pop, sobe da direita .... 340ms  1000ms  → float 6px / 5.6s (fase oposta)
 *   subtítulo · condições · botão  up ...... 380ms + 100ms  750ms
 *
 * ≥ 768 tudo está numa faixa só: o banner dispara a seção inteira (uma
 * coreografia). < 768 (uma coluna, mais alta que a tela): banner + título
 * entram juntos e o texto e as fatias entram quando chegam na tela — com
 * atrasos que mantêm a ordem título → texto → fatias se tudo entrar junto
 * (ex.: link âncora).
 * Pontos de partida das fatias (giro/deslocamento) em sections/02-primeira-fatia.css.
 * Estado final = layout atual; só opacity/translate/scale/rotate/clip-path.
 */
(function () {
  'use strict';

  var M = window.cdcMotion;
  if (!M || M.reduced) return;

  var section = document.querySelector('.primeira-fatia');
  if (!section) return;

  var box = section.querySelector('.primeira-fatia__box');
  var title = section.querySelector('.primeira-fatia__title');
  var group = section.querySelector('.primeira-fatia__images');
  var back = section.querySelector('.primeira-fatia__img--background');
  var front = section.querySelector('.primeira-fatia__img--foreground');
  var content = section.querySelectorAll('.primeira-fatia__content > *');
  if (!box) return;

  var mobile = window.matchMedia('(max-width: 767.98px)').matches;
  var sectionTrigger = mobile ? null : box;
  var boxThreshold = mobile ? 0.1 : 0.2;     // banner mobile ~650px: o título já está na tela

  // Depois da entrada, flutuação sutil e defasada (começa no meio do ciclo,
  // em translate 0 — sem salto; uma desce, a outra sobe).
  function float(amp, dur, phase) {
    return function (el) {
      M.ambient(el, 'float', {
        '--float-amp': amp + 'px',
        '--float-dur': dur + 's',
        '--float-delay': (-phase * dur).toFixed(2) + 's'
      });
    };
  }

  // O ponto de partida (escondido) entra sem transição: se o navegador já
  // calculou o estilo antes deste script (seção na tela ao carregar — link
  // âncora, recarregar no meio da página, preview /s/), a transição do motor
  // levaria visível → escondido e "comeria" a entrada.
  var all = [box, title, back, front].concat(Array.prototype.slice.call(content)).filter(Boolean);
  all.forEach(function (el) { el.style.setProperty('transition', 'none', 'important'); });

  M.reveal(box, { variant: 'scale', scale: 0.96, duration: 900, threshold: boxThreshold });

  M.reveal(title, { variant: 'mask', duration: 950, delay: 120, trigger: box, threshold: boxThreshold });

  M.reveal(back, {
    variant: 'pop', duration: 1050, delay: mobile ? 240 : 220,
    trigger: sectionTrigger || group, threshold: mobile ? 0.3 : boxThreshold,
    onReveal: float(mobile ? 6 : 8, 6.8, 0.5)
  });
  M.reveal(front, {
    variant: 'pop', duration: 1000, delay: mobile ? 360 : 340,
    trigger: sectionTrigger || group, threshold: mobile ? 0.3 : boxThreshold,
    onReveal: float(mobile ? 5 : 6, 5.6, 1.5)
  });

  M.reveal(content, {
    variant: 'up', duration: 750, delay: mobile ? 220 : 380, stagger: mobile ? 90 : 100,
    trigger: sectionTrigger, threshold: mobile ? 0.2 : boxThreshold
  });

  all.forEach(function (el) { void window.getComputedStyle(el).opacity; });   // aplica já o estado inicial
  all.forEach(function (el) { el.style.removeProperty('transition'); });
})();
