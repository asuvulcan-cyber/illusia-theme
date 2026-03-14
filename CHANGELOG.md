# Changelog

All notable changes to Illusia Theme will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

## [1.12.3] - 2026-03-13

### Changed
- **Template: TCG** redesenhado com flip card front/back — frente minimalista (imagem + nome + bio), verso com detalhes (aparência, personalidade, relacionamentos, notas, hierarquia)
- Animação 3D CSS com `perspective` + `rotateY` e botão "Virar Carta"
- Corner ornaments duplicados em ambas as faces da carta
- Wrappers `__card`, `__front`, `__back` transparentes (`display: contents`) para templates não-TCG

### Technical
- HTML structure: novo `__card > __front + __back` wrapper + `__flip-btn` na `_character-sheet.php`
- CSS: `perspective: 1200px`, `transform-style: preserve-3d`, `backface-visibility: hidden`, grid stacking
- JS: `initTcgFlip()` toggle de classe `--flipped` no `illusia-char-gallery.js`
- Reduced motion: desabilita transição do card flip

## [1.12.2] - 2026-03-13

### Added
- **Carousel**: Main image + gallery merged into auto-sliding carousel with random start, prev/next nav, dots
- **Lightbox**: Full-screen image viewer with keyboard navigation (Escape, Arrow keys), backdrop blur
- **Children tree**: Personagem pages now show hierarchical children tree (e.g., subordinates, members)
- **Template selector**: Admin dropdown to choose display template (Padrão, Compacto, Wiki, TCG, RPG)
- **Template: Compacto** — single column, retrato 80px, espaçamento reduzido, max-width 560px
- **Template: Wiki** — infobox flutuante à direita estilo Wikipedia, headings serif, seções com border-bottom
- **Template: TCG** — carta colecionável centralizada 400px, borda decorativa amber com glow, corner ornaments, painéis
- **Template: RPG** — ficha de mesa com borda dupla, headings bold com underline, stat entries com dotted dividers
- **Type-colored badges**: Distinct colors for Obra (amber), Local (teal), Organização (violet), Personagem (crimson) in breadcrumb and index
- **Type indicator dots**: Colored dot before each tree node matching its type

### Changed
- Removed max-width limitation on character index and sheet (was 680px/720px)
- Breadcrumb badges now use type-specific colors instead of monochrome
- Index tree connectors thickened from 1px to 2px for better visibility
- Gallery items now open in lightbox instead of new tab
- Carousel replaces static portrait when multiple images exist

### Technical
- New file: `js/illusia-char-gallery.js` — vanilla JS carousel + lightbox
- New meta: `illusia_char_template` with sanitize callback and helper
- Updated `illusia_get_char_meta()` to include `template` key
- Front-end JS conditionally enqueued on `fcn_character` taxonomy pages
- CSS type color tokens scoped via custom properties (`--type-obra`, `--type-local`, etc.)
- 4 template variant CSS sections (Compacto, Wiki, TCG, RPG) with light mode + responsive support

## [1.12.1] - 2026-03-13

### Added
- Character Sheet System — sistema completo de fichas de personagem para taxonomia `fcn_character`
- `illusia_char_type` term meta — campo select (obra/local/organização/personagem) define o tipo do termo na hierarquia
- `illusia_char_image` — URL de imagem principal para qualquer tipo
- `illusia_char_full_name`, `illusia_char_titles` — nome completo e títulos/epítetos (personagem only)
- `illusia_char_appearance`, `illusia_char_personality`, `illusia_char_notes` — textareas descritivos (personagem only)
- `illusia_char_relationships` — repeater de relacionamentos com AJAX autocomplete e drag-and-drop para reordenação
- `illusia_char_gallery` — galeria de imagens adicionais armazenada como JSON de URLs (personagem only)
- `illusia_char_former_orgs` — organizações anteriores com busca AJAX filtrada por tipo organização (personagem only)
- `illusia_char_creator` — ownership tracking: auto-salva criador, autores editam/deletam apenas seus termos
- `map_meta_cap` filter (priority 10000) — restringe edit_term/delete_term por ownership para autores
- `pre_get_terms` filter — filtra lista admin para mostrar apenas termos do autor (scoped a edit-tags screen)
- Admin UI: form fields no add/edit, colunas Tipo e Criador, campo de ownership (editors+)
- Front-end routing por tipo: personagem → character sheet, obra/local/org → índice hierárquico em árvore
- Tax cloud filtrado para exibir apenas termos tipo personagem
- CSS component `illusia-character-sheet.css` — breadcrumb, ficha, galeria, índice em árvore, layout duas colunas
- Layout responsivo: 640px (colunas empilham), 480px (identidade vertical, breadcrumb vertical, tree compacto)
- Light mode e reduced motion suportados
- `includes/illusia-character-meta.php` — constantes, registro de meta, sanitização, helpers, tree builder/renderer
- `includes/illusia-character-admin.php` — admin UI, save handlers, AJAX search com filtro de tipo
- `includes/illusia-character-caps.php` — capabilities, ownership, cap mapping, admin list filtering
- `partials/_character-sheet.php` — ficha completa com breadcrumb, retrato, galeria, bio, aparência/personalidade (duas colunas), relacionamentos/orgs anteriores (duas colunas), notas, aparições
- `partials/_character-index.php` — índice hierárquico com badges inline e linhas conectoras CSS
- `js/illusia-char-admin.js` — type toggle, image preview, relationship repeater com drag-and-drop, galeria, former orgs
- `css/admin/illusia-char-admin.css` — estilos admin com badges de tipo, drag handle, galeria grid, tags de orgs

## [1.11.8] - 2026-03-09

### Fixed
- 6 templates de taxonomia — Contador de resultados no header corrigido: substituído `$term->count` (conta apenas posts diretos do termo) por `$wp_query->found_posts` (total real da query, incluindo posts de termos filhos)

## [1.11.7] - 2026-03-09

### Fixed
- 6 templates de taxonomia — Links do tax cloud corrigidos: `wp_generate_tag_cloud()` usa `$tag->link` para o `href`, com fallback para `#` se ausente; `wp_tag_cloud()` seta via `get_term_link()` automaticamente, mas a chamada direta não; adicionado `foreach` com `get_term_link()` antes de `wp_generate_tag_cloud()`

## [1.11.6] - 2026-03-09

### Changed
- 6 templates de taxonomia — Labels da overline traduzidos para PT-BR: Genre → Gênero, Fandom → Nacionalidade, Character → Personagem, Content Warning → Aviso de Conteúdo, Category → Categoria

## [1.11.5] - 2026-03-09

### Fixed
- 6 templates de taxonomia — Ordenação do tax cloud corrigida: `wp_generate_tag_cloud()` re-ordena internamente por nome (ignora ordem do array); adicionado `orderby => count` + `order => DESC` explícito na chamada de `wp_generate_tag_cloud()`

## [1.11.4] - 2026-03-09

### Changed
- 6 templates de taxonomia — Tax cloud ordenado por popularidade (`orderby => count`, `order => DESC`); termos mais populares aparecem primeiro, especialmente relevante com o collapse de 2-3 linhas

### Fixed
- 6 templates de taxonomia — Tax cloud reescrito: `wp_tag_cloud()` substituído por `get_terms()` + filtro explícito `count > 0` + `wp_generate_tag_cloud()`; abordagem anterior com `hide_empty` não filtrava termos vazios efetivamente

## [1.11.2] - 2026-03-08

### Fixed
- `js/illusia-archive-cloud.js` — Detecção de overflow corrigida: agora aplica `._overflowing` primeiro para ativar o `max-height`, depois compara `scrollHeight > clientHeight`; antes verificava sem constraint (sempre iguais, toggle nunca aparecia)
- `css/components/illusia-archives.css` — max-height reduzido de 10rem para 6rem (~2-3 linhas de pills); mask fade ajustado de 2.5rem para 1.5rem
- 6 templates de taxonomia — `hide_empty => true` adicionado ao `wp_tag_cloud()` para excluir termos com 0 itens

## [1.11.1] - 2026-03-08

### Added
- `js/illusia-archive-cloud.js` — Script de progressive enhancement para tax cloud colapsável: detecta overflow do container, adiciona classe `._overflowing` com max-height + mask-image fade, botão toggle "Ver todos" / "Recolher" com chevron CSS animado, scroll-into-view ao recolher

### Changed
- `css/components/illusia-archives.css` — Tax cloud refatorado: layout flex movido para `.illusia-archive__cloud-items` wrapper; estados `._overflowing` (max-height 10rem + mask gradient) e `._expanded`; botão toggle mono com chevron rotativo; light mode e reduced motion para toggle
- 6 templates de taxonomia — Cloud envolvido em `<div class="illusia-archive__cloud-items">`, botão toggle adicionado com `hidden` (revelado pelo JS)
- `functions.php` — Enqueue condicional de `illusia-archive-cloud.js` em taxonomy archives; versão bumped para 1.11.1

## [1.11.0] - 2026-03-08

### Added
- `chapters.php` — Template override da página /chapters/ do Fictioneer; query logic espelhada do pai com markup Illusia; sort UI do pai preservada via chamada direta a `fictioneer_sort_order_filter_interface`; layout single column para cards; paginação estilizada
- `recommendations.php` — Template override da página /recommendations/ do Fictioneer; mesma estrutura que chapters
- `partials/_archive-loop.php` — Override do archive loop do Fictioneer; cards tipados via `fictioneer_echo_card()` com staggered entry animation; paginação Illusia; sort UI preservada via hook `fictioneer_archive_loop_before`
- `taxonomy-fcn_genre.php` — Archive de gêneros com header semântico (overline teal), counter, descrição, divider diamante, tax cloud reimaginado como fichas de referência cruzada
- `taxonomy-fcn_fandom.php` — Archive de fandoms (overline violet), mesma estrutura
- `taxonomy-fcn_character.php` — Archive de personagens (overline amber), mesma estrutura
- `taxonomy-fcn_content_warning.php` — Archive de avisos de conteúdo (overline crimson), mesma estrutura
- `category.php` — Archive de categorias (overline neutra, com parent hierárquico)
- `tag.php` — Archive de tags (overline neutra)
- `css/components/illusia-list-pages.css` — CSS base unificado para todas as list pages e archives:
  - Page header com overline mono e shimmer line âmbar
  - Sort UI "Observatory Instrument Panel": micro-label, botões glass, popups blur
  - Card list flex column com staggered fade-in (`calc(--i * 60ms)`, até 8)
  - Empty state "Silent Observatory" com borda dashed e tipografia mono
  - Paginação "Observatory Page Dial" com mono numbers e amber glow no current
  - Light mode, responsive (900px, 640px, 400px), reduced motion
- `css/components/illusia-archives.css` — CSS "The Catalog Index" para taxonomy archives:
  - Header com overline semântica colorida por taxonomia (teal/violet/amber/crimson/neutral)
  - Título em Playfair, parent term, counter badge com borda semântica
  - Descrição editorial em Syne italic
  - Divider diamante com linhas convergentes na cor da taxonomia
  - Tax cloud reimaginado: pills mono uniformes com barra lateral `scaleY` no hover (regra #8), cores semânticas por taxonomia
  - Light mode com variantes -dim, responsive, reduced motion

### Changed
- `functions.php` — Enqueue condicional de `illusia-list-pages.css` (chapters + recommendations + archives) e `illusia-archives.css` (apenas taxonomy archives); versão bumped para 1.11.0
- `style.css` — Versão bumped para 1.11.0

## [1.10.4] - 2026-03-08

### Fixed
- `css/illusia-page-style-frame.css` — Alvo migrado de `.main__wrapper` para `.main__background` (mesmo elemento usado pelos page styles nativos do Fictioneer como Chamfered, Wave, etc.); pseudo-elementos `::before`/`::after` agora no target correto
- `css/illusia-page-style-frame.css` — Cores amber no corner ornament agora usam `hsl(var(--amber-free) / .2)` em vez de `rgba()` hardcoded; light mode usa `--amber-dim-free` e `--illusia-border-base`
- `functions.php` — Versão bumped para 1.10.4

## [1.10.1] - 2026-03-08

### Added
- `css/illusia-page-style-frame.css` — Page style "Illusia Frame" para o Customizer:
  - Moldura decorativa sutil (`::before`) com borda arredondada em `.main__background`
  - Ornamento de canto âmbar (`::after`) no topo esquerdo
  - Light mode com opacidades reduzidas
  - Responsive ≤400px: frame removida (muito apertado)
- `functions.php` — Filtro `fictioneer_filter_customizer_page_style` para registrar opção "Illusia Frame" no dropdown de Page Style do Customizer

### Changed
- `css/components/illusia-stories.css` — Moldura decorativa (seção 1: `::before`/`::after`) removida; agora fornecida globalmente pelo page style `illusia-page-style-frame.css`
- `functions.php` — Enqueue de `illusia-page-style-frame.css` (global, sempre carregado); versão bumped para 1.10.1

## [1.10.0] - 2026-03-08

### Changed
- `stories.php` — Adicionado overline monospace "Observatory Archive" acima do título; adicionado divider decorativo com diamante entre header e stats
- `css/components/illusia-stories.css` — Redesign "Catalog Room":
  - Moldura decorativa sutil (`::before`) envolvendo o conteúdo com borda arredondada
  - Ornamento de canto âmbar (`::after`) no topo esquerdo
  - Overline monospace com letter-spacing .3em e cor amber-dim
  - Divider com linhas gradient convergentes e diamante central (6px rotacionado 45°)
  - Stats como fichas de catálogo unidas (flex em vez de grid), bordas arredondadas apenas nas pontas (primeiro/último)
  - Sort com border-bottom separator entre controles e card list
  - Mobile ≤640px: stats 2×2 wrap com radius nos 4 cantos, padding reduzido
  - Small ≤400px: frame removida (muito apertado), padding mínimo
  - Light mode atualizado para frame, overline, divider, diamond

### Added
- `docs/stories-page-ideas.html` — Design guide com 5 conceitos exploratórios para a página /stories/ (Catalog Room, Observatory Broadcast, Manuscript Archive, Gallery Wall, Chronicle Ledger)

## [1.9.0] - 2026-03-07

### Added
- `collections.php` — Template override da página /collections/ do Fictioneer; query logic espelhada do pai com markup Illusia; sort UI do pai preservada via chamada direta a `fictioneer_sort_order_filter_interface`; layout single column para cards; paginação estilizada
- `css/components/illusia-collections.css` — Estilos da página /collections/:
  - Header editorial com shimmer line âmbar sob o título
  - Sort UI na paleta Observatory Panel: micro-label "ORDENAR" em Fira Code, botões com borda/fundo escuro/hover âmbar, popups com backdrop blur e tipografia Illusia
  - Card list flex column com staggered fade-in (`calc(--i * 60ms)` por card, até 8)
  - Empty state Observatory com borda dashed, fundo glass, tipografia mono uppercase
  - Paginação com mono numbers e hover/current âmbar
  - Light mode completo (header shimmer, sort, pagination)
  - Reduced motion (cards, pagination)
  - Responsive: tablet ≤900px, mobile ≤640px, small ≤400px

### Changed
- `functions.php` — Enqueue condicional `illusia-collections` CSS (só em `is_page_template('collections.php')`); versão bumped para 1.9.0

## [1.8.1] - 2026-03-07

### Fixed
- `partials/_card-chapter.php` — Segurança: `esc_html()` em `$text_icon` (XSS), `esc_attr()` em `$post_id` nos atributos `id`/`class`/`data-*`
- `partials/_card-collection.php` — Segurança: `esc_attr()` em `$post_id` nos atributos HTML
- `partials/_card-recommendation.php` — Segurança: `esc_attr()` em `$post_id`; adicionado `nofollow` em `rel` dos links externos
- `partials/_card-chapter.php` — Status da história principal ("Ongoing") agora traduzido via `fcntr()` para PT-BR

### Changed
- `partials/_card-chapter.php` — Labels traduzidos para PT-BR: "Parent Story" → "História Principal", aria-label "Chapter stats" → "Estatísticas do capítulo"
- `partials/_card-collection.php` — Labels traduzidos para PT-BR: "Featured" → "Destaques", aria-label "Collection stats" → "Estatísticas da coleção"

## [1.8.0] - 2026-03-07

### Added
- `partials/_card-chapter.php` — Template override do Chapter Card com markup Illusia: cover com fallback triplo (chapter thumb > story thumb > text_icon), rating ribbon herdável do story, 2 stat cells (words + comments), seção "História Principal" com link ao story pai e status traduzido, Stimulus controller preservado (`fictioneer-large-card`), card controls via `fictioneer_get_card_controls()`
- `partials/_card-collection.php` — Template override do Collection Card: cover com 4 stat cells (stories, caps, words, comments) via `Stats::get_collection_statistics()`, lista "Destaques" com até 3 featured items de tipos mistos (Story/Chapter/Collection), sem Stimulus controller, sem card controls, sem author
- `partials/_card-recommendation.php` — Template override do Recommendation Card: cover sem stats, autor via meta field `fictioneer_recommendation_author` (não WP author), lista de links externos com `rel="noopener nofollow"` e `target="_blank"`, lógica `$display_text` (maior entre `$one_sentence` e `$excerpt`), sem footer (`_no-footer`)
- `css/components/illusia-cards.css` — Seção 18: Chapter Card variant — cover stats compact (grid 1×2), text-icon cover, story-unpublished opacity, parent-story-row com amber sidebar bar hover
- `css/components/illusia-cards.css` — Seção 19: Collection Card variant — featured-list com padrão chapter-list (border, hover, amber bar), type labels mono
- `css/components/illusia-cards.css` — Seção 20: Recommendation Card variant — `--no-footer` display none, links-list com cores amber-dim
- `css/components/illusia-cards.css` — Focus-visible para `.illusia-featured-list__link`, `.illusia-links-list__link`, `.illusia-card__parent-story-link`
- `css/components/illusia-cards.css` — Light mode para parent-story-row, featured-list, links-list
- `css/components/illusia-cards.css` — Mobile (≤640px): parent-story-right hidden, featured-list date/separator hidden, collection footer grid
- `css/components/illusia-cards.css` — Seção 21: Reduced Motion atualizada com todos os novos elementos animados

## [1.7.0] - 2026-03-07

### Fixed
- `stories.php` — Paginação quebrada: variável `$page` era sobrescrita para 1 pelo `setup_postdata()` do WordPress (global reservada para paginação `<!--nextpage-->`); renomeada para `$current_page` em todo o template para evitar colisão com a global do WP

### Changed
- `css/components/illusia-cards.css` — Ícones de status agora são todos standalone (sem círculo) para consistência visual: Completed `fa-circle-check` → `fa-trophy`, Hiatus `fa-circle-pause` → `fa-pause`, Canceled `fa-ban` → `fa-xmark`

## [1.6.0] - 2026-03-07

### Added
- `stories.php` — Template override da página /stories/ do Fictioneer; query logic espelhada do pai com markup Illusia; stats panel "Observatory Instruments" com valores em Playfair Display âmbar (`--text-lg`), labels em Fira Code (`--text-2xs`) uppercase; sort UI do pai preservada via chamada direta a `fictioneer_sort_order_filter_interface`; layout single column para cards; paginação estilizada
- `css/components/illusia-stories.css` — Estilos da página /stories/:
  - Header editorial com shimmer line âmbar sob o título
  - Stats panel compacto 4×1 (2×2 mobile) com gap 1px, shimmer line, valores âmbar, cells com fundo escuro
  - Sort UI na paleta Observatory Panel: micro-label "ORDENAR" em Fira Code, botões com borda/fundo escuro/hover âmbar, popups com backdrop blur e tipografia Illusia
  - Card list flex column com staggered fade-in (`calc(--i * 60ms)` por card, até 8)
  - Empty state Observatory com borda dashed, fundo glass, tipografia mono uppercase
  - Paginação com mono numbers e hover/current âmbar
  - Light mode completo (header shimmer, stat values `--amber-dim`, sort, pagination)
  - Reduced motion (stats, cards, pagination)
  - Responsive: tablet ≤900px, mobile ≤640px (stats 2×2), small ≤400px
- `css/illusia-properties.css` — Tokens `--text-3xs` e `--space-3xs` adicionados à escala fluida

### Changed
- `functions.php` — Enqueue condicional `illusia-stories` CSS (só em `is_page_template('stories.php')`)
- `css/components/illusia-cards.css` — Cover stats overflow fix (`min-width: 0`, `width: 100%`, `text-overflow: ellipsis`); label font-size migrado de `clamp()` hardcoded para `var(--text-3xs)`; removidos fallbacks hardcoded `.6rem`; ícones de status customizados: Ongoing `fa-circle` → `fa-pen-nib`, Oneshot `fa-circle-check` → `fa-bolt`

## [1.5.12] - 2026-03-04

### Fixed
- `partials/_card-story.php` — Segurança: escape de outputs (`esc_html` em título, contagens, título de capítulo; `wp_kses` em ícone de status; `wp_kses_post` em excerpt); corrigido bug de precedência de operador em `$hide_author`; substituído `current_time('timestamp')` deprecado por `time()`
- `css/components/illusia-cards.css` — Substituídos font-sizes hardcoded (`.6rem`/`.65rem`/`.7rem`) por tokens (`--text-2xs`/`--text-3xs`); substituído `--fs-em-xxs` (token do parent) por `--text-xs`; removido `gap: 0` redundante e `border-radius` duplicado; adicionados estilos `:focus-visible` em links e botões interativos; ampliado clamp do stat-label; removido `-webkit-line-clamp: 2` duplicado no mobile

## [1.5.11] - 2026-03-04

### Changed
- `css/components/illusia-cards.css` — Footer removido no desktop para cards com cover (info vive nos stats + ribbon); rating badge migrou para ribbon na capa (posicionado no canto superior direito com backdrop-blur, variantes por rating); grid reduzido para 3 rows (header/middle/tax); taxonomies com padding-bottom aumentado (último row visível); mobile: footer restaurado, ribbon e stats hidden
- `partials/_card-story.php` — Rating ribbon (`illusia-card__rating-ribbon`) adicionado dentro do cover-link com variantes por rating (everyone/teen/mature/adult)

## [1.5.10] - 2026-03-04

### Fixed
- `css/components/illusia-cards.css` — Cover frame: `justify-content: space-between` empurra stats para o fundo do frame (elimina espaço vazio); footer items duplicados nos stats (caps, palavras, comentários, status) hidden no desktop via `--in-stats`; itens restaurados no mobile onde stats panel é hidden
- `partials/_card-story.php` — Classes `--in-stats` nos footer items chapters/words/comments e status badge

## [1.5.9] - 2026-03-02

### Changed
- `css/components/illusia-cards.css` — Redesenho "Observatory Panel": cover vive em moldura própria (padding, fundo escuro, border-radius) com grid de mini stats abaixo (caps, palavras, comments, status); cover-w aumentado para `clamp(110px, 18vw, 180px)`; cover-link com border-radius `--r-md`; label "Recent Chapters" acima da chapter list; cores de status no stat-cell; mobile: stats hidden (migram pro footer), cover frame compacto
- `partials/_card-story.php` — Cover frame com stats panel (chapter_count, word_count_short, comment_count, status icon+label); label "Recent Chapters" acima da chapter list

## [1.5.8] - 2026-03-02

### Changed
- `css/components/illusia-cards.css` — Desktop: cover-link usa `aspect-ratio: 3/4` fixo em vez de `height: 100%` (imagem proporcional no topo, não estica como banner lateral); cover div continua `grid-row: 1/-1` como separador visual

## [1.5.7] - 2026-03-01

### Changed
- `css/components/illusia-cards.css` — Mobile: cover com `aspect-ratio: 3/4` (imagem aparece inteira); grid row 2 como `1fr` (absorve espaço extra da cover, elimina gap); header `align-self: end`; no-cover excerpt re-aplica `-webkit-box-orient: vertical` e `line-clamp: 4` (fix leak ao toggle display); desktop: aspect-ratio corrigido de 2/3 para 3/4

## [1.5.6] - 2026-03-01

### Changed
- `css/components/illusia-cards.css` — Mobile: redesenho do trecho cover + header + excerpt; cover agora usa height content-driven (sem aspect-ratio forçado, elimina gap vazio); cover-img com position absolute + object-fit cover para preencher qualquer altura; header com `align-self: end` para proximidade natural ao excerpt; título mobile com 2 linhas (clamp 1 apenas em ≤400px); excerpt inline com 3 linhas; desktop: aspect-ratio corrigido de 2/3 para 3/4

## [1.5.5] - 2026-03-01

### Changed
- `css/components/illusia-badges.css` — Tag pills inline (`._inline`) agora têm estilo pill completo (font-size, padding, border-radius, border, white-space) em vez de apenas texto colorido
- `css/components/illusia-cards.css` — Rating badge adult: fundo escuro com borda/texto claro, padrão outline; popup menu toggle: cor `--fg-400` e borda `--border-1` (mais visível); popup menu dropdown: glass bg com blur, borda, shadow e hover amber nos itens; mobile: cover com aspect-ratio 2/3, título 1 linha (--text-sm), header/excerpt mais compactos

## [1.5.1] - 2026-03-01

### Changed
- `partials/_card-story.php` — Removido autor duplicado do footer (já visível no header); datas do footer e chapter list agora usam `human_time_diff()` para formato relativo ("há 3 dias")
- `css/components/illusia-cards.css` — Excerpt limitado a 2 linhas; popup menu toggle refinado (glass bg, transições suaves, estado `:active`); tag pills com `white-space: nowrap` forçado; rating badges redesenhados (everyone/teen/mature como outline sutil, adult com bg escuro e borda/texto claro); light mode rating badges adicionados; mobile completamente reescrito (proporções compactas, tipografia reduzida, chapter list e taxonomias ajustados, variante no-cover tratada)

## [1.5.0] - 2026-03-01

### Added
- `partials/_card-story.php` — Template override completo do Story Card do Fictioneer com markup original Illusia: estrutura semântica (`<article>`, `<header>`, `<footer>`), classes BEM `.illusia-card*`, grid CSS 2 colunas (cover + conteúdo)
- `css/components/illusia-cards.css` — Estilos do story card: glass gradient bg, shimmer line, amber hover, chapter list com barra lateral animada (scaleY), taxonomias em linha com fade mask, footer com meta items mono, badges de status/rating por cor semântica
- Variantes: `--sticky` (borda/bg âmbar, radial gradient corner), `--no-cover` (coluna única + barra lateral âmbar 3px), `--no-tax`
- Responsivo mobile (≤640px): cover spana header+excerpt, capítulos/taxa/footer full-width, excerpt inline ao lado da capa
- Light mode com acentos `-dim` para WCAG, reduced motion support
- Staggered entry animation (`illusia-card-in`) com delays incrementais

### Changed
- `functions.php` — Adicionado enqueue de `illusia-cards.css` (depende de `illusia-properties` + `illusia-badges`); versão bumped para 1.5.0

## [1.4.5] - 2026-03-01

### Added
- `css/components/illusia-badges.css` — Watermark hashtag icon para tags genéricas (`._tag`, `._taxonomy-post_tag`); padding-left aumentado (1.7em) nas tag pills para acomodar ícones

## [1.4.4] - 2026-03-01

### Changed
- `css/components/illusia-badges.css` — Watermark icons agora usam fill na cor da taxonomia (amber, teal, crimson, violet) em vez de branco; ícones cobrem 100% da altura do pill com fade suave da esquerda para direita

## [1.4.3] - 2026-03-01

### Added
- `css/components/illusia-badges.css` — Watermark icons nas tag pills de taxonomia: ícones decorativos sutis no fundo (character→pessoa, genre→livro, warning→triângulo, fandom→globo/bandeira), fade da esquerda para direita via `mask-image`, bandeiras simplificadas por slug (brasileira, moçambicana, chinesa, coreana, japonesa), ajustes de opacidade para light mode

## [1.4.2] - 2026-03-01

### Fixed
- `css/illusia-properties.css` — Border base invertida no light mode: `--illusia-border-base` de 91% L (creme claro, invisível em fundo branco) para 15% L (tom escuro); `--border-amber` e `--border-active` com opacidades aumentadas (.3/.55) para visibilidade

## [1.4.1] - 2026-03-01

### Fixed
- `css/components/illusia-buttons.css` — Tokens bridge (`--bg-*`/`--fg-*`) em vez de `--void-*`/`--ink-*` para compatibilidade light mode; seção light mode com acentos `-dim` para contraste WCAG 4.5:1 (CTA texto escuro, tabs/pagination amber-dim, focus outline amber-dim)
- `css/components/illusia-badges.css` — Tokens bridge para light mode; borda default `--border-1` nas tag pills (contorno visível); seção light mode com cores de taxonomia `-dim` (teal-dim, violet-dim, amber-dim, crimson-dim), opacidades aumentadas em bordas/backgrounds, rating labels com texto escuro em fundo sólido

## [1.4.0] - 2026-03-01

### Added
- `css/components/illusia-buttons.css` — Redesenho visual de botões: custom properties para 4 variantes (CTA âmbar, outline, ghost, danger), tipografia Syne uppercase, border-radius `--r-md`, motion com `--ease-expo`, focus-visible âmbar, tabs Fira Code mono, pagination estilizada
- `css/components/illusia-badges.css` — Redesenho visual de badges e tags: custom properties para tag-pill (block + inline + secondary + warning), cores semânticas por taxonomia (genre→teal, fandom→violet, character→amber, warning→crimson), comment badges mono, rating labels (E→sage, T→amber, M→crimson), fade mask para listas single-line

### Changed
- `functions.php` — Enqueue de `illusia-buttons.css` e `illusia-badges.css` com dependência `illusia-properties`

## [1.3.0] - 2026-02-28

### Removed
- `includes/illusia-atmosphere.php` — Arquivo removido; orbs de luz ambiente descartados (efeito visual não atingiu o objetivo de "luz ambiente")
- `css/illusia-atmosphere.css` — Seção de orbs removida (CSS, keyframes, stacking context do `.main__wrapper`)
- `functions.php` — Removidos require do includes, hook `illusia_register_atmosphere_hooks` e seção INCLUDES

### Changed
- `css/illusia-atmosphere.css` — Grain noise opacity ajustada para .30 (equilíbrio entre presença e sutileza)

## [1.2.2] - 2026-02-28

### Fixed
- `css/illusia-atmosphere.css` — Orbs z-index de 0 para 1 (ficavam atrás do stacking context do #site); opacidade dos gradientes aumentada de .07/.05 para .13/.10 para visibilidade sutil

## [1.2.1] - 2026-02-28

### Fixed
- `css/illusia-atmosphere.css` — Grain noise reduzido de opacity .45 para .08 com mix-blend-mode overlay; removida opacidade duplicada no SVG rect

## [1.2.0] - 2026-02-28

### Added
- `css/illusia-atmosphere.css` — Camada atmosférica global: grain noise overlay (`body::before`), orbs de luz ambiente (amber + violet), scrollbar customizada (4px, thumb âmbar)
- `includes/illusia-atmosphere.php` — Hook PHP para injetar orbs no body via `fictioneer_body`
- `css/illusia-properties.css` — Token `--ff-mono` ('Fira Code', monospace) adicionado à seção de famílias tipográficas

### Changed
- `docs/DESIGN-DIRECTION.md` — Sincronizado com illusia-properties.css (fonte de verdade): void expandido de 6→11 stops, ink expandido de 5→10 stops + tinted + inverted, saturação ink corrigida de 9-51%→2-8%, glass corrigido para usar bridge `--bg-900-free`, variáveis de controle agora documentam ponte com Fictioneer computed vars
- `functions.php` — Adicionados require de includes, enqueue do illusia-atmosphere.css, e registro do hook de orbs via `after_setup_theme`

## [1.1.5] - 2026-02-28

### Changed
- `functions.php` — Removido enqueue manual de Google Fonts (Playfair Display + Syne); fontes agora gerenciadas via painel Fictioneer > Fonts > Google Fonts

## [1.1.4] - 2026-02-28

### Changed
- `functions.php` — Removido inline CSS override; fontes, cores do título e layout agora controlados inteiramente pelo Customizer do Fictioneer
- `css/illusia-properties.css` — Seção 2 (Control Variables) removida do CSS; variáveis delegadas ao Customizer

## [1.1.3] - 2026-02-28

### Fixed
- `functions.php` — Overrides de identidade visual (fontes, cores do título) movidos para inline CSS com prioridade 10000, resolvendo conflito com `customize.css` do Fictioneer (prioridade 9999); variáveis de layout/sizing permanecem controláveis pelo Customizer

## [1.1.2] - 2026-02-28

### Added
- `functions.php` — Enqueue de Google Fonts (Playfair Display + Syne) como dependência do illusia-properties.css

### Changed
- `css/illusia-properties.css` — Saturação dos tokens ink reduzida de 9–40% para 2–8%; texto quase neutro com warmth sutil, eliminando fadiga visual em leitura prolongada

## [1.1.1] - 2026-02-28

### Fixed
- `css/illusia-properties.css` — Removidos overrides de background de componentes secundários (tabs, pagination, buttons, badges, inputs, tags) que quebravam o par fundo/texto no light mode; Fictioneer gerencia esses pares e a ponte bg→void garante o hue Illusia

## [1.1.0] - 2026-02-28

### Added
- `docs/STANDARDS.md` — Padrão Ouro: regras PHP, segurança, CSS, JS, a11y, versionamento
- `docs/DESIGN-DIRECTION.md` — Direção artística completa com sistema dinâmico de cores HSL
- `css/illusia-properties.css` — Override integral das custom properties do Fictioneer (v5.34.4)
- `docs/IMPLEMENTATION-GUIDE.md` — Roteiro operacional para redesenho de componentes (Fase 0–3 + checklist)

### Fixed
- `css/illusia-properties.css` — Variáveis de controle reconectadas ao sistema de offsets do Fictioneer; modal de hue-rotate/darken/saturation volta a funcionar
- `css/illusia-properties.css` — Legibilidade de texto restaurada: overrides manuais de cor (ink-9 a 49% L) removidos; Fictioneer fg-* resolve via mapeamento fg→ink com contraste testado (+9–22% lightness em tabs, pagination, buttons, code, navigation)

### Changed
- `functions.php` — Enqueue de `illusia-properties.css` com prioridade 99
- Sistema de cores do design direction migrado de hex estático para HSL dinâmico com variáveis de controle (`--illusia-hue-offset`, `--illusia-saturation`, `--illusia-darken`, `--illusia-font-saturation`, `--illusia-font-lightness`)
- `docs/STANDARDS.md` — Seção de versionamento expandida com regras SemVer completas (MAJOR/MINOR/PATCH)
- `css/illusia-properties.css` — Design Direction aplicado: todos os tokens Illusia definidos e variáveis Fictioneer mapeadas
  - Variáveis de controle Illusia (`--illusia-hue-offset`, `--illusia-saturation`, `--illusia-darken`, etc.)
  - Matizes base por família (void 270°, ink 35°, amber 37°, teal 170°, crimson 0°, violet 260°, sage 134°)
  - Void colors (fundos) com triplets `-free` + `hsl()` — 11 stops (void-0 a void-10, 4%–80% lightness), paridade 1:1 com Fictioneer bg-950→bg-50
  - Ink colors (textos) com `font-saturation` / `font-lightness` — 10 stops + tinted + inverted (ink-0 a ink-9, 93%–49% lightness), paridade 1:1 com Fictioneer fg-100→fg-950
  - Mapeamento bg-*/fg-* simplificado para referências diretas a void/ink tokens (sem fórmulas duplicadas)
  - ~125 referências semânticas remapeadas para os novos índices void/ink preservando hierarquia visual
  - Accent colors (amber, teal, crimson, violet, sage) com triplets `-free` e variantes dim
  - Glows, halos, bordas semânticas (border-0 a border-active), glass tokens
  - Espaçamento (`--space-*`), tipografia (`--text-*`, `--ff-display/ui/mono`), raio de borda (`--r-*`)
  - Motion (`--ease-expo/std`, `--t-fast/mid/slow`) e layout (`--max-w`)
  - Fictioneer `--bg-*` remapeado para void hue (270°), `--fg-*` para ink hue (35°)
  - Fictioneer `--primary-*` → amber, `--green-*` → sage, `--red-*` → crimson
  - Font families: `--ff-base` → Syne, `--ff-heading` → Playfair Display, `--ff-mono` → Fira Code
  - Zero hex/rgba estático nas variáveis sobrescritas — tudo via HSL dinâmico ou referência a token
- `css/illusia-properties.css` — Slimizado de 1.653 → ~590 linhas (−64%)
  - Removida Seção 7 (Rule Blocks) — 523 linhas de regras CSS estruturais que o tema pai já provê
  - Removidos ~200 overrides redundantes idênticos ao Fictioneer (shadows, font weights/sizes, popup/tooltip/comment/button props)
  - Removidos overrides manuais de cor de texto — fg→ink mapping (Seção 4) já faz o trabalho
  - Seção 6 (Light Mode) reescrita: 260→65 linhas, hue 210° (azul Fictioneer) → 270° void / 35° ink com sat/lightness original
  - Seção 2 (Control Variables) limpa: removidos computed vars e props idênticos ao Fictioneer
  - Seção 5 (Scoped Blocks) limpa: removidos WP preset font sizes e font weight selectors idênticos
  - Propriedades de componentes padronizadas: `--void-*` → `--bg-*`, `--ink-*` → `--fg-*` (respondem ao modal Fictioneer)

## [1.0.3] - 2026-02-25
### Changed
- Renamed theme from "Fictioneer Child Theme" to "Illusia Theme"
- Updated all function names from `fictioneer_child_*` to `illusia_*`
- Added AsuVN as contributor
- Updated theme metadata and description

### Added
- CHANGELOG.md documentation
- GitHub repository tracking (illusia-theme)

## [1.0.0] - 2023-01-01
### Added
- Initial release based on Fictioneer Child Theme by Tetrakern
