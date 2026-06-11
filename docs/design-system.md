# 🦊 Foxbit Design System

> Design tokens, componentes e padrões extraídos da plataforma **app.foxbit.com.br**
> para reutilização em projetos que seguem a mesma identidade visual.

---

## Sumário

1. [Como usar](#como-usar)
2. [Cores](#cores)
3. [Tipografia](#tipografia)
4. [Espaçamentos](#espaçamentos)
5. [Border Radius](#border-radius)
6. [Sombras](#sombras)
7. [Z-Index](#z-index)
8. [Dark Theme](#dark-theme)
9. [Componentes](#componentes)
   - [Botões](#botões)
   - [Inputs de Formulário](#inputs-de-formulário)
   - [Input de Busca](#input-de-busca)
   - [Category Pills](#category-pills)
   - [Badges de Variação](#badges-de-variação)
   - [Tabela da Carteira](#tabela-da-carteira)
   - [Cards](#cards)
   - [Collapsible / Accordion](#collapsible--accordion)
   - [Menu Lateral (Sidebar)](#menu-lateral-sidebar)
   - [User Button](#user-button)
   - [Toggle Switch](#toggle-switch)
   - [Progress Bar (VIP)](#progress-bar-vip)
   - [Highlight Cards](#highlight-cards)
   - [Alertas / Flash Messages](#alertas--flash-messages)
   - [Notification Badge](#notification-badge)
10. [CSS Variables (copiar e colar)](#css-variables-copiar-e-colar)

---

## Como usar

### Opção 1: Copiar as CSS Variables
Copie o bloco de variáveis CSS no final deste documento e cole no `:root` do seu projeto.

### Opção 2: Importar como arquivo
Salve as variáveis em um arquivo `foxbit-tokens.css` e importe:
```css
@import './foxbit-tokens.css';
```

### Opção 3: Tailwind CSS
Use as variáveis para configurar o `tailwind.config.js`:
```js
module.exports = {
  theme: {
    extend: {
      colors: {
        primary: { 100: '#fff0e0', 500: '#ff6f00', 600: '#e56300' },
        mono: { 50: '#f5f6f7', 100: '#ecedef', 200: '#d5d7da', 300: '#b2b7bb', 600: '#8d959d', 900: '#212529' },
        success: '#1cc97d',
        error: '#ff4747',
        up: '#15a96f',
        down: '#e43b3b',
      },
      fontFamily: { sans: ['Reddit Sans', 'system-ui', 'sans-serif'] },
      borderRadius: { pill: '999px' },
    },
  },
}
```

### Fonte
Adicionar no `<head>`:
```html
<link href="https://fonts.googleapis.com/css2?family=Reddit+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
```

---

## Cores

### Primária (Laranja Foxbit)
| Token | Valor | Uso |
|-------|-------|-----|
| `--colors-primary-g100` | `#fff0e0` | Background hover menu, badge ativo |
| `--colors-primary-g500` | `#ff6f00` | Botão primário, links, ícones ativos |
| `--colors-primary-g600` | `#e56300` | Hover do botão primário |

### Monocromáticas
| Token | Valor | Uso |
|-------|-------|-----|
| `--colors-mono-white` | `#ffffff` | Background principal |
| `--colors-mono-black` | `#212427` | Texto principal, ícones |
| `--colors-mono-g50` | `#f5f6f7` | Background sutil, hover leve |
| `--colors-mono-g100` | `#ecedef` | Bordas, dividers, backgrounds de cards |
| `--colors-mono-g200` | `#d5d7da` | Bordas de inputs, separadores |
| `--colors-mono-g300` | `#b2b7bb` | Placeholder, ícones inativos |
| `--colors-mono-g600` | `#8d959d` | Texto secundário, labels |
| `--colors-mono-g900` | `#212529` | Texto principal, títulos |

### Sistema
| Token | Valor | Uso |
|-------|-------|-----|
| `--colors-success` | `#1cc97d` | Validação sucesso |
| `--colors-success-bg` | `#e8f3ea` | Background alerta sucesso |
| `--colors-up` | `#15a96f` | Variação positiva (seta verde) |
| `--colors-down` | `#e43b3b` | Variação negativa (seta vermelha) |
| `--colors-error` | `#ff4747` | Erro, danger, logout |

---

## Tipografia

**Família:** `Reddit Sans` (fallback: system sans-serif)

| Nome | Tamanho | Peso | Uso |
|------|---------|------|-----|
| XXL / H1 | `2rem` (32px) | Bold (700) | Título de página ("Início") |
| XL / H2 | `1.5rem` (24px) | Bold (700) | Título de seção, login |
| LG / H3 | `1.25rem` (20px) | SemiBold (600) | Subtítulo de card |
| SM / Body | `1rem` (16px) | Medium (500) | Texto padrão |
| XS / Small | `0.875rem` (14px) | Regular (400) | Labels, valores, inputs |
| XXS / Caption | `0.625rem` (10px) | SemiBold (600) | Tags VIP, micro-labels |

### Pesos disponíveis
| Token | Valor |
|-------|-------|
| `--font-weight-light` | 300 |
| `--font-weight-regular` | 400 |
| `--font-weight-medium` | 500 |
| `--font-weight-semibold` | 600 |
| `--font-weight-bold` | 700 |

### Line Heights
| Token | Valor |
|-------|-------|
| `--line-height-sm` | 1.2 |
| `--line-height-md` | 1.4 |
| `--line-height-lg` | 1.6 |

---

## Espaçamentos

Escala baseada em múltiplos de 4px.

| Token | Valor | px |
|-------|-------|----|
| `--sp-xxxs` | `0.25rem` | 4px |
| `--sp-xxs` | `0.5rem` | 8px |
| `--sp-xs` | `0.75rem` | 12px |
| `--sp-sm` | `1rem` | 16px |
| `--sp-md` | `1.25rem` | 20px |
| `--sp-lg` | `1.5rem` | 24px |
| `--sp-xl` | `2rem` | 32px |
| `--sp-xxl` | `2.5rem` | 40px |

---

## Border Radius

| Token | Valor | Uso |
|-------|-------|-----|
| `--radius-xs` | `4px` | Badges internos |
| `--radius-sm` | `8px` | Cards pequenos |
| `--radius-md` | `12px` | Cards médios, menus |
| `--radius-lg` | `16px` | Cards principais, modais |
| `--radius-xl` | `20px` | Dropdown do user menu |
| `--radius-pill` | `999px` | **Botões, inputs, pills** (padrão Foxbit) |

> ⚠️ **O padrão da Foxbit é pill (999px)** para botões e inputs. Isso é o elemento visual mais marcante do design system.

---

## Sombras

| Token | Valor | Uso |
|-------|-------|-----|
| `--shadow-card` | `0 2px 8px rgba(0,0,0,.06)` | Cards padrão |
| `--shadow-dropdown` | `0 4px 20px hsla(0,0%,54%,.16), 0 4px 20px rgba(0,0,0,.1)` | Dropdowns, popups |
| `--shadow-elevated` | `0 8px 32px rgba(0,0,0,.12)` | Modais, cards elevados |

---

## Z-Index

| Token | Valor | Uso |
|-------|-------|-----|
| `--z-dropdown` | `100` | Menus dropdown |
| `--z-modal` | `1000` | Modais |

---

## Dark Theme

Aplique com `data-theme="dark"` no `<body>`. Tokens que mudam:

```css
[data-theme="dark"] {
  --colors-mono-white: #1a1d21;
  --colors-mono-black: #f5f6f7;
  --colors-mono-g50: #22262b;
  --colors-mono-g100: #2c3138;
  --colors-mono-g200: #3a4049;
  --colors-mono-g900: #f5f6f7;
  --colors-custom-background1: #1a1d21;
  --shadow-card: 0 2px 8px rgba(0,0,0,.2);
}
```

---

## Componentes

### Botões

**Formato:** pill (`border-radius: 999px`)
**Altura:** 44px (default), 36px (small)

#### Variantes

| Variante | Background | Texto | Borda |
|----------|-----------|-------|-------|
| **Primary** | `#ff6f00` | `#ffffff` | nenhuma |
| **Standard** | transparente | `#212529` | `1px solid #d5d7da` |
| **Mono** | `#ecedef` | `#212529` | nenhuma |
| **Text** | transparente | `#212529` | nenhuma |

```html
<button class="fx-btn fx-btn--primary">Depositar reais</button>
<button class="fx-btn fx-btn--standard">Depositar</button>
<button class="fx-btn fx-btn--mono">Sacar</button>
<button class="fx-btn fx-btn--text">Marcar como lidas</button>
<button class="fx-btn fx-btn--primary fx-btn--sm">Small</button>
```

```css
.fx-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-family: 'Reddit Sans', sans-serif;
  font-weight: 600;
  font-size: 0.875rem;
  border: none;
  cursor: pointer;
  border-radius: 999px;
  padding: 0 24px;
  height: 44px;
  transition: background .2s ease, transform .1s ease;
}
.fx-btn:active { transform: scale(.97); }
.fx-btn--primary { background: #ff6f00; color: #fff; }
.fx-btn--primary:hover { background: #e56300; }
.fx-btn--standard { background: transparent; color: #212529; border: 1px solid #d5d7da; }
.fx-btn--standard:hover { background: #f5f6f7; }
.fx-btn--mono { background: #ecedef; color: #212529; }
.fx-btn--mono:hover { background: #d5d7da; }
.fx-btn--sm { height: 36px; padding: 0 16px; font-size: 13px; }
```

---

### Inputs de Formulário

**Formato:** pill (`border-radius: 999px`)
**Altura:** 48px
**Estrutura:** `[ícone esquerdo] [input] [ícone status direito]`

#### Estados

| Estado | Borda | Ícone direito |
|--------|-------|---------------|
| **Default** | `#d5d7da` | nenhum |
| **Focus** | `#ff6f00` + sombra laranja | nenhum |
| **Sucesso** | `#1cc97d` | ✓ verde |
| **Erro** | `#ff4747` | ⚠ vermelho |
| **Disabled** | `#d5d7da` + bg cinza | nenhum |

```html
<!-- Input padrão -->
<div class="fx-form-field">
  <span class="fx-field-icon">[SVG]</span>
  <input type="email" placeholder="E-mail">
  <span class="fx-field-status"></span>
</div>

<!-- Input com sucesso -->
<div class="fx-form-field fx-form-field--success">
  <span class="fx-field-icon">[SVG]</span>
  <input type="email" value="user@email.com">
  <span class="fx-field-status fx-field-status--success">[SVG ✓]</span>
</div>

<!-- Input com erro -->
<div class="fx-form-field fx-form-field--error">
  <span class="fx-field-icon">[SVG]</span>
  <input type="email" value="invalido">
  <span class="fx-field-status fx-field-status--error">[SVG ⚠]</span>
</div>
<div class="fx-form-helper fx-form-helper--error">E-mail inválido.</div>

<!-- Link abaixo do input (ex: "Desativar 2FA") -->
<a class="fx-form-link" href="#">Desativar 2FA</a>
```

```css
.fx-form-field {
  display: flex;
  align-items: center;
  background: white;
  border: 1px solid #d5d7da;
  border-radius: 999px;
  padding: 0 16px;
  height: 48px;
  gap: 10px;
  transition: border-color .2s, box-shadow .2s;
}
.fx-form-field:focus-within {
  border-color: #ff6f00;
  box-shadow: 0 0 0 3px rgba(255,111,0,.1);
}
.fx-form-field--success { border-color: #1cc97d; }
.fx-form-field--error { border-color: #ff4747; }
.fx-form-field--disabled { background: #f5f6f7; opacity: .6; pointer-events: none; }

.fx-field-icon { flex-shrink: 0; width: 20px; height: 20px; color: #b2b7bb; }
.fx-form-field:focus-within .fx-field-icon { color: #ff6f00; }

.fx-field-status--success { color: #1cc97d; }
.fx-field-status--error { color: #ff4747; }

.fx-form-helper { font-size: 12px; margin-top: 6px; padding-left: 16px; color: #8d959d; }
.fx-form-helper--error { color: #ff4747; font-weight: 500; }

.fx-form-link { font-size: 13px; font-weight: 500; color: #ff6f00; text-decoration: none; margin-top: 8px; }
.fx-form-link:hover { color: #e56300; text-decoration: underline; }
```

#### Ícones utilizados nos inputs

| Campo | Ícone | Material Icon |
|-------|-------|---------------|
| E-mail | ✉ envelope | `email` |
| Senha | 🔑 chave | `vpn_key` |
| Código 2FA | ⊞ grid de pontos | `dialpad` |
| Sucesso | ✓ check circulado | `check_circle` |
| Erro | ⚠ alerta circulado | `error` |

---

### Input de Busca

**Formato:** pill, 44px de altura, ícone de lupa à esquerda.
Usado na carteira para filtrar ativos.

```html
<div class="fx-input-wrap">
  <svg><!-- lupa --></svg>
  <input class="fx-input" placeholder="Pesquisar" type="text">
</div>
```

---

### Category Pills

Filtros de categoria. Pill shape, o ativo fica com fundo laranja.

```html
<div class="fx-pills">
  <button class="fx-pill fx-pill--active">Todas</button>
  <button class="fx-pill">Com saldo</button>
  <button class="fx-pill">Cripto</button>
  <button class="fx-pill">DeFi</button>
</div>
```

```css
.fx-pill {
  font-size: 13px; font-weight: 500;
  padding: 6px 16px;
  border-radius: 999px;
  border: 1px solid #d5d7da;
  background: white; color: #212529;
}
.fx-pill:hover { border-color: #ff6f00; color: #ff6f00; }
.fx-pill--active { background: #ff6f00; color: white; border-color: #ff6f00; }
```

---

### Badges de Variação

Indicadores de variação 24h (positiva / negativa / neutra).

| Variante | Cor texto | Background |
|----------|-----------|------------|
| Up | `#15a96f` | `#e8f8f0` |
| Down | `#e43b3b` | `#fdeaea` |
| Neutral | `#8d959d` | `#ecedef` |

```html
<span class="fx-badge fx-badge--up">▲ 4,22%</span>
<span class="fx-badge fx-badge--down">▼ 3,46%</span>
<span class="fx-badge fx-badge--neutral">0,00%</span>
```

---

### Tabela da Carteira

Layout principal da homepage. Colunas: Ativo (ícone+nome), Preço em R$, Saldo em aberto, Saldo.

```css
.fx-table { border-collapse: collapse; background: white; border-radius: 16px; overflow: hidden; }
.fx-table th { font-size: 12px; font-weight: 600; color: #8d959d; background: #f5f6f7; padding: 12px 16px; }
.fx-table td { font-size: 14px; padding: 14px 16px; border-bottom: 1px solid #ecedef; }
.fx-table tr:hover td { background: #f5f6f7; }
```

Ícones de moedas: `https://statics.foxbit.com.br/icons/colored/{ticker}.svg`
Exemplos: `btc.svg`, `eth.svg`, `usdt.svg`, `sol.svg`

---

### Cards

```css
.fx-card {
  background: white;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0,0,0,.06);
  border: 1px solid #ecedef;
}
```

---

### Collapsible / Accordion

Seções colapsáveis (Carteira, Destaques, VIP, Novidades).

```html
<details class="fx-collapsible" open>
  <summary>Carteira <span class="chevron">▼</span></summary>
  <div class="fx-collapsible-content">...</div>
</details>
```

---

### Menu Lateral (Sidebar)

```css
.fx-menu-item {
  display: flex; align-items: center; gap: 12px;
  padding: 10px 14px;
  border-radius: 12px;
  font-size: 14px; font-weight: 500;
  color: #212529;
}
.fx-menu-item:hover { background: #ecedef; }
.fx-menu-item--active { color: #ff6f00; background: #fff0e0; }
```

#### Itens do menu
| ID | Label | Ícone |
|----|-------|-------|
| home | Início | 🏠 casa |
| wallet | Depositar / Sacar | ↕ setas |
| convert | Conversão | 🔄 swap |
| buy-sell | Comprar / Vender | ↔ setas |
| markets | Explorar | 🌐 globo |
| order-book | Livro de Ofertas | 📊 gráfico |
| recurring-buy | Compra Recorrente | 🔁 ciclo |
| history | Meu Histórico | 📅 calendário |
| card | Foxbit Card | 💳 cartão |
| referral | Indique Amigos | 👥 pessoas |
| foxbit-earn | Foxbit Earn | ⚡ earn |
| foxbit-pay | Foxbit Pay | 👋 pay |
| crypto-assets | Crypto Assets | 💎 token |
| profile | Configurações | ⚙ engrenagem |
| logout | Sair | 🚪 porta (vermelho) |

---

### User Button

Botão no header com avatar + nome + dropdown.

```css
.fx-user-btn {
  display: inline-flex; align-items: center; gap: 12px;
  border-radius: 999px; padding: 8px;
  font-size: 14px; font-weight: 500;
  text-transform: capitalize;
}
.fx-user-btn:hover { background: #ecedef; }
.fx-user-avatar { width: 32px; height: 32px; border-radius: 50%; background: #f5f6f7; }
.fx-user-btn:hover .fx-user-avatar { background: #fff0e0; }
```

---

### Toggle Switch

Usado para modo escuro e configurações binárias.

```css
.fx-toggle {
  width: 50px; height: 24px;
  background: #b2b7bb; border-radius: 12px;
}
.fx-toggle:checked { background: #ff6f00; }
.fx-toggle::before { /* círculo branco 20x20 */ }
.fx-toggle:checked::before { transform: translateX(26px); }
```

---

### Progress Bar (VIP)

```css
.fx-progress { width: 100%; height: 8px; background: #ecedef; border-radius: 4px; }
.fx-progress-bar { height: 100%; background: #ff6f00; border-radius: 4px; }
```

Informações exibidas: Faixa atual, Próxima faixa, Taxas (Maker/Taker), Nível (INICIANTE, VIP, etc.)

---

### Highlight Cards

Cards de destaque com moeda, nome e variação. Usados em "Novidades", "Maiores altas", "Maiores baixas".

```html
<div class="fx-highlight-card">
  <img src="https://statics.foxbit.com.br/icons/colored/btc.svg" alt="BTC">
  <div>
    <div style="font-weight:600">Bitcoin</div>
    <div style="font-size:12px; color:#8d959d">btc</div>
  </div>
  <span class="fx-badge fx-badge--up">▲ 4,84%</span>
</div>
```

---

### Alertas / Flash Messages

3 variantes: erro, sucesso, informacional.

```html
<div class="fx-alert fx-alert--error">
  <svg><!-- ícone --></svg>
  <span>Mensagem de erro</span>
  <button class="fx-alert-close">✕</button>
</div>
```

| Variante | Background | Cor | Borda |
|----------|-----------|-----|-------|
| Error | `#fdeaea` | `#ff4747` | `rgba(228,59,59,.2)` |
| Success | `#e8f3ea` | `#1cc97d` | `rgba(28,201,125,.2)` |
| Info | `#e8f0fe` | `#1a73e8` | `rgba(26,115,232,.15)` |

---

### Notification Badge

Contador de notificações não lidas.

```css
.fx-notif-badge {
  min-width: 20px; height: 20px; padding: 0 5px;
  border-radius: 10px;
  background: #ff4747; color: white;
  font-size: 11px; font-weight: 700;
}
```

---

## CSS Variables (copiar e colar)

```css
:root {
  /* ── Cores ── */
  --colors-primary-g100: #fff0e0;
  --colors-primary-g500: #ff6f00;
  --colors-primary-g600: #e56300;

  --colors-mono-white: #ffffff;
  --colors-mono-black: #212427;
  --colors-mono-g50: #f5f6f7;
  --colors-mono-g100: #ecedef;
  --colors-mono-g200: #d5d7da;
  --colors-mono-g300: #b2b7bb;
  --colors-mono-g600: #8d959d;
  --colors-mono-g900: #212529;

  --colors-up: #15a96f;
  --colors-down: #e43b3b;
  --colors-error: #ff4747;
  --colors-success: #1cc97d;
  --colors-success-bg: #e8f3ea;

  /* ── Tipografia ── */
  --font-family: 'Reddit Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  --fs-xxs: 0.625rem;
  --fs-xs: 0.875rem;
  --fs-sm: 1rem;
  --fs-md: 1.125rem;
  --fs-lg: 1.25rem;
  --fs-xl: 1.5rem;
  --fs-xxl: 2rem;

  --fw-light: 300;
  --fw-regular: 400;
  --fw-medium: 500;
  --fw-semibold: 600;
  --fw-bold: 700;

  --lh-sm: 1.2;
  --lh-md: 1.4;
  --lh-lg: 1.6;

  /* ── Espaçamentos ── */
  --sp-xxxs: 0.25rem;
  --sp-xxs: 0.5rem;
  --sp-xs: 0.75rem;
  --sp-sm: 1rem;
  --sp-md: 1.25rem;
  --sp-lg: 1.5rem;
  --sp-xl: 2rem;
  --sp-xxl: 2.5rem;

  /* ── Bordas ── */
  --radius-xs: 4px;
  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 16px;
  --radius-xl: 20px;
  --radius-pill: 999px;

  /* ── Sombras ── */
  --shadow-card: 0 2px 8px rgba(0,0,0,.06);
  --shadow-dropdown: 0 4px 20px 0 hsla(0,0%,54%,.16), 0 4px 20px 0 rgba(0,0,0,.1);
  --shadow-elevated: 0 8px 32px rgba(0,0,0,.12);

  /* ── Z-Index ── */
  --z-dropdown: 100;
  --z-modal: 1000;
}

/* ── Dark Theme ── */
[data-theme="dark"] {
  --colors-mono-white: #1a1d21;
  --colors-mono-black: #f5f6f7;
  --colors-mono-g50: #22262b;
  --colors-mono-g100: #2c3138;
  --colors-mono-g200: #3a4049;
  --colors-mono-g900: #f5f6f7;
  --shadow-card: 0 2px 8px rgba(0,0,0,.2);
}
```

---

## Referência Visual

O arquivo `jr-design-system.html` (localizado na pasta `docs/`) contém todos os componentes renderizados interativamente.
Abra no navegador (`docs/jr-design-system.html`) para visualizar cada componente com seus estados.

---

## Checklist para novo projeto

- [ ] Adicionar fonte Reddit Sans via Google Fonts
- [ ] Copiar CSS variables para o `:root`
- [ ] Configurar dark theme com `data-theme="dark"`
- [ ] Usar `border-radius: 999px` como padrão para botões e inputs
- [ ] Usar ícones de moedas de `statics.foxbit.com.br/icons/colored/`
- [ ] Seguir escala de espaçamento (múltiplos de 4px)
- [ ] Manter hierarquia tipográfica (XXL → XXS)
