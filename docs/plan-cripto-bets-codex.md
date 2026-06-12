# Plano Codex - Cripto Integrado Ao Modulo Bets

> **Modulo:** Cripto + Bets  
> **Objetivo:** Gerenciar contas/carteiras de criptomoedas usadas como ponte operacional entre bancos, corretoras/carteiras e casas de apostas, mantendo todos os saldos e relatorios principais em BRL.  
> **Projeto:** Sistema JR  
> **Autor:** Jairo Rodrigues + Codex  
> **Data:** Junho 2026  
> **Status:** Planejamento tecnico

---

## 1. Visao Geral

Hoje o Sistema JR ja controla:

- Contas bancarias tradicionais no Financeiro.
- Contas de bet por casa de apostas e usuario.
- Transacoes de bet com integracao opcional ao Financeiro.

O novo desafio e inserir uma camada comum na operacao real:

```text
Banco BRL -> Corretora/Carteira cripto -> Casa de apostas
Casa de apostas -> Corretora/Carteira cripto -> Banco BRL
```

Em muitas casas, a operacao nao acontece diretamente em real por banco. Ela passa por USDT, BTC, LTC ou outras moedas. Mesmo assim, para gestao e resultado, o sistema deve continuar mostrando tudo em **R$**.

Principio central:

> A cripto deve ser tratada como meio de movimentacao/liquidacao, nao como moeda principal dos relatorios.

Ou seja:

- O valor oficial da transacao no sistema continua sendo `amount_brl`.
- O valor em cripto entra como dado operacional/auditoria: `crypto_amount`, `crypto_asset`, `network`, `tx_hash`, `exchange_rate_brl`.
- Saldos principais continuam em BRL.
- Quando fizer sentido, o sistema tambem mostra saldo estimado em cripto.

---

## 2. Problema Que Queremos Resolver

O fluxo real pode ter varias camadas:

1. Dinheiro sai de uma conta bancaria em BRL.
2. Vai para uma corretora, como Binance, CoinEx, OKX etc.
3. Vira USDT, BTC, LTC ou outra moeda.
4. Sai por uma rede, como TRC20, ERC20, BEP20, BTC, LTC, Solana.
5. Chega em uma casa de apostas.
6. A casa credita saldo para uma conta de bet.

O caminho inverso tambem acontece:

1. Saldo sai da casa de apostas.
2. Volta em cripto para carteira/corretora.
3. Pode ficar parado em cripto.
4. Pode ser convertido para BRL.
5. Pode voltar para banco.

Sem modelagem propria, isso fica confuso porque:

- Um deposito de bet pode nao sair direto do banco.
- Um saque de bet pode nao entrar direto no banco.
- Pode existir dinheiro parado em exchanges/carteiras.
- Pode existir taxa de rede.
- Pode existir diferenca cambial entre comprar cripto, enviar e converter de volta.
- A mesma carteira pode atender varias contas de bet e varios usuarios.

---

## 3. Decisao Principal

Criar uma camada chamada **Crypto Accounts** dentro do ecossistema financeiro do JR.

Ela deve representar:

- Conta em corretora: Binance, CoinEx, OKX, Bybit etc.
- Carteira self-custody: Trust Wallet, Phantom, MetaMask etc.
- Carteira operacional especifica para bets.

Essa camada nao substitui o Financeiro nem o Bets. Ela fica entre os dois.

```text
Financeiro
    Account bancaria em BRL
        |
        | deposito/compra cripto
        v
Cripto
    CryptoAccount / CryptoWallet
        |
        | envio cripto para casa
        v
Bets
    BetAccount na casa de apostas
```

---

## 4. Regra Da Moeda Principal

Todas as transacoes devem ter um valor principal em BRL.

Campos conceituais:

```text
amount_brl              valor oficial para saldo/relatorio
crypto_amount           quantidade em cripto, opcional
crypto_asset            USDT, BTC, LTC etc.
exchange_rate_brl       cotacao usada na operacao
fee_brl                 taxa convertida para BRL
fee_crypto_amount       taxa em cripto, opcional
```

Exemplo:

```text
Envio para Betano
amount_brl: 1.000,00
crypto_asset: USDT
crypto_amount: 185.432100
exchange_rate_brl: 5.3928
network: TRC20
tx_hash: abc123...
fee_brl: 5,40
fee_crypto_amount: 1.000000 USDT
```

O saldo principal da carteira pode ser:

```text
current_balance_brl = soma entradas confirmadas em BRL - soma saidas confirmadas em BRL
```

Opcionalmente, o sistema tambem pode exibir:

```text
saldo por ativo = soma crypto_amount de entradas - soma crypto_amount de saidas
```

Mas o dashboard principal continua em BRL.

---

## 5. Entidades Sugeridas

## 5.1 CryptoInstitution

Representa a instituicao onde a conta/carteira existe.

Exemplos:

- Binance
- CoinEx
- OKX
- Trust Wallet
- Phantom
- MetaMask

**Model:** `CryptoInstitution`  
**Tabela:** `crypto_institutions`

Campos:

| Campo | Tipo | Observacao |
|---|---|---|
| `id` | uuid | Chave primaria |
| `name` | string | Nome da corretora/carteira |
| `slug` | string unique | Identificador |
| `type` | enum | exchange, wallet, broker, other |
| `website` | string nullable | Site oficial |
| `color` | string | Cor para UI |
| `is_active` | boolean | Ativa/inativa |
| `notes` | text nullable | Observacoes |

Relacionamentos:

- `hasMany(CryptoAccount::class)`

---

## 5.2 CryptoAsset

Representa a moeda.

Exemplos:

- USDT
- BTC
- LTC
- ETH
- SOL
- USDC

**Model:** `CryptoAsset`  
**Tabela:** `crypto_assets`

Campos:

| Campo | Tipo | Observacao |
|---|---|---|
| `id` | uuid | Chave primaria |
| `symbol` | string unique | USDT, BTC, LTC |
| `name` | string | Tether, Bitcoin, Litecoin |
| `decimals` | integer | Precisao |
| `is_stablecoin` | boolean | USDT/USDC etc. |
| `is_active` | boolean | Ativo/inativo |

Observacao:

- Stablecoin facilita relatorio, mas mesmo USDT deve ter cotacao BRL registrada por transacao.

---

## 5.3 CryptoNetwork

Representa a rede usada na transferencia.

Exemplos:

- TRC20
- ERC20
- BEP20
- BTC
- LTC
- SOL

**Model:** `CryptoNetwork`  
**Tabela:** `crypto_networks`

Campos:

| Campo | Tipo | Observacao |
|---|---|---|
| `id` | uuid | Chave primaria |
| `name` | string | Tron, Ethereum, BSC |
| `code` | string unique | TRC20, ERC20, BEP20 |
| `native_asset_id` | uuid nullable | TRX, ETH, BNB etc. |
| `is_active` | boolean | Ativa/inativa |

---

## 5.4 CryptoAccount

Representa uma conta em corretora ou carteira.

**Model:** `CryptoAccount`  
**Tabela:** `crypto_accounts`

Campos:

| Campo | Tipo | Observacao |
|---|---|---|
| `id` | uuid | Chave primaria |
| `crypto_institution_id` | uuid FK | Binance, CoinEx, Trust Wallet |
| `bet_user_id` | uuid nullable | Dono/perfil, quando a conta for ligada a um usuario de bet |
| `name` | string | Nome amigavel |
| `account_identifier` | string nullable | Email/login/codigo interno |
| `custody_type` | enum | exchange, self_custody, shared |
| `initial_balance_brl` | decimal | Saldo inicial em BRL |
| `current_balance_brl` | decimal | Saldo atual em BRL |
| `is_active` | boolean | Ativa/inativa |
| `last_checked_at` | datetime nullable | Ultima conferencia |
| `notes` | text nullable | Observacoes |

Relacionamentos:

- `belongsTo(CryptoInstitution::class)`
- `belongsTo(BetUser::class)` opcional
- `hasMany(CryptoWalletAddress::class)`
- `hasMany(CryptoTransaction::class)`

Regra:

- Uma conta cripto pode ser propria do Jairo ou estar associada a um `BetUser`.
- Nao guardar seed phrase, senha, private key ou 2FA no sistema.

---

## 5.5 CryptoWalletAddress

Representa enderecos por rede/ativo.

**Model:** `CryptoWalletAddress`  
**Tabela:** `crypto_wallet_addresses`

Campos:

| Campo | Tipo | Observacao |
|---|---|---|
| `id` | uuid | Chave primaria |
| `crypto_account_id` | uuid FK | Conta/carteira |
| `crypto_asset_id` | uuid nullable | Ativo principal |
| `crypto_network_id` | uuid FK | Rede |
| `address` | string | Endereco |
| `label` | string nullable | Ex: USDT TRC20 principal |
| `is_deposit_address` | boolean | Se recebe |
| `is_withdrawal_address` | boolean | Se envia |
| `is_active` | boolean | Ativo/inativo |
| `notes` | text nullable | Observacoes |

Regras:

- O mesmo `address` pode existir em redes diferentes dependendo do ecossistema, mas o ideal e indexar por `network + address`.
- Enderecos de casas de aposta podem ser registrados como contatos externos no futuro.

---

## 5.6 CryptoTransaction

Livro razao da conta/carteira cripto.

**Model:** `CryptoTransaction`  
**Tabela:** `crypto_transactions`

Campos principais:

| Campo | Tipo | Observacao |
|---|---|---|
| `id` | uuid | Chave primaria |
| `crypto_account_id` | uuid FK | Conta cripto |
| `finance_transaction_id` | uuid nullable | Quando envolve banco/Financeiro |
| `bet_transaction_id` | uuid nullable | Quando envolve uma transacao de bet |
| `type` | enum | Tipo de movimento |
| `status` | enum | pending, confirmed, cancelled, failed |
| `amount_brl` | decimal | Valor principal |
| `crypto_asset_id` | uuid nullable | USDT, BTC, LTC |
| `crypto_network_id` | uuid nullable | TRC20, BTC, LTC etc. |
| `crypto_amount` | decimal nullable | Quantidade do ativo |
| `exchange_rate_brl` | decimal nullable | Cotacao BRL usada |
| `fee_brl` | decimal nullable | Taxa em BRL |
| `fee_crypto_amount` | decimal nullable | Taxa em cripto |
| `tx_hash` | string nullable | Hash on-chain |
| `from_address` | string nullable | Origem |
| `to_address` | string nullable | Destino |
| `occurred_at` | datetime | Data da operacao |
| `confirmed_at` | datetime nullable | Data de confirmacao |
| `description` | string | Descricao |
| `notes` | text nullable | Observacoes |
| `metadata` | json nullable | Dados extras |

Tipos sugeridos:

```text
bank_deposit          dinheiro saiu do banco para corretora
bank_withdrawal       dinheiro voltou da corretora para banco
buy_crypto            compra de cripto dentro da corretora
sell_crypto           venda de cripto dentro da corretora
send_to_bet           envio cripto para casa de apostas
receive_from_bet      recebimento cripto da casa de apostas
send_to_wallet        transferencia para outra carteira
receive_from_wallet   recebimento de outra carteira
network_fee           taxa de rede
exchange_fee          taxa da corretora
adjustment_credit     ajuste manual positivo
adjustment_debit      ajuste manual negativo
```

Regra de saldo:

- Entradas confirmadas aumentam `current_balance_brl`.
- Saidas confirmadas reduzem `current_balance_brl`.
- Taxas reduzem saldo.
- Transacoes pendentes nao alteram saldo.

---

## 6. Integracao Com Bets

Hoje `BetTransaction` pode vincular uma transacao financeira bancaria via `finance_transaction_id`.

Com cripto, existem duas opcoes.

## 6.1 Opcao simples

Adicionar campos diretos em `bet_transactions`:

```text
settlement_method       bank, crypto, manual
crypto_account_id
crypto_asset_id
crypto_network_id
crypto_amount
exchange_rate_brl
tx_hash
```

Vantagem:

- Implementacao mais rapida.
- Menos tabelas no inicio.

Desvantagem:

- A carteira cripto nao tem livro razao proprio.
- Fica dificil ver saldo em Binance/CoinEx/Trust Wallet.
- Nao resolve bem transferencias entre carteiras.

## 6.2 Opcao recomendada

Criar `CryptoTransaction` e vincular com `BetTransaction`.

Fluxo:

```text
BetTransaction
    amount_brl = R$ 1.000,00
    type = deposit
    status = confirmed
    crypto_transaction_id -> CryptoTransaction send_to_bet

CryptoTransaction
    type = send_to_bet
    amount_brl = R$ 1.000,00
    crypto_asset = USDT
    crypto_amount = 185.43
    network = TRC20
    tx_hash = ...
```

Vantagem:

- Bet continua simples e em BRL.
- Cripto ganha saldo proprio por corretora/carteira.
- Fica possivel auditar hash, rede, taxa e quantidade.
- Fica possivel medir quanto dinheiro esta parado em cripto.

Desvantagem:

- Exige mais telas e services.

Recomendacao:

> Usar a opcao recomendada. Cripto e um fluxo grande o suficiente para merecer livro razao proprio.

---

## 7. Fluxos Operacionais

## 7.1 Banco para corretora

Exemplo:

```text
Nubank -> Binance
R$ 5.000,00
```

Registros:

1. `Transaction` no Financeiro:
   - Conta bancaria: Nubank
   - Tipo: expense ou transfer_out
   - Valor: R$ 5.000,00
   - Categoria: Cripto - Aporte

2. `CryptoTransaction`:
   - Conta cripto: Binance Jairo
   - Tipo: bank_deposit
   - Valor: R$ 5.000,00
   - Vinculo: `finance_transaction_id`

Efeito:

```text
Banco diminui R$ 5.000
Carteira cripto aumenta R$ 5.000
```

Observacao:

- Idealmente o Financeiro deveria tratar isso como transferencia entre contas, nao despesa real. Mas no modelo atual, pode ser despesa operacional em uma categoria especifica ate criarmos transferencias mais robustas.

## 7.2 Compra de cripto

Exemplo:

```text
Comprar USDT na Binance
R$ 5.000,00 -> 927.50 USDT
Cotacao: 5.3908
```

Registro:

```text
CryptoTransaction
type: buy_crypto
amount_brl: 5.000,00
crypto_asset: USDT
crypto_amount: 927.50
exchange_rate_brl: 5.3908
```

Observacao:

- Essa transacao nao altera necessariamente o saldo BRL da conta cripto se for apenas conversao interna de BRL para USDT na mesma conta.
- Para MVP, ela pode ser informativa/auditoria.
- Para fase avancada, separar saldo fiat BRL dentro da corretora e saldo por ativo.

## 7.3 Corretora/carteira para casa de aposta

Exemplo:

```text
Binance -> Betano
185.43 USDT TRC20
Valor BRL: R$ 1.000,00
```

Registros:

1. `CryptoTransaction`:
   - Tipo: send_to_bet
   - Conta cripto: Binance Jairo
   - Valor: R$ 1.000,00
   - Ativo: USDT
   - Quantidade: 185.43
   - Rede: TRC20
   - Hash: tx_hash

2. `BetTransaction`:
   - Conta bet: Betano - Usuario X
   - Tipo: deposit
   - Valor: R$ 1.000,00
   - Status: confirmed
   - Vinculo: `crypto_transaction_id`

Efeito:

```text
Carteira cripto diminui R$ 1.000
Conta bet aumenta R$ 1.000
```

## 7.4 Casa de aposta para cripto

Exemplo:

```text
Betano -> Trust Wallet
100 USDT
Valor BRL: R$ 540,00
```

Registros:

1. `BetTransaction`:
   - Tipo: withdrawal
   - Valor: R$ 540,00
   - Status: pending ou confirmed

2. `CryptoTransaction`:
   - Tipo: receive_from_bet
   - Valor: R$ 540,00
   - Ativo: USDT
   - Quantidade: 100
   - Rede: TRC20
   - Hash: tx_hash

Efeito quando confirmado:

```text
Conta bet diminui R$ 540
Carteira cripto aumenta R$ 540
```

## 7.5 Corretora para banco

Exemplo:

```text
Binance -> Nubank
R$ 2.000,00
```

Registros:

1. `CryptoTransaction`:
   - Tipo: bank_withdrawal
   - Valor: R$ 2.000,00

2. `Transaction` no Financeiro:
   - Conta bancaria: Nubank
   - Tipo: income ou transfer_in
   - Valor: R$ 2.000,00
   - Categoria: Cripto - Resgate

Efeito:

```text
Carteira cripto diminui R$ 2.000
Banco aumenta R$ 2.000
```

---

## 8. Como Fica O Fluxo De Cadastro Na Tela

Na tela de transacao de Bets, hoje existe:

```text
Criar/vincular transacao no Financeiro
Conta financeira
```

Depois da evolucao, essa area deveria virar:

```text
Forma de liquidacao:
  - Banco / Financeiro
  - Cripto
  - Manual / Sem vinculo
```

Se escolher Banco:

```text
Conta financeira
```

Se escolher Cripto:

```text
Conta cripto
Ativo: USDT, BTC, LTC...
Rede: TRC20, BTC, LTC...
Quantidade em cripto
Cotacao BRL
Taxa em cripto
Taxa em BRL
Hash da transacao
Endereco origem/destino
```

O campo principal continua:

```text
Valor em R$
```

O sistema pode sugerir:

```text
valor_brl = crypto_amount * exchange_rate_brl
```

Mas o usuario pode ajustar manualmente.

---

## 9. Dashboard Cripto

Criar uma tela `/cripto` ou `/financeiro/cripto`.

Cards:

- Saldo total em cripto em BRL.
- Saldo por corretora/carteira.
- Saldo por ativo em BRL.
- Total enviado para bets no mes.
- Total recebido das bets no mes.
- Taxas de rede/corretora no mes.
- Saldo parado em cripto.

Listas:

- Carteiras com maior saldo.
- Ultimas transacoes cripto.
- Transacoes pendentes.
- Transacoes sem hash.
- Saques de bet ainda pendentes.

Graficos:

- Saldo por instituicao.
- Saldo por ativo.
- Enviado vs recebido de bets.
- Taxas por mes.

---

## 10. Relatorios Importantes

## 10.1 Exposicao total

```text
Total em bancos
Total em cripto
Total em casas de apostas
Total geral
```

Isso responde:

> Quanto dinheiro operacional eu tenho em cada camada?

## 10.2 Fluxo por usuario de bet

Como existem contas de bet em nome de varios usuarios, o relatorio deve permitir:

```text
Usuario X
  Contas bet
  Carteiras cripto relacionadas
  Depositos em casas
  Saques de casas
  Resultado operacional
```

## 10.3 Fluxo por casa

```text
Betano
  Total depositado via banco
  Total depositado via cripto
  Total sacado via banco
  Total sacado via cripto
  Saldo atual
  Resultado operacional
```

## 10.4 Taxas cripto

```text
Taxas de rede
Taxas de corretora
Spread/cambio estimado
```

Isso ajuda a saber se operar via BTC/LTC/USDT esta custando caro.

---

## 11. Services Sugeridos

## 11.1 CryptoBalanceService

Responsabilidades:

- Recalcular saldo BRL de `CryptoAccount`.
- Calcular saldo por ativo.
- Calcular saldo por instituicao.
- Ignorar transacoes pendentes/canceladas.

Assinaturas:

```php
public function recalculate(CryptoAccount $account): void;
public function totalBalanceBrl(): float;
public function balanceByInstitution(): Collection;
public function balanceByAsset(): Collection;
```

## 11.2 CryptoTransactionService

Responsabilidades:

- Criar transacoes cripto.
- Confirmar/cancelar transacoes.
- Vincular transacao cripto a Financeiro ou Bets.
- Recalcular saldos.

Assinaturas:

```php
public function create(array $data): CryptoTransaction;
public function update(CryptoTransaction $transaction, array $data): CryptoTransaction;
public function confirm(CryptoTransaction $transaction): void;
public function cancel(CryptoTransaction $transaction): void;
public function delete(CryptoTransaction $transaction): void;
```

## 11.3 BetCryptoSettlementService

Responsabilidades:

- Criar deposito de bet via cripto.
- Criar saque de bet via cripto.
- Manter `BetTransaction` e `CryptoTransaction` sincronizadas.
- Desvincular corretamente quando o usuario trocar forma de liquidacao.

Assinaturas:

```php
public function depositToBet(array $betData, array $cryptoData): BetTransaction;
public function withdrawFromBet(array $betData, array $cryptoData): BetTransaction;
public function unlinkCrypto(BetTransaction $betTransaction): void;
```

---

## 12. Mudancas No Modulo Bets

Adicionar em `bet_transactions`:

```text
settlement_method       bank, crypto, manual
crypto_transaction_id   nullable
```

Possivel evolucao:

```text
finance_transaction_id e crypto_transaction_id nao devem coexistir na mesma BetTransaction.
```

Regra:

- `settlement_method = bank`: usa `finance_transaction_id`.
- `settlement_method = crypto`: usa `crypto_transaction_id`.
- `settlement_method = manual`: nao usa vinculo externo.

Ao editar uma transacao:

- Se trocar de bank para crypto, remover vinculo financeiro e criar/vincular cripto.
- Se trocar de crypto para bank, remover vinculo cripto e criar/vincular financeiro.
- Se trocar para manual, remover os dois vinculos.

Essa regra evita o problema que aconteceu com o checkbox do Financeiro.

---

## 13. Enums Sugeridos

```text
CryptoInstitutionType
  exchange
  wallet
  broker
  other

CryptoCustodyType
  exchange
  self_custody
  shared

CryptoTransactionType
  bank_deposit
  bank_withdrawal
  buy_crypto
  sell_crypto
  send_to_bet
  receive_from_bet
  send_to_wallet
  receive_from_wallet
  network_fee
  exchange_fee
  adjustment_credit
  adjustment_debit

CryptoTransactionStatus
  pending
  confirmed
  cancelled
  failed

BetSettlementMethod
  bank
  crypto
  manual
```

---

## 14. Fases De Implementacao

## Fase 1 - Cadastro e livro razao cripto

- Criar `CryptoInstitution`.
- Criar `CryptoAsset`.
- Criar `CryptoNetwork`.
- Criar `CryptoAccount`.
- Criar `CryptoTransaction`.
- Criar services de saldo.
- Criar CRUD de instituicoes, ativos, redes e contas.
- Criar extrato cripto.
- Exibir saldo em BRL por carteira/corretora.

Entrega:

- Voce consegue cadastrar Binance, CoinEx, Trust Wallet etc.
- Voce consegue cadastrar USDT, BTC, LTC.
- Voce consegue controlar saldo BRL por carteira.

## Fase 2 - Integracao Cripto com Bets

- Adicionar `settlement_method` em `BetTransaction`.
- Adicionar `crypto_transaction_id` em `BetTransaction`.
- Evoluir tela de transacao bet para escolher Banco, Cripto ou Manual.
- Criar `BetCryptoSettlementService`.
- Ao depositar via cripto:
  - cria saida na carteira cripto;
  - cria entrada na conta bet.
- Ao sacar via cripto:
  - cria saida na conta bet;
  - cria entrada na carteira cripto.

Entrega:

- O fluxo real casa de aposta via cripto fica rastreavel.
- O saldo da carteira cripto e o saldo da bet batem.

## Fase 3 - Integracao Cripto com Financeiro

- Banco -> corretora.
- Corretora -> banco.
- Criar ou reaproveitar categorias:
  - Cripto - Aporte
  - Cripto - Resgate
  - Cripto - Taxas
- Recalcular saldos bancarios.
- Relatorio de dinheiro em bancos, cripto e bets.

Entrega:

- Voce enxerga o ciclo completo: banco -> cripto -> bet -> cripto -> banco.

## Fase 4 - Analises avancadas

- Saldo por ativo em quantidade.
- Cotacao atual automatica opcional.
- Variacao cambial estimada.
- Taxas por rede.
- Performance por rota:
  - USDT TRC20
  - BTC
  - LTC
  - Binance
  - CoinEx
  - Trust Wallet
- Alertas:
  - carteira com saldo alto;
  - transacao pendente sem hash;
  - saque da bet pendente ha muitos dias;
  - taxa acima do normal.

---

## 15. Recomendacao De MVP

Eu nao comecaria tentando valorar carteira por cotacao em tempo real. Isso pode complicar demais.

MVP recomendado:

1. Criar contas/carteiras cripto.
2. Criar ativos e redes.
3. Registrar transacoes cripto sempre com `amount_brl`.
4. Registrar quantidade cripto, rede e hash como dados de auditoria.
5. Integrar deposito/saque de Bets com CryptoTransaction.
6. Dashboard simples mostrando:
   - saldo em bancos;
   - saldo em cripto;
   - saldo em bets;
   - total geral.

Depois disso, a fase avancada pode trazer:

- saldo por moeda;
- cotacao atual;
- ganho/perda cambial;
- importacao por API/extrato.

---

## 16. Exemplo Pratico Completo

## Deposito via USDT

```text
Banco Nubank -> Binance
R$ 2.000,00

Binance -> Betano Usuario X
370.50 USDT
Cotacao: R$ 5,3981
Valor BRL: R$ 2.000,00
Rede: TRC20
Taxa: 1 USDT
```

No sistema:

```text
Financeiro:
  Nubank -R$ 2.000,00

Cripto:
  Binance +R$ 2.000,00 bank_deposit
  Binance -R$ 2.000,00 send_to_bet

Bets:
  Betano Usuario X +R$ 2.000,00 deposit
```

## Saque via LTC

```text
Bet365 Usuario Y -> CoinEx
2.15 LTC
Cotacao: R$ 470,00
Valor BRL: R$ 1.010,50

CoinEx -> Banco Inter
R$ 1.000,00
Taxas/spread: R$ 10,50
```

No sistema:

```text
Bets:
  Bet365 Usuario Y -R$ 1.010,50 withdrawal

Cripto:
  CoinEx +R$ 1.010,50 receive_from_bet
  CoinEx -R$ 1.000,00 bank_withdrawal
  CoinEx -R$ 10,50 exchange_fee

Financeiro:
  Banco Inter +R$ 1.000,00
```

---

## 17. Pontos De Cuidado

- Nunca armazenar seed phrase, private key, senha ou 2FA.
- Sempre registrar hash quando existir.
- Sempre registrar rede, principalmente em USDT.
- Nao misturar lucro de bet com variacao cambial.
- Deposito/saque e fluxo de caixa; aposta/payout e resultado operacional.
- Taxa de rede deve ser visivel para saber custo real da operacao.
- Uma transacao bet deve ter no maximo um metodo de liquidacao ativo: banco, cripto ou manual.
- Transacoes pendentes nao devem alterar saldo.
- Ao desvincular, recalcular todos os saldos afetados.

---

## 18. Decisao Final Recomendada

Criar um pequeno modulo **Cripto** integrado ao Bets, e nao apenas campos soltos dentro de Bets.

Motivo:

- Voce tem varias corretoras/carteiras.
- Usa varias moedas.
- Usa varias redes.
- Movimenta em nome de varios usuarios.
- Precisa saber quanto esta parado em cada camada.

O desenho ideal fica:

```text
Financeiro controla bancos em BRL.
Cripto controla corretoras/carteiras em BRL com auditoria em moeda/rede/hash.
Bets controla casas de apostas e resultado operacional.
```

Assim o sistema continua simples no relatorio principal, mas forte o bastante para auditar a operacao real.

