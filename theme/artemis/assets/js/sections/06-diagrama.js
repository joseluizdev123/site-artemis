/* Seção 06 — Diagram: rolagem horizontal do diagrama em telas estreitas (mobile 8653:2900, anotação "Scroll horizontal") */
(function () {
  'use strict';

  /* - barra de progresso (8653:2896): o trecho branco vai de 164/343 (início, como no Figma) a 100% no fim,
       via --diagrama-progress (0 → 1) no wrapper
     - teclado: o scroller só entra na ordem de foco quando há o que rolar; ← → rolam nativamente
     - mouse: arrastar rola o diagrama (no mobile a barra nativa fica escondida) */
  Array.prototype.forEach.call(document.querySelectorAll('[data-diagrama]'), function (root) {
    var scroller = root.querySelector('[data-diagrama-scroll]');
    if (!scroller) return;

    var frame = 0;
    function update() {
      frame = 0;
      var max = scroller.scrollWidth - scroller.clientWidth;
      var scrollable = max > 1;
      var progress = scrollable ? Math.min(1, Math.max(0, scroller.scrollLeft / max)) : 0;
      root.style.setProperty('--diagrama-progress', progress.toFixed(4));
      scroller.classList.toggle('is-scrollable', scrollable); // cursor de arrasto só quando há o que rolar
      if (scrollable) scroller.tabIndex = 0;
      else scroller.removeAttribute('tabindex');
    }
    function schedule() {
      if (!frame) frame = requestAnimationFrame(update);
    }

    scroller.addEventListener('scroll', schedule, { passive: true });
    if ('ResizeObserver' in window) new ResizeObserver(schedule).observe(scroller);
    else window.addEventListener('resize', schedule);
    update();

    // Arrastar com o mouse (toque e caneta usam a rolagem nativa)
    var drag = null;
    scroller.addEventListener('pointerdown', function (e) {
      if (e.pointerType !== 'mouse' || e.button !== 0) return;
      if (scroller.scrollWidth - scroller.clientWidth <= 1) return;
      drag = { id: e.pointerId, x: e.clientX, left: scroller.scrollLeft, moved: false };
    });
    scroller.addEventListener('pointermove', function (e) {
      if (!drag || e.pointerId !== drag.id) return;
      var dx = e.clientX - drag.x;
      if (!drag.moved) {
        if (Math.abs(dx) < 4) return; // clique simples não vira arrasto
        drag.moved = true;
        scroller.setPointerCapture(drag.id);
        scroller.classList.add('is-dragging');
        var sel = window.getSelection && window.getSelection();
        if (sel) sel.removeAllRanges();
      }
      scroller.scrollLeft = drag.left - dx;
    });
    function end(e) {
      if (!drag || e.pointerId !== drag.id) return;
      if (drag.moved) {
        scroller.classList.remove('is-dragging');
        if (scroller.hasPointerCapture(drag.id)) scroller.releasePointerCapture(drag.id);
      }
      drag = null;
    }
    scroller.addEventListener('pointerup', end);
    scroller.addEventListener('pointercancel', end);
    scroller.addEventListener('dragstart', function (e) { e.preventDefault(); }); // imagens das linhas não "descolam"
  });

  /* ---------------------------------------------------------------------------
     Montagem ligada à rolagem (scrub, reversível) + fluxo em loop — referências: galeria Animation do Awwwards e
     as luzes do banner do Blog. Só com o movimento liberado (html.motion-on) — sem JS, com movimento reduzido e
     na impressão o diagrama é o quadro do Figma, sem o caminho nem o ponto.
     1. Montagem: p = quanto da figura já entrou pela base da tela (0 → 1); com a figura inteira na tela, p = 1 =
        Figma. Réguas de POC/MVP/Escala se desenham (scaleX), linhas pontilhadas crescem para baixo (scaleY, topo),
        cartões entram na ordem do fluxo deslizando 24px da esquerda, legenda por último. Rolar de volta desmonta.
        Só transform/opacity, rAF + scroll passivo, e só perto da tela (IO com margem).
     2. Fluxo (enquanto o figure está na tela — data-loop, motion:loop-start/stop): um ponto de luz azul com rastro
        curto percorre conectores ortogonais pelos vãos e pelas bordas dos cartões (nunca por cima do texto) —
        01 → 02 → Ajuste de critério → desvio ao "Humano errou" e volta → 03 → V 1.0 → V 1.1 → V 1.2. O caminho é
        um SVG por cima do diagrama, com o traço só como guia bem sutil. Ao chegar num cartão, o selo brilha, o
        refresh dá uma volta, a lixeira treme 1 s ou a etiqueta da versão clareia (a caixa do cartão não se mexe).
        Nas linhas pontilhadas descem luzes curtas de vez em quando. Fora da tela: para.
     --------------------------------------------------------------------------- */
  var reduceMQ = window.matchMedia('(prefers-reduced-motion: reduce)');
  var motionOn = function () { return document.documentElement.classList.contains('motion-on') && !reduceMQ.matches; };
  var SVGNS = 'http://www.w3.org/2000/svg';

  Array.prototype.forEach.call(document.querySelectorAll('.diagrama__chart'), function (chart) {
    if (!motionOn() || !window.requestAnimationFrame) return;
    var content = chart.querySelector('.diagrama__content');
    if (!content) return;
    var q = function (sel) { return chart.querySelector(sel); };
    var qa = function (sel) { return Array.prototype.slice.call(chart.querySelectorAll(sel)); };
    var ease = function (k) { return 1 - Math.pow(1 - k, 3); };
    var seg = function (p, a, b) { return ease(Math.max(0, Math.min(1, (p - a) / (b - a)))); };

    /* ---------- 1. Montagem (scrub) ---------- */
    var phases = qa('.diagrama__phase');
    var lines = ['.diagrama__lines--poc', '.diagrama__lines--mvp', '.diagrama__lines--escala'].map(q).filter(Boolean);
    var flow = ['.diagrama__item--light', '.diagrama__item--measure', '.diagrama__item--adjust', '.diagrama__item--error', '.diagrama__item--lab']
      .map(q).filter(Boolean).concat(qa('.diagrama__item--version'));
    var legend = qa('.diagrama__legend-item');
    var parts = [];
    phases.forEach(function (el, i) { parts.push({ el: el, a: i * 0.05, b: 0.25 + i * 0.05, kind: 'rule' }); });
    lines.forEach(function (el, i) { parts.push({ el: el, a: 0.1 + i * 0.05, b: 0.4 + i * 0.05, kind: 'grow' }); });
    flow.forEach(function (el, i) { parts.push({ el: el, a: 0.25 + i * 0.065, b: 0.45 + i * 0.065, kind: 'slide' }); });
    legend.forEach(function (el, i) { parts.push({ el: el, a: 0.8 + i * 0.05, b: 0.95 + i * 0.05, kind: 'fade' }); });

    var p = -1, near = false, sraf = 0;
    function progress() {
      var r = chart.getBoundingClientRect();
      var vh = window.innerHeight || document.documentElement.clientHeight;
      return Math.max(0, Math.min(1, (vh - r.top) / Math.max(1, r.height)));
    }
    function apply(np) {
      if (np === p) return;
      p = np;
      chart.classList.toggle('is-assembled', p >= 0.98); // o fluxo só corre com o diagrama montado
      parts.forEach(function (it) {
        var k = seg(p, it.a, it.b), s = it.el.style;
        if (it.kind === 'rule') { it.el.style.setProperty('--diagrama-rule', k >= 1 ? '' : k.toFixed(4)); return; }
        if (k >= 1) { s.removeProperty('transform'); s.removeProperty('opacity'); return; } // repouso = Figma
        if (it.kind === 'grow') s.transform = 'scaleY(' + k.toFixed(4) + ')';
        else if (it.kind === 'slide') { s.transform = 'translateX(' + (-24 * (1 - k)).toFixed(2) + 'px)'; s.opacity = k.toFixed(3); }
        else s.opacity = k.toFixed(3);
      });
    }
    function scrub() { sraf = 0; apply(progress()); }
    function onScroll() { if (near && !sraf) sraf = requestAnimationFrame(scrub); }
    chart.classList.add('is-scrub');
    apply(progress());
    new IntersectionObserver(function (e) { near = e[0].isIntersecting; if (near) onScroll(); }, { rootMargin: '25% 0px' }).observe(chart);
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll);

    /* ---------- 2. Fluxo em loop ---------- */
    var svg = document.createElementNS(SVGNS, 'svg');
    svg.setAttribute('class', 'diagrama__flow');
    svg.setAttribute('aria-hidden', 'true');
    svg.setAttribute('focusable', 'false');
    var guide = document.createElementNS(SVGNS, 'path');
    guide.setAttribute('class', 'diagrama__flow-guide');
    svg.appendChild(guide);
    var dot = document.createElementNS(SVGNS, 'g');
    dot.setAttribute('class', 'diagrama__flow-dot');
    svg.appendChild(dot);
    var trails = [30, 16, 6].map(function (len, i) { // rastro: três trechos do próprio caminho, do mais fraco ao mais forte
      var t = document.createElementNS(SVGNS, 'path');
      t.setAttribute('class', 'diagrama__flow-trail diagrama__flow-trail--' + i);
      dot.appendChild(t);
      return { el: t, len: len };
    });
    var head = document.createElementNS(SVGNS, 'circle');
    head.setAttribute('class', 'diagrama__flow-head');
    head.setAttribute('r', '2.5');
    dot.appendChild(head);
    content.appendChild(svg);

    var route = [], total = 0, stops = [];
    // Caixa do cartão no .diagrama__content pela posição de layout (offset*: ignora o translate da montagem)
    function rect(el) {
      var x = 0, y = 0, n = el;
      while (n && n !== content) { x += n.offsetLeft; y += n.offsetTop; n = n.offsetParent; }
      return { l: x, t: y, r: x + el.offsetWidth, b: y + el.offsetHeight, cx: x + el.offsetWidth / 2, cy: y + el.offsetHeight / 2, el: el };
    }
    // Caminho ortogonal pelos vãos (corredores entre as linhas) e pelas bordas dos cartões; paradas = onde realça
    function build() {
      var get = function (sel) { var el = q(sel); return el ? rect(el) : null; };
      var a = get('.diagrama__item--light'), m = get('.diagrama__item--measure'), aj = get('.diagrama__item--adjust');
      var er = get('.diagrama__item--error'), lab = get('.diagrama__item--lab');
      var vs = qa('.diagrama__item--version').map(rect);
      if (!a || !m || !aj || !er || !lab) { total = 0; return; }
      svg.setAttribute('viewBox', '0 0 ' + content.offsetWidth + ' ' + content.offsetHeight);
      var c1 = (m.b + aj.t) / 2, c2 = (aj.b + er.t) / 2;
      var xLab = Math.min(lab.r - 24, Math.max(er.r + 24, lab.l + 24)); // desce ao 03 pela direita do "Humano errou"
      var pts = [], st = [];
      var add = function (x, y, hitEl) { pts.push([x, y]); if (hitEl) st.push({ i: pts.length - 1, el: hitEl }); };
      add(a.r, a.cy, a.el);                         // sai do 01 (selo brilha)
      add(m.l, a.cy, m.el);                         // chega ao 02
      add(m.l, c1);                                 // desce pela borda do 02 até o corredor
      add(aj.l, c1); add(aj.l, aj.cy, aj.el);       // borda do Ajuste de critério (refresh dá uma volta)
      add(aj.l, c2);
      add(er.cx, c2); add(er.cx, er.t, er.el); add(er.cx, c2); // desvio ao "Humano errou" e volta
      add(xLab, c2); add(xLab, lab.t, lab.el);      // ao 03
      if (vs.length) {
        var c4 = (lab.b + vs[0].t) / 2;
        add(lab.l, lab.t); add(lab.l, c4);          // contorna o 03 pela borda esquerda
        vs.forEach(function (v, i) {                // V 1.0 → V 1.1 → V 1.2
          add(v.cx, c4); add(v.cx, v.t, v.el);
          if (i < vs.length - 1) add(v.cx, c4);
        });
      }
      route = []; total = 0;
      pts.forEach(function (pt, i) {
        if (i) total += Math.abs(pt[0] - pts[i - 1][0]) + Math.abs(pt[1] - pts[i - 1][1]);
        route.push({ x: pt[0], y: pt[1], s: total });
      });
      stops = st.map(function (o) { return { s: route[o.i].s, el: o.el }; });
      var d = 'M' + pts.map(function (pt) { return pt[0].toFixed(1) + ' ' + pt[1].toFixed(1); }).join('L');
      [guide].concat(trails.map(function (t) { return t.el; })).forEach(function (el) { el.setAttribute('d', d); });
      trails.forEach(function (t) { t.el.style.strokeDasharray = t.len + ' ' + (total + 100); });
    }
    function at(s) {
      for (var i = 1; i < route.length; i++) {
        if (s <= route[i].s) {
          var a = route[i - 1], b = route[i], k = (s - a.s) / Math.max(1e-6, b.s - a.s);
          return [a.x + (b.x - a.x) * k, a.y + (b.y - a.y) * k];
        }
      }
      var e = route[route.length - 1];
      return [e.x, e.y];
    }

    // Realce ao chegar num cartão: selo, ícone ou etiqueta (nunca a caixa do cartão)
    function hit(card) {
      var t = card.querySelector('.diagrama__badge') ||
        (card.classList.contains('diagrama__item--adjust') && card.querySelector('.diagrama__icon')) ||
        (card.classList.contains('diagrama__item--error') && card.querySelector('.diagrama__icon')) ||
        card.querySelector('.diagrama__item-title');
      if (!t) return;
      var cls = card.classList.contains('diagrama__item--adjust') ? 'is-spin' : card.classList.contains('diagrama__item--error') ? 'is-shake' : 'is-hit';
      t.classList.remove(cls);
      void t.offsetWidth;
      t.classList.add(cls);
      clearTimeout(t.__hitT);
      t.__hitT = setTimeout(function () { t.classList.remove(cls); }, 1100);
    }

    // Luzes que descem pelas linhas pontilhadas (4 por imagem: 0, 1/3, 2/3 e 1 da largura)
    var dripT = 0;
    function drip() {
      if (!lines.length) return;
      var host = lines[Math.floor(Math.random() * lines.length)];
      var d = document.createElement('i');
      d.className = 'diagrama__drip';
      d.style.left = (Math.floor(Math.random() * 4) / 3 * 100).toFixed(3) + '%';
      d.addEventListener('animationend', function () { d.remove(); });
      host.appendChild(d);
      setTimeout(function () { d.remove(); }, 3000);
    }

    var SPEED = 300;   // px/s
    var PAUSE = 900;   // ms entre uma passada e a próxima
    var raf = 0, last = 0, s = 0, next = 0, waitUntil = 0, running = false;
    function frame(t) {
      raf = 0;
      if (!running) return;
      var dt = last ? Math.min(t - last, 50) : 0;
      last = t;
      if (!total) build();
      if (!total) { raf = requestAnimationFrame(frame); return; }
      if (!chart.classList.contains('is-assembled')) { s = 0; next = 0; waitUntil = t + 300; } // espera montar
      if (t >= waitUntil) {
        s += dt / 1000 * SPEED;
        while (next < stops.length && s >= stops[next].s) { hit(stops[next].el); next++; }
        if (s > total + trails[0].len) { s = 0; next = 0; waitUntil = t + PAUSE; } // fim: o rastro some e recomeça
      }
      var pt = at(Math.min(s, total));
      head.setAttribute('transform', 'translate(' + pt[0].toFixed(1) + ' ' + pt[1].toFixed(1) + ')');
      head.style.opacity = s <= total ? '' : '0';  // no fim, a cabeça some e o rastro termina de entrar
      dot.style.opacity = t >= waitUntil ? '' : '0';
      trails.forEach(function (tr) { tr.el.style.strokeDashoffset = (-(Math.min(s, total) - tr.len)).toFixed(1); });
      raf = requestAnimationFrame(frame);
    }
    function start() {
      if (running || !motionOn()) return;
      running = true;
      build();
      chart.classList.add('is-flow');
      s = 0; next = 0; last = 0; waitUntil = performance.now() + 400;
      raf = requestAnimationFrame(frame);
      clearInterval(dripT);
      dripT = setInterval(function () { if (Math.random() < 0.7) drip(); }, 1100);
    }
    function stop() {
      running = false;
      if (raf) cancelAnimationFrame(raf);
      raf = 0;
      clearInterval(dripT);
      chart.classList.remove('is-flow');
      qa('.diagrama__drip').forEach(function (d) { d.remove(); });
    }
    chart.addEventListener('motion:loop-start', start);
    chart.addEventListener('motion:loop-stop', stop);
    if (chart.classList.contains('is-looping')) start();
    if ('ResizeObserver' in window) new ResizeObserver(function () { total = 0; }).observe(content);

    // Impressão e movimento reduzido ligado com a página aberta: quadro do Figma, sem caminho nem ponto
    function rest() {
      stop();
      near = false;
      p = -1;
      parts.forEach(function (it) { it.el.style.removeProperty('transform'); it.el.style.removeProperty('opacity'); it.el.style.removeProperty('--diagrama-rule'); });
      chart.classList.remove('is-scrub', 'is-assembled');
    }
    window.addEventListener('beforeprint', rest);
    window.addEventListener('afterprint', function () { if (motionOn()) { chart.classList.add('is-scrub'); apply(progress()); } });
    if (reduceMQ.addEventListener) reduceMQ.addEventListener('change', function () { if (reduceMQ.matches) rest(); });
  });
})();
