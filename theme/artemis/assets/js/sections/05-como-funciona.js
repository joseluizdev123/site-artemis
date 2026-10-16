/* Seção 05 — Como funciona: os passos entram um por vez quando a lista chega na tela —
   o número sobe, depois o texto (e o traço sob o número se desenha), até o último.
   Sem JS / prefers-reduced-motion os passos aparecem prontos (o CSS só esconde com .is-armed). */
(function () {
  'use strict';

  var list = document.querySelector('.como-funciona__steps');
  if (!list || !('IntersectionObserver' in window)) return;
  if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  Array.prototype.forEach.call(list.children, function (step, i) { step.style.setProperty('--i', i); });
  list.classList.add('is-armed');

  // Começa quando o topo da lista passa de 80% da altura da tela; quem recarregou
  // mais abaixo (lista já acima da tela) também dispara, para não ficar escondida
  var io = new IntersectionObserver(function (entries) {
    var e = entries[0];
    if (!e.isIntersecting && e.boundingClientRect.top > 0) return;
    io.disconnect();
    list.classList.add('is-in');
  }, { rootMargin: '0px 0px -20% 0px' });
  io.observe(list);
})();
