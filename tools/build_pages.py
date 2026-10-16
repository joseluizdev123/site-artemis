"""Gera _site/ para o GitHub Pages a partir do build estático (index.html, blog/, assets/).

O site usa caminhos absolutos (/assets/…, /blog/) — certo na raiz (localhost:5500, domínio próprio),
errado num site de projeto do Pages, servido em /<repositório>/. Aqui os atributos de URL das páginas
(href, src, srcset, data-*, content) e os url(/…) de style="" que começam com "/" ganham o prefixo BASE; CSS e JS já usam
caminhos relativos e são copiados como estão.

    python tools/build_pages.py [--base /site-artemis] [--out _site]

Páginas fora do Pages: EXCLUDE (chave de src/pages.json → pastas de imagem só dela). A página, o CSS/JS das seções
que só ela usa e essas pastas não são copiados; links para ela viram "#".

Rode depois de python tools/build.py (o workflow .github/workflows/pages.yml faz os dois).
"""
import argparse
import os
import re
import shutil

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
ATTR_RE = re.compile(r'(\s(?:href|src|srcset|content|action|poster|data-[\w-]+)=")([^"]*)(")')
EXCLUDE = {'seo': ['images/seo']}  # Artemis SEO: fica no projeto e no tema, não vai para o Pages
URL_RE = re.compile(r"""url\((["']?)/(?!/)""")


def prefix_url(url, base):
    if url.startswith('//') or not url.startswith('/'):
        return url
    return base + url


def rewrite_attr(m, base):
    name, value = m.group(1), m.group(2)
    if 'srcset=' in name:  # "url 1x, url 2x"
        parts = [p.strip() for p in value.split(',')]
        value = ', '.join(' '.join([prefix_url(p.split(' ', 1)[0], base)] + p.split(' ', 1)[1:]) for p in parts if p)
    else:
        value = prefix_url(value, base)
    return name + value + m.group(3)


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument('--base', default='/site-artemis', help='caminho do site no Pages ("" para a raiz)')
    ap.add_argument('--out', default='_site')
    a = ap.parse_args()
    base = a.base.rstrip('/')
    out = os.path.join(ROOT, a.out)
    if os.path.isdir(out):
        shutil.rmtree(out)
    import json
    spec = json.load(open(os.path.join(ROOT, 'src', 'pages.json'), encoding='utf-8'))

    def slugs(p):
        return {x if isinstance(x, str) else x['slug'] for x in (p['sections'] if isinstance(p['sections'], list) else [])} | set(p.get('assets', []))

    kept = {k: p for k, p in spec.items() if k not in EXCLUDE}
    used = set().union(*(slugs(p) for p in kept.values()))
    drop = {os.path.normpath(d) for k in EXCLUDE if k in spec for d in EXCLUDE[k]}
    for k in EXCLUDE:
        for sl in slugs(spec.get(k, {'sections': []})) - used:
            drop |= {os.path.normpath('css/sections/%s.css' % sl), os.path.normpath('js/sections/%s.js' % sl)}
    dead = ['/' + os.path.dirname(spec[k]['out']).replace(os.sep, '/') + '/' for k in EXCLUDE if k in spec]

    def ignore(d, names):
        rel = os.path.relpath(d, os.path.join(ROOT, 'assets'))
        return [n for n in names if os.path.normpath(os.path.join(rel, n)) in drop]
    shutil.copytree(os.path.join(ROOT, 'assets'), os.path.join(out, 'assets'), ignore=ignore)

    # páginas do build (campo "out" de src/pages.json) menos as de EXCLUDE: index.html, blog/, servicos/, sobre/…
    pages = [p['out'].replace('/', os.sep) for p in kept.values()]
    for rel in pages:
        html = open(os.path.join(ROOT, rel), encoding='utf-8').read()
        for u in dead:  # link para página fora do Pages
            html = html.replace('href="%s"' % u, 'href="#"')
        html = ATTR_RE.sub(lambda m: rewrite_attr(m, base), html)
        html = URL_RE.sub(lambda m: 'url(' + m.group(1) + base + '/', html)  # url(/assets/…) em style=""
        dest = os.path.join(out, rel)
        os.makedirs(os.path.dirname(dest), exist_ok=True)
        with open(dest, 'w', encoding='utf-8', newline='\n') as fh:
            fh.write(html)
        print('%s/%s' % (a.out, rel.replace(os.sep, '/')))
    open(os.path.join(out, '.nojekyll'), 'w').close()


if __name__ == '__main__':
    main()
