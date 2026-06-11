# Plano de Desenvolvimento — Sistema JR

> **Baseado em:** PRD-JR.md + design-system.md
> **Stack:** Laravel 12 + Livewire 4/Volt + Tailwind CSS + MySQL (externo) + Redis + Docker
> **Criado em:** 27/03/2026

> **Nota (2026-06):** O projeto foi implementado com **MySQL** rodando externamente (via `host.docker.internal`). O `docker-compose.yml` final nao inclui servico de banco de dados (postgres foi planejado inicialmente mas removido).

---

## Fase 1 — Fundacao e Infraestrutura (Semana 1-2)

### 1.1 Docker e Ambiente

- [ ] Criar `docker/php/Dockerfile` (PHP 8.3-FPM com extensoes: pgsql, pdo_pgsql, redis, bcmath, gd, zip, intl)
- [ ] Criar `docker/nginx/default.conf` (fastcgi_pass para o container app, root em /var/www/html/public)
- [ ] Criar `docker-compose.yml` com 6 servicos: app, nginx (8080:80), redis, queue, scheduler, reverb
- [ ] Configurar MySQL externo (acessado via host.docker.internal no .env)
- [ ] Criar `.env.example` com variaveis de conexao (DB_CONNECTION=mysql, DB_HOST=host.docker.internal, REDIS_HOST=redis, etc.)
- [ ] Subir containers e validar que todos os servicos estao rodando

### 1.2 Projeto Laravel

- [ ] Criar projeto Laravel 12 via Composer (`composer create-project laravel/laravel .`)
- [ ] Instalar dependencias: `livewire/livewire`, `livewire/volt`
- [ ] Instalar Laravel Breeze para autenticacao simplificada
- [ ] Configurar `.env` para MySQL e Redis (cache, session, queue)
- [ ] Rodar `php artisan migrate` para validar conexao com o banco
- [ ] Configurar Pest PHP como framework de testes

### 1.3 Tailwind CSS + Design System

- [ ] Instalar Tailwind CSS via Vite
- [ ] Criar `resources/css/app.css` com as CSS Variables do design system (bloco `:root` completo)
- [ ] Adicionar variaveis de dark theme (`[data-theme="dark"]`)
- [ ] Configurar `tailwind.config.js` com cores customizadas:
  - `primary: { 100: '#fff0e0', 500: '#ff6f00', 600: '#e56300' }`
  - `mono: { 50, 100, 200, 300, 600, 900 }`
  - `success, error, up, down`
- [ ] Configurar fonte Reddit Sans (Google Fonts import no layout)
- [ ] Configurar `borderRadius: { pill: '999px' }` como padrao de botoes/inputs
- [ ] Configurar escala de espacamentos (multiplos de 4px)

### 1.4 Layout Base

- [ ] Criar layout principal: `resources/views/components/layouts/app.blade.php`
  - Estrutura: sidebar fixa a esquerda (240px) + area de conteudo a direita
  - Include da fonte Reddit Sans no `<head>`
  - Suporte a `data-theme="dark"` no `<body>`
- [ ] Criar componente `sidebar.blade.php` com todos os itens de menu (15 itens conforme design system)
  - Itens: Inicio, Depositar/Sacar, Conversao, Comprar/Vender, Explorar, Livro de Ofertas, Compra Recorrente, Meu Historico, Foxbit Card, Indique Amigos, Foxbit Earn, Foxbit Pay, Crypto Assets, Configuracoes, Sair
  - Estado ativo: fundo `#fff0e0` + texto `#ff6f00`
  - **Nota:** Adaptar labels do menu para o contexto do sistema JR (ex: Inicio, Financeiro, Agenda, Tarefas, Configuracoes)
- [ ] Criar componente `header.blade.php` com user button (avatar + nome + dropdown)
- [ ] Validar visual: sidebar + header + area de conteudo renderizando corretamente

### 1.5 Componentes Blade Reutilizaveis

- [ ] `components/button.blade.php` — 4 variantes (primary, standard, mono, text) + tamanho sm, formato pill
- [ ] `components/input.blade.php` — Input pill com estados (default, focus, success, error, disabled), icone esquerdo/direito
- [ ] `components/card.blade.php` — Card padrao (border-radius 16px, shadow-card, borda #ecedef)
- [ ] `components/badge.blade.php` — Badges de variacao (up/down/neutral) + badges genericos
- [ ] `components/modal.blade.php` — Modal com overlay, shadow-elevated, z-index 1000
- [ ] `components/alert.blade.php` — 3 variantes (error, success, info) com icone e botao fechar
- [ ] `components/table.blade.php` — Tabela estilizada (header cinza, hover nas linhas, border-radius 16px)

### 1.6 Autenticacao

- [ ] Configurar Laravel Breeze (blade stack)
- [ ] Customizar telas de login com design system (inputs pill, botao primary laranja)
- [ ] Criar seeder para usuario unico (Jairo Rodrigues)
- [ ] Proteger todas as rotas com middleware `auth`
- [ ] Testar fluxo de login/logout

### 1.7 Migrations

Criar todas as migrations na ordem correta de dependencias:

- [ ] `create_accounts_table` — uuid PK, name, type (enum), bank, initial_balance, current_balance, color, icon, is_active
- [ ] `create_categories_table` — uuid PK, name, type (enum), color, icon, parent_id (self-ref FK)
- [ ] `create_credit_cards_table` — uuid PK, name, last_digits, brand (enum), credit_limit, closing_day, due_day, color, account_id (FK), is_active
- [ ] `create_credit_card_invoices_table` — uuid PK, credit_card_id (FK), reference_month, total_amount, due_date, paid_at, is_paid, is_closed
- [ ] `create_transactions_table` — uuid PK, account_id (FK), category_id (FK), credit_card_id (FK nullable), type (enum), description, amount, date, due_date, paid_at, is_paid, is_recurring, recurrence_type, recurrence_end, installment_number, installment_total, notes, tags (json)
- [ ] `create_investments_table` — uuid PK, name, type (enum), broker, invested_amount, current_amount, quantity, purchase_date, maturity_date, notes
- [ ] `create_events_table` — uuid PK, title, description, start_at, end_at, is_all_day, location, color, reminder_minutes, is_recurring, recurrence_type, recurrence_end
- [ ] `create_task_lists_table` — uuid PK, name, color, sort_order
- [ ] `create_tasks_table` — uuid PK, title, description, priority (enum), status (enum), due_date, completed_at, list_id (FK), sort_order

### 1.8 Enums e Models

- [ ] Criar Enums PHP:
  - `AccountType`: checking, savings, investment, wallet, other
  - `TransactionType`: income, expense, transfer
  - `CardBrand`: visa, mastercard, elo, amex, other
  - `InvestmentType`: crypto, fixed_income, stocks, funds, other
  - `Priority`: low, medium, high, urgent
  - `TaskStatus`: pending, in_progress, done, cancelled
  - `RecurrenceType`: daily, weekly, monthly, yearly
- [ ] Criar Models com UUIDs, fillable, casts e relationships:
  - `Account` (hasMany Transaction)
  - `Category` (hasMany Transaction, belongsTo parent Category)
  - `CreditCard` (hasMany CreditCardInvoice, belongsTo Account)
  - `CreditCardInvoice` (belongsTo CreditCard, hasMany Transaction)
  - `Transaction` (belongsTo Account, Category, CreditCard)
  - `Investment`
  - `Event`
  - `TaskList` (hasMany Task)
  - `Task` (belongsTo TaskList)

### 1.9 Seeders

- [ ] `CategorySeeder` com categorias pre-definidas:
  - Despesas: Moradia, Alimentacao, Transporte, Saude, Educacao, Lazer, Assinaturas, Vestuario, Pets, Impostos, Outros
  - Receitas: Salario, Freelance, Investimentos, Cashback, Outros
- [ ] `UserSeeder` — usuario unico Jairo
- [ ] `DatabaseSeeder` — orquestrar seeders

---

## Fase 2 — Financeiro Core (Semana 3-4)

### 2.1 Contas Bancarias

- [ ] Criar pagina Volt: `livewire/financeiro/contas.blade.php`
- [ ] Listagem de contas em cards (icone + nome + banco + saldo atual + cor de identificacao)
- [ ] Modal para criar/editar conta (campos: nome, tipo, banco, saldo inicial, cor, icone)
- [ ] Exclusao com confirmacao (soft delete ou hard com validacao de transacoes vinculadas)
- [ ] Toggle ativar/desativar conta
- [ ] Card totalizador: soma de saldos de todas as contas ativas
- [ ] Rota: `/financeiro/contas`

### 2.2 Categorias

- [ ] Criar pagina Volt: `livewire/financeiro/categorias.blade.php`
- [ ] Listagem separada por tipo (Receitas / Despesas)
- [ ] Cada categoria exibe: icone + nome + cor + contagem de transacoes
- [ ] CRUD com modal (nome, tipo, cor, icone, subcategoria pai)
- [ ] Proteger contra exclusao de categoria com transacoes vinculadas
- [ ] Rota: `/financeiro/categorias`

### 2.3 Transacoes

- [ ] Criar pagina Volt: `livewire/financeiro/transacoes.blade.php`
- [ ] Listagem em tabela (design system: tabela da carteira) com colunas: data, descricao, categoria (badge com cor), conta, valor, status (pago/pendente)
- [ ] Filtros (category pills):
  - Periodo (data inicio / data fim)
  - Categoria (select)
  - Conta (select)
  - Tipo (receita/despesa/transferencia)
  - Status (pago/pendente)
- [ ] Busca por descricao (input de busca pill)
- [ ] Modal para criar/editar transacao com todos os campos
- [ ] Botao "Marcar como pago" que preenche `paid_at` com data atual
- [ ] Totalizadores no topo: total receitas, total despesas, saldo do periodo
- [ ] Paginacao
- [ ] Rota: `/financeiro/transacoes`

### 2.4 Transferencia entre Contas

- [ ] Modal especifico de transferencia (conta origem, conta destino, valor, data, descricao)
- [ ] Ao salvar, criar 2 transacoes atomicamente:
  - `expense` na conta de origem
  - `income` na conta de destino
  - Ambas com `type = transfer`
- [ ] Atualizar saldos de ambas as contas

### 2.5 Service: BalanceService

- [ ] Criar `app/Services/BalanceService.php`
- [ ] Metodo `recalculate(Account $account)`: saldo_inicial + SUM(receitas pagas) - SUM(despesas pagas)
- [ ] Chamar automaticamente ao criar/editar/excluir transacao ou marcar como paga
- [ ] Testes unitarios para calculo de saldo

---

## Fase 3 — Cartao de Credito (Semana 5)

### 3.1 CRUD de Cartoes

- [ ] Criar pagina Volt: `livewire/financeiro/cartoes.blade.php`
- [ ] Listagem em cards visuais (simulando cartao): nome, bandeira, ultimos 4 digitos, limite, cor
- [ ] Card exibe: limite total, fatura atual, limite disponivel
- [ ] Modal criar/editar cartao (nome, ultimos digitos, bandeira, limite, dia fechamento, dia vencimento, cor, conta de pagamento)
- [ ] Toggle ativar/desativar
- [ ] Rota: `/financeiro/cartoes`

### 3.2 Fatura do Cartao

- [ ] Criar pagina Volt: `livewire/financeiro/fatura.blade.php`
- [ ] Navegacao entre meses (botoes anterior/proximo)
- [ ] Listagem de transacoes da fatura (agrupadas entre fechamento anterior e fechamento atual)
- [ ] Totalizador da fatura
- [ ] Status visual: aberta, fechada, paga
- [ ] Botao "Fechar fatura" (bloqueia edicao das transacoes)
- [ ] Botao "Pagar fatura" (preenche paid_at, cria transacao de pagamento na conta vinculada)
- [ ] Rota: `/financeiro/cartoes/{id}/fatura`

### 3.3 Compra Parcelada

- [ ] No modal de nova transacao, quando `credit_card_id` selecionado, exibir campo de parcelas
- [ ] Ao salvar: criar N transacoes (1 por parcela) com `installment_number` e `installment_total`
- [ ] Cada parcela vinculada a fatura do mes correto
- [ ] Descricao automatica: "Descricao (1/12)", "Descricao (2/12)", etc.

### 3.4 Service: InvoiceService

- [ ] Criar `app/Services/InvoiceService.php`
- [ ] Metodo `getOrCreateInvoice(CreditCard, referenceMonth)` — retorna ou cria fatura do mes
- [ ] Metodo `calculateTotal(CreditCardInvoice)` — soma transacoes vinculadas
- [ ] Metodo `closeInvoice(CreditCardInvoice)` — marca como fechada
- [ ] Metodo `payInvoice(CreditCardInvoice)` — marca como paga + cria transacao na conta vinculada
- [ ] Testes

---

## Fase 4 — Dashboard (Semana 6)

### 4.1 Pagina Principal

- [ ] Criar pagina Volt: `livewire/dashboard.blade.php`
- [ ] Rota: `/` (home)

### 4.2 Cards de Resumo (topo)

- [ ] Saldo total (soma de todas as contas ativas) — card com valor grande em destaque
- [ ] Receitas do mes — card com valor em verde (#15a96f)
- [ ] Despesas do mes — card com valor em vermelho (#e43b3b)
- [ ] Fatura atual do cartao principal — card com valor + status

### 4.3 Grafico Receitas vs Despesas

- [ ] Grafico de barras simples (ultimos 6 meses)
- [ ] Usar biblioteca JS leve (Chart.js ou Alpine.js inline)
- [ ] Barras verdes (receitas) e vermelhas (despesas) lado a lado

### 4.4 Proximas Contas a Vencer

- [ ] Lista das transacoes pendentes com `due_date` nos proximos 7 dias
- [ ] Exibir: descricao, valor, data de vencimento, categoria (badge)
- [ ] Highlight visual para contas vencidas (overdue)
- [ ] Botao rapido "Marcar como pago"

### 4.5 Mini Graficos e Widgets

- [ ] Evolucao do patrimonio (mini line chart — ultimos 12 meses)
- [ ] Proximos compromissos da agenda (hoje + amanha)
- [ ] Top 5 tarefas pendentes por prioridade

---

## Fase 5 — Investimentos (Semana 7)

### 5.1 CRUD de Investimentos

- [ ] Criar pagina Volt: `livewire/financeiro/investimentos.blade.php`
- [ ] Listagem em tabela: nome, tipo (badge), corretora, valor investido, valor atual, rentabilidade (badge up/down)
- [ ] Modal criar/editar investimento
- [ ] Botao "Atualizar valor atual" (atualiza manualmente)
- [ ] Rota: `/financeiro/investimentos`

### 5.2 Visao Consolidada

- [ ] Cards no topo: total investido, valor atual total, rendimento total (R$ e %)
- [ ] Rentabilidade: `((valor_atual - valor_investido) / valor_investido) * 100`
- [ ] Badge up/down conforme rentabilidade positiva/negativa

### 5.3 Distribuicao por Tipo

- [ ] Grafico pizza/donut: distribuicao do patrimonio por tipo de investimento (crypto, renda fixa, acoes, fundos, outros)
- [ ] Legenda com valores absolutos e percentuais

---

## Fase 6 — Agenda (Semana 8)

### 6.1 Calendario

- [ ] Criar pagina Volt: `livewire/agenda/calendario.blade.php`
- [ ] Visualizacao mensal (grid de dias), semanal e diaria
- [ ] Navegacao entre meses/semanas
- [ ] Rota: `/agenda`

### 6.2 CRUD de Eventos

- [ ] Modal criar/editar evento (titulo, descricao, data/hora inicio e fim, dia inteiro, local, cor, lembrete)
- [ ] Exibir eventos como blocos coloridos no calendario
- [ ] Clicar no evento abre detalhes

### 6.3 Eventos Recorrentes

- [ ] Suporte a recorrencia (diaria, semanal, mensal, anual)
- [ ] Gerar instancias visuais no calendario SEM duplicar no banco
- [ ] Definir data de fim da recorrencia

### 6.4 Integracao com Financeiro

- [ ] Exibir vencimentos de transacoes pendentes como eventos especiais no calendario
- [ ] Visual diferenciado (icone de dinheiro, cor de alerta)
- [ ] Clicar redireciona para a transacao

---

## Fase 7 — Tarefas (Semana 9)

### 7.1 CRUD de Listas e Tarefas

- [ ] Criar pagina Volt: `livewire/tarefas/index.blade.php`
- [ ] Sidebar com listas de tarefas (nome + cor + contagem de pendentes)
- [ ] CRUD de listas (nome, cor)
- [ ] Lista de tarefas da lista selecionada
- [ ] CRUD de tarefa (titulo, descricao, prioridade, prazo, lista)
- [ ] Rota: `/tarefas`

### 7.2 Interacoes

- [ ] Checkbox para marcar como concluida (preenche `completed_at`)
- [ ] Tarefas concluidas ficam com texto riscado e opacidade reduzida
- [ ] Highlight visual para tarefas vencidas (due_date < hoje e status != done) — borda vermelha ou badge "Atrasada"

### 7.3 Filtros e Ordenacao

- [ ] Filtros por: lista, prioridade (category pills), status
- [ ] Drag-and-drop para reordenar tarefas (atualiza `sort_order`)
- [ ] Usar Livewire + SortableJS para drag-and-drop

---

## Fase 8 — Polimento e Finalizacao (Semana 10)

### 8.1 Transacoes Recorrentes

- [ ] Criar `app/Console/Commands/GenerateRecurringTransactions.php`
- [ ] Criar `app/Services/RecurrenceService.php`
- [ ] Logica: buscar transacoes com `is_recurring = true`, gerar transacoes futuras ate 30 dias a frente
- [ ] Registrar no scheduler do Laravel (rodar diariamente)
- [ ] Testes

### 8.2 Dark Mode

- [ ] Implementar toggle de tema (claro/escuro) no header
- [ ] Alternar `data-theme="dark"` no body
- [ ] Persistir preferencia no localStorage + banco (configuracoes do usuario)
- [ ] Validar todos os componentes no dark mode

### 8.3 Responsividade Mobile

- [ ] Sidebar colapsavel em telas < 768px (hamburger menu)
- [ ] Tabelas com scroll horizontal em mobile
- [ ] Cards empilhados em coluna unica
- [ ] Modais em fullscreen no mobile
- [ ] Testar em resolucoes: 375px, 768px, 1024px, 1440px

### 8.4 Testes com Pest

- [ ] Testes de Feature para cada modulo:
  - Autenticacao (login/logout)
  - CRUD de contas bancarias
  - CRUD de transacoes + calculo de saldo
  - Transferencia entre contas
  - Fatura do cartao (criacao, fechamento, pagamento)
  - Compra parcelada
  - CRUD de investimentos + rentabilidade
  - CRUD de eventos
  - CRUD de tarefas + conclusao
- [ ] Testes unitarios para Services (BalanceService, InvoiceService, RecurrenceService)
- [ ] Rodar testes no CI (dentro do container Docker)

### 8.5 Ajustes Finais de UX

- [ ] Feedback visual em todas as acoes (alerts/flash messages do design system)
- [ ] Loading states nos botoes durante requests Livewire
- [ ] Confirmacao antes de exclusoes
- [ ] Validacao de formularios com mensagens claras
- [ ] Empty states para listas vazias (mensagem + ilustracao)
- [ ] Breadcrumbs de navegacao
- [ ] Favicon e titulo da pagina dinamico

---

## Estrutura de Rotas Final

```
GET  /                              → Dashboard
GET  /financeiro/contas             → Contas Bancarias
GET  /financeiro/transacoes         → Transacoes
GET  /financeiro/categorias         → Categorias
GET  /financeiro/cartoes            → Cartoes de Credito
GET  /financeiro/cartoes/{id}/fatura → Fatura do Cartao
GET  /financeiro/investimentos      → Investimentos
GET  /agenda                        → Calendario
GET  /tarefas                       → Tarefas
GET  /configuracoes                 → Configuracoes do Usuario
```

---

## Dependencias entre Fases

```
Fase 1 (Fundacao)
  └── Fase 2 (Financeiro Core)
        ├── Fase 3 (Cartao de Credito)
        └── Fase 4 (Dashboard) ← depende tambem de Fase 3, 5, 6, 7
  └── Fase 5 (Investimentos)
  └── Fase 6 (Agenda) ← integra com transacoes da Fase 2
  └── Fase 7 (Tarefas)
Fase 8 (Polimento) ← depende de todas as anteriores
```

> **Nota:** Fases 5, 6 e 7 podem ser desenvolvidas em paralelo apos a Fase 2, pois sao modulos independentes. A Fase 4 (Dashboard) pode ser construida incrementalmente conforme cada modulo fica pronto.

---

## Checklist Pre-Desenvolvimento

- [ ] Ter Docker Desktop instalado e funcionando
- [ ] Ter Composer instalado (ou usar via Docker)
- [ ] Ter Node.js 20+ instalado (para Vite/Tailwind)
- [ ] Ter o arquivo `design-system.md` acessivel para referencia
- [ ] Ter o `foxbit-design-system.html` para referencia visual
