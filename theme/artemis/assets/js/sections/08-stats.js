/* Seção 08 — Stats: cada número conta de 0 até o valor quando a célula dele entra na tela
   (desktop: um por vez, da esquerda para a direita; mobile, empilhados: cada um conforme a rolagem).
   Conta o primeiro número de cada valor fora do prefixo/sufixo (.stats__unit), no formato
   brasileiro do próprio texto (40.000 · 97 · 86,3); no fim volta o texto original exato.
   - Leitor de tela: o número que conta é aria-hidden; ao lado, o valor real num span só para
     leitor de tela (.stats__sr) — nunca se ouve "0" nem um número pela metade.
   - O sufixo não anda: durante a contagem o número fica numa caixa com a largura do valor final,
     alinhado à direita (os dígitos da Exo são proporcionais: um valor intermediário mais largo
     passa um pouco para a esquerda, nunca empurra o sufixo). A largura é medida depois de a Exo
     carregar (document.fonts.ready) — com a fonte reserva sairia menor.
   - Impressão: os que ainda não contaram saem com o valor final.
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

  function setup() {
    var counters = [];
    Array.prototype.forEach.call(list.querySelectorAll('.stats__item'), function (item) {
      var el = item.querySelector('.stats__value');
      var node = null, m = null;
      if (!el) return;
      for (var c = el.firstChild; c; c = c.nextSibling) {
        if (c.nodeType === 3 && (m = NUM.exec(c.nodeValue))) { node = c; break; }
      }
      if (!node) return;

      // texto antes · [número visual, aria-hidden][valor real, só leitor de tela] · texto depois
      var raw = m[0];
      var num = node.splitText(m.index);
      num.splitText(raw.length);
      var span = document.createElement('span');
      span.className = 'stats__count';
      span.setAttribute('aria-hidden', 'true');
      span.textContent = raw;
      var sr = document.createElement('span');
      sr.className = 'stats__sr';
      sr.textContent = raw;
      num.parentNode.replaceChild(span, num);
      span.parentNode.insertBefore(sr, span.nextSibling);

      var em = parseFloat(getComputedStyle(span).fontSize) || 1;
      span.style.width = (span.getBoundingClientRect().width / em).toFixed(4) + 'em';
      span.classList.add('is-counting');

      var comma = raw.indexOf(',');
      var c0 = {
        item: item,
        span: span,
        sr: sr,
        raw: raw,
        done: false,
        dec: comma < 0 ? 0 : raw.length - comma - 1,
        group: raw.indexOf('.') >= 0,
        target: parseFloat(raw.replace(/\./g, '').replace(',', '.'))
      };
      span.textContent = format(0, c0.dec, c0.group);
      counters.push(c0);
    });
    return counters;
  }

  // Fim da contagem: volta o texto original, sem as caixas — o mesmo DOM de antes
  function finish(c) {
    if (c.done) return;
    c.done = true;
    var parent = c.span.parentNode;
    parent.replaceChild(document.createTextNode(c.raw), c.span);
    parent.removeChild(c.sr);
    parent.normalize();
  }

  function run(c, delay) {
    var t0 = 0;
    function step(t) {
      if (c.done) return;
      if (!t0) t0 = t + delay;
      var k = Math.max(0, Math.min(1, (t - t0) / DURATION));
      var e = 1 - Math.pow(1 - k, 3); // easeOutCubic: desacelera até assentar no valor
      if (k < 1) {
        c.span.textContent = format(c.target * e, c.dec, c.group);
        requestAnimationFrame(step);
      } else {
        finish(c);
      }
    }
    requestAnimationFrame(step);
  }

  function start() {
    var counters = setup();
    if (!counters.length) return;

    window.addEventListener('beforeprint', function () { counters.forEach(finish); });

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
  }

  // Mede só com a Exo carregada (até lá o valor final fica visível, sem contar). fonts.ready sozinho
  // não basta: pode resolver antes de a página pedir a fonte; load() pede a face usada nos valores.
  var first = list.querySelector('.stats__value');
  if (document.fonts && document.fonts.load && first) {
    var cs = getComputedStyle(first);
    document.fonts.load(cs.fontWeight + ' ' + cs.fontSize + ' ' + cs.fontFamily, '0123456789')
      .then(function () { return document.fonts.ready; }, function () { /* sem a fonte: mede com a reserva */ })
      .then(start);
  } else {
    start();
  }
})();
