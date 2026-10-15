#!/usr/bin/env python
"""
Diff numérico Figma × render (gate da skill Pixel Perfect V2).

  python tools/diff.py SLUG [--mobile] [--page blog] [--tol 0.5] [--shot] [--url http://localhost:5500]

1. Gera preview/SLUG.html (tools/build.py --preview SLUG)
2. Abre no Chrome headless via DevTools (tools/cdp_measure.mjs) com viewport emulado
   em 1440px — ou 375px com --mobile (frame Mobile 8635:1583)
3. Lê rect + estilos computados de todo elemento com data-figma="<node id desktop>"
   (ou data-figma-m="<node id mobile>" com --mobile)
4. Compara com a geometria do Figma (tools/figma-meta.xml + complementos em
   tools/figma-meta.d/*.xml), ambos relativos ao elemento raiz da seção (data-figma-root)
5. Imprime tabela de Δ e sai com código 1 se algum |Δ| > tol

--shot salva preview/SLUG.png (ou SLUG.mobile.png) do tamanho da seção no Figma.
Complementos: cada arquivo em tools/figma-meta.d/ traz blocos de get_metadata cujo nó
raiz existe autofechado em figma-meta.xml (ou dentro de outro complemento) — o bloco
substitui a linha autofechada.
Requisitos: servidor estático em --url servindo a raiz do projeto; Node 22+.
"""
import argparse
import html
import json
import os
import re
import shutil
import subprocess
import sys
import tempfile

try:
    sys.stdout.reconfigure(encoding='utf-8')
except Exception:
    pass

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
META = os.path.join(ROOT, 'tools', 'figma-meta.xml')
META_D = os.path.join(ROOT, 'tools', 'figma-meta.d')
MOBILE_W = 375
CHROME_CANDIDATES = [
    r'C:\Program Files\Google\Chrome\Application\chrome.exe',
    r'C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe',
]

TAG_RE = re.compile(r'<(/?)([a-zA-Z-]+)((?:\s+[\w-]+="[^"]*")*)\s*(/?)>')
ATTR_RE = re.compile(r'([\w-]+)="([^"]*)"')


def load_figma():
    """Retorna {id: {name,type,x,y,w,h (absolutos no canvas), parent, depth}}."""
    text = open(META, encoding='utf-8').read()
    text = text[text.index('<canvas'):]
    text = apply_overlays(text)
    nodes, stack = {}, []
    for m in TAG_RE.finditer(text):
        closing, tag, attrs, selfclose = m.groups()
        if closing:
            if stack:
                stack.pop()
            continue
        a = {k: html.unescape(v) for k, v in ATTR_RE.findall(attrs)}
        if 'id' not in a:
            continue
        parent = stack[-1] if stack else None
        px, py = (nodes[parent]['x'], nodes[parent]['y']) if parent and parent in nodes and nodes[parent]['type'] != 'canvas' else (0.0, 0.0)
        nodes[a['id']] = {
            'name': a.get('name', ''), 'type': tag,
            'x': px + float(a.get('x', 0)), 'y': py + float(a.get('y', 0)),
            'w': float(a.get('width', 0)), 'h': float(a.get('height', 0)),
            'parent': parent, 'depth': len(stack), 'hidden': a.get('hidden') == 'true',
        }
        if not selfclose:
            stack.append(a['id'])
    return nodes


def apply_overlays(text):
    """Substitui nós autofechados pelos blocos com filhos de tools/figma-meta.d/*.xml."""
    if not os.path.isdir(META_D):
        return text
    blocks = []
    for name in sorted(os.listdir(META_D)):
        if name.endswith('.xml'):
            raw = open(os.path.join(META_D, name), encoding='utf-8').read()
            blocks.append(re.sub(r'<!--.*?-->', '', raw, flags=re.S))
    for _ in range(3):  # complementos podem expandir nós de outros complementos
        for bt in blocks:
            for m in TAG_RE.finditer(bt):
                if m.group(1) or m.group(4):
                    continue
                nid = dict(ATTR_RE.findall(m.group(3))).get('id')
                if not nid:
                    continue
                sc = re.search(r'<[a-zA-Z-]+ id="%s"[^>]*?/>' % re.escape(nid), text)
                if not sc:
                    continue
                depth, end = 0, None
                for t in TAG_RE.finditer(bt, m.start()):
                    if t.group(1):
                        depth -= 1
                    elif not t.group(4):
                        depth += 1
                    if depth == 0:
                        end = t.end()
                        break
                if end:
                    text = text[:sc.start()] + bt[m.start():end] + text[sc.end():]
    return text


def descendants(nodes, root_id, max_depth):
    base = nodes[root_id]['depth']
    out = []
    for nid, n in nodes.items():
        p, d = n['parent'], 0
        while p and p != root_id:
            p = nodes[p]['parent'] if p in nodes else None
            d += 1
        if p == root_id and d + 1 <= max_depth and not n['hidden']:
            out.append(nid)
    return out


def chrome():
    for c in CHROME_CANDIDATES:
        if os.path.exists(c):
            return c
    sys.exit('Chrome/Edge não encontrado')


def measure(url, width, shot=None, shot_h=None):
    cmd = ['node', os.path.join(ROOT, 'tools', 'cdp_measure.mjs'), url, str(width)]
    if shot:
        cmd += [shot, str(int(round(shot_h)))]
    r = subprocess.run(cmd, capture_output=True, timeout=180)
    if r.returncode != 0:
        sys.exit('Medidor falhou (%s) — o servidor está no ar? %s' % (url, r.stderr.decode('utf-8', 'replace')[:600]))
    return json.loads(r.stdout.decode('utf-8'))


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument('slug')
    ap.add_argument('--tol', type=float, default=0.5)
    ap.add_argument('--shot', action='store_true')
    ap.add_argument('--url', default='http://localhost:5500')
    ap.add_argument('--width', type=int, default=None)
    ap.add_argument('--mobile', action='store_true', help='mede em 375px contra os nós data-figma-m (frame Mobile)')
    ap.add_argument('--page', help='contexto de página do preview (home, blog, …) — ver tools/build.py')
    ap.add_argument('--attr', help='atributo com os node ids (padrão data-figma / data-figma-m); ex.: data-figma-blog para o header no Blog')
    a = ap.parse_args()
    width = a.width or (MOBILE_W if a.mobile else 1440)

    subprocess.run([sys.executable, os.path.join(ROOT, 'tools', 'build.py'), '--preview', a.slug] + (['--page', a.page] if a.page else []), check=True)
    url = '%s/preview/%s.html' % (a.url.rstrip('/'), a.slug)

    attr = a.attr or ('data-figma-m' if a.mobile else 'data-figma')
    url += '?m=1' if a.mobile else '?d=1'
    if a.attr:
        url += '&attr=' + a.attr
    figma = load_figma()
    data = measure(url, width)

    measured = {n['id']: n for n in data['nodes']}
    root_id = data.get('root')
    if not root_id or root_id not in figma:
        sys.exit('Raiz inválida: marque o elemento raiz da seção com data-figma-root e %s="<node id>" (achado: %r)' % (attr, root_id))
    fr = figma[root_id]

    print('\nSeção %s  raiz %s "%s"  Figma %gx%g   viewport %s   docH %s   largura da página %s' % (
        a.slug, root_id, fr['name'], fr['w'], fr['h'], data['viewport'], data['docH'], data.get('docW', '?')))
    bad_fonts = [f for f in data['fonts'] if not f.endswith('loaded')]
    if bad_fonts:
        print('! fontes não carregadas:', bad_fonts)
    broken = [i['src'] for i in data['images'] if not i['ok']]
    if broken:
        print('! imagens quebradas:', broken)
    if data.get('docW') and data['docW'] > data['viewport']:
        print('! rolagem horizontal: página com %spx num viewport de %spx' % (data['docW'], data['viewport']))

    fails = 0
    print('\n%-14s %-28s %-31s %-31s %s' % ('node', 'nome Figma', 'Figma x,y,w,h', 'Render x,y,w,h', 'Δ (x,y,w,h)'))
    for n in data['nodes']:
        f = figma.get(n['id'])
        if not f:
            print('%-14s %-28s %s' % (n['id'], '?? id não existe no Figma', ''))
            fails += 1
            continue
        fx, fy = f['x'] - fr['x'], f['y'] - fr['y']
        d = (n['x'] - fx, n['y'] - fy, n['w'] - f['w'], n['h'] - f['h'])
        flag = any(abs(v) > a.tol for v in d)
        fails += flag
        print('%-14s %-28.28s %-31s %-31s %s%s' % (
            n['id'], f['name'],
            '%g,%g,%g,%g' % (round(fx, 2), round(fy, 2), round(f['w'], 2), round(f['h'], 2)),
            '%g,%g,%g,%g' % (n['x'], n['y'], n['w'], n['h']),
            ','.join('%+.2f' % v for v in d), '   <-- DIFF' if flag else ''))

    print('\nEstilos computados (comparar com get_design_context):')
    for n in data['nodes']:
        if n['tag'] in ('p', 'h1', 'h2', 'h3', 'h4', 'span', 'a', 'em', 'strong', 'li', 'button', 'blockquote', 'cite', 'figcaption', 'small', 'dt', 'dd'):
            print('  %-12s %-4s %s %s/%s w%s %s ls=%s %s' % (n['id'], n['tag'], n['fontFamily'], n['fontSize'], n['lineHeight'], n['fontWeight'], n['fontStyle'], n['letterSpacing'], n['color']))
        else:
            extras = ' '.join(x for x in ['bg=' + n['bg'] if n['bg'] not in ('rgba(0, 0, 0, 0)', 'transparent') else '', 'pad=' + n['padding'] if n['padding'] != '0px' else '', 'gap=' + n['gap'] if n['gap'] not in ('normal', '') else '', 'r=' + n['radius'] if n['radius'] != '0px' else '', 'border=' + n['border'] if not n['border'].startswith('0px') else '', 'shadow=' + n['shadow'] if n['shadow'] else ''] if x)
            if extras:
                print('  %-12s %-4s %s' % (n['id'], n['tag'], extras))

    unmapped = [i for i in descendants(figma, root_id, 2) if i not in measured]
    if unmapped:
        print('\nNós Figma (até 2 níveis) sem %s no HTML — confira se deveriam ser medidos:' % attr)
        for i in unmapped:
            f = figma[i]
            print('  %-14s %-30.30s %gx%g' % (i, f['name'], round(f['w'], 2), round(f['h'], 2)))

    if a.shot:
        png = os.path.join(ROOT, 'preview', a.slug + ('.mobile' if a.mobile else '') + '.png')
        measure(url, width, png, fr['h'])
        print('\nscreenshot: %s (%dx%d)' % (os.path.relpath(png, ROOT), width, round(fr['h'])))

    print('\nResultado: %s (%d nós com |Δ| > %gpx)' % ('OK' if not fails else 'DIFF', fails, a.tol))
    sys.exit(1 if fails else 0)


if __name__ == '__main__':
    main()
