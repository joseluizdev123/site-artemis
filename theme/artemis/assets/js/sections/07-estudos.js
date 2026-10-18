/* Seção 07 — Estudos de impacto: carrossel (Figma 8201:3770 · variantes 8201:3769) */
(function () {
  'use strict';

  /* - setas ‹ › (8201:3697 / 8201:3700) avançam/voltam em loop
     - teclado: ← → Home End com o foco dentro do carrossel
     - toque: arrastar na horizontal troca de estudo
     - barra de progresso (8201:3694): 164px por estudo, via --estudos-step
     - altura reservada: todos os estudos ficam com a altura do mais alto (--estudos-h, medida a
       cada mudança de largura e quando as fontes carregam), então trocar de estudo — sozinho ou à
       mão — não empurra o conteúdo de baixo. Sem a medida (sem JS), a altura acompanha o estudo
       ativo com transição, e quem já rolou para dentro do estudo (topo sob o header) não vê as
       setas pularem: a rolagem compensa a diferença
     - estudo sem rodapé (texto centralizado): altura do menor bloco de texto dos outros
       estudos (--estudos-text-min)
     - troca automática, só com o carrossel na tela: a cada AUTOPLAY ms com o cursor fora e a cada
       AUTOPLAY_HOVER ms com o cursor em cima (tempo de ler — entrar/sair do carrossel só troca o
       prazo, sem zerar a contagem); segura durante o toque e com a aba escondida. Para de vez com
       o botão Pausar (primeiro elemento focável do carrossel, WCAG 2.2.2), com qualquer navegação à
       mão (seta, teclado, toque, arrasto) e quando o foco de teclado entra no carrossel; o botão
       Reproduzir retoma. Trocando sozinho, aria-live "off"; parado, "polite"
     - o estudo que sai ganha .is-leaving (texto sobe na diagonal e some) e o que entra ganha
       .is-entering (texto sobe na diagonal em sequência, pintura com zoom-out leve, captura
       deslizando no sentido da navegação via --estudos-dir — CSS) */
  var AUTOPLAY = 8000;
  var AUTOPLAY_HOVER = 20000;
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

    /* Medidas, sempre sem a altura reservada (senão cada medida inflaria a seguinte):
       1. Estudo sem rodapé (texto centralizado, 8284:168): o bloco de texto fica com a altura do
          menor bloco de texto dos outros estudos (--estudos-text-min; o CSS usa abaixo de 1440) —
          no 375 = 645 do estudo 1 (8635:3461), no 1440 = 573. Assim ele não fica mais alto que os
          outros (768) nem mais baixo (320, 1024).
       2. Altura reservada (--estudos-h): a do estudo mais alto. */
    var texts = cards.map(function (card) { return card.querySelector('.estudos__text'); });
    var centered = texts.filter(function (t) { return t && t.classList.contains('estudos__text--center'); });
    var others = texts.filter(function (t) { return t && !t.classList.contains('estudos__text--center'); });
    function measure() {
      root.style.removeProperty('--estudos-h');
      if (centered.length && others.length) {
        var min = Infinity;
        others.forEach(function (t) { min = Math.min(min, t.getBoundingClientRect().height); });
        if (min > 0 && min < Infinity) root.style.setProperty('--estudos-text-min', min + 'px');
      }
      var max = 0;
      cards.forEach(function (c) { max = Math.max(max, c.getBoundingClientRect().height); });
      if (max > 0) root.style.setProperty('--estudos-h', max + 'px');
    }
    measure();
    var lastW = root.clientWidth;
    if ('ResizeObserver' in window) {
      new ResizeObserver(function () {
        if (root.clientWidth === lastW) return; // só a largura muda a quebra das linhas
        lastW = root.clientWidth;
        measure();
      }).observe(root);
    } else {
      window.addEventListener('resize', measure);
    }
    if (document.fonts && document.fonts.ready) {
      document.fonts.ready.then(measure);
      if (document.fonts.addEventListener) document.fonts.addEventListener('loadingdone', measure);
    }
    window.addEventListener('load', measure); // fontes e imagens que chegaram depois

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

    var animTimer = 0;
    function clearAnim() {
      cards.forEach(function (c) { c.classList.remove('is-entering', 'is-leaving'); });
    }
    function animate(from, to, dir) {
      // Estudo que sai antes de terminar de entrar (duas trocas seguidas): segue com a entrada
      // enquanto o card some no fade — trocar para a saída faria o texto, ainda invisível, piscar
      var interrupted = from.classList.contains('is-entering');
      cards.forEach(function (c) {
        if (c !== from || !interrupted) c.classList.remove('is-entering', 'is-leaving');
      });
      if (reduce.matches) return;
      to.style.setProperty('--estudos-dir', dir);
      void to.offsetWidth; // reinicia as animações se o mesmo estudo voltar logo
      if (!interrupted) from.classList.add('is-leaving');
      to.classList.add('is-entering');
      clearTimeout(animTimer);
      animTimer = setTimeout(clearAnim, 2600);
    }

    /* 1ª entrada: com o movimento liberado (html.motion-on, biblioteca Motion), o estudo ativo espera
       escondido (.is-pending, mesmo estado inicial da troca) e entra como numa troca quando o carrossel
       chega na tela; a contagem da troca automática começa aí. Já passou da tela ao carregar: fica pronto. */
    var pending = null;
    function revealPending() {
      if (pending) pending.classList.remove('is-pending');
      pending = null;
    }
    function enterFirst() {
      if (!pending) return;
      var card = pending;
      pending = null;
      card.style.setProperty('--estudos-dir', 1);
      card.classList.remove('is-pending');
      card.classList.add('is-entering');
      clearTimeout(animTimer);
      animTimer = setTimeout(clearAnim, 2600);
    }
    if (!reduce.matches && document.documentElement.classList.contains('motion-on') &&
        root.getBoundingClientRect().bottom > 0) {
      pending = cards[current];
      pending.classList.add('is-pending');
      window.addEventListener('beforeprint', revealPending);
    }

    function go(index, dir) {
      index = (index + total) % total;
      if (index === current) return;
      revealPending();
      animate(cards[current], cards[index], dir || (index > current ? 1 : -1));
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
    var toggle = root.querySelector('[data-estudos-toggle]');
    // Troca automática
    var playing = !reduce.matches;
    var auto = 0, shownAt = Date.now(), inView = false, hovering = false, touching = false;
    function canPlay() {
      return playing && inView && !touching && !document.hidden;
    }
    function syncToggle() {
      track.setAttribute('aria-live', playing ? 'off' : 'polite');
      if (!toggle) return;
      toggle.classList.toggle('is-paused', !playing);
      toggle.setAttribute('aria-label', toggle.getAttribute(playing ? 'data-label-pause' : 'data-label-play'));
    }
    function setPlaying(on) {
      playing = on;
      syncToggle();
      schedule();
    }
    // keep = true: só troca o prazo (cursor entrou/saiu), sem zerar o tempo já passado no estudo
    function schedule(keep) {
      clearTimeout(auto);
      auto = 0;
      if (keep !== true) shownAt = Date.now();
      if (!canPlay()) return;
      var wait = (hovering ? AUTOPLAY_HOVER : AUTOPLAY) - (Date.now() - shownAt);
      auto = setTimeout(function () { show(current + 1, 1, false); }, Math.max(1500, wait));
    }
    // Navegação à mão para a troca automática de vez (o anúncio "polite" nunca é cortado)
    function show(index, dir, manual) {
      if (manual && playing) {
        playing = false;
        syncToggle();
      }
      go(index, dir);
      schedule();
    }
    if (toggle && !reduce.matches) {
      toggle.hidden = false;
      toggle.addEventListener('click', function () { setPlaying(!playing); });
    }
    syncToggle();

    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (entries) {
        inView = entries[0].isIntersecting;
        if (inView) enterFirst();
        schedule();
      }, { threshold: 0.35 }).observe(root);
    }
    document.addEventListener('visibilitychange', function () { schedule(); });
    root.addEventListener('pointerenter', function (e) {
      if (e.pointerType !== 'mouse') return;
      hovering = true;
      schedule(true);
    });
    root.addEventListener('pointerleave', function (e) {
      if (e.pointerType !== 'mouse') return;
      hovering = false;
      schedule(true);
    });
    // Foco de teclado entrando no carrossel para a troca (o foco que o clique do mouse deixa na
    // seta não conta); lá dentro, o botão Reproduzir retoma
    root.addEventListener('focusin', function (e) {
      if (!playing || (e.relatedTarget && root.contains(e.relatedTarget))) return;
      var kb = true;
      try { kb = e.target.matches(':focus-visible'); } catch (err) { /* navegador sem :focus-visible */ }
      if (kb) setPlaying(false);
    });
    // Toque: segura enquanto o dedo está na tela; o toque que termina sem virar rolagem é
    // interação com o carrossel e para a troca de vez (o botão cuida de si mesmo)
    root.addEventListener('pointerdown', function (e) {
      if (e.pointerType === 'mouse') return;
      touching = true;
      schedule();
    });
    root.addEventListener('pointerup', function (e) {
      if (e.pointerType === 'mouse' || !touching) return;
      touching = false;
      if (playing && !(toggle && toggle.contains(e.target))) setPlaying(false);
      else schedule();
    });
    root.addEventListener('pointercancel', function (e) {
      if (e.pointerType === 'mouse' || !touching) return;
      touching = false; // virou rolagem da página: retoma
      schedule();
    });

    if (prev) prev.addEventListener('click', function () { show(current - 1, -1, true); });
    if (next) next.addEventListener('click', function () { show(current + 1, 1, true); });

    root.addEventListener('keydown', function (e) {
      var keys = { ArrowLeft: current - 1, ArrowRight: current + 1, Home: 0, End: total - 1 };
      if (!(e.key in keys) || e.altKey || e.ctrlKey || e.metaKey) return;
      e.preventDefault();
      show(keys[e.key], e.key === 'ArrowLeft' || e.key === 'Home' ? -1 : 1, true);
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
      if (Math.abs(dx) > 48 && Math.abs(dx) > Math.abs(dy)) show(current + (dx < 0 ? 1 : -1), dx < 0 ? 1 : -1, true);
    });
    track.addEventListener('pointercancel', function () { startX = null; });
  });
})();
