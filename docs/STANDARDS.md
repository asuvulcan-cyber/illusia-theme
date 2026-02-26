# Padrão Ouro — Illusia Theme
> Última modificação: 2026-02-25

Regras de qualidade obrigatórias para todo código do tema filho **Illusia**. Este documento é a referência final — nenhum código entra no projeto sem seguir estes padrões.

---

## Sumário

1. [Estrutura de Diretórios](#1-estrutura-de-diretórios)
2. [PHP — Regras de Código](#2-php--regras-de-código)
3. [Segurança — Regras Invioláveis](#3-segurança--regras-invioláveis)
4. [CSS — Regras de Estilo](#4-css--regras-de-estilo)
5. [JavaScript — Regras](#5-javascript--regras)
6. [Integração com Tema Pai](#6-integração-com-tema-pai)
7. [Performance](#7-performance)
8. [Acessibilidade (WCAG)](#8-acessibilidade-wcag)
9. [Commits e Versionamento](#9-commits-e-versionamento)

---

## 1. Estrutura de Diretórios

```
illusia-theme/
├── css/                  # Estilos próprios do Illusia
│   └── illusia-{feature}.css
├── js/                   # Scripts próprios
│   └── illusia-{feature}.js
├── includes/             # Funções PHP organizadas por domínio
│   └── illusia-{dominio}.php
├── partials/             # Template parts sobrescritos ou novos
│   └── partial-{nome}.php
├── fonts/                # Fontes customizadas
├── img/                  # Imagens do tema (não de conteúdo)
├── docs/                 # Documentação do projeto
│   └── STANDARDS.md
├── style.css             # Header do tema (metadados apenas)
├── functions.php         # Ponto de entrada — carrega includes
├── CHANGELOG.md          # Histórico de mudanças
├── LICENSE               # GPL v3
└── README.md             # Visão geral do projeto
```

### Convenção de nomes de arquivos

| Tipo | Padrão | Exemplo |
|---|---|---|
| Include PHP | `illusia-{dominio}.php` | `illusia-hooks.php` |
| Template part | `partial-{nome}.php` | `partial-hero.php` |
| Template page | `{template-name}.php` | `single-fcn_story.php` |
| CSS | `illusia-{feature}.css` | `illusia-story.css` |
| JS | `illusia-{feature}.js` | `illusia-lightbox.js` |

---

## 2. PHP — Regras de Código

### Prefixos

- Funções: `illusia_`
- Constantes: `ILLUSIA_`
- Classes (se usadas): namespace `Illusia\`
- Hooks customizados: `illusia/`

### Indentação e formatação

**2 espaços** — consistente com o Fictioneer. Sem tabs.

```php
// ✅ Correto
function illusia_get_hero_data( int $post_id ): array {
  $data = get_post_meta( $post_id, 'illusia_hero', true );

  if ( empty( $data ) ) {
    return [];
  }

  return $data;
}

// ❌ Errado — 4 espaços, sem type hints
function illusia_get_hero_data($post_id) {
    $data = get_post_meta($post_id, 'illusia_hero', true);
    if (empty($data)) {
        return array();
    }
    return $data;
}
```

### Espaços em parênteses

Seguir o padrão WordPress/Fictioneer — espaços internos em parênteses de controle e funções:

```php
// ✅ Correto
if ( $condition ) {
  do_something( $arg1, $arg2 );
}

// ❌ Errado
if ($condition) {
  do_something($arg1, $arg2);
}
```

### Type hints obrigatórios

Todos os parâmetros e retornos devem ter type hints:

```php
// ✅ Correto
function illusia_format_title( string $title, int $max_length = 100 ): string {
  return mb_substr( $title, 0, $max_length );
}

// ❌ Errado — sem type hints
function illusia_format_title( $title, $max_length = 100 ) {
  return mb_substr( $title, 0, $max_length );
}
```

### Null coalescing

Preferir `??` sobre ternários com `isset()`:

```php
// ✅ Correto
$value = $_GET['key'] ?? 'default';
$nested = $args['config']['option'] ?? null;

// ❌ Errado
$value = isset( $_GET['key'] ) ? $_GET['key'] : 'default';
```

### PHPDoc obrigatório

Toda função deve ter bloco PHPDoc com `@since`, `@param` e `@return`:

```php
/**
 * Retorna os dados do hero de um post.
 *
 * @since 1.0.0
 *
 * @param int    $post_id  ID do post.
 * @param string $size     Tamanho da imagem. Default 'full'.
 *
 * @return array{url: string, alt: string} Dados do hero.
 */
function illusia_get_hero( int $post_id, string $size = 'full' ): array {
  // ...
}
```

### Sem tag de fechamento

Arquivos PHP puros **nunca** terminam com `?>`:

```php
// ✅ Correto — arquivo termina aqui
add_action( 'init', 'illusia_setup' );

// ❌ Errado
add_action( 'init', 'illusia_setup' );
?>
```

### PHP mínimo: 7.4

Features permitidas:
- Type hints (parâmetros e retorno)
- Null coalescing (`??`)
- Null coalescing assignment (`??=`)
- Arrow functions (`fn() =>`)
- Typed properties em classes
- Spread operator (`...`)

Features **não permitidas** (PHP 8.0+):
- Named arguments
- Match expressions
- Union types (`int|string`)
- Nullsafe operator (`?->`)

> **Nota**: O Fictioneer usa algumas features PHP 8.0+ mas nós mantemos compatibilidade com 7.4 para máxima portabilidade. Se o requisito mínimo do pai mudar, revisaremos.

---

## 3. Segurança — Regras Invioláveis

Estas regras **não têm exceção**. Todo código que toca input do usuário ou gera output deve seguir estas regras.

### Sanitização de input

**Nunca** usar `$_GET`, `$_POST`, `$_REQUEST` ou `$_COOKIE` sem sanitizar:

```php
// ✅ Correto
$search = sanitize_text_field( $_GET['s'] ?? '' );
$page = absint( $_GET['paged'] ?? 1 );
$key = sanitize_key( $_POST['action'] ?? '' );
$email = sanitize_email( $_POST['email'] ?? '' );
$url = esc_url_raw( $_POST['website'] ?? '' );

// ❌ Errado — input cru
$search = $_GET['s'];
$page = $_GET['paged'];
```

### Tabela de sanitização

| Tipo de dado | Função |
|---|---|
| Texto genérico | `sanitize_text_field()` |
| Texto com HTML | `wp_kses_post()` |
| Número inteiro positivo | `absint()` |
| Número inteiro (pode ser negativo) | `intval()` |
| Slug/chave | `sanitize_key()` |
| Email | `sanitize_email()` |
| URL | `esc_url_raw()` |
| Nome de arquivo | `sanitize_file_name()` |
| Classe CSS | `sanitize_html_class()` |

### Escape de output

**Todo** dado dinâmico no HTML deve ser escapado:

```php
// ✅ Correto
<h1><?php echo esc_html( $title ); ?></h1>
<a href="<?php echo esc_url( $link ); ?>">Link</a>
<input value="<?php echo esc_attr( $value ); ?>">
<div><?php echo wp_kses_post( $content ); ?></div>

// ❌ Errado — output sem escape
<h1><?php echo $title; ?></h1>
<a href="<?php echo $link; ?>">Link</a>
```

### Tabela de escape

| Contexto | Função |
|---|---|
| Texto em HTML | `esc_html()` |
| Atributo HTML | `esc_attr()` |
| URL | `esc_url()` |
| HTML seguro (posts) | `wp_kses_post()` |
| JavaScript inline | `wp_json_encode()` |
| Tradução + escape | `esc_html__()`, `esc_attr__()` |

### Nonces

Obrigatório em todo formulário e requisição AJAX:

```php
// No formulário:
wp_nonce_field( 'illusia_save_settings', 'illusia_nonce' );

// Na verificação:
if ( ! wp_verify_nonce( $_POST['illusia_nonce'] ?? '', 'illusia_save_settings' ) ) {
  wp_die( 'Verificação de segurança falhou.' );
}

// Em AJAX:
check_ajax_referer( 'illusia_ajax_action', 'nonce' );
```

### Capability checks

Verificar permissões antes de qualquer operação sensível:

```php
// ✅ Correto
function illusia_save_settings(): void {
  if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Acesso negado.' );
  }

  // ... salvar configurações
}

// ❌ Errado — sem verificação de capability
function illusia_save_settings(): void {
  update_option( 'illusia_config', $_POST['config'] );
}
```

### SQL seguro

Sempre usar `$wpdb->prepare()` para queries customizadas:

```php
// ✅ Correto
global $wpdb;
$results = $wpdb->get_results(
  $wpdb->prepare(
    "SELECT * FROM {$wpdb->posts} WHERE post_author = %d AND post_status = %s",
    $author_id,
    'publish'
  )
);

// ❌ Errado — SQL injection
$results = $wpdb->get_results(
  "SELECT * FROM {$wpdb->posts} WHERE post_author = {$author_id}"
);
```

---

## 4. CSS — Regras de Estilo

### Metodologia: BEM modificado

Consistente com o Fictioneer. Modificadores usam prefixo `_` (underscore):

```css
/* Bloco */
.illusia-card { }

/* Elemento */
.illusia-card__title { }
.illusia-card__image { }

/* Modificador — prefixo underscore */
.illusia-card._featured { }
.illusia-card__title._large { }
```

```html
<!-- Uso no HTML -->
<div class="illusia-card _featured">
  <h2 class="illusia-card__title _large">Título</h2>
  <img class="illusia-card__image" src="..." alt="...">
</div>
```

### Prefixo CSS

Todos os blocos próprios do Illusia usam `.illusia-`:

```css
/* ✅ Correto */
.illusia-hero { }
.illusia-sidebar { }

/* ❌ Errado — sem prefixo, pode colidir com o pai */
.hero { }
.custom-sidebar { }
```

### Custom Properties

Preferir variáveis CSS sobre valores hardcoded. Definir no `:root` do Illusia:

```css
/* ✅ Correto */
:root {
  --illusia-color-primary: hsl(220, 60%, 50%);
  --illusia-spacing-md: 1.5rem;
  --illusia-radius: 8px;
}

.illusia-card {
  background: var(--illusia-color-primary);
  padding: var(--illusia-spacing-md);
  border-radius: var(--illusia-radius);
}

/* ❌ Errado — valores soltos */
.illusia-card {
  background: hsl(220, 60%, 50%);
  padding: 1.5rem;
  border-radius: 8px;
}
```

### Responsividade

Mobile-first com `clamp()` para fluid sizing:

```css
/* ✅ Correto — fluid, sem media queries */
.illusia-hero__title {
  font-size: clamp(1.5rem, 3vw + 1rem, 3rem);
  padding: clamp(1rem, 5vw, 3rem);
}

/* ❌ Evitar — cascata de media queries para tamanhos */
.illusia-hero__title {
  font-size: 1.5rem;
}
@media (min-width: 768px) {
  .illusia-hero__title {
    font-size: 2.5rem;
  }
}
```

### Sobrescrita do pai

Nunca editar CSS compilado do Fictioneer. Sobrescrever via cascata com especificidade adequada:

```css
/* ✅ Correto — sobrescreve via cascata (carregado com prioridade 99) */
.story__title {
  font-family: var(--illusia-font-heading);
}

/* ❌ Errado — !important */
.story__title {
  font-family: "Comic Sans" !important;
}
```

> `!important` só é permitido em casos extremos documentados com comentário explicando o motivo.

---

## 5. JavaScript — Regras

### Vanilla JS apenas

Sem jQuery. O Fictioneer não usa e nós também não:

```js
// ✅ Correto — Vanilla JS com helpers do Fictioneer
const element = _$('.illusia-card');
const elements = _$$('.illusia-card');
const byId = _$$$('illusia-hero');

// ❌ Errado — jQuery
$('.illusia-card').fadeIn();
```

### Utilitários do Fictioneer

Aproveitar os shorthands e utils existentes:

| Shorthand | Equivale a |
|---|---|
| `_$()` | `document.querySelector()` |
| `_$$()` | `document.querySelectorAll()` |
| `_$$$()` | `document.getElementById()` |
| `FcnUtils` | Objeto com funções utilitárias |
| `FcnGlobals` | Variáveis globais do tema |

### Prefixos

```js
// Funções globais: illusia_
function illusia_initLightbox() { }

// Objetos/namespace: Illusia
const Illusia = {
  lightbox: { init() { } },
  utils: { formatDate() { } }
};
```

### Stimulus para componentes interativos

Consistente com o Fictioneer (5.27.0+):

```js
// Registrar controller
window.FictioneerApp.Controllers.illusiaModal = class extends Stimulus.Controller {
  static targets = ['content', 'backdrop'];

  open() {
    this.contentTarget.classList.add('_open');
  }

  close() {
    this.contentTarget.classList.remove('_open');
  }
};
```

### Defer obrigatório

Scripts sempre carregados com `defer`:

```php
// ✅ Correto
wp_register_script(
  'illusia-script',
  get_stylesheet_directory_uri() . '/js/illusia-main.js',
  ['fictioneer-application-scripts'],
  CHILD_VERSION,
  array( 'strategy' => 'defer' )
);
```

---

## 6. Integração com Tema Pai

### Hierarquia de abordagens

Sempre seguir esta ordem de preferência:

1. **Hooks** (`add_action`, `add_filter`, `remove_action`) — preferido
2. **Sobrescrita de template** (copiar para o child theme) — quando hooks não bastam
3. **Substituição de função** (se o pai usa `function_exists()`) — raro

### Hooks primeiro

```php
// ✅ Correto — usar hooks do Fictioneer
function illusia_custom_header( string $html, int $post_id ): string {
  return '<div class="illusia-header">' . $html . '</div>';
}
add_filter( 'fictioneer_filter_header_image', 'illusia_custom_header', 10, 2 );

// Remover funcionalidade do pai
function illusia_customize_parent(): void {
  remove_action( 'wp_head', 'fictioneer_add_fiction_css', 10 );
}
add_action( 'init', 'illusia_customize_parent' );
```

### Sobrescrita de templates

Quando copiar um template do pai, **sempre documentar o motivo**:

```php
<?php
/**
 * Sobrescrita: single-fcn_story.php
 *
 * Motivo: Layout completamente redesenhado para o Illusia.
 * Original: fictioneer/single-fcn_story.php
 *
 * @since 1.0.0
 * @package Illusia
 */
```

### Prioridades

```php
// Frontend: prioridade 99+ (após o pai)
add_action( 'wp_enqueue_scripts', 'illusia_enqueue_styles', 99 );

// Init: prioridade padrão (10) é OK
add_action( 'init', 'illusia_customize_parent' );

// Se init não funcionar, usar wp com prioridade 11+
add_action( 'wp', 'illusia_late_customize', 11 );
```

### Dependências de assets

```php
// CSS sempre depende do pai
wp_enqueue_style(
  'illusia-style',
  get_stylesheet_directory_uri() . '/css/illusia-style.css',
  ['fictioneer-application'], // dependência do pai
  CHILD_VERSION
);

// JS sempre depende do pai
wp_register_script(
  'illusia-script',
  get_stylesheet_directory_uri() . '/js/illusia-main.js',
  ['fictioneer-application-scripts'], // dependência do pai
  CHILD_VERSION,
  array( 'strategy' => 'defer' )
);
```

---

## 7. Performance

### Enqueue condicional

Só carregar assets nas páginas que precisam:

```php
// ✅ Correto — só carrega na página de story
function illusia_enqueue_story_assets(): void {
  if ( ! is_singular( 'fcn_story' ) ) {
    return;
  }

  wp_enqueue_style( 'illusia-story', get_stylesheet_directory_uri() . '/css/illusia-story.css', ['fictioneer-application'], CHILD_VERSION );
}
add_action( 'wp_enqueue_scripts', 'illusia_enqueue_story_assets', 99 );

// ❌ Errado — carrega em todas as páginas
function illusia_enqueue_all(): void {
  wp_enqueue_style( 'illusia-story', '...' );
  wp_enqueue_style( 'illusia-chapter', '...' );
  wp_enqueue_style( 'illusia-home', '...' );
}
```

### Imagens

```html
<!-- ✅ Correto -->
<img src="hero.webp" alt="Descrição da imagem" loading="lazy" decoding="async" width="800" height="400">

<!-- ❌ Errado — sem lazy, sem dimensões -->
<img src="hero.png" alt="">
```

### Queries

```php
// ✅ Correto — cache com transient para dados pesados
function illusia_get_featured_stories(): array {
  $cached = get_transient( 'illusia_featured_stories' );

  if ( $cached !== false ) {
    return $cached;
  }

  $stories = get_posts( [
    'post_type' => 'fcn_story',
    'posts_per_page' => 6,
    'meta_key' => 'fictioneer_story_sticky',
    'meta_value' => '1',
    'no_found_rows' => true, // Não precisa de paginação? Desabilite a contagem
  ] );

  set_transient( 'illusia_featured_stories', $stories, HOUR_IN_SECONDS );

  return $stories;
}

// ❌ Errado — rand() não é cacheável
$stories = get_posts( [
  'orderby' => 'rand',
  'posts_per_page' => 6,
] );
```

### no_found_rows

Sempre usar `'no_found_rows' => true` em queries que não precisam de paginação. Isso elimina uma query `SQL_CALC_FOUND_ROWS` desnecessária.

---

## 8. Acessibilidade (WCAG)

### ARIA landmarks e roles

```html
<!-- ✅ Correto — semântica + ARIA -->
<nav aria-label="Navegação principal">
  <ul role="list">
    <li><a href="/">Home</a></li>
  </ul>
</nav>

<main id="main" role="main">
  <article aria-labelledby="post-title">
    <h1 id="post-title">Título</h1>
  </article>
</main>

<!-- ❌ Errado — divs genéricos sem semântica -->
<div class="nav">
  <div class="nav-item"><a href="/">Home</a></div>
</div>
```

### Contraste

| Tipo | Ratio mínimo |
|---|---|
| Texto normal (< 18px) | 4.5:1 |
| Texto grande (>= 18px ou 14px bold) | 3:1 |
| Elementos de UI e gráficos | 3:1 |

### Navegação por teclado

```html
<!-- ✅ Correto — botão acessível -->
<button type="button" class="illusia-modal__close" aria-label="Fechar modal">
  <svg aria-hidden="true">...</svg>
</button>

<!-- ❌ Errado — div como botão -->
<div class="close-btn" onclick="closeModal()">X</div>
```

### Focus visible

Nunca remover o outline de foco sem substituir por alternativa visível:

```css
/* ✅ Correto — estiliza o foco */
.illusia-button:focus-visible {
  outline: 2px solid var(--illusia-color-primary);
  outline-offset: 2px;
}

/* ❌ Errado — remove foco sem alternativa */
.illusia-button:focus {
  outline: none;
}
```

### Alt text

```html
<!-- Imagem informativa: alt descritivo -->
<img src="cover.webp" alt="Capa do livro A Última Fronteira">

<!-- Imagem decorativa: alt vazio + aria-hidden -->
<img src="divider.svg" alt="" aria-hidden="true">
```

### Tags semânticas

| Use | Em vez de |
|---|---|
| `<nav>` | `<div class="nav">` |
| `<main>` | `<div class="main">` |
| `<article>` | `<div class="post">` |
| `<aside>` | `<div class="sidebar">` |
| `<header>` | `<div class="header">` |
| `<footer>` | `<div class="footer">` |
| `<section>` | `<div class="section">` |
| `<button>` | `<div onclick="">` |
| `<time>` | `<span class="date">` |

---

## 9. Commits e Versionamento

### Formato de commits

```
tipo: descrição curta em inglês

Corpo opcional explicando o "porquê"
```

### Tipos de commit

| Tipo | Uso |
|---|---|
| `feat` | Nova funcionalidade |
| `fix` | Correção de bug |
| `style` | Mudanças visuais (CSS, layout) |
| `refactor` | Refatoração sem mudar comportamento |
| `docs` | Documentação |
| `chore` | Manutenção, configs, dependências |
| `perf` | Melhoria de performance |
| `a11y` | Melhoria de acessibilidade |

### Exemplos

```
feat: add story hero section with dynamic cover
fix: correct chapter navigation z-index overlap
style: update card hover animation timing
docs: add CSS naming conventions to STANDARDS
perf: cache featured stories query with transient
a11y: add aria-labels to modal close buttons
```

### Versionamento (SemVer)

Formato: `MAJOR.MINOR.PATCH` — baseado em [Semantic Versioning](https://semver.org/).

- `style.css` → campo `Version:`
- `functions.php` → constante `CHILD_VERSION`
- **Sempre sincronizados** — nunca um com versão diferente do outro

```php
// functions.php
define( 'CHILD_VERSION', '1.1.0' );
```

```css
/* style.css */
Version: 1.1.0
```

#### Quando incrementar cada casa

| Casa | Formato | Quando incrementar | Exemplos |
|------|---------|-------------------|----------|
| **PATCH** `x.x.X` | `1.0.3 → 1.0.4` | Correções de bug, ajustes visuais pontuais, typos, hotfixes que **não adicionam** funcionalidade nem mudam comportamento existente | Fix z-index, corrigir cor errada, ajustar padding |
| **MINOR** `x.X.0` | `1.0.4 → 1.1.0` | Nova funcionalidade, redesenho de componente/página, novo partial, novo CSS/JS, qualquer adição que **não quebra** o que já existe | Redesenhar story card, adicionar hero section, novo modal |
| **MAJOR** `X.0.0` | `1.1.0 → 2.0.0` | Mudança que **quebra compatibilidade**: reestruturação de templates, renomear hooks/filtros públicos, alterar estrutura de dados, trocar sistema de design | Migrar de BEM para outra metodologia, reescrever functions.php |

#### Regras adicionais

- **PATCH reseta ao incrementar MINOR**: `1.0.4` → `1.1.0` (não `1.1.4`)
- **MINOR e PATCH resetam ao incrementar MAJOR**: `1.3.2` → `2.0.0`
- **Documentação sozinha (`docs:`) não incrementa versão** — apenas atualiza CHANGELOG em `[Unreleased]`
- **Refatoração interna (`refactor:`) que não muda comportamento** → PATCH
- **Redesenho visual completo de componente** → MINOR (é funcionalidade nova do frontend)
- **Múltiplos commits acumulados em `[Unreleased]`** → incrementar versão uma vez no momento da release, não a cada commit
- **Pre-release / dev**: enquanto acumulando mudanças, o CHANGELOG agrupa em `[Unreleased]`. Ao publicar, fecha a seção com versão e data

#### Fluxo prático

```
1. Trabalho acumula em [Unreleased] no CHANGELOG
2. Ao publicar/fazer deploy:
   a. Avaliar: só fixes? → PATCH. Componentes novos? → MINOR. Quebra algo? → MAJOR.
   b. Renomear [Unreleased] para [X.Y.Z] - YYYY-MM-DD
   c. Atualizar CHILD_VERSION e style.css Version
   d. Commit: "chore: release vX.Y.Z"
   e. Tag git: git tag vX.Y.Z
   f. Criar novo [Unreleased] vazio no CHANGELOG
```

### CHANGELOG.md

Atualizado a cada release com formato:

```markdown
## [1.1.0] - 2026-03-15
### Adicionado
- Seção hero na página de story
- Modal de lightbox para imagens

### Corrigido
- Z-index da navegação de capítulos

### Alterado
- Animação de hover nos cards
```

---

## Checklist Rápido

Antes de commitar qualquer código, verificar:

- [ ] Funções usam prefixo `illusia_`?
- [ ] Type hints em todos os parâmetros e retornos?
- [ ] PHPDoc em todas as funções?
- [ ] Inputs sanitizados?
- [ ] Outputs escapados?
- [ ] Nonces em formulários/AJAX?
- [ ] Capability checks em operações sensíveis?
- [ ] CSS usa prefixo `.illusia-`?
- [ ] JS é Vanilla (sem jQuery)?
- [ ] Assets carregados condicionalmente?
- [ ] Elementos interativos acessíveis por teclado?
- [ ] Imagens com `alt`, `loading="lazy"`, dimensões?
- [ ] `CHILD_VERSION` e `style.css` Version sincronizados?
