/* 09 — Restaurantes (Figma 7057:508): entrada da moto.
 * Adição deliberada (não existe motion no Figma): quando o banner chega na tela,
 * a moto avança de trás do título e freia exatamente na posição do Figma.
 * - O estado de repouso (sem classes) É o layout do Figma. A animação só é
 *   "armada" (.has-moto-anim = ponto de partida) enquanto a seção está TOTALMENTE
 *   fora da tela, então nada salta/some diante do usuário.
 * - Toca a cada chegada: ao sair totalmente da tela a moto é rearmada sem
 *   transição (fora da vista, sem "dar ré") e anima de novo na próxima chegada.
 * - Se a página já abre com a seção visível, a moto fica parada no lugar (não há
 *   chegada); a animação passa a valer a partir da próxima saída/chegada.
 * - Sem JS, sem IntersectionObserver ou com prefers-reduced-motion: moto parada.
 */
(function () {
  'use strict';

  var sections = document.querySelectorAll('.restaurantes');
  if (!sections.length || !('IntersectionObserver' in window)) return;
  if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  // Arma no ponto de partida sem transição (só chamado com a seção fora da tela).
  function arm(section) {
    section.classList.add('is-resetting', 'has-moto-anim');
    section.classList.remove('is-inview');
    void section.offsetWidth; // aplica o estado inicial antes de religar a transição
    section.classList.remove('is-resetting');
  }

  // Chegada: banner ≥ 45% visível e animação armada → anima até o repouso.
  var enter = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      var section = entry.target.closest('.restaurantes');
      if (entry.isIntersecting && section.classList.contains('has-moto-anim')) {
        section.classList.add('is-inview');
      }
    });
  }, { threshold: 0.45 });

  // Saída: seção inteira fora da tela (inclui o padding acima do banner, onde a
  // moto vaza 46px) → arma/rearma. A 1ª chamada informa o estado inicial: se a
  // seção começa visível, nada é armado e a moto fica no lugar.
  var leave = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (!entry.isIntersecting) arm(entry.target);
    });
  }, { threshold: 0 });

  Array.prototype.forEach.call(sections, function (section) {
    var banner = section.querySelector('.restaurantes__banner');
    if (!banner) return;
    leave.observe(section);
    enter.observe(banner);
  });
})();
