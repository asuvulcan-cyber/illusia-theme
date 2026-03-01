# Changelog

All notable changes to Illusia Theme will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

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
