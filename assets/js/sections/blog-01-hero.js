/* Blog 01 — Hero: luzes correndo pelas linhas convergentes do banner (Figma Light 8512:5748 sobre Lines 8512:5704)
   - cada luz parte da posição do Figma, segue a linha mais próxima até o fim e recomeça do início de outra linha
   - além das 10 do Figma, o JS acende mais luzes automáticas (AUTO no total), em pontos sorteados das linhas
   - a geometria vem do mesmo SVG que o <img> exibe (currentSrc: desktop ou mobile), lido sem editar o arquivo
   - clique (ou toque) no banner: nasce mais uma luz no ponto da linha mais próximo, corre até o fim e some;
     as automáticas continuam como estão
   - sem JS ou com prefers-reduced-motion: luzes paradas nas posições do Figma (CSS) */
(function () {
  'use strict';

  var banner = document.querySelector('[data-blog-hero-banner]');
  if (!banner || !window.fetch || !window.DOMParser || !window.requestAnimationFrame) return;

  var light = banner.querySelector('.blog-hero__light');
  var layers = Array.prototype.slice.call(banner.querySelectorAll('.blog-hero__curves'));
  var pulses = Array.prototype.slice.call(light.querySelectorAll('.blog-hero__pulse'));
  if (!light || !layers.length || !pulses.length) return;

  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)');
  var LOOP = 14;     // segundos, em média, para uma luz percorrer a linha inteira
  var STEP = 3;      // amostragem do caminho, em unidades do SVG
  var FADE = 0.035;  // fração do comprimento em que a luz acende/apaga nas pontas
  var AUTO = 30;      // luzes automáticas no total (as 10 do Figma + as criadas aqui)
  var BORN_IN = 0.4; // segundos para acender uma luz criada pelo JS (extra ou do clique)
  var CLICK_MAX = 24; // luzes de clique ao mesmo tempo (a mais antiga sai)

  var paths = [];    // { layer, pts: [[x, y], …] em unidades do SVG, len }
  var maps = [];     // por camada: { ox, oy, sx, sy, h, flip } — SVG → coordenadas do grupo Light
  var runs = [];     // por luz: { el, path, s, speed, w, h } — criadas pelo JS têm age (s); as do clique, once: true
  var layersInfo = null; // SVGs carregados (viewBox + amostras), para remedir no resize
  var sources = '';
  var raf = 0, last = 0, visible = false, busy = false;

  /* SVG da camada (o arquivo que o <picture> escolheu), num <svg> invisível só para medir */
  function loadLayer(layer, index) {
    var img = layer.querySelector('img');
    var src = img.currentSrc || img.src;
    return fetch(src)
      .then(function (r) { if (!r.ok) throw new Error(r.status); return r.text(); })
      .then(function (txt) {
        var doc = new DOMParser().parseFromString(txt, 'image/svg+xml');
        var svg = document.importNode(doc.documentElement, true);
        var box = document.createElement('div');
        box.setAttribute('aria-hidden', 'true');
        box.style.cssText = 'position:absolute;left:0;top:0;width:0;height:0;overflow:hidden;visibility:hidden;pointer-events:none';
        box.appendChild(svg);
        document.body.appendChild(box);
        var vb = svg.viewBox && svg.viewBox.baseVal;
        var out = { index: index, vbW: vb ? vb.width : +svg.getAttribute('width'), vbH: vb ? vb.height : +svg.getAttribute('height'), paths: [] };
        Array.prototype.forEach.call(svg.querySelectorAll('path'), function (p) {
          var len = p.getTotalLength();
          var pts = [];
          for (var s = 0; s < len; s += STEP) {
            var pt = p.getPointAtLength(s);
            pts.push([pt.x, pt.y]);
          }
          var end = p.getPointAtLength(len);
          pts.push([end.x, end.y]);
          out.paths.push({ layer: index, pts: pts, len: len });
        });
        box.remove();
        return out;
      });
  }

  /* SVG → grupo Light: posição e escala do <img> renderizado; a camada espelhada inverte o y */
  function measure(info) {
    var lb = light.getBoundingClientRect();
    maps = layers.map(function (layer, i) {
      var r = layer.querySelector('img').getBoundingClientRect();
      var d = info[i];
      return {
        ox: r.left - lb.left, oy: r.top - lb.top,
        sx: r.width / d.vbW, sy: r.height / d.vbH, h: d.vbH,
        flip: layer.classList.contains('blog-hero__curves--flip')
      };
    });
  }

  function at(path, s) {
    var m = maps[path.layer];
    var i = Math.min(path.pts.length - 2, Math.max(0, Math.floor(s / STEP)));
    var a = path.pts[i], b = path.pts[i + 1];
    var t = Math.min(1, Math.max(0, s / STEP - i));
    var x = a[0] + (b[0] - a[0]) * t, y = a[1] + (b[1] - a[1]) * t;
    var dx = (b[0] - a[0]) * m.sx, dy = (b[1] - a[1]) * m.sy;
    if (m.flip) { y = m.h - y; dy = -dy; }
    return { x: m.ox + x * m.sx, y: m.oy + y * m.sy, a: Math.atan2(dy, dx) * 180 / Math.PI };
  }

  /* Luz nova criada pelo JS (extra automática ou do clique), com o tamanho do traço da tela atual */
  function born(path, s, once) {
    var el = document.createElement('i');
    el.className = 'blog-hero__pulse';
    light.appendChild(el);
    var cs = getComputedStyle(el);
    return {
      el: el, path: path, s: s, w: parseFloat(cs.width), h: parseFloat(cs.height),
      speed: 1 / (LOOP * (0.8 + Math.random() * 0.45)), age: 0, once: !!once
    };
  }

  /* Ponto do caminho mais próximo do centro da luz (posição estática do Figma) */
  function nearest(cx, cy) {
    var best = { d: Infinity, path: paths[0], s: 0 };
    paths.forEach(function (path) {
      var m = maps[path.layer];
      for (var i = 0; i < path.pts.length; i++) {
        var y = m.flip ? m.h - path.pts[i][1] : path.pts[i][1];
        var dx = m.ox + path.pts[i][0] * m.sx - cx, dy = m.oy + y * m.sy - cy;
        var d = dx * dx + dy * dy;
        if (d < best.d) best = { d: d, path: path, s: Math.min(path.len, i * STEP) };
      }
    });
    return best;
  }

  function place(run) {
    var p = at(run.path, run.s);
    var f = FADE * run.path.len;
    var o = Math.max(0, Math.min(1, run.s / f, (run.path.len - run.s) / f));
    if (run.age != null) o *= Math.min(1, run.age / BORN_IN);
    run.el.style.transform = 'translate(' + (p.x - run.w / 2).toFixed(2) + 'px,' + (p.y - run.h / 2).toFixed(2) + 'px) rotate(' + p.a.toFixed(2) + 'deg)';
    run.el.style.opacity = o.toFixed(3);
  }

  function frame(now) {
    raf = 0;
    var dt = last ? Math.min(0.1, (now - last) / 1000) : 0;
    last = now;
    runs = runs.filter(function (run) {
      run.s += run.speed * run.path.len * dt;
      if (run.s >= run.path.len) {
        if (run.once) { run.el.remove(); return false; } // luz do clique: some no fim da linha
        run.path = paths[Math.floor(Math.random() * paths.length)]; // fim da linha: recomeça do início de outra linha
        run.s = 0;
      }
      if (run.age != null) run.age += dt;
      place(run);
      return true;
    });
    if (visible) raf = requestAnimationFrame(frame);
  }

  function start() {
    if (raf || !runs.length || !visible || reduce.matches) return;
    last = 0;
    raf = requestAnimationFrame(frame);
  }
  function stop() {
    if (raf) cancelAnimationFrame(raf);
    raf = 0;
  }

  /* Volta às posições do Figma (CSS) */
  function rest() {
    stop();
    runs.forEach(function (run) { if (run.age != null) run.el.remove(); }); // extras e cliques
    light.classList.remove('is-flowing');
    banner.classList.remove('is-clickable');
    pulses.forEach(function (el) { el.style.transform = ''; el.style.opacity = ''; });
    runs = [];
  }

  function init() {
    if (busy) return;
    var src = layers.map(function (l) { var img = l.querySelector('img'); return img.currentSrc || img.src; }).join('|');
    if (src === sources && runs.length) return;
    busy = true;
    rest();
    Promise.all(layers.map(loadLayer)).then(function (info) {
      busy = false;
      sources = src;
      paths = [];
      info.forEach(function (d) { paths = paths.concat(d.paths); });
      measure(info);
      layersInfo = info;
      if (reduce.matches) return;
      // Centro estático de cada luz (left/top do CSS + metade do traço), antes de ligar o modo em movimento
      runs = pulses.map(function (el) {
        var cs = getComputedStyle(el);
        var w = parseFloat(cs.width), h = parseFloat(cs.height);
        var hit = nearest(parseFloat(cs.left) + w / 2, parseFloat(cs.top) + h / 2);
        return { el: el, path: hit.path, s: hit.s, w: w, h: h, speed: 1 / (LOOP * (0.8 + Math.random() * 0.45)) };
      });
      light.classList.add('is-flowing');
      for (var n = runs.length; n < AUTO; n++) { // extras: linha e ponto sorteados
        var path = paths[Math.floor(Math.random() * paths.length)];
        runs.push(born(path, Math.random() * path.len));
      }
      runs.forEach(place);
      banner.classList.add('is-clickable');
      start();
    }).catch(function () { busy = false; rest(); }); // sem rede: luzes paradas
  }

  /* Clique no banner: uma luz a mais, no ponto da linha mais próximo do clique */
  banner.addEventListener('click', function (e) {
    if (!runs.length || reduce.matches) return; // linhas ainda não medidas ou movimento reduzido: nada
    var lb = light.getBoundingClientRect();
    var hit = nearest(e.clientX - lb.left, e.clientY - lb.top);
    var clicks = runs.filter(function (run) { return run.once; });
    if (clicks.length >= CLICK_MAX) { clicks[0].el.remove(); runs.splice(runs.indexOf(clicks[0]), 1); }
    var run = born(hit.path, hit.s, true);
    runs.push(run);
    place(run);
    start();
  });

  var resizeTimer = 0;
  window.addEventListener('resize', function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function () {
      var src = layers.map(function (l) { var img = l.querySelector('img'); return img.currentSrc || img.src; }).join('|');
      if (src !== sources) { init(); return; } // trocou desktop ↔ mobile
      if (layersInfo && runs.length) { measure(layersInfo); runs.forEach(place); }
    }, 150);
  });

  var onReduce = function () { if (reduce.matches) rest(); else init(); };
  if (reduce.addEventListener) reduce.addEventListener('change', onReduce); else if (reduce.addListener) reduce.addListener(onReduce);

  // Só anima com o banner na tela
  if ('IntersectionObserver' in window) {
    new IntersectionObserver(function (entries) {
      visible = entries[0].isIntersecting;
      if (visible) start(); else stop();
    }).observe(banner);
  } else {
    visible = true;
  }

  // Começa assim que os <img> escolheram o arquivo (currentSrc) — não espera o load da página (fontes externas)
  var imgs = layers.map(function (l) { return l.querySelector('img'); });
  var boot = function () {
    if (!reduce.matches && imgs.every(function (img) { return img.currentSrc || img.complete; })) init();
  };
  boot();
  imgs.forEach(function (img) { img.addEventListener('load', boot); });
})();
