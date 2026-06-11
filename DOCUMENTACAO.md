# Sistema JR - Documentacao Completa

> Plataforma completa de gestao financeira, CRM e WhatsApp construida com Laravel 12, Livewire 4 e Alpine.js 3.

---

## Indice

1. [Visao Geral](#1-visao-geral)
2. [Stack Tecnologica](#2-stack-tecnologica)
3. [Estrutura do Projeto](#3-estrutura-do-projeto)
4. [Configuracao e Deploy](#4-configuracao-e-deploy)
5. [Modulos do Sistema](#5-modulos-do-sistema)
   - [5.1 Dashboard](#51-dashboard)
   - [5.2 Financeiro](#52-financeiro)
   - [5.3 Agenda](#53-agenda)
   - [5.4 Tarefas](#54-tarefas)
   - [5.5 CRM](#55-crm)
   - [5.6 WhatsApp](#56-whatsapp)
   - [5.7 Configuracoes](#57-configuracoes)
6. [Banco de Dados](#6-banco-de-dados)
7. [Services (Camada de Servico)](#7-services-camada-de-servico)
8. [Eventos e Tempo Real](#8-eventos-e-tempo-real)
9. [Filas e Jobs](#9-filas-e-jobs)
10. [Rotas](#10-rotas)
11. [Componentes de UI](#11-componentes-de-ui)
12. [Comandos de Deploy](#12-comandos-de-deploy)

---

## 1. Visao Geral

O **Sistema JR** e uma plataforma web completa para gestao pessoal e empresarial, integrando:

- **Gestao Financeira** — contas bancarias, transacoes, cartoes de credito, investimentos, comparacao de periodos, importacao OFX
- **CRM de Vendas** — pipeline Kanban, contatos, negocios, produtos, timeline de atividades
- **WhatsApp Business** — integracao com Evolution API para gerenciar conversas, enviar/receber mensagens em tempo real, templates e campanhas em massa
- **Agenda e Tarefas** — calendario com eventos recorrentes e listas de tarefas com prioridades

---

## 2. Stack Tecnologica

### Backend

| Tecnologia | Versao | Funcao |
|---|---|---|
| PHP | ^8.2 | Linguagem do servidor |
| Laravel | ^12.0 | Framework principal |
| Livewire | ^4.2 | Componentes reativos (SPA-like) |
| Volt | ^1.10 | Componentes Livewire em arquivo unico |
| Laravel Reverb | ^1.10 | Servidor WebSocket nativo |
| Laravel Breeze | ^2.4 | Scaffolding de autenticacao |
| Pest | ^3.8 | Framework de testes |

### Frontend

| Tecnologia | Versao | Funcao |
|---|---|---|
| Alpine.js | ^3.4.2 | Reatividade JavaScript leve |
| Tailwind CSS | ^3.1+ | Framework CSS utilitario |
| Laravel Echo | ^2.3.3 | Cliente WebSocket |
| Pusher.js | ^8.5.0 | Protocolo de broadcasting |
| Vite | ^7.0.7 | Bundler e dev server |
| Axios | ^1.11.0 | Cliente HTTP |

### Infraestrutura

| Servico | Funcao |
|---|---|
| MySQL | Banco de dados relacional |
| Redis | Cache, sessoes e filas |
| Nginx | Servidor web / proxy reverso |
| Docker | Containerizacao |
| Evolution API | Gateway WhatsApp (Baileys) |

### Design System

- **Cor primaria:** `#ff6f00` (laranja)
- **Inputs:** formato pill (rounded-pill)
- **Icones:** Material Icons Outlined
- **Chaves primarias:** UUID em todas as tabelas (trait HasUuids)

---

## 3. Estrutura do Projeto

```
jr/
├── app/
│   ├── Enums/                    # 15 enums PHP 8.1+
│   │   ├── AccountType.php
│   │   ├── ActivityType.php
│   │   ├── CampaignStatus.php
│   │   ├── CardBrand.php
│   │   ├── DealStage.php
│   │   ├── DealStatus.php
│   │   ├── InstanceStatus.php
│   │   ├── InvestmentType.php
│   │   ├── MessageStatus.php
│   │   ├── MessageType.php
│   │   ├── Priority.php
│   │   ├── RecurrenceType.php
│   │   ├── TaskStatus.php
│   │   ├── TemplateCategory.php
│   │   └── TransactionType.php
│   ├── Events/                   # Eventos de broadcast
│   │   ├── NewWhatsAppMessage.php
│   │   ├── WhatsAppConnectionUpdated.php
│   │   └── WhatsAppMessageStatusUpdated.php
│   ├── Http/Controllers/
│   │   └── WhatsAppWebhookController.php
│   ├── Jobs/
│   │   └── ProcessWhatsAppCampaign.php
│   ├── Livewire/                 # Componentes Livewire
│   │   ├── Agenda/
│   │   │   └── Calendario.php
│   │   ├── Configuracoes.php
│   │   ├── Crm/
│   │   │   ├── Contatos.php
│   │   │   ├── Negocio.php
│   │   │   ├── Pipeline.php
│   │   │   └── Produtos.php
│   │   ├── Dashboard.php
│   │   ├── Financeiro/
│   │   │   ├── Cartoes.php
│   │   │   ├── Categorias.php
│   │   │   ├── Comparacao.php
│   │   │   ├── Contas.php
│   │   │   ├── Fatura.php
│   │   │   ├── ImportarOfx.php
│   │   │   ├── Investimentos.php
│   │   │   ├── Transacoes.php
│   │   │   └── Transferencia.php
│   │   ├── Tarefas/
│   │   │   └── Index.php
│   │   └── WhatsApp/
│   │       ├── Campanhas.php
│   │       ├── Chat.php
│   │       ├── Instancias.php
│   │       └── Templates.php
│   ├── Models/                   # 20 modelos Eloquent
│   │   ├── Account.php
│   │   ├── Category.php
│   │   ├── Contact.php
│   │   ├── CreditCard.php
│   │   ├── CreditCardInvoice.php
│   │   ├── Deal.php
│   │   ├── DealActivity.php
│   │   ├── Event.php
│   │   ├── Investment.php
│   │   ├── Product.php
│   │   ├── Task.php
│   │   ├── TaskList.php
│   │   ├── Transaction.php
│   │   ├── User.php
│   │   ├── WhatsAppCampaign.php
│   │   ├── WhatsAppCampaignRecipient.php
│   │   ├── WhatsAppConversation.php
│   │   ├── WhatsAppInstance.php
│   │   ├── WhatsAppMessage.php
│   │   └── WhatsAppTemplate.php
│   └── Services/                 # Servicos de negocio
│       ├── BalanceService.php
│       ├── EvolutionApiService.php
│       ├── InvoiceService.php
│       ├── OfxParserService.php
│       └── RecurrenceService.php
├── config/
│   ├── broadcasting.php          # Reverb WebSocket config
│   ├── reverb.php                # Reverb server config
│   └── services.php              # Evolution API keys
├── database/migrations/          # ~25 migration files
├── docker-compose.yml            # 6 servicos Docker
├── resources/
│   ├── js/bootstrap.js           # Echo + Reverb init
│   └── views/
│       ├── components/jr/        # UI components (button, badge, alert, card, input, table, modal)
│       ├── layouts/              # app, guest, header, sidebar
│       └── livewire/             # Blade views dos componentes Livewire
├── routes/
│   ├── web.php                   # Todas as rotas web
│   ├── channels.php              # Canais de broadcast publicos
│   └── auth.php                  # Rotas de autenticacao (Breeze)
└── bootstrap/app.php             # Middleware, CSRF exception
```

---

## 4. Configuracao e Deploy

### Variaveis de Ambiente (.env)

```env
# Aplicacao
APP_NAME=JR
APP_URL=http://localhost:8080
APP_LOCALE=pt_BR

# Banco de Dados
DB_CONNECTION=mysql
DB_HOST=host.docker.internal
DB_PORT=3306
DB_DATABASE=jr_db
DB_USERNAME=jr_user
DB_PASSWORD=jr_secret

# Cache, Sessao e Fila
SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PORT=6379

# Broadcasting (WebSocket)
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=jr-app
REVERB_APP_KEY=jr-reverb-key
REVERB_APP_SECRET=jr-reverb-secret
REVERB_HOST=localhost
REVERB_PORT=8085

# Frontend Echo
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"

# WhatsApp (Evolution API)
EVOLUTION_API_URL=http://localhost:8084
EVOLUTION_API_KEY=sua-chave-aqui
```

### Docker Compose

O projeto roda com 6 servicos:

| Servico | Porta | Funcao |
|---|---|---|
| **nginx** | 8080 | Servidor web |
| **app** | -- | PHP-FPM (aplicacao) |
| **redis** | 6379 | Cache, sessao, fila |
| **queue** | -- | Worker de filas (campanhas) |
| **scheduler** | -- | Agendador de tarefas |
| **reverb** | 8085 | Servidor WebSocket |

### Comandos de Deploy

```bash
# Primeira vez
composer install
npm install && npm run build
php artisan migrate
php artisan storage:link
php artisan key:generate

# Atualizacao
php artisan migrate --force
npm run build
php artisan optimize:clear && php artisan optimize

# Servicos
php artisan reverb:start          # WebSocket server
php artisan queue:work             # Queue worker
php artisan schedule:run           # Scheduler
```

---

## 5. Modulos do Sistema

### 5.1 Dashboard

**Rota:** `GET /dashboard`
**Componente:** `App\Livewire\Dashboard`

Painel central com visao geral de todos os modulos:

- **Saldo total** de todas as contas ativas
- **Receitas e despesas** do mes atual
- **Grafico de barras** comparando receitas vs despesas dos ultimos meses
- **Fatura atual** do cartao principal
- **Contas a pagar** proximas do vencimento
- **Eventos** do calendario para os proximos dias
- **Tarefas pendentes** por prioridade
- **Patrimonio** (saldo + investimentos)

**Acoes rapidas:** marcar transacao como paga diretamente do dashboard.

---

### 5.2 Financeiro

#### 5.2.1 Contas Bancarias

**Rota:** `GET /financeiro/contas`
**Componente:** `App\Livewire\Financeiro\Contas`

- CRUD completo de contas (corrente, poupanca, investimento, carteira, outro)
- Saldo inicial e saldo atual (recalculado automaticamente)
- Ativar/desativar conta
- Cor e icone personalizaveis
- Totalizador geral de saldos

#### 5.2.2 Transacoes

**Rota:** `GET /financeiro/transacoes`
**Componente:** `App\Livewire\Financeiro\Transacoes`

- Listagem paginada (20 por pagina) com navegacao mensal
- Filtros: tipo (receita/despesa/transferencia), categoria, conta, status (pago/pendente), busca texto
- Range customizado de datas
- Suporte a transacoes recorrentes (diaria, semanal, mensal, anual)
- Suporte a parcelas de cartao de credito
- Marcar como pago com data de pagamento
- Totais do periodo: receitas, despesas, saldo

#### 5.2.3 Categorias

**Rota:** `GET /financeiro/categorias`
**Componente:** `App\Livewire\Financeiro\Categorias`

- CRUD de categorias de receita e despesa
- Hierarquia (categorias pai/filho)
- Cor e icone personalizaveis
- Separacao visual entre categorias de receita e despesa

#### 5.2.4 Cartoes de Credito

**Rota:** `GET /financeiro/cartoes`
**Componente:** `App\Livewire\Financeiro\Cartoes`

- CRUD de cartoes (Visa, Mastercard, Elo, Amex, Outro)
- Limite de credito, dia de fechamento e dia de vencimento
- Valor da fatura atual calculado automaticamente
- Vinculacao com conta bancaria para pagamento
- Ativar/desativar

#### 5.2.5 Fatura do Cartao

**Rota:** `GET /financeiro/cartoes/{id}/fatura`
**Componente:** `App\Livewire\Financeiro\Fatura`

- Visualizacao mensal da fatura
- Listagem de transacoes da fatura
- Fechar/reabrir fatura
- Pagar fatura (gera transacao automatica na conta vinculada)
- Navegacao entre meses

#### 5.2.6 Investimentos

**Rota:** `GET /financeiro/investimentos`
**Componente:** `App\Livewire\Financeiro\Investimentos`

- CRUD de investimentos (Cripto, Renda Fixa, Acoes, Fundos, Outro)
- Valor investido vs valor atual
- Calculo de lucro/prejuizo em R$ e percentual
- Atualizacao rapida de valor atual
- Grafico de distribuicao por tipo
- Totalizadores: investido, atual, rendimento total

#### 5.2.7 Comparacao Financeira

**Rota:** `GET /financeiro/comparacao`
**Componente:** `App\Livewire\Financeiro\Comparacao`

Ferramenta para comparacao lado a lado de dois periodos (Periodo A vs Periodo B):

- Navegacao por mes (botoes anterior/proximo) com suporte a **periodo personalizado** (selecao de datas)
- Filtros independentes por periodo: Conta, Categoria e Tipo (receita/despesa/transferencia)
- Botao "Copiar filtros" de A para B (e vice-versa)
- **Atalhos rapidos (presets):**
  - Mes anterior vs Atual
  - Mesmo mes do ano passado
  - Trimestre vs Trimestre
  - Ano anterior vs Atual
- Estatisticas por periodo: Receitas, Despesas, Saldo, Quantidade de transacoes
- Top 5 categorias (com cor e valor)
- Diferencas automaticas entre os periodos (valor absoluto + percentual)
- Opcao de visualizar lista detalhada das transacoes (ultimas 50) de cada lado

Ideal para analises mes a mes, ano a ano ou por trimestre.

#### 5.2.8 Importacao OFX

**Rota:** `GET /financeiro/importar-ofx`
**Componente:** `App\Livewire\Financeiro\ImportarOfx`

- Upload de arquivo OFX (formato bancario brasileiro)
- Parser SGML para bancos que nao usam XML puro
- Preview das transacoes antes de importar
- Atribuicao automatica de categorias por palavra-chave
- Selecao manual de categoria por transacao
- Remover/restaurar transacoes do lote
- Prevencao de duplicatas via FITID (identificador unico do banco)
- Resumo pos-importacao (importados, ignorados, receitas, despesas)

#### 5.2.9 Transferencias

**Componente:** `App\Livewire\Financeiro\Transferencia`

- Transferir valores entre contas
- Gera automaticamente: uma despesa na conta de origem e uma receita na conta de destino
- Descricao automatica com nomes das contas

---

### 5.3 Agenda

**Rota:** `GET /agenda`
**Componente:** `App\Livewire\Agenda\Calendario`

- Visualizacao mensal em grade (calendario completo)
- Criar/editar/excluir eventos
- Eventos o dia todo ou com horario
- Eventos recorrentes (diario, semanal, mensal, anual)
- Localizacao e cor personalizavel
- Lembretes configuravel (em minutos)
- Overlay de transacoes financeiras pendentes no calendario
- Detalhe do evento em modal
- Navegacao entre meses

---

### 5.4 Tarefas

**Rota:** `GET /tarefas`
**Componente:** `App\Livewire\Tarefas\Index`

- Multiplas listas de tarefas (com cor personalizavel)
- CRUD de listas e tarefas
- Prioridades: Baixa, Media, Alta, Urgente
- Status: Pendente, Em Progresso, Concluido, Cancelado
- Data de vencimento com indicador de atraso
- Reordenacao drag-and-drop
- Filtros por prioridade e status
- Contador de tarefas pendentes por lista
- Marcar como concluido com um clique

---

### 5.5 CRM

#### 5.5.1 Pipeline (Kanban)

**Rota:** `GET /crm/pipeline`
**Componente:** `App\Livewire\Crm\Pipeline`

- Board Kanban com 6 colunas: Lead, Contato Feito, Proposta, Negociacao, Ganho, Perdido
- Drag-and-drop de cards entre etapas
- Criar negocio diretamente na etapa desejada
- Filtros por produto e busca texto
- Totalizador do pipeline (valor total dos negocios abertos)
- Ganhos do mes
- Marcar como ganho/perdido rapidamente
- Log automatico de mudanca de etapa

**Etapas e cores:**

| Etapa | Cor | Icone |
|---|---|---|
| Lead | info | person_search |
| Contato Feito | primary | phone_callback |
| Proposta | warning | description |
| Negociacao | warning | handshake |
| Ganho | success | emoji_events |
| Perdido | error | block |

#### 5.5.2 Contatos

**Rota:** `GET /crm/contatos`
**Componente:** `App\Livewire\Crm\Contatos`

- CRUD completo de contatos
- Campos: nome, email, telefone, empresa, observacoes
- Ativar/desativar contato
- Busca por nome, email ou empresa
- Contagem de negocios vinculados
- Indicador de conversas WhatsApp vinculadas (com link direto para o chat)

#### 5.5.3 Produtos

**Rota:** `GET /crm/produtos`
**Componente:** `App\Livewire\Crm\Produtos`

- CRUD de produtos/servicos
- Campos: nome, descricao, preco, cor
- Ativar/desativar
- Contagem de negocios usando o produto

#### 5.5.4 Detalhe do Negocio

**Rota:** `GET /crm/negocios/{id}`
**Componente:** `App\Livewire\Crm\Negocio`

- Visao completa do negocio com layout 2/3 + 1/3
- **Coluna principal:**
  - Header com titulo, status, contato, produto, data
  - Barra de progresso de etapas (clicavel)
  - Botoes rapidos: Marcar como Ganho/Perdido, Reabrir
  - Timeline de atividades (notas, ligacoes, emails, reunioes, WhatsApp, mudancas de etapa)
  - Adicionar nova atividade via modal
- **Sidebar:**
  - Card do contato (nome, empresa, email, telefone)
  - Card do produto (nome, preco, descricao)
  - **Card de Conversas WhatsApp** — mostra conversas vinculadas ao negocio ou ao contato, com badge de nao-lidas e link para o chat
  - Detalhes do negocio (etapa, status, valor, previsao, datas)
  - Resumo de atividades por tipo

**Tipos de atividade:**

| Tipo | Icone | Cor |
|---|---|---|
| Nota | sticky_note_2 | mono |
| Ligacao | phone | info |
| Email | email | info |
| Reuniao | groups | primary |
| Mudanca de Etapa | swap_horiz | mono |
| WhatsApp | chat | success |

---

### 5.6 WhatsApp

#### 5.6.1 Instancias

**Rota:** `GET /whatsapp/instancias`
**Componente:** `App\Livewire\WhatsApp\Instancias`

Gerenciamento das conexoes WhatsApp via Evolution API:

- Criar/editar/excluir instancias
- Conectar via QR Code (exibido em modal com atualizacao em tempo real via WebSocket)
- Desconectar instancia
- Sincronizar status de todas as instancias
- Status: Conectado (verde), Desconectado (vermelho), Conectando (amarelo)
- Contagem de conversas por instancia

**Fluxo de conexao:**
1. Cria instancia na Evolution API
2. Configura webhook automaticamente
3. Solicita QR Code
4. Webhook recebe evento `qrcode.updated` -> broadcast em tempo real
5. Apos escanear, webhook recebe `connection.update` -> marca como conectado

#### 5.6.2 Chat (Conversas)

**Rota:** `GET /whatsapp/chat/{instanceId?}`
**Componente:** `App\Livewire\WhatsApp\Chat`

Interface completa de conversas estilo WhatsApp com layout de 3 colunas:

**Coluna esquerda — Lista de conversas:**
- Seletor de instancia
- Busca por nome/telefone
- Botao "Nova Conversa"
- Lista de conversas com avatar, nome, ultima mensagem, horario
- Badge verde de contato CRM vinculado
- Badge de mensagens nao lidas

**Coluna central — Area de chat:**
- Header com info do contato, badges CRM/negocio
- Separadores de data (Hoje, Ontem, dd/mm/yyyy)
- Bolhas de mensagem com estilo WhatsApp (verde para enviadas, branco para recebidas)
- Suporte a tipos de midia:
  - **Texto** — renderizacao com quebra de linha
  - **Imagem** — thumbnail clicavel que abre em nova aba
  - **Audio** — player nativo com controles
  - **Video** — player nativo com controles
  - **Documento** — card com icone por extensao (PDF vermelho, DOC azul, XLS verde), nome do arquivo e botao de download
  - **Sticker** — imagem 128x128
  - **Localizacao** — icone indicativo
- Status da mensagem (enviado, entregue, lido) com icones de check
- **Upload de midia:**
  - Botao de clip ao lado do campo de texto
  - Aceita: imagens, videos, audios, PDF, DOC, XLS, ZIP, TXT, CSV (max 16MB)
  - Preview antes de enviar: thumbnail, nome, extensao, tamanho
  - Campo de legenda opcional
  - Spinner durante upload
- Campo de texto com auto-resize (max 120px)
- Envio com Enter (Shift+Enter para nova linha)
- Indicador de "Tempo real" no canto inferior (WebSocket conectado)

**Coluna direita — Painel CRM (toggle):**
- **Contato vinculado:** nome, empresa, email, telefone, link para CRM
- **Acoes de contato:** auto-vincular (por telefone), buscar contato, criar contato, desvincular
- **Negocio vinculado:** titulo, etapa, valor, previsao, link para detalhe
- **Acoes de negocio:** vincular, trocar, desvincular
- **Lista de negocios do contato** com link direto

**Vinculacao automatica (auto-link):**
Quando uma mensagem chega, o webhook compara os ultimos 9 digitos do telefone com os contatos do CRM para vincular automaticamente.

**Nova conversa:** informar numero com DDI (ex: 5511999998888), auto-vincula contato se encontrar.

**Tempo real (WebSocket):**
- Mensagens novas aparecem instantaneamente
- Status de mensagens atualiza em tempo real (entregue, lido)
- Som de notificacao para mensagens recebidas
- Canais: `whatsapp.instance.{id}`, `whatsapp.chat.{id}`

#### 5.6.3 Templates de Mensagem

**Rota:** `GET /whatsapp/templates`
**Componente:** `App\Livewire\WhatsApp\Templates`

- CRUD completo de templates
- Grid visual com cards
- **Categorias:** Geral, Marketing, Vendas, Suporte, Follow-up, Saudacao, Lembrete
- **Variaveis dinamicas:** `{nome}`, `{empresa}`, `{telefone}`, `{email}`
  - Extraidas automaticamente do corpo da mensagem
  - Exibidas como badges nos cards
- Preview estilo WhatsApp (bolha verde com dados de exemplo)
- Duplicar template
- Ativar/desativar
- Contador de usos
- Filtro por categoria e busca texto

**Exemplo de template:**
```
Ola {nome}! Tudo bem?

Sou da equipe da {empresa} e gostaria de apresentar nossos servicos.

Podemos agendar uma conversa?
```

#### 5.6.4 Campanhas (Mensagens em Massa)

**Rota:** `GET /whatsapp/campanhas`
**Componente:** `App\Livewire\WhatsApp\Campanhas`

Envio de mensagens em massa com controle total:

**Criar campanha:**
- Nome da campanha
- Selecionar instancia WhatsApp (somente conectadas)
- Tipo de mensagem: Template ou Personalizada
- Se template: selecionar template ativo
- Se personalizada: campo de texto com variaveis `{nome}`, `{telefone}`
- Intervalo entre envios: slider de 1 a 60 segundos (prevencao de bloqueio)

**Gerenciar destinatarios (3 modos):**
1. **Manual** — informar telefone e nome, um por vez
2. **Importar Contatos CRM** — buscar e selecionar contatos com telefone (preenche variaveis automaticamente: nome, empresa, email)
3. **Colar Numeros** — formato `telefone|nome` por linha (nome opcional)

**Acoes da campanha:**
- **Iniciar** (play) — despacha job na fila, verifica instancia conectada
- **Pausar** — interrompe envio, pode retomar depois
- **Retomar** — continua de onde parou
- **Cancelar** — encerra campanha definitivamente
- **Duplicar** — cria copia com todos os destinatarios
- **Excluir** — remove campanha (somente se nao estiver enviando)

**Monitoramento:**
- Barra de progresso com percentual
- Contadores: enviados, falhas, pendentes
- Modal de detalhes com:
  - 4 cards de metricas (total, enviados, falhas, pendentes)
  - Barra de progresso colorida (verde = enviados, vermelho = falhas)
  - Info da campanha (instancia, template, intervalo, datas)
  - Tabela de destinatarios com status individual e erro (se falhou)
  - Auto-refresh a cada 5s durante envio (wire:poll)

**Status da campanha:**

| Status | Label | Cor | Icone |
|---|---|---|---|
| Draft | Rascunho | neutral | edit_note |
| Scheduled | Agendada | info | schedule |
| Sending | Enviando | warning | send |
| Paused | Pausada | neutral | pause_circle |
| Completed | Concluida | success | check_circle |
| Cancelled | Cancelada | error | cancel |

---

### 5.7 Configuracoes

**Rota:** `GET /configuracoes`
**Componente:** `App\Livewire\Configuracoes`

- Editar perfil (nome, email)
- Alterar senha (validacao de senha atual)
- Excluir conta

---

## 6. Banco de Dados

### Diagrama de Tabelas

```
users
├── accounts ──────────── transactions
│                          ├── category
│                          └── credit_card_invoice
├── credit_cards ──────── credit_card_invoices
│                          └── transactions
├── investments
├── events
├── task_lists ────────── tasks
├── contacts ──────────── deals ──────── deal_activities
│   │                      └── product
│   └── whatsapp_conversations
├── whatsapp_instances ── whatsapp_conversations ── whatsapp_messages
│                          ├── contact (nullable)
│                          └── deal (nullable)
├── whatsapp_message_templates ── whatsapp_campaigns
│                                  └── whatsapp_campaign_recipients
│                                       └── contact (nullable)
```

### Tabelas e Campos Principais

#### Financeiro

**accounts** — id (uuid), name, type (enum), bank, initial_balance, current_balance, color, icon, is_active

**categories** — id (uuid), name, type (income/expense), color, icon, parent_id (self-ref)

**transactions** — id (uuid), account_id, category_id, credit_card_id, credit_card_invoice_id, type, description, fitid (OFX dedup), amount, date, due_date, paid_at, is_paid, is_recurring, recurrence_type, recurrence_end, installment_number, installment_total, notes, tags (json)

**credit_cards** — id (uuid), name, last_digits, brand, credit_limit, closing_day, due_day, color, account_id, is_active

**credit_card_invoices** — id (uuid), credit_card_id, reference_month, total_amount, due_date, paid_at, is_paid, is_closed

**investments** — id (uuid), name, type (enum), broker, invested_amount, current_amount, quantity, purchase_date, maturity_date, notes

#### Agenda e Tarefas

**events** — id (uuid), title, description, start_at, end_at, is_all_day, location, color, reminder_minutes, is_recurring, recurrence_type, recurrence_end

**task_lists** — id (uuid), name, color, sort_order

**tasks** — id (uuid), title, description, priority (enum), status (enum), due_date, completed_at, list_id, sort_order

#### CRM

**contacts** — id (uuid), name, email, phone, company, notes, is_active

**products** — id (uuid), name, description, price, is_active, color

**deals** — id (uuid), title, contact_id, product_id, stage (enum), status (enum), value, expected_close_date, closed_at, sort_order, notes

**deal_activities** — id (uuid), deal_id, type (enum), description, happened_at

#### WhatsApp

**whatsapp_instances** — id (uuid), name, instance_name (unique), phone, status (enum), qrcode (text), settings (json), connected_at

**whatsapp_conversations** — id (uuid), instance_id, contact_id (nullable), deal_id (nullable), remote_jid, contact_name, contact_phone, profile_pic_url, last_message, last_message_at, unread_count, is_group. Unique: [instance_id, remote_jid]

**whatsapp_messages** — id (uuid), conversation_id, message_id, type (enum), body, media_url, media_mimetype, media_filename, from_me, status (enum), raw_data (json), message_at

**whatsapp_message_templates** — id (uuid), name, body, category (enum), is_active, usage_count

**whatsapp_campaigns** — id (uuid), name, instance_id, template_id (nullable), status (enum), custom_message, scheduled_at, started_at, completed_at, total_recipients, sent_count, failed_count, delay_seconds

**whatsapp_campaign_recipients** — id (uuid), campaign_id, contact_id (nullable), phone, name, variables (json), status, sent_at, error_message, message_id. Unique: [campaign_id, phone]

---

## 7. Services (Camada de Servico)

### BalanceService

Recalcula o saldo de uma conta a partir de todas as transacoes pagas:
- Saldo = saldo_inicial + receitas - despesas
- Metodo `totalBalance()` retorna soma de todas as contas ativas

### InvoiceService

Gerencia o ciclo de faturas de cartoes de credito:
- Cria fatura automaticamente com base no dia de fechamento
- Calcula total da fatura somando transacoes
- Fecha/reabre faturas
- Paga fatura gerando transacao automatica na conta vinculada
- Gera parcelas para compras parceladas

### RecurrenceService

Gera transacoes futuras para lancamentos recorrentes:
- Projecao de ate 30 dias a frente
- Suporta: diario, semanal, mensal, anual
- Respeita data de fim da recorrencia

### OfxParserService

Parser de arquivos OFX (extrato bancario):
- Suporta formato SGML (bancos brasileiros) e XML
- Extrai: FITID, data, valor, descricao, tipo (debito/credito)
- FITID usado para prevenir importacao duplicada

### EvolutionApiService

Cliente HTTP para a Evolution API (WhatsApp):

| Metodo | Funcao |
|---|---|
| `createInstance(name, options)` | Criar instancia WhatsApp |
| `deleteInstance(name)` | Excluir instancia |
| `getInstanceStatus(name)` | Status da conexao |
| `connectInstance(name)` | Solicitar QR Code |
| `disconnectInstance(name)` | Desconectar (logout) |
| `restartInstance(name)` | Reiniciar instancia |
| `fetchInstances()` | Listar todas as instancias |
| `setWebhook(name, url)` | Configurar webhook |
| `sendText(name, number, text)` | Enviar mensagem de texto |
| `sendMedia(name, number, type, url, caption)` | Enviar imagem/video/audio |
| `sendDocument(name, number, url, fileName)` | Enviar documento |
| `fetchProfilePicture(name, number)` | Buscar foto do perfil |
| `isOnWhatsApp(name, numbers)` | Verificar se numeros tem WhatsApp |

---

## 8. Eventos e Tempo Real

### Arquitetura de Tempo Real

```
Evolution API  ──webhook──>  WhatsAppWebhookController
                                      │
                                      ▼
                              Broadcast Event
                              (ShouldBroadcastNow)
                                      │
                                      ▼
                              Laravel Reverb
                             (WebSocket Server)
                                      │
                                      ▼
                              Laravel Echo (JS)
                                      │
                                      ▼
                              Alpine.js listener
                                      │
                                      ▼
                              Livewire dispatch
                              ($wire.dispatch)
```

### Eventos de Broadcast

| Evento | Canal | Nome | Dados |
|---|---|---|---|
| `NewWhatsAppMessage` | `whatsapp.chat.{id}` + `whatsapp.instance.{id}` | `.message.new` | message, conversation |
| `WhatsAppMessageStatusUpdated` | `whatsapp.chat.{id}` | `.message.status` | messageId, status |
| `WhatsAppConnectionUpdated` | `whatsapp.instance.{id}` | `.connection.updated` | instance (id, status, qrcode) |

### Canais (channels.php)

Todos os canais sao **publicos** (sem autenticacao), pois os dados vem do webhook:

```php
Broadcast::channel('whatsapp.instance.{instanceId}', fn() => true);
Broadcast::channel('whatsapp.chat.{conversationId}', fn() => true);
```

### Webhook Controller

**Rota:** `POST /api/whatsapp/webhook` (sem CSRF)

Eventos tratados:

| Evento Evolution API | Handler | Acoes |
|---|---|---|
| `messages.upsert` | `handleMessagesUpsert()` | Cria/atualiza conversa e mensagem, auto-link CRM, log atividade, broadcast |
| `messages.update` | `handleMessagesUpdate()` | Atualiza status (delivered/read), broadcast |
| `connection.update` | `handleConnectionUpdate()` | Atualiza status da instancia, broadcast |
| `qrcode.updated` | `handleQrcodeUpdated()` | Armazena novo QR Code, broadcast |

---

## 9. Filas e Jobs

### ProcessWhatsAppCampaign

**Fila:** default (Redis)
**Timeout:** 7200 segundos (2 horas)
**Tentativas:** 1

Processa o envio de uma campanha em massa:

1. Carrega campanha com template e instancia
2. Itera sobre destinatarios pendentes (`status = 'pending'`)
3. Para cada destinatario:
   - Verifica se campanha foi pausada/cancelada (refresh do model)
   - Monta mensagem (template com variaveis ou mensagem customizada)
   - Envia via `EvolutionApiService::sendText()`
   - Atualiza status do destinatario (sent/failed)
   - Incrementa contadores da campanha
   - Aplica delay configurado entre envios
4. Ao finalizar todos os pendentes, marca campanha como concluida
5. Incrementa contador de uso do template

**Variaveis automaticas:**
- `{nome}` — preenchido com nome do destinatario
- `{telefone}` — preenchido com telefone do destinatario
- `{empresa}` — preenchido com empresa do contato CRM (se importado)
- `{email}` — preenchido com email do contato CRM (se importado)

---

## 10. Rotas

### Rotas Web (autenticadas)

| Metodo | URI | Nome | Componente |
|---|---|---|---|
| GET | `/dashboard` | dashboard | Dashboard |
| GET | `/financeiro/contas` | financeiro.contas | Financeiro\Contas |
| GET | `/financeiro/transacoes` | financeiro.transacoes | Financeiro\Transacoes |
| GET | `/financeiro/categorias` | financeiro.categorias | Financeiro\Categorias |
| GET | `/financeiro/cartoes` | financeiro.cartoes | Financeiro\Cartoes |
| GET | `/financeiro/cartoes/{id}/fatura` | financeiro.fatura | Financeiro\Fatura |
| GET | `/financeiro/investimentos` | financeiro.investimentos | Financeiro\Investimentos |
| GET | `/financeiro/comparacao` | financeiro.comparacao | Financeiro\Comparacao |
| GET | `/financeiro/importar-ofx` | financeiro.importar-ofx | Financeiro\ImportarOfx |
| GET | `/agenda` | agenda | Agenda\Calendario |
| GET | `/tarefas` | tarefas | Tarefas\Index |
| GET | `/crm/pipeline` | crm.pipeline | Crm\Pipeline |
| GET | `/crm/contatos` | crm.contatos | Crm\Contatos |
| GET | `/crm/negocios/{id}` | crm.negocio | Crm\Negocio |
| GET | `/crm/produtos` | crm.produtos | Crm\Produtos |
| GET | `/whatsapp/instancias` | whatsapp.instancias | WhatsApp\Instancias |
| GET | `/whatsapp/chat/{instanceId?}` | whatsapp.chat | WhatsApp\Chat |
| GET | `/whatsapp/templates` | whatsapp.templates | WhatsApp\Templates |
| GET | `/whatsapp/campanhas` | whatsapp.campanhas | WhatsApp\Campanhas |
| GET | `/configuracoes` | configuracoes | Configuracoes |

### Rota Publica (sem auth, sem CSRF)

| Metodo | URI | Nome | Controller |
|---|---|---|---|
| POST | `/api/whatsapp/webhook` | whatsapp.webhook | WhatsAppWebhookController@handle |

### Menu Lateral (Sidebar)

```
Menu
├── Inicio (dashboard)
├── Contas (financeiro.contas)
├── Transacoes (financeiro.transacoes)
├── Categorias (financeiro.categorias)
├── Cartoes (financeiro.cartoes)
├── Investimentos (financeiro.investimentos)
├── Comparacao (financeiro.comparacao)
├── Agenda (agenda)
├── Tarefas (tarefas)
├── Pipeline (crm.pipeline)
├── Contatos (crm.contatos)
├── Produtos (crm.produtos)
├── WhatsApp (whatsapp.instancias)
├── Conversas (whatsapp.chat)
├── Templates (whatsapp.templates)
└── Campanhas (whatsapp.campanhas)

Sistema
├── Configuracoes
└── Sair
```

---

## 11. Componentes de UI

O sistema utiliza componentes Blade reutilizaveis no namespace `x-jr.*`:

| Componente | Funcao |
|---|---|
| `x-jr.button` | Botao com variantes (primary, mono, danger), tamanhos (sm, md, lg) |
| `x-jr.badge` | Badge/tag com cores (neutral, primary, success, warning, error, info) |
| `x-jr.alert` | Alerta de notificacao (success, error, warning, info) |
| `x-jr.card` | Card container com sombra e bordas arredondadas |
| `x-jr.input` | Input estilo pill com icone Material, label e mensagem de erro |
| `x-jr.table` | Tabela com slots header/body |
| `x-jr.modal` | Modal generico (maioria usa implementacao inline) |

---

## 12. Comandos de Deploy

### Primeira Instalacao

```bash
# Clonar repositorio
git clone <repo-url> jr && cd jr

# Backend
composer install
cp .env.example .env
php artisan key:generate
# Editar .env com credenciais reais

# Banco de dados
php artisan migrate

# Storage
php artisan storage:link

# Frontend
npm install
npm run build

# Iniciar servicos (Docker)
docker-compose up -d
```

### Atualizacao em Producao

```bash
# Backend
composer install --no-dev --optimize-autoloader
php artisan migrate --force

# Frontend
npm ci && npm run build

# Cache
php artisan optimize:clear
php artisan optimize

# Reiniciar servicos
docker-compose restart app queue scheduler reverb
```

### Servicos Necessarios

```bash
# WebSocket (obrigatorio para tempo real)
php artisan reverb:start --port=8085

# Worker de filas (obrigatorio para campanhas)
php artisan queue:work redis --timeout=7200

# Agendador (para recorrencias)
php artisan schedule:run
```

---

> Documentacao gerada em 03/04/2026 para o Sistema JR v1.0
