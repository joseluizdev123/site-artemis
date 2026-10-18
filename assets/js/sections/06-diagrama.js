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

  /* Ciclo do fluxo (CSS .is-flowing): a cada vez que o diagrama entra na tela (motion:loop-start do data-loop,
     biblioteca Motion), espera a entrada terminar e toca o processo uma vez (~4,6 s). Fim do tempo do loop com o
     ciclo tocando: deixa terminar (o ciclo já é curto); saiu da tela: para e volta ao quadro do Figma. */
  Array.prototype.forEach.call(document.querySelectorAll('.diagrama__chart[data-loop]'), function (chart) {
    var FLOW_MS = 4600;
    var timer = 0, wait = 0;
    var targets = chart.querySelectorAll('[data-reveal],[data-split],[data-wipe]');
    function entranceDone() {
      return Array.prototype.every.call(targets, function (t) { return t.classList.contains('is-done'); });
    }
    function stop() {
      clearTimeout(timer);
      clearTimeout(wait);
      chart.classList.remove('is-flowing');
    }
    function play() {
      stop();
      void chart.offsetWidth; // reinicia as animações
      chart.classList.add('is-flowing');
      timer = setTimeout(function () { chart.classList.remove('is-flowing'); }, FLOW_MS);
    }
    function start(tries) {
      clearTimeout(wait);
      if (entranceDone()) { wait = setTimeout(play, 250); return; }
      if (tries > 50) return; // a entrada não terminou em ~5 s: fica para a próxima entrada na tela
      wait = setTimeout(function () { start(tries + 1); }, 100);
    }
    function inView() {
      var r = chart.getBoundingClientRect();
      return r.bottom > 0 && r.top < (window.innerHeight || document.documentElement.clientHeight);
    }
    chart.addEventListener('motion:loop-start', function () { start(0); });
    chart.addEventListener('motion:loop-stop', function () { if (!inView()) stop(); });
    window.addEventListener('beforeprint', stop);
    if (chart.classList.contains('is-looping')) start(0); // o loop pode ter ligado antes deste script
  });
})();
