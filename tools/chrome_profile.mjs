// Chrome headless das ferramentas de medição (tools/cdp_measure.mjs, tools/page_check.mjs, tools/motion_check.mjs).
//
// Lições (2026-10-16 e 2026-10-18, disco C: cheio duas vezes):
//   - cada perfil novo (%TEMP%/artemis-<nome>-XXXX, ~15 MB) ficava para trás: depois do taskkill o Chrome ainda segurava
//     arquivos e processos auxiliares (crashpad) sobreviviam, então o rm falhava em silêncio;
//   - cada perfil novo também fazia o component updater baixar pacotes CRX (~9 MB, %TEMP%/<guid>.tmp, cabeçalho "Cr24")
//     que nunca eram apagados: 656 numa hora, 11,6 GB no total.
// Por isso:
//   - CHROME_FLAGS desliga atualização de componentes, rede em segundo plano, crash reporter e cache em disco;
//   - removeProfile mata QUALQUER processo cujo comando cite o perfil e tenta apagar por até ~6 s;
//   - makeProfile apaga antes as pastas artemis-* com mais de 20 min (nenhuma medição dura isso) e os CRX "Cr24"
//     soltos com mais de 20 min.
import { execFileSync } from 'node:child_process';
import { closeSync, mkdtempSync, openSync, readdirSync, readSync, rmSync, statSync, unlinkSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';

export const CHROME_FLAGS = [
  '--headless=new', '--hide-scrollbars', '--no-first-run', '--no-default-browser-check',
  '--disable-component-update', '--disable-background-networking', '--disable-extensions', '--disable-default-apps',
  '--disable-sync', '--disable-crash-reporter', '--disable-breakpad', '--no-pings', '--metrics-recording-only',
  '--disk-cache-size=1', '--media-cache-size=1', '--disable-gpu-shader-disk-cache',
  '--window-size=1440,900', // janela inicial conhecida antes do Emulation.setDeviceMetricsOverride (uma medição saiu inflada sem isso)
];

const STALE_MS = 20 * 60 * 1000;
const sleepSync = (ms) => Atomics.wait(new Int32Array(new SharedArrayBuffer(4)), 0, 0, ms);

function isCrx(p) {
  let fd;
  try { fd = openSync(p, 'r'); const b = Buffer.alloc(4); readSync(fd, b, 0, 4, 0); return b.toString('ascii') === 'Cr24'; } catch { return false; } finally { if (fd !== undefined) try { closeSync(fd); } catch { /* ok */ } }
}

function purgeStale() {
  const now = Date.now();
  let names = [];
  try { names = readdirSync(tmpdir()); } catch { return; }
  for (const n of names) {
    const p = join(tmpdir(), n);
    try {
      if (n.startsWith('artemis-')) {
        const st = statSync(p);
        if (st.isDirectory() && now - st.mtimeMs > STALE_MS) rmSync(p, { recursive: true, force: true });
      } else if (/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\.tmp$/i.test(n)) {
        const st = statSync(p);
        if (st.isFile() && now - st.mtimeMs > STALE_MS && isCrx(p)) unlinkSync(p);
      }
    } catch { /* em uso ou já removido */ }
  }
}

export function makeProfile(name) {
  purgeStale();
  return mkdtempSync(join(tmpdir(), 'artemis-' + name + '-'));
}

function killProfileProcesses(prof) {
  if (process.platform !== 'win32') return;
  const leaf = prof.split(/[\\/]/).pop().replace(/'/g, "''");
  try {
    execFileSync('powershell', ['-NoProfile', '-NonInteractive', '-Command',
      `Get-CimInstance Win32_Process | Where-Object { $_.CommandLine -and $_.CommandLine -like '*${leaf}*' } | ForEach-Object { Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue }`],
    { stdio: 'ignore', timeout: 15000 });
  } catch { /* nada a matar */ }
}

export function removeProfile(prof) {
  killProfileProcesses(prof);
  for (let i = 0; i < 20; i++) {
    try { rmSync(prof, { recursive: true, force: true }); return true; } catch { sleepSync(300); }
  }
  return false; // fica para o purgeStale da próxima execução
}
