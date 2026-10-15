/* Seção 07 — Estudos de impacto: carrossel (Figma 8201:3770 · variantes 8201:3769) */
(function () {
  'use strict';

  /* - setas ‹ › (8201:3697 / 8201:3700) avançam/voltam em loop
     - teclado: ← → Home End com o foco dentro do carrossel
     - toque: arrastar na horizontal troca de estudo
     - barra de progresso (8201:3694): 164px por estudo, via --estudos-step
     - a altura acompanha o estudo ativo (Gascat é mais alto), com transição
     - quem já rolou para dentro do estudo (topo escondido sob o header — sempre no mobile
       8635:3459, onde o estudo é mais alto que a tela) não vê as setas pularem: a altura
       troca na hora e a rolagem compensa a diferença, então setas e imagem ficam paradas
     - estudo sem rodapé (texto centralizado): altura do menor bloco de texto dos outros
       estudos (--estudos-text-min) */
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)');

  Array.prototype.forEach.call(document.querySelectorAll('[data-estudos]'), function (root) {
    var track = root.querySelector('[data-estudos-slides]');
    if (!track) return;
    var cards = Array.prototype.slice.call(track.children);
    var total = cards.length;
    root.style.setProperty('--estudos-total', total);
    if (total < 2) {
      root.classList.add('is-single');
      return;
    }

    // Perto da tela, carrega as imagens dos outros estudos (sem "piscar" branco na troca)
    var lazy = root.querySelectorAll('img[loading="lazy"]');
    var eager = function () {
      Array.prototype.forEach.call(lazy, function (img) { img.loading = 'eager'; });
    };
    if ('IntersectionObserver' in window) {
      var io = new IntersectionObserver(function (entries) {
        if (entries.some(function (en) { return en.isIntersecting; })) {
          io.disconnect();
          eager();
        }
      }, { rootMargin: '600px 0px' });
      io.observe(root);
    } else {
      eager();
    }

    var current = 0;
    cards.forEach(function (card, i) {
      if (card.classList.contains('is-active')) current = i;
    });

    /* Estudo sem rodapé (texto centralizado, 8284:168): o bloco de texto fica com a altura do
       menor bloco de texto dos outros estudos (--estudos-text-min; o CSS usa abaixo de 1440) —
       no 375 = 645 do estudo 1 (8635:3461), no 1440 = 573. Assim ele não fica mais alto que os
       outros (768) nem mais baixo (320, 1024) e as setas andam o mínimo entre os estudos. */
    var texts = cards.map(function (card) { return card.querySelector('.estudos__text'); });
    var centered = texts.filter(function (t) { return t && t.classList.contains('estudos__text--center'); });
    var others = texts.filter(function (t) { return t && !t.classList.contains('estudos__text--center'); });
    if (centered.length && others.length) {
      var syncTextMin = function () {
        var min = Infinity;
        others.forEach(function (t) { min = Math.min(min, t.getBoundingClientRect().height); });
        if (min > 0 && min < Infinity) root.style.setProperty('--estudos-text-min', min + 'px');
      };
      syncTextMin();
      if ('ResizeObserver' in window) {
        var ro = new ResizeObserver(syncTextMin);
        others.forEach(function (t) { ro.observe(t); });
      } else {
        window.addEventListener('resize', syncTextMin);
      }
    }

    function setState(card, active) {
      card.classList.toggle('is-active', active);
      if (active) {
        card.removeAttribute('aria-hidden');
        card.removeAttribute('inert');
      } else {
        card.setAttribute('aria-hidden', 'true');
        card.setAttribute('inert', '');
      }
    }

    var controls = root.querySelector('.estudos__controls');

    // Altura do header fixo depois do scroll (60) — o que fica escondido sob ele não conta como visível
    function headerOffset() {
      var h = parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--header-h-scrolled'));
      return isNaN(h) ? 0 : h;
    }

    // Rola a página pela diferença para o elemento voltar ao mesmo ponto da tela
    function keepInPlace(el, top) {
      var d = el.getBoundingClientRect().top - top;
      if (Math.abs(d) < 1) return;
      var html = document.documentElement;
      var behavior = html.style.scrollBehavior;
      html.style.scrollBehavior = 'auto'; // o html tem scroll-behavior: smooth
      window.scrollBy(0, d);
      html.style.scrollBehavior = behavior;
    }

    var timer = 0;
    function releaseHeight() {
      clearTimeout(timer);
      track.style.height = '';
    }

    function go(index) {
      index = (index + total) % total;
      if (index === current) return;
      var from = track.offsetHeight;
      var pin = controls && track.getBoundingClientRect().top < headerOffset();
      var pinTop = pin ? controls.getBoundingClientRect().top : 0;
      track.style.height = from + 'px';
      setState(cards[current], false);
      setState(cards[index], true);
      current = index;
      root.style.setProperty('--estudos-step', index + 1);
      var to = cards[index].offsetHeight;
      if (reduce.matches || from === to || pin) {
        releaseHeight();
        if (pin) keepInPlace(controls, pinTop);
        return;
      }
      track.style.height = to + 'px';
      clearTimeout(timer);
      timer = setTimeout(releaseHeight, 600); // garante a altura automática mesmo sem transitionend
    }

    track.addEventListener('transitionend', function (e) {
      if (e.target === track && e.propertyName === 'height') releaseHeight();
    });

    var prev = root.querySelector('[data-estudos-prev]');
    var next = root.querySelector('[data-estudos-next]');
    if (prev) prev.addEventListener('click', function () { go(current - 1); });
    if (next) next.addEventListener('click', function () { go(current + 1); });

    root.addEventListener('keydown', function (e) {
      var keys = { ArrowLeft: current - 1, ArrowRight: current + 1, Home: 0, End: total - 1 };
      if (!(e.key in keys) || e.altKey || e.ctrlKey || e.metaKey) return;
      e.preventDefault();
      go(keys[e.key]);
    });

    // Gesto de arrastar (toque/caneta): horizontal > 48px troca de estudo
    var startX = null, startY = 0;
    track.addEventListener('pointerdown', function (e) {
      if (e.pointerType === 'mouse') return;
      startX = e.clientX;
      startY = e.clientY;
    });
    track.addEventListener('pointerup', function (e) {
      if (startX === null) return;
      var dx = e.clientX - startX, dy = e.clientY - startY;
      startX = null;
      if (Math.abs(dx) > 48 && Math.abs(dx) > Math.abs(dy)) go(current + (dx < 0 ? 1 : -1));
    });
    track.addEventListener('pointercancel', function () { startX = null; });
  });
})();
