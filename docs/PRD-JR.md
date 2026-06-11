# PRD — Sistema JR

> **Produto:** JR — Sistema de Gestão Pessoal
> **Autor:** Jairo Rodrigues
> **Versão:** 1.0
> **Data:** 27/03/2026 (atualizado posteriormente)
> **Status:** Em definição

> **Nota:** O projeto foi implementado usando **MySQL** (banco externo via host.docker.internal) em vez de PostgreSQL. O `docker-compose.yml` atual não inclui container de banco de dados.

---

## 1. Visão Geral

O **JR** é um sistema web pessoal para o Jairo Rodrigues gerenciar sua vida financeira, agenda e tarefas em um único lugar. O objetivo é substituir planilhas, apps separados e anotações soltas por uma plataforma unificada, com visual inspirado no design system Foxbit e arquitetura moderna preparada para crescer.

### Por que construir?

- Ter controle total dos dados financeiros sem depender de apps terceiros
- Centralizar finanças, agenda e tarefas em um só lugar
- Ter liberdade para adicionar módulos novos no futuro
- Aprender e praticar Laravel + Livewire + Volt na prática

---

## 2. Stack Técnica

| Camada | Tecnologia                                              |
|--------|---------------------------------------------------------|
| **Backend** | PHP 8.3 + Laravel 12                                    |
| **Frontend** | Livewire 4 + Volt (single-file components)              |
| **Estilização** | Tailwind CSS + Design System (Arquivo design-system.md) |
| **Banco de dados** | MySQL 8+ (externo)                                      |
| **Cache/Sessão** | Redis                                                   |
| **Infraestrutura** | Docker + Docker Compose                                 |
| **Servidor web** | Nginx                                                   |
| **Fila de jobs** | Laravel Queue (Redis driver)                            |
| **Autenticação** | Laravel Breeze (simplificado, single user)              |
| **Testes** | Pest PHP                                                |

### Docker Compose — Serviços

| Serviço | Imagem | Porta |
|---------|--------|-------|
| `app` | PHP 8.3-FPM (custom Dockerfile) | — |
| `nginx` | nginx:alpine | `8080:80` |
| (sem container de banco — MySQL externo via host.docker.internal) | — | — |
| `redis` | redis:7-alpine | `6379:6379` |
| `queue` | Mesmo da app (artisan queue:work) | — |
| `scheduler` | Mesmo da app (artisan schedule:work) | — |

---

## 3. Usuários

Sistema **single-user** (apenas o Jairo). Não haverá cadastro público nem multi-tenancy. O login será por e-mail + senha com opção de 2FA futuramente.

---

## 4. Módulos

### 4.1 Dashboard

**Página inicial** com visão consolidada de tudo.

**Conteúdo:**
- Saldo total (soma de todas as contas)
- Resumo do mês: receitas vs despesas (barra ou gráfico simples)
- Próximas contas a vencer (7 dias)
- Fatura atual do cartão de crédito
- Próximos compromissos da agenda (hoje + amanhã)
- Tarefas pendentes (top 5 por prioridade)
- Evolução do patrimônio (mini gráfico)

---

### 4.2 Financeiro — Contas Bancárias

Gerenciar contas bancárias e saldos.

**Entidade: `Account`**

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | uuid | PK |
| `name` | string | Ex: "Nubank", "Foxbit", "Carteira" |
| `type` | enum | `checking`, `savings`, `investment`, `wallet`, `other` |
| `bank` | string (nullable) | Nome do banco |
| `initial_balance` | decimal(12,2) | Saldo inicial |
| `current_balance` | decimal(12,2) | Saldo calculado |
| `color` | string | Cor para identificação visual |
| `icon` | string (nullable) | Ícone opcional |
| `is_active` | boolean | Ativa/inativa |
| `created_at` | timestamp | — |
| `updated_at` | timestamp | — |

**Funcionalidades:**
- CRUD de contas
- Ver saldo atual de cada conta
- Ver extrato (transações vinculadas)
- Transferência entre contas

---

### 4.3 Financeiro — Transações

Registro de todas as movimentações (receitas e despesas).

**Entidade: `Transaction`**

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | uuid | PK |
| `account_id` | uuid | FK → Account |
| `category_id` | uuid | FK → Category |
| `credit_card_id` | uuid (nullable) | FK → CreditCard (se for gasto no cartão) |
| `type` | enum | `income`, `expense`, `transfer` |
| `description` | string | Descrição da transação |
| `amount` | decimal(12,2) | Valor |
| `date` | date | Data da transação |
| `due_date` | date (nullable) | Data de vencimento (contas a pagar) |
| `paid_at` | timestamp (nullable) | Data do pagamento efetivo |
| `is_paid` | boolean | Pago ou pendente |
| `is_recurring` | boolean | Se é recorrente |
| `recurrence_type` | enum (nullable) | `monthly`, `weekly`, `yearly` |
| `recurrence_end` | date (nullable) | Fim da recorrência |
| `installment_number` | integer (nullable) | Parcela atual (1/12) |
| `installment_total` | integer (nullable) | Total de parcelas |
| `notes` | text (nullable) | Observações |
| `tags` | json (nullable) | Tags livres |
| `created_at` | timestamp | — |
| `updated_at` | timestamp | — |

**Funcionalidades:**
- CRUD de transações
- Filtros: período, categoria, conta, tipo, pago/pendente
- Marcar como pago (com data)
- Transações recorrentes (gera automaticamente via scheduler)
- Transações parceladas
- Busca por descrição
- Totalizadores: total receitas, total despesas, saldo do período

---

### 4.4 Financeiro — Categorias

**Entidade: `Category`**

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | uuid | PK |
| `name` | string | Ex: "Alimentação", "Salário", "Lazer" |
| `type` | enum | `income`, `expense` |
| `color` | string | Cor do badge |
| `icon` | string (nullable) | Ícone |
| `parent_id` | uuid (nullable) | FK → Category (subcategorias) |
| `created_at` | timestamp | — |

**Categorias pré-definidas (seed):**

*Despesas:* Moradia, Alimentação, Transporte, Saúde, Educação, Lazer, Assinaturas, Vestuário, Pets, Impostos, Outros

*Receitas:* Salário, Freelance, Investimentos, Cashback, Outros

---

### 4.5 Financeiro — Cartão de Crédito

**Entidade: `CreditCard`**

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | uuid | PK |
| `name` | string | Ex: "Nubank Mastercard" |
| `last_digits` | string(4) | Últimos 4 dígitos |
| `brand` | enum | `visa`, `mastercard`, `elo`, `amex`, `other` |
| `credit_limit` | decimal(12,2) | Limite total |
| `closing_day` | integer | Dia de fechamento (1-31) |
| `due_day` | integer | Dia de vencimento (1-31) |
| `color` | string | Cor para identificação |
| `account_id` | uuid (nullable) | FK → Account (conta de pagamento) |
| `is_active` | boolean | — |
| `created_at` | timestamp | — |

**Entidade: `CreditCardInvoice`**

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | uuid | PK |
| `credit_card_id` | uuid | FK → CreditCard |
| `reference_month` | date | Mês de referência (2026-03-01) |
| `total_amount` | decimal(12,2) | Total da fatura (calculado) |
| `due_date` | date | Vencimento |
| `paid_at` | timestamp (nullable) | Data do pagamento |
| `is_paid` | boolean | — |
| `is_closed` | boolean | Fatura fechada? |

**Funcionalidades:**
- CRUD de cartões
- Ver fatura do mês (lista de transações do cartão)
- Navegar entre faturas (meses anteriores/próximos)
- Fechar fatura manualmente
- Marcar fatura como paga
- Ver limite disponível (limite - fatura aberta)
- Lançar compra parcelada no cartão

---

### 4.6 Financeiro — Investimentos

Acompanhar investimentos de forma simples (sem integração com corretoras por enquanto).

**Entidade: `Investment`**

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | uuid | PK |
| `name` | string | Ex: "Bitcoin", "Tesouro Selic 2029", "PETR4" |
| `type` | enum | `crypto`, `fixed_income`, `stocks`, `funds`, `other` |
| `broker` | string (nullable) | Ex: "Foxbit", "Rico", "Nubank" |
| `invested_amount` | decimal(14,2) | Total investido |
| `current_amount` | decimal(14,2) | Valor atual |
| `quantity` | decimal(18,8) (nullable) | Quantidade (ações, crypto) |
| `purchase_date` | date | Data da compra |
| `maturity_date` | date (nullable) | Vencimento (renda fixa) |
| `notes` | text (nullable) | Observações |
| `created_at` | timestamp | — |
| `updated_at` | timestamp | — |

**Funcionalidades:**
- CRUD de investimentos
- Atualizar valor atual manualmente
- Ver rentabilidade (% e R$)
- Visão consolidada: total investido, valor atual, rendimento total
- Distribuição por tipo (gráfico pizza)

---

### 4.7 Agenda

Calendário pessoal para compromissos e lembretes.

**Entidade: `Event`**

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | uuid | PK |
| `title` | string | Título do evento |
| `description` | text (nullable) | Detalhes |
| `start_at` | datetime | Início |
| `end_at` | datetime (nullable) | Fim |
| `is_all_day` | boolean | Dia inteiro? |
| `location` | string (nullable) | Local |
| `color` | string | Cor no calendário |
| `reminder_minutes` | integer (nullable) | Lembrete X min antes |
| `is_recurring` | boolean | Recorrente? |
| `recurrence_type` | enum (nullable) | `daily`, `weekly`, `monthly`, `yearly` |
| `recurrence_end` | date (nullable) | Fim da recorrência |
| `created_at` | timestamp | — |

**Funcionalidades:**
- Visualização mensal, semanal e diária
- CRUD de eventos
- Eventos recorrentes
- Cores diferentes por tipo
- Integração visual com contas a pagar (mostrar vencimentos no calendário)

---

### 4.8 Tarefas

Gerenciador de tarefas tipo to-do list com prioridades.

**Entidade: `Task`**

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | uuid | PK |
| `title` | string | Título |
| `description` | text (nullable) | Detalhes |
| `priority` | enum | `low`, `medium`, `high`, `urgent` |
| `status` | enum | `pending`, `in_progress`, `done`, `cancelled` |
| `due_date` | date (nullable) | Prazo |
| `completed_at` | timestamp (nullable) | Data da conclusão |
| `list_id` | uuid (nullable) | FK → TaskList (agrupamento) |
| `sort_order` | integer | Ordenação drag-and-drop |
| `created_at` | timestamp | — |
| `updated_at` | timestamp | — |

**Entidade: `TaskList`**

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | uuid | PK |
| `name` | string | Ex: "Pessoal", "Casa", "Projetos" |
| `color` | string | Cor |
| `sort_order` | integer | Ordenação |

**Funcionalidades:**
- CRUD de tarefas e listas
- Marcar como concluída (checkbox)
- Filtrar por: lista, prioridade, status, prazo
- Ordenação por drag-and-drop
- Contagem de pendentes por lista

---

## 5. Estrutura de Rotas

```
/                          → Dashboard
/financeiro
  /contas                  → Contas bancárias
  /transacoes              → Transações (receitas/despesas)
  /categorias              → Categorias
  /cartoes                 → Cartões de crédito
  /cartoes/{id}/fatura     → Fatura do cartão
  /investimentos           → Investimentos
/agenda                    → Calendário
/tarefas                   → Lista de tarefas
/configuracoes             → Perfil, preferências
```

---

## 6. Estrutura de Pastas (Laravel)

```
jr/
├── docker/
│   ├── nginx/
│   │   └── default.conf
│   └── php/
│       └── Dockerfile
├── docker-compose.yml
├── .env.example
├── app/
│   ├── Models/
│   │   ├── Account.php
│   │   ├── Transaction.php
│   │   ├── Category.php
│   │   ├── CreditCard.php
│   │   ├── CreditCardInvoice.php
│   │   ├── Investment.php
│   │   ├── Event.php
│   │   ├── Task.php
│   │   └── TaskList.php
│   ├── Enums/
│   │   ├── AccountType.php
│   │   ├── TransactionType.php
│   │   ├── CardBrand.php
│   │   ├── InvestmentType.php
│   │   ├── Priority.php
│   │   ├── TaskStatus.php
│   │   └── RecurrenceType.php
│   ├── Services/
│   │   ├── BalanceService.php
│   │   ├── InvoiceService.php
│   │   └── RecurrenceService.php
│   └── Console/Commands/
│       └── GenerateRecurringTransactions.php
├── resources/
│   ├── views/
│   │   ├── livewire/         ← Volt single-file components
│   │   │   ├── dashboard.blade.php
│   │   │   ├── financeiro/
│   │   │   │   ├── contas.blade.php
│   │   │   │   ├── transacoes.blade.php
│   │   │   │   ├── categorias.blade.php
│   │   │   │   ├── cartoes.blade.php
│   │   │   │   ├── fatura.blade.php
│   │   │   │   └── investimentos.blade.php
│   │   │   ├── agenda/
│   │   │   │   └── calendario.blade.php
│   │   │   └── tarefas/
│   │   │       └── index.blade.php
│   │   ├── components/       ← Blade components reutilizáveis
│   │   │   ├── layouts/
│   │   │   │   └── app.blade.php
│   │   │   ├── sidebar.blade.php
│   │   │   ├── header.blade.php
│   │   │   ├── card.blade.php
│   │   │   ├── badge.blade.php
│   │   │   ├── input.blade.php
│   │   │   ├── button.blade.php
│   │   │   ├── modal.blade.php
│   │   │   ├── alert.blade.php
│   │   │   └── table.blade.php
│   │   └── layouts/
│   │       └── app.blade.php
│   └── css/
│       └── app.css           ← Tailwind + tokens do design system
├── database/
│   ├── migrations/
│   └── seeders/
│       └── CategorySeeder.php
├── docs/
│   ├── design-system.md
│   ├── jr-design-system.html
│   └── PRD-JR.md            ← este arquivo
└── tests/
    └── Feature/
```

---

## 7. Docker Compose

```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    volumes:
      - .:/var/www/html
    depends_on:
      - redis
    networks:
      - jr-network

  nginx:
    image: nginx:alpine
    ports:
      - "8080:80"
    volumes:
      - .:/var/www/html
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - app
    networks:
      - jr-network

  # Banco de dados MySQL roda externamente (host.docker.internal)
  # Nao ha servico de banco neste compose

  redis:
    image: redis:7-alpine
    ports:
      - "6379:6379"
    networks:
      - jr-network

  queue:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    command: php artisan queue:work --sleep=3 --tries=3
    volumes:
      - .:/var/www/html
    depends_on:
      - app
      - redis
    networks:
      - jr-network

  scheduler:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    command: php artisan schedule:work
    volumes:
      - .:/var/www/html
    depends_on:
      - app
    networks:
      - jr-network

volumes:
networks:
  jr-network:
    driver: bridge
```

---

## 8. Regras de Negócio

### Financeiro

1. **Saldo da conta** = saldo inicial + soma de receitas - soma de despesas (apenas transações pagas)
2. **Transferência** entre contas gera 2 transações: uma `expense` na origem, uma `income` no destino
3. **Transação recorrente**: scheduler roda diariamente e cria transações futuras até 30 dias à frente
4. **Parcelas do cartão**: ao lançar compra parcelada, cria N transações (uma por parcela) vinculadas às faturas corretas
5. **Fatura do cartão**: agrupa transações entre o dia de fechamento do mês anterior e o fechamento atual
6. **Fatura fechada**: não permite edição das transações vinculadas
7. **Investimento**: rentabilidade = ((valor_atual - valor_investido) / valor_investido) × 100

### Agenda

8. **Eventos recorrentes**: gera instâncias no calendário sem duplicar no banco
9. **Vencimentos financeiros** aparecem automaticamente no calendário como eventos especiais

### Tarefas

10. **Ordenação**: drag-and-drop atualiza o campo `sort_order`
11. **Ao concluir**: preenche `completed_at` com timestamp atual
12. **Tarefas vencidas**: highlight visual para tarefas com `due_date` passado e `status != done`

---

## 9. Fases de Desenvolvimento

### Fase 1 — Fundação (Semana 1-2)
- [ ] Setup Docker (compose + Dockerfile + nginx)
- [ ] Criar projeto Laravel 11
- [ ] Configurar Livewire 3 + Volt
- [ ] Configurar Tailwind CSS com tokens do design system
- [ ] Layout base: sidebar, header, estrutura de página
- [ ] Autenticação (login simples, single user)
- [ ] Criar migrations de todas as entidades
- [ ] Seeders de categorias

### Fase 2 — Financeiro Core (Semana 3-4)
- [ ] CRUD de Contas Bancárias
- [ ] CRUD de Categorias
- [ ] CRUD de Transações (receitas/despesas)
- [ ] Filtros e busca de transações
- [ ] Marcar transação como paga
- [ ] Transferência entre contas
- [ ] Cálculo automático de saldo

### Fase 3 — Cartão de Crédito (Semana 5)
- [ ] CRUD de Cartões
- [ ] Visualização de fatura
- [ ] Navegação entre meses
- [ ] Lançar compra parcelada
- [ ] Fechamento e pagamento de fatura

### Fase 4 — Dashboard (Semana 6)
- [ ] Página inicial com resumo
- [ ] Gráfico receitas vs despesas
- [ ] Próximas contas a vencer
- [ ] Mini cards de saldo, fatura, investimentos

### Fase 5 — Investimentos (Semana 7)
- [ ] CRUD de Investimentos
- [ ] Atualização de valor atual
- [ ] Visão consolidada + rentabilidade
- [ ] Gráfico de distribuição por tipo

### Fase 6 — Agenda (Semana 8)
- [ ] Visualização de calendário (mês/semana/dia)
- [ ] CRUD de eventos
- [ ] Eventos recorrentes
- [ ] Vencimentos financeiros no calendário

### Fase 7 — Tarefas (Semana 9)
- [ ] CRUD de Listas e Tarefas
- [ ] Checkbox de conclusão
- [ ] Filtros por lista/prioridade/status
- [ ] Drag-and-drop para ordenação

### Fase 8 — Polimento (Semana 10)
- [ ] Transações recorrentes automáticas (scheduler)
- [ ] Testes com Pest
- [ ] Dark mode
- [ ] Responsividade mobile
- [ ] Ajustes finais de UX

---

## 10. Futuras Expansões (pós v1)

| Módulo | Descrição |
|--------|-----------|
| **Metas financeiras** | Definir metas de economia e acompanhar progresso |
| **Relatórios** | Relatórios mensais/anuais em PDF |
| **Importação** | Importar extrato bancário (CSV/OFX) |
| **Notificações** | Alertas de vencimento via e-mail ou Telegram |
| **API de cotações** | Atualizar investimentos em crypto automaticamente |
| **Notas/Documentos** | Guardar notas, contratos, comprovantes |
| **Controle de assinaturas** | Gerenciar serviços recorrentes (Netflix, etc) |
| **App mobile** | PWA ou app nativo |
| **Backup automático** | Backup do banco via S3 |

---

## 11. Design System

O visual do sistema segue os tokens documentados em `design-system.md`, baseados na identidade visual da Foxbit.

**Referências na pasta `/docs`:**
- `design-system.md` — Tokens, componentes, código CSS
- `jr-design-system.html` — Referência visual interativa

**Elementos-chave:**
- Fonte: Reddit Sans
- Cor primária: `#ff6f00` (laranja)
- Botões e inputs: formato **pill** (border-radius: 999px)
- Cards: border-radius 16px, sombra sutil
- Sidebar: menu com ícones, item ativo com fundo laranja claro
