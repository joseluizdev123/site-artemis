#!/usr/bin/env python
"""
Monta o HTML estático a partir das partes por seção — várias páginas (src/pages.json).

  partials/header.html           → <header> (compartilhado; blocos <!--@if page=blog--> … <!--@endif-->)
  partials/<slug>.html           → seções, na ordem definida por página em src/pages.json
                                   ("auto" = partials/NN-*.html, a Home)
  partials/footer.html           → <footer> (compartilhado)
  assets/css/sections/<slug>.css, assets/js/sections/<slug>.js → carregados se existirem

Seção repetida com variáveis: {"slug": "blog-03-categoria", "vars": {"CAT_TITLE": "…"}} —
o partial usa {{CAT_TITLE}}. Condicionais por página em qualquer partial:
  <!--@if page=blog--> … <!--@endif-->   <!--@if page!=blog--> … <!--@endif-->

Uso:
  python tools/build.py                          # gera todas as páginas (produção, sem data-figma)
  python tools/build.py --preview SLUG [--page P] # preview/SLUG.html só com a seção (mantém data-figma + medidor)
  python tools/build.py --preview all            # previews de todas as seções

SLUG = nome do partial sem .html (ex.: 02-o-que-fazemos, blog-01-hero, footer).
--page = contexto da página para os condicionais/variáveis (padrão: a 1ª página que usa o SLUG; header/footer → home).
Escrita atômica (tmp + replace) para permitir builds concorrentes de previews diferentes.
"""
import argparse
import glob
import json
import os
import re
import sys

try:
    sys.stdout.reconfigure(encoding='utf-8')
except Exception:
    pass

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
TEMPLATE = os.path.join(ROOT, 'src', 'index.template.html')
PAGES_FILE = os.path.join(ROOT, 'src', 'pages.json')

MEASURE_SCRIPT = """  <script>
  /* Medidor (só em preview): grava rects + estilos de todo [data-figma] em #__measure */
  (function () {
    function run() {
      var qa = /[?&]attr=([a-z0-9_-]+)/i.exec(location.search); // ?attr=data-figma-blog → ids de outra página (componentes compartilhados)
      var attr = qa ? qa[1] : /[?&]m=1/.test(location.search) ? 'data-figma-m' : 'data-figma'; // ?m=1 → nós do frame Mobile
      var root = document.querySelector('[data-figma-root]') || document.body;
      var rb = root.getBoundingClientRect();
      var out = [];
      document.querySelectorAll('[' + attr + ']').forEach(function (el) {
        var b = el.getBoundingClientRect(), c = getComputedStyle(el);
        out.push({
          id: el.getAttribute(attr), tag: el.tagName.toLowerCase(), cls: el.className && el.className.baseVal === undefined ? el.className : '',
          x: +(b.left - rb.left).toFixed(2), y: +(b.top - rb.top).toFixed(2), w: +b.width.toFixed(2), h: +b.height.toFixed(2),
          fontFamily: c.fontFamily.split(',')[0].replace(/"/g, ''), fontSize: c.fontSize, lineHeight: c.lineHeight, fontWeight: c.fontWeight,
          fontStyle: c.fontStyle, letterSpacing: c.letterSpacing, color: c.color, bg: c.backgroundColor,
          padding: c.padding, gap: c.gap, radius: c.borderRadius, border: c.borderTopWidth + ' ' + c.borderTopStyle + ' ' + c.borderTopColor,
          shadow: c.boxShadow === 'none' ? '' : c.boxShadow, opacity: c.opacity
        });
      });
      var imgs = Array.prototype.map.call(document.images, function (i) { return { src: i.getAttribute('src'), ok: i.complete && i.naturalWidth > 0 }; });
      var pre = document.createElement('script');
      pre.type = 'application/json'; pre.id = '__measure';
      pre.textContent = JSON.stringify({ root: root.getAttribute(attr), viewport: innerWidth, docW: document.documentElement.scrollWidth, docH: document.documentElement.scrollHeight, rootH: rb.height, fonts: Array.prototype.map.call(document.fonts, function (f) { return f.family + ' ' + f.weight + ' ' + f.style + ' ' + f.status; }), images: imgs, nodes: out });
      document.body.appendChild(pre);
    }
    var go = function () { (document.fonts ? document.fonts.ready : Promise.resolve()).then(function () { setTimeout(run, 400); }); };
    if (document.readyState === 'complete') go(); else addEventListener('load', go);
  })();
  </script>"""


def read(path):
    with open(path, encoding='utf-8') as f:
        return f.read()


def write_atomic(path, text):
    os.makedirs(os.path.dirname(path), exist_ok=True)
    tmp = '%s.%d.tmp' % (path, os.getpid())
    with open(tmp, 'w', encoding='utf-8', newline='\n') as f:
        f.write(text)
    os.replace(tmp, path)


def indent(text, n):
    pad = ' ' * n
    return '\n'.join((pad + l) if l.strip() else l for l in text.rstrip('\n').split('\n'))


def section_slugs():
    files = sorted(glob.glob(os.path.join(ROOT, 'partials', '[0-9][0-9]-*.html')))
    return [os.path.splitext(os.path.basename(f))[0] for f in files]


def partial(slug):
    p = os.path.join(ROOT, 'partials', slug + '.html')
    return read(p) if os.path.exists(p) else ''


def pages():
    return json.loads(read(PAGES_FILE))


def page_sections(page):
    """Lista de (slug, vars) da página, na ordem."""
    spec = pages()[page]['sections']
    if spec == 'auto':
        return [(s, {}) for s in section_slugs()]
    return [(e, {}) if isinstance(e, str) else (e['slug'], e.get('vars', {})) for e in spec]


def page_of(slug):
    """Primeira página que usa a seção e as variáveis dessa ocorrência."""
    for pid in pages():
        for s, v in page_sections(pid):
            if s == slug:
                return pid, v
    return 'home', {}


IF_RE = re.compile(r'<!--@if page(!?=)([\w|-]+)-->(.*?)<!--@endif-->', re.S)  # page=blog|post = qualquer uma
INCLUDE_RE = re.compile(r'([ \t]*)<!--@include ([\w-]+)-->')


def include(m):
    """<!--@include nome--> → conteúdo de partials/nome.html, com a mesma indentação."""
    body = partial(m.group(2))
    if not body:
        sys.exit('include não encontrado: partials/%s.html' % m.group(2))
    return indent(body, len(m.group(1).expandtabs()))


def apply_context(html, page, variables):
    """Inclusões <!--@include nome-->, condicionais <!--@if page=X--> e variáveis {{NOME}} do partial."""
    for _ in range(3):  # inclusões podem ter inclusões
        html = INCLUDE_RE.sub(include, html)
    html = IF_RE.sub(lambda m: m.group(3) if (m.group(1) == '=') == (page in m.group(2).split('|')) else '', html)
    if '<!--@if' in html or '<!--@endif' in html:  # condicional aninhado ou sem par: o regex não-guloso quebraria o HTML
        sys.exit('condicional <!--@if--> aninhado ou sem <!--@endif--> (página %s)' % page)
    for k, v in variables.items():
        html = html.replace('{{%s}}' % k, v)
    return html


def ver(rel):
    """?v=<mtime> para o navegador não usar CSS/JS antigo do cache após cada build."""
    return '%s?v=%d' % (rel, int(os.path.getmtime(os.path.join(ROOT, rel.lstrip('/')))))


def section_files(kind, slug):
    """assets/<kind>/sections/<slug>.<kind> e os auxiliares <slug>--*.<kind> (ex.: uma ilustração animada)."""
    base = os.path.join(ROOT, 'assets', kind, 'sections')
    main = [slug] if os.path.exists(os.path.join(base, '%s.%s' % (slug, kind))) else []
    extra = sorted(os.path.splitext(os.path.basename(f))[0] for f in glob.glob(os.path.join(base, '%s--*.%s' % (slug, kind))))
    return main + extra


def asset_tags(slugs):
    css, js = [], []
    for slug in slugs:
        for name in section_files('css', slug):
            css.append('  <link rel="stylesheet" href="%s">' % ver('/assets/css/sections/%s.css' % name))
        for name in section_files('js', slug):
            js.append('  <script src="%s" defer></script>' % ver('/assets/js/sections/%s.js' % name))
    return '\n'.join(css), '\n'.join(js)


def strip_figma(html):
    html = re.sub(r'\s+data-figma-root(?=[\s>])', '', html)
    return re.sub(r'\s+data-figma(?:-[\w-]+)?="[^"]*"', '', html)


def render(header, main, footer, slugs, preview, page='home'):
    css, js = asset_tags(slugs)
    meta = pages()[page]
    out = read(TEMPLATE)
    out = out.replace('{{MAIN_CSS}}', ver('/assets/css/main.css')).replace('{{MAIN_JS}}', ver('/assets/js/main.js'))
    out = out.replace('{{MOTION_CSS}}', ver('/assets/css/motion.css')).replace('{{MOTION_JS}}', ver('/assets/js/motion.js'))
    out = out.replace('{{TITLE}}', meta['title'] + (' · preview' if preview else ''))
    out = out.replace('{{DESCRIPTION}}', meta['description'])
    out = out.replace('{{CSS}}', css)
    out = out.replace('{{JS}}', js)
    out = out.replace('{{BODY_ATTRS}}', ' class="page-%s%s"' % (page, ' is-preview' if preview else ''))
    out = out.replace('{{HEADER}}', indent(header, 2) if header else '')
    out = out.replace('{{MAIN}}', indent(main, 4) if main else '')
    out = out.replace('{{FOOTER}}', indent(footer, 2) if footer else '')
    out = out.replace('{{MEASURE}}', MEASURE_SCRIPT if preview else '')
    out = re.sub(r'\n{3,}', '\n\n', out)
    return out if preview else strip_figma(out)


def build_page(page):
    secs = page_sections(page)
    slugs = []
    for s, _ in secs:
        if s not in slugs:
            slugs.append(s)
    for s in pages()[page].get('assets', []):  # CSS/JS de componentes usados por outras seções (ex.: .post-card)
        if s not in slugs:
            slugs.insert(0, s)
    main = '\n\n'.join(apply_context(partial(s), page, v).rstrip('\n') for s, v in secs if partial(s))
    header = apply_context(partial('header'), page, {})
    footer = apply_context(partial('footer'), page, {})
    html = render(header, main, footer, slugs + ['footer'], preview=False, page=page)
    out = pages()[page]['out']
    write_atomic(os.path.join(ROOT, out), html)
    print('%-18s (%d seções: %s)' % (out, len(secs), ', '.join(s for s, _ in secs)))


def build_index():
    for page in pages():
        build_page(page)


def build_preview(slug, page=None):
    body = partial(slug)
    if not body:
        sys.exit('partial não encontrado: partials/%s.html' % slug)
    pid, variables = page_of(slug)
    page = page or pid
    body = apply_context(body, page, variables)
    if slug == 'footer':
        html = render('', '', body, ['footer'], preview=True, page=page)
    elif slug == 'header':
        html = render(body, '', '', [], preview=True, page=page)
    else:
        html = render('', body, '', pages()[page].get('assets', []) + [slug], preview=True, page=page)
    path = os.path.join(ROOT, 'preview', slug + '.html')
    write_atomic(path, html)
    print('preview/%s.html' % slug)


if __name__ == '__main__':
    ap = argparse.ArgumentParser()
    ap.add_argument('--preview', help='slug do partial, ou "all"')
    ap.add_argument('--page', help='contexto de página do preview (home, blog, …)')
    a = ap.parse_args()
    if a.preview == 'all':
        seen = []
        for pid in pages():
            for s, _ in page_sections(pid):
                if s not in seen:
                    seen.append(s)
        for s in seen + ['header', 'footer']:
            if partial(s):
                build_preview(s)
    elif a.preview:
        build_preview(a.preview, a.page)
    else:
        build_index()
