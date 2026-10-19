// Palavra viúva (uma palavra sozinha na última linha de um bloco de texto): varre páginas em várias larguras.
//
//   node tools/widow_check.mjs <url-base> [--pages=/,/blog/] [--widths=320,375,1440] [--fallback] [--motion] [--verbose]
//
// Mede em repouso (movimento reduzido = Figma). Para cada bloco de texto (elemento não-inline com texto próprio),
// agrupa as palavras por linha (Range.getClientRects) e acusa quando a última linha tem 1 palavra só e o trecho
// tem 3 palavras ou mais (título de 2 palavras que quebra não tem como ser corrigido sem estourar a caixa).
// --fallback simula um navegador sem text-wrap: pretty (Firefox): desliga pretty/balance (text-wrap-style) e faz o CSS.supports negar,
// para exercitar o reforço do assets/js/main.js. --motion liga o movimento, rola até o fim e volta (as entradas tocam e
// repetem, devolvendo o HTML guardado) e mede depois de ArtemisMotion.finish(). Também acusa rolagem lateral. Nunca abre janela.
import { spawn, execFileSync } from 'node:child_process';
import { existsSync, readFileSync } from 'node:fs';
import { join } from 'node:path';
import { CHROME_FLAGS, makeProfile, removeProfile } from './chrome_profile.mjs';

const args = process.argv.slice(2);
const base = (args.find((a) => !a.startsWith('--')) || 'http://localhost:5500').replace(/\/$/, '');
const opt = (name, def) => { const a = args.find((x) => x.startsWith(`--${name}=`)); return a ? a.split('=')[1] : def; };
const PAGES = opt('pages', '/,/blog/,/blog/anatomia-do-wcontrol-como-se-valida-um-documento-de-mil-paginas/,/servicos/,/sobre/,/contato/,/mktops/,/artemis-seo/').split(',');
const WIDTHS = opt('widths', '320,375,390,414,768,1024,1280,1440,1920').split(',').map(Number);
const FALLBACK = args.includes('--fallback');
const VERBOSE = args.includes('--verbose');
const MOTION = args.includes('--motion');
const CHROME = ['C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe', 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe'].find(existsSync);
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

// Antes de qualquer script da página: sem pretty e CSS.supports negando text-wrap (como no Firefox)
const FALLBACK_SCRIPT = `(() => {
  const s = document.createElement('style');
  s.textContent = '*, *::before, *::after { text-wrap-style: auto !important; }';
  const put = () => { const p = document.head || document.documentElement; if (p && s.parentNode !== p) p.appendChild(s); };
  put();
  document.addEventListener('readystatechange', put);
  document.addEventListener('DOMContentLoaded', put);
  const o = CSS.supports.bind(CSS);
  CSS.supports = function (a, b) { return /text-wrap/.test(String(a)) ? false : o.apply(CSS, arguments); };
})()`;

const DETECT = `(async () => {
  await document.fonts.ready;
  await new Promise((r) => setTimeout(r, 300));
  if (${MOTION}) {
    const H = document.documentElement.scrollHeight, wait = (ms) => new Promise((r) => setTimeout(r, ms));
    for (let y = 0; y <= H; y += 400) { scrollTo(0, y); await wait(90); }
    await wait(2500);
    for (let y = H; y >= 0; y -= 600) { scrollTo(0, y); await wait(90); }
    await wait(2500);
    if (window.ArtemisMotion) window.ArtemisMotion.finish();
    await wait(400);
  }
  const inline = (d) => d === 'inline' || d === 'contents';
  const blockOf = (el) => { while (el && el !== document.body) { if (!inline(getComputedStyle(el).display)) return el; el = el.parentElement; } return document.body; };
  const name = (el) => { let s = el.tagName.toLowerCase(); if (el.classList.length) s += '.' + [...el.classList].slice(0, 2).join('.'); const p = el.parentElement && el.parentElement.closest('[class]'); return (p ? p.className.toString().split(' ')[0] + ' › ' : '') + s; };
  const blocks = new Map();
  const tw = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
  for (let n = tw.nextNode(); n; n = tw.nextNode()) {
    if (!/\\S/.test(n.data)) continue;
    const pe = n.parentElement;
    if (!pe || pe.closest('script,style,noscript,svg,textarea,select,option,template,.m-sr')) continue;
    const b = blockOf(pe);
    if (!blocks.has(b)) blocks.set(b, []);
    blocks.get(b).push(n);
  }
  const range = document.createRange();
  const out = [];
  for (const [b, nodes] of blocks) {
    const cs = getComputedStyle(b);
    if (cs.visibility === 'hidden' || (cs.webkitLineClamp && cs.webkitLineClamp !== 'none')) continue;
    const br = b.getBoundingClientRect();
    if (br.width <= 2 || br.height <= 2) continue; // texto só para leitor de tela (caixa de 1px)
    // só o trecho depois do último <br> do bloco (antes dele a quebra é forçada)
    const brs = [...b.querySelectorAll('br')].filter((x) => blockOf(x.parentElement) === b);
    const lastBr = brs[brs.length - 1];
    const seg = lastBr ? nodes.filter((t) => lastBr.compareDocumentPosition(t) & Node.DOCUMENT_POSITION_FOLLOWING) : nodes;
    // palavras: tokens não-brancos; um token que termina no fim de um nó e continua no início do próximo é a mesma palavra
    const words = [];
    let prevEndsGlued = false;
    for (const t of seg) {
      const re = /\\S+/g; let m;
      while ((m = re.exec(t.data))) {
        const glue = prevEndsGlued && m.index === 0 && words.length;
        if (glue) { const w = words[words.length - 1]; w.endNode = t; w.end = m.index + m[0].length; w.text += m[0]; }
        else words.push({ startNode: t, start: m.index, endNode: t, end: m.index + m[0].length, text: m[0] });
      }
      prevEndsGlued = t.data.length > 0 && !/\\s$/.test(t.data);
    }
    if (words.length < 3) continue;
    for (const w of words) {
      range.setStart(w.startNode, w.start); range.setEnd(w.endNode, w.end);
      const rs = range.getClientRects();
      const r = rs[rs.length - 1]; // palavra quebrada no hífen: vale o pedaço de baixo
      w.r = r && r.width ? r : null;
    }
    const ws = words.filter((w) => w.r);
    if (ws.length < 3) continue;
    // última linha = palavras que se sobrepõem na vertical à última (tamanhos diferentes na mesma linha contam juntos)
    const L = ws[ws.length - 1];
    const cy = (r) => (r.top + r.bottom) / 2;
    const lastLine = ws.filter((w) => (cy(w.r) >= L.r.top && cy(w.r) <= L.r.bottom) || (cy(L.r) >= w.r.top && cy(L.r) <= w.r.bottom));
    if (lastLine.length === 1 && ws.length > 1) {
      out.push({ el: name(b), word: lastLine[0].text, tail: seg.map((t) => t.data).join('').replace(/\\s+/g, ' ').trim().slice(-70) });
    }
  }
  return { widows: out, docW: document.documentElement.scrollWidth, vw: innerWidth, blocks: blocks.size };
})()`;

const prof = makeProfile('widow');
const chrome = spawn(CHROME, [...CHROME_FLAGS, '--remote-debugging-port=0', '--user-data-dir=' + prof, 'about:blank'], { stdio: 'ignore' });
let total = 0;
try {
  let port;
  for (let i = 0; i < 100 && !port; i++) {
    try { const f = join(prof, 'DevToolsActivePort'); if (existsSync(f)) port = readFileSync(f, 'utf8').split('\n')[0].trim(); } catch { /* EBUSY */ }
    if (!port) await sleep(100);
  }
  const page = (await (await fetch(`http://127.0.0.1:${port}/json/list`)).json()).find((t) => t.type === 'page');
  const ws = new WebSocket(page.webSocketDebuggerUrl);
  await new Promise((r) => ws.addEventListener('open', r));
  let id = 0; const pending = new Map(); const errors = [];
  ws.addEventListener('message', (m) => {
    const d = JSON.parse(m.data);
    if (d.id && pending.has(d.id)) { pending.get(d.id)(d); pending.delete(d.id); }
    if (d.method === 'Runtime.exceptionThrown') errors.push(d.params.exceptionDetails.exception?.description || d.params.exceptionDetails.text);
  });
  const send = (method, params = {}) => new Promise((r) => { const i = ++id; pending.set(i, r); ws.send(JSON.stringify({ id: i, method, params })); });
  const evaluate = async (expression) => (await send('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true })).result?.result?.value;
  await send('Runtime.enable');
  await send('Page.enable');
  await send('Emulation.setEmulatedMedia', { features: [{ name: 'prefers-reduced-motion', value: MOTION ? 'no-preference' : 'reduce' }] });
  if (FALLBACK) await send('Page.addScriptToEvaluateOnNewDocument', { source: FALLBACK_SCRIPT });

  for (const p of PAGES) {
    for (const w of WIDTHS) {
      await send('Emulation.setDeviceMetricsOverride', { width: w, height: 900, deviceScaleFactor: 1, mobile: w < 768 });
      await send('Page.navigate', { url: base + p });
      for (let i = 0; i < 100; i++) { await sleep(150); if (await evaluate("document.readyState === 'complete'")) break; }
      const r = await evaluate(DETECT);
      if (!r) { console.log(`ERRO ${p} ${w}: sem resultado`); total++; continue; }
      const scroll = r.docW > w ? `  <-- ROLAGEM LATERAL ${r.docW}px` : '';
      if (r.widows.length || scroll) total += r.widows.length + (scroll ? 1 : 0);
      console.log(`${r.widows.length ? 'VIÚVA' : 'ok   '} ${p} ${w}: ${r.widows.length} viúva(s) em ${r.blocks} blocos${scroll}`);
      if (r.widows.length && (VERBOSE || r.widows.length <= 12)) r.widows.forEach((x) => console.log(`        ${x.el} → "${x.word}" · …${x.tail}`));
    }
  }
  errors.slice(0, 5).forEach((e) => console.log('  erro JS:', String(e).split('\n')[0]));
  console.log(`total: ${total}${FALLBACK ? ' (modo sem pretty)' : ''}${MOTION ? ' (com movimento)' : ''}`);
  ws.close();
  if (total || errors.length) process.exitCode = 1;
} catch (e) {
  console.error(String(e && e.message || e)); process.exitCode = 2;
} finally {
  try { execFileSync('taskkill', ['/PID', String(chrome.pid), '/T', '/F'], { stdio: 'ignore' }); } catch { /* já saiu */ }
  removeProfile(prof);
}
