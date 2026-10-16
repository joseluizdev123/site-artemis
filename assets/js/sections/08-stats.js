/* Seção 08 — Stats: cada número conta de 0 até o valor quando a célula dele entra na tela
   (desktop: um por vez, da esquerda para a direita; mobile, empilhados: cada um conforme a rolagem).
   Conta o primeiro número de cada valor fora do prefixo/sufixo (.stats__unit), no formato
   brasileiro do próprio texto (40.000 · 97 · 86,3); no fim volta o texto original exato.
   Sem JS / prefers-reduced-motion o valor aparece pronto. */
(function () {
  'use strict';

  var list = document.querySelector('.stats__list');
  if (!list || !('IntersectionObserver' in window)) return;
  if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  var DURATION = 2000;  // ms de cada contagem
  var STAGGER = 1200;   // ms entre um número e o próximo: um por vez — o seguinte começa com o anterior já em ~94%
  var NUM = /\d{1,3}(?:\.\d{3})+(?:,\d+)?|\d+(?:,\d+)?/;

  function format(v, dec, group) {
    var parts = v.toFixed(dec).split('.');
    if (group) parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    return parts.join(',');
  }

  var counters = [];
  Array.prototype.forEach.call(list.querySelectorAll('.stats__item'), function (item) {
    var el = item.querySelector('.stats__value');
    var node = null, m = null;
    if (!el) return;
    for (var c = el.firstChild; c; c = c.nextSibling) {
      if (c.nodeType === 3 && (m = NUM.exec(c.nodeValue))) { node = c; break; }
    }
    if (!node) return;

    // O número vai para um span com a largura final (em em: vale no desktop e no mobile),
    // assim o sufixo não dança enquanto os dígitos mudam
    var raw = m[0];
    var num = node.splitText(m.index);
    num.splitText(raw.length);
    var span = document.createElement('span');
    span.className = 'stats__count';
    span.textContent = raw;
    num.parentNode.replaceChild(span, num);
    var em = parseFloat(getComputedStyle(span).fontSize) || 1;
    span.style.minWidth = (span.getBoundingClientRect().width / em).toFixed(3) + 'em';

    var comma = raw.indexOf(',');
    var c0 = {
      item: item,
      span: span,
      raw: raw,
      dec: comma < 0 ? 0 : raw.length - comma - 1,
      group: raw.indexOf('.') >= 0,
      target: parseFloat(raw.replace(/\./g, '').replace(',', '.'))
    };
    span.textContent = format(0, c0.dec, c0.group);
    counters.push(c0);
  });
  if (!counters.length) return;

  function run(c, delay) {
    var t0 = 0;
    function step(t) {
      if (!t0) t0 = t + delay;
      var k = Math.max(0, Math.min(1, (t - t0) / DURATION));
      var e = 1 - Math.pow(1 - k, 3); // easeOutCubic: desacelera até assentar no valor
      if (k < 1) {
        c.span.textContent = format(c.target * e, c.dec, c.group);
        requestAnimationFrame(step);
      } else {
        c.span.textContent = c.raw;
        c.span.style.minWidth = '';
      }
    }
    requestAnimationFrame(step);
  }

  // Começa com a célula 60% visível; quem recarregou mais abaixo (célula acima da tela) também
  // dispara. As que entram juntas contam em cascata, na ordem da página.
  var io = new IntersectionObserver(function (entries) {
    var batch = entries.filter(function (e) { return e.isIntersecting || e.boundingClientRect.top < 0; })
      .map(function (e) { return e.target; });
    counters.filter(function (c) { return batch.indexOf(c.item) >= 0; }).forEach(function (c, k) {
      io.unobserve(c.item);
      run(c, k * STAGGER);
    });
  }, { threshold: 0.6 });
  counters.forEach(function (c) { io.observe(c.item); });
})();
