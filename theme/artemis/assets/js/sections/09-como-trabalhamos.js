/* Seção 09 — Como trabalhamos: ilustração que "monta" com o scroll (8490:2308)
   Grava --p (0 → 1) na ilustração conforme ela sobe na tela; o CSS move a camada
   da tela, desenha o tracejado e revela o selo. Suavizado para subir devagar.
   Montada (p = 1) e com o data-loop da biblioteca Motion ligado (até 5 s a cada entrada na tela, eventos
   motion:loop-start/stop), ganha .is-floating: as camadas flutuam. Ao parar, cada camada termina a volta
   em que está e assenta no quadro do Figma (sem salto). */
(function () {
  'use strict';

  var el = document.querySelector('.como-trabalhamos__illustration[data-rise]');
  if (!el) return;
  if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  var START = 0.9;  // topo da ilustração a 90% da altura da tela → p = 0
  var END = 0.4;    // topo da ilustração a 40% da altura da tela → p = 1
  var EASE = 0.08;  // fração do caminho percorrida por quadro (menor = mais devagar)

  var current = 0, target = 0, raf = 0, visible = false;

  var assembled = false, looping = el.classList.contains('is-looping'), floating = false;

  function startFloat() {
    if (floating || !assembled || !looping) return;
    floating = true;
    el.classList.remove('is-floating');
    void el.offsetWidth; // reinicia as animações
    el.classList.add('is-floating');
  }

  // alternate: a ida é a volta ímpar; terminar num número par de voltas devolve a camada ao 0
  function stopFloat() {
    if (!floating) return;
    floating = false;
    if (!el.getAnimations) { el.classList.remove('is-floating'); return; }
    el.getAnimations({ subtree: true }).forEach(function (a) {
      if (a.animationName !== 'como-trabalhamos-float') return;
      var it = a.effect.getComputedTiming().currentIteration;
      if (it == null) { a.cancel(); return; } // ainda no atraso: nem saiu do lugar
      a.effect.updateTiming({ iterations: Math.ceil((it + 1) / 2) * 2 });
    });
  }

  el.addEventListener('motion:loop-start', function () { looping = true; startFloat(); });
  el.addEventListener('motion:loop-stop', function () { looping = false; stopFloat(); });

  function setP() {
    el.style.setProperty('--p', current.toFixed(4));
    if (current >= 0.999 && !assembled) { assembled = true; startFloat(); }
  }

  function measure() {
    var vh = window.innerHeight || document.documentElement.clientHeight;
    var top = el.getBoundingClientRect().top;
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

  // Começa no estado "encaixado" e já posiciona conforme o scroll atual
  measure();
  current = target;
  setP();

  if ('IntersectionObserver' in window) {
    new IntersectionObserver(function (entries) {
      visible = entries[0].isIntersecting;
      if (visible) update();
    }, { rootMargin: '25% 0px' }).observe(el);
  } else {
    visible = true;
  }

  window.addEventListener('scroll', function () { if (visible) update(); }, { passive: true });
  window.addEventListener('resize', update);
})();
