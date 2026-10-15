/* Footer (Figma 8165:2501): voltar ao topo + newsletter */
(function () {
  'use strict';

  var motionQuery = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)') : null;

  /* Voltar ao topo (8165:2404) — rolagem suave e foco no início da página */
  Array.prototype.forEach.call(document.querySelectorAll('[data-back-to-top]'), function (link) {
    link.addEventListener('click', function (e) {
      e.preventDefault();
      // Foco no header fixo (logo) para quem navega por teclado — antes de rolar, sem cancelar a rolagem
      var first = document.querySelector('[data-header] a');
      if (first) first.focus({ preventScroll: true });
      // 'instant' (e não 'auto'): 'auto' herda o scroll-behavior: smooth do <html> (main.css)
      var reduce = motionQuery && motionQuery.matches;
      try {
        window.scrollTo({ top: 0, behavior: reduce ? 'instant' : 'smooth' });
      } catch (err) {
        window.scrollTo(0, 0);
      }
    });
  });

  /* Newsletter (8165:2346) — sem serviço configurado (action "#"), não envia e avisa */
  Array.prototype.forEach.call(document.querySelectorAll('[data-newsletter]'), function (form) {
    var msg = form.querySelector('.site-footer__form-msg');
    form.addEventListener('submit', function (e) {
      var action = (form.getAttribute('action') || '').trim();
      if (action && action !== '#') return; // envia para o serviço configurado
      e.preventDefault();
      if (msg) {
        msg.textContent = form.getAttribute('data-unconfigured') || 'Newsletter ainda não configurada.';
        msg.hidden = false;
      }
    });
  });
})();
