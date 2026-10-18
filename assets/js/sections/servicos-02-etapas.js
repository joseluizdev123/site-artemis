/* Serviços — as três etapas (Figma "Tabs" 8573:1797). Desktop (≥1024): cada cabeçalho é um botão que abre o painel
   da etapa (variantes etapa 1/2/3); a barra branca sob a etapa ativa enche e passa para a próxima — rápido com o
   cursor fora da seção (DURATION) e devagar com o cursor ou o foco dentro dela (DURATION_READ), para dar tempo de ler.
   Escolher uma etapa troca na hora e o avanço segue a partir dela. Pausa fora da tela e com a aba oculta; com
   movimento reduzido não avança sozinho (a barra fica no estado do Figma).
   ≤1023: todas as etapas abertas em sequência (só CSS) — aqui só se ajusta o aria-expanded.

   Entrada dos textos (.is-armed): as etapas chegam uma por vez quando a lista entra na tela (.is-in) e os itens do
   painel, um por vez, quando ele entra na tela e a cada troca (.is-entering). Como as entradas da biblioteca motion,
   cada uma volta ao estado inicial quando sai da tela por inteiro e toca de novo na volta (de cima ou de baixo).
   Sem JS / movimento reduzido / impressão, tudo pronto.

   Ilustração (.srv-art[data-rise]): monta com o scroll e depois flutua — o mesmo da Home (09-como-trabalhamos.js). */
(function () {
  'use strict';

  var grid = document.querySelector('[data-steps]');
  if (!grid) return;

  var DURATION = 6000;       // ms por etapa com o cursor fora da seção
  var DURATION_READ = 20000; // ms por etapa com o cursor (ou o foco) dentro da seção
  var tabs = Array.prototype.slice.call(grid.querySelectorAll('.srv-step__tab'));
  var heads = Array.prototype.slice.call(grid.querySelectorAll('.srv-step__head'));
  var panels = tabs.map(function (tab) { return document.getElementById(tab.getAttribute('aria-controls')); });
  var stacked = window.matchMedia('(max-width: 1023px)');
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)');
  var section = grid.closest('section') || grid;
  var animate = !reduce.matches && 'IntersectionObserver' in window;

  var active = Math.max(0, tabs.findIndex(function (t) { return t.getAttribute('aria-expanded') === 'true'; }));
  var auto = !reduce.matches, hovering = false, visible = false, progress = 0, last = 0, raf = 0;

  function bars() { return grid.querySelectorAll('.srv-step__bar i'); }
  function setProgress(p) {
    Array.prototype.forEach.call(bars(), function (b) { b.style.setProperty('--progress', p.toFixed(4)); });
  }

  // Reinicia a entrada dos itens do painel (remove a classe, força o reflow e devolve)
  function enter(panel) {
    if (!animate || !panel) return;
    panel.classList.remove('is-entering');
    void panel.offsetWidth;
    panel.classList.add('is-entering');
  }

  function show(i) {
    var changed = i !== active;
    active = i;
    grid.setAttribute('data-active', String(i + 1));
    tabs.forEach(function (tab, k) {
      var on = stacked.matches || k === i;
      tab.setAttribute('aria-expanded', on ? 'true' : 'false');
      var panel = panels[k];
      if (!panel) return;
      panel.hidden = !on;
      if (!on) panel.classList.remove('is-entering'); // volta a entrar na próxima vez que abrir
      else if (changed && !stacked.matches) enter(panel);
    });
    progress = 0;
    setProgress(auto ? 0 : 0.122);
  }

  function running() { return auto && !stacked.matches && visible && !document.hidden; }

  function tick(now) {
    raf = 0;
    if (!running()) { last = 0; return; }
    if (last) progress += (now - last) / (hovering ? DURATION_READ : DURATION);
    last = now;
    if (progress >= 1) {
      show((active + 1) % tabs.length);
    } else {
      setProgress(progress);
    }
    raf = requestAnimationFrame(tick);
  }
  function resume() { if (!raf && running()) { last = 0; raf = requestAnimationFrame(tick); } }

  tabs.forEach(function (tab, k) {
    tab.addEventListener('click', function () {
      if (stacked.matches) return;
      show(k); // escolha da pessoa: troca na hora e o avanço recomeça desta etapa
      resume();
    });
    tab.addEventListener('keydown', function (e) {
      if (stacked.matches) return;
      var dir = e.key === 'ArrowDown' || e.key === 'ArrowRight' ? 1 : (e.key === 'ArrowUp' || e.key === 'ArrowLeft' ? -1 : 0);
      if (!dir) return;
      e.preventDefault();
      var next = (k + dir + tabs.length) % tabs.length;
      tabs[next].focus();
      show(next);
    });
  });

  // Cursor ou foco de teclado dentro da seção: a etapa demora mais para trocar (não para). O foco que fica no
  // botão depois de um clique não conta — senão o ritmo seguiria lento com o cursor já fora.
  var pointerIn = false, keyboardIn = false;
  function syncReading() { hovering = pointerIn || keyboardIn; }
  function isKeyboardFocus(el) { try { return el.matches(':focus-visible'); } catch (err) { return true; } }
  section.addEventListener('pointerenter', function () { pointerIn = true; syncReading(); });
  section.addEventListener('pointerleave', function () { pointerIn = false; syncReading(); });
  section.addEventListener('focusin', function (e) { keyboardIn = isKeyboardFocus(e.target); syncReading(); });
  section.addEventListener('focusout', function (e) { if (!section.contains(e.relatedTarget)) { keyboardIn = false; syncReading(); } });
  document.addEventListener('visibilitychange', resume);

  if ('IntersectionObserver' in window) {
    new IntersectionObserver(function (entries) {
      visible = entries[0].isIntersecting;
      resume();
    }, { threshold: 0.3 }).observe(section);
  } else {
    visible = true;
  }

  // Entrada dos textos: ordem (--i) dos itens de cada painel e observador das etapas e painéis
  if (animate) {
    panels.forEach(function (panel) {
      if (!panel) return;
      var items = panel.querySelectorAll('.srv-info__icon, .srv-info__label, .srv-info__text, .srv-info__list li');
      Array.prototype.forEach.call(items, function (el, k) { el.style.setProperty('--i', k); });
    });
    section.classList.add('is-armed');

    // Entra quando passa de 90% da altura da tela. Painel oculto (outra etapa) espera a troca.
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        var el = e.target;
        if (el.hidden || !e.isIntersecting) return;
        el.classList.add(el.classList.contains('srv-step__head') ? 'is-in' : 'is-entering');
      });
    }, { rootMargin: '0px 0px -10% 0px' });
    // Fora da tela por inteiro: volta ao estado inicial (ninguém vê) para entrar de novo na volta
    var outIO = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) { if (!e.isIntersecting) e.target.classList.remove('is-in', 'is-entering'); });
    }, { threshold: 0 });
    heads.concat(panels).forEach(function (el) { if (el) { io.observe(el); outIO.observe(el); } });
  }

  function onLayout() { show(active); resume(); }
  if (stacked.addEventListener) stacked.addEventListener('change', onLayout); else stacked.addListener(onLayout);
  if (reduce.addEventListener) reduce.addEventListener('change', function () { if (reduce.matches) { auto = false; show(active); } });

  show(active);
  resume();
})();

/* Ilustração das etapas (.srv-art[data-rise]) — "monta" com o scroll, como a da Home (09-como-trabalhamos.js).
   Grava --p (0 → 1) conforme ela sobe na tela; o CSS sobe a camada da tela, desenha o tracejado e revela o selo.
   Suavizado para subir devagar. Montada (p = 1) ganha .is-built e, com o data-loop do HTML (.is-looping da biblioteca
   motion enquanto está na tela), as camadas flutuam sem parar. Quando sai da tela por inteiro volta a encaixar
   (p = 0, sem .is-built) sem ninguém ver; na volta — de cima ou de baixo — monta de novo até onde a rolagem pede. */
(function () {
  'use strict';

  var el = document.querySelector('.srv-art[data-rise]');
  if (!el) return;
  if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  var START = 0.9;  // topo da ilustração a 90% da altura da tela → p = 0
  var END = 0.4;    // topo da ilustração a 40% da altura da tela → p = 1
  var EASE = 0.08;  // fração do caminho percorrida por quadro (menor = mais devagar)

  var current = 0, target = 0, raf = 0, visible = false;

  function setP() {
    el.style.setProperty('--p', current.toFixed(4));
    if (current >= 0.999) el.classList.add('is-built');
  }

  function measure() {
    var vh = window.innerHeight || document.documentElement.clientHeight;
    var top = el.getBoundingClientRect().top;
    var p = (vh * START - top) / (vh * (START - END));
    target = p < 0 ? 0 : p > 1 ? 1 : p;
  }

  function tick() {
    raf = 0;
    current += (target - current) * EASE;
    if (Math.abs(target - current) < 0.001) current = target;
    setP();
    if (current !== target) raf = requestAnimationFrame(tick);
  }

  function update() {
    measure();
    if (!raf) raf = requestAnimationFrame(tick);
  }

  // Começa no estado "encaixado" e já posiciona conforme o scroll atual
  measure();
  current = target;
  setP();

  if ('IntersectionObserver' in window) {
    new IntersectionObserver(function (entries) {
      visible = entries[0].isIntersecting;
      if (visible) { update(); return; }
      cancelAnimationFrame(raf);
      raf = 0;
      current = target = 0;
      el.classList.remove('is-built');
      setP();
    }, { threshold: 0 }).observe(el);
  } else {
    visible = true;
  }

  window.addEventListener('scroll', function () { if (visible) update(); }, { passive: true });
  window.addEventListener('resize', update);
})();
