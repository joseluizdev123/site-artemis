/* Serviços — as três etapas (Figma "Tabs" 8573:1797). Desktop (≥1024): cada cabeçalho é um botão que abre o painel
   da etapa (variantes etapa 1/2/3); a barra branca sob a etapa ativa enche em DURATION e passa para a próxima.
   O avanço automático pausa com o cursor/foco na seção, fora da tela ou com a aba oculta, e para de vez depois
   que a pessoa escolhe uma etapa. Com movimento reduzido não avança sozinho (a barra fica no estado do Figma).
   ≤1023: todas as etapas abertas em sequência (só CSS) — aqui só se ajusta o aria-expanded. */
(function () {
  'use strict';

  var grid = document.querySelector('[data-steps]');
  if (!grid) return;

  var DURATION = 9000; // ms por etapa
  var tabs = Array.prototype.slice.call(grid.querySelectorAll('.srv-step__tab'));
  var panels = tabs.map(function (tab) { return document.getElementById(tab.getAttribute('aria-controls')); });
  var stacked = window.matchMedia('(max-width: 1023px)');
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)');
  var section = grid.closest('section') || grid;

  var active = Math.max(0, tabs.findIndex(function (t) { return t.getAttribute('aria-expanded') === 'true'; }));
  var auto = !reduce.matches, hovering = false, visible = false, elapsed = 0, last = 0, raf = 0;

  function bars() { return grid.querySelectorAll('.srv-step__bar i'); }
  function setProgress(p) {
    Array.prototype.forEach.call(bars(), function (b) { b.style.setProperty('--progress', p.toFixed(4)); });
  }

  function show(i) {
    active = i;
    grid.setAttribute('data-active', String(i + 1));
    tabs.forEach(function (tab, k) {
      var on = stacked.matches || k === i;
      tab.setAttribute('aria-expanded', on ? 'true' : 'false');
      if (panels[k]) panels[k].hidden = !on;
    });
    elapsed = 0;
    setProgress(auto ? 0 : 0.122);
  }

  function running() { return auto && !stacked.matches && !hovering && visible && !document.hidden; }

  function tick(now) {
    raf = 0;
    if (!running()) { last = 0; return; }
    if (last) elapsed += now - last;
    last = now;
    if (elapsed >= DURATION) {
      show((active + 1) % tabs.length);
    } else {
      setProgress(elapsed / DURATION);
    }
    raf = requestAnimationFrame(tick);
  }
  function resume() { if (!raf && running()) { last = 0; raf = requestAnimationFrame(tick); } }

  tabs.forEach(function (tab, k) {
    tab.addEventListener('click', function () {
      if (stacked.matches) return;
      auto = false; // escolha da pessoa: para o avanço automático
      show(k);
    });
    tab.addEventListener('keydown', function (e) {
      if (stacked.matches) return;
      var dir = e.key === 'ArrowDown' || e.key === 'ArrowRight' ? 1 : (e.key === 'ArrowUp' || e.key === 'ArrowLeft' ? -1 : 0);
      if (!dir) return;
      e.preventDefault();
      var next = (k + dir + tabs.length) % tabs.length;
      tabs[next].focus();
      auto = false;
      show(next);
    });
  });

  section.addEventListener('pointerenter', function () { hovering = true; });
  section.addEventListener('pointerleave', function () { hovering = false; resume(); });
  section.addEventListener('focusin', function () { hovering = true; });
  section.addEventListener('focusout', function (e) { if (!section.contains(e.relatedTarget)) { hovering = false; resume(); } });
  document.addEventListener('visibilitychange', resume);

  if ('IntersectionObserver' in window) {
    new IntersectionObserver(function (entries) {
      visible = entries[0].isIntersecting;
      resume();
    }, { threshold: 0.3 }).observe(section);
  } else {
    visible = true;
  }

  function onLayout() { show(active); resume(); }
  if (stacked.addEventListener) stacked.addEventListener('change', onLayout); else stacked.addListener(onLayout);
  if (reduce.addEventListener) reduce.addEventListener('change', function () { if (reduce.matches) { auto = false; show(active); } });

  show(active);
  resume();
})();
