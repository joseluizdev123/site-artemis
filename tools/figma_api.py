#!/usr/bin/env python
"""
Acesso direto à API REST do Figma com token pessoal (alternativa ao conector MCP).

Token: variável de ambiente FIGMA_API_KEY/FIGMA_TOKEN (global) ou arquivo .figma-token (projeto ou ~)
(uma linha, só o token; o arquivo está no .gitignore e NÃO vai para o tema).
Crie em Figma → Settings → Security → Personal access tokens (escopo "File content: read-only").

  python tools/figma_api.py nodes 8294:509 [8294:579 …] [--depth 3]   # JSON dos nós (geometria, camadas)
  python tools/figma_api.py images 8294:534 [...] --out DIR [--scale 2] [--format png|svg]
"""
import argparse
import json
import os
import sys
import urllib.parse
import urllib.request

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
FILE_KEY = 'i23qiP70wQhTOjyhYLT9LM'  # Studio Artemis - Site Institucional

try:
    sys.stdout.reconfigure(encoding='utf-8')
except Exception:
    pass


def token():
    """FIGMA_API_KEY / FIGMA_TOKEN (variável de ambiente do usuário — vale para todos os projetos),
    ou arquivo .figma-token no projeto, ou ~/.figma-token."""
    t = (os.environ.get('FIGMA_API_KEY') or os.environ.get('FIGMA_TOKEN') or '').strip()
    for path in (os.path.join(ROOT, '.figma-token'), os.path.expanduser('~/.figma-token')):
        if not t and os.path.exists(path):
            t = open(path, encoding='utf-8').read().strip()
    if not t:
        sys.exit('Sem token: defina a variável de ambiente FIGMA_API_KEY (ou FIGMA_TOKEN), ou crie ~/.figma-token.')
    return t


def get(url):
    req = urllib.request.Request(url, headers={'X-Figma-Token': token()})
    with urllib.request.urlopen(req, timeout=120) as r:
        return json.loads(r.read().decode('utf-8'))


def nodes(ids, depth=None):
    q = {'ids': ','.join(ids)}
    if depth:
        q['depth'] = str(depth)
    return get('https://api.figma.com/v1/files/%s/nodes?%s' % (FILE_KEY, urllib.parse.urlencode(q)))


def images(ids, out, scale=2, fmt='png'):
    q = urllib.parse.urlencode({'ids': ','.join(ids), 'scale': scale, 'format': fmt})
    res = get('https://api.figma.com/v1/images/%s?%s' % (FILE_KEY, q))
    os.makedirs(out, exist_ok=True)
    saved = []
    for nid, url in (res.get('images') or {}).items():
        if not url:
            print('sem imagem para', nid)
            continue
        dest = os.path.join(out, '%s.%s' % (nid.replace(':', '-').replace(';', '_'), fmt))
        urllib.request.urlretrieve(url, dest)
        saved.append(dest)
        print(dest, os.path.getsize(dest), 'bytes')
    return saved


if __name__ == '__main__':
    ap = argparse.ArgumentParser()
    ap.add_argument('cmd', choices=['nodes', 'images'])
    ap.add_argument('ids', nargs='+')
    ap.add_argument('--depth', type=int)
    ap.add_argument('--out', default=os.path.join(ROOT, 'tmp-figma'))
    ap.add_argument('--scale', type=float, default=2)
    ap.add_argument('--format', default='png', choices=['png', 'svg', 'jpg'])
    a = ap.parse_args()
    if a.cmd == 'nodes':
        print(json.dumps(nodes(a.ids, a.depth), ensure_ascii=False)[:200000])
    else:
        images(a.ids, a.out, a.scale, a.format)
