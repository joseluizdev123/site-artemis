/* Sobre — foto da equipe: cortina para cima e assentamento (motion.js: data-wipe="up" data-wipe-zoom). O atributo só
   entra quando a foto está fora da tela por inteiro, para o CSS nunca esconder uma foto à vista: abaixo da dobra ao
   abrir (tela baixa) entra já; visível ao abrir (desktop alto, celular comum) ela fica parada e entra na 1ª vez que
   sai da tela. Daí em diante o motion.js repete a entrada a cada volta. */
(function () {
  'use strict';

  var photo = document.querySelector('.sobre-porque__photo');
  if (!photo || !('IntersectionObserver' in window)) return;
  if (!document.documentElement.classList.contains('motion-on')) return;

  var io = new IntersectionObserver(function (entries) {
    if (entries[entries.length - 1].isIntersecting) return;
    io.disconnect();
    // sem o motion.js (não carregou) ou com movimento reduzido ligado depois: a foto fica parada
    if (!window.ArtemisMotion || !document.documentElement.classList.contains('motion-on')) return;
    photo.setAttribute('data-wipe', 'up');
    photo.setAttribute('data-wipe-zoom', '');
    window.ArtemisMotion.refresh(photo.parentNode);
  });
  io.observe(photo);
})();
