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
