/* MktOps — o que é instalado: ilustração da comunidade (partials/mktops-03-comunidade.html · Figma 8512:5774).
   Fixa na posição do Figma; luz nas arestas: cada peça (path) ganha uma cópia .mk-com__run com --len = comprimento do
   path, e o CSS corre um segmento verde por ela, cada uma no seu ritmo (mesma velocidade, início diferente) — só
   enquanto a faixa tem .is-looping (data-loop: enquanto está na tela, motion.js).
   Sem JS / prefers-reduced-motion: o quadro do Figma, sem luz. */
(function () {
  'use strict';

  var band = document.querySelector('.mk-inst__community');
  var illus = band && band.querySelector('svg.mk-inst__illus');
  if (!illus) return;
  if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  var SPEED = 42; // unidades do SVG por segundo
  var NS = 'http://www.w3.org/2000/svg';

  // Luzes: uma cópia de cada path, por cima das linhas do Figma
  var base = illus.querySelector('.mk-com__base');
  var runs = document.createElementNS(NS, 'g');
  runs.setAttribute('class', 'mk-com__runs');
  Array.prototype.forEach.call(base.querySelectorAll('path'), function (p, i) {
    var len = p.getTotalLength();
    if (len < 30) return; // lascas de poucos pixels: sem luz
    var run = p.cloneNode(false);
    run.setAttribute('class', 'mk-com__run');
    run.removeAttribute('stroke-width');
    var dur = Math.max(3, len / SPEED);
    run.style.setProperty('--len', len.toFixed(2) + 'px');
    run.style.setProperty('--dur', dur.toFixed(2) + 's');
    run.style.setProperty('--delay', (-((i * 0.37) % 1) * dur).toFixed(2) + 's');
    runs.appendChild(run);
  });
  illus.appendChild(runs);
})();
