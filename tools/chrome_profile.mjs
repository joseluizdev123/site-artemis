// Perfil temporário do Chrome headless (tools/cdp_measure.mjs, tools/page_check.mjs).
//
// Cada medição cria %TEMP%/artemis-<nome>-XXXX (~15 MB). Logo depois do taskkill o Chrome ainda
// segura arquivos do perfil por alguns instantes: apagar em seguida falhava em silêncio e as pastas
// se acumulavam (1457 pastas, ~21 GB, encheram o disco C: em 2026-10-16). Por isso:
//   - removeProfile tenta por até ~6 s, com pausa entre as tentativas;
//   - makeProfile apaga antes as pastas artemis-* com mais de 1 h que tenham sobrado de execuções
//     interrompidas (as recentes podem estar em uso por outra medição em paralelo).
import { mkdtempSync, readdirSync, rmSync, statSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';

const STALE_MS = 60 * 60 * 1000;
const sleepSync = (ms) => Atomics.wait(new Int32Array(new SharedArrayBuffer(4)), 0, 0, ms);

function purgeStale() {
  const now = Date.now();
  let names = [];
  try { names = readdirSync(tmpdir()); } catch { return; }
  for (const n of names) {
    if (!n.startsWith('artemis-')) continue;
    const p = join(tmpdir(), n);
    try {
      const st = statSync(p);
      if (st.isDirectory() && now - st.mtimeMs > STALE_MS) rmSync(p, { recursive: true, force: true });
    } catch { /* em uso ou já removida */ }
  }
}

export function makeProfile(name) {
  purgeStale();
  return mkdtempSync(join(tmpdir(), 'artemis-' + name + '-'));
}

export function removeProfile(prof) {
  for (let i = 0; i < 20; i++) {
    try { rmSync(prof, { recursive: true, force: true }); return true; } catch { sleepSync(300); }
  }
  return false; // fica para o purgeStale da próxima execução
}
