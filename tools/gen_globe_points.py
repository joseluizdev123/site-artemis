#!/usr/bin/env python
"""
Gera assets/data/globe-land.json — grade de pontos sobre os continentes para o globo
girando da seção 04 (depoimento).

  python -I tools/gen_globe_points.py <land-110m.json> [--step 1.44] [--preview mask.png]

Entrada: TopoJSON "land" do pacote world-atlas@2 (Natural Earth 110m, domínio público):
  https://cdn.jsdelivr.net/npm/world-atlas@2.0.2/land-110m.json
Saída: {"step": graus, "rows": [[lat, n, [ini, fim, ini, fim, ...]], ...]}
  - cada linha é um paralelo; n = quantos pontos cabem nele (espaçamento ~igual na esfera)
  - ini/fim = faixas de índices de longitude que caem sobre terra (lon = -180 + (i + .5) * 360 / n)
O passo de 1,44° reproduz o espaçamento do globo do Figma (8,5px num raio de 338px).
"""
import argparse
import json
import math
import os

from PIL import Image, ImageDraw

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
W, H = 4096, 2048  # máscara equiretangular


def decode_arcs(topo):
    sx, sy = topo['transform']['scale']
    tx, ty = topo['transform']['translate']
    arcs = []
    for arc in topo['arcs']:
        x = y = 0
        pts = []
        for dx, dy in arc:
            x += dx
            y += dy
            pts.append((x * sx + tx, y * sy + ty))
        arcs.append(pts)
    return arcs


def ring(arcs, idx):
    pts = []
    for i in idx:
        a = arcs[i] if i >= 0 else list(reversed(arcs[~i]))
        pts.extend(a if not pts else a[1:])
    return pts


def to_px(lon, lat):
    return ((lon + 180) / 360 * W, (90 - lat) / 180 * H)


def unwrap(pts):
    """Desdobra a longitude em anéis que cruzam a linha de data (±180) — evita faixas
    horizontais na máscara. Anel que dá a volta no globo (Antártida) é fechado no polo."""
    out = [pts[0]]
    for lon, lat in pts[1:]:
        prev = out[-1][0]
        while lon - prev > 180:
            lon -= 360
        while lon - prev < -180:
            lon += 360
        out.append((lon, lat))
    span = out[-1][0] - out[0][0]
    if abs(span) > 180:  # contorna o polo: fecha pela borda (-90 no sul, +90 no norte)
        pole = -90 if min(p[1] for p in out) < 0 else 90
        out += [(out[-1][0], pole), (out[0][0], pole)]
    return out


def build_mask(topo):
    arcs = decode_arcs(topo)
    img = Image.new('L', (W, H), 0)
    draw = ImageDraw.Draw(img)
    for geom in topo['objects']['land']['geometries']:
        polys = geom['arcs'] if geom['type'] == 'MultiPolygon' else [geom['arcs']]
        for poly in polys:
            for k, r in enumerate(poly):
                pts = unwrap(ring(arcs, r))
                if len(pts) < 3:
                    continue
                for shift in (-360, 0, 360):  # desenha as cópias vizinhas para cobrir o recorte em ±180
                    draw.polygon([to_px(lon + shift, lat) for lon, lat in pts], fill=0 if k else 255)  # 1º anel = continente, demais = buracos
    return img


def sample(mask, step):
    px = mask.load()
    rows = []
    nlat = int(180 / step)
    for j in range(nlat):
        lat = 90 - (j + 0.5) * step
        n = max(1, int(round(360 * math.cos(math.radians(lat)) / step)))
        runs, start = [], None
        for i in range(n):
            lon = -180 + (i + 0.5) * 360 / n
            x, y = to_px(lon, lat)
            land = px[min(W - 1, int(x)), min(H - 1, int(y))] > 127
            if land and start is None:
                start = i
            if not land and start is not None:
                runs += [start, i - 1]
                start = None
        if start is not None:
            runs += [start, n - 1]
        if runs:
            rows.append([round(lat, 3), n, runs])
    return rows


if __name__ == '__main__':
    ap = argparse.ArgumentParser()
    ap.add_argument('topojson')
    ap.add_argument('--step', type=float, default=1.44)
    ap.add_argument('--preview')
    a = ap.parse_args()
    with open(a.topojson, encoding='utf-8') as f:
        topo = json.load(f)
    mask = build_mask(topo)
    if a.preview:
        mask.resize((1024, 512)).save(a.preview)
    rows = sample(mask, a.step)
    out = os.path.join(ROOT, 'assets', 'data', 'globe-land.json')
    os.makedirs(os.path.dirname(out), exist_ok=True)
    with open(out, 'w', encoding='utf-8') as f:
        json.dump({'step': a.step, 'source': 'Natural Earth 110m via world-atlas@2.0.2 (domínio público)', 'rows': rows}, f, separators=(',', ':'))
    total = sum((r[2][k + 1] - r[2][k] + 1) for r in rows for k in range(0, len(r[2]), 2))
    print('%s  %d linhas, %d pontos, %d bytes' % (out, len(rows), total, os.path.getsize(out)))
