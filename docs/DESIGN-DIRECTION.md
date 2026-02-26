# Direção Artística — Illusia Theme
> Última modificação: 2026-02-25

Documento extraído do `design-guide.html`. Define a identidade visual completa do Illusia — tokens, cores, tipografia, componentes, motion e regras invioláveis. Todo CSS e HTML do projeto **deve** seguir este documento.

---

## Sumário

1. [Filosofia](#1-filosofia)
2. [Design Tokens](#2-design-tokens)
3. [Paleta de Cores](#3-paleta-de-cores)
4. [Tipografia](#4-tipografia)
5. [Espaçamento](#5-espaçamento)
6. [Raio de Borda](#6-raio-de-borda)
7. [Atmosfera](#7-atmosfera)
8. [Componentes](#8-componentes)
9. [Motion](#9-motion)
10. [Layout & Responsivo](#10-layout--responsivo)
11. [As 10 Regras Invioláveis](#11-as-10-regras-invioláveis)

---

## 1. Filosofia

### Nome do estilo
**Dark Editorial Observatory** — a intersecção entre o refinamento tipográfico de uma publicação literária de luxo e a precisão silenciosa de um painel de controle científico.

### Conceito central
> Um livro raro de colecionador dentro de um observatório astronômico. Escuridão profunda com detalhes que brilham. Densidade controlada. Cada elemento justifica sua presença ou não existe.

### Os quatro pilares

| Pilar | Descrição |
|---|---|
| **Atmosfera** | Cada tela tem profundidade visual mesmo sem imagens. Ruído de grão, orbs de luz difusa, bordas hairline criam um ambiente vivo. |
| **Hierarquia** | O olhar tem um caminho: título serifado dramático → subtítulo mono sutil → corpo sans-serif leve. Nunca dois elementos disputando protagonismo. |
| **Contenção** | Cada cor, borda e sombra tem função semântica. Decoração sem propósito é ruído — não elegância. |
| **Fluidez** | Nenhum valor fixo. Todo espaçamento, fonte e raio usa `clamp()` para se adaptar de 375px a 1440px sem breakpoints artificiais. |

### O que este design NUNCA é
- Gradientes roxos genéricos
- Fundos brancos
- Fontes Inter/Roboto
- Cards com sombras coloridas excessivas
- Animações desnecessárias em elementos estáticos
- Bordas coloridas fortes sem motivo semântico
- Mais de 3 tons de acento em uso simultâneo

---

## 2. Design Tokens

Todos os valores vivem como custom properties no `:root`. **Nunca use valores literais** — sempre referencie um token.

```css
/* ✅ Correto */
padding: var(--space-md);
font-size: var(--text-sm);
border-color: var(--border-amber);

/* ❌ Errado — hardcoded */
padding: 12px;
font-size: .875rem;
border-color: rgba(232,184,109,.22);
```

### Anatomia do clamp()

Todo valor fluido: `clamp(mínimo, preferido-em-vw, máximo)`. O viewport entre 375px e 1440px.

```css
--space-md: clamp(.875rem, 2vw, 1.25rem);
/*          ↑ 375px    ↑ cresce  ↑ 1440px */
```

### Sistema Dinâmico de Cores

Todas as cores do Illusia são **calculadas via HSL** com variáveis de controle, seguindo o padrão do Fictioneer. Nenhuma cor é um hex estático — toda cor é derivada de fórmulas que respondem a offsets globais.

#### Variáveis de Controle

```css
:root {
  /* ── Ajustes globais ── */
  --illusia-hue-offset: 0deg;         /* Rotação global de matiz (shift de tema) */
  --illusia-saturation: 1;            /* Multiplicador de saturação (0 = cinza, 1 = normal) */
  --illusia-darken: 1;                /* Multiplicador de luminosidade dos fundos */

  /* ── Ajustes de texto ── */
  --illusia-font-saturation: 1;       /* Saturação dos textos (independente dos fundos) */
  --illusia-font-lightness: 1;        /* Luminosidade dos textos */

  /* ── Matizes base por família ── */
  --illusia-void-hue: 270deg;         /* Roxo profundo — fundos */
  --illusia-ink-hue: 35deg;           /* Creme quente — textos */
  --illusia-amber-hue: 37deg;         /* Âmbar dourado — primário */
  --illusia-teal-hue: 170deg;         /* Turquesa — em andamento */
  --illusia-crimson-hue: 0deg;        /* Vermelho — avisos/erros */
  --illusia-violet-hue: 260deg;       /* Violeta — fandoms */
  --illusia-sage-hue: 134deg;         /* Verde sálvia — sucesso */
}
```

#### Como funciona

Cada cor segue o padrão `-free` / `hsl()` do Fictioneer:

```css
/* 1. Triplet HSL "livre" (sem hsl wrapper) — reutilizável com opacidade */
--void-0-free: calc(var(--illusia-void-hue) + var(--illusia-hue-offset))
               calc(18% * var(--illusia-saturation))
               clamp(1%, 3% * var(--illusia-darken), 53%);

/* 2. Cor final */
--void-0: hsl(var(--void-0-free));

/* 3. Variante com opacidade — usa o -free */
--void-0-glass: hsl(var(--void-0-free) / .7);
```

**Por que `clamp()` na luminosidade?** Garante que mesmo com `--illusia-darken` extremo, a cor não extrapole para preto puro (0%) ou branco (100%). O padrão é `clamp(MIN%, BASE% * var(--illusia-darken), MAX%)` onde MIN = ~BASE/3 e MAX = BASE + 50%.

**Por que `max()` na saturação de texto?** Textos usam `max(calc(SAT% * (font-sat + sat - 1)), 0%)` — isso garante que a saturação nunca fique negativa quando ambos os multiplicadores diminuem.

---

## 3. Paleta de Cores

### Void — fundos (escuridão em camadas)

Todas as cores void derivam de `--illusia-void-hue` (270°). Padrão de fundos: luminosidade cresce de 3% a 17% em 6 passos.

| Token | HSL base | Uso |
|---|---|---|
| `--void-0` | `270° 18% 3%` | Fundo raiz (body) |
| `--void-1` | `270° 16% 6%` | Header, nav, scrollbar track |
| `--void-2` | `270° 16% 8%` | Cards, code blocks |
| `--void-3` | `270° 14% 11%` | Cards hover |
| `--void-4` | `270° 13% 14%` | Elementos interativos |
| `--void-5` | `270° 15% 17%` | Alt dark |

```css
/* Void — triplets -free + cor final */
--void-0-free: calc(var(--illusia-void-hue) + var(--illusia-hue-offset)) calc(18% * var(--illusia-saturation)) clamp(1%, 3% * var(--illusia-darken), 53%);
--void-1-free: calc(var(--illusia-void-hue) + var(--illusia-hue-offset)) calc(16% * var(--illusia-saturation)) clamp(2%, 6% * var(--illusia-darken), 56%);
--void-2-free: calc(var(--illusia-void-hue) + var(--illusia-hue-offset)) calc(16% * var(--illusia-saturation)) clamp(3%, 8% * var(--illusia-darken), 58%);
--void-3-free: calc(var(--illusia-void-hue) + var(--illusia-hue-offset)) calc(14% * var(--illusia-saturation)) clamp(4%, 11% * var(--illusia-darken), 61%);
--void-4-free: calc(var(--illusia-void-hue) + var(--illusia-hue-offset)) calc(13% * var(--illusia-saturation)) clamp(5%, 14% * var(--illusia-darken), 64%);
--void-5-free: calc(var(--illusia-void-hue) + var(--illusia-hue-offset)) calc(15% * var(--illusia-saturation)) clamp(6%, 17% * var(--illusia-darken), 67%);

--void-0: hsl(var(--void-0-free));
--void-1: hsl(var(--void-1-free));
--void-2: hsl(var(--void-2-free));
--void-3: hsl(var(--void-3-free));
--void-4: hsl(var(--void-4-free));
--void-5: hsl(var(--void-5-free));
```

### Ink — textos

Todas as cores ink derivam de `--illusia-ink-hue` (35°). Usam `--illusia-font-saturation` e `--illusia-font-lightness` para controle independente dos fundos.

| Token | HSL base | Uso |
|---|---|---|
| `--ink-0` | `35° 51% 92%` | Títulos, texto principal |
| `--ink-1` | `35° 22% 72%` | Corpo de texto, parágrafos |
| `--ink-2` | `35° 11% 49%` | Texto secundário, metadados |
| `--ink-3` | `35° 16% 25%` | Desabilitado, placeholders |
| `--ink-4` | `35° 27% 13%` | Fantasma (quase invisível) |

```css
/* Ink — padrão de texto com font-saturation e font-lightness */
--ink-0: hsl(
  calc(var(--illusia-ink-hue) + var(--illusia-hue-offset))
  max(calc(51% * (var(--illusia-font-saturation) + var(--illusia-saturation) - 1)), 0%)
  clamp(0%, calc(92% * var(--illusia-font-lightness, 1)), 100%)
);
--ink-1: hsl(
  calc(var(--illusia-ink-hue) + var(--illusia-hue-offset))
  max(calc(22% * (var(--illusia-font-saturation) + var(--illusia-saturation) - 1)), 0%)
  clamp(0%, calc(72% * var(--illusia-font-lightness, 1)), 100%)
);
--ink-2: hsl(
  calc(var(--illusia-ink-hue) + var(--illusia-hue-offset))
  max(calc(11% * (var(--illusia-font-saturation) + var(--illusia-saturation) - 1)), 0%)
  clamp(0%, calc(49% * var(--illusia-font-lightness, 1)), 100%)
);
--ink-3: hsl(
  calc(var(--illusia-ink-hue) + var(--illusia-hue-offset))
  max(calc(16% * (var(--illusia-font-saturation) + var(--illusia-saturation) - 1)), 0%)
  clamp(0%, calc(25% * var(--illusia-font-lightness, 1)), 100%)
);
--ink-4: hsl(
  calc(var(--illusia-ink-hue) + var(--illusia-hue-offset))
  max(calc(27% * (var(--illusia-font-saturation) + var(--illusia-saturation) - 1)), 0%)
  clamp(0%, calc(13% * var(--illusia-font-lightness, 1)), 100%)
);
```

### Acentos — papéis semânticos RÍGIDOS

Cada acento tem seu matiz base. Saturação responde a `--illusia-saturation`. Luminosidade é fixa (identidade visual).

| Token | HSL base | Papel |
|---|---|---|
| `--amber` | `37° 73% 67%` | **Primário**: CTAs, links, foco, destaque — ÚNICO acento livre |
| `--amber-dim` | `37° 43% 44%` | Labels, ícones, bordas sutis |
| `--teal` | `170° 50% 62%` | **Status "em andamento"** e gêneros literários |
| `--teal-dim` | `170° 41% 38%` | Variante dim do teal |
| `--crimson` | `0° 58% 60%` | **Avisos, erros, conteúdo adulto** |
| `--crimson-dim` | `0° 41% 38%` | Variante dim do crimson |
| `--violet` | `260° 50% 66%` | **Fandoms, especial** |
| `--violet-dim` | `260° 33% 38%` | Variante dim do violet |
| `--sage` | `134° 35% 61%` | **Concluído, sucesso** |
| `--sage-dim` | `134° 29% 32%` | Variante dim do sage |

```css
/* Acentos — triplets -free para reuso com opacidade */
--amber-free:       calc(var(--illusia-amber-hue) + var(--illusia-hue-offset)) calc(73% * var(--illusia-saturation)) 67%;
--amber-dim-free:   calc(var(--illusia-amber-hue) + var(--illusia-hue-offset)) calc(43% * var(--illusia-saturation)) 44%;
--teal-free:        calc(var(--illusia-teal-hue) + var(--illusia-hue-offset)) calc(50% * var(--illusia-saturation)) 62%;
--teal-dim-free:    calc(var(--illusia-teal-hue) + var(--illusia-hue-offset)) calc(41% * var(--illusia-saturation)) 38%;
--crimson-free:     calc(var(--illusia-crimson-hue) + var(--illusia-hue-offset)) calc(58% * var(--illusia-saturation)) 60%;
--crimson-dim-free: calc(var(--illusia-crimson-hue) + var(--illusia-hue-offset)) calc(41% * var(--illusia-saturation)) 38%;
--violet-free:      calc(var(--illusia-violet-hue) + var(--illusia-hue-offset)) calc(50% * var(--illusia-saturation)) 66%;
--violet-dim-free:  calc(var(--illusia-violet-hue) + var(--illusia-hue-offset)) calc(33% * var(--illusia-saturation)) 38%;
--sage-free:        calc(var(--illusia-sage-hue) + var(--illusia-hue-offset)) calc(35% * var(--illusia-saturation)) 61%;
--sage-dim-free:    calc(var(--illusia-sage-hue) + var(--illusia-hue-offset)) calc(29% * var(--illusia-saturation)) 32%;

/* Cores finais */
--amber:       hsl(var(--amber-free));
--amber-dim:   hsl(var(--amber-dim-free));
--teal:        hsl(var(--teal-free));
--teal-dim:    hsl(var(--teal-dim-free));
--crimson:     hsl(var(--crimson-free));
--crimson-dim: hsl(var(--crimson-dim-free));
--violet:      hsl(var(--violet-free));
--violet-dim:  hsl(var(--violet-dim-free));
--sage:        hsl(var(--sage-free));
--sage-dim:    hsl(var(--sage-dim-free));
```

### Variantes de acento (glows e halos)

Usam os triplets `-free` com opacidade variável — nunca hex/rgba estático.

```css
--amber-glow:  hsl(var(--amber-free) / .12);  /* Background de seleção/hover */
--amber-halo:  hsl(var(--amber-free) / .06);  /* Background ultra-sutil */
--teal-glow:   hsl(var(--teal-free) / .1);    /* Background teal sutil */
```

### Regra dos 3 tons
> Nunca mais de 3 tons de acento em uso visível simultâneo na mesma tela. Em geral: âmbar + teal + neutros. Crimson e violet aparecem **apenas** quando há semântica (aviso, fandom).

### Bordas semânticas

Bordas neutras derivam de `--illusia-ink-hue` com alta luminosidade. Bordas de acento derivam de `--amber-free`.

| Token | Fórmula | Uso |
|---|---|---|
| `--border-0` | `hsl(ink / .04)` | Separadores internos de grid |
| `--border-1` | `hsl(ink / .08)` | Borda padrão de card/elemento |
| `--border-2` | `hsl(ink / .13)` | Hover neutro |
| `--border-amber` | `hsl(amber / .22)` | Hover com intenção |
| `--border-active` | `hsl(amber / .45)` | Foco, ativo, selecionado |

```css
/* Base de borda neutra — creme quente translúcido */
--illusia-border-base: calc(var(--illusia-ink-hue) + var(--illusia-hue-offset))
                       calc(100% * var(--illusia-saturation)) 91%;

--border-0:      hsl(var(--illusia-border-base) / .04);
--border-1:      hsl(var(--illusia-border-base) / .08);
--border-2:      hsl(var(--illusia-border-base) / .13);
--border-amber:  hsl(var(--amber-free) / .22);
--border-active: hsl(var(--amber-free) / .45);
```

**Progressão obrigatória**: `--border-1` → `--border-2` (hover neutro) → `--border-amber` (hover com intenção) → `--border-active` (foco/ativo). Nunca pule níveis.

---

## 4. Tipografia

### Três famílias com papéis rígidos

| Família | Token | Papel |
|---|---|---|
| **Playfair Display** | `--ff-display` | Títulos, nomes de obra, stats, seções. Peso 600–900. Itálico com âmbar para ênfase dramática. |
| **Syne** | `--ff-ui` | Corpo de texto, botões, labels de interface. Peso 400–700. |
| **Fira Code** | `--ff-mono` | Metadados: eyebrows, badges, tabs, pills, datas, contadores, breadcrumbs, labels de sidebar. Uppercase + letter-spacing quando for label. |

### Escala tipográfica fluida

| Token | Range | Uso típico |
|---|---|---|
| `--text-3xl` | 2.8rem → 5rem | Hero titles (Playfair 900) |
| `--text-2xl` | 2rem → 3.2rem | Títulos de seção (Playfair 700) |
| `--text-xl` | 1.45rem → 1.9rem | Subtítulos, stats (Playfair 600) |
| `--text-lg` | 1.15rem → 1.45rem | Títulos de card (Playfair 600) |
| `--text-md` | 1rem → 1.15rem | Texto de destaque (Syne) |
| `--text-base` | .88rem → 1rem | Corpo padrão (Syne 400) |
| `--text-sm` | .78rem → .88rem | Metadados, datas, nomes de autor |
| `--text-xs` | .68rem → .78rem | Labels de botão, contadores (Fira Code uppercase) |
| `--text-2xs` | .58rem → .68rem | Eyebrows, badges, metadata (Fira Code uppercase) |

### Valores exatos dos tokens

```css
--text-2xs: clamp(.58rem, 1.1vw, .68rem);
--text-xs:  clamp(.68rem, 1.3vw, .78rem);
--text-sm:  clamp(.78rem, 1.5vw, .88rem);
--text-base:clamp(.88rem, 1.7vw, 1rem);
--text-md:  clamp(1rem,   2vw,   1.15rem);
--text-lg:  clamp(1.15rem,2.4vw, 1.45rem);
--text-xl:  clamp(1.45rem,3.2vw, 1.9rem);
--text-2xl: clamp(2rem,   5vw,   3.2rem);
--text-3xl: clamp(2.8rem, 7vw,   5rem);
```

### Regras de uso

- **Playfair**: Títulos, nomes de obra, stats. Nunca para labels ou metadados.
- **Fira Code**: Tudo que é metadado. Nunca para corpo de texto ou parágrafos longos.
- **Syne**: Corpo, botões, interface. É o default.
- Nunca mescle mais de 2 pesos da mesma família no mesmo bloco visual.

---

## 5. Espaçamento

8 níveis fluidos. Espaço generoso é parte da linguagem.

| Token | Valor |
|---|---|
| `--space-2xs` | `clamp(.25rem, .5vw, .375rem)` |
| `--space-xs` | `clamp(.375rem, .75vw, .625rem)` |
| `--space-sm` | `clamp(.5rem, 1.2vw, .875rem)` |
| `--space-md` | `clamp(.875rem, 2vw, 1.25rem)` |
| `--space-lg` | `clamp(1.25rem, 3vw, 2rem)` |
| `--space-xl` | `clamp(1.75rem, 4.5vw, 3rem)` |
| `--space-2xl` | `clamp(2.5rem, 6vw, 4.5rem)` |
| `--space-3xl` | `clamp(3rem, 8vw, 6rem)` |

---

## 6. Raio de Borda

5 níveis fluidos. O raio comunica hierarquia: elementos menores = raio menor, containers maiores = raio maior.

| Token | Valor | Uso |
|---|---|---|
| `--r-xs` | `clamp(2px, .3vw, 3px)` | Badges, pills, chips |
| `--r-sm` | `clamp(3px, .5vw, 5px)` | Botões icon, status badges |
| `--r-md` | `clamp(6px, .9vw, 9px)` | Botões de ação, inputs, capas |
| `--r-lg` | `clamp(9px, 1.4vw, 14px)` | Listas de capítulos, demo frames |
| `--r-xl` | `clamp(12px, 1.8vw, 20px)` | Cards principais, modais, sidebar cards |

**Regras**:
- Quanto maior o container na hierarquia, maior o raio
- **Nunca** `border-radius: 9999px` (pílula) — o sistema é editorial, não playful
- **Exceção única**: avatares de personagem/usuário usam `border-radius: 50%` — o círculo é semanticamente "pessoa"

---

## 7. Atmosfera

### Grain (ruído global)

Aplicado via `body::before` com `position: fixed`. Cria materialidade física.

```css
body::before {
  content: '';
  position: fixed;
  inset: 0;
  background-image: url("data:image/svg+xml,...feTurbulence...");
  pointer-events: none;
  z-index: 9999;
  opacity: .45;  /* entre .35 e .55 — sutil */
}
```

- `baseFrequency`: .8 a .9 (grão fino)
- `numOctaves`: 4 (textura rica)
- Deve ser **sentido**, não visto conscientemente

### Orbs de luz ambiente

Gradientes radiais difusos em `position: fixed`. Invisíveis conscientemente, presentes atmosfericamente.

```css
.orb {
  position: fixed;
  border-radius: 50%;
  filter: blur(clamp(60px, 10vw, 120px));
  pointer-events: none;
  z-index: 0;
  animation: orb-drift 25s ease-in-out infinite;
}

.orb-amber {
  background: radial-gradient(circle, hsl(var(--amber-free) / .07) 0%, transparent 70%);
}

@keyframes orb-drift {
  0%, 100% { transform: translate(0,0) scale(1); }
  33%      { transform: translate(3%, 5%) scale(1.05); }
  66%      { transform: translate(-3%,-3%) scale(.96); }
}
```

- **Nunca** mais de 3 orbs
- Opacidade máxima: .08

### Glass Surface

Cards e superfícies elevadas usam vidro fosco. Nunca branco puro.

```css
.card {
  background:       var(--glass-bg);     /* hsl(void-2 / .7) */
  backdrop-filter:  var(--glass-blur);   /* blur(clamp(8px,1.5vw,16px)) */
  border: 1px solid var(--glass-border); /* hsl(border-base / .08) */
  border-radius:    var(--r-xl);
}
```

### Tokens de glass

Derivados dos triplets `-free` — mudam junto com a paleta.

```css
--glass-bg:     hsl(var(--void-2-free) / .7);
--glass-blur:   blur(clamp(8px, 1.5vw, 16px));
--glass-border: hsl(var(--illusia-border-base) / .08);
```

---

## 8. Componentes

### Botões

| Variante | Uso | Estilo |
|---|---|---|
| `.btn--cta` | Ação principal. **Apenas 1 por view.** | Âmbar sólido, box-shadow glow |
| `.btn--outline` | Ação secundária importante | Borda âmbar translúcida |
| `.btn--ghost` | Ações terciárias, utilitárias | Borda `--border-1` |
| `.btn--icon` | Ações sem label. Máx 3 seguidos. | Quadrado, raio `--r-md` |

**Hover padrão**: `translateY(-1px)` + box-shadow aumentado. Botões icon usam `scale(1.06)`.

### Badges & Tags

| Tipo | Cor | Semântica |
|---|---|---|
| `.badge--genre` | Teal | Gêneros literários |
| `.badge--fandom` | Violet | Fandoms |
| `.badge--character` | Amber | Personagens |
| `.badge--tag` | Neutro (ink-2) | Tags genéricas |
| `.badge--warning` | Crimson | Avisos de conteúdo |
| `.badge--ongoing` | Teal | Status "em andamento" |
| `.badge--done` | Sage | Status "concluído" |
| `.badge--hiatus` | Amber | Status "hiato" |
| `.badge--rating-e` | Sage bg | Rating Everyone |
| `.badge--rating-t` | Amber bg | Rating Teen |
| `.badge--rating-m` | Crimson bg | Rating Mature |

**Regra de badges em listas**: `white-space: nowrap` + `flex-shrink: 0`. Container: `flex-wrap: nowrap` + `overflow: hidden` + `mask-image: linear-gradient(to right, black 72%, transparent)` para fade.

### Cards

**Glass Card** (sidebar, conteúdo elevado):
- Glass bg + backdrop-filter + shimmer line no topo via `::before`
- Hover: `border-color: var(--border-amber)` + `translateY(-1px)`
- Shimmer line obrigatório: `linear-gradient(90deg, transparent, hsl(var(--amber-free) / .12), transparent)` de 1px no topo

**Illusia-card** (listagem de obras):
- Grid 2 colunas: cover fixo (col 1, spana todas as rows) + conteúdo (col 2)
- Proporção da capa: sempre **2:3** (`min-height: calc(var(--cover-w) * 1.5)`)
- Mobile: cover spana header + excerpt (rows 1–2), resto full-width
- Variante `--no-cover`: `grid-template-columns: 1fr` + barra âmbar lateral 3px
- Variante `--sticky`: fundo mais âmbar, borda `--border-amber` inicial, radial-gradient sutil

### Inputs

```css
.input {
  background:    hsl(var(--void-0-free) / .6);
  border: 1px solid var(--border-1);
  border-radius: var(--r-md);
  color:         var(--ink-0);
  font-family:   var(--ff-ui);
  font-size:     var(--text-sm);
  padding:       .65em var(--space-md);
  outline:       none;
}
.input:focus {
  border-color: var(--border-amber);
  box-shadow:   0 0 0 3px hsl(var(--amber-free) / .08); /* halo difuso */
}
```

**Labels de campo**: Fira Code, `--text-2xs`, uppercase, `letter-spacing: .1em`, cor `--ink-3`. Nunca fonte de corpo para labels.

### Tabs vs Pills

- **Tabs**: Navegação estrutural, borda de cards, `border-bottom` como indicador
- **Pills**: Filtros de lista, dentro de toolbars, `border-radius: 2px` (quase retangular, nunca circular)

### Listas de item

Padrão universal: barra vertical âmbar que nasce do `scaleY(0)` no hover.

```css
.list-item { position: relative; overflow: hidden; }

.list-item::before {
  content: '';
  position: absolute; left: 0; top: 0; bottom: 0;
  width: 2px;
  background: var(--amber);
  transform: scaleY(0);
  transition: transform var(--t-mid) var(--ease-expo);
}

.list-item:hover { background: hsl(var(--amber-free) / .03); }
.list-item:hover::before { transform: scaleY(1); }
```

### Barra de progresso

- Fundo: 3px, `hsl(var(--illusia-border-base) / .05)`, border-radius 3px
- Fill: gradiente `--amber-dim → --amber`
- **Ponto brilhante obrigatório**: `::after` com `border-radius: 50%` e `box-shadow` âmbar no final do fill

### Breadcrumbs

Fira Code, `--text-2xs`, uppercase, `letter-spacing: .08em`, cor `--ink-3`. Separadores com `opacity: .3`.

---

## 9. Motion

### Curvas de easing

| Token | Valor | Uso |
|---|---|---|
| `--ease-expo` | `cubic-bezier(.16,1,.3,1)` | Entradas de elementos, card hover, barras laterais |
| `--ease-std` | `cubic-bezier(.4,0,.2,1)` | Transições de estado, fades |
| Spring | `cubic-bezier(.34,1.56,.64,1)` | Botões CTA, confirmações de sucesso |

### Durações

| Token | Valor | Uso |
|---|---|---|
| `--t-fast` | `110ms` | Hover de cor, border, opacity |
| `--t-mid` | `240ms` | Hover de transform, expansões, aparecimento |
| `--t-slow` | `420ms` | Entradas de página (com delay escalonado), zoom de capa |

### Microinterações por tipo de elemento

| Elemento | Animação hover |
|---|---|
| Cards | `translateY(-2px)` + sombra expandida |
| Sidebar buttons | `translateX(2px)` + barra lateral `scaleY(1)` |
| Botões icon | `scale(1.06)` |
| Botões CTA | `translateY(-1px)` + box-shadow expandido + glow |
| Badges | `filter: brightness(1.2)` |

### Entradas escalonadas

Cada item de lista renderizado: `animation-delay: N * 30ms`. Conteúdo aparece de forma orgânica, não simultânea.

### Scrollbar

A scrollbar é parte da atmosfera — fina e discreta, com thumb âmbar sobre track void.

```css
::-webkit-scrollbar {
  width: 4px;
}
::-webkit-scrollbar-track {
  background: var(--void-1);  /* #100e13 */
}
::-webkit-scrollbar-thumb {
  background: var(--amber-dim);  /* #a07840 */
  border-radius: 4px;
}
```

Para Firefox, usar o equivalente:

```css
* {
  scrollbar-width: thin;
  scrollbar-color: var(--amber-dim) transparent;
}
```

**Regras**:
- Largura sempre **4px** — nunca mais larga
- Thumb usa `--amber-dim`, não `--amber` (sutil, não chamativo)
- Track usa `--void-1` ou `transparent` dependendo do contexto
- Em containers com scroll interno (sidebars, listas), aplicar `scrollbar-width: thin` + `scrollbar-color`

---

## 10. Layout & Responsivo

### Largura máxima

```css
--max-w: min(1100px, 94vw);
/* Nunca use max-width: 1100px diretamente */
/* min() garante padding lateral automático em telas menores */
```

### Breakpoints semânticos

| Breakpoint | Comportamento |
|---|---|
| `≤ 400px` | Controles extras somem. Layout máximo comprimido. |
| `≤ 640px` | Cards: capa + header+excerpt side-by-side. Resto full-width. |
| `≤ 700px` | Nav vira horizontal. Story page: coluna única. |
| `≤ 900px` | Story page: sidebar sobe, vira grid horizontal. |

---

## 11. As 10 Regras Invioláveis

1. **Nunca valores literais no CSS** — Todo espaço, fonte, cor e raio vem de um token `var(--...)`. Valores literais só em tokens novos no `:root`.

2. **Toda medida de layout usa clamp()** — Sem pixels fixos para espaçamento, tipografia ou raio de borda. O design vive entre 375px e 1440px.

3. **Âmbar é o único acento livre** — Teal, crimson e violet têm papéis semânticos rígidos. Nunca use para decoração.

4. **Playfair só para títulos e stats** — Nunca para metadados. Fira Code só para metadados — nunca para corpo de texto.

5. **Grain sempre presente** — `body::before` com noise SVG em `position: fixed` e `z-index: 9999`. Opacidade entre .35 e .55.

6. **Borda semântica no hover** — `--border-1` → `--border-2` → `--border-amber` → `--border-active`. Nunca pule níveis.

7. **Tags sempre em uma linha com fade** — `flex-wrap: nowrap` + `overflow: hidden` + `mask-image` gradiente.

8. **Barra lateral nos items de lista** — Todo row clicável tem `::before` com `scaleY(0) → scaleY(1)` usando `--ease-expo`.

9. **Entradas escalonadas em listas** — Cada item tem `animation-delay: N * 30ms`.

10. **Shimmer line no topo de cards** — Todo card tem `::before` com gradiente `transparent → hsl(var(--amber-free) / .12) → transparent` de 1px no topo.

---

## Referência Rápida — Todos os Tokens CSS

```css
:root {
  /* ═══ VARIÁVEIS DE CONTROLE ═══ */
  --illusia-hue-offset: 0deg;
  --illusia-saturation: 1;
  --illusia-darken: 1;
  --illusia-font-saturation: 1;
  --illusia-font-lightness: 1;

  /* ═══ MATIZES BASE ═══ */
  --illusia-void-hue:    270deg;
  --illusia-ink-hue:     35deg;
  --illusia-amber-hue:   37deg;
  --illusia-teal-hue:    170deg;
  --illusia-crimson-hue: 0deg;
  --illusia-violet-hue:  260deg;
  --illusia-sage-hue:    134deg;

  /* ═══ VOID — fundos (triplets + cor) ═══ */
  --void-0-free: calc(var(--illusia-void-hue) + var(--illusia-hue-offset)) calc(18% * var(--illusia-saturation)) clamp(1%, 3% * var(--illusia-darken), 53%);
  --void-1-free: calc(var(--illusia-void-hue) + var(--illusia-hue-offset)) calc(16% * var(--illusia-saturation)) clamp(2%, 6% * var(--illusia-darken), 56%);
  --void-2-free: calc(var(--illusia-void-hue) + var(--illusia-hue-offset)) calc(16% * var(--illusia-saturation)) clamp(3%, 8% * var(--illusia-darken), 58%);
  --void-3-free: calc(var(--illusia-void-hue) + var(--illusia-hue-offset)) calc(14% * var(--illusia-saturation)) clamp(4%, 11% * var(--illusia-darken), 61%);
  --void-4-free: calc(var(--illusia-void-hue) + var(--illusia-hue-offset)) calc(13% * var(--illusia-saturation)) clamp(5%, 14% * var(--illusia-darken), 64%);
  --void-5-free: calc(var(--illusia-void-hue) + var(--illusia-hue-offset)) calc(15% * var(--illusia-saturation)) clamp(6%, 17% * var(--illusia-darken), 67%);

  --void-0: hsl(var(--void-0-free));
  --void-1: hsl(var(--void-1-free));
  --void-2: hsl(var(--void-2-free));
  --void-3: hsl(var(--void-3-free));
  --void-4: hsl(var(--void-4-free));
  --void-5: hsl(var(--void-5-free));

  /* ═══ INK — textos ═══ */
  --ink-0: hsl(calc(var(--illusia-ink-hue) + var(--illusia-hue-offset)) max(calc(51% * (var(--illusia-font-saturation) + var(--illusia-saturation) - 1)), 0%) clamp(0%, calc(92% * var(--illusia-font-lightness, 1)), 100%));
  --ink-1: hsl(calc(var(--illusia-ink-hue) + var(--illusia-hue-offset)) max(calc(22% * (var(--illusia-font-saturation) + var(--illusia-saturation) - 1)), 0%) clamp(0%, calc(72% * var(--illusia-font-lightness, 1)), 100%));
  --ink-2: hsl(calc(var(--illusia-ink-hue) + var(--illusia-hue-offset)) max(calc(11% * (var(--illusia-font-saturation) + var(--illusia-saturation) - 1)), 0%) clamp(0%, calc(49% * var(--illusia-font-lightness, 1)), 100%));
  --ink-3: hsl(calc(var(--illusia-ink-hue) + var(--illusia-hue-offset)) max(calc(16% * (var(--illusia-font-saturation) + var(--illusia-saturation) - 1)), 0%) clamp(0%, calc(25% * var(--illusia-font-lightness, 1)), 100%));
  --ink-4: hsl(calc(var(--illusia-ink-hue) + var(--illusia-hue-offset)) max(calc(27% * (var(--illusia-font-saturation) + var(--illusia-saturation) - 1)), 0%) clamp(0%, calc(13% * var(--illusia-font-lightness, 1)), 100%));

  /* ═══ ACENTOS — triplets + cor ═══ */
  --amber-free:       calc(var(--illusia-amber-hue) + var(--illusia-hue-offset)) calc(73% * var(--illusia-saturation)) 67%;
  --amber-dim-free:   calc(var(--illusia-amber-hue) + var(--illusia-hue-offset)) calc(43% * var(--illusia-saturation)) 44%;
  --teal-free:        calc(var(--illusia-teal-hue) + var(--illusia-hue-offset)) calc(50% * var(--illusia-saturation)) 62%;
  --teal-dim-free:    calc(var(--illusia-teal-hue) + var(--illusia-hue-offset)) calc(41% * var(--illusia-saturation)) 38%;
  --crimson-free:     calc(var(--illusia-crimson-hue) + var(--illusia-hue-offset)) calc(58% * var(--illusia-saturation)) 60%;
  --crimson-dim-free: calc(var(--illusia-crimson-hue) + var(--illusia-hue-offset)) calc(41% * var(--illusia-saturation)) 38%;
  --violet-free:      calc(var(--illusia-violet-hue) + var(--illusia-hue-offset)) calc(50% * var(--illusia-saturation)) 66%;
  --violet-dim-free:  calc(var(--illusia-violet-hue) + var(--illusia-hue-offset)) calc(33% * var(--illusia-saturation)) 38%;
  --sage-free:        calc(var(--illusia-sage-hue) + var(--illusia-hue-offset)) calc(35% * var(--illusia-saturation)) 61%;
  --sage-dim-free:    calc(var(--illusia-sage-hue) + var(--illusia-hue-offset)) calc(29% * var(--illusia-saturation)) 32%;

  --amber:       hsl(var(--amber-free));
  --amber-dim:   hsl(var(--amber-dim-free));
  --teal:        hsl(var(--teal-free));
  --teal-dim:    hsl(var(--teal-dim-free));
  --crimson:     hsl(var(--crimson-free));
  --crimson-dim: hsl(var(--crimson-dim-free));
  --violet:      hsl(var(--violet-free));
  --violet-dim:  hsl(var(--violet-dim-free));
  --sage:        hsl(var(--sage-free));
  --sage-dim:    hsl(var(--sage-dim-free));

  /* ═══ GLOWS & HALOS ═══ */
  --amber-glow:  hsl(var(--amber-free) / .12);
  --amber-halo:  hsl(var(--amber-free) / .06);
  --teal-glow:   hsl(var(--teal-free) / .1);

  /* ═══ BORDAS ═══ */
  --illusia-border-base: calc(var(--illusia-ink-hue) + var(--illusia-hue-offset)) calc(100% * var(--illusia-saturation)) 91%;

  --border-0:      hsl(var(--illusia-border-base) / .04);
  --border-1:      hsl(var(--illusia-border-base) / .08);
  --border-2:      hsl(var(--illusia-border-base) / .13);
  --border-amber:  hsl(var(--amber-free) / .22);
  --border-active: hsl(var(--amber-free) / .45);

  /* ═══ GLASS ═══ */
  --glass-bg:     hsl(var(--void-2-free) / .7);
  --glass-blur:   blur(clamp(8px, 1.5vw, 16px));
  --glass-border: hsl(var(--illusia-border-base) / .08);

  /* ═══ ESPAÇAMENTO (clamp fluido) ═══ */
  --space-2xs: clamp(.25rem,  .5vw,  .375rem);
  --space-xs:  clamp(.375rem, .75vw, .625rem);
  --space-sm:  clamp(.5rem,   1.2vw, .875rem);
  --space-md:  clamp(.875rem, 2vw,   1.25rem);
  --space-lg:  clamp(1.25rem, 3vw,   2rem);
  --space-xl:  clamp(1.75rem, 4.5vw, 3rem);
  --space-2xl: clamp(2.5rem,  6vw,   4.5rem);
  --space-3xl: clamp(3rem,    8vw,   6rem);

  /* ═══ TIPOGRAFIA (clamp fluido) ═══ */
  --text-2xs: clamp(.58rem, 1.1vw, .68rem);
  --text-xs:  clamp(.68rem, 1.3vw, .78rem);
  --text-sm:  clamp(.78rem, 1.5vw, .88rem);
  --text-base:clamp(.88rem, 1.7vw, 1rem);
  --text-md:  clamp(1rem,   2vw,   1.15rem);
  --text-lg:  clamp(1.15rem,2.4vw, 1.45rem);
  --text-xl:  clamp(1.45rem,3.2vw, 1.9rem);
  --text-2xl: clamp(2rem,   5vw,   3.2rem);
  --text-3xl: clamp(2.8rem, 7vw,   5rem);

  /* ═══ RAIO DE BORDA (clamp fluido) ═══ */
  --r-xs: clamp(2px,  .3vw, 3px);
  --r-sm: clamp(3px,  .5vw, 5px);
  --r-md: clamp(6px,  .9vw, 9px);
  --r-lg: clamp(9px,  1.4vw,14px);
  --r-xl: clamp(12px, 1.8vw,20px);

  /* ═══ FAMÍLIAS TIPOGRÁFICAS ═══ */
  --ff-display: 'Playfair Display', Georgia, serif;
  --ff-ui:      'Syne', sans-serif;
  --ff-mono:    'Fira Code', monospace;

  /* ═══ EASING ═══ */
  --ease-expo: cubic-bezier(.16,1,.3,1);
  --ease-std:  cubic-bezier(.4,0,.2,1);

  /* ═══ DURAÇÕES ═══ */
  --t-fast: 110ms;
  --t-mid:  240ms;
  --t-slow: 420ms;

  /* ═══ LAYOUT ═══ */
  --max-w: min(1100px, 94vw);
}
```
