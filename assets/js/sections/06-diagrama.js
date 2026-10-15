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
})();
