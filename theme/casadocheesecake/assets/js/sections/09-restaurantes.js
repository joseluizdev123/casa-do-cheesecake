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

/* 09 — Restaurantes: coreografia de entrada (ver MOTION.md)
 *
 *   banner amarelo  scale .96 + fade ........ 0ms   900ms
 *   moto            entra dirigindo (bloco acima, CSS da seção) ...... 0ms  1400ms
 *   título          mask .................... 120ms  950ms
 *   texto · botão   up ...................... 380ms + 100ms  750ms  (< 768: 220ms + 90ms)
 *   selo            pop quando a moto freia . 700ms  600ms   (fim ≈ 1.3s)
 *
 * ≥ 768 o banner é uma faixa só: ele dispara a seção inteira (a moto dispara
 * junto, pelo próprio observador, a 45% do banner à vista). < 768 (uma coluna,
 * mais alta que a tela): banner + título entram juntos; texto e botão quando
 * chegam na tela.
 * Selo: é UMA imagem (anel de texto + ícone no mesmo SVG) → só pop, sem giro.
 * Na chegada normal ele acompanha a moto (CSS: .has-moto-anim → .is-inview, com
 * atraso até a frenagem; repete a cada nova chegada, como a moto). Se a página
 * já abre com a seção à vista a moto fica parada (não é armada) — aí o pop do
 * selo vem do motor, no mesmo tempo da coreografia.
 * Estado final = layout atual; só opacity/translate/scale/rotate/clip-path.
 */
(function () {
  'use strict';

  var M = window.cdcMotion;
  if (!M || M.reduced) return;

  var mobile = window.matchMedia && window.matchMedia('(max-width: 767.98px)').matches;

  Array.prototype.forEach.call(document.querySelectorAll('.restaurantes'), function (section) {
    var banner = section.querySelector('.restaurantes__banner');
    if (!banner) return;
    var title = section.querySelector('.restaurantes__title');
    var content = section.querySelectorAll('.restaurantes__content > *');
    var selo = section.querySelector('.restaurantes__selo');

    M.reveal(banner, { variant: 'scale', scale: 0.96, duration: 900 });

    // mask recorta o próprio título: quem dispara é o banner (ver 08-entrega.js)
    M.reveal(title, { variant: 'mask', duration: 950, delay: 120, trigger: banner });

    M.reveal(content, {
      variant: 'up', duration: 750, delay: mobile ? 220 : 380, stagger: mobile ? 90 : 100,
      trigger: mobile ? null : banner, threshold: mobile ? 0.2 : null
    });

    // Seção já à vista ao carregar → a moto não é armada (bloco acima): o selo
    // entra pelo motor. Caso contrário quem cuida dele é o CSS da seção.
    var r = section.getBoundingClientRect();
    var visibleAtLoad = r.bottom > 0 && r.top < (window.innerHeight || document.documentElement.clientHeight);
    if (selo && visibleAtLoad) {
      M.reveal(selo, { variant: 'pop', duration: 600, delay: 700, trigger: banner });
    }
  });
})();
