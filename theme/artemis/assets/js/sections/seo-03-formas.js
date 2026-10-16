/* Artemis SEO — duas formas de contratar (Figma 8396:3369). Desktop: acordeão de duas opções (variantes Plataforma /
   SEO’Ops); a barra branca sob a aberta enche em DURATION e abre a próxima, trocando a imagem da direita.
   Pausa com cursor/foco na seção, fora da tela ou com a aba oculta; para de vez quando a pessoa escolhe.
   Com movimento reduzido não avança sozinho. No celular o HTML é outro (os dois estados em sequência). */
(function () {
  'use strict';

  var box = document.querySelector('[data-formas]');
  if (!box) return;

  var DURATION = 9000;
  var tabs = Array.prototype.slice.call(box.querySelectorAll('button.seo-opt__tab'));
  var bodies = tabs.map(function (t) { return document.getElementById(t.getAttribute('aria-controls')); });
  var imgs = Array.prototype.slice.call(box.querySelectorAll('.seo-formas__img'));
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)');
  var section = box.closest('section') || box;

  var active = 0, auto = !reduce.matches, hovering = false, visible = false, elapsed = 0, last = 0, raf = 0;

  function setProgress(p) {
    Array.prototype.forEach.call(box.querySelectorAll('.seo-opt__bar i'), function (b) { b.style.setProperty('--progress', p.toFixed(4)); });
  }

  function show(i) {
    active = i;
    box.setAttribute('data-active', String(i + 1));
    tabs.forEach(function (t, k) {
      t.setAttribute('aria-expanded', k === i ? 'true' : 'false');
      if (bodies[k]) bodies[k].hidden = k !== i;
    });
    imgs.forEach(function (im, k) { im.classList.toggle('is-active', k === i); });
    elapsed = 0;
    setProgress(auto ? 0 : 0.0945);
  }

  function running() { return auto && !hovering && visible && !document.hidden && box.offsetParent !== null; }

  function tick(now) {
    raf = 0;
    if (!running()) { last = 0; return; }
    if (last) elapsed += now - last;
    last = now;
    if (elapsed >= DURATION) show((active + 1) % tabs.length);
    else setProgress(elapsed / DURATION);
    raf = requestAnimationFrame(tick);
  }
  function resume() { if (!raf && running()) { last = 0; raf = requestAnimationFrame(tick); } }

  tabs.forEach(function (t, k) {
    t.addEventListener('click', function () { auto = false; show(k); });
  });
  section.addEventListener('pointerenter', function () { hovering = true; });
  section.addEventListener('pointerleave', function () { hovering = false; resume(); });
  section.addEventListener('focusin', function () { hovering = true; });
  section.addEventListener('focusout', function (e) { if (!section.contains(e.relatedTarget)) { hovering = false; resume(); } });
  document.addEventListener('visibilitychange', resume);
  window.addEventListener('resize', resume);

  if ('IntersectionObserver' in window) {
    new IntersectionObserver(function (entries) { visible = entries[0].isIntersecting; resume(); }, { threshold: 0.3 }).observe(section);
  } else {
    visible = true;
  }

  show(0);
  resume();
})();
