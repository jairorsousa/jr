# Plano do Módulo Bets — Gestão de Investimentos em Apostas

> **Módulo:** Bets  
> **Objetivo:** Gerenciar de forma profissional investimentos em casas de apostas, controlando múltiplas contas de vários usuários em diversas plataformas.  
> **Autor:** Jairo Rodrigues + Grok  
> **Data:** Junho 2026  
> **Status:** Em planejamento

---

## 1. Visão Geral

O usuário atua com **investimento em apostas** (betting investment) e precisa de uma ferramenta robusta para gerenciar:

- Várias **casas de apostas** (Bet365, Betano, Sportingbet, Galera.bet, etc.)
- Vários **usuários/apostadores** (pode ser ele mesmo + outras pessoas)
- Múltiplas **contas** vinculadas (uma casa pode ter várias contas de diferentes usuários)
- Todas as **movimentações** (depósitos, saques, apostas, bônus, cashouts, perdas e ganhos)

O módulo deve permitir uma visão clara de:
- Saldo por conta, por usuário e por casa
- Rentabilidade (ROI, lucro/prejuízo)
- Fluxo de caixa entre o banco e as casas de apostas
- Risco por casa/usuário

O módulo deve **integrar** com o módulo Financeiro já existente (transações bancárias, contas, categorias).

---

## 2. Entidades Principais

### 2.1 BettingHouses (Casas de Apostas)

Representa a plataforma de apostas.

**Campos sugeridos:**
- `id` (uuid)
- `name` (Bet365, Betano, etc.)
- `slug` (único, para URLs)
- `country` (Brasil, etc.)
- `website`
- `logo_url` (opcional)
- `deposit_fee_percent` (taxa de depósito)
- `withdrawal_fee_percent` (taxa de saque)
- `min_deposit`
- `min_withdrawal`
- `withdrawal_time_days` (estimativa)
- `notes`
- `is_active`
- `color` (para UI)
- `created_at`, `updated_at`

**Regras:**
- Uma casa pode ter N contas.

### 2.2 BetUsers (Usuários de Apostas)

Pessoas que possuem contas nas casas.

Diferente do `User` do sistema (que é o dono do JR). Aqui são os "apostadores" ou "operadores".

**Campos sugeridos:**
- `id` (uuid)
- `name`
- `nickname` (apelido usado nas casas)
- `document` (CPF, opcional)
- `phone`
- `email`
- `notes`
- `is_active`
- `color` (para identificar visualmente)
- `created_at`, `updated_at`

**Relacionamentos:**
- Um BetUser pode ter várias BetAccounts.

### 2.3 BetAccounts (Contas de Apostas)

É o coração do módulo. Representa uma conta específica em uma casa pertencente a um usuário.

**Campos sugeridos:**
- `id` (uuid)
- `betting_house_id` (FK)
- `bet_user_id` (FK)
- `username` (login na casa)
- `account_name` (nome amigável ex: "Betano - Jairo Principal")
- `current_balance` (saldo atual — pode ser calculado via transações)
- `initial_balance` (saldo inicial quando a conta foi cadastrada)
- `status` (active, suspended, closed, limited)
- `account_type` (principal, secundária, cashout, bônus, etc.)
- `last_login_at`
- `notes`
- `created_at`, `updated_at`

**Regras importantes:**
- Combinação única: `(betting_house_id + bet_user_id + username)` deve ser única.
- O saldo deve ser **calculado** preferencialmente a partir das transações (evitar atualização manual sempre).

### 2.4 BetTransactions (Transações de Apostas)

Todo movimento financeiro dentro de uma conta de apostas.

**Campos sugeridos:**
- `id` (uuid)
- `bet_account_id` (FK)
- `type` (enum): 
  - `deposit` (depósito vindo do banco)
  - `withdrawal` (saque para o banco)
  - `bet_placed` (aposta realizada)
  - `bet_won` (ganho de aposta)
  - `bet_lost` (perda de aposta)
  - `cashout` (cashout parcial/total)
  - `bonus` (bônus creditado)
  - `bonus_wagered` (bônus cumprido)
  - `fee` (taxa da casa)
  - `adjustment` (ajuste manual)
- `amount` (sempre positivo, o sinal vem do `type`)
- `balance_before`
- `balance_after`
- `description`
- `external_reference` (ID da transação na casa de apostas)
- `bet_event` (descrição do evento: "Flamengo x Palmeiras - Vitória")
- `odd` (quando for aposta)
- `stake` (valor apostado)
- `profit` (lucro/prejuízo da operação — calculado)
- `category_id` (FK para categories do módulo Financeiro, ex: "Apostas Esportivas")
- `occurred_at` (data/hora real da transação)
- `is_confirmed` (boolean — útil para saques pendentes)
- `notes`
- `created_at`, `updated_at`

**Relacionamentos extras sugeridos:**
- `linked_finance_transaction_id` (FK para a tabela `transactions` do módulo Financeiro) — para rastrear quando o dinheiro saiu/entrou da conta bancária real.

### 2.5 Opcional (fase 2): BetBets (Apostas Individuais)

Para análise mais profunda de performance:

- `bet_account_id`
- `sport` / `league`
- `event`
- `selection`
- `odd`
- `stake`
- `potential_return`
- `result` (won, lost, void, cashout)
- `profit`
- `placed_at`
- `settled_at`

---

## 3. Diagrama de Relacionamentos (resumo)

```
BettingHouses
    └── BetAccounts (N)
            └── BetUsers (1)
            └── BetTransactions (N)

BetTransactions
    └── (opcional) Finance Transactions (1:1 ou 1:N)

BetUsers
    └── BetAccounts (N)
```

---

## 4. Regras de Negócio Importantes

1. **Saldo da conta** deve ser recalculado sempre que uma transação é confirmada (similar ao `BalanceService` atual).
2. Depósito em uma BetAccount geralmente vem de uma conta bancária → deve gerar uma transação do tipo `expense` no módulo Financeiro.
3. Saque de uma BetAccount para o banco → transação do tipo `income` no Financeiro.
4. Apostas (bet_placed, bet_won, bet_lost) são **internas** à casa e não afetam diretamente o saldo bancário.
5. É possível ter várias contas do mesmo usuário na mesma casa (ex: conta principal + conta para cashout).
6. Controle de **limites** por casa (muitos usuários têm limite de depósito diário/semanal).
7. Rastreabilidade: toda movimentação grande deve ter referência externa da casa.

---

## 5. Funcionalidades Principais do Módulo

### Cadastros
- CRUD de Casas de Apostas
- CRUD de Usuários de Apostas (BetUsers)
- CRUD de Contas de Apostas (com seleção de casa + usuário)
- Cadastro rápido de transações (com atalhos para depósito/saque)

### Visões e Relatórios
- Dashboard de Apostas (saldo total em todas as casas, lucro do mês, ROI geral)
- Visão por Casa (saldo + rentabilidade por plataforma)
- Visão por Usuário
- Extrato detalhado por conta
- Histórico de depósitos e saques (com link para transações bancárias)
- Relatório de Rentabilidade (por casa, por usuário, por período)
- Alerta de contas com saldo alto (risco)

### Integração com Financeiro
- Ao registrar um **depósito**, sugerir automaticamente a criação de uma transação de saída na conta bancária.
- Ao registrar um **saque**, sugerir transação de entrada.
- Usar as mesmas Categories do módulo Financeiro (ou criar subcategorias específicas de "Apostas").

---

## 6. Estrutura Técnica Sugerida

### Models (app/Models/)
- `BettingHouse.php`
- `BetUser.php`
- `BetAccount.php`
- `BetTransaction.php`

### Enums (app/Enums/)
- `BetTransactionType.php`
- `BetAccountStatus.php`

### Services
- `BetBalanceService.php` (recalcula saldos das contas de apostas)
- `BetTransferService.php` (lógica de depósito/saque com integração ao Financeiro)

### Livewire Components (sugestão)
```
app/Livewire/Bets/
├── Houses/
│   └── Index.php
├── Users/
│   └── Index.php
├── Accounts/
│   ├── Index.php
│   └── Show.php
├── Transactions/
│   ├── Index.php
│   └── Create.php
└── Dashboard.php
```

### Rotas sugeridas
- `/bets` → Dashboard
- `/bets/houses`
- `/bets/users`
- `/bets/accounts`
- `/bets/accounts/{id}`
- `/bets/transactions`

### Integração com Sidebar
Adicionar no menu:
- Bets
  - Dashboard
  - Casas
  - Usuários
  - Contas
  - Transações

---

## 7. Fases de Implementação Sugeridas

**Fase 1 – Base (essencial)**
- Models + Migrations
- CRUD básico de Casas, Usuários e Contas
- Cadastro de transações simples (depósito, saque, aposta perdida/ganha)
- Recálculo de saldo das contas

**Fase 2 – Integração Financeira**
- Vincular depósitos/saques com transações do módulo Financeiro
- Serviço de transferência entre conta bancária ↔ conta de apostas

**Fase 3 – Análise e Relatórios**
- Dashboard com gráficos (saldo por casa, ROI por período)
- Filtros avançados
- Exportação

**Fase 4 – Avançado**
- Módulo de Apostas individuais (BetBets)
- Controle de limites e alertas
- Integração com API das casas (se possível no futuro)

---

## 8. Perguntas para Refinar o Modelo

Antes de implementar, seria bom esclarecer:

1. Os "usuários" são pessoas diferentes que usam as contas, ou são apenas diferentes perfis/logins do mesmo dono?
2. Você quer rastrear **cada aposta individual** (com odd, evento, resultado) ou só o fluxo financeiro (depósitos, saques e resultado líquido)?
3. Quer controlar **bônus** separadamente (saldo de bônus vs saldo sacável)?
4. Alguma casa tem regras específicas que precisam ser modeladas (ex: rollover obrigatório)?
5. Você costuma fazer **cashout** com frequência? Precisa de tratamento especial?

---

## 9. Próximos Passos

Após aprovação deste plano:

1. Criar as migrations
2. Criar os Models com relacionamentos e casts
3. Criar Enums
4. Criar o `BetBalanceService`
5. Desenvolver os componentes Livewire básicos
6. Adicionar integração com o módulo Financeiro

---

**Documento gerado com Grok para o projeto Sistema JR.**