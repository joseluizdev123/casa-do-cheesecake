/* A Casa do Cheesecake — motor de animação (ver MOTION.md)
 *
 * API global usada pelos scripts de seção (assets/js/sections/*.js):
 *
 *   cdcMotion.reveal(target, opts)      entrada ao aparecer na tela
 *     target: seletor | Element | NodeList/array
 *     opts: { variant: 'up'|'down'|'left'|'right'|'fade'|'scale'|'zoom'|'pop'|'mask',
 *             delay: ms, duration: ms, distance: px, scale: n, stagger: ms (lista),
 *             trigger: Element (quem dispara; padrão = o próprio alvo / 1º da lista),
 *             threshold: 0–1 (padrão .15), onReveal: fn(el) }
 *   cdcMotion.counter(el, opts)         número conta de 0 até o valor do texto
 *     opts: { duration: ms (1600), delay: ms, trigger: Element }
 *   cdcMotion.parallax(el, opts)        desloca no scroll (propriedade translate)
 *     opts: { speed: .12, max: 40 }  — não combine com reveal que use translate no MESMO elemento
 *   cdcMotion.ambient(el, 'float'|'spin', vars)   movimento contínuo (classe + CSS vars)
 *   cdcMotion.fall(el, { section, measure, rotate, endAt })  cai de cima conforme a rolagem
 *   cdcMotion.onEnter(el, fn, { threshold, once })
 *   cdcMotion.reduced                  true se o usuário pediu menos movimento
 *
 * Regras: o estado final é sempre o layout atual (só opacity/translate/scale/
 * rotate/clip-path animam). Com prefers-reduced-motion nada é escondido ou
 * animado. Um timeout de segurança no <head> mostra tudo se este arquivo
 * não carregar.
 */
(function () {
  'use strict';

  var root = document.documentElement;
  var mqReduce = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)') : { matches: false };
  var reduced = !!mqReduce.matches || !('IntersectionObserver' in window);
  var noop = function () {};

  function toList(target) {
    if (!target) return [];
    if (typeof target === 'string') return Array.prototype.slice.call(document.querySelectorAll(target));
    if (target instanceof Element) return [target];
    return Array.prototype.slice.call(target).filter(Boolean);
  }

  // ---------- Observador compartilhado ----------
  var watchers = new Map();   // Element -> [{ fn, once, threshold }]
  var observers = {};         // threshold -> IntersectionObserver
  function observer(threshold) {
    var key = String(threshold);
    if (observers[key]) return observers[key];
    observers[key] = new IntersectionObserver(function (entries, obs) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        var list = watchers.get(entry.target) || [];
        var keep = [];
        list.forEach(function (w) {
          if (String(w.threshold) !== key) { keep.push(w); return; }
          w.fn(entry.target);
          if (!w.once) keep.push(w);
        });
        if (keep.length) watchers.set(entry.target, keep);
        else { watchers.delete(entry.target); obs.unobserve(entry.target); }
      });
    }, { threshold: threshold, rootMargin: '0px 0px -8% 0px' });
    return observers[key];
  }

  // No fim da página o observador (margem -8% embaixo) nunca "vê" o que está
  // colado no rodapé: ao chegar ao fim, dispara tudo o que já está na tela.
  var flushTicking = false;
  function flushAtEnd() {
    flushTicking = false;
    if (window.innerHeight + window.scrollY < document.documentElement.scrollHeight - 4) return;
    var vh = window.innerHeight;
    watchers.forEach(function (list, el) {
      var r = el.getBoundingClientRect();
      if (r.top >= vh || r.bottom <= 0 || !r.width) return;
      list.forEach(function (w) { w.fn(el); });
      watchers.delete(el);
      Object.keys(observers).forEach(function (k) { observers[k].unobserve(el); });
    });
  }
  window.addEventListener('scroll', function () {
    if (!flushTicking) { flushTicking = true; window.requestAnimationFrame(flushAtEnd); }
  }, { passive: true });

  function onEnter(el, fn, opts) {
    opts = opts || {};
    if (!el) return;
    if (reduced) { fn(el); return; }
    var t = opts.threshold == null ? 0.15 : opts.threshold;
    var list = watchers.get(el) || [];
    list.push({ fn: fn, once: opts.once !== false, threshold: t });
    watchers.set(el, list);
    observer(t).observe(el);
  }

  // ---------- Entradas ----------
  function finish(el) {
    el.classList.remove('is-revealed');
    el.removeAttribute('data-reveal');
    el.style.removeProperty('--reveal-delay');
    el.style.removeProperty('--reveal-dur');
    el.style.removeProperty('--reveal-distance');
    el.style.removeProperty('--reveal-scale');
  }

  function reveal(target, opts) {
    opts = opts || {};
    var els = toList(target);
    if (!els.length || reduced) return els;
    var variant = opts.variant || 'up';
    var dur = opts.duration || null;
    var baseDelay = opts.delay || 0;
    var stagger = opts.stagger || 0;
    var trigger = opts.trigger || null;

    els.forEach(function (el, i) {
      if (el.hasAttribute('data-reveal')) return;          // já registrado
      var delay = baseDelay + i * stagger;
      el.setAttribute('data-reveal', variant);
      if (delay) el.style.setProperty('--reveal-delay', delay + 'ms');
      if (dur) el.style.setProperty('--reveal-dur', dur + 'ms');
      if (opts.distance != null) el.style.setProperty('--reveal-distance', opts.distance + 'px');
      if (opts.scale != null) el.style.setProperty('--reveal-scale', String(opts.scale));
    });

    var show = function (el) {
      if (!el.hasAttribute('data-reveal') || el.classList.contains('is-revealed')) return;
      var cs = window.getComputedStyle(el);
      var total = (parseFloat(cs.getPropertyValue('--reveal-delay')) || 0) +
        (parseFloat(cs.getPropertyValue('--reveal-dur')) || parseFloat(window.getComputedStyle(root).getPropertyValue('--motion-dur-2')) || 800);
      // força o estado inicial a ser pintado antes de revelar (entradas logo no load)
      window.requestAnimationFrame(function () {
        el.classList.add('is-revealed');
        window.setTimeout(function () {
          finish(el);
          if (opts.onReveal) opts.onReveal(el);
        }, total + 80);
      });
    };

    if (trigger) {
      onEnter(trigger, function () { els.forEach(show); }, { threshold: opts.threshold });
    } else if (stagger && els.length > 1) {
      // lista: o 1º que entrar dispara os ainda não revelados, em cascata a partir dele
      els.forEach(function (el) {
        onEnter(el, function () { els.forEach(show); }, { threshold: opts.threshold });
      });
    } else {
      els.forEach(function (el) { onEnter(el, show, { threshold: opts.threshold }); });
    }
    return els;
  }

  // ---------- Contadores ----------
  function counter(target, opts) {
    opts = opts || {};
    toList(target).forEach(function (el) {
      if (reduced) return;
      // primeiro nó de texto com número
      var walker = document.createTreeWalker(el, NodeFilter.SHOW_TEXT);
      var node = null, match = null;
      while (walker.nextNode()) {
        match = /(\d[\d.]*)/.exec(walker.currentNode.nodeValue);
        if (match) { node = walker.currentNode; break; }
      }
      if (!node) return;
      var raw = match[1];
      var to = parseInt(raw.replace(/\./g, ''), 10);
      if (!to || to < 2) return;
      var before = node.nodeValue.slice(0, match.index);
      var after = node.nodeValue.slice(match.index + raw.length);
      var original = node.nodeValue;

      var span = document.createElement('span');
      span.className = 'motion-count';
      span.textContent = raw;
      var parent = node.parentNode;
      var frag = document.createDocumentFragment();
      if (before) frag.appendChild(document.createTextNode(before));
      frag.appendChild(span);
      if (after) frag.appendChild(document.createTextNode(after));
      parent.replaceChild(frag, node);
      span.style.minWidth = span.getBoundingClientRect().width + 'px';   // reserva a largura final
      var fmt = function (n) { return raw.indexOf('.') > -1 ? n.toLocaleString('pt-BR') : String(n); };
      span.textContent = fmt(0);

      var restore = function () {
        var text = document.createTextNode(original);
        var first = span.previousSibling && span.previousSibling.nodeType === 3 && span.previousSibling.nodeValue === before ? span.previousSibling : null;
        var last = span.nextSibling && span.nextSibling.nodeType === 3 && span.nextSibling.nodeValue === after ? span.nextSibling : null;
        parent.insertBefore(text, first || span);
        if (first) parent.removeChild(first);
        if (last) parent.removeChild(last);
        parent.removeChild(span);
      };

      onEnter(opts.trigger || el, function () {
        var dur = opts.duration || 1600;
        window.setTimeout(function () {
          var start = null;
          var step = function (t) {
            if (start === null) start = t;
            var p = Math.min(1, (t - start) / dur);
            var eased = 1 - Math.pow(1 - p, 4);
            span.textContent = fmt(Math.round(eased * to));
            if (p < 1) window.requestAnimationFrame(step);
            else restore();
          };
          window.requestAnimationFrame(step);
        }, opts.delay || 0);
      }, { threshold: opts.threshold == null ? 0.4 : opts.threshold });
    });
  }

  // ---------- Parallax (rAF, só enquanto visível) ----------
  var parallaxItems = [];
  var parallaxTicking = false;
  function updateParallax() {
    parallaxTicking = false;
    var vh = window.innerHeight;
    parallaxItems.forEach(function (it) {
      if (!it.visible) return;
      var r = it.el.getBoundingClientRect();
      var center = r.top + r.height / 2 - vh / 2;
      var y = Math.max(-it.max, Math.min(it.max, -center * it.speed));
      it.el.style.translate = '0 ' + y.toFixed(1) + 'px';
    });
  }
  function requestParallax() {
    if (!parallaxTicking) { parallaxTicking = true; window.requestAnimationFrame(updateParallax); }
  }
  function parallax(target, opts) {
    opts = opts || {};
    if (reduced) return;
    toList(target).forEach(function (el) {
      var it = { el: el, speed: opts.speed == null ? 0.12 : opts.speed, max: opts.max == null ? 40 : opts.max, visible: false };
      parallaxItems.push(it);
      new IntersectionObserver(function (entries) {
        entries.forEach(function (e) { it.visible = e.isIntersecting; });
        requestParallax();
      }, { rootMargin: '20% 0px 20% 0px' }).observe(el);
    });
    if (parallaxItems.length === toList(target).length) {
      window.addEventListener('scroll', requestParallax, { passive: true });
      window.addEventListener('resize', requestParallax, { passive: true });
    }
  }

  // ---------- Queda ligada à rolagem ----------
  // O elemento começa acima da borda de cima da seção (a seção recorta com
  // overflow: clip no CSS dela) e desce conforme a página rola, até pousar na
  // posição do layout quando o topo da seção chega a `endAt` da altura da tela.
  // Reversível (rolar para cima faz subir). Inércia leve para ficar "devagar".
  // opts: { section, measure (elemento para medir a distância), rotate: -18, endAt: .12 }
  function fall(target, opts) {
    opts = opts || {};
    var els = toList(target);
    if (!els.length || reduced) return;
    var section = opts.section || els[0].closest('section') || els[0].parentElement;
    var measureEl = opts.measure || els[0];
    var rot = opts.rotate == null ? -18 : opts.rotate;
    var endAt = opts.endAt == null ? 0.12 : opts.endAt;
    var dist = 0, cur = null, raf = 0, near = true;

    function clear() {
      els.forEach(function (el) {
        el.style.removeProperty('translate');
        el.style.removeProperty('rotate');
        el.style.removeProperty('opacity');
      });
    }
    function measure() {
      clear();
      var s = section.getBoundingClientRect();
      var m = measureEl.getBoundingClientRect();
      dist = Math.max(0, m.bottom - s.top) + 24;      // até sumir acima da borda da seção
    }
    function goal() {
      var vh = window.innerHeight || 1;
      var r = section.getBoundingClientRect();
      return Math.max(0, Math.min(1, (vh - r.top) / (vh * (1 - endAt))));
    }
    function paint(p) {
      if (p >= 1) { clear(); return; }
      // descida proporcional à rolagem (devagar, o trajeto todo) e pouso suave
      // só nos últimos 15%
      var e = p < 0.85 ? p : 0.85 + (1 - Math.pow(1 - (p - 0.85) / 0.15, 2)) * 0.15;
      var y = -(1 - e) * dist;
      var r = (1 - e) * rot;
      var o = Math.min(1, p * 3);
      els.forEach(function (el) {
        el.style.translate = '0 ' + y.toFixed(1) + 'px';
        el.style.rotate = r.toFixed(2) + 'deg';
        el.style.opacity = o.toFixed(3);
      });
    }
    function tick() {
      raf = 0;
      var g = goal();
      if (cur === null) cur = g;
      cur += (g - cur) * 0.14;
      if (Math.abs(g - cur) < 0.0015) cur = g;
      paint(cur);
      if (cur !== g) raf = window.requestAnimationFrame(tick);
    }
    function request() { if (near && !raf) raf = window.requestAnimationFrame(tick); }

    measure();
    cur = goal();
    paint(cur);                                          // estado inicial já no lugar certo
    new IntersectionObserver(function (entries) {
      near = entries[entries.length - 1].isIntersecting;
      request();
    }, { rootMargin: '50% 0px 50% 0px' }).observe(section);
    window.addEventListener('scroll', request, { passive: true });
    window.addEventListener('resize', function () { measure(); cur = goal(); paint(cur); }, { passive: true });
  }

  // ---------- Movimento ambiente ----------
  function ambient(target, kind, vars) {
    if (reduced) return;
    toList(target).forEach(function (el) {
      el.classList.add(kind === 'spin' ? 'motion-spin' : 'motion-float');
      if (vars) Object.keys(vars).forEach(function (k) { el.style.setProperty(k, vars[k]); });
    });
  }

  window.cdcMotion = {
    ready: true,
    reduced: reduced,
    reveal: reveal,
    counter: counter,
    parallax: parallax,
    ambient: ambient,
    fall: fall,
    onEnter: onEnter,
  };
  window.clearTimeout(window.__cdcMotionSafety);

  // Sem motion (reduced / sem IntersectionObserver): nada fica escondido
  if (reduced) root.classList.remove('js-motion', 'motion-pending');

  // Scripts defer de seção rodam antes do DOMContentLoaded: aí as entradas já
  // estão registradas e o conteúdo pode aparecer (os elementos com entrada
  // continuam escondidos até animar).
  var release = function () {
    window.requestAnimationFrame(function () { root.classList.remove('motion-pending'); });
  };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', release);
  else release();
  // Se o usuário mudar a preferência com a página aberta: mostra tudo
  if (mqReduce.addEventListener) {
    mqReduce.addEventListener('change', function (e) {
      if (e.matches) {
        root.classList.remove('js-motion');
        document.querySelectorAll('[data-reveal]').forEach(finish);
      }
    });
  }
  void noop;
})();
