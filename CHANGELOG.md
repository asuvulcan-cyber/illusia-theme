# Changelog

All notable changes to Illusia Theme will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

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
