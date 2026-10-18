/* Seção 09 — Como trabalhamos: ilustração que "monta" com o scroll (8490:2308)
   Grava --p (0 → 1) na ilustração conforme ela sobe na tela; o CSS move a camada
   da tela, desenha o tracejado e revela o selo. Suavizado para subir devagar.
   A montagem acompanha a rolagem (repete sozinha quando a ilustração volta à tela). Montada (p = 1) e com
   o data-loop da biblioteca Motion ligado (enquanto está na tela, eventos motion:loop-start/stop), ganha
   .is-floating: as camadas flutuam sem parar. Fora da tela a flutuação para na hora (quadro do Figma) e,
   na volta, só recomeça depois de montar de novo. */
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

  // Fora da tela (ou aba oculta): para na hora — ninguém vê o salto — e a próxima flutuação espera montar de novo
  function stopFloat() {
    floating = false;
    el.classList.remove('is-floating');
    measure();                 // fora da tela: a montagem assenta na posição da rolagem (repete ao voltar)
    current = target;
    el.style.setProperty('--p', current.toFixed(4));
    assembled = current >= 0.999;
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
