/* Seção 01 — Hero: padrão de traços animado (Figma 8160:174) */
(function () {
  'use strict';

  /* Hero pattern (8160:174)
     - cada traço pisca em loop sutil (fade de opacidade, tempo aleatório) — só enquanto o data-loop da
       biblioteca Motion estiver ligado (até 5 s a cada entrada na tela, eventos motion:loop-start/stop);
       ao parar, cada traço termina o ciclo em que está e fica no quadro do Figma (sem salto)
     - perto do cursor o traço acende por completo, com transição suave */
  var pattern = document.querySelector('[data-dash-pattern]');
  if (!pattern) return;

  var LIGHT_RADIUS = 72;
  var imgs = Array.prototype.slice.call(pattern.querySelectorAll('.hero__dash-row img'));

  // Troca cada <img> pelo SVG inline (mesmo arquivo, sem edição) para animar traço a traço
  Promise.all(imgs.map(function (img) {
    return fetch(img.getAttribute('src'))
      .then(function (r) { return r.text(); })
      .then(function (txt) {
        var doc = new DOMParser().parseFromString(txt, 'image/svg+xml');
        var svg = doc.documentElement;
        svg.setAttribute('aria-hidden', 'true');
        svg.setAttribute('focusable', 'false');
        img.replaceWith(document.importNode(svg, true));
      })
      .catch(function () { /* mantém o <img> estático */ });
  })).then(init);

  function init() {
    var paths = Array.prototype.slice.call(pattern.querySelectorAll('path'));
    paths.forEach(function (p) {
      var o = parseFloat(p.getAttribute('opacity') || '1');
      p.removeAttribute('opacity');
      p.style.setProperty('--o', o);
      p.style.setProperty('--d', (2.8 + Math.random() * 3.6).toFixed(2) + 's');
      p.style.setProperty('--delay', (-Math.random() * 6).toFixed(2) + 's');
    });
    pattern.classList.add('is-live');

    // Pisca só com o loop ligado; ao parar, cada traço completa o ciclo atual (0% = 100% = opacidade do Figma)
    var blink = function () {
      pattern.classList.remove('is-blinking');
      void pattern.offsetWidth; // reinicia as animações
      pattern.classList.add('is-blinking');
    };
    var settle = function () {
      if (!pattern.getAnimations) { pattern.classList.remove('is-blinking'); return; }
      pattern.getAnimations({ subtree: true }).forEach(function (a) {
        if (a.animationName !== 'dash-blink') return;
        var it = a.effect.getComputedTiming().currentIteration;
        a.effect.updateTiming({ iterations: (it == null ? 0 : it) + 1 });
      });
    };
    pattern.addEventListener('motion:loop-start', blink);
    pattern.addEventListener('motion:loop-stop', settle);
    if (pattern.classList.contains('is-looping')) blink(); // o loop pode ter ligado antes deste script

    var area = pattern.parentElement; // hero__main
    var centers = [];
    var measure = function () {
      centers = paths.map(function (p) {
        var r = p.getBoundingClientRect();
        return { el: p, x: r.left + r.width / 2 + window.scrollX, y: r.top + r.height / 2 + window.scrollY };
      });
    };
    measure();
    window.addEventListener('resize', measure);

    var raf = 0, mx = -1e4, my = -1e4;
    var update = function () {
      raf = 0;
      var r2 = LIGHT_RADIUS * LIGHT_RADIUS;
      for (var i = 0; i < centers.length; i++) {
        var c = centers[i], dx = c.x - mx, dy = c.y - my;
        c.el.classList.toggle('is-lit', dx * dx + dy * dy < r2);
      }
    };
    // Só com mouse: no celular o toque dispara mousemove de compatibilidade e deixaria traços acesos
    area.addEventListener('pointermove', function (e) {
      if (e.pointerType !== 'mouse') return;
      mx = e.pageX; my = e.pageY;
      if (!raf) raf = requestAnimationFrame(update);
    });
    area.addEventListener('pointerleave', function (e) {
      if (e.pointerType !== 'mouse') return;
      mx = my = -1e4;
      if (!raf) raf = requestAnimationFrame(update);
    });
  }
})();
