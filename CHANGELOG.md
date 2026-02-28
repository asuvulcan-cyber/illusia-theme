# Changelog

All notable changes to Illusia Theme will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

### Added
- `docs/STANDARDS.md` — Padrão Ouro: regras PHP, segurança, CSS, JS, a11y, versionamento
- `docs/DESIGN-DIRECTION.md` — Direção artística completa com sistema dinâmico de cores HSL
- `css/illusia-properties.css` — Override integral das custom properties do Fictioneer (v5.34.4)
- `docs/IMPLEMENTATION-GUIDE.md` — Roteiro operacional para redesenho de componentes (Fase 0–3 + checklist)

### Changed
- `functions.php` — Enqueue de `illusia-properties.css` com prioridade 99
- Sistema de cores do design direction migrado de hex estático para HSL dinâmico com variáveis de controle (`--illusia-hue-offset`, `--illusia-saturation`, `--illusia-darken`, `--illusia-font-saturation`, `--illusia-font-lightness`)
- `docs/STANDARDS.md` — Seção de versionamento expandida com regras SemVer completas (MAJOR/MINOR/PATCH)
- `css/illusia-properties.css` — Design Direction aplicado: todos os tokens Illusia definidos e variáveis Fictioneer mapeadas
  - Variáveis de controle Illusia (`--illusia-hue-offset`, `--illusia-saturation`, `--illusia-darken`, etc.)
  - Matizes base por família (void 270°, ink 35°, amber 37°, teal 170°, crimson 0°, violet 260°, sage 134°)
  - Void colors (fundos) com triplets `-free` + `hsl()` — 6 níveis de 3% a 17% lightness
  - Ink colors (textos) com `font-saturation` / `font-lightness` — 5 níveis de 13% a 92%
  - Accent colors (amber, teal, crimson, violet, sage) com triplets `-free` e variantes dim
  - Glows, halos, bordas semânticas (border-0 a border-active), glass tokens
  - Espaçamento (`--space-*`), tipografia (`--text-*`, `--ff-display/ui/mono`), raio de borda (`--r-*`)
  - Motion (`--ease-expo/std`, `--t-fast/mid/slow`) e layout (`--max-w`)
  - Fictioneer `--bg-*` remapeado para void hue (270°), `--fg-*` para ink hue (35°)
  - Fictioneer `--primary-*` → amber, `--green-*` → sage, `--red-*` → crimson
  - Font families: `--ff-base` → Syne, `--ff-heading` → Playfair Display, `--ff-mono` → Fira Code
  - ~200 variáveis Fictioneer com comentário indicando token Illusia correspondente
  - Zero hex/rgba estático nas variáveis sobrescritas — tudo via HSL dinâmico ou referência a token

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
