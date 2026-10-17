/* MktOps — topo: diagrama animado (partials/mktops-01-diagrama.html · Figma 8453:9443 / mobile 8696:4910).
   Fluxo de dados, num ciclo que se repete:
     1. uma luz sai de Skills e outra de MCP (o contorno da pasta acende) e corre pela linha até o MktOps;
     2. no núcleo, o contorno acende e um traço verde dá a volta pela borda (Skills por cima, MCP por baixo no desktop;
        pelas laterais no celular) até o lado de saída;
     3. dali a luz segue para Pipelines (vinda de Skills) e Infra (vinda de MCP), e o contorno da pasta acende na chegada.
   As etiquetas entram uma por vez quando a pasta chega na tela (CSS: .is-in + --delay); as pastas flutuam só com CSS.
   Para fora da tela ou com a aba escondida (.is-paused). Sem JS / prefers-reduced-motion: o quadro do Figma. */
(function () {
  'use strict';

  var fig = document.querySelector('.mk-hero__diagram');
  if (!fig) return;
  if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  if (!('IntersectionObserver' in window) || !window.requestAnimationFrame) return;

  var TRAVEL = 1.5;   // s de uma luz pela linha
  var TRACE = 0.65;   // s do traço contornando o núcleo
  var PERIOD = 5;     // s do ciclo inteiro (o contorno de Infra apaga antes de recomeçar)
  var LAG = 0.3;      // MCP sai um pouco depois de Skills
  var FADE = 0.12;    // fração da viagem em que a luz acende/apaga nas pontas
  var PAIRS = [['skills', 'pipelines', 0], ['mcp', 'infra', LAG]]; // entrada → saída, atraso

  var svgs = Array.prototype.slice.call(fig.querySelectorAll('svg.mk-dg'));
  if (!svgs.length) return;

  // Etiquetas: cada pasta ganha .is-in ao entrar na tela; as que entram juntas vão em sequência (0,45 s)
  var folderIO = new IntersectionObserver(function (entries) {
    var batch = entries.filter(function (e) { return e.isIntersecting; })
      .map(function (e) { return e.target; })
      .sort(function (a, b) { return a.style.getPropertyValue('--i') - b.style.getPropertyValue('--i'); });
    batch.forEach(function (folder, n) {
      folder.style.setProperty('--delay', (0.25 + n * 0.45).toFixed(2) + 's');
      folder.classList.add('is-in');
      folderIO.unobserve(folder);
    });
  }, { threshold: 0.6 });
  svgs.forEach(function (svg) {
    Array.prototype.forEach.call(svg.querySelectorAll('.mk-dg__folder'), function (f) { folderIO.observe(f); });
  });

  function pick(svg, sel) {
    var out = {};
    Array.prototype.forEach.call(svg.querySelectorAll(sel), function (el) { out[el.getAttribute('data-key') || el.parentNode.getAttribute('data-key')] = el; });
    return out;
  }

  function setup(svg) {
    var lines = pick(svg, '.mk-dg__line');
    var st = {
      svg: svg,
      lines: {},
      lights: pick(svg, '.mk-dg__light'),
      traces: {},
      hls: pick(svg, '.mk-dg__hl'),
      glow: svg.querySelector('.mk-dg__glow'),
      dash: svg.classList.contains('mk-dg--m') ? 34 : 40
    };
    Object.keys(lines).forEach(function (k) {
      var p = lines[k];
      st.lines[k] = { el: p, len: p.getTotalLength(), rev: p.hasAttribute('data-rev') };
    });
    var traces = pick(svg, '.mk-dg__trace');
    Object.keys(traces).forEach(function (k) {
      var p = traces[k], len = p.getTotalLength();
      st.traces[k] = { el: p, len: len };
      p.style.strokeDasharray = st.dash + ' ' + (len + st.dash);
    });
    return st;
  }

  function ease(u) { return 0.5 * u + 0.5 * u * u * (3 - 2 * u); } // meio linear, meio suave
  function clamp01(v) { return v < 0 ? 0 : v > 1 ? 1 : v; }
  // pulso: sobe em 0,15 s e apaga em 0,9 s
  function bump(t, t0) {
    var d = t - t0;
    if (d < 0) return 0;
    return d < 0.15 ? d / 0.15 : Math.max(0, 1 - (d - 0.15) / 0.9);
  }

  function drawLight(st, key, t, t0) {
    var light = st.lights[key], line = st.lines[key];
    if (!light || !line) return;
    var u = (t - t0) / TRAVEL;
    if (u < 0 || u > 1) { light.style.opacity = 0; return; }
    var s = ease(u), d = line.rev ? line.len * (1 - s) : line.len * s;
    var step = line.rev ? -1 : 1, d2 = d + step, flip = d2 < 0 || d2 > line.len;
    if (flip) d2 = d - step; // na ponta: tangente pelo ponto de trás, invertida
    var p = line.el.getPointAtLength(d);
    var q = line.el.getPointAtLength(d2);
    var a = Math.atan2(q.y - p.y, q.x - p.x) * 180 / Math.PI + (flip ? 180 : 0);
    light.setAttribute('transform', 'translate(' + p.x.toFixed(2) + ' ' + p.y.toFixed(2) + ') rotate(' + a.toFixed(1) + ')');
    light.style.opacity = Math.min(1, u / FADE, (1 - u) / FADE).toFixed(3);
  }

  // traço no núcleo: a cabeça vai de 0 a len + dash (entra e sai inteiro); devolve quando a cabeça chega na saída
  function drawTrace(st, key, t, t0) {
    var tr = st.traces[key];
    if (!tr) return t0 + TRACE;
    var total = tr.len + st.dash, u = (t - t0) / TRACE;
    if (u < 0 || u > 1) tr.el.style.opacity = 0;
    else {
      tr.el.style.strokeDashoffset = (st.dash - u * total).toFixed(2);
      tr.el.style.opacity = Math.min(1, u / 0.08, (1 - u) / 0.08).toFixed(3);
    }
    return t0 + TRACE * tr.len / total;
  }

  function render(st, t) {
    var glow = 0;
    PAIRS.forEach(function (pair) {
      var inKey = pair[0], outKey = pair[1], t0 = pair[2];
      var arrive = t0 + TRAVEL;
      drawLight(st, inKey, t, t0);
      var outStart = drawTrace(st, inKey, t, arrive);
      drawLight(st, outKey, t, outStart);
      glow = Math.max(glow, bump(t, arrive));
      if (st.hls[inKey]) st.hls[inKey].style.opacity = (0.85 * bump(t, t0)).toFixed(3);
      if (st.hls[outKey]) st.hls[outKey].style.opacity = (0.85 * bump(t, outStart + TRAVEL)).toFixed(3);
    });
    if (st.glow) st.glow.style.opacity = (0.7 * glow).toFixed(3);
  }

  var state = null, clock = 0, last = 0, raf = 0, visible = false;
  var mqMobile = window.matchMedia('(max-width: 767px)');

  function active() {
    var want = mqMobile.matches ? 'mk-dg--m' : 'mk-dg--d';
    var svg = svgs.filter(function (s) { return s.classList.contains(want); })[0];
    if (!svg) return null;
    if (!state || state.svg !== svg) {
      if (state) state.svg.classList.remove('is-running');
      state = setup(svg);
      svg.classList.add('is-running');
    }
    return state;
  }

  function frame(now) {
    raf = 0;
    if (!visible || document.hidden) return;
    var dt = last ? Math.min(0.1, (now - last) / 1000) : 0;
    last = now;
    clock = (clock + dt) % PERIOD;
    var st = active();
    if (st) render(st, clock);
    raf = requestAnimationFrame(frame);
  }

  function run() {
    var on = visible && !document.hidden;
    svgs.forEach(function (s) { s.classList.toggle('is-paused', !on); });
    if (on && !raf) { last = 0; raf = requestAnimationFrame(frame); }
  }

  new IntersectionObserver(function (entries) {
    visible = entries[0].isIntersecting;
    run();
  }).observe(fig);
  document.addEventListener('visibilitychange', run);
  if (mqMobile.addEventListener) mqMobile.addEventListener('change', function () { active(); });

})();
