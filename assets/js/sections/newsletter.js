/* Newsletter (componente · símbolo 8436:2153): formulário de inscrição */
(function () {
  'use strict';

  /* Formulário (8314:2508) — a validação de e-mail é a nativa do navegador (type="email" + required).
     Sem serviço configurado (action vazia ou "#"), não envia: avisa na região role="status". */
  Array.prototype.forEach.call(document.querySelectorAll('[data-newsletter-form]'), function (form) {
    var msg = form.querySelector('.newsletter__msg');
    var input = form.querySelector('input[type="email"]');

    form.addEventListener('submit', function (e) {
      var action = (form.getAttribute('action') || '').trim();
      if (action && action !== '#') return; // envia para o serviço configurado
      e.preventDefault();
      if (msg) msg.textContent = form.getAttribute('data-unconfigured') || 'Newsletter ainda não configurada.';
    });

    // Some com o aviso quando a pessoa volta a editar o e-mail
    if (input && msg) {
      input.addEventListener('input', function () {
        if (msg.textContent) msg.textContent = '';
      });
    }
  });
})();
