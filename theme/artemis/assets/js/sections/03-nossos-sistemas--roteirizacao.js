/* Ilustração "Alocação e roteirização" — o cubo percorre a rota laranja até a posição do Figma.
   Rota (vetor 8294:790, centro do traço) em coordenadas do frame da ilustração; o último ponto é onde o
   cubo está no Figma, então no fim da animação o deslocamento volta a zero (quadro final exato).
   Dispara com o evento "sistemas:play" do card (assets/js/sections/03-nossos-sistemas.js). */
(function () {
  'use strict';

  var card = document.querySelector('.sistemas__card[data-anim="roteirizacao"]');
  if (!card) return;

  var ROUTE = [[343.1, -11.75], [196.07, 72.63], [216.85, 84.63], [150.39, 123]]; // início → fim (cubo)
  var DURATION = 2600; // ms — 23 segmentos × 110 ms da barra do card
  var END = ROUTE[ROUTE.length - 1];
  var lengths = [], total = 0;
  for (var i = 1; i < ROUTE.length; i++) {
    var l = Math.hypot(ROUTE[i][0] - ROUTE[i - 1][0], ROUTE[i][1] - ROUTE[i - 1][1]);
    lengths.push(l);
    total += l;
  }

  function pointAt(t) {
    var d = t * total;
    for (var k = 0; k < lengths.length; k++) {
      if (d <= lengths[k] || k === lengths.length - 1) {
        var f = lengths[k] ? Math.min(1, d / lengths[k]) : 1;
        return [ROUTE[k][0] + (ROUTE[k + 1][0] - ROUTE[k][0]) * f, ROUTE[k][1] + (ROUTE[k + 1][1] - ROUTE[k][1]) * f];
      }
      d -= lengths[k];
    }
    return END;
  }

  function ease(t) { return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2; }

  var raf = 0;
  function run() {
    cancelAnimationFrame(raf);
    var cubes = Array.prototype.slice.call(card.querySelectorAll('.ilu__cube'));
    var start = performance.now();
    function frame(now) {
      if (!card.classList.contains('is-playing')) { // saiu da tela no meio: volta ao estado do CSS
        cubes.forEach(function (c) { c.style.transform = ''; });
        return;
      }
      var t = Math.min(1, (now - start) / DURATION), p = pointAt(ease(t));
      cubes.forEach(function (c) {
        var scene = c.parentNode, W = parseFloat(scene.style.aspectRatio) || 320;
        var s = scene.getBoundingClientRect().width / W; // px por unidade do frame do Figma
        c.style.transform = t < 1 ? 'translate(' + ((p[0] - END[0]) * s).toFixed(2) + 'px,' + ((p[1] - END[1]) * s).toFixed(2) + 'px)' : '';
      });
      if (t < 1) raf = requestAnimationFrame(frame);
    }
    raf = requestAnimationFrame(frame);
  }

  card.addEventListener('sistemas:play', run);
})();
