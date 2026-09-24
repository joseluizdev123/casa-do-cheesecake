/* 11 — CTA final ("Peça o seu cheesecake hoje"): coreografia (ver MOTION.md)
 *
 *   selo (anel gira sem parar no CSS) .. pop ............ 0ms    800ms
 *   título ............................. mask ........... 150ms  1000ms
 *   texto .............................. up ............. 350ms  800ms
 *   botão · "ou" · link WhatsApp ....... up, cascata .... 450ms  700ms
 *   fatia .............................. cai de cima conforme a página rola
 *                                        (pedido do cliente) — pousa na
 *                                        posição do Figma quando o topo da
 *                                        seção chega a 12% da tela.
 * Estado final = layout atual (só opacity/translate/scale/rotate/clip-path).
 */
(function () {
  'use strict';
  var M = window.cdcMotion;
  if (!M || M.reduced) return;

  var section = document.querySelector('.cta-final');
  if (!section) return;
  var head = section.querySelector('.cta-final__head');
  var selo = section.querySelector('.cta-final__selo');
  var title = section.querySelector('.cta-final__title');
  var text = section.querySelector('.cta-final__text');
  var fatia = section.querySelector('.cta-final__fatia');
  var actions = Array.prototype.slice.call(section.querySelectorAll('.cta-final__actions > *'));

  // estado inicial aplicado sem transição (a seção pode já estar na tela ao carregar)
  var all = [selo, title, text].concat(actions).filter(Boolean);
  all.forEach(function (el) { el.style.setProperty('transition', 'none', 'important'); });

  // o título começa recortado (mask): quem dispara é o bloco que o contém
  M.reveal(selo, { variant: 'pop', duration: 800, scale: 0.6, trigger: head });
  M.reveal(title, { variant: 'mask', duration: 1000, delay: 150, trigger: head });
  M.reveal(text, { variant: 'up', duration: 800, delay: 350, trigger: head });
  if (actions.length) M.reveal(actions, { variant: 'up', duration: 700, delay: 450, stagger: 90, trigger: actions[0] });

  void section.offsetHeight;
  all.forEach(function (el) { el.style.removeProperty('transition'); });

  // A fatia cai de cima, devagar, acompanhando a rolagem
  if (fatia) M.fall(fatia, { section: section, rotate: -22, endAt: 0.12 });
})();
