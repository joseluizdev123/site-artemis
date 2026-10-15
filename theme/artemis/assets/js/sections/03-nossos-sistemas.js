/* Seção 03 — Nossos sistemas: disparo das ilustrações animadas (cards [data-anim]).
   Contrato com o CSS de cada ilustração (assets/css/sections/03-nossos-sistemas--<anim>.css):
     sem JS / prefers-reduced-motion  → nada é armado: a ilustração fica no quadro final (o do Figma)
     .sistemas.is-armed .sistemas__card (sem estado) → estado inicial (antes da animação)
     .sistemas__card.is-playing        → toca a animação até o quadro final (fill-mode both)
     .sistemas__card.is-static         → quadro final sem animar (card entrou rolando para CIMA)
   Disparo: toda vez que o card entra na tela rolando para BAIXO, e toda vez que o cursor (ou o dedo)
   entra no card — que também vem um pouco para frente (CSS :hover). Ao sair da tela, volta ao inicial.
   Evento "sistemas:play" no card, para ilustrações que precisem de JS próprio. */
(function () {
  'use strict';

  var section = document.querySelector('.sistemas');
  if (!section) return;
  var cards = Array.prototype.slice.call(section.querySelectorAll('.sistemas__card[data-anim]'));
  if (!cards.length) return;
  if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  if (!('IntersectionObserver' in window)) return; // sem observer: fica no quadro final

  section.classList.add('is-armed');

  function play(card) {
    card.classList.remove('is-playing', 'is-static');
    void card.offsetWidth; // reinicia as animações CSS
    card.classList.add('is-playing');
    card.dispatchEvent(new CustomEvent('sistemas:play', { bubbles: true }));
  }

  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      var card = entry.target;
      if (entry.isIntersecting) {
        if (card.classList.contains('is-playing') || card.classList.contains('is-static')) return;
        // entrou por baixo (rolando para baixo) → anima; entrou por cima (rolando para cima) → quadro final
        if (entry.boundingClientRect.top > 0) play(card);
        else card.classList.add('is-static');
      } else {
        card.classList.remove('is-playing', 'is-static'); // fora da tela: pronto para animar de novo
      }
    });
  }, { threshold: 0.35 });

  cards.forEach(function (card) {
    io.observe(card);
    card.addEventListener('pointerenter', function () { play(card); });
  });
})();
