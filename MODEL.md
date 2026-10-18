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

### Home — Motion (biblioteca global, atributos no partial e no template PHP)

| Seção | Entrada |
|---|---|
| 01 hero | ao carregar (`data-motion-start="load"`): grupo em `.hero__row` — eyebrow fade → h1 `data-split` (0,6 s por linha, 40 ms entre linhas: termina em ≤ 0,9 s depois de começar, é o LCP) → lead up → 2 botões up. Logos em fade da esquerda para a direita (grupo em `.clients__grid`, 70 ms; o fade é no logo, não na célula — no tema via `preg_replace` sobre `artemis_cliente_logo()`), faixa do padrão com `data-wipe` |
| 02 | cabeçalho (eyebrow fade, título split); avatares `data-reveal="pop"` na `<img>` (o `li` tem o tooltip; no tema em `artemis_oqf_avatar()`); parágrafos `data-reveal-group="up"` (120 ms) |
| 03 | só o cabeçalho (título split, texto up); os cards têm IO próprio |
| 04 | eyebrow fade, citação split, autor up (+350 ms); globo como estava |
| 05 | bloco da esquerda (eyebrow fade, título split, parágrafos up) + "Quatro passos:" fade; os passos seguem com a entrada própria |
| 06 | grupo único na figura (~2,2 s, `data-stagger="0"` + `data-motion-delay`): fases fade 0/100/200 → linhas POC/MVP/Escala `data-wipe="down"` 250/400/550 → cartões up 600–1060, versões 1160+80·i → legenda fade 1500/1600. Só eixo Y (no celular o desenho rola na horizontal). Depois, a cada entrada na tela (`data-loop` no figure), o ciclo do fluxo toca uma vez (~4,6 s, `.is-flowing`): pulso de luz desce as linhas POC → MVP → Escala (faixa recortada pelo SVG das linhas); cartões acendem na ordem (brilho na borda) 01 → 02 → Ajuste (ícone dá uma volta) → Humano errou (tracejado anda, lixeira treme) → 03 → V 1.0/1.1/1.2; losangos da legenda pulsam. Sai da tela: para no quadro do Figma |
| 07 | cabeçalho (eyebrow fade, título split); a 1ª entrada do estudo ativo (`.is-pending` → `.is-entering`) toca quando o carrossel chega na tela e a contagem de 8 s começa aí |
| 08 | faixa de cima `data-wipe="out"`; a de baixo (`data-stats-band-end`) ganha o `data-wipe="out"` do 08-stats.js quando a última contagem termina |
| 09 | cabeçalho padrão; regras up em sequência no `.como-trabalhamos__rule-body` (grupo no `ul`, 120 ms); faixa de diagonais `data-wipe` |

Loops com tempo (`data-loop`, até 5 s por entrada na tela; ao parar, cada animação termina o ciclo e assenta no quadro do Figma, sem salto): traços do hero (`.is-blinking`, 01-hero.js), globo do 04 (gira só no loop e volta suave à orientação inicial), flutuação do 09 (só montada e no loop). Laser do 03: duas passadas de 2,5 s a cada vez que o card toca (entrada ou cursor) — finito, sem `data-loop`.

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
- **Luzes do banner** (`blog-01-hero.js`): o banner tem `data-loop` (Motion). Enquanto o motion.js mantém `.is-looping`, correm 30 luzes automáticas (`AUTO`): as 10 do Figma partem da sua posição e 20 acendem em 0,4 s em pontos sorteados das linhas. No fim do loop as automáticas apagam em 0,6 s (`SETTLE`) e as 10 do Figma reacendem no repouso (`.is-back`).
- **Clique ou toque no banner** (o "tiro" da pessoa), a qualquer momento, durante o loop ou fora dele:
  - um anel abre no ponto da linha mais próximo do clique (`.blog-hero__ring`);
  - dali sai uma luz que se destaca das automáticas (`--shot`): 64×2, ou 36×1,5 no celular, com cabeça branca e brilho azul;
  - ela acende na hora (`SHOT_IN`), corre 3× mais rápido (`SHOT_SPEED`) até o fim da linha e some;
  - ficam no máximo 24 de uma vez (`CLICK_MAX`) e o banner mostra o cursor de clique (`.is-clickable`).
- **Movimento reduzido, sem JS ou sem o motion.js**: as 10 luzes do Figma ficam paradas.
- **Impressão**: no `beforeprint` o banner vai direto ao quadro do Figma, mesmo com o loop ligado. Não espera o `SETTLE`, não reacende e tira as luzes do clique; no CSS de impressão, as `--js` somem.
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

### Post — motion (2026-10-18, biblioteca global › Motion)

| Onde | Atributos | Estático | WordPress |
|---|---|---|---|
| Topo, ao carregar | `.post-hero__article` `data-reveal-group data-motion-start="load"`: meta `fade`, h1 `data-split`, abertura `up`, autor `up` (90 ms entre itens, 80 ms por linha: termina em ~1,15 s no desktop e ~1,23 s no celular, 4 linhas) | `post-01-hero` | `01-hero.php` (autor de `artemis_blog_author()` ganha o atributo via `WP_HTML_Tag_Processor`) |
| Categoria do topo e links do índice | `data-hover="line"` (sublinhado; o link atual do índice fica sem) — o link do índice é `inline` para a linha acompanhar o texto à direita; a caixa do Figma (largura toda) é o `<li>` | `post-01-hero`, `post-02-conteudo` | `01-hero.php`, `02-conteudo.php` |
| Títulos H2 do texto | `data-split="lines"` | `post-02-conteudo` | `artemis_post_content()` acrescenta em todo H2 |
| Imagem "Recorte do layout" | `data-wipe="up" data-wipe-zoom` | `post-02-conteudo` | filtro `render_block_core/image` (classe `is-style-recorte`) |
| Números | `.post-numeros` `data-reveal-group data-stagger="240"`: rótulo e descrições `fade`, valores `data-count data-count-duration="800"` (um por vez: o seguinte começa com o anterior em ~94%) | `post-02-conteudo` | `[artemis_numeros]` |
| Continue lendo | título `data-split`, `.blog-cat__grid` `data-reveal-group="up"` (cards em sequência; no celular o 3º fica oculto); no hover só o título muda de cor | `post-03-relacionados` | `03-relacionados.php` |

Chamada final do texto (`.post-cta`) sem entrada: só o rótulo que rola dos botões (automático). Validação: `motion_check` 1440 e 375 — pendentes 0, sobras 0, erros 0, página 1440×6048 / 375×8416; `page_check` Δ 0 em todos os blocos; `diff.py` das 3 seções com as mesmas diferenças de antes (larguras de texto ≤1px).

## Serviços — mapa (desktop `8340:1700` 1440×3523 · mobile `8699:4921` 375×6599)

Estático: `servicos/index.html` (`src/pages.json` → página `servicos`). WP: página de slug `servicos` → `page-servicos.php` → `template-parts/servicos/*.php` + `template-parts/home/10-cta.php`.

| # | Seção | Desktop | Mobile | Partial / template | Status |
|---|-------|---------|--------|--------------------|--------|
| 0 | Header (o da Home; "Serviços" atual, "Estudos de impacto" → `/#estudos`) | `8340:2111` | `8700:6155` | `partials/header.html` (`page=servicos`) · `header.php` | ✅ |
| 1 | topo (título com trecho cinza, texto, botão) · motion ao carregar: título por linhas (o trecho cinza por último) → texto (350 ms) → botão (500 ms) | `8340:2096` (611) | `8699:4923` (450) | `servicos-01-hero` · `template-parts/servicos/01-hero.php` | ✅ |
| — | "formas de contratar" | `8575:2269` | — | oculto no Figma — não implementado | — |
| 2 | as três etapas: abas (selo azul + barra de progresso; avançam sozinhas a cada 6 s, ou 20 s com o cursor/foco de teclado na seção; o clique troca na hora e o avanço segue), ilustração em 4 camadas que monta com o scroll e, montada, flutua um ciclo (≤ 4,8 s) a cada entrada na tela — `data-loop` ligado pelo JS no fim da montagem, `.is-looping` da biblioteca motion, painel da etapa ativa · hover: só a etapa (aba, clicável) cresce 10% · textos entram na diagonal (etapas ao chegar na tela, itens do painel um por vez a cada troca) · mobile: etapas abertas em sequência + ilustração | `8587:2376` (instância de `8573:1797`: 593 · 569 · 496 por etapa) | `8709:5717` (869) · `8709:5588` (869) · `8709:5463` (539) · `8709:5854` (421) | `servicos-02-etapas` · `template-parts/servicos/02-etapas.php` | ✅ |
| 3 | o que não fazemos (4 cards, sem hover, faixa de traços, nota Artemis SEO / MktOps) · motion: título por linhas → texto; cards um a um (`data-reveal-group`, 90 ms: X em pop, título e texto sobem; caixa e divisórias paradas); faixa com `data-wipe`; marcas em pop → nota | `8348:2419` (871) | `8699:5244` (1304) | `servicos-03-nao-fazemos` · `template-parts/servicos/03-nao-fazemos.php` | ✅ |
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

Motion (2026-10-18, biblioteca global): `motion_check` 1440 / 375 com 154 alvos, 0 pendentes, 0 sobras, 0 erros de JS, sem rolagem lateral, 3523 / 6599; `page_check` e `diff.py` das seções 1–3 sem diferença nova (ficam só o botão do topo −0,92 de largura, métrica da fonte, e o nó raiz "Tabs" do celular, estrutural). As etapas não usam `data-reveal` (têm entrada própria: `.is-armed` / `.is-in` / `.is-entering`).

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

Animações (2026-10-18, biblioteca Motion — atributos no partial e no template PHP):

| Seção | Entrada |
|-------|---------|
| 1 topo | ao carregar: círculos em pop lento (1,6 s, `sobre-01-hero.css` — a mesma animação vale em `.is-in` e `.is-done`, então segue até o fim depois que o motion.js troca a classe), título por linhas (200 ms), texto sobe (500 ms); na rolagem os círculos descem até 24 px (`data-parallax="0.12"`). Os círculos centralizam com `margin-left: -960px` (não `transform`), para o pop crescer do centro |
| 2 por que | título por linhas, parágrafos sobem (grupo, +150 ms); foto com cortina para cima + assentamento só se abrir abaixo da dobra (`sobre-02-porque.js` põe o `data-wipe`); visível ao abrir (1440×900: topo em 602; 375: em 402) fica parada |
| 3 três coisas | título por linhas; lista em grupo a cada 75 ms: número sobe → marcador cresce de cima → título → texto, item a item (~1,7 s) |
| 4 audita | título por linhas, parágrafos sobem (grupo, +150 ms) |
| 5 equipe A | título por linhas, texto sobe; cada card é um grupo (+0/120/240/360 ms): foto (`.sobre-card__media`) com cortina para cima e assentamento, nome (+350) e cargo (+450) sobem; nota em fade. Sem hover |
| 6 equipe B | só título por linhas, texto (+150) e nota sobem; fotos e bio continuam com o `sobre-06-socios.js` |
| 7 onde | forma em pop; título por linhas, texto (+150) e botão (+250) sobem; mapa com cortina para cima; faixa de traços abre da esquerda (+300) |

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

### Contato — motion (2026-10-18, biblioteca global, atributos no partial e no template)

| Elemento | Atributos | Início |
|----------|-----------|--------|
| Título | `data-split="lines"` | ao carregar |
| Texto | `data-reveal="up"` · atraso 120 | ao carregar |
| Seta | `data-reveal="grow-y" data-origin="top"` (cresce para baixo, em direção ao formulário) · atraso 300 | ao carregar |
| Formulário | `data-reveal="up"` no `<form>` inteiro, nada por campo · sem atraso (era 200; desde que o topo espera as fontes, cada ms a mais é campo escondido) | ao carregar |
| Ilustração | `data-reveal="fade"` · atraso 150 | ao carregar |
| Contatos | `data-reveal-group="up"` no `.contato-info` (os 3 em sequência) | ao entrar na tela (no celular ficam embaixo) |
| E-mail e WhatsApp | `data-hover="line"` num `<span>` dentro do link, para o sublinhado ter a largura do texto e não a do link (232/343, como no Figma); `contato-01-form.css` acende o sublinhado com o cursor em qualquer ponto do link | hover |
| Faixa | `data-wipe="right"` | ao entrar na tela |

Validação (2026-10-18): `motion_check` 1440 e 375 com 0 pendentes, 0 sobras, sem rolagem lateral e sem erro de JS (92 alvos); `page_check` e `diff.py` iguais aos de antes (Δ 0); CLS ≈ 0. Campos escondidos depois da 1ª pintura (primeiro acesso, headless, 375): 0,1–1,6 s conforme a carga da máquina — quase tudo a espera global até o `motion.js` (defer) rodar e as fontes ficarem prontas (no máx. 1 s depois dele), que vale para todo item `data-motion-start="load"` do site; o atraso do formulário foi zerado por isso.

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

Motion (2026-10-18, biblioteca global — MODEL.md › Motion; atributos no partial e no template PHP):
- topo, ao carregar: `.seo-hero__article` é `data-reveal-group data-motion-start="load"` (h1 `data-split`, texto e os dois botões `up`, em sequência); o navegador `data-reveal="rise"` + `data-motion-start="load"` + `data-motion-delay="300"` (sem fade: candidato a LCP, visível desde o 1º quadro); a pintura do fundo `data-parallax` (padrão 0,12, até 24 px; o `overflow: hidden` da seção corta o que desce).
- "O problema": `.seo-problema__inner` é grupo — título `data-split`, parágrafos `up`.
- "Duas formas de contratar": desktop — `.seo-formas__left` é grupo (título `data-split`, as duas `.seo-opt` `up`); `.seo-formas__media` `data-wipe="up"` (uma vez, no contêiner; a troca das imagens e a barra de progresso seguem iguais). O corpo oculto da opção (`hidden`) não tem reveal. Celular — cada `.seo-fm` `up`. Os elementos do layout escondido (`display: none`) ficam prontos na hora (o motion.js os dá como já passados).
- Validação: `motion_check` 1440 e 375 com 0 pendentes, 0 sobras, sem rolagem lateral e sem erro de JS; `page_check` e `diff.py` iguais aos de antes do motion.
- Achado aqui e corrigido na biblioteca (b007725): no celular, do carregamento até o status do CTA ser digitado, a página ficava com 1399 px de largura — o `.m-sr` (absolute) do `data-type` escapava do `overflow: clip`. Agora `[data-type], [data-count] { position: relative; }` no motion.css, e o `motion_check` mede também a largura logo após o carregamento.

## MktOps — mapa (desktop `8348:4181` 1440×5137 · mobile `8695:3268` 375×7468,08)

Estático: `mktops/index.html` (`src/pages.json` → página `mktops`, `assets: 10-cta`). WP: página de slug `mktops` → `page-mktops.php` → `template-parts/mktops/*.php`. O link "MktOps" do rodapé aponta para cá.

| # | Seção | Desktop | Mobile | Partial / template | Status |
|---|-------|---------|--------|--------------------|--------|
| 0 | Header (o da Home) | `8348:4182` | `8695:4281` | `partials/header.html` | ✅ |
| 1 | topo: título com trecho cinza, 2 botões, diagrama MktOps (Skills, MCP, Pipelines, Infra → marca; SVG inline em camadas, vertical no mobile, animado) | `8425:3827` (864) | `8695:3271` (1500,08) | `mktops-01-hero` (+ `mktops-01-diagrama`) · `01-hero.php` (+ `svg/diagrama.php`) | ✅ |
| 2 | "O problema" + foto | `8425:4174` (966) | `8695:3456` (671) | `mktops-02-problema` · `02-problema.php` | ✅ |
| 3 | "O que é instalado": 5 itens com arquivo (1 grande + 2×2) e faixa da comunidade com ilustração cortada (SVG inline, animada) | `8502:4262` (911) | `8695:3463` (1506) | `mktops-03-instalado` (+ `mktops-03-comunidade`) · `03-instalado.php` (+ `svg/comunidade.php`) | ✅ |
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
| Imagens | arquivos do tema | `assets/images/mktops` | padrão do chão do diagrama (`hero-padrao.svg`), foto do problema, arquivos, desenhos dos planos, traços verdes. O diagrama e a ilustração da comunidade são SVG inline: `partials/mktops-01-diagrama.html` e `partials/mktops-03-comunidade.html`, com cópia igual (sem os `data-figma`) em `template-parts/mktops/svg/*.php` — mexeu num, copie no outro |

Validação (2026-10-16): 1440×5137 e 375×7468,08 = Figma (Δ 0 em todos os blocos); gates das 5 seções com 0 diferenças, salvo larguras de texto ≤ 0,95 px (nome dos planos, selo). Template PHP renderizado com o tema = mesmo texto e classes do estático. 768–1023: planos empilhados (em 3 colunas o desenho cobria nome e selo).

### MktOps — animações (pedido do cliente, 2026-10-17 · Motion em 2026-10-18)

Repouso = Figma: sem JS, com `prefers-reduced-motion` e fora dos loops fica o quadro do Figma em todas. Sem efeito de hover: arquivos e planos não são link (o "vem para frente" dos arquivos e dos desenhos dos planos saiu em 2026-10-18 — "Regra de hover"). Loops com `data-loop` (motion.js): rodam enquanto a parte está na tela (pedido do cliente, 2026-10-18). Entradas repetem: quando a parte sai da tela por inteiro volta ao início (fora da tela, sem piscar) e toca de novo na volta — as do motion.js e as próprias (etiquetas do diagrama, planos).

| Onde | O quê | Como |
|------|-------|------|
| Topo — texto | ao carregar: título por linhas, texto sobe (+150 ms), botões sobem (+200/+260 ms), 0,7 s cada → termina em ~1 s | `data-split` / `data-reveal` / `data-reveal-group="up"` com `data-motion-start="load"`; durações em `mktops-01-hero.css` |
| Topo — diagrama | etiquetas entram uma por vez (fila de 0,45 s) quando a pasta chega na tela; se o título está entrando, depois dele; resetam quando a pasta sai da tela. Enquanto a figura está na tela, o ciclo de 5 s se repete (`data-loop` → `motion:loop-start/stop`): luz sai de Skills e de MCP (contorno da pasta acende) → no MktOps o contorno acende e um traço verde contorna a borda (Skills por cima, MCP por baixo; no celular pelas laterais) → luz segue para Pipelines (de Skills) e Infra (de MCP), que acendem na chegada; as pastas flutuam (5 s, defasadas; vertical no desktop, horizontal no celular) | `mktops-01-hero.css/.js`, `mktops-01-diagrama.html` |
| O problema | título por linhas, parágrafos sobem, foto abre de baixo para cima e assenta | `data-split`, `data-reveal-group="up"`, `data-wipe="up" data-wipe-zoom` (o `<picture>` é block) |
| O que é instalado | título por linhas; itens um a um (arquivo em pop, texto sobe); texto da faixa sobe | `data-split`, `data-reveal-group` + `data-reveal="pop"/"up"` |
| Comunidade | ilustração fixa; enquanto a faixa está na tela cada peça tem uma luz verde correndo pelas arestas (cópia `.mk-com__run` de cada path, mesma velocidade, início diferente), que apaga quando a faixa sai | `data-loop` na faixa; `mktops-03-instalado.css/.js`, `mktops-03-comunidade.html` |
| Os três níveis | título por linhas (motion.js); quando o plano chega na tela: nome sobe, selo "Popular" em pop, desenho sobe 16 dentro do cabeçalho; depois as linhas — rótulo e valor sobem 10, linha a linha e plano a plano (o fundo não se mexe); o `.is-in` sai quando o plano sai da tela por inteiro | `data-split`; `.is-armed`/`.is-in` em `mktops-04-niveis.css/.js` |
| Garantias | automático do motion.js (CTA) | — |

Validação (2026-10-18): `motion_check` 1440 e 375 com 0 pendentes, 0 sobras e 0 erros de JS; `diff.py` (movimento reduzido) topo, problema e instalado com 0 diferenças (desktop e mobile), níveis só com as larguras de texto de sempre; `page_check` 1440 e 375 com Δ 0 em todos os blocos.

## Regra de hover (2026-10-18, pedido do cliente)

Efeito de passar o mouse que mexe no elemento (crescer, subir, zoom, sombra, texto que avança) só em link ou botão: em card ou texto que não é clicável dá a impressão de link. Removidos: cards dos sistemas e do diagrama e números das stats (Home), textos do painel e cards "o que não fazemos" (Serviços). Mantidos: links, cards do Blog (o card inteiro é link), abas de Serviços, setas e pausa dos Estudos. Animações de imagem, de entrada e de rolagem continuam.

## Motion (2026-10-18) — biblioteca global

Arquivos: `assets/js/motion.js` + `assets/css/motion.css` (carregados em todas as páginas: `src/index.template.html` e `functions.php`; o `<head>` liga `html.motion-on` antes da 1ª pintura). Referência: técnicas da galeria Animation do Awwwards (plano em scratchpad/motion-plan.txt). Princípio: em repouso, sem JS, com movimento reduzido e na impressão a página é o Figma; toda entrada termina com `.is-done` e sem spans auxiliares. Repetição (2026-10-18): a entrada volta ao estado inicial quando o elemento sai da tela por inteiro e toca de novo quando ele volta; os loops rodam enquanto estão na tela.

| Atributo | Efeito |
|---|---|
| `data-reveal="up\|diag\|fade\|pop\|rise\|grow-x\|grow-y"` (+ `data-origin`) | entra uma vez ao chegar na tela |
| `data-split="lines"` | título por linhas (máscara por palavra; quebras do repouso medidas antes e fixadas; HTML original volta no fim) |
| `data-wipe="right\|left\|up\|down\|out"` (+ `data-wipe-zoom`) | recorte que abre (clip-path) e imagem que assenta 1,06→1 |
| `data-count` | número conta de 0 ao valor (formato BR; aria-hidden + valor real para leitor de tela) |
| `data-type` / `data-type-group` | texto digitado (barra de status) |
| `data-parallax="0.12"` (+ `data-parallax-max="24"`) | desloca na rolagem; 0 com a página no topo |
| `data-loop` (+ `data-loop-max`) | `.is-looping` enquanto está na tela e a aba visível — para sempre (pedido do cliente, 2026-10-18; sem botão de pausa, decisão dele); `data-loop-max="N"` limita a N s por entrada |
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
