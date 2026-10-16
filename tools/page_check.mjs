// Checagem da página inteira (index.html) contra a pilha de seções do Figma.
//
//   node tools/page_check.mjs [url] [largura] [png-saida]
//
// Abre a página no Chrome headless (viewport emulado, sem scrollbar), espera fontes e
// imagens, e imprime: largura/altura do documento, rolagem horizontal e o y/altura de
// cada bloco (header, seções de <main>, footer) com o Δ contra o frame do Figma.
import { spawn, execFileSync } from 'node:child_process';
import { existsSync, readFileSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';
import { makeProfile, removeProfile } from './chrome_profile.mjs';

const url = process.argv[2] || 'http://localhost:5500/';
const width = Number(process.argv[3] || 1440);
const shotPath = process.argv[4];
const mobile = width < 768;

// Pilha do Figma: [nome, altura] — Home: desktop 8160:163 (8594) · mobile 8635:1583 (13162,47);
// Blog (URL com /blog): desktop 8294:1703 (6906) · mobile 8673:3845 (8037,41).
// No desktop o header fixo (80) fica sobre o topo do hero; no Figma ele ocupa os 80 primeiros px.
const BLOG = /\/blog\b/.test(url);
const POST = /\/blog\/[^/?#]+/.test(url); // post: desktop 8331:321 (6048) · mobile 8673:5776 (8416)
const SERVICOS = /\/servicos\b/.test(url); // serviços: desktop 8340:1700 (3523) · mobile 8699:4921 (6599)
const SOBRE = /\/sobre\b/.test(url); // sobre: desktop 8348:3076 (7855,45) · mobile 8689:1932 (10334,67)
const CONTATO = /\/contato\b/.test(url); // contato: desktop 8348:3835 (2075) · mobile 8693:2761 (2934)
const SEO = /\/artemis-seo\b/.test(url); // artemis seo: desktop 8348:4008 (3463,51) · mobile 8704:6450 (5860)
const MKTOPS = /\/mktops\b/.test(url); // mktops: desktop 8348:4181 (5137) · mobile 8695:3268 (7468,08)
const FIGMA = MKTOPS ? (mobile
  ? [['header', 60], ['hero', 1500.08], ['problema', 671], ['instalado', 1506], ['niveis', 1644], ['cta', 628], ['footer', 1459]]
  : [['header', 80], ['hero', 864], ['problema', 966], ['instalado', 911], ['niveis', 948], ['cta', 628], ['footer', 740]])
  : SEO ? (mobile
  ? [['header', 60], ['hero', 1237], ['problema', 464], ['formas', 2117], ['cta', 523], ['footer', 1459]]
  : [['header', 80], ['hero', 1081.51], ['problema', 418], ['formas', 682], ['cta', 462], ['footer', 740]])
  : CONTATO ? (mobile
  ? [['header', 60], ['contato', 1383], ['faixa', 32], ['footer', 1459]]
  : [['header', 80], ['contato', 1191], ['faixa', 64], ['footer', 740]])
  : SOBRE ? (mobile
  ? [['header', 60], ['hero', 342], ['por que', 734], ['tres coisas', 1004], ['audita', 560], ['equipe A', 2780.33], ['equipe B', 2678.33], ['onde', 717], ['footer', 1459]]
  : [['header', 80], ['hero', 522], ['por que', 917], ['tres coisas', 765], ['audita', 434], ['equipe A', 1051.67], ['equipe B', 2629.78], ['onde', 716], ['footer', 740]])
  : SERVICOS ? (mobile
  ? [['header', 60], ['hero', 450], ['etapas', 2698], ['nao fazemos', 1304], ['cta', 628], ['footer', 1459]]
  : [['header', 80], ['hero', 611], ['etapas', 593], ['nao fazemos', 871], ['cta', 628], ['footer', 740]])
  : POST ? (mobile
  ? [['header', 64], ['hero', 611], ['conteudo', 4913], ['continue lendo', 809], ['newsletter', 560], ['footer', 1459]]
  : [['header', 80], ['hero', 691], ['conteudo', 3324], ['continue lendo', 715], ['newsletter', 498], ['footer', 740]])
  : BLOG ? (mobile
  ? [['header', 60], ['hero', 813.16], ['destaque', 641.25], ['categoria 1', 900], ['categoria 2', 901], ['categoria 3', 901], ['categoria 4', 901], ['categoria 5', 901], ['newsletter', 560], ['footer', 1459]]
  : [['header', 80], ['hero', 964], ['destaque', 590], ['categoria 1', 806], ['categoria 2', 807], ['categoria 3', 807], ['categoria 4', 807], ['categoria 5', 807], ['newsletter', 498], ['footer', 740]])
  : mobile
  ? [['header', 60], ['hero', 1154], ['o que fazemos', 1096], ['nossos sistemas', 2649], ['depoimento', 501], ['como funciona', 1318], ['diagrama', 869], ['estudos', 1321.47], ['stats', 932], ['como trabalhamos', 1227], ['cta', 576], ['footer', 1459]]
  : [['header', 80], ['hero', 1072], ['o que fazemos', 639], ['nossos sistemas', 1033], ['depoimento', 443], ['como funciona', 814], ['diagrama', 843], ['estudos', 1055], ['stats', 382], ['como trabalhamos', 865], ['cta', 628], ['footer', 740]];

const CHROME = ['C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe', 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe'].find(existsSync);
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
const prof = makeProfile('page');
const chrome = spawn(CHROME, ['--headless=new', '--hide-scrollbars', '--no-first-run', '--remote-debugging-port=0', '--user-data-dir=' + prof, 'about:blank'], { stdio: 'ignore' });

try {
  let port;
  for (let i = 0; i < 100 && !port; i++) {
    try { const f = join(prof, 'DevToolsActivePort'); if (existsSync(f)) port = readFileSync(f, 'utf8').split('\n')[0].trim(); } catch { /* EBUSY */ }
    if (!port) await sleep(100);
  }
  const page = (await (await fetch(`http://127.0.0.1:${port}/json/list`)).json()).find((t) => t.type === 'page');
  const ws = new WebSocket(page.webSocketDebuggerUrl);
  await new Promise((r) => ws.addEventListener('open', r));
  let id = 0; const pending = new Map();
  ws.addEventListener('message', (m) => { const d = JSON.parse(m.data); if (d.id && pending.has(d.id)) { pending.get(d.id)(d); pending.delete(d.id); } });
  const send = (method, params = {}) => new Promise((r) => { const i = ++id; pending.set(i, r); ws.send(JSON.stringify({ id: i, method, params })); });
  const evaluate = async (expression) => (await send('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true })).result?.result?.value;

  await send('Emulation.setDeviceMetricsOverride', { width, height: 900, deviceScaleFactor: 1, mobile });
  await send('Emulation.setEmulatedMedia', { features: [{ name: 'prefers-reduced-motion', value: 'reduce' }] });
  await send('Page.enable');
  await send('Page.navigate', { url });
  for (let i = 0; i < 100; i++) { await sleep(200); if (await evaluate("document.readyState === 'complete'")) break; }
  await evaluate('document.fonts.ready.then(() => true)');
  await sleep(600);

  const out = await evaluate(`(() => {
    const els = [document.querySelector('.site-header'), ...document.querySelectorAll('main > *'), document.querySelector('footer')].filter(Boolean);
    return {
      viewport: innerWidth, docW: document.documentElement.scrollWidth, docH: document.documentElement.scrollHeight,
      blocks: els.map((e) => { const b = e.getBoundingClientRect(); return { cls: (e.className || e.tagName).toString().split(' ')[0], y: +(b.top + scrollY).toFixed(2), h: +b.height.toFixed(2) }; }),
      wide: [...document.querySelectorAll('body *')].filter((e) => { const b = e.getBoundingClientRect(); return b.right > innerWidth + 0.5 && getComputedStyle(e).position !== 'fixed' && !e.closest('[aria-hidden=true], .diagrama__scroll, [inert]'); }).slice(0, 8).map((e) => (e.className || e.tagName).toString().split(' ')[0])
    };
  })()`);
  if (shotPath) { // página inteira, com viewport da altura do documento para imagens lazy carregarem
    await send('Emulation.setDeviceMetricsOverride', { width, height: Math.ceil(out.docH), deviceScaleFactor: 1, mobile });
    await sleep(2500);
    const r = await send('Page.captureScreenshot', { format: 'png', captureBeyondViewport: true, clip: { x: 0, y: 0, width, height: out.docH, scale: 1 } });
    writeFileSync(shotPath, Buffer.from(r.result.data, 'base64'));
  }
  ws.close();

  let fy = 0;
  const total = FIGMA.reduce((s, f) => s + f[1], 0);
  console.log(`\n${url} @ ${width}px — viewport ${out.viewport}, página ${out.docW}×${out.docH} (Figma ${width}×${total.toFixed(2)})${out.docW > out.viewport ? '  <-- ROLAGEM HORIZONTAL' : ''}`);
  out.blocks.forEach((b, i) => {
    const f = FIGMA[i] || ['?', 0];
    const fyy = fy; fy += f[1];
    const dy = b.y - fyy, dh = b.h - f[1];
    const isHeader = i === 0;
    console.log(`  ${f[0].padEnd(18)} ${b.cls.padEnd(24)} y ${String(b.y).padStart(9)} (Δ ${dy >= 0 ? '+' : ''}${dy.toFixed(2)})  h ${String(b.h).padStart(8)} (Δ ${dh >= 0 ? '+' : ''}${dh.toFixed(2)})${!isHeader && (Math.abs(dy) > 1 || Math.abs(dh) > 1) ? '  <--' : ''}`);
  });
  if (out.wide.length) console.log('  elementos passando da borda direita:', out.wide.join(', '));
} catch (e) {
  console.error(String(e && e.message || e)); process.exitCode = 1;
} finally {
  try { execFileSync('taskkill', ['/PID', String(chrome.pid), '/T', '/F'], { stdio: 'ignore' }); } catch { /* já saiu */ }
  removeProfile(prof);
}
