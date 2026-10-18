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
- WhatsApp `+55 (48) 0000-0000` e links de redes sociais são placeholders.
- MktOps: o texto do topo repete o da Artemis SEO ("E a IA responde citando alguém…") — parece copy-paste no Figma; mantido. O botão "Rodar o SpyOps" aponta para `/contato/` até existir o destino.
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
| 3 | nossos sistemas      | `8294:500`  | 1033   | 4 cards → CPT `art_sistema`; ilustrações em camadas animadas (`_art_sistema_ilustra`, `tools/gen_ilustras.py`): laser verde, pinos azuis sobem, segmentos rosa carregam, cubo laranja segue a rota; tocam ao entrar rolando para baixo e com o cursor no card (o card não se mexe: não é link) | ✅      |
| 4 | testmonial           | `8186:1160` | 443    | → CPT `art_depoimento`; globo em canvas girando no enquadramento do Figma (esfera 594,8/365,1 R 351,3; Atlântico no centro; #CECECE com degradê horizontal 0→50%; pontos 40% mais fortes que o Figma a pedido do cliente: `INK = 1.4` → 0→70%). Terra = grade do mapa real sem sobreposição; oceano = mesma grade a 16% (globo nunca vazio); velocidade 1 volta/90 s sobre continentes, até 10× sobre oceano aberto (o Pacífico passa em ~5 s). Dados: `assets/data/globe-points.json` (5 KB, `tools/gen_globe_figma.py`) | ✅ |
| 5 | como funciona        | `8307:3380` | 814    | 4 passos; coluna esquerda sticky; ao entrar na tela os passos aparecem um por vez (número sobe, depois texto + traço; 1,1 s por passo, ~5 s no total, `05-como-funciona.js`) | ✅      |
| 6 | Diagram              | `8653:2286` | 843    | diagrama POC → MVP → Escala em HTML/CSS (sem hover: os cartões não são link) | ✅      |
| 7 | estudos de impacto   | `8195:1063` | 1055 → 1127 | carrossel 4 estudos → CPT `art_estudo`; troca sozinho só na tela: a cada 8 s com o cursor fora e 20 s com o cursor em cima (entrar/sair só troca o prazo); segura durante o toque e com a aba escondida. Para de vez com o botão Pausar/Reproduzir (fora do Figma, WCAG 2.2.2: antes das setas, primeiro focável do carrossel), com qualquer navegação à mão e com o foco de teclado entrando; Reproduzir retoma. Altura reservada = estudo mais alto (`--estudos-h`, JS): a troca não empurra o conteúdo de baixo (CLS) — por isso a seção tem 1127 em vez de 1055 e a sobra dos estudos mais baixos fica no texto, com o rodapé no pé. Na troca o texto que sai sobe na diagonal e some; o que entra sobe na diagonal bloco a bloco; pintura com zoom-out leve e captura deslizando no sentido da navegação | ✅      |
| 8 | stats                | `8486:1798` | 382    | → Customizer `art_stat_*`; número conta de 0 até o valor quando a célula entra na tela, um por vez (2 s cada, o seguinte 1,2 s depois; formato BR do próprio texto); sem hover (não é link). O número que conta é `aria-hidden` e o valor real fica em `.stats__sr` (leitor de tela); largura medida com a Exo carregada, sufixo parado; impressão sai com o valor final | ✅      |
| 9 | como trabalhamos     | `8490:2191` | 865    | 5 regras + ilustração isométrica que monta com o scroll (tela sobe 56px, tracejado se desenha, selo aparece); depois de montada as camadas flutuam (tela/selo descem até 5/4px, código sobe 2px) | ✅ |
| 10| call to action       | `8160:1628` | 628    | barra de status (estática) + CTA + traços | ✅    |
| 11| Footer container     | `8165:2501` | 740    | newsletter, links, contato, redes       | ✅      |

Página inteira (1440, sem scrollbar): altura 8594 no Figma → 8666 no código (+72 da altura reservada dos estudos, ver seção 7); demais seções Δ ≤ 0,8px. Diferenças restantes = largura de texto por métrica de fonte web (≤ 1,9px) e glifos ~1px mais altos (métrica vertical do Google Fonts).

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
| estudos de impacto | `8195:1063` | `8635:2366` | 1321,47 → 1442,47 | carrossel com Pausar/Reproduzir, setas, teclado e swipe; estudos 2–4 extrapolados do layout do estudo 1; altura reservada do estudo mais alto (+121 em 375) | ✅ |
| stats              | `8486:1798` | `8635:2371` | 932 | 4 números empilhados; cada um conta quando entra na tela | ✅ |
| como trabalhamos   | `8490:2191` | `8635:2435` | 1227 | animação de scroll da ilustração mantida; zoom 0,9/0,8 abaixo de 370/341px | ✅ |
| call to action     | `8160:1628` | `8635:2625` | 576 | barra de status cortada na borda, como no Figma | ✅ |
| Footer             | `8165:2501` | `8641:1595` | 1459 | em 320 o 6º ícone social desce de linha | ✅ |

Página inteira (verificada com `node tools/page_check.mjs URL LARGURA`): 375 → 375×13283 (Figma 13162: +121 da altura reservada dos estudos; as outras seções com Δ 0,00); 1440 → 1440×8666 (Figma 8594: +72 dos estudos; demais Δ ≤ 0,19). Sem rolagem horizontal em 320/375/390/414/768/1024/1280/1440.

### Mobile — a confirmar com cliente/designer
- Placeholder do e-mail no Figma mobile com espaço duplo ("Seu endereço  de e-mail") — o código usa espaço simples.
- "Como funciona": só o marcador do passo 1 (`8635:2324`) está ancorado embaixo no Figma mobile; implementado literalmente.
- Estudos 2–4 não têm layout mobile no Figma — seguem o do estudo 1.
- O fundo escurecido atrás da gaveta do menu não está no Figma (leitura da anotação "em overlay").
- Animações pedidas pelo cliente fora do Figma (passos, contagem, troca automática dos estudos, flutuação da ilustração 09): todas respeitam `prefers-reduced-motion` e saem no estado final na impressão. Em repouso a geometria é a do Figma, exceto os estudos: altura do mais alto e o botão Pausar/Reproduzir (+48 nos controles). O `diff.py` mede no meio da entrada dos passos — confira o repouso.

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

Interações pedidas pelo cliente, fora do Figma (2026-10-16). Valem no estático e no tema, que usam o mesmo CSS e JS.
- **Luzes do banner** (`blog-01-hero.js`): o banner tem `data-loop` (Motion). Enquanto o motion.js liga `.is-looping` (até 5 s a cada entrada na tela), correm 30 luzes automáticas (`AUTO`): as 10 do Figma partem da sua posição e 20 acendem em 0,4 s em pontos sorteados das linhas. No fim do loop as automáticas apagam em 0,6 s (`SETTLE`) e as 10 do Figma reacendem no repouso (`.is-back`).
- **Clique ou toque no banner**, a qualquer momento (ação da pessoa, fora do limite de 5 s): cria uma luz no ponto da linha mais próximo, que corre até o fim da linha e some. Ficam no máximo 24 de uma vez (`CLICK_MAX`) e o banner mostra o cursor de clique (`.is-clickable`).
- **Movimento reduzido, sem JS ou sem o motion.js**: as 10 luzes do Figma ficam paradas.
- **Hover nos artigos**: só com mouse (`hover: hover`); o foco do teclado tem o mesmo efeito.
  - O card em destaque aumenta 2% (`scale(1.02)`).
  - Os cards das categorias aumentam 4% e ganham fundo `black-100` e borda `gray-800` por cima dos vizinhos.
  - O "Continue lendo" do artigo (`.blog-cat--related`) fica de fora.

Motion (2026-10-18; atributos no partial e no template PHP — no tema, a meta e o autor do destaque recebem o atributo por `artemis_blog_attr()`, porque vêm de funções compartilhadas com o card e o artigo):

| Seção | Elemento | Atributos |
|---|---|---|
| hero | eyebrow · título · texto | `data-reveal="fade"` · `data-split="lines"` (+100 ms) · `data-reveal="up"` (+300 ms), todos `data-motion-start="load"` |
| hero | abas | `data-reveal-group="up" data-stagger="50"` (+450 ms, ao carregar) |
| hero | banner | `data-loop` (luzes, acima) |
| destaque | imagem | `data-wipe="up" data-wipe-zoom` no `.blog-destaque__media` (o assentamento usa `scale`; o hover, `transform` — não brigam) |
| destaque | texto | `data-reveal-group` no `.blog-destaque__text`: selo `pop`, meta `fade`, título `split`, linha fina e autor `up` |
| categoria ×5 | título · cards · botão | `data-split="lines"` · `data-reveal-group="up"` no `.blog-cat__grid` · `data-reveal="up"` (o reveal usa `translate`; o crescer do hover, `transform`) |

Newsletter, header, rodapé e botões: automáticos (motion.js).

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

## Serviços — mapa (desktop `8340:1700` 1440×3523 · mobile `8699:4921` 375×6599)

Estático: `servicos/index.html` (`src/pages.json` → página `servicos`). WP: página de slug `servicos` → `page-servicos.php` → `template-parts/servicos/*.php` + `template-parts/home/10-cta.php`.

| # | Seção | Desktop | Mobile | Partial / template | Status |
|---|-------|---------|--------|--------------------|--------|
| 0 | Header (o da Home; "Serviços" atual, "Estudos de impacto" → `/#estudos`) | `8340:2111` | `8700:6155` | `partials/header.html` (`page=servicos`) · `header.php` | ✅ |
| 1 | topo (título com trecho cinza, texto, botão) | `8340:2096` (611) | `8699:4923` (450) | `servicos-01-hero` · `template-parts/servicos/01-hero.php` | ✅ |
| — | "formas de contratar" | `8575:2269` | — | oculto no Figma — não implementado | — |
| 2 | as três etapas: abas (selo azul + barra de progresso; avançam sozinhas a cada 6 s, ou 20 s com o cursor/foco de teclado na seção; o clique troca na hora e o avanço segue), ilustração em 4 camadas que monta com o scroll e flutua (a da Home, seção 9), painel da etapa ativa · hover: só a etapa (aba, clicável) cresce 10% · textos entram na diagonal (etapas ao chegar na tela, itens do painel um por vez a cada troca) · mobile: etapas abertas em sequência + ilustração | `8587:2376` (instância de `8573:1797`: 593 · 569 · 496 por etapa) | `8709:5717` (869) · `8709:5588` (869) · `8709:5463` (539) · `8709:5854` (421) | `servicos-02-etapas` · `template-parts/servicos/02-etapas.php` | ✅ |
| 3 | o que não fazemos (4 cards, sem hover, faixa de traços, nota Artemis SEO / MktOps) | `8348:2419` (871) | `8699:5244` (1304) | `servicos-03-nao-fazemos` · `template-parts/servicos/03-nao-fazemos.php` | ✅ |
| 4 | chamada final (a da Home; no celular 628 de altura → `10-cta--servicos.css`) | `8348:2636` (628) | `8699:5366` (628) | `10-cta` · `template-parts/home/10-cta.php` | ✅ |
| 5 | Footer | `8340:1926` | `8700:5995` | `footer` | ✅ |

### Serviços — modelo de dados

| Conteúdo | Tipo | Chave | Campos |
|----------|------|-------|--------|
| Topo | Customizer | `art_srv_title` / `_lead` / `_cta` | título com {trecho em cinza}, texto, rótulo do botão (link = `art_contact_cta_url`) |
| Etapas | CPT | `art_etapa` (ordem = `menu_order`, selo "Etapa N" pela posição) | título · `_art_etapa_quem` (um item por linha) · `_art_etapa_b2_icon` (arquivo/cifrão) · `_art_etapa_b2_label` · `_art_etapa_b2_text` · `_art_etapa_duracao` (vazio esconde) |
| O que não fazemos | Customizer | `art_srv_nao_*` | título com {destaque}, texto, 4 cards (título + texto), nota |
| Ilustração, ícones, faixa de traços | arquivos do tema | `assets/images/servicos-02-etapas`, `servicos-03-nao-fazemos` | exportados do Figma (SVG, texto em curvas). A ilustração vem em 4 camadas do tamanho do quadro — `ilustra-{base,tela,tracejado,selo}.svg` (desktop, I8587:2376;8709:4560, 296×593) e `m-ilustra-*.svg` (celular, 8709:5854, 375×421) —, separadas do export de cada nó pela ordem de pintura: base de código até o tique, tela (parede `#09090B` com stroke 1,56081) até o tique, tracejado `#CECECE`, selo. O export do desktop omite o que passa de x = 296 (por isso um export por recorte). |

Validação (2026-10-16): 1440×3523 e 375×6599 = Figma com Δ 0 em todos os blocos; gates das seções 1–3 com 0 diferenças (só a largura do texto do botão do topo, métrica da fonte); as 3 variantes das abas conferidas (posições das etapas, barra em 182/297/374, alturas 593/569/496). Fiel ao Figma: no celular a última etapa tem o ícone ao lado do texto (as outras, em cima) e só a lista da etapa 1 usa quebra "pretty" — parecem inconsistências do design; cada uma é uma regra de CSS em `servicos-02-etapas.css`.

Animações (2026-10-17, pedido do cliente): ilustração das etapas montada (sem JS / movimento reduzido) = os exports antigos pixel a pixel (máx. 9/255 no desktop, 2/255 no celular, antialiasing); alturas 3523 / 6599 inalteradas; ritmos medidos em headless (fora 6 s, dentro 20 s, volta ao rápido quando o cursor sai mesmo com o foco no botão clicado); hovers sem rolagem horizontal. Medidas da montagem em `servicos-02-etapas.css` em unidades do desenho ÷ `--h` (593 · 421).

## Sobre — mapa (desktop `8348:3076` 1440×7855,45 · mobile `8689:1932` 375×10334,67)

Estático: `sobre/index.html` (`src/pages.json` → página `sobre`, `assets: sobre-02-porque` = `.sobre-split` e `.sobre-note` compartilhados). WP: página de slug `sobre` → `page-sobre.php` → `template-parts/sobre/*.php`.

| # | Seção | Desktop | Mobile | Partial / template | Status |
|---|-------|---------|--------|--------------------|--------|
| 0 | Header (o da Home; "Sobre" atual) | `8348:3077` | `8693:2103` | `partials/header.html` (`page=sobre`) | ✅ |
| 1 | topo (título com trecho cinza, texto, círculos ao fundo) | `8355:1920` (522) | `8689:1935` (342) | `sobre-01-hero` · `01-hero.php` | ✅ |
| 2 | foto da equipe + "Por que existimos" | `8497:2188` (917) | `8689:1946` (734) | `sobre-02-porque` · `02-porque.php` | ✅ |
| 3 | "Três coisas que se repetem" (01/02/03) | `8355:1946` (765) | `8689:1956` (1004) | `sobre-03-tres-coisas` · `03-tres-coisas.php` | ✅ |
| 4 | "O sistema audita a sua operação de graça" | `8355:1996` (434) | `8689:1992` (560) | `sobre-04-audita` · `04-audita.php` | ✅ |
| 5 | "Quem está por trás" — versão A: 4 cards + nota | `8355:2000` (1051,67) | `8689:1996` (2780,33) | `sobre-05-equipe` · `05-equipe.php` | ✅ |
| 6 | "Quem está por trás" — versão B: título e bio fixos, fotos na rolagem (a do centro acende, a bio troca) · mobile: 1ª foto, bio, demais fotos | `8355:3485` (2629,78) | `8689:2027` (2678,33) | `sobre-06-socios` · `06-socios.php` · `sobre-06-socios.js` | ✅ |
| 7 | "Onde estamos" (texto, botão, forma, mapa Google real + faixa de traços) | `8355:3565` (716) | `8689:2043` (717) | `sobre-07-onde` · `07-onde.php` | ✅ |
| 8 | Footer | `8348:3655` | `8693:2591` | `footer` | ✅ |

Decisões (2026-10-16, com o usuário): as duas versões da equipe entram, como no Figma; o mapa é o Google Maps real (iframe) no lugar do print.

### Sobre — modelo de dados

| Conteúdo | Tipo | Chave | Campos |
|----------|------|-------|--------|
| Foto da equipe | imagem destacada da página Sobre | — | sem ela, `assets/images/sobre/equipe.jpg` |
| Textos | Customizer | `art_sobre_*` | topo, "Por que existimos", 3 itens, "O sistema audita", títulos/texto/nota da equipe, "Onde estamos" (texto, botão, endereço do mapa) — linha em branco separa parágrafos |
| Sócios | CPT `art_membro` (o da Home) | `_art_membro_foto_sobre` · `_art_membro_ordem_sobre` | retrato 3:4 desta página, ordem própria (a da Home não muda); nome = título, `_art_membro_cargo`, bio = conteúdo do editor |

Validação (2026-10-16): 1440×7855 e 375×10335 = Figma (Δ ≤ 0,03 por bloco); gates das 7 seções com 0 diferenças, salvo larguras de texto ≤ 0,94 px (métrica da fonte). No Figma: "Nome"/"Cargo" e a bio da versão B são provisórios (o estático mantém; o WP usa os dados dos sócios); os cards (A) e as fotos (B) estão em ordens diferentes; no mobile o marcador do item 01 está 67 px mais abaixo que os outros — mantive os três iguais. Retratos 3 e 4 atribuídos a Enzo e Willy por palpite (a confirmar).

## Contato — mapa (desktop `8348:3835` 1440×2075 · mobile `8693:2761` 375×2934)

Estático: `contato/index.html` (`src/pages.json` → página `contato`). WP: página de slug `contato` → `page-contato.php` → `template-parts/contato/*.php`.

| # | Seção | Desktop | Mobile | Partial / template | Status |
|---|-------|---------|--------|--------------------|--------|
| 0 | Header (o da Home; "Agendar diagnóstico" atual) | `8348:3836` | `8693:3062` | `partials/header.html` (`page=contato`) · `header.php` | ✅ |
| 1 | contatos · formulário · ilustração (mobile: formulário + faixa de 16 da ilustração, contatos embaixo) | `8370:1542` (1191) | `8693:2763` (1383) | `contato-01-form` · `01-form.php` · `contato-01-form.js` | ✅ |
| 2 | faixa de traços (strokes em cima e embaixo) | `8370:1696` (64) | `8693:2807` (32) | `contato-02-faixa` · `02-faixa.php` | ✅ |
| 3 | Footer | `8348:3837` | `8693:3078` | `footer` | ✅ |

### Contato — modelo de dados

| Conteúdo | Tipo | Chave | Campos |
|----------|------|-------|--------|
| E-mail, WhatsApp, endereço | Customizer (os do rodapé) | `art_contact_email` / `_whatsapp` / `_address` | — |
| Formulário | Customizer | `art_contato_*` | título com {cinza}, texto, botão, nota, mensagem de confirmação, quem recebe (`_to`, vazio = e-mail do site), envio externo opcional (`_action`) |
| Envio | tema | `admin-post.php?action=artemis_contato` | nonce + campo-isca; `wp_mail()` com Reply-To de quem escreveu; volta com `?contato=ok|erro|dados`. No estático (sem servidor): avisa "Formulário ainda não configurado.", como a newsletter |

Validação (2026-10-16): 1440×2075 e 375×2934 = Figma com Δ 0 em todos os blocos; gates das 2 seções com 0 diferenças. A ilustração é a imagem como o Figma a renderiza (a pintura esticada na vertical) — exportada do nó, não do arquivo original.

## Artemis SEO — mapa (desktop `8348:4008` 1440×3463,51 · mobile `8704:6450` 375×5860)

Estático: `artemis-seo/index.html` (`src/pages.json` → página `seo`, `assets: 10-cta`). WP: página de slug `artemis-seo` → `page-artemis-seo.php` → `template-parts/seo/*.php`. O link "Artemis SEO" do rodapé aponta para cá.

| # | Seção | Desktop | Mobile | Partial / template | Status |
|---|-------|---------|--------|--------------------|--------|
| 0 | Header (o da Home) | `8348:4009` | `8704:7178` | `partials/header.html` | ✅ |
| 1 | topo: título com trecho cinza, 2 botões, pintura ao fundo e o navegador com a resposta de IA (imagem exportada) | `8388:1923` (1081,51) | `8704:6452` (1237) | `seo-01-hero` · `01-hero.php` | ✅ |
| 2 | "O problema" | `8393:1582` (418) | `8704:6597` (464) | `seo-02-problema` · `02-problema.php` | ✅ |
| 3 | "Duas formas de contratar": acordeão Plataforma / SEO’Ops (selo de preço, texto, "Para quem", barra de progresso que avança a cada 9 s, imagem da opção) · mobile: os dois estados em sequência | `8396:3370` (682; variantes em `8396:3369`) | `8704:7347` (1051) + `8704:7536` (1066) | `seo-03-formas` · `03-formas.php` · `seo-03-formas.js` | ✅ |
| 4 | "Por que conosco" (barra de status e traços da chamada da Home) | `8399:1455` (462) | `8704:6603` (523) | `seo-04-cta` · `04-cta.php` (+ `10-cta.css/js`) | ✅ |
| 5 | Footer | `8348:4010` | `8704:7179` | `footer` | ✅ |

### Artemis SEO — modelo de dados

| Conteúdo | Tipo | Chave | Campos |
|----------|------|-------|--------|
| Topo, problema, fim | Customizer | `art_seo_*` | título com {cinza}, texto, 2 botões (texto + link, usados no topo e no fim), "O problema", "Por que conosco" |
| Formas de contratar | Customizer | `art_seo_formas_title` · `art_seo_opt{1,2}_name/_tag/_desc/_para` | nome, selo de preço, texto, "Para quem" de cada opção |
| Imagens | arquivos do tema | `assets/images/seo` | navegador (PNG), pintura do topo, imagens das opções como o Figma renderiza |

Validação (2026-10-16): 1440×3464 e 375×5860 = Figma (Δ ≤ 0,01); gates das 4 seções com 0 diferenças, salvo larguras de texto ≤ 1,06 px; variante SEO’Ops conferida após o clique (título 120, linha 261, SEO’Ops 286, texto 331, "Para quem" 471, barra 560, altura 682). No Figma: o retângulo branco nas imagens das opções é o lugar da tela do produto (mantido); no celular as duas opções usam a mesma pintura (no desktop, cada uma tem a sua) — mantido como está.

## MktOps — mapa (desktop `8348:4181` 1440×5137 · mobile `8695:3268` 375×7468,08)

Estático: `mktops/index.html` (`src/pages.json` → página `mktops`, `assets: 10-cta`). WP: página de slug `mktops` → `page-mktops.php` → `template-parts/mktops/*.php`. O link "MktOps" do rodapé aponta para cá.

| # | Seção | Desktop | Mobile | Partial / template | Status |
|---|-------|---------|--------|--------------------|--------|
| 0 | Header (o da Home) | `8348:4182` | `8695:4281` | `partials/header.html` | ✅ |
| 1 | topo: título com trecho cinza, 2 botões, diagrama MktOps (Skills, MCP, Pipelines, Infra → marca; SVG exportado, vertical no mobile) | `8425:3827` (864) | `8695:3271` (1500,08) | `mktops-01-hero` · `01-hero.php` | ✅ |
| 2 | "O problema" + foto | `8425:4174` (966) | `8695:3456` (671) | `mktops-02-problema` · `02-problema.php` | ✅ |
| 3 | "O que é instalado": 5 itens com arquivo (1 grande + 2×2) e faixa da comunidade com ilustração cortada | `8502:4262` (911) | `8695:3463` (1506) | `mktops-03-instalado` · `03-instalado.php` | ✅ |
| 4 | "Os três níveis": Solo / Squad (selo Popular) / Enterprise, 5 linhas cada | `8502:3101` (948) | `8695:3548` (1644) | `mktops-04-niveis` · `04-niveis.php` | ✅ |
| 5 | "Garantias" (barra de status e traços da chamada da Home, traços em green-300) | `8436:2565` (628) | `8695:3733` (628) | `mktops-05-cta` · `05-cta.php` (+ `10-cta.css/js`) | ✅ |
| 6 | Footer | `8348:4183` | `8695:4282` | `footer` | ✅ |

### MktOps — modelo de dados

| Conteúdo | Tipo | Chave | Campos |
|----------|------|-------|--------|
| Topo e problema | Customizer | `art_mk_title/_lead/_btn{1,2}/_btn{1,2}_url/_problema_*` | título com {cinza}, texto, 2 botões, "O problema" (linha em branco separa parágrafos) |
| O que é instalado | Customizer | `art_mk_inst_title` · `art_mk_item{1..5}` · `art_mk_comunidade` | texto de cada item com {cinza}; o desenho do arquivo (Tools, Brand, DNA, Template, Guide) é fixo pela posição |
| Planos | Customizer | `art_mk_plano{1..3}_name/_tag/_rows` | nome, selo (vazio esconde), linhas "Rótulo \| Valor" |
| Garantias | Customizer | `art_mk_cta_note/_title/_bold/_text/_btn/_btn_url` | nota, título, frase em negrito, texto, botão |
| Imagens | arquivos do tema | `assets/images/mktops` | diagramas (SVG), foto do problema, arquivos, comunidade, desenhos dos planos, traços verdes |

Validação (2026-10-16): 1440×5137 e 375×7468,08 = Figma (Δ 0 em todos os blocos); gates das 5 seções com 0 diferenças, salvo larguras de texto ≤ 0,95 px (nome dos planos, selo). Template PHP renderizado com o tema = mesmo texto e classes do estático. 768–1023: planos empilhados (em 3 colunas o desenho cobria nome e selo).

## Regra de hover (2026-10-18, pedido do cliente)

Efeito de passar o mouse que mexe no elemento (crescer, subir, zoom, sombra, texto que avança) só em link ou botão: em card ou texto que não é clicável dá a impressão de link. Removidos: cards dos sistemas e do diagrama e números das stats (Home), textos do painel e cards "o que não fazemos" (Serviços). Mantidos: links, cards do Blog (o card inteiro é link), abas de Serviços, setas e pausa dos Estudos. Animações de imagem, de entrada e de rolagem continuam.

## Motion (2026-10-18) — biblioteca global

Arquivos: `assets/js/motion.js` + `assets/css/motion.css` (carregados em todas as páginas: `src/index.template.html` e `functions.php`; o `<head>` liga `html.motion-on` antes da 1ª pintura). Referência: técnicas da galeria Animation do Awwwards (plano em scratchpad/motion-plan.txt). Princípio: em repouso, sem JS, com movimento reduzido e na impressão a página é o Figma; toda entrada termina com `.is-done` e sem spans auxiliares.

| Atributo | Efeito |
|---|---|
| `data-reveal="up\|diag\|fade\|pop\|rise\|grow-x\|grow-y"` (+ `data-origin`) | entra uma vez ao chegar na tela |
| `data-split="lines"` | título por linhas (máscara por palavra; quebras do repouso medidas antes e fixadas; HTML original volta no fim) |
| `data-wipe="right\|left\|up\|down\|out"` (+ `data-wipe-zoom`) | recorte que abre (clip-path) e imagem que assenta 1,06→1 |
| `data-count` | número conta de 0 ao valor (formato BR; aria-hidden + valor real para leitor de tela) |
| `data-type` / `data-type-group` | texto digitado (barra de status) |
| `data-parallax="0.12"` (+ `data-parallax-max="24"`) | desloca na rolagem; 0 com a página no topo |
| `data-loop` (+ `data-loop-max="5"`) | `.is-looping` por até 5 s a cada entrada na tela (WCAG 2.2.2) — as seções ligam o loop infinito só com essa classe |
| `data-reveal-group[="variante"]` (+ `data-stagger`) | dispara os itens em sequência (máx. 1,2 s acumulado) |
| `data-motion-delay="ms"` · `data-motion-start="load"` | atraso · toca ao carregar (topo) |

Automático (sem atributo no markup): rótulo que rola no hover de todo `.btn` (só mouse); sublinhado no hover dos links do header e legais do rodapé (e `data-hover="line"`); CTA (status digitado, traços acendendo, nota, título por linhas, texto e botões); newsletter; traços do rodapé; links da gaveta do celular em sequência; transição entre páginas (View Transitions); LED e traços do CTA/newsletter só 5 s por entrada. Não usar: rolagem suave que substitui a nativa, WebGL, cursor próprio, marquee/loop infinito, preloader, seção presa com scrub, hover que mexe em não clicável.
Validação: `node tools/motion_check.mjs URL [largura]` (movimento liberado: tudo termina em repouso, sem sobras, sem rolagem lateral, sem erro de JS) + `page_check`/`diff.py` (movimento reduzido = repouso = Figma).

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
