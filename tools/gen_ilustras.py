#!/usr/bin/env python
"""
Gera as ilustrações animadas da seção 03 (nossos sistemas) em CAMADAS, a partir do Figma (API REST com
token — tools/figma_api.py), para desktop (frames 8294:509…) e celular (frames 8635:2008…).

  python -I tools/gen_ilustras.py [chave …] [--check]

Para cada chave (validacao, quantificacao, codificacao, roteirizacao):
  - exporta as camadas (PNG 2× / SVG) em assets/images/03-nossos-sistemas/<chave>/
  - escreve partials/ilustra-<chave>.html (cena desktop + cena celular, camadas em % do frame)
  - escreve theme/artemis/template-parts/components/ilustra-<chave>.php (mesmo markup, caminhos via artemis_asset)
  - --check: compõe o quadro final em PIL e compara com o PNG atual do frame (ilustra-*.png / m-ilustra-*.png)

O estado final (sem animar) é o desenho do Figma. As peças animadas viram camadas próprias; onde a peça
não pode ser separada do fundo (segmentos de barra), entra uma "capa" da cor do fundo por cima, que some.
"""
import json
import os
import re
import sys
import urllib.request

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
sys.path.insert(0, os.path.join(ROOT, 'tools'))
import figma_api as F  # noqa: E402

IMG = os.path.join(ROOT, 'assets', 'images', '03-nossos-sistemas')
BG = '#111111'  # fundo do card "Código gerado" (capas dos segmentos)

FRAMES = {  # chave: (frame desktop, frame celular, png desktop, png celular)
    'validacao': ('8294:509', '8635:2008', 'ilustra-validacao-documental.png'),
    'quantificacao': ('8294:579', '8635:2078', 'ilustra-quantificacao-projeto.png'),
    'codificacao': ('8294:696', '8635:2195', 'ilustra-codificacao-produto.png'),
    'roteirizacao': ('8294:746', '8635:3593', 'ilustra-alocacao-roteirizacao.png'),
}

# Camadas por chave, em ordem de empilhamento (de trás para a frente). Cada camada: (caminho de índices
# de filhos a partir do frame, papel). Papéis: 'img' (fixa), 'laser', 'stem'/'pin' (+ índice do pino),
# 'covers' (todos os filhos do nó viram capas), 'route' (traçado da rota, SVG), 'cube'.
LAYERS = {
    'validacao': [((0, 0), 'img'), ((0, 1, 0), 'img'), ((0, 1, 1), 'laser'),
                  ((0, 2), 'img'), ((0, 3), 'img'), ((0, 4), 'img')],
    'quantificacao': [((0,), 'img'), ((1,), 'img'), ((2,), 'img'),
                      ((3, 0), 'stem:0'), ((3, 1), 'pin:0'),
                      ((4, 0), 'stem:1'), ((4, 1), 'pin:1'),
                      ((5, 0), 'pin:2'), ((5, 1), 'stem:2')],
    'codificacao': [((0,), 'img'), ((1,), 'img'), ((1, 3, 1, 1), 'covers'), ((1, 3, 2), 'covers-status')],
    'roteirizacao': [((0,), 'img'), ((1,), 'img'), ((1, 2, 1, 0), 'covers'), ((2,), 'img'), ((2, 0), 'route'), ((3,), 'cube')],
}


def tree(frame_id):
    return F.nodes([frame_id])['nodes'][frame_id]['document']


def at(node, path):
    for i in path:
        node = node['children'][i]
    return node


def bounds(node, origin):
    """Render bounds relativos ao frame (podem vir CORTADOS pelo recorte do frame) + bounding box."""
    b = node.get('absoluteRenderBounds') or node['absoluteBoundingBox']
    bb = node['absoluteBoundingBox']
    return (b['x'] - origin['x'], b['y'] - origin['y'], b['width'], b['height'],
            bb['x'] - origin['x'], bb['y'] - origin['y'], bb['width'], bb['height'])


def place(rb, img_w, img_h, W, H, eps=0.05):
    """Posição real da imagem exportada (que vem inteira, sem o recorte do frame). Em cada eixo usa a
    borda do render bounds que NÃO encostou no recorte; se as duas encostaram, centraliza no bounding box."""
    rx, ry, rw, rh, bx, by, bw, bh = rb

    def axis(r0, rsize, b0, bsize, isize, total):
        if r0 > eps:
            return r0
        if r0 + rsize < total - eps:
            return r0 + rsize - isize
        return b0 - (isize - bsize) / 2

    return axis(rx, rw, bx, bw, img_w, W), axis(ry, rh, by, bh, img_h, H), img_w, img_h


def svg_size(path):
    s = open(path, encoding='utf-8').read(600)
    w = re.search(r'width="([\d.]+)"', s)
    h = re.search(r'height="([\d.]+)"', s)
    return float(w.group(1)), float(h.group(1))


def export(ids, fmt):
    """{id: url} — o Figma limita o tamanho do lote; exporta em grupos."""
    out = {}
    for i in range(0, len(ids), 40):
        part = ids[i:i + 40]
        q = 'ids=%s&format=%s&scale=%s' % (','.join(part), fmt, 2 if fmt == 'png' else 1)
        res = F.get('https://api.figma.com/v1/images/%s?%s' % (F.FILE_KEY, q))
        out.update(res.get('images') or {})
    return out


def fname(scene, nid, ext):
    return '%s-%s.%s' % (scene, nid.replace(':', '-').replace(';', '_'), ext)


def recolor_svg(svg, color):
    svg = re.sub(r'fill="(?!none)[^"]*"', 'fill="%s"' % color, svg)
    # um pouco maior que a peça, para cobrir o antialias da borda
    return re.sub(r'<(path|rect)\b', r'<\1 stroke="%s" stroke-width="1.2"' % color, svg)


def build_scene(key, scene, frame_id):
    root = tree(frame_id)
    origin = root['absoluteBoundingBox']
    W, H = origin['width'], origin['height']
    layers, png_ids, svg_ids = [], [], []
    for path, role in LAYERS[key]:
        node = at(root, path)
        if role.startswith('covers'):
            kids = node['children']
            for i, k in enumerate(kids):
                layers.append({'id': k['id'], 'role': role, 'i': i, 'n': len(kids), 'b': bounds(k, origin)})
                svg_ids.append(k['id'])
        elif role == 'route':
            layers.append({'id': node['id'], 'role': role, 'b': bounds(node, origin)})
            svg_ids.append(node['id'])
        else:
            layers.append({'id': node['id'], 'role': role, 'b': bounds(node, origin)})
            png_ids.append(node['id'])
    os.makedirs(os.path.join(IMG, key), exist_ok=True)
    for nid, url in export(png_ids, 'png').items():
        urllib.request.urlretrieve(url, os.path.join(IMG, key, fname(scene, nid, 'png')))
    for nid, url in export(svg_ids, 'svg').items():
        raw = urllib.request.urlopen(url, timeout=120).read().decode('utf-8')
        lay = next(l for l in layers if l['id'] == nid)
        if lay['role'].startswith('covers'):
            raw = recolor_svg(raw, BG)
        with open(os.path.join(IMG, key, fname(scene, nid, 'svg')), 'w', encoding='utf-8') as f:
            f.write(raw)
    for l in layers:
        ext = 'svg' if l['role'].startswith('covers') or l['role'] == 'route' else 'png'
        l['file'] = fname(scene, l['id'], ext)
        path = os.path.join(IMG, key, l['file'])
        if ext == 'png':  # tamanho real do PNG exportado (2×) define a caixa
            from PIL import Image
            w2, h2 = Image.open(path).size
            iw, ih = w2 / 2, h2 / 2
        else:
            iw, ih = svg_size(path)
        l['b'] = place(l['b'], iw, ih, W, H)
    return {'W': W, 'H': H, 'frame': frame_id, 'layers': layers}


def pct(v, total):
    return '%.4f%%' % (v / total * 100)


def scene_html(key, scene, data, asset):
    W, H = data['W'], data['H']
    out = ['<div class="ilu__scene ilu__scene--%s" style="aspect-ratio:%g / %g">' % (scene, W, H)]
    for l in data['layers']:
        x, y, w, h = l['b']
        style = 'left:%s;top:%s;width:%s;height:%s' % (pct(x, W), pct(y, H), pct(w, W), pct(h, H))
        role = l['role']
        cls = 'ilu__l'
        extra = ''
        if role == 'laser':
            cls += ' ilu__laser'
        elif role.startswith('stem') or role.startswith('pin'):
            kind, idx = role.split(':')
            cls += ' ilu__%s' % kind
            extra = ';--i:%s' % idx
        elif role.startswith('covers'):
            cls += ' ilu__cover' + (' ilu__cover--status' if role == 'covers-status' else '')
            extra = ';--i:%d;--n:%d' % (l['i'], l['n'])
        elif role == 'route':
            cls += ' ilu__route'
        elif role == 'cube':
            cls += ' ilu__cube'
        out.append('  <img class="%s" src="%s" style="%s%s" alt="" loading="lazy" decoding="async"%s>' % (
            cls, asset('%s/%s' % (key, l['file'])), style, extra,
            ' data-x="%g" data-y="%g" data-w="%g" data-h="%g"' % (x, y, w, h) if role in ('route', 'cube') else ''))
    out.append('</div>')
    return out


def write(key, d, m):
    desk_id, mob_id, _ = FRAMES[key]
    root_html = '<div class="sistemas__media ilu ilu--%s" data-figma="%s" data-figma-m="%s" role="img" aria-hidden="true">' % (key, desk_id, mob_id)
    static = lambda p: '/assets/images/03-nossos-sistemas/' + p  # noqa: E731
    lines = ['<!-- Ilustração "%s" em camadas (Figma %s · celular %s) — gerada por tools/gen_ilustras.py; animação em assets/css/sections/03-nossos-sistemas--%s.css -->' % (key, desk_id, mob_id, key), root_html]
    lines += ['  ' + s for s in scene_html(key, 'd', d, static)]
    lines += ['  ' + s for s in scene_html(key, 'm', m, static)]
    lines.append('</div>')
    with open(os.path.join(ROOT, 'partials', 'ilustra-%s.html' % key), 'w', encoding='utf-8', newline='\n') as f:
        f.write('\n'.join(lines) + '\n')
    # WP: mesmo markup, sem data-figma, caminhos via artemis_asset()
    php_asset = lambda p: "<?php echo esc_url( artemis_asset( 'images/03-nossos-sistemas/%s' ) ); ?>" % p  # noqa: E731
    body = ['<div class="sistemas__media ilu ilu--%s" role="img" aria-hidden="true">' % key]
    body += ['\t' + s for s in scene_html(key, 'd', d, php_asset)]
    body += ['\t' + s for s in scene_html(key, 'm', m, php_asset)]
    body.append('</div>')
    php = ['<?php', '/**', ' * Ilustração animada "%s" — espelha partials/ilustra-%s.html (gerado por tools/gen_ilustras.py).' % (key, key), ' */', '', "defined( 'ABSPATH' ) || exit;", '?>'] + body
    with open(os.path.join(ROOT, 'theme', 'artemis', 'template-parts', 'components', 'ilustra-%s.php' % key), 'w', encoding='utf-8', newline='\n') as f:
        f.write('\n'.join(php) + '\n')


def check(key, scene, data, ref_png):
    """Compõe o quadro final (sem capas) a 2× e compara com o PNG atual do frame."""
    from PIL import Image, ImageChops, ImageStat
    W, H = data['W'], data['H']
    canvas = Image.new('RGBA', (round(W * 2), round(H * 2)), (9, 9, 11, 255))
    for l in data['layers']:
        if not l['file'].endswith('.png'):
            continue  # capas somem no quadro final; a rota em SVG também está no PNG da camada "Rota"
        im = Image.open(os.path.join(IMG, key, l['file'])).convert('RGBA')
        canvas.alpha_composite(im, (round(l['b'][0] * 2), round(l['b'][1] * 2)))
    # stroke interno 1px #111 do frame
    from PIL import ImageDraw
    ImageDraw.Draw(canvas).rectangle((0, 0, canvas.width - 1, canvas.height - 1), outline=(17, 17, 17, 255), width=2)
    ref = Image.open(ref_png).convert('RGBA').resize(canvas.size)
    diff = ImageStat.Stat(ImageChops.difference(canvas.convert('RGB'), ref.convert('RGB')).convert('L')).mean[0]
    os.makedirs(os.path.join(ROOT, 'tmp-figma'), exist_ok=True)
    canvas.save(os.path.join(ROOT, 'tmp-figma', 'check-%s-%s.png' % (key, scene)))
    print('  %s %-3s diferença média %.2f/255' % (key, scene, diff))
    return diff


if __name__ == '__main__':
    args = [a for a in sys.argv[1:] if not a.startswith('--')]
    keys = args or list(FRAMES)
    for key in keys:
        desk_id, mob_id, png = FRAMES[key]
        d = build_scene(key, 'd', desk_id)
        m = build_scene(key, 'm', mob_id)
        write(key, d, m)
        print('%s: %d camadas desktop, %d celular' % (key, len(d['layers']), len(m['layers'])))
        if '--check' in sys.argv:
            check(key, 'd', d, os.path.join(IMG, png))
            check(key, 'm', m, os.path.join(IMG, 'm-' + png))
