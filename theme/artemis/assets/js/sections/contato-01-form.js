/* Contato — formulário (Figma 8370:1580). Sem endereço de envio (action="#", como no HTML estático) não envia:
   avisa "Formulário ainda não configurado." no lugar da mensagem, igual à newsletter. No WordPress o tema aponta
   o formulário para o envio por e-mail (admin-post) e mostra a confirmação na volta. */
(function () {
  'use strict';

  var form = document.querySelector('[data-contato-form]');
  if (!form) return;
  var msg = form.querySelector('.contato-form__msg');

  form.addEventListener('submit', function (e) {
    if (form.getAttribute('action') && form.getAttribute('action') !== '#') return;
    e.preventDefault();
    if (msg) msg.textContent = form.getAttribute('data-unconfigured') || 'Formulário ainda não configurado.';
  });
})();
