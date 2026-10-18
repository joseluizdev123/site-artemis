/* Sobre — foto da equipe: cortina para cima e assentamento (motion.js: data-wipe="up" data-wipe-zoom) só quando a foto
   abre abaixo da dobra (tela baixa). Já na tela ao abrir (desktop alto, celular comum) ela fica parada. O atributo
   entra aqui, e não no markup, para o CSS nunca esconder uma foto que já está à vista. */
(function () {
  'use strict';

  var photo = document.querySelector('.sobre-porque__photo');
  if (!photo || !document.documentElement.classList.contains('motion-on')) return;
  if (photo.getBoundingClientRect().top < window.innerHeight) return;

  photo.setAttribute('data-wipe', 'up');
  photo.setAttribute('data-wipe-zoom', '');
  if (window.ArtemisMotion) window.ArtemisMotion.refresh(photo.parentNode);
})();
