/* Seção 08 — Stats: contador de rolo (odômetro) — referência: galeria Animation do Awwwards.
   Cada dígito do primeiro número de cada valor (fora do prefixo/sufixo .stats__unit) vira uma coluna com
   máscara da altura da linha; a coluna rola de 0 até o dígito final — as da direita dão mais voltas — e
   para com easing suave, da esquerda para a direita, num escalonamento curto. Pontuação (. de milhar, ,)
   e sufixos (R$, /MÊS, %, min) ficam parados. Cada coluna tem a largura do dígito final (medida com a Exo
   carregada): nada empurra o layout.
   - Quando a célula entra na tela (60% visível) rola; as que entram juntas rolam em sequência (desktop:
     os quatro, da esquerda para a direita; mobile, empilhados: cada um conforme a rolagem).
   - Repete: a célula que sai da tela por inteiro volta ao zero (fora da tela) e rola de novo ao voltar.
   - Leitor de tela: as colunas são aria-hidden; o valor real fica num span visualmente oculto (.stats__sr).
   - No fim volta o DOM original (texto puro). Impressão: valor final. Sem JS / movimento reduzido: o texto
     do Figma, sem nada armado.
   - Faixa de elipses de baixo ([data-stats-band-end]): abre do centro depois que o último número para
     (.is-opening → .is-open) e fecha de novo quando ela mesma sai da tela por inteiro — não depende dos
     números de cima terem rolado (no celular o 1º pode já ter saído da tela). */
(function () {
  'use strict';

  var list = document.querySelector('.stats__list');
  if (!list || !('IntersectionObserver' in window)) return;
  var reduceMQ = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)') : null;
  if (reduceMQ && reduceMQ.matches) return;

  var BASE = 1300;      // ms da coluna da esquerda
  var COL_STEP = 140;   // ms a mais por coluna à direita — param da esquerda para a direita
  var STAGGER = 350;    // ms entre um número e o próximo
  var NUM = /\d{1,3}(?:\.\d{3})+(?:,\d+)?|\d+(?:,\d+)?/;
  var band = document.querySelector('[data-stats-band-end]');

  // Valor = { item, value, raw, node(antes), odo, sr, cols, state: 'raw' | 'armed' | 'rolling' | 'done' }
  function find(item) {
    var el = item.querySelector('.stats__value');
    if (!el) return null;
    for (var c = el.firstChild; c; c = c.nextSibling) {
      var m = c.nodeType === 3 && NUM.exec(c.nodeValue);
      if (m) return { item: item, value: el, raw: m[0], state: 'raw' };
    }
    return null;
  }

  // Monta as colunas no lugar do número (estado inicial: todas no 0)
  function arm(v) {
    if (v.state === 'armed') return;
    restore(v);
    var node = null, m = null;
    for (var c = v.value.firstChild; c; c = c.nextSibling) {
      if (c.nodeType === 3 && (m = NUM.exec(c.nodeValue)) && m[0] === v.raw) { node = c; break; }
    }
    if (!node) return;
    var num = node.splitText(m.index);
    num.splitText(v.raw.length);

    var cs = getComputedStyle(v.value);
    var fs = parseFloat(cs.fontSize) || 16;
    var lh = (parseFloat(cs.lineHeight) || fs * 1.2) / fs; // em

    var odo = document.createElement('span');
    odo.className = 'stats__odo';
    odo.setAttribute('aria-hidden', 'true');
    odo.style.setProperty('--odo-lh', lh.toFixed(4) + 'em');
    var sr = document.createElement('span');
    sr.className = 'stats__sr';
    sr.textContent = v.raw;

    // Largura de cada dígito final, medida no próprio valor (mesma fonte e corpo)
    var probe = document.createElement('span');
    probe.style.cssText = 'position:absolute;visibility:hidden;white-space:pre';
    v.value.appendChild(probe);
    var digits = v.raw.replace(/\D/g, '').length;
    var cols = [], seen = 0;
    for (var i = 0; i < v.raw.length; i++) {
      var ch = v.raw[i];
      if (!/\d/.test(ch)) {
        var p = document.createElement('span');
        p.className = 'stats__odo-sep';
        p.textContent = ch;
        odo.appendChild(p);
        continue;
      }
      probe.textContent = ch;
      var w = probe.getBoundingClientRect().width / fs;
      var d = +ch;
      var fromRight = digits - 1 - seen;           // 0 = coluna da direita
      var turns = 1 + fromRight;                    // a direita dá mais voltas
      var reel = document.createElement('span');
      reel.className = 'stats__odo-reel';
      var seq = '';
      for (var t = 0; t < turns; t++) seq += '0123456789';
      seq += '0123456789'.slice(0, d + 1);          // termina no dígito final
      for (var k = 0; k < seq.length; k++) {
        var s = document.createElement('span');
        s.textContent = seq[k];
        reel.appendChild(s);
      }
      var col = document.createElement('span');
      col.className = 'stats__odo-col';
      col.style.width = w.toFixed(4) + 'em';
      col.style.setProperty('--odo-to', 'calc(var(--odo-lh) * ' + -(seq.length - 1) + ')');
      col.style.setProperty('--odo-dur', BASE + seen * COL_STEP + 'ms');
      col.appendChild(reel);
      odo.appendChild(col);
      cols.push(col);
      seen++;
    }
    probe.remove();
    num.parentNode.replaceChild(odo, num);
    odo.parentNode.insertBefore(sr, odo.nextSibling);
    v.odo = odo;
    v.sr = sr;
    v.cols = cols;
    v.state = 'armed';
  }

  // Volta o texto original (DOM de antes)
  function restore(v) {
    clearTimeout(v.timer);
    if (v.odo && v.odo.parentNode) {
      var parent = v.odo.parentNode;
      parent.replaceChild(document.createTextNode(v.raw), v.odo);
      if (v.sr && v.sr.parentNode) v.sr.parentNode.removeChild(v.sr);
      parent.normalize();
    }
    v.odo = v.sr = null;
    v.cols = [];
    v.state = 'raw';
  }

  function roll(v, delay) {
    if (v.state !== 'armed') return;
    v.state = 'rolling';
    v.cols.forEach(function (c) { c.style.setProperty('--odo-delay', delay + 'ms'); });
    void v.odo.offsetWidth; // aplica o estado inicial antes da transição
    v.odo.classList.add('is-rolling');
    var last = BASE + (v.cols.length - 1) * COL_STEP;
    v.timer = setTimeout(function () { finish(v); }, delay + last + 80);
  }

  function finish(v) {
    if (v.state === 'done') return;
    restore(v);
    v.state = 'done';
    maybeOpenBand();
  }

  // Faixa de baixo: abre quando o último número parou (e nenhum está rolando); fecha quando sai da tela
  function maybeOpenBand() {
    if (!band || band.classList.contains('is-opening') || band.classList.contains('is-open')) return;
    var lastV = values[values.length - 1];
    if (!lastV || lastV.state !== 'done' || values.some(function (v) { return v.state === 'rolling'; })) return;
    band.classList.add('is-opening');
    var settle = function () { band.classList.remove('is-opening'); band.classList.add('is-open'); };
    band.addEventListener('animationend', settle, { once: true });
    setTimeout(function () { if (band.classList.contains('is-opening')) settle(); }, 1400);
  }
  function closeBand() {
    if (band) band.classList.remove('is-opening', 'is-open');
  }

  var values = [];

  function start() {
    values = Array.prototype.map.call(list.querySelectorAll('.stats__item'), find).filter(Boolean);
    if (!values.length) { if (band) band.classList.add('is-open'); return; }

    // Já passou da tela ao carregar: fica pronto (e rola quando voltar)
    values.forEach(function (v) {
      if (v.item.getBoundingClientRect().bottom <= 0) v.state = 'done';
      else arm(v);
    });
    maybeOpenBand();

    // Rola com a célula 60% visível; as que entram juntas rolam em sequência, na ordem da página
    var playIO = new IntersectionObserver(function (entries) {
      var batch = entries.filter(function (e) { return e.isIntersecting; }).map(function (e) { return e.target; });
      values.filter(function (v) { return batch.indexOf(v.item) >= 0 && v.state === 'armed'; })
        .forEach(function (v, k) { roll(v, k * STAGGER); });
    }, { threshold: 0.6 });
    // Repetição: saiu da tela por inteiro → volta ao zero (sem piscar: está fora da tela)
    var resetIO = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) return;
        var v = values.filter(function (x) { return x.item === e.target; })[0];
        if (!v || v.state === 'armed') return;
        arm(v);
      });
    }, { threshold: 0 });
    values.forEach(function (v) { playIO.observe(v.item); resetIO.observe(v.item); });
    // A faixa fecha quando sai da tela por inteiro; ao voltar, abre se o último número já estiver parado
    var bandIO = band && new IntersectionObserver(function (entries) {
      if (entries[0].isIntersecting) maybeOpenBand();
      else closeBand();
    }, { threshold: 0 });
    if (bandIO) bandIO.observe(band);

    // Impressão e movimento reduzido ligado com a página aberta: tudo no valor final
    var finishAll = function () { values.forEach(finish); if (band) { band.classList.remove('is-opening'); band.classList.add('is-open'); } };
    window.addEventListener('beforeprint', finishAll);
    if (reduceMQ && reduceMQ.addEventListener) reduceMQ.addEventListener('change', function () {
      if (!reduceMQ.matches) return;
      playIO.disconnect();
      resetIO.disconnect();
      if (bandIO) bandIO.disconnect();
      finishAll();
    });
  }

  // Mede só com a Exo carregada (até lá o valor fica no texto final). fonts.ready sozinho não basta: pode
  // resolver antes de a página pedir a fonte; load() pede a face usada nos valores.
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
