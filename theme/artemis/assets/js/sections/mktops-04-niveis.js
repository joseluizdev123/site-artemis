/* MktOps — os três níveis: o cabeçalho (nome, selo, desenho) e as linhas de cada plano entram de leve quando o plano
   chega na tela (CSS: .is-armed / .is-in, --r = linha, --c = plano). Começa quando o topo do plano passa de 85% da
   altura da tela; quem recarregou mais abaixo (plano acima da tela) também dispara. Sem JS / prefers-reduced-motion:
   tudo à vista desde o início; movimento reduzido ligado com a página aberta: tudo à vista na hora (sai o .is-armed). */
(function () {
  'use strict';

  var grid = document.querySelector('.mk-niveis__grid');
  if (!grid || !('IntersectionObserver' in window)) return;
  var reduceMQ = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)');
  if (reduceMQ && reduceMQ.matches) return;

  var planos = Array.prototype.slice.call(grid.querySelectorAll('.mk-plano'));
  planos.forEach(function (plano, c) {
    plano.style.setProperty('--c', c);
    Array.prototype.forEach.call(plano.querySelectorAll('.mk-plano__row'), function (row, r) { row.style.setProperty('--r', r); });
  });
  grid.classList.add('is-armed');

  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (e) {
      if (!e.isIntersecting && e.boundingClientRect.top > 0) return;
      e.target.classList.add('is-in');
      io.unobserve(e.target);
    });
  }, { rootMargin: '0px 0px -15% 0px' });
  planos.forEach(function (p) { io.observe(p); });

  // Movimento reduzido ligado com a página aberta: repouso imediato (sem .is-armed o CSS não esconde nem anima)
  if (reduceMQ && reduceMQ.addEventListener) {
    reduceMQ.addEventListener('change', function () {
      if (!reduceMQ.matches) return;
      io.disconnect();
      grid.classList.remove('is-armed');
    });
  }

  window.addEventListener('beforeprint', function () {
    planos.forEach(function (p) { p.classList.add('is-in'); });
  });
})();
