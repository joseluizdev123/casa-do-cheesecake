/* 09 — Restaurantes (Figma 7057:508): entrada da moto.
 * A moto começa recuada (atrás do título, menor e transparente) e, quando o
 * banner entra na tela, avança e freia exatamente na posição do Figma.
 * - Toca uma vez a cada chegada na seção: quando a seção sai TOTALMENTE da tela
 *   (para cima ou para baixo), a moto volta ao ponto de partida sem transição
 *   (fora da vista, sem "dar ré") e anima de novo na próxima chegada.
 * - Sem JS ou com prefers-reduced-motion: a moto já aparece parada no lugar.
 */
(function () {
  'use strict';

  var sections = document.querySelectorAll('.restaurantes');
  if (!sections.length || !('IntersectionObserver' in window)) return;
  if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  // Chegada: banner ≥ 45% visível → anima.
  var enter = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) entry.target.closest('.restaurantes').classList.add('is-inview');
    });
  }, { threshold: 0.45 });

  // Saída: seção inteira fora da tela (inclui o padding acima do banner, onde a
  // moto vaza 46px) → rearma sem transição.
  var leave = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      var section = entry.target;
      if (entry.isIntersecting || !section.classList.contains('is-inview')) return;
      section.classList.add('is-resetting');
      section.classList.remove('is-inview');
      void section.offsetWidth; // aplica o estado inicial antes de religar a transição
      section.classList.remove('is-resetting');
    });
  }, { threshold: 0 });

  Array.prototype.forEach.call(sections, function (section) {
    var banner = section.querySelector('.restaurantes__banner');
    if (!banner) return;
    // arma no ponto de partida sem animar (evita "ré" visível se a página abrir na seção)
    section.classList.add('is-resetting', 'has-moto-anim');
    void section.offsetWidth;
    section.classList.remove('is-resetting');
    enter.observe(banner);
    leave.observe(section);
  });
})();
