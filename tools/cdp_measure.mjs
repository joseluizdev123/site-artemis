// Mede uma página de preview no Chrome headless via DevTools Protocol, com viewport
// emulado na largura exata (inclusive 375 — a janela headless não desce de ~500px).
//
//   node tools/cdp_measure.mjs <url> <largura> [png-saida] [altura-png]
//
// Imprime o JSON gravado pelo medidor do preview (#__measure). Com png-saida, salva a
// screenshot da página do topo até altura-png (captura além do viewport).
import { spawn, execFileSync } from 'node:child_process';
import { existsSync, readFileSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';
import { makeProfile, removeProfile } from './chrome_profile.mjs';

const [url, widthArg, shotPath, shotHeightArg] = process.argv.slice(2);
const width = Number(widthArg || 1440);
const CHROME = [
  'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
  'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
].find(existsSync);
if (!CHROME) { console.error('Chrome/Edge não encontrado'); process.exit(2); }

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
const prof = makeProfile('cdp');
const chrome = spawn(CHROME, [
  '--headless=new', '--hide-scrollbars', '--no-first-run', '--no-default-browser-check',
  '--remote-debugging-port=0', '--user-data-dir=' + prof, '--window-size=1440,900', 'about:blank',
], { stdio: 'ignore' });

function cleanup() {
  try {
    if (process.platform === 'win32') execFileSync('taskkill', ['/PID', String(chrome.pid), '/T', '/F'], { stdio: 'ignore' });
    else chrome.kill('SIGKILL');
  } catch { /* já saiu */ }
  removeProfile(prof);
}

try {
  let port;
  for (let i = 0; i < 100 && !port; i++) {
    const f = join(prof, 'DevToolsActivePort');
    try { if (existsSync(f)) port = readFileSync(f, 'utf8').split('\n')[0].trim(); } catch { /* EBUSY: Chrome ainda escrevendo */ }
    if (!port) await sleep(100);
  }
  if (!port) throw new Error('Chrome não abriu a porta de depuração');

  const targets = await (await fetch(`http://127.0.0.1:${port}/json/list`)).json();
  const page = targets.find((t) => t.type === 'page');
  const ws = new WebSocket(page.webSocketDebuggerUrl);
  await new Promise((r, j) => { ws.addEventListener('open', r); ws.addEventListener('error', j); });
  let id = 0; const pending = new Map();
  ws.addEventListener('message', (m) => { const d = JSON.parse(m.data); if (d.id && pending.has(d.id)) { pending.get(d.id)(d); pending.delete(d.id); } });
  const send = (method, params = {}) => new Promise((r) => { const i = ++id; pending.set(i, r); ws.send(JSON.stringify({ id: i, method, params })); });

  await send('Emulation.setDeviceMetricsOverride', { width, height: 900, deviceScaleFactor: 1, mobile: width < 768 });
  await send('Page.enable');
  await send('Page.navigate', { url });

  let measure = null;
  for (let i = 0; i < 150 && !measure; i++) {
    await sleep(200);
    const r = await send('Runtime.evaluate', { expression: "(document.getElementById('__measure')||{}).textContent||''", returnByValue: true });
    const txt = r.result?.result?.value;
    if (txt) measure = JSON.parse(txt);
  }
  if (!measure) throw new Error('Medidor não rodou em ' + url);

  if (shotPath) {
    const h = Number(shotHeightArg || measure.rootH || 900);
    // viewport da altura da captura: imagens loading=lazy abaixo da dobra também carregam
    await send('Emulation.setDeviceMetricsOverride', { width, height: Math.max(900, Math.ceil(h)), deviceScaleFactor: 1, mobile: width < 768 });
    await sleep(1200);
    const r = await send('Page.captureScreenshot', { format: 'png', captureBeyondViewport: true, clip: { x: 0, y: 0, width, height: h, scale: 1 } });
    writeFileSync(shotPath, Buffer.from(r.result.data, 'base64'));
  }
  ws.close();
  process.stdout.write(JSON.stringify(measure));
} catch (e) {
  console.error(String(e && e.message || e));
  process.exitCode = 1;
} finally {
  cleanup();
}
