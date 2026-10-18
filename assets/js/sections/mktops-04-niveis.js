/* MktOps — os três níveis: o cabeçalho (nome, selo, desenho) e as linhas de cada plano entram de leve quando o plano
   chega na tela (CSS: .is-armed / .is-in, --r = linha, --c = plano). Começa quando o topo do plano passa de 85% da
   altura da tela; quando o plano sai da tela por inteiro volta ao início (fora da tela, sem piscar: esconder não tem
   transição) e entra de novo na volta. Sem JS / prefers-reduced-motion: tudo à vista desde o início; movimento
   reduzido ligado com a página aberta: tudo à vista na hora (sai o .is-armed). */
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

  var inIO = new IntersectionObserver(function (entries) {
    entries.forEach(function (e) { if (e.isIntersecting) e.target.classList.add('is-in'); });
  }, { rootMargin: '0px 0px -15% 0px' });
  var outIO = new IntersectionObserver(function (entries) {
    entries.forEach(function (e) { if (!e.isIntersecting) e.target.classList.remove('is-in'); });
  }, { threshold: 0 });
  planos.forEach(function (p) { inIO.observe(p); outIO.observe(p); });

  // Movimento reduzido ligado com a página aberta: repouso imediato (sem .is-armed o CSS não esconde nem anima)
  if (reduceMQ && reduceMQ.addEventListener) {
    reduceMQ.addEventListener('change', function () {
      if (!reduceMQ.matches) return;
      inIO.disconnect();
      outIO.disconnect();
      grid.classList.remove('is-armed');
    });
  }

  window.addEventListener('beforeprint', function () {
    planos.forEach(function (p) { p.classList.add('is-in'); });
  });
})();
