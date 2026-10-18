/* Blog 01 — Hero: luzes correndo pelas linhas convergentes do banner (Figma Light 8512:5748 sobre Lines 8512:5704)
   - em repouso as 10 luzes ficam nas posições do Figma (CSS)
   - o banner tem data-loop: enquanto o motion.js mantém .is-looping, as 10 do Figma partem da sua posição e o JS
     acende mais luzes (AUTO no total), em pontos sorteados das linhas; cada luz segue a linha até o fim e recomeça do
     início de outra
   - quando o loop acaba, as automáticas apagam (SETTLE) e as 10 do Figma reacendem no lugar do repouso
   - clique (ou toque) no banner, a qualquer momento, durante o loop ou fora dele: o "tiro" da pessoa. Um anel abre no
     ponto da linha mais próximo e dali sai uma luz mais longa, grossa e brilhante que as automáticas (--shot), que
     acende na hora, corre SHOT_SPEED× mais rápido até o fim da linha e some
   - a geometria vem do mesmo SVG que o <img> exibe (currentSrc: desktop ou mobile), lido sem editar o arquivo
   - sem JS, sem o motion.js, com prefers-reduced-motion e na impressão: luzes paradas nas posições do Figma */
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
  var BORN_IN = 0.4; // segundos para acender uma luz extra
  var CLICK_MAX = 24; // tiros ao mesmo tempo (o mais antigo sai)
  var SHOT_IN = 0.06; // segundos para o tiro acender (na hora, para a pessoa ver que foi ela)
  var SHOT_SPEED = 3; // o tiro corre 3× mais rápido que as automáticas
  var SETTLE = 0.6;  // segundos para as automáticas apagarem quando o loop acaba

  var paths = [];    // { layer, pts: [[x, y], …] em unidades do SVG, len }
  var maps = [];     // por camada: { ox, oy, sx, sy, h, flip } — SVG → coordenadas do grupo Light
  // Luz em movimento: { el, path, s, speed, w, h } — as criadas pelo JS têm age (s); as do clique, once: true
  var figma = [];    // repouso das 10 do Figma: { el, path, s, w, h } (ponto da linha mais próximo do CSS)
  var auto = [];     // automáticas em movimento (só durante o loop ou apagando)
  var clicks = [];   // luzes do clique
  var settle = -1;   // ≥ 0: segundos desde o fim do loop (as automáticas apagando)
  var layersInfo = null; // SVGs carregados (viewBox + amostras), para remedir no resize
  var sources = '';
  var raf = 0, last = 0, visible = false, busy = false, ready = false;

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

  /* Luz nova criada pelo JS (extra automática ou tiro do clique), com o tamanho do traço da tela atual */
  function born(path, s, once) {
    var el = document.createElement('i');
    el.className = 'blog-hero__pulse blog-hero__pulse--js' + (once ? ' blog-hero__pulse--shot' : '');
    el.style.opacity = '0';
    light.appendChild(el);
    var cs = getComputedStyle(el);
    return {
      el: el, path: path, s: s, w: parseFloat(cs.width), h: parseFloat(cs.height),
      speed: speed() * (once ? SHOT_SPEED : 1), age: 0, once: !!once
    };
  }

  /* Anel que abre no ponto de partida do tiro (some sozinho) */
  function ring(p) {
    var el = document.createElement('i');
    el.className = 'blog-hero__ring';
    el.style.left = p.x.toFixed(2) + 'px';
    el.style.top = p.y.toFixed(2) + 'px';
    light.appendChild(el);
    var gone = function () { el.remove(); };
    el.addEventListener('animationend', gone);
    setTimeout(gone, 1200);
  }

  function speed() { return 1 / (LOOP * (0.8 + Math.random() * 0.45)); }

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

  function place(run, k) {
    var p = at(run.path, run.s);
    var f = FADE * run.path.len;
    var o = Math.max(0, Math.min(1, run.s / f, (run.path.len - run.s) / f)) * k;
    if (run.age != null) o *= Math.min(1, run.age / (run.once ? SHOT_IN : BORN_IN));
    run.el.style.transform = 'translate(' + (p.x - run.w / 2).toFixed(2) + 'px,' + (p.y - run.h / 2).toFixed(2) + 'px) rotate(' + p.a.toFixed(2) + 'deg)';
    run.el.style.opacity = o.toFixed(3);
  }

  /* Anda dt segundos; devolve false se a luz do clique chegou ao fim (e já saiu) */
  function advance(run, dt) {
    run.s += run.speed * run.path.len * dt;
    if (run.age != null) run.age += dt;
    if (run.s < run.path.len) return true;
    if (run.once) { run.el.remove(); return false; }
    run.path = paths[Math.floor(Math.random() * paths.length)]; // fim da linha: recomeça do início de outra linha
    run.s = 0;
    return true;
  }

  function frame(now) {
    raf = 0;
    var dt = last ? Math.min(0.1, (now - last) / 1000) : 0;
    last = now;
    var k = settle >= 0 ? Math.max(0, 1 - settle / SETTLE) : 1;
    auto.forEach(function (run) { advance(run, dt); place(run, k); });
    clicks = clicks.filter(function (run) { if (!advance(run, dt)) return false; place(run, 1); return true; });
    if (settle >= 0) {
      settle += dt;
      if (settle >= SETTLE) toRest();
    }
    if (visible && (auto.length || clicks.length)) raf = requestAnimationFrame(frame);
  }

  function kick() {
    if (raf || !visible || reduce.matches || !(auto.length || clicks.length)) return;
    last = 0;
    raf = requestAnimationFrame(frame);
  }
  function stop() {
    if (raf) cancelAnimationFrame(raf);
    raf = 0;
  }

  function onScreen() {
    var r = banner.getBoundingClientRect();
    return !document.hidden && r.bottom > 0 && r.top < window.innerHeight;
  }

  /* Loop ligado: as 10 do Figma partem da sua posição e as extras acendem em pontos sorteados */
  function startFlow() {
    if (!ready || reduce.matches) return;
    if (auto.length) { settle = -1; kick(); return; } // ainda apagando: voltam a correr
    light.classList.remove('is-back');
    auto = figma.map(function (f) { return { el: f.el, path: f.path, s: f.s, w: f.w, h: f.h, speed: speed() }; });
    light.classList.add('is-flowing');
    for (var n = auto.length; n < AUTO; n++) {
      var path = paths[Math.floor(Math.random() * paths.length)];
      auto.push(born(path, Math.random() * path.len));
    }
    auto.forEach(function (run) { place(run, 1); });
    kick();
  }

  /* Loop desligado: na tela, as automáticas apagam em SETTLE s; fora dela, repouso na hora */
  function stopFlow() {
    if (!auto.length) return;
    if (!onScreen()) { toRest(); return; }
    if (settle < 0) settle = 0;
    kick();
  }

  /* Repouso do Figma: extras saem, as 10 voltam às posições do CSS e reacendem (.is-back; quiet: sem reacender) */
  function toRest(quiet) {
    var wasFlowing = auto.length > 0;
    auto.forEach(function (run) { if (run.age != null) run.el.remove(); });
    auto = [];
    settle = -1;
    light.classList.remove('is-flowing');
    pulses.forEach(function (el) { el.style.transform = ''; el.style.opacity = ''; });
    if (wasFlowing && !quiet && onScreen()) {
      light.classList.remove('is-back');
      void light.offsetWidth; // reinicia a animação de reacender
      light.classList.add('is-back');
    }
  }

  function sync() {
    if (banner.classList.contains('is-looping') && !reduce.matches) startFlow(); else stopFlow();
  }

  /* Tudo parado e sem luzes do JS (troca de SVG, movimento reduzido) */
  function reset() {
    stop();
    toRest();
    clicks.forEach(function (run) { run.el.remove(); });
    clicks = [];
    light.classList.remove('is-back');
    banner.classList.remove('is-clickable');
    ready = false;
  }

  function init() {
    if (busy) return;
    var src = layers.map(function (l) { var img = l.querySelector('img'); return img.currentSrc || img.src; }).join('|');
    if (src === sources && ready) return;
    busy = true;
    reset();
    Promise.all(layers.map(loadLayer)).then(function (info) {
      busy = false;
      sources = src;
      paths = [];
      info.forEach(function (d) { paths = paths.concat(d.paths); });
      measure(info);
      layersInfo = info;
      if (reduce.matches) return;
      // Centro estático de cada luz do Figma (left/top do CSS + metade do traço) → ponto da linha mais próximo
      figma = pulses.map(function (el) {
        var cs = getComputedStyle(el);
        var w = parseFloat(cs.width), h = parseFloat(cs.height);
        var hit = nearest(parseFloat(cs.left) + w / 2, parseFloat(cs.top) + h / 2);
        return { el: el, path: hit.path, s: hit.s, w: w, h: h };
      });
      ready = true;
      banner.classList.add('is-clickable');
      sync(); // o loop pode ter ligado antes de as linhas estarem medidas
    }).catch(function () { busy = false; reset(); }); // sem rede: luzes paradas
  }

  /* Loop do motion.js (data-loop): as automáticas correm só com .is-looping */
  banner.addEventListener('motion:loop-start', sync);
  banner.addEventListener('motion:loop-stop', sync);

  /* Clique ou toque no banner: o tiro da pessoa, do ponto da linha mais próximo do clique */
  banner.addEventListener('click', function (e) {
    if (!ready || reduce.matches) return; // linhas ainda não medidas ou movimento reduzido: nada
    var lb = light.getBoundingClientRect();
    var hit = nearest(e.clientX - lb.left, e.clientY - lb.top);
    if (clicks.length >= CLICK_MAX) clicks.shift().el.remove();
    var run = born(hit.path, hit.s, true);
    clicks.push(run);
    place(run, 1);
    ring(at(hit.path, hit.s));
    kick();
  });

  var resizeTimer = 0;
  window.addEventListener('resize', function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function () {
      var src = layers.map(function (l) { var img = l.querySelector('img'); return img.currentSrc || img.src; }).join('|');
      if (src !== sources) { init(); return; } // trocou desktop ↔ mobile
      if (layersInfo && ready) { measure(layersInfo); auto.concat(clicks).forEach(function (run) { place(run, 1); }); }
    }, 150);
  });

  var onReduce = function () { if (reduce.matches) reset(); else init(); };
  // Impressão: o quadro do Figma na hora (sem apagar em SETTLE, sem reacender, sem os tiros)
  window.addEventListener('beforeprint', function () {
    stop();
    toRest(true);
    clicks.forEach(function (run) { run.el.remove(); });
    clicks = [];
    Array.prototype.forEach.call(light.querySelectorAll('.blog-hero__ring'), function (el) { el.remove(); });
    light.classList.remove('is-back');
  });
  if (reduce.addEventListener) reduce.addEventListener('change', onReduce); else if (reduce.addListener) reduce.addListener(onReduce);

  // Só anima com o banner na tela
  if ('IntersectionObserver' in window) {
    new IntersectionObserver(function (entries) {
      visible = entries[0].isIntersecting;
      if (visible) kick(); else stop();
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
