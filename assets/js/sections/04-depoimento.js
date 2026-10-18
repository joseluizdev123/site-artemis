/* Seção 04 — Depoimento: globo pontilhado girando (Figma 8229:2131 · mobile 8635:2311)
   Desenha em <canvas> a grade de pontos de assets/data/globe-points.json (tools/gen_globe_figma.py)
   numa projeção ortográfica que gira devagar, no enquadramento do Figma: mesma esfera (centro e
   raio), orientação inicial (Atlântico no centro), cor #CECECE, degradê horizontal e tamanho dos pontos.
   - Terra: grade do mapa real (Natural Earth), cada ponto uma vez — sem continentes sobrepostos.
   - Oceano: o resto da grade, bem fraco — o globo nunca fica vazio.
   - Velocidade variável: sobre o oceano (pouco continente no lado visível) acelera até MAX_BOOST×.
   - Gira sem parar enquanto está na tela (data-loop da biblioteca Motion, eventos motion:loop-start/stop); fora
     da tela ou com a aba oculta, para e volta na hora à orientação inicial — o quadro do Figma. */
(function () {
  'use strict';

  var box = document.querySelector('.depoimento__globe[data-globe]');
  var canvas = box && box.querySelector('canvas');
  if (!canvas || !canvas.getContext || !window.fetch) return;

  // Frame do globo por breakpoint (W×H = caixa no CSS). O vetor do Figma é o mesmo nos dois:
  // desktop em (272, 16) do frame 935×443; mobile em (427, 74) do frame 800×501 → deslocamento OX/OY.
  var FRAMES = {
    desktop: { W: 935, H: 443, OX: 0, OY: 0 },     // 8229:2131
    mobile: { W: 800, H: 501, OX: 155, OY: 58 }    // 8635:2311
  };
  var MOBILE_MQ = '(max-width: 767px)';             // mesmo breakpoint do CSS

  // Degradê do Figma (fill de cada ponto): linear horizontal de x=948 (transparente até 17,5%)
  // a x=272 (50%), cor #CECECE — em coordenadas do frame desktop.
  var GRAD_FROM = 948, GRAD_TO = 272, GRAD_STOP = 0.175, GRAD_MAX = 0.5;
  var COLOR = '#cecece';
  var INK = 1.4;                 // pontos 40% mais fortes que o Figma (pedido do cliente): opacidade máx. 0,5 → 0,7
  var DOT_D = 2.95;              // diâmetro do ponto no centro do disco (bbox de 3,3 do Figma sem o antialias)
  var DOT_EXP = 0.85;            // encolhimento rumo à borda (calibrado pelo brilho por faixa de raio do export)
  var OCEAN = 0.16;              // intensidade dos pontos de oceano em relação aos de terra
  var SECONDS_PER_TURN = 90;     // uma volta completa a cada 90 s (velocidade sobre os continentes)
  var MAX_BOOST = 10;            // sobre o oceano vazio gira até 10× mais rápido (o trecho do Pacífico passa em ~5 s)
  var BUCKETS = 12;

  var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var mobile = window.matchMedia ? window.matchMedia(MOBILE_MQ) : null;
  var ctx = canvas.getContext('2d');
  var view = null, land = null, sea = null;
  var looping = false, raf = 0, last = 0, spin = 0, speed = 1; // spin = quanto já girou (°)
  var landInk = 0, refInk = 0;   // "quanto continente aparece" no quadro atual / no 1º quadro
  var W, H, OX, OY, sized = '';

  // Ajusta o canvas ao frame atual; devolve false se nada mudou (o resize do celular dispara a
  // cada barra de endereço que some/aparece — aí não precisa redesenhar)
  function resize() {
    var f = mobile && mobile.matches ? FRAMES.mobile : FRAMES.desktop;
    var dpr = Math.min(window.devicePixelRatio || 1, 2);
    var key = f.W + 'x' + f.H + '@' + dpr;
    if (key === sized) return false;
    sized = key;
    W = f.W; H = f.H; OX = f.OX; OY = f.OY;
    canvas.width = Math.round(W * dpr);
    canvas.height = Math.round(H * dpr);
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    refInk = 0;                  // a referência de "continente visível" depende do enquadramento
    return true;
  }

  function push(list, latDeg, lonDeg) {
    var lat = latDeg * Math.PI / 180;
    list.push(Math.sin(lat), Math.cos(lat), lonDeg * Math.PI / 180);
  }

  // Grade: {step, rows: [[lat, n, [ini, fim, …]], …]} — terra = faixas, oceano = o resto da grade.
  // (Modo híbrido opcional do gerador: {pts: base64 Int16 [lat×100, lon×100, …]} — só terra.)
  function load(data) {
    view = data.view;
    var l = [], s = [];
    if (data.rows) {
      var step = data.step, byLat = {};
      data.rows.forEach(function (r) { byLat[r[0].toFixed(3)] = r[2]; });
      var nLat = Math.floor(180 / step);
      for (var j = 0; j < nLat; j++) {
        var lat = 90 - (j + 0.5) * step;
        var n = Math.max(1, Math.round(360 * Math.cos(lat * Math.PI / 180) / step));
        var runs = byLat[(Math.round(lat * 1000) / 1000).toFixed(3)] || [];
        var k = 0;
        for (var i = 0; i < n; i++) {
          while (k < runs.length && i > runs[k + 1]) k += 2;
          var isLand = k < runs.length && i >= runs[k] && i <= runs[k + 1];
          push(isLand ? l : s, lat, -180 + (i + 0.5) * 360 / n);
        }
      }
    } else if (data.pts && window.atob) {
      var bin = window.atob(data.pts), dv = new DataView(new ArrayBuffer(bin.length));
      for (var b = 0; b < bin.length; b++) dv.setUint8(b, bin.charCodeAt(b));
      for (var p = 0; p < bin.length; p += 4) push(l, dv.getInt16(p, true) / 100, dv.getInt16(p + 2, true) / 100);
    }
    land = new Float32Array(l);
    sea = new Float32Array(s);
  }

  function gradientAlpha(xDesktop) {
    var t = (GRAD_FROM - xDesktop) / (GRAD_FROM - GRAD_TO);
    if (t <= GRAD_STOP) return 0;
    return GRAD_MAX * Math.min(1, (t - GRAD_STOP) / (1 - GRAD_STOP));
  }

  // Projeta um conjunto de pontos nos paths por faixa de opacidade; devolve a "tinta" (soma das opacidades)
  function plot(list, paths, factor, P) {
    var ink = 0;
    for (var j = 0; j < list.length; j += 3) {
      var sl = list[j], cl = list[j + 1], d = list[j + 2] - P.l0, cd = Math.cos(d);
      var z = P.sp * sl + P.cp * cl * cd;        // profundidade (1 = de frente, 0 = borda)
      if (z <= 0) continue;                       // lado de trás da esfera
      var x0 = cl * Math.sin(d), y0 = P.cp * sl - P.sp * cl * cd;
      var x = x0 * P.cr - y0 * P.sr, y = x0 * P.sr + y0 * P.cr;
      var sx = P.cx + P.R * x, sy = P.cy - P.R * y;
      if (sx < -3 || sx > W + 3 || sy < -3 || sy > H + 3) continue;

      var a = gradientAlpha(sx - OX) * Math.min(1, z / 0.05) * factor; // degradê do Figma + borda suave
      if (a <= 0.004) continue;
      ink += a;
      var bucket = Math.min(BUCKETS - 1, Math.floor(a / GRAD_MAX * BUCKETS));
      var rad = Math.max(0.45, DOT_D / 2 * Math.pow(z, DOT_EXP)); // pontos menores perto da borda, como no Figma
      paths[bucket].moveTo(sx + rad, sy);
      paths[bucket].arc(sx, sy, rad, 0, Math.PI * 2);
    }
    return ink;
  }

  function draw() {
    var p0 = view.LAT * Math.PI / 180, rr = view.ROLL * Math.PI / 180;
    var P = {
      cx: view.CX + OX, cy: view.CY + OY, R: view.R, l0: (view.LON + spin) * Math.PI / 180,
      sp: Math.sin(p0), cp: Math.cos(p0), sr: Math.sin(rr), cr: Math.cos(rr)
    };
    var paths = [];
    for (var b = 0; b < BUCKETS; b++) paths.push(new Path2D());
    plot(sea, paths, OCEAN, P);                   // oceano primeiro (fica por baixo)
    landInk = plot(land, paths, 1, P);
    if (!refInk) refInk = landInk;

    ctx.clearRect(0, 0, W, H);
    ctx.fillStyle = COLOR;
    for (var k = 0; k < BUCKETS; k++) {
      ctx.globalAlpha = Math.min(1, INK * GRAD_MAX * (k + 0.5) / BUCKETS);
      ctx.fill(paths[k]);
    }
    ctx.globalAlpha = 1;
  }

  // Velocidade: 1× com continente no lado visível (≥ 60% da "tinta" de terra do 1º quadro);
  // sobe até MAX_BOOST× conforme a terra some (≤ 15%: oceano aberto). Suavizada para não dar trancos.
  function targetSpeed() {
    if (!refInk) return 1;
    var r = (landInk / refInk - 0.15) / (0.6 - 0.15);
    r = r < 0 ? 0 : r > 1 ? 1 : r;
    return 1 + (MAX_BOOST - 1) * (1 - r);
  }

  function frame() {
    var t = performance.now();
    raf = 0;
    if (last) {
      var dt = Math.min(t - last, 100);         // salto máx. 100ms ao voltar de aba
      speed += (targetSpeed() - speed) * Math.min(1, dt / 350);
      spin -= dt / 1000 * 360 / SECONDS_PER_TURN * speed; // continentes andam para a direita
    }
    last = t;
    draw();
    if (looping && !reduced) raf = requestAnimationFrame(frame);
  }

  function start() {
    if (!land || reduced || raf) return;
    last = 0;
    raf = requestAnimationFrame(frame);
  }

  // Fim do loop (fora da tela / aba oculta): para e redesenha o quadro inicial — ninguém vê o salto
  function settle() {
    if (raf) cancelAnimationFrame(raf);
    raf = 0;
    spin = 0;
    speed = 1;
    last = 0;
    if (land) draw();
  }

  function redraw() { if (resize() && land) draw(); }

  resize();
  window.addEventListener('resize', redraw); // densidade de pixels e troca de breakpoint
  if (mobile) {
    if (mobile.addEventListener) mobile.addEventListener('change', redraw);
    else if (mobile.addListener) mobile.addListener(redraw);
  }

  box.addEventListener('motion:loop-start', function () { looping = true; start(); });
  box.addEventListener('motion:loop-stop', function () { looping = false; settle(); });

  fetch(box.getAttribute('data-globe'))
    .then(function (r) { return r.json(); })
    .then(function (data) {
      load(data);
      draw();                                   // quadro do Figma
      if (box.classList.contains('is-looping')) { looping = true; start(); } // o loop pode ter ligado antes
    })
    .catch(function () { /* sem dados: canvas fica vazio, o resto da seção segue normal */ });
})();
