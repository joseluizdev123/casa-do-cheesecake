/* 08 — Entrega: coreografia de entrada (ver MOTION.md)
 *
 * Linha do tempo (quando tudo entra junto na tela):
 *   0ms    título ............ mask 950ms
 *   150ms  item 1 ............ selo (círculo) pop 600ms → ícone balança (wiggle)
 *                              texto (título + descrição) up 750ms, +100ms
 *   +100ms itens 2, 3, 4 ..... mesma cascata
 *   650ms  botão ............. up 700ms           (fim ≈ 1.35s)
 *
 * Cada grupo dispara quando ELE entra na tela, mas respeita a cascata em
 * relação ao grupo anterior: se entram juntos (preview, desktop com a seção
 * inteira à vista), os atrasos acima valem; se entram depois (rolagem lenta,
 * mobile com os itens empilhados), começam na hora, sem espera artificial.
 * Nada escondido além do que já está na tela ao rolar — sem carrossel/aba aqui.
 * Estado final = layout atual (só opacity / translate / scale / rotate / clip-path).
 */
(function () {
  'use strict';
  var M = window.cdcMotion;
  if (!M || M.reduced) return;

  var section = document.querySelector('.entrega');
  if (!section) return;

  var title = section.querySelector('.entrega__title');
  // mask recorta o próprio título (clip-path 100%): o Chrome aplica esse recorte
  // no IntersectionObserver e ele nunca "entraria" — quem dispara é o header.
  var header = section.querySelector('.entrega__header') || (title && title.parentElement);
  var items = Array.prototype.slice.call(section.querySelectorAll('.entrega__item'));
  var button = section.querySelector('.entrega__body > .btn');

  // ---------- linha do tempo compartilhada pelos grupos ----------
  var last = null;   // { at: performance.now() do início efetivo, off: offset do grupo }

  function setDelay(el, ms) {
    el.style.setProperty('--reveal-delay', Math.max(0, Math.round(ms)) + 'ms');
  }

  /* group(trigger, off, parts, after)
   *   off   = posição do grupo na coreografia (ms desde o título)
   *   parts = [{ el, variant, duration, at (ms dentro do grupo), distance }]
   *   after = fn(extra) — efeitos extras quando o grupo começa
   * O ajuste do atraso é registrado ANTES do reveal no mesmo alvo/limiar, então
   * roda antes de o motor ler --reveal-delay e adicionar .is-revealed. */
  function group(trigger, off, parts, after) {
    parts = parts.filter(function (p) { return p.el; });
    if (!trigger || !parts.length) return;
    M.onEnter(trigger, function () {
      var now = performance.now();
      var start = last ? Math.max(now, last.at + (off - last.off)) : now;
      last = { at: start, off: off };
      var extra = start - now;
      parts.forEach(function (p) { setDelay(p.el, extra + (p.at || 0)); });
      if (after) after(extra);
    });
    parts.forEach(function (p) {
      var opts = { variant: p.variant, duration: p.duration, trigger: trigger };
      if (p.distance != null) opts.distance = p.distance;
      M.reveal(p.el, opts);
    });
  }

  // ---------- ícone: balanço leve (keyframe cdc-wiggle do motor) ----------
  function wiggle(icon) {
    if (!icon || icon.classList.contains('motion-wiggle')) return;
    icon.classList.add('motion-wiggle');
  }
  items.forEach(function (item) {
    var icon = item.querySelector('.entrega__icon');
    if (!icon) return;
    icon.addEventListener('animationend', function (e) {
      if (e.target === icon) icon.classList.remove('motion-wiggle');
    });
  });

  // ---------- coreografia ----------
  group(header, 0, [
    { el: title, variant: 'mask', duration: 950 }
  ]);

  items.forEach(function (item, i) {
    var badge = item.querySelector('.entrega__badge');
    var text = item.querySelector('.entrega__text');
    var icon = item.querySelector('.entrega__icon');
    group(item, 150 + i * 100, [
      { el: badge, variant: 'pop', duration: 600, at: 0 },
      { el: text, variant: 'up', duration: 750, at: 100 }
    ], function (extra) {
      // o selo já assentou (expo-out) quando o ícone balança
      window.setTimeout(function () { wiggle(icon); }, extra + 260);
    });
  });

  group(button, 150 + Math.max(0, items.length - 1) * 100 + 200, [
    { el: button, variant: 'up', duration: 700 }
  ]);

  // ---------- interação: o ícone balança ao passar o cursor no item ----------
  var fine = window.matchMedia && window.matchMedia('(hover: hover) and (pointer: fine)');
  if (fine && fine.matches) {
    items.forEach(function (item) {
      var icon = item.querySelector('.entrega__icon');
      item.addEventListener('mouseenter', function () { wiggle(icon); });
    });
  }
})();
