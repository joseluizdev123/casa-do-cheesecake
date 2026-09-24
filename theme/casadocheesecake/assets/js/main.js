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
})();
