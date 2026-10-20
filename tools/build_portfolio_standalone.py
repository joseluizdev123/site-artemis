"""Gera o Portfólio sozinho, para publicar num GitHub Pages separado do site (repositório portfolio-artemis).

Parte do portfolio/index.html já montado (rode antes python tools/build.py): copia só os arquivos que a página usa,
troca os caminhos /assets/… por relativos (funciona em qualquer subpasta) e aponta o logo e "Comece um projeto"
para o site oficial.

    python tools/build_portfolio_standalone.py --out <pasta> [--site https://studioartemis.co]

No repositório portfolio-artemis a saída vai para layout-antigo/ (o workflow de lá publica essa pasta na raiz do Pages).
"""
import argparse
import os
import re
import shutil

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
URL_ATTR = re.compile(r'(\s(?:href|src)=")(/[^"]*)(")')


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument('--out', required=True)
    ap.add_argument('--site', default='https://studioartemis.co')
    a = ap.parse_args()
    site = a.site.rstrip('/')

    with open(os.path.join(ROOT, 'portfolio', 'index.html'), encoding='utf-8') as f:
        html = f.read()

    if os.path.isdir(a.out):
        shutil.rmtree(a.out)
    os.makedirs(a.out)
    copied = []

    def fix(m):
        url = m.group(2)
        if url.startswith('/assets/'):
            path = url.split('?')[0]
            src = os.path.join(ROOT, path.lstrip('/'))
            dst = os.path.join(a.out, path.lstrip('/'))
            os.makedirs(os.path.dirname(dst), exist_ok=True)
            shutil.copy2(src, dst)
            copied.append(path)
            return m.group(1) + url.lstrip('/') + m.group(3)
        # páginas do site (logo → início, "Comece um projeto" → contato): endereço do site oficial
        return m.group(1) + site + url + m.group(3)

    html = URL_ATTR.sub(fix, html)
    if re.search(r'url\((["\']?)/(?!/)', html):
        raise SystemExit('url(/…) absoluto no HTML: trate antes de publicar')
    for css in [p for p in copied if p.endswith('.css')]:  # CSS do site não usa url() hoje; avisa se passar a usar
        with open(os.path.join(ROOT, css.lstrip('/')), encoding='utf-8') as f:
            if 'url(' in f.read():
                raise SystemExit('%s usa url(): copie os arquivos referenciados' % css)

    with open(os.path.join(a.out, 'index.html'), 'w', encoding='utf-8', newline='\n') as f:
        f.write(html)
    open(os.path.join(a.out, '.nojekyll'), 'w').close()
    print('%s: index.html + %d arquivos (links do site -> %s)' % (a.out, len(copied), site))


if __name__ == '__main__':
    main()
