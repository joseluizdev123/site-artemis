# Studio Artemis — site institucional

Site do Studio Artemis implementado pixel a pixel a partir do Figma (*Studio Artemis — Site Institucional*), com saída dupla:

- **HTML estático** — `index.html` (Home), `blog/` (Blog) e `blog/anatomia-do-wcontrol-como-se-valida-um-documento-de-mil-paginas/` (artigo), gerados a partir de `partials/` + `src/pages.json`.
- **Tema WordPress** — `theme/artemis/` (Customizer, CPTs, meta boxes e seeder com o conteúdo do Figma).

O modelo de dados, o mapa de seções ↔ nós do Figma e as validações estão em [`MODEL.md`](MODEL.md).

## Rodar localmente

```bash
python tools/build.py          # gera index.html e blog/ a partir dos partials
python tools/serve.py 5500     # http://localhost:5500
```

## Validação

- `python tools/diff.py SLUG [--mobile] [--page blog]` — compara a geometria de uma seção com o Figma (Chrome headless).
- `node tools/page_check.mjs URL [largura]` — confere a pilha de seções da página inteira.

## Publicação

O workflow `.github/workflows/pages.yml` publica no GitHub Pages a cada push na `main`
(`tools/build_pages.py` ajusta os caminhos para `/<repositório>/`).

O tema é sincronizado com `bash tools/sync-theme.sh` (copia `assets/` para `theme/artemis/assets/`).
