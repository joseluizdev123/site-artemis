"""Refaz o build sempre que um arquivo de partials/ ou src/ for salvo (para editar e ver no navegador).

    python tools/watch.py

CSS e JS de assets/ não precisam de build: o tools/serve.py serve sem cache, basta recarregar a página.
Edite os partials (partials/*.html), não index.html nem blog/ — esses são gerados e seriam sobrescritos.
"""
import os
import subprocess
import sys
import time

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
WATCH = [os.path.join(ROOT, 'partials'), os.path.join(ROOT, 'src')]


def snapshot():
    state = {}
    for base in WATCH:
        for d, _, files in os.walk(base):
            for f in files:
                p = os.path.join(d, f)
                try:
                    state[p] = os.path.getmtime(p)
                except OSError:
                    pass
    return state


def build():
    r = subprocess.run([sys.executable, os.path.join(ROOT, 'tools', 'build.py')], cwd=ROOT, capture_output=True, text=True)
    stamp = time.strftime('%H:%M:%S')
    if r.returncode == 0:
        print('[%s] build ok' % stamp, flush=True)
    else:
        print('[%s] build FALHOU:\n%s' % (stamp, (r.stderr or r.stdout).strip()), flush=True)


if __name__ == '__main__':
    print('observando partials/ e src/ — Ctrl+C para parar', flush=True)
    last = snapshot()
    build()
    while True:
        time.sleep(0.7)
        now = snapshot()
        if now != last:
            changed = [os.path.relpath(p, ROOT) for p in now if now.get(p) != last.get(p)]
            print('alterado: ' + ', '.join(changed[:5]), flush=True)
            last = now
            build()
