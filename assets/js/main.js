/* Studio Artemis — main.js */
(function () {
  'use strict';

  /* Header: após o scroll fica fixo, com logo e padding menores (8165:2339) */
  var header = document.querySelector('[data-header]');
  if (header) {
    var onScroll = function () {
      header.classList.toggle('is-scrolled', window.scrollY > 8);
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  /* Menu mobile (8662:2946): gaveta em overlay que entra da esquerda.
     Abre no botão "Menu"; fecha em "Fechar", Esc, clique fora ou ao seguir um link.
     Enquanto aberta: rolagem da página travada, resto da página inerte e foco preso na gaveta. */
  var drawer = document.querySelector('[data-menu]');
  var opener = document.querySelector('[data-menu-open]');
  if (drawer && opener) {
    var root = document.documentElement;
    var mobile = window.matchMedia('(max-width: 767px)');
    var inerted = [];
    var focusable = 'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])';

    var setInert = function (on) {
      if (on) {
        inerted = Array.prototype.filter.call(document.body.children, function (el) {
          return el !== header && el.tagName !== 'SCRIPT' && !el.hasAttribute('inert');
        });
        var bar = opener.parentElement; // barra do header (logo + botão "Menu")
        if (bar && bar !== header) inerted.push(bar);
      }
      inerted.forEach(function (el) {
        if (on) el.setAttribute('inert', ''); else el.removeAttribute('inert');
      });
      if (!on) inerted = [];
    };

    var isOpen = function () { return drawer.classList.contains('is-open'); };

    var open = function () {
      if (isOpen()) return;
      drawer.classList.add('is-open');
      opener.setAttribute('aria-expanded', 'true');
      root.classList.add('has-menu-open');
      setInert(true);
      var first = drawer.querySelector(focusable);
      if (first) first.focus({ preventScroll: true });
    };

    var close = function (restoreFocus) {
      if (!isOpen()) return;
      drawer.classList.remove('is-open');
      opener.setAttribute('aria-expanded', 'false');
      root.classList.remove('has-menu-open');
      setInert(false);
      if (restoreFocus !== false) opener.focus({ preventScroll: true });
    };

    opener.addEventListener('click', function () {
      if (isOpen()) close(); else open();
    });

    drawer.addEventListener('click', function (e) {
      if (e.target.closest('[data-menu-close]')) {
        close();
        return;
      }
      var link = e.target.closest('a[href]');
      if (link && !link.hasAttribute('data-copy-email')) close(false); // o destino do link recebe o foco
    });

    document.addEventListener('keydown', function (e) {
      if (!isOpen()) return;
      if (e.key === 'Escape') {
        e.preventDefault();
        close();
        return;
      }
      if (e.key === 'Tab') { // mantém o Tab dentro da gaveta
        var items = Array.prototype.filter.call(drawer.querySelectorAll(focusable), function (el) {
          return el.offsetParent !== null || el === document.activeElement;
        });
        if (!items.length) return;
        var first = items[0];
        var last = items[items.length - 1];
        if (e.shiftKey && (document.activeElement === first || !drawer.contains(document.activeElement))) {
          e.preventDefault();
          last.focus();
        } else if (!e.shiftKey && (document.activeElement === last || !drawer.contains(document.activeElement))) {
          e.preventDefault();
          first.focus();
        }
      }
    });

    /* Passou para o layout desktop (≥768) com a gaveta aberta: fecha sem mover o foco */
    var onBreakpoint = function (mq) { if (!mq.matches) close(false); };
    if (mobile.addEventListener) mobile.addEventListener('change', onBreakpoint);
    else if (mobile.addListener) mobile.addListener(onBreakpoint);

    /* E-mail de contato (8662:2967): o clique copia o endereço; sem Clipboard API segue o mailto: */
    var email = drawer.querySelector('[data-copy-email]');
    var status = drawer.querySelector('[data-copy-status]');
    if (email && navigator.clipboard && window.isSecureContext) {
      var label = status ? status.textContent : '';
      var timer;
      email.addEventListener('click', function (e) {
        e.preventDefault();
        var href = email.getAttribute('href');
        navigator.clipboard.writeText(email.textContent.trim()).then(function () {
          if (!status) return;
          status.textContent = status.getAttribute('data-copied') || label;
          clearTimeout(timer);
          timer = setTimeout(function () { status.textContent = label; }, 2400);
        }, function () {
          window.location.href = href;
        });
      });
    }
  }
})();
