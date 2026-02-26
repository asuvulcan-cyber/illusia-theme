# Guia de Implementação — Illusia Theme
> Última modificação: 2026-02-26

Roteiro operacional obrigatório para **todo redesenho** de página, componente ou partial. Este é o **primeiro documento a ser lido** antes de qualquer tarefa de implementação.

O Illusia usa o Fictioneer **apenas como máquina** — lógica, hooks, dados, queries. Todo o frontend (HTML, CSS, JS) é **100% original**. Nunca copiar markup do pai; apenas consumir seus dados e funcionalidades.

---

## Sumário

1. [Fase 0 — Preparação](#fase-0--preparação)
2. [Fase 1 — Mapeamento](#fase-1--mapeamento)
3. [Fase 2 — Implementação](#fase-2--implementação)
4. [Fase 3 — Checklist Final](#fase-3--checklist-final)
5. [Exemplo de Fluxo](#exemplo-de-fluxo)

---

## Fase 0 — Preparação

**Objetivo**: Ler tudo o que é relevante antes de escrever uma única linha de código.

### Leitura obrigatória

| # | Arquivo | Por quê |
|---|---------|---------|
| 1 | `docs/STANDARDS.md` | Regras de código PHP, CSS, JS, segurança, a11y |
| 2 | `docs/DESIGN-DIRECTION.md` | Tokens dinâmicos, paleta HSL, componentes, motion, regras invioláveis |
| 3 | Templates do Fictioneer | Entender **o que** o pai renderiza e **quais dados** usa |
| 4 | CSS/JS do Fictioneer | Identificar classes, controllers Stimulus, event handlers |
| 5 | Estado atual do Illusia | Verificar se já existe override no tema filho |

### Como encontrar os arquivos do Fictioneer

```
Elemento a redesenhar: "Story Card"

1. Template principal:
   grep -r "story.*card\|card.*story" fictioneer/partials/ --include="*.php" -l

2. Hooks disponíveis:
   grep -r "do_action\|apply_filters" fictioneer/partials/partial-card-story.php

3. CSS relevante:
   grep -r "story-card\|\.card\b" fictioneer/css/ --include="*.css" -l

4. JS relevante:
   grep -r "story.*card\|card.*story" fictioneer/js/ --include="*.js" -l

5. Override existente no Illusia:
   ls illusia-theme/partials/ illusia-theme/css/ illusia-theme/js/
```

### Resultado da Fase 0

Ao final, você deve saber:
- Quais arquivos do pai estão envolvidos
- Que dados PHP estão disponíveis (variáveis, meta, queries)
- Que hooks/filters existem para interceptar
- Se já existe algo no Illusia que será afetado

---

## Fase 1 — Mapeamento

**Objetivo**: Documentar a "máquina" do Fictioneer antes de redesenhar o visual.

### O que mapear

| Item | Pergunta a responder |
|------|---------------------|
| **Template** | Qual arquivo do Fictioneer renderiza este elemento? |
| **Partial** | Quais partials são chamados dentro dele? |
| **Dados** | Quais variáveis PHP são passadas? (`$story`, `$post`, `get_post_meta()`, etc.) |
| **Hooks** | Quais `do_action()` e `apply_filters()` existem no contexto? |
| **Dependências** | Que outros componentes dependem deste? (ex: card usado em archive, search, shortcode) |
| **CSS/JS pai** | Classes CSS e controllers JS que o pai aplica |

### Decisão de abordagem

Escolher **uma** das opções (em ordem de preferência):

| Prioridade | Abordagem | Quando usar |
|-----------|-----------|-------------|
| 1 | **Hook/Filter** | O pai oferece `apply_filters` no dado ou `do_action` no local certo |
| 2 | **Template override** | Copiar o template para o tema filho e reescrever o HTML |
| 3 | **Partial novo** | O pai não oferece hooks e o template é muito acoplado |
| 4 | **Remove + Replace** | `remove_action()` do pai + `add_action()` com função própria |

> **Regra**: Sempre documentar no PHPDoc **por que** a abordagem foi escolhida.

### Resultado da Fase 1

Um mini-relatório (mental ou escrito) respondendo:
1. "O Fictioneer faz X via arquivo Y, usando dados Z"
2. "Vou interceptar via [hook/template/partial/replace] porque [razão]"
3. "Isso afeta [lista de locais onde o componente aparece]"

---

## Fase 2 — Implementação

**Objetivo**: Construir o frontend original do Illusia, consumindo a máquina do Fictioneer.

### Regras de execução

#### HTML
- Semântico: `<article>`, `<nav>`, `<section>`, `<figure>`, `<time>`, etc.
- Classes BEM com prefixo: `.illusia-story-card`, `.illusia-story-card__title`, `.illusia-story-card--sticky`
- Modificadores com `._`: `.illusia-story-card._featured`
- ARIA labels em elementos interativos
- `data-` attributes para JS, nunca classes CSS

#### CSS
- **Zero valores literais** — tudo via tokens `var(--...)`
- Cores via triplets `-free` / `hsl()` — nunca hex ou rgba estático
- Espaçamento: `--space-*`
- Tipografia: `--text-*`, `--ff-*`
- Raio: `--r-*`
- Motion: `--ease-*`, `--t-*`
- Glass: `--glass-*`
- Bordas: seguir progressão `--border-1` → `--border-2` → `--border-amber` → `--border-active`
- Responsivo com `clamp()`, breakpoints semânticos apenas quando necessário
- Sem `!important` (exceto override pontual e documentado do pai)

#### JS
- Vanilla JS + Stimulus para componentes interativos
- Sem jQuery
- Aproveitar utilitários do Fictioneer: `_$()`, `_$$()`, `_$$$()`, `FcnUtils`
- Sempre `defer` no enqueue
- Prefixo: `illusia_` para funções, `Illusia` para objetos/controllers

#### PHP
- Prefixo: `illusia_` para funções, `ILLUSIA_` para constantes
- Type hints obrigatórios em parâmetros e retorno
- PHPDoc com `@since`, `@param`, `@return`
- Sanitizar inputs: `sanitize_text_field()`, `absint()`, `sanitize_key()`
- Escapar outputs: `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()`
- Nonces em formulários e AJAX
- Capability checks antes de operações sensíveis
- Sem `?>` final
- PHP mínimo: 7.4

#### Enqueue
- CSS depende de `fictioneer-application`
- JS depende de `fictioneer-application-scripts`
- Prioridade `99+` para garantir cascata sobre o pai
- **Condicional**: só carregar onde o componente é usado

```php
/**
 * Enqueue story card styles only on archive pages.
 *
 * @since 1.1.0
 */
function illusia_enqueue_story_card_styles(): void {
  if ( ! is_archive() && ! is_home() ) {
    return;
  }

  wp_enqueue_style(
    'illusia-story-card',
    get_stylesheet_directory_uri() . '/css/components/illusia-story-card.css',
    ['fictioneer-application'],
    CHILD_VERSION
  );
}
add_action( 'wp_enqueue_scripts', 'illusia_enqueue_story_card_styles', 99 );
```

### Estrutura de arquivos

Novos arquivos seguem este padrão:

```
illusia-theme/
├── css/
│   ├── illusia-properties.css          ← Override de variáveis do pai
│   └── components/
│       └── illusia-story-card.css      ← CSS do componente
├── js/
│   └── controllers/
│       └── illusia-story-card.js       ← Controller Stimulus (se houver)
├── partials/
│   └── illusia-story-card.php          ← Partial com markup original
├── includes/
│   └── illusia-story-card-functions.php ← Lógica PHP do componente
└── functions.php                        ← Enqueues e includes
```

---

## Fase 3 — Checklist Final

**Copie este checklist para cada redesenho. Todos os itens devem ser marcados antes de concluir.**

### Design Tokens
- [ ] Todas as cores usam tokens dinâmicos HSL (zero hex/rgba estático)
- [ ] Espaçamento usa `--space-*` com `clamp()`
- [ ] Tipografia usa `--text-*` e famílias `--ff-*`
- [ ] Border-radius usa `--r-*`
- [ ] Motion usa `--ease-*` e `--t-*`
- [ ] Glass surfaces usam `--glass-*` tokens
- [ ] Shimmer line presente no topo de cards (quando aplicável)
- [ ] Progressão de bordas respeitada no hover

### Responsivo
- [ ] Funciona de 375px a 1440px sem quebras
- [ ] Layout fluido com `clamp()`, breakpoints apenas semânticos
- [ ] Testado nos breakpoints: 400px, 640px, 700px, 900px

### Acessibilidade
- [ ] Contraste >= 4.5:1 (texto normal) / >= 3:1 (texto grande)
- [ ] Focus visible em todos os elementos interativos
- [ ] Navegação por teclado: Tab / Enter / Esc funcionam
- [ ] ARIA labels em elementos que necessitam
- [ ] HTML semântico (tags corretas para cada contexto)

### Código
- [ ] PHP: prefixo `illusia_`, type hints, PHPDoc, sem `?>` final
- [ ] CSS: BEM com `.illusia-`, sem valores literais, sem `!important`
- [ ] JS: Vanilla + Stimulus, sem jQuery, `defer`
- [ ] Segurança: sanitização de input, escape de output, nonces em forms/AJAX
- [ ] Enqueue condicional (só carrega onde necessário)

### Integração com Fictioneer
- [ ] Funcionalidade original do pai preservada (dados, queries, lógica)
- [ ] Hooks/filters usados quando disponíveis (antes de copiar templates)
- [ ] Dependências de enqueue corretas (`fictioneer-application`)
- [ ] Sem conflito com outros componentes já redesenhados
- [ ] Documentado por que a abordagem foi escolhida (hook vs template vs replace)

### Finalização
- [ ] CHANGELOG.md atualizado
- [ ] Versão incrementada (se necessário)
- [ ] Commit com mensagem descritiva (`feat:`, `fix:`, `style:`, etc.)

---

## Exemplo de Fluxo

> Cenário: "Vamos redesenhar o Story Card."

### Fase 0 — Preparação

```
1. Ler docs/STANDARDS.md
2. Ler docs/DESIGN-DIRECTION.md
3. Encontrar no Fictioneer:
   - fictioneer/partials/partial-card-story.php  → template do card
   - fictioneer/css/_card-story.scss              → estilos do pai
   - fictioneer/js/controllers/...                → JS relacionado
4. Listar hooks:
   - do_action('fictioneer_story_card_header', $story_id)
   - apply_filters('fictioneer_story_card_classes', $classes)
5. Verificar illusia-theme/ → nenhum override existente
```

### Fase 1 — Mapeamento

```
Relatório:
- O Fictioneer renderiza o story card via partial-card-story.php
- Dados disponíveis: $post, $story (custom post), get_post_meta() para rating, word count, etc.
- Hooks: há apply_filters para classes e do_action no header/footer
- O card aparece em: archive-stories.php, shortcode [fictioneer_stories], search results
- Decisão: usar remove_action + add_action para substituir o partial inteiro,
  porque o markup do pai é incompatível com nosso design (grid vs flexbox)
```

### Fase 2 — Implementação

```
Arquivos criados:
- partials/illusia-story-card.php      → HTML semântico com .illusia-story-card
- css/components/illusia-story-card.css → Estilos usando tokens dinâmicos
- includes/illusia-story-card-functions.php → PHP para remover card do pai e adicionar o nosso
- functions.php → require do includes + enqueue condicional do CSS
```

### Fase 3 — Checklist

```
✅ Cores dinâmicas (--void-*, --ink-*, --amber-free com hsl)
✅ Espaçamento --space-md, --space-sm
✅ Tipografia --text-lg (título), --text-sm (metadados), --ff-display / --ff-mono
✅ Border-radius --r-xl
✅ Motion --ease-expo, --t-mid
✅ Glass surface com --glass-bg, --glass-blur, --glass-border
✅ Shimmer line no topo via ::before
✅ Progressão de bordas: --border-1 → --border-amber no hover
✅ Responsivo 375px–1440px, grid colapsa em 640px
✅ Contraste verificado, focus visible, teclado ok
✅ PHP com prefixo, type hints, escape, PHPDoc
✅ CSS BEM .illusia-story-card, zero literais
✅ Enqueue condicional (só em archives e home)
✅ Funcionalidade do Fictioneer preservada
✅ CHANGELOG atualizado, commit feito
```
