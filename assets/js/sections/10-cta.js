/* Seção 10 — Call to action: padrão de traços animado (Figma 8160:1729), mesmo comportamento do hero */
(function () {
  'use strict';

  /* Pattern (8160:1729)
     - cada traço pisca em loop sutil (fade de opacidade, tempo aleatório)
     - perto do cursor o traço acende por completo, com transição suave */
  var pattern = document.querySelector('[data-cta-pattern]');
  if (!pattern) return;

  var LIGHT_RADIUS = 72;
  var imgs = Array.prototype.slice.call(pattern.querySelectorAll('.cta__dash-row img'));

  // Troca cada <img> pelo SVG inline (mesmo arquivo, sem edição) para animar traço a traço
  Promise.all(imgs.map(function (img) {
    return fetch(img.getAttribute('src'))
      .then(function (r) {
        if (!r.ok) throw new Error(r.status);
        return r.text();
      })
      .then(function (txt) {
        var doc = new DOMParser().parseFromString(txt, 'image/svg+xml');
        var svg = doc.documentElement;
        if (svg.nodeName.toLowerCase() !== 'svg') return;
        svg.setAttribute('aria-hidden', 'true');
        svg.setAttribute('focusable', 'false');
        img.replaceWith(document.importNode(svg, true));
      })
      .catch(function () { /* mantém o <img> estático */ });
  })).then(init);

  function init() {
    var paths = Array.prototype.slice.call(pattern.querySelectorAll('path'));
    if (!paths.length) return;
    paths.forEach(function (p) {
      var o = parseFloat(p.getAttribute('opacity') || '1');
      p.removeAttribute('opacity');
      p.style.setProperty('--o', o);
      p.style.setProperty('--d', (2.8 + Math.random() * 3.6).toFixed(2) + 's');
      p.style.setProperty('--delay', (-Math.random() * 6).toFixed(2) + 's');
    });
    pattern.classList.add('is-live');

    var area = pattern.parentElement; // cta__main
    var centers = [];
    var measure = function () {
      centers = paths.map(function (p) {
        var r = p.getBoundingClientRect();
        return { el: p, x: r.left + r.width / 2 + window.scrollX, y: r.top + r.height / 2 + window.scrollY };
      });
    };

    var raf = 0, mx = -1e4, my = -1e4, stale = true;
    var update = function () {
      raf = 0;
      if (stale) { measure(); stale = false; }
      var r2 = LIGHT_RADIUS * LIGHT_RADIUS;
      for (var i = 0; i < centers.length; i++) {
        var c = centers[i], dx = c.x - mx, dy = c.y - my;
        c.el.classList.toggle('is-lit', dx * dx + dy * dy < r2);
      }
    };
    // Mede sob demanda: a seção fica no fim da página e o layout acima pode mudar
    window.addEventListener('resize', function () { stale = true; });
    area.addEventListener('mouseenter', function () { stale = true; });
    area.addEventListener('mousemove', function (e) {
      mx = e.pageX; my = e.pageY;
      if (!raf) raf = requestAnimationFrame(update);
    });
    area.addEventListener('mouseleave', function () {
      mx = my = -1e4;
      if (!raf) raf = requestAnimationFrame(update);
    });
  }
})();
