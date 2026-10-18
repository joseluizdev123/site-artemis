// Checagem do motion (assets/js/motion.js) numa página: Chrome headless, movimento LIBERADO.
//
//   node tools/motion_check.mjs <url> [largura]
//
// Carrega, rola até o fim em passos (como uma pessoa), espera as entradas terminarem e confere que tudo voltou ao
// repouso: nenhum alvo sem .is-done, nenhum span auxiliar (.m-w, .m-c, .m-count, .m-sr) sobrando, nenhuma rolagem
// lateral, sem erros de JS. Imprime também a altura do documento (compare com o page_check, que mede em
// movimento reduzido). Nunca abre janela; perfil temporário via tools/chrome_profile.mjs.
import { spawn, execFileSync } from 'node:child_process';
import { existsSync, readFileSync } from 'node:fs';
import { join } from 'node:path';
import { makeProfile, removeProfile } from './chrome_profile.mjs';

const url = process.argv[2] || 'http://localhost:5500/';
const width = Number(process.argv[3] || 1440);
const CHROME = ['C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe', 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe'].find(existsSync);
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
const prof = makeProfile('motion');
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
  let id = 0; const pending = new Map(); const errors = [];
  ws.addEventListener('message', (m) => {
    const d = JSON.parse(m.data);
    if (d.id && pending.has(d.id)) { pending.get(d.id)(d); pending.delete(d.id); }
    if (d.method === 'Runtime.exceptionThrown') errors.push(d.params.exceptionDetails.exception?.description || d.params.exceptionDetails.text);
    if (d.method === 'Runtime.consoleAPICalled' && d.params.type === 'error') errors.push(d.params.args.map((a) => a.value || a.description).join(' '));
  });
  const send = (method, params = {}) => new Promise((r) => { const i = ++id; pending.set(i, r); ws.send(JSON.stringify({ id: i, method, params })); });
  const evaluate = async (expression) => (await send('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true })).result?.result?.value;

  await send('Runtime.enable');
  await send('Emulation.setDeviceMetricsOverride', { width, height: 900, deviceScaleFactor: 1, mobile: width < 768 });
  await send('Emulation.setEmulatedMedia', { features: [{ name: 'prefers-reduced-motion', value: 'no-preference' }] });
  await send('Page.enable');
  await send('Page.navigate', { url });
  for (let i = 0; i < 100; i++) { await sleep(200); if (await evaluate("document.readyState === 'complete'")) break; }
  const start = await evaluate(`({ motion: document.documentElement.classList.contains('motion-on'), api: !!window.ArtemisMotion })`);
  const H = await evaluate('document.documentElement.scrollHeight');
  for (let y = 0; y <= H; y += 400) { await evaluate(`window.scrollTo(0, ${y})`); await sleep(120); }
  await sleep(3500);
  const out = await evaluate(`(() => {
    const T = '[data-reveal],[data-split],[data-wipe],[data-count],[data-type]';
    const pending = [...document.querySelectorAll(T)].filter((e) => !e.classList.contains('is-done')).map((e) => (e.className || e.tagName).toString().split(' ')[0] + '[' + [...e.attributes].filter((a) => a.name.startsWith('data-') && !a.name.startsWith('data-figma')).map((a) => a.name).join(',') + ']');
    const leftovers = document.querySelectorAll('.m-w, .m-c, .m-count, .m-sr, .m-br').length;
    return {
      targets: document.querySelectorAll(T).length, pending: pending.slice(0, 12), pendingCount: pending.length, leftovers,
      rolls: document.querySelectorAll('.m-roll').length,
      docW: document.documentElement.scrollWidth, vw: innerWidth, docH: document.documentElement.scrollHeight,
    };
  })()`);
  ws.close();
  console.log(`${url} @ ${width}px — motion-on ${start.motion} · api ${start.api} · alvos ${out.targets} · pendentes ${out.pendingCount}${out.pendingCount ? ' ' + out.pending.join(' ') : ''} · sobras ${out.leftovers} · botões com roll ${out.rolls} · página ${out.docW}×${out.docH}${out.docW > out.vw ? '  <-- ROLAGEM HORIZONTAL' : ''} · erros JS ${errors.length}`);
  errors.slice(0, 5).forEach((e) => console.log('  erro:', String(e).split('\n')[0]));
  if (out.pendingCount || out.leftovers || errors.length || out.docW > out.vw) process.exitCode = 1;
} catch (e) {
  console.error(String(e && e.message || e)); process.exitCode = 2;
} finally {
  try { execFileSync('taskkill', ['/PID', String(chrome.pid), '/T', '/F'], { stdio: 'ignore' }); } catch { /* já saiu */ }
  removeProfile(prof);
}
