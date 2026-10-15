# Studio Artemis — Site Institucional · Modelo de dados WordPress

Figma: `i23qiP70wQhTOjyhYLT9LM` · canvas `8159:163` ("◆ ・ Design - Website Nova Copy")
Tema: `theme/artemis/` · prefixo de CPT/meta: `art_`

## Decisões (defaults da skill — revisar)

| Decisão                | Escolha                                                    |
|------------------------|------------------------------------------------------------|
| Campos                 | Meta box nativo (sem ACF)                                  |
| Operador do admin      | Marketing / cliente leigo → meta boxes com labels em PT-BR |
| Imagens                | Assets do Figma viram seed, importados pro Mídia pelo seeder |
| Updates                | Git Updater (`GitHub Theme URI` no `style.css`) — repo a definir |

## Modelo

| Conteúdo                 | Tipo          | Slug WP            | Campos principais                                              |
|--------------------------|---------------|--------------------|----------------------------------------------------------------|
| Clientes (logos)         | CPT           | `art_cliente`      | nome (título), logo (featured), ordem, `_art_cliente_url`, `_art_cliente_w/_h`, `_art_cliente_offset_x`, `_art_cliente_mask` (pinta de branco) |
| Equipe                   | CPT           | `art_membro`       | nome, foto (featured), bio, ordem, `_art_membro_cargo`, `_art_membro_fundador` (aparece na Home). *Registrado em `inc/section-02-o-que-fazemos.php` — mover para `inc/cpt-membro.php` quando a página Sobre for feita* |
| Sistemas (o que decidem) | CPT           | `art_sistema`      | título, descrição do card (excerpt), ilustração (featured, 640² @2x), ordem |
| Depoimentos              | CPT           | `art_depoimento`   | nome (título), citação (content, sem aspas), foto (featured), `_art_depoimento_cargo`, `_art_depoimento_empresa`; menu_order 0 = o exibido |
| Estudos de impacto       | CPT           | `art_estudo`       | manchete (título), textos (content), screenshot (featured), `_art_estudo_numero`, `_art_estudo_cliente`, `_art_estudo_sistema`, `_art_estudo_categoria`, `_art_estudo_status`, fundo |
| Blog                     | Post nativo   | `post`             | título, conteúdo, featured, categoria                          |
| Hero                     | Customizer    | `art_hero_*`       | eyebrow, título com {destaque}, subtítulo, CTAs, legenda dos clientes |
| O que fazemos            | Customizer    | `art_oqf_*`        | eyebrow, título com {destaque}, corpo (parágrafos + **negrito**), legenda dos fundadores |
| Nossos sistemas          | Customizer    | `art_sistemas_*`   | título, texto, legenda                                         |
| Depoimento               | Customizer    | `art_depoimento_eyebrow` | frase à esquerda                                         |
| Como funciona            | Customizer    | `art_cf_*`, `art_cf_step_{1..4}_title/_text` | eyebrow, título, texto; 4 passos (número automático) |
| Diagrama                 | Customizer    | `art_diag_*`       | fases, cartões, rótulos, título/texto "Não tocamos no seu sistema" |
| Estudos (cabeçalho)      | Customizer    | `art_estudos_*`    | eyebrow, título com {destaque}                                 |
| Números (stats)          | Customizer    | `art_stat_{1..4}_*`| prefixo, valor, sufixo, legenda                                |
| Como trabalhamos         | Customizer    | `art_ct_*`, `art_ct_rule_{1..5}_title/_text` | eyebrow, título, 5 regras                         |
| Call to action           | Customizer    | `art_cta_*`        | barra de status (itens separados por \|), nota em itálico, título, texto, botão |
| Rodapé                   | Customizer    | `art_footer_*`     | frase da newsletter com {destaque}, action do formulário (vazio = desativado), copyright, links legais |
| Contato global           | Customizer    | `art_contact_*`    | e-mail, WhatsApp, endereço (link Maps), link "Agendar diagnóstico" |
| Redes sociais            | Customizer    | `art_social_*`     | instagram, youtube, linkedin, behance, dribbble, x (vazio esconde o ícone) |
| Menu principal / rodapé  | Menu nativo   | `primary` / `footer` | com fallback estático                                        |
| Sobre                    | Page única    | `/sobre`           | post_content rico                                              |
| Serviços                 | Page única    | `/servicos`        | post_content rico                                              |
| Contato                  | Page única    | `/contato`         | post_content + formulário                                      |

### Conteúdo a confirmar com o cliente

- E-mail: Figma mostra `contato@studioartemis.co`, o default do tema é `contato@studioartemis.com.br`.
- Regra 4 ("A ferramenta se adapta ao seu processo"): texto truncado no Figma — "…para caber no nosso".
- Estudo Gascat: rodapé "Validação de documentos · Em produção" igual ao estudo 1 (provável copy-paste).
- WhatsApp `+55 (48) 0000-0000`, links de redes sociais e de "Outras linhas" (Artemis SEO, MktOps) são placeholders.
- Cargos/bios dos fundadores (Willy, Enzo, Julio, Helio): seed usa "Sócio-fundador".

## Páginas no Figma (frames desktop 1440)

| Página (inferida)   | Desktop    | Mobile     |
|---------------------|------------|------------|
| Home                | `8160:163` | `8635:1583`|
| (demais — mapear quando forem implementadas) | `8294:1703`, `8331:321`, `8340:1700`, `8348:3076`, `8348:3835`, `8348:4008`, `8348:4181` | |

## Home — mapa de seções (Fase 0)

| # | Seção (Figma)        | Node ID     | Altura | Notas                                   | Status |
|---|----------------------|-------------|--------|-----------------------------------------|--------|
| 0 | Header               | `8165:2338` | 80     | símbolo; versão scroll `8165:2339` (60) | ✅      |
| 1 | hero                 | `8160:165`  | 1072   | padrão de traços animado + logos        | ✅      |
| 2 | o que fazemos        | `8183:302`  | 639    | 2-col; fundadores → CPT `art_membro`, nome no hover | ✅ |
| 3 | nossos sistemas      | `8294:500`  | 1033   | 4 cards → CPT `art_sistema`; ilustrações em camadas animadas (`_art_sistema_ilustra`, `tools/gen_ilustras.py`): laser verde, pinos azuis sobem, segmentos rosa carregam, cubo laranja segue a rota; tocam ao entrar rolando para baixo e no hover (card avança) | ✅      |
| 4 | testmonial           | `8186:1160` | 443    | → CPT `art_depoimento`; globo em canvas girando no enquadramento do Figma (esfera 594,8/365,1 R 351,3; Atlântico no centro; #CECECE com degradê horizontal 0→50%). Terra = grade do mapa real sem sobreposição; oceano = mesma grade a 16% (globo nunca vazio); velocidade 1 volta/90 s sobre continentes, até 10× sobre oceano aberto (o Pacífico passa em ~5 s). Dados: `assets/data/globe-points.json` (5 KB, `tools/gen_globe_figma.py`) | ✅ |
| 5 | como funciona        | `8307:3380` | 814    | 4 passos; coluna esquerda sticky        | ✅      |
| 6 | Diagram              | `8653:2286` | 843    | diagrama POC → MVP → Escala em HTML/CSS | ✅      |
| 7 | estudos de impacto   | `8195:1063` | 1055   | carrossel 4 estudos → CPT `art_estudo`  | ✅      |
| 8 | stats                | `8486:1798` | 382    | → Customizer `art_stat_*`               | ✅      |
| 9 | como trabalhamos     | `8490:2191` | 865    | 5 regras + ilustração isométrica que monta com o scroll (tela sobe 56px, tracejado se desenha, selo aparece) | ✅ |
| 10| call to action       | `8160:1628` | 628    | barra de status (estática) + CTA + traços | ✅    |
| 11| Footer container     | `8165:2501` | 740    | newsletter, links, contato, redes       | ✅      |

Página inteira (1440, sem scrollbar): altura 8594 = Figma; empilhamento Δ ≤ 0,8px. Diferenças restantes = largura de texto por métrica de fonte web (≤ 1,9px) e glifos ~1px mais altos (métrica vertical do Google Fonts).

## Home mobile — mapa (frame `8635:1583`, 375 × 13162)

Layout do Figma Mobile vale em `@media (max-width: 767px)`; tokens: `--section-py: 60px`, `--header-h: 60px`, `--section-px` = 16.
Gate: `python tools/diff.py SLUG --mobile --shot` (375 via DevTools, nós `data-figma-m`). Complementos de geometria em `tools/figma-meta.d/*.xml`.

| Seção              | Desktop     | Mobile      | Altura mobile | Notas mobile | Status |
|--------------------|-------------|-------------|---------------|--------------|--------|
| Header             | `8165:2338` | `8635:3579` | 60 | botão "Menu" + gaveta `8662:2946` (325×812): Esc, clique fora, foco preso, scroll travado, e-mail copia ao tocar | ✅ |
| hero               | `8160:165`  | `8635:1586` | 1154 | logos 2×3 com tamanho mobile por cliente (`_art_cliente_mw/_mh`); traços acendem só com mouse | ✅ |
| o que fazemos      | `8183:302`  | `8635:1990` | 1096 | nome do fundador aparece ao tocar no avatar | ✅ |
| nossos sistemas    | `8294:500`  | `8635:1999` | 2649 | ilustração mobile própria via `<picture>` (`_art_sistema_img_m`) | ✅ |
| testmonial         | `8186:1160` | `8635:2302` | 501 | globo com enquadramento próprio no mobile (`FRAMES.mobile` em `04-depoimento.js`) | ✅ |
| como funciona      | `8307:3380` | `8635:2313` | 1318 | sem sticky abaixo de 1024 | ✅ |
| Diagram            | `8653:2286` | `8653:2599` | 869 | mesmo desenho com rolagem horizontal + barra de progresso `8653:2896`; rótulo "Você testa" (`art_diag_lab_label`) | ✅ |
| estudos de impacto | `8195:1063` | `8635:2366` | 1321,47 | carrossel com setas, teclado e swipe; estudos 2–4 extrapolados do layout do estudo 1 | ✅ |
| stats              | `8486:1798` | `8635:2371` | 932 | 4 números empilhados | ✅ |
| como trabalhamos   | `8490:2191` | `8635:2435` | 1227 | animação de scroll da ilustração mantida; zoom 0,9/0,8 abaixo de 370/341px | ✅ |
| call to action     | `8160:1628` | `8635:2625` | 576 | barra de status cortada na borda, como no Figma | ✅ |
| Footer             | `8165:2501` | `8641:1595` | 1459 | em 320 o 6º ícone social desce de linha | ✅ |

Página inteira (verificada com `node tools/page_check.mjs URL LARGURA`): 375 → 375×13162, todas as seções com Δ 0,00 contra o frame Mobile; 1440 → 1440×8594, Δ ≤ 0,19. Sem rolagem horizontal em 320/375/390/414/768/1024/1280/1440.

### Mobile — a confirmar com cliente/designer
- Placeholder do e-mail no Figma mobile com espaço duplo ("Seu endereço  de e-mail") — o código usa espaço simples.
- "Como funciona": só o marcador do passo 1 (`8635:2324`) está ancorado embaixo no Figma mobile; implementado literalmente.
- Estudos 2–4 não têm layout mobile no Figma — seguem o do estudo 1.
- O fundo escurecido atrás da gaveta do menu não está no Figma (leitura da anotação "em overlay").

## Blog — mapa (desktop `8294:1703` 1440×6906 · mobile `8673:3845` 375×8037,41)

Estático: `blog/index.html` (`src/pages.json` → página `blog`). WP: `home.php` (página de posts, "Blog" em `page_for_posts`).

| # | Seção | Desktop | Mobile | Partial / template | Status |
|---|-------|---------|--------|--------------------|--------|
| 0 | Header (variante Blog: selo "BLOG", link "Início") | `8340:2124` | `8673:4443` | `partials/header.html` com `<!--@if page=blog-->` · `header.php` (`artemis_is_blog()`: página de posts, artigo, categoria) | ✅ |
| 1 | hero + abas de categoria | `8325:2675` (964) | `8673:3847` (813,16) | `blog-01-hero` · `template-parts/blog/01-hero.php` | ✅ |
| 2 | artigo em destaque | `8314:1380` (590) | `8673:3901` (641,25) | `blog-02-destaque` · `template-parts/blog/02-destaque.php` | ✅ |
| 3–7 | categoria ×5 (título + 3 cards + "Ver todos os artigos") | `8314:1689`, `8314:1768`, `8314:1795`, `8314:1822`, `8314:1849` | `8673:3920`, `8673:4790`, `8673:4839`, `8673:4888`, `8673:4937` | `blog-03-categoria` (repetido com `{{CAT_TITLE}}`) · `template-parts/blog/03-categorias.php` (loop) | ✅ |
| 8 | newsletter (componente) | `8436:2153` (498) | `8673:4986` (560) | `newsletter` · `template-parts/components/newsletter.php` | ✅ |
| 9 | Footer (compartilhado) | `8294:2907` | `8673:4452` | `partials/footer.html` · `footer.php` | ✅ |

Validação mobile (375, 2026-10-15): página 375×8038 = Figma `8673:3845`; gates por seção só com larguras de texto ≤1px (métrica da fonte) (título do hero com entrelinha arredondada para 38, como a caixa do Figma: gate 0 — revalidado após a página do artigo). Diferenças intencionais: rótulo de categoria nos cards = categoria real do post (Figma repete "Análises de caso"); placeholder da newsletter sem o espaço duplo do Figma. Sem rolagem horizontal de 320 a 767; abas do hero param logo abaixo do header (`section[id] { scroll-margin-top }`); favicon = marca "A" (`assets/images/favicon.svg`; no WP só se não houver Ícone do site).

### Blog — modelo de dados

| Conteúdo | Tipo | Slug WP | Campos |
|----------|------|---------|--------|
| Artigos | Post nativo | `post` | título, resumo (excerpt = linha fina), imagem destacada, categoria, data, autor |
| Categorias do blog | Categoria nativa | `category` | nome, slug, ordem (`_art_ordem`); 5 do Figma: Análises de caso, Implementação de IA, IA na operação, SEO e AI Search, Marketing com IA |
| Destaque | Post fixado | `is_sticky()` | o mais recente fixado (fallback: o mais recente) |
| Autor | Usuário WP | user meta | nome de exibição, `art_cargo`, foto (avatar) |
| Hero do blog | Customizer | `art_blog_eyebrow/_title/_text` | eyebrow, título com {destaque}, texto; abas = categorias com seção na página (`artemis_blog_hero_tabs()`) |
| Newsletter | Customizer | `art_newsletter_*` | eyebrow, título com {destaque}, texto, placeholder, botão, action do formulário |

## Post — mapa (desktop `8331:321` 1440×6048 · mobile `8673:5776` 375×8416)

Estático: `blog/anatomia-do-wcontrol-como-se-valida-um-documento-de-mil-paginas/index.html` (`src/pages.json` → página `post`, `assets: blog-03-categoria` para o `.post-card`). WP: `single.php` → `template-parts/post/*.php` + newsletter.

| # | Seção | Desktop | Mobile | Partial / template | Status |
|---|-------|---------|--------|--------------------|--------|
| 0 | Header (variante Blog; no celular com selo, 64 de altura) | `8340:2125` | `8693:2102` | `partials/header.html` (`page=post`, `.site-header--tag-m`) · `header.php` (`is_singular('post')`) | ✅ |
| 1 | topo do artigo (categoria · data, título, abertura, autor) | `8331:338` (691) | `8673:5779` (611) | `post-01-hero` · `template-parts/post/01-hero.php` | ✅ |
| 2 | conteúdo: texto + índice "Neste artigo" (fixo na rolagem) + chamada final | `8331:1178` (3324) | `8673:5795` (4913) | `post-02-conteudo` · `template-parts/post/02-conteudo.php` | ✅ |
| 3 | "Continue lendo" (3 cards; 2 no celular) | `8331:1587` (715) | `8673:6757` (809) | `post-03-relacionados` · `template-parts/post/03-relacionados.php` | ✅ |
| 4 | newsletter (componente) | `8436:2154` | `8673:6793` | `newsletter` · `components/newsletter.php` | ✅ |
| 5 | Footer (compartilhado) | `8331:799` | `8673:6215` | `footer` | ✅ |

A imagem do topo (`8331:1153` · `8673:5793`) está oculta no Figma — não implementada.

### Post — modelo de dados

| Conteúdo | Tipo | Chave | Campos |
|----------|------|-------|--------|
| Texto do artigo | `post_content` (editor de blocos) | — | parágrafo, título H2/H3, lista, imagem; estilos de bloco **Destaque** (título 40, fora do índice) e **Recorte do layout** (imagem 859×600 / 343×292) |
| Abertura | post meta | `_art_post_lead` | texto abaixo do título (vazio → resumo) |
| Números | post meta + shortcode | `_art_post_numeros` · `[artemis_numeros]` | rótulo + 3 itens (prefixo menor, valor, sufixo menor, descrição) |
| Chamada final | post meta + Customizer | `_art_post_cta_title/_text/_off` · `art_post_cta_*` | título, texto (padrão no Personalizar), botões, esconder |
| Índice | automático | — | H2 do texto (exceto "Destaque"), ids gerados pelo título; título `art_post_toc_title` |
| Continue lendo | consulta | — | 3 posts da mesma categoria, completando com os mais recentes; título `art_post_related_title` |

Validação (2026-10-15): página 1440×6048 e 375×8416 = Figma, Δ 0 em todos os blocos; conteúdo mobile Δ 0; demais diferenças só larguras de texto ≤1px (métrica da fonte). O texto do post usa `text-wrap: pretty` (o Figma marca `textWrapStyle: PRETTY`); as quebras de algumas linhas variam entre o algoritmo do Chrome e o do Figma sem mudar alturas. Cabin (corpo) é desenhada 1px acima do Figma dentro da mesma caixa — métrica vertical da fonte, igual no site todo. A imagem do artigo no Figma é a mesma capa de 672×573 ampliada — pedir ao cliente a versão em alta.

## Arquitetura (estático ⇄ tema, por seção)

| Camada            | Estático                                   | Tema WP (`theme/artemis/`)                        |
|-------------------|--------------------------------------------|---------------------------------------------------|
| Markup            | `partials/NN-slug.html` (+ `header`, `footer`) | `template-parts/home/NN-slug.php`, `header.php`, `footer.php` |
| Estilo            | `assets/css/main.css` (tokens + componentes) + `assets/css/sections/NN-slug.css` | mesmos arquivos (copiados por `tools/sync-theme.sh`), enfileirados por glob |
| Comportamento     | `assets/js/main.js` + `assets/js/sections/NN-slug.js` | idem                                       |
| Conteúdo          | texto fixo no partial                      | `inc/section-NN-slug.php` (CPT, Customizer via filtros, seed via filtro `artemis_seed`) |
| Página            | `tools/build.py` → `index.html`            | `front-page.php` itera `template-parts/home/*.php` |

Gate numérico: `python tools/diff.py SLUG [--mobile] --shot` (Chrome headless via DevTools — `tools/cdp_measure.mjs` — em 1440 ou 375; mede `data-figma` / `data-figma-m` vs `tools/figma-meta.xml` + `tools/figma-meta.d/*.xml`).
`data-figma` / `data-figma-m` existem só nos partials/previews — `build.py` remove do `index.html` e põe `?v=<mtime>` em todo CSS/JS.

## Tokens estruturais (Fase 0.5)

| Token           | Valor                         | Origem                                  |
|-----------------|-------------------------------|-----------------------------------------|
| `--frame-w`     | 1440px                        | width do frame `8160:163`               |
| `--section-px`  | clamp(16px, calc(32 / 1440 * 100vw), 32px) | padding lateral 32px das seções (32,000 em 1440) |
| `--content-w`   | 1376px                        | 1440 − 2×32 (main container / blocks)   |
| `--content-gap` | 32px                          | gap entre colunas (672 + 32 + 672)      |
| `--section-py`  | 120px                         | padding-top padrão das seções           |
