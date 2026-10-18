/* Seção 05 — Como funciona: os passos entram um por vez quando a lista chega na tela —
   o número sobe, depois o texto (e o traço sob o número se desenha), até o último.
   Repete: quando a lista sai da tela por inteiro, volta ao estado inicial (fora da tela, sem piscar) e entra
   de novo ao voltar.
   Sem JS / prefers-reduced-motion os passos aparecem prontos (o CSS só esconde com .is-armed);
   na impressão também. */
(function () {
  'use strict';

  var list = document.querySelector('.como-funciona__steps');
  if (!list || !('IntersectionObserver' in window)) return;
  var reduceMQ = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)') : null;
  if (reduceMQ && reduceMQ.matches) return;

  Array.prototype.forEach.call(list.children, function (step, i) { step.style.setProperty('--i', i); });
  list.classList.add('is-armed');

  // Entra quando o topo da lista passa de 80% da altura da tela; quem recarregou mais abaixo (lista já
  // acima da tela) fica pronto — e entra de novo se voltar
  if (list.getBoundingClientRect().bottom <= 0) list.classList.add('is-in');
  var playIO = new IntersectionObserver(function (entries) {
    if (entries[0].isIntersecting) list.classList.add('is-in');
  }, { rootMargin: '0px 0px -20% 0px' });
  // Saiu da tela por inteiro: volta ao início (as transições só existem com .is-in — esconder é instantâneo)
  var resetIO = new IntersectionObserver(function (entries) {
    if (!entries[0].isIntersecting) list.classList.remove('is-in');
  }, { threshold: 0 });
  playIO.observe(list);
  resetIO.observe(list);

  // Impressão / PDF e movimento reduzido ligado com a página aberta: passos visíveis (o CSS de print também garante)
  function rest() {
    playIO.disconnect();
    resetIO.disconnect();
    list.classList.add('is-in');
  }
  window.addEventListener('beforeprint', rest);
  if (reduceMQ && reduceMQ.addEventListener) reduceMQ.addEventListener('change', function () { if (reduceMQ.matches) rest(); });
})();
