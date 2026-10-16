/* Sobre — quem está por trás, versão B (Figma 8355:3485). A foto que cruza o meio da tela fica acesa (as outras a 20%)
   e, no desktop, a bio fixa à direita troca para essa pessoa. No celular só as fotos acendem (a bio é a do Figma). */
(function () {
  'use strict';

  var list = document.querySelector('[data-socios]');
  if (!list || !('IntersectionObserver' in window)) return;

  var photos = Array.prototype.slice.call(list.querySelectorAll('.sobre-socios__photo'));
  var members = Array.prototype.slice.call(list.querySelectorAll('.sobre-socios__member'));
  if (photos.length < 2) return;

  function activate(i) {
    photos.forEach(function (p, k) { p.classList.toggle('is-active', k === i); });
    members.forEach(function (m, k) {
      var on = k === i;
      m.classList.toggle('is-active', on);
      if (on) m.removeAttribute('aria-hidden'); else m.setAttribute('aria-hidden', 'true');
    });
  }

  // faixa fina no meio da tela: a foto que estiver nela é a ativa
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (e) {
      if (e.isIntersecting) activate(photos.indexOf(e.target));
    });
  }, { rootMargin: '-50% 0px -50% 0px', threshold: 0 });
  photos.forEach(function (p) { io.observe(p); });

  // acima da 1ª foto, ela continua acesa (estado do Figma)
  window.addEventListener('scroll', function () {
    if (photos[0].getBoundingClientRect().top > window.innerHeight / 2) activate(0);
  }, { passive: true });
})();
