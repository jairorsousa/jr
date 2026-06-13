# Plano Codex - Modulo Bets

> **Modulo:** Bets  
> **Objetivo:** Gerenciar investimentos em casas de apostas com controle de casas, usuarios, contas, saldos, transacoes e integracao com o Financeiro.  
> **Projeto:** Sistema JR  
> **Autor:** Jairo Rodrigues + Codex  
> **Data:** Junho 2026  
> **Status:** Planejamento tecnico

---

## 1. Visao Geral

O modulo Bets deve funcionar como uma carteira operacional de investimentos em apostas. A ideia central e controlar onde o dinheiro esta, de quem e cada conta, em qual casa de apostas ela esta aberta, quanto entrou, quanto saiu, quanto foi apostado e qual foi o resultado.

O modulo precisa responder rapidamente perguntas como:

- Quanto tenho em todas as casas de apostas?
- Quanto tenho por casa?
- Quanto tenho por usuario/apostador?
- Qual conta esta com saldo alto demais?
- Quanto depositei e saquei no mes?
- Qual foi meu lucro/prejuizo no periodo?
- Qual ROI por casa, usuario e conta?
- Quais saques ainda estao pendentes?

O modelo deve priorizar primeiro o **controle financeiro das contas de bet**. O controle de cada aposta individual, com esporte, odd, evento e resultado detalhado, pode entrar como fase 2 sem travar o MVP.

---

## 2. Conceito Principal

O modulo tera quatro cadastros centrais:

1. **Casas de apostas**: Betano, Bet365, Sportingbet, Superbet, etc.
2. **Usuarios de bet**: pessoas/perfis que possuem contas nas casas.
3. **Contas de bet**: vinculo entre uma casa de apostas e um usuario.
4. **Transacoes de bet**: qualquer movimento financeiro dentro da conta.

Modelo mental:

```text
BettingHouse
    1:N BetAccount

BetUser
    1:N BetAccount

BetAccount
    N:1 BettingHouse
    N:1 BetUser
    1:N BetTransaction

BetTransaction
    N:1 BetAccount
    0:1 Transaction do Financeiro
```

---

## 3. Decisoes De Modelagem

### 3.1 Usuario do sistema vs Usuario de bet

O projeto ja tem `User`, que representa o usuario autenticado no Sistema JR. Para evitar conflito, o modulo deve criar `BetUser`.

`BetUser` representa a pessoa, perfil ou operador que possui contas nas casas de apostas. Pode ser o proprio Jairo, um familiar, um socio, um perfil operacional ou qualquer pessoa usada na estrategia.

### 3.2 Conta de bet como entidade central

`BetAccount` e o coracao do modulo. Ela representa exatamente isto:

> "Usuario X na Casa Y"

Exemplos:

- Jairo na Betano
- Jairo na Bet365
- Perfil Operacional 01 na Superbet
- Usuario Maria na Sportingbet

Cada `BetAccount` tera saldo proprio, status, login/apelido e extrato.

### 3.3 Transacoes como livro razao

O saldo da conta deve ser calculado a partir de um livro razao:

```text
saldo atual = saldo inicial + entradas confirmadas - saidas confirmadas
```

Transacoes pendentes podem aparecer em relatorios, mas nao devem alterar o `current_balance` ate serem confirmadas.

### 3.4 Primeiro controlar fluxo, depois aposta individual

Para o MVP, uma aposta pode ser registrada diretamente como transacao:

- `bet_stake`: valor apostado, reduz saldo.
- `bet_payout`: retorno recebido, aumenta saldo.

Se depois voce quiser medir performance por esporte, mercado, odd, tipster, estrategia ou campeonato, entra uma tabela futura `BetSlip` ou `BetOrder`, vinculada as transacoes.

---

## 4. Entidades

## 4.1 BettingHouse

Representa uma casa de apostas.

**Model:** `App\Models\BettingHouse`  
**Tabela:** `betting_houses`

Campos sugeridos:

| Campo | Tipo | Observacao |
|---|---|---|
| `id` | uuid | Chave primaria |
| `name` | string | Nome da casa |
| `slug` | string unique | Para URL e identificacao |
| `website` | string nullable | Site oficial |
| `country` | string nullable | Pais/regiao |
| `logo_url` | string nullable | Logo externo ou interno |
| `color` | string nullable | Cor para UI |
| `min_deposit` | decimal nullable | Deposito minimo |
| `min_withdrawal` | decimal nullable | Saque minimo |
| `deposit_fee_percent` | decimal nullable | Taxa de deposito, se houver |
| `withdrawal_fee_percent` | decimal nullable | Taxa de saque, se houver |
| `withdrawal_time_hours` | integer nullable | Tempo medio de saque |
| `is_active` | boolean | Ativa/inativa |
| `notes` | text nullable | Observacoes |
| `created_at` | timestamp | Padrao Laravel |
| `updated_at` | timestamp | Padrao Laravel |

Relacionamentos:

- `hasMany(BetAccount::class)`

Regras:

- `name` obrigatorio.
- `slug` unico.
- Casas inativas nao devem aparecer como opcao principal em novos cadastros.

---

## 4.2 BetUser

Representa a pessoa/perfil que usa contas nas casas.

**Model:** `App\Models\BetUser`  
**Tabela:** `bet_users`

Campos sugeridos:

| Campo | Tipo | Observacao |
|---|---|---|
| `id` | uuid | Chave primaria |
| `name` | string | Nome do usuario/perfil |
| `nickname` | string nullable | Apelido operacional |
| `document` | string nullable | CPF/documento, se fizer sentido |
| `email` | string nullable | Email usado ou contato |
| `phone` | string nullable | Telefone |
| `pix_key` | string nullable | Opcional, se for util para saque/deposito |
| `color` | string nullable | Cor para UI |
| `is_active` | boolean | Ativo/inativo |
| `notes` | text nullable | Observacoes |
| `created_at` | timestamp | Padrao Laravel |
| `updated_at` | timestamp | Padrao Laravel |

Relacionamentos:

- `hasMany(BetAccount::class)`

Regras:

- Nao armazenar senha das casas de apostas no sistema.
- Se um dia for necessario guardar credenciais, usar outro fluxo com cofre/criptografia e permissao explicita.

---

## 4.3 BetAccount

Representa uma conta em uma casa de apostas vinculada a um usuario de bet.

**Model:** `App\Models\BetAccount`  
**Tabela:** `bet_accounts`

Campos sugeridos:

| Campo | Tipo | Observacao |
|---|---|---|
| `id` | uuid | Chave primaria |
| `betting_house_id` | uuid FK | Casa de apostas |
| `bet_user_id` | uuid FK | Usuario de bet |
| `name` | string | Nome amigavel: "Jairo - Betano Principal" |
| `username` | string nullable | Login/apelido na casa |
| `account_code` | string nullable | Codigo interno, se existir |
| `status` | enum | active, limited, suspended, blocked, closed |
| `verification_status` | enum nullable | pending, verified, rejected |
| `initial_balance` | decimal | Saldo inicial informado no cadastro |
| `current_balance` | decimal | Saldo confirmado recalculado |
| `bonus_balance` | decimal default 0 | Saldo de bonus, se quiser separar |
| `withdrawable_balance` | decimal nullable | Saldo sacavel, se informado manualmente |
| `daily_deposit_limit` | decimal nullable | Limite operacional |
| `monthly_deposit_limit` | decimal nullable | Limite operacional |
| `opened_at` | date nullable | Data de abertura |
| `last_checked_at` | datetime nullable | Ultima conferencia manual |
| `is_active` | boolean | Ativa/inativa no sistema |
| `notes` | text nullable | Observacoes |
| `created_at` | timestamp | Padrao Laravel |
| `updated_at` | timestamp | Padrao Laravel |

Relacionamentos:

- `belongsTo(BettingHouse::class)`
- `belongsTo(BetUser::class)`
- `hasMany(BetTransaction::class)`

Indices e unicidade:

- Index em `betting_house_id`.
- Index em `bet_user_id`.
- Unique recomendado em `betting_house_id`, `bet_user_id`, `username`.

Regras:

- Uma conta sempre pertence a uma casa e a um usuario.
- `current_balance` deve ser atualizado por service, nao manualmente em qualquer tela.
- Contas com status `closed` nao aceitam novas transacoes, exceto ajuste administrativo.
- Contas com status `blocked`, `limited` ou `suspended` devem aparecer com alerta.

---

## 4.4 BetTransaction

Representa qualquer movimento financeiro em uma conta de bet.

**Model:** `App\Models\BetTransaction`  
**Tabela:** `bet_transactions`

Campos sugeridos:

| Campo | Tipo | Observacao |
|---|---|---|
| `id` | uuid | Chave primaria |
| `bet_account_id` | uuid FK | Conta de bet |
| `finance_transaction_id` | uuid nullable | Transacao do modulo Financeiro |
| `type` | enum | Tipo do movimento |
| `status` | enum | pending, confirmed, cancelled, failed |
| `amount` | decimal | Sempre positivo |
| `balance_before` | decimal nullable | Auditoria |
| `balance_after` | decimal nullable | Auditoria |
| `occurred_at` | datetime | Quando aconteceu |
| `confirmed_at` | datetime nullable | Quando confirmou |
| `description` | string | Descricao curta |
| `external_reference` | string nullable | ID da casa de apostas |
| `event_name` | string nullable | Nome do evento/aposta |
| `market_name` | string nullable | Mercado: vencedor, over, handicap etc. |
| `selection_name` | string nullable | Selecao apostada |
| `odd` | decimal nullable | Odd da aposta |
| `strategy` | string nullable | Estrategia usada |
| `tags` | json nullable | Tags livres |
| `metadata` | json nullable | Dados extras |
| `notes` | text nullable | Observacoes |
| `created_at` | timestamp | Padrao Laravel |
| `updated_at` | timestamp | Padrao Laravel |

Relacionamentos:

- `belongsTo(BetAccount::class)`
- `belongsTo(Transaction::class, 'finance_transaction_id')`

Indices recomendados:

- `bet_account_id`, `occurred_at`
- `type`, `status`
- `finance_transaction_id`
- `external_reference`

Regras:

- `amount` sempre positivo.
- O tipo define se a transacao aumenta ou reduz saldo.
- Apenas transacoes `confirmed` entram no saldo atual.
- Depositos e saques podem nascer como `pending`.
- Se uma transacao confirmada for editada, recalcular o saldo da conta inteira.

---

## 5. Enums

## 5.1 BetAccountStatus

Arquivo: `app/Enums/BetAccountStatus.php`

Valores:

```php
active
limited
suspended
blocked
closed
```

Sentido:

- `active`: conta operando normalmente.
- `limited`: conta ativa, mas com restricao de limite.
- `suspended`: temporariamente parada.
- `blocked`: bloqueada pela casa.
- `closed`: encerrada.

## 5.2 BetVerificationStatus

Arquivo: `app/Enums/BetVerificationStatus.php`

Valores:

```php
pending
verified
rejected
not_required
```

## 5.3 BetTransactionType

Arquivo: `app/Enums/BetTransactionType.php`

Valores de entrada:

```php
deposit
bet_payout
bonus_credit
cashback
adjustment_credit
transfer_in
```

Valores de saida:

```php
withdrawal
bet_stake
fee
adjustment_debit
transfer_out
```

Metodos uteis no enum:

```php
public function direction(): string; // in ou out
public function affectsFinance(): bool; // true para deposit/withdrawal
public function label(): string;
public function badge(): string; // success, error, warning, info
```

Observacao importante:

- Uma aposta perdida nao precisa ser outra saida se o `bet_stake` ja reduziu o saldo.
- Uma aposta ganha deve entrar como `bet_payout`, preferencialmente com o valor bruto retornado pela casa.
- Lucro da aposta = `bet_payout - bet_stake`, quando ambos estiverem vinculados a mesma operacao futura.

## 5.4 BetTransactionStatus

Arquivo: `app/Enums/BetTransactionStatus.php`

Valores:

```php
pending
confirmed
cancelled
failed
```

---

## 6. Services

## 6.1 BetBalanceService

Arquivo: `app/Services/BetBalanceService.php`

Responsabilidades:

- Recalcular saldo de uma `BetAccount`.
- Calcular saldo total do modulo.
- Calcular saldo por casa.
- Calcular saldo por usuario.
- Atualizar `balance_before` e `balance_after` das transacoes confirmadas.

Assinaturas sugeridas:

```php
public function recalculate(BetAccount $account): void;
public function totalBalance(): float;
public function balanceByHouse(): Collection;
public function balanceByUser(): Collection;
```

Regra de calculo:

```text
current_balance =
    initial_balance
    + soma confirmed/in
    - soma confirmed/out
```

## 6.2 BetTransactionService

Arquivo: `app/Services/BetTransactionService.php`

Responsabilidades:

- Criar transacao de bet com `DB::transaction`.
- Confirmar, cancelar ou falhar transacoes.
- Recalcular saldo apos qualquer mudanca.
- Controlar integracao com Financeiro quando necessario.

Assinaturas sugeridas:

```php
public function create(array $data): BetTransaction;
public function confirm(BetTransaction $transaction): void;
public function cancel(BetTransaction $transaction): void;
public function delete(BetTransaction $transaction): void;
```

## 6.3 BetFinanceIntegrationService

Arquivo: `app/Services/BetFinanceIntegrationService.php`

Responsabilidades:

- Criar transacao no Financeiro para deposito.
- Criar transacao no Financeiro para saque confirmado.
- Vincular `bet_transactions.finance_transaction_id`.
- Evitar duplicidade.

Regras:

- `deposit`: cria despesa no Financeiro, pois o dinheiro saiu da conta bancaria para a casa de apostas.
- `withdrawal`: cria receita no Financeiro somente quando o saque for confirmado.
- `bet_stake`, `bet_payout`, bonus, taxas e ajustes internos nao criam transacao no Financeiro.

---

## 7. Integracao Com O Financeiro

O modulo Bets deve conversar com o Financeiro, mas sem misturar tudo.

### 7.1 Deposito

Fluxo:

1. Usuario escolhe uma conta bancaria do Financeiro.
2. Usuario escolhe a conta de bet destino.
3. Sistema cria `BetTransaction` tipo `deposit`.
4. Sistema cria `Transaction` no Financeiro tipo `expense`.
5. Sistema vincula os dois registros.
6. Sistema recalcula o saldo da conta de bet e da conta bancaria.

Exemplo:

```text
Conta bancaria Nubank: -R$ 1.000,00
Conta Betano Jairo: +R$ 1.000,00
```

### 7.2 Saque

Fluxo recomendado:

1. Usuario registra saque como `pending`.
2. Saldo de bet ainda nao muda se o dinheiro ainda nao saiu da casa.
3. Quando o saque cair no banco, usuario confirma.
4. Sistema muda status para `confirmed`.
5. Sistema cria `Transaction` no Financeiro tipo `income`.
6. Sistema recalcula saldos.

Exemplo:

```text
Conta Betano Jairo: -R$ 500,00
Conta bancaria Nubank: +R$ 500,00
```

### 7.3 Categoria financeira

Criar ou sugerir categoria:

- Receita: `Bets - Saques`
- Despesa: `Bets - Depositos`

Opcao simples:

- Usar uma categoria unica `Bets` e separar pelo tipo da transacao.

Opcao melhor:

- Criar uma categoria pai `Bets`.
- Subcategorias: `Depositos`, `Saques`, `Taxas`.

---

## 8. Telas Do Modulo

## 8.1 Dashboard Bets

Rota: `GET /bets`  
Componente: `App\Livewire\Bets\Dashboard`

Cards:

- Saldo total em bets.
- Lucro/prejuizo do mes.
- Depositos do mes.
- Saques do mes.
- ROI do mes.
- Saques pendentes.
- Contas com alerta.

Graficos:

- Saldo por casa.
- Saldo por usuario.
- Resultado mensal.
- Depositos vs saques.

Listas:

- Top contas por saldo.
- Ultimas transacoes.
- Saques pendentes.
- Contas limitadas/bloqueadas.

## 8.2 Casas de Apostas

Rota: `GET /bets/casas`  
Componente: `App\Livewire\Bets\Houses\Index`

Funcionalidades:

- CRUD de casas.
- Ativar/desativar.
- Buscar por nome.
- Ver saldo total por casa.
- Ver quantidade de contas por casa.

## 8.3 Usuarios de Bet

Rota: `GET /bets/usuarios`  
Componente: `App\Livewire\Bets\Users\Index`

Funcionalidades:

- CRUD de usuarios/perfis.
- Ativar/desativar.
- Buscar por nome, nickname, email ou telefone.
- Ver saldo total por usuario.
- Ver quantidade de contas.

## 8.4 Contas de Bet

Rota: `GET /bets/contas`  
Componente: `App\Livewire\Bets\Accounts\Index`

Funcionalidades:

- CRUD de contas.
- Selecionar casa e usuario.
- Filtrar por casa, usuario, status e saldo.
- Exibir saldo atual, bonus, saldo sacavel e ultima conferencia.
- Acoes rapidas: deposito, saque, ajuste, conferir saldo.

## 8.5 Detalhe da Conta

Rota: `GET /bets/contas/{id}`  
Componente: `App\Livewire\Bets\Accounts\Show`

Funcionalidades:

- Resumo da conta.
- Extrato completo.
- Filtros por periodo, tipo e status.
- Botao para adicionar transacao.
- Botao para recalcular saldo.
- Linha do tempo de movimentacoes.
- Indicadores de ROI da conta.

## 8.6 Transacoes de Bet

Rota: `GET /bets/transacoes`  
Componente: `App\Livewire\Bets\Transactions\Index`

Funcionalidades:

- Listagem paginada.
- Filtros por conta, casa, usuario, tipo, status e periodo.
- Cadastro rapido.
- Confirmar/cancelar transacoes pendentes.
- Link para transacao financeira vinculada.
- Totais do periodo.

---

## 9. Estrutura Tecnica Sugerida

Models:

```text
app/Models/BettingHouse.php
app/Models/BetUser.php
app/Models/BetAccount.php
app/Models/BetTransaction.php
```

Enums:

```text
app/Enums/BetAccountStatus.php
app/Enums/BetVerificationStatus.php
app/Enums/BetTransactionType.php
app/Enums/BetTransactionStatus.php
```

Services:

```text
app/Services/BetBalanceService.php
app/Services/BetTransactionService.php
app/Services/BetFinanceIntegrationService.php
```

Livewire:

```text
app/Livewire/Bets/Dashboard.php
app/Livewire/Bets/Houses/Index.php
app/Livewire/Bets/Users/Index.php
app/Livewire/Bets/Accounts/Index.php
app/Livewire/Bets/Accounts/Show.php
app/Livewire/Bets/Transactions/Index.php
```

Views:

```text
resources/views/bets/dashboard.blade.php
resources/views/bets/houses/index.blade.php
resources/views/bets/users/index.blade.php
resources/views/bets/accounts/index.blade.php
resources/views/bets/accounts/show.blade.php
resources/views/bets/transactions/index.blade.php

resources/views/livewire/bets/dashboard.blade.php
resources/views/livewire/bets/houses/index.blade.php
resources/views/livewire/bets/users/index.blade.php
resources/views/livewire/bets/accounts/index.blade.php
resources/views/livewire/bets/accounts/show.blade.php
resources/views/livewire/bets/transactions/index.blade.php
```

Rotas:

```php
Route::prefix('bets')->name('bets.')->group(function () {
    Route::get('/', fn () => view('bets.dashboard'))->name('dashboard');
    Route::get('/casas', fn () => view('bets.houses.index'))->name('houses');
    Route::get('/usuarios', fn () => view('bets.users.index'))->name('users');
    Route::get('/contas', fn () => view('bets.accounts.index'))->name('accounts');
    Route::get('/contas/{id}', fn ($id) => view('bets.accounts.show', ['id' => $id]))->name('accounts.show');
    Route::get('/transacoes', fn () => view('bets.transactions.index'))->name('transactions');
});
```

Menu sidebar:

```text
Bets
  - Dashboard
  - Casas
  - Usuarios
  - Contas
  - Transacoes
```

---

## 10. Migrations Em Ordem

1. `create_betting_houses_table`
2. `create_bet_users_table`
3. `create_bet_accounts_table`
4. `create_bet_transactions_table`

Ordem importante porque `bet_accounts` depende de `betting_houses` e `bet_users`, e `bet_transactions` depende de `bet_accounts` e opcionalmente de `transactions`.

---

## 11. Relatorios E Metricas

Metricas essenciais:

```text
saldo_total_bets
saldo_por_casa
saldo_por_usuario
saldo_por_conta
depositos_periodo
saques_periodo
apostas_periodo
retornos_periodo
lucro_periodo
roi_periodo
saques_pendentes
contas_com_status_critico
```

Formulas:

```text
lucro_periodo = entradas_operacionais - saidas_operacionais

entradas_operacionais =
    bet_payout
    + bonus_credit
    + cashback
    + adjustment_credit

saidas_operacionais =
    bet_stake
    + fee
    + adjustment_debit

roi_periodo =
    lucro_periodo / total_apostado_periodo * 100
```

Observacao:

- Deposito e saque sao fluxo de caixa entre banco e casa, nao lucro/prejuizo operacional.
- Resultado operacional deve olhar principalmente `bet_stake`, `bet_payout`, bonus, taxas e ajustes.

---

## 12. Fases De Implementacao

## Fase 1 - MVP de cadastro e extrato

- Criar enums.
- Criar migrations.
- Criar models e relationships.
- Criar seed opcional com algumas casas conhecidas.
- Criar CRUD de casas.
- Criar CRUD de usuarios de bet.
- Criar CRUD de contas.
- Criar CRUD/listagem de transacoes.
- Criar `BetBalanceService`.
- Recalcular saldo apos criar/editar/excluir transacao.

Entrega esperada:

- Voce consegue cadastrar casas, usuarios, contas e transacoes.
- Cada conta mostra saldo atual correto.
- Dashboard simples mostra saldo total.

## Fase 2 - Integracao com Financeiro

- Criar `BetFinanceIntegrationService`.
- Em deposito, criar despesa no Financeiro.
- Em saque confirmado, criar receita no Financeiro.
- Vincular `bet_transactions.finance_transaction_id`.
- Adicionar filtro para ver transacoes com/sem vinculo financeiro.
- Adicionar categorias financeiras de Bets via seeder ou criacao automatica.

Entrega esperada:

- Depositos e saques nao ficam soltos.
- Financeiro e Bets batem entre si.

## Fase 3 - Dashboard e analises

- Dashboard completo.
- Graficos por casa e usuario.
- ROI por periodo.
- Relatorio de lucro/prejuizo.
- Saques pendentes.
- Alertas de saldo alto e contas limitadas.

Entrega esperada:

- Voce acompanha resultado e risco sem abrir cada conta manualmente.

## Fase 4 - Apostas individuais

Adicionar tabela futura `bet_slips` ou `bet_orders`.

Campos provaveis:

```text
id
bet_account_id
stake_transaction_id
payout_transaction_id
sport
league
event_name
market_name
selection_name
odd
stake_amount
potential_return
payout_amount
profit_amount
status
placed_at
settled_at
notes
```

Entrega esperada:

- Analise por esporte, mercado, odd, campeonato, estrategia e taxa de acerto.

---

## 13. Testes Recomendados

Unitarios:

- `BetTransactionType::direction()` retorna direcao correta.
- `BetBalanceService` calcula saldo com entradas e saidas.
- Transacoes pendentes nao alteram saldo.
- Transacoes canceladas/falhas nao alteram saldo.

Feature:

- CRUD de casas.
- CRUD de usuarios.
- CRUD de contas.
- Criar deposito atualiza saldo.
- Criar saque pendente nao altera saldo.
- Confirmar saque altera saldo e cria transacao financeira.
- Editar transacao confirmada recalcula saldo.
- Excluir transacao confirmada recalcula saldo.

Integracao:

- Deposito cria despesa no Financeiro.
- Saque confirmado cria receita no Financeiro.
- Nao duplica transacao financeira ao confirmar duas vezes.

---

## 14. Pontos De Cuidado

- Nao misturar deposito/saque com lucro operacional.
- Nao armazenar senhas de casas de apostas.
- Usar `DB::transaction` sempre que mexer em saldo e transacao financeira juntas.
- Evitar editar `current_balance` diretamente por tela.
- Ter acao manual "Recalcular saldo" na conta.
- Registrar `balance_before` e `balance_after` para auditoria.
- Tratar saques pendentes com cuidado para nao inflar entrada no Financeiro antes do dinheiro cair.
- Pensar em `metadata` json para capturar informacoes que variam muito entre casas.

---

## 15. Decisao Recomendada Para O MVP

Para comecar rapido e com boa base, eu faria o MVP assim:

1. Casas de apostas.
2. Usuarios de bet.
3. Contas de bet.
4. Transacoes tipo livro razao.
5. Saldo recalculado por service.
6. Deposito e saque vinculados ao Financeiro.
7. Dashboard simples.

Eu deixaria apostas individuais para a segunda fase. Isso evita modelar esporte/mercado/odd antes da hora e entrega logo o controle que mais importa: onde esta o dinheiro e qual foi o resultado.
