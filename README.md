# Sistema JR

Sistema completo de gerenciamento financeiro pessoal construido com Laravel 12, Livewire 4, Tailwind CSS e MySQL.

![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)
![Livewire](https://img.shields.io/badge/Livewire-4-FB70A9?logo=livewire&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?logo=docker&logoColor=white)

---

## Funcionalidades

### Financeiro
- **Contas bancarias** — CRUD com saldo calculado automaticamente, toggle ativo/inativo
- **Transacoes** — Receitas, despesas e transferencias com filtros avancados, paginacao e status de pagamento
- **Categorias** — Organizacao por tipo (receita/despesa) com icones, cores e subcategorias
- **Cartoes de credito** — Gestao de cartoes com limite, dia de fechamento e vencimento
- **Faturas** — Navegacao mensal, fechamento, reabertura e pagamento de faturas com lancamento automatico na conta vinculada
- **Compras parceladas** — Distribuicao automatica de parcelas nas faturas corretas baseado no dia de fechamento
- **Investimentos** — CRUD com atualizacao de valor, rentabilidade e grafico de distribuicao por tipo
- **Comparacao Financeira** — Comparacao lado a lado de periodos (meses, trimestres, anos) com filtros, diferencas e breakdown por categoria
- **Transacoes recorrentes** — Geracao automatica via scheduler diario

### Dashboard
- Cards resumo (saldo total, receitas, despesas, fatura atual)
- Grafico de receitas vs despesas (6 meses)
- Evolucao patrimonial (12 meses)
- Contas a pagar proximas e atrasadas
- Proximos eventos da agenda
- Top 5 tarefas pendentes

### Agenda
- Calendario com 3 visualizacoes (mes, semana, dia)
- CRUD de eventos com recorrencia (diaria, semanal, mensal, anual)
- Integracao com transacoes financeiras pendentes
- Mini calendario de navegacao

### Tarefas
- Listas de tarefas com cores personalizaveis
- CRUD de tarefas com prioridade (urgente, alta, media, baixa)
- Status (pendente, em progresso, concluida)
- Drag-and-drop para reordenacao
- Filtros por lista, prioridade e status
- Destaque de tarefas atrasadas

### Sistema
- Dark mode com persistencia via localStorage
- Layout responsivo (mobile-first)
- Design system proprio (7 componentes Blade `x-jr.*`)
- Pagina de configuracoes (perfil, senha, aparencia)

---

## Stack Tecnologica

| Camada | Tecnologia |
|---|---|
| Backend | PHP 8.3, Laravel 12 |
| Frontend | Livewire 4, Alpine.js 3, Tailwind CSS 3 |
| Banco de Dados | MySQL 8+ |
| Cache/Sessao/Fila | Redis 7 |
| Graficos | Chart.js 4 |
| Infra | Docker Compose (6 servicos) |
| Testes | Pest 3 |

---

## Estrutura do Projeto

```
app/
├── Console/Commands/       # Comando de transacoes recorrentes
├── Enums/                  # 7 enums (AccountType, TransactionType, Priority, etc.)
├── Livewire/               # 11 componentes Livewire
│   ├── Agenda/             #   Calendario
│   ├── Financeiro/         #   Contas, Transacoes, Categorias, Cartoes, Fatura, Investimentos, Transferencia
│   ├── Tarefas/            #   Index (listas + tarefas)
│   ├── Configuracoes.php
│   └── Dashboard.php
├── Models/                 # 9 models com UUID
└── Services/               # BalanceService, InvoiceService, RecurrenceService

resources/views/
├── components/jr/          # 7 componentes do design system
├── layouts/                # app, sidebar, header
└── livewire/               # Views dos componentes Livewire

database/migrations/        # 12 migrations (users, accounts, categories, credit_cards,
                            #   invoices, transactions, investments, events, task_lists, tasks)
```

---

## Instalacao

### Pre-requisitos

- Docker e Docker Compose

### Setup

```bash
# Clonar o repositorio
git clone <url-do-repositorio> jr
cd jr

# Copiar variaveis de ambiente
cp .env.example .env

# Subir os containers
docker compose up -d

# Instalar dependencias
docker compose exec app composer install
docker compose exec app npm install

# Gerar chave da aplicacao
docker compose exec app php artisan key:generate

# Rodar migrations e seeders
docker compose exec app php artisan migrate --seed

# Compilar assets
docker compose exec app npm run build
```

### Acessar

- **Aplicacao:** http://localhost:8080
- **Login:** jairo@jr.com / `password`

---

## Docker Services

| Servico | Porta | Descricao |
|---|---|---|
| **nginx** | 8080 | Servidor web |
| **app** | 9000 (interno) | PHP-FPM |
| **redis** | 6379 | Cache, sessao e filas |
| **queue** | — | Worker de filas Laravel |
| **scheduler** | — | Scheduler Laravel (cron) |
| **reverb** | 8085 | Servidor WebSocket (tempo real WhatsApp) |

> **Nota:** O banco de dados MySQL roda externamente (acessado via `host.docker.internal`). Nao ha container de banco no `docker-compose.yml`.

---

## Comandos Uteis

```bash
# Desenvolvimento (com hot reload)
docker compose exec app npm run dev

# Gerar transacoes recorrentes manualmente
docker compose exec app php artisan transactions:generate-recurring

# Gerar para os proximos 60 dias
docker compose exec app php artisan transactions:generate-recurring --days=60

# Rodar testes
docker compose exec app php artisan test

# Limpar cache
docker compose exec app php artisan optimize:clear
```

---

## Design System

O projeto utiliza um design system proprio inspirado na UI do Foxbit, com componentes Blade reutilizaveis:

| Componente | Uso |
|---|---|
| `<x-jr.card>` | Container com sombra e borda |
| `<x-jr.button>` | Botao primario/secundario |
| `<x-jr.input>` | Input com icone e estados |
| `<x-jr.modal>` | Modal com overlay e animacao |
| `<x-jr.table>` | Tabela responsiva com scroll |
| `<x-jr.badge>` | Badge colorido para status |
| `<x-jr.alert>` | Alerta de sucesso/erro/info |

Cores do tema definidas via CSS variables em `resources/css/app.css`, com suporte completo a dark mode via `data-theme="dark"`.

---

## Licenca

Projeto pessoal. Todos os direitos reservados.
