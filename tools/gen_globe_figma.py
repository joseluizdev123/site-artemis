#!/usr/bin/env python
"""
Gera assets/data/globe-points.json — nuvem de pontos do globo girando da seção 04.

  python -I tools/gen_globe_figma.py

O globo usa o enquadramento do Figma (vetor 8229:2132): mesma silhueta (centro e raio) e a orientação
que mais aproxima os continentes do desenho (Atlântico no centro, Américas à esquerda, África à direita).
Os pontos vêm só do mapa real (assets/data/globe-land.json — tools/gen_globe_points.py, Natural Earth
110m): uma grade uniforme em que cada ponto aparece uma vez, sem continentes sobrepostos ao girar.

Por que não usar os pontos do próprio vetor do Figma: o desenho é estilizado (a geografia não bate com
a real em nenhuma rotação e a borda é comprimida); misturado ao mapa real ele sobrepõe continentes
quando o globo gira. FIGMA_CAP > 0 reativa essa mistura (calota frontal com os pontos do Figma).

Parâmetros (frame desktop 935×443, coordenadas do Figma):
  círculo da silhueta: ajuste de círculo nos pontos da borda onde há terra encostando no limite
  orientação: maior sobreposição (IoU) entre os pontos do Figma e o mapa real no interior do disco
Saída: {"view": {...}, "pts": "<base64 de Int16 [lat×100, lon×100, …]>"}
"""
import base64
import json
import math
import os
import re
import struct

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SVG = os.path.join(ROOT, 'assets', 'images', '04-depoimento', 'globe.svg')
LAND = os.path.join(ROOT, 'assets', 'data', 'globe-land.json')
OUT = os.path.join(ROOT, 'assets', 'data', 'globe-points.json')

VIEW = {'CX': 594.76, 'CY': 365.07, 'R': 351.32,     # silhueta (frame desktop 8229:2131)
        'LON': -3.75, 'LAT': -3.59, 'ROLL': -19.84}   # orientação do 1º quadro
FIGMA_CAP = 0.0  # 0 = só mapa real (sem sobreposição); >0 = usa os pontos do Figma até essa fração do raio
RHO_MAX = FIGMA_CAP

NUM = re.compile(r'[-+]?(?:\d*\.\d+|\d+\.?)(?:[eE][-+]?\d+)?')


def figma_dots():
    """Centro de cada ponto do vetor do Figma (bbox de cada <path>)."""
    s = open(SVG, encoding='utf-8').read()
    out = []
    for d in re.findall(r'<path d="([^"]*)" fill="url\(#paint', s):
        xs, ys, cx, cy = [], [], 0.0, 0.0
        for cmd, args in re.findall(r'([MLCSQTHVZ])([^MLCSQTHVZ]*)', d):
            a = [float(v) for v in NUM.findall(args)]
            if cmd in 'MLCSQT':
                xs += a[0::2]
                ys += a[1::2]
                if len(a) >= 2:
                    cx, cy = a[-2], a[-1]
            elif cmd == 'H':
                xs += a
                ys += [cy] * len(a)
                cx = a[-1] if a else cx
            elif cmd == 'V':
                ys += a
                xs += [cx] * len(a)
                cy = a[-1] if a else cy
        if xs:
            out.append(((min(xs) + max(xs)) / 2, (min(ys) + max(ys)) / 2))
    return out


def inverse(X, Y):
    """Ortográfica inversa: ponto da tela → (lat, lon) na orientação VIEW."""
    v = VIEW
    sp, cp = math.sin(math.radians(v['LAT'])), math.cos(math.radians(v['LAT']))
    sr, cr = math.sin(math.radians(-v['ROLL'])), math.cos(math.radians(-v['ROLL']))
    x, y = (X - v['CX']) / v['R'], -(Y - v['CY']) / v['R']
    x, y = x * cr - y * sr, x * sr + y * cr
    rho = math.hypot(x, y)
    if rho < 1e-9:
        return v['LAT'], v['LON']
    c = math.asin(min(1.0, rho))
    lat = math.degrees(math.asin(math.cos(c) * sp + y * math.sin(c) * cp / rho))
    lon = v['LON'] + math.degrees(math.atan2(x * math.sin(c), rho * math.cos(c) * cp - y * math.sin(c) * sp))
    return lat, (lon + 180) % 360 - 180


def angular_dist(lat1, lon1, lat2, lon2):
    a, b = math.radians(lat1), math.radians(lat2)
    return math.degrees(math.acos(max(-1, min(1, math.sin(a) * math.sin(b) + math.cos(a) * math.cos(b) * math.cos(math.radians(lon2 - lon1))))))


def main():
    cap_deg = math.degrees(math.asin(RHO_MAX)) if RHO_MAX > 0 else -1.0
    pts = []
    n_fig = 0
    if RHO_MAX > 0:
        for X, Y in figma_dots():
            if math.hypot(X - VIEW['CX'], Y - VIEW['CY']) / VIEW['R'] < RHO_MAX:
                pts.append(inverse(X, Y))
                n_fig += 1
    land = json.load(open(LAND, encoding='utf-8'))
    n_real = 0
    for lat, n, runs in land['rows']:
        for k in range(0, len(runs), 2):
            for i in range(runs[k], runs[k + 1] + 1):
                lon = -180 + (i + 0.5) * 360 / n
                if angular_dist(lat, lon, VIEW['LAT'], VIEW['LON']) > cap_deg:
                    pts.append((lat, lon))
                    n_real += 1
    data = {'view': VIEW, 'source': 'Natural Earth 110m (domínio público)'}
    if n_fig:
        # Modo híbrido: pontos explícitos (lat/lon) em base64 — sem oceano.
        data['source'] = 'calota frontal = pontos do vetor Figma 8229:2132; resto = ' + data['source']
        data['pts'] = base64.b64encode(b''.join(struct.pack('<hh', int(round(la * 100)), int(round(lo * 100))) for la, lo in pts)).decode('ascii')
    else:
        # Modo padrão: grade compacta (faixas de terra por paralelo, como globe-land.json). O JS monta a
        # grade inteira — terra = faixas, oceano = o resto, desenhado bem fraco para o globo nunca ficar vazio.
        data['step'] = land['step']
        data['rows'] = land['rows']
    with open(OUT, 'w', encoding='utf-8') as f:
        json.dump(data, f, separators=(',', ':'), ensure_ascii=False)
    print('%s: %d pontos de terra (%d do Figma, %d reais)%s, %d bytes' % (
        os.path.relpath(OUT, ROOT), len(pts), n_fig, n_real, '' if n_fig else ' + oceano montado no JS', os.path.getsize(OUT)))


if __name__ == '__main__':
    main()
