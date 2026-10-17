/* MktOps — o que é instalado: ilustração da comunidade (partials/mktops-03-comunidade.html · Figma 8512:5774).
   - Sobe com o scroll: grava --p (0 → 1) na ilustração enquanto a faixa sobe na tela (topo da faixa de 95% a 55% da
     altura da tela), suavizado. Rolando de volta para cima ela desce de novo.
   - Luz nas arestas: cada peça (path) ganha uma cópia .mk-com__run com --len = comprimento do path; o CSS corre um
     segmento verde por ela, cada uma no seu ritmo (mesma velocidade, início diferente). Para fora da tela (.is-paused).
   Sem JS / prefers-reduced-motion: o quadro do Figma (ilustração no lugar, sem luz). */
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

  // Subida com o scroll
  var START = 0.95, END = 0.55, EASE = 0.1;
  var current = 0, target = 0, raf = 0;

  function setP() { illus.style.setProperty('--p', current.toFixed(4)); }
  function measure() {
    var vh = window.innerHeight || document.documentElement.clientHeight;
    var top = band.getBoundingClientRect().top;
    var p = (vh * START - top) / (vh * (START - END));
    target = p < 0 ? 0 : p > 1 ? 1 : p;
  }
  function tick() {
    raf = 0;
    current += (target - current) * EASE;
    if (Math.abs(target - current) < 0.001) current = target;
    setP();
    if (current !== target) raf = requestAnimationFrame(tick);
  }
  function update() {
    measure();
    if (!raf) raf = requestAnimationFrame(tick);
  }

  // Começa já na posição do scroll atual (quem recarrega com a faixa acima da tela a vê no lugar)
  measure();
  current = target;
  setP();

  var visible = false;
  function onScroll() { if (visible) update(); }
  if ('IntersectionObserver' in window) {
    new IntersectionObserver(function (entries) {
      visible = entries[0].isIntersecting;
      illus.classList.toggle('is-paused', !visible);
      update();
    }).observe(band);
  } else {
    visible = true;
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', update);

  // Impressão / PDF: no lugar
  window.addEventListener('beforeprint', function () { current = target = 1; setP(); });
})();
