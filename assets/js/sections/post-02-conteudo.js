/* Post — índice "Neste artigo" (8331:1264): marca com aria-current a seção em leitura
   (o último título do índice que já passou de 35% da altura da tela). Antes do primeiro, nenhum fica marcado,
   como no Figma. O índice fica fixo na rolagem pelo CSS (position: sticky, só no desktop). */
(function () {
  'use strict';

  var links = Array.prototype.slice.call(document.querySelectorAll('.post-toc__link[href^="#"]'));
  if (!links.length) return;

  var items = links.map(function (link) {
    var target = document.getElementById(decodeURIComponent(link.getAttribute('href').slice(1)));
    return target ? { link: link, target: target } : null;
  }).filter(Boolean);
  if (!items.length) return;

  var current = null, ticking = false;

  function update() {
    ticking = false;
    var line = window.innerHeight * 0.35, next = null;
    items.forEach(function (it) {
      if (it.target.getBoundingClientRect().top <= line) next = it;
    });
    if (next === current) return;
    if (current) current.link.removeAttribute('aria-current');
    if (next) next.link.setAttribute('aria-current', 'true');
    current = next;
  }

  function onScroll() {
    if (!ticking) { ticking = true; requestAnimationFrame(update); }
  }

  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', onScroll);
  update();
})();
