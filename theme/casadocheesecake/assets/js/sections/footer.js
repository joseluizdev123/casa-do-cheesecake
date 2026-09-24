/* Footer — coreografia de entrada sutil (é o fim da página; ver MOTION.md)
 *
 *   logo circular ........ pop ............ 0ms    800ms
 *   frase da marca ....... up ............. 150ms  750ms
 *   colunas de links ..... up, cascata .... 200ms  +90ms  700ms
 *   copyright / crédito .. fade ........... 500ms  700ms
 * Estado final = layout atual.
 */
(function () {
  'use strict';
  var M = window.cdcMotion;
  if (!M || M.reduced) return;

  var footer = document.querySelector('.site-footer');
  if (!footer) return;
  var q = function (s) { return footer.querySelector(s); };
  var qa = function (s) { return Array.prototype.slice.call(footer.querySelectorAll(s)); };

  var logo = q('.site-footer__logo');
  var tagline = q('.site-footer__tagline');
  var cols = qa('.site-footer__col');
  var bottom = q('.site-footer__bottom');
  var main = q('.site-footer__main') || footer;

  var all = [logo, tagline, bottom].concat(cols).filter(Boolean);
  all.forEach(function (el) { el.style.setProperty('transition', 'none', 'important'); });

  M.reveal(logo, { variant: 'pop', scale: 0.7, duration: 800, trigger: main, threshold: 0.1 });
  M.reveal(tagline, { variant: 'up', duration: 750, delay: 150, trigger: main, threshold: 0.1 });
  if (cols.length) M.reveal(cols, { variant: 'up', duration: 700, delay: 200, stagger: 90 });
  M.reveal(bottom, { variant: 'fade', duration: 700, delay: 300 });

  void footer.offsetHeight;
  all.forEach(function (el) { el.style.removeProperty('transition'); });
})();
