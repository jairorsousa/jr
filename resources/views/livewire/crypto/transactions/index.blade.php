<div>
    @if (session('success'))
        <div class="mb-4"><x-jr.alert variant="success">{{ session('success') }}</x-jr.alert></div>
    @endif
    @if (session('error'))
        <div class="mb-4"><x-jr.alert variant="error">{{ session('error') }}</x-jr.alert></div>
    @endif

    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between mb-6">
        <div class="flex items-center gap-2">
            <button wire:click="previousMonth" class="p-2 rounded-xl text-mono-600 hover:bg-mono-100 transition-colors" title="Mes anterior">
                <span class="material-icons-outlined text-[22px]">chevron_left</span>
            </button>
            <div class="text-center min-w-[180px]">
                <h2 class="text-lg font-bold text-mono-900">{{ $monthLabel }}</h2>
                @unless($isCurrentMonth)
                    <button wire:click="goToCurrentMonth" class="text-xs text-primary-500 hover:underline font-medium">Voltar ao mes atual</button>
                @endunless
            </div>
            <button wire:click="nextMonth" class="p-2 rounded-xl text-mono-600 hover:bg-mono-100 transition-colors" title="Proximo mes">
                <span class="material-icons-outlined text-[22px]">chevron_right</span>
            </button>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <x-jr.button wire:click="openCreateModal('bank_deposit')" variant="mono" size="sm">
                <span class="material-icons-outlined text-[16px]">south_west</span>
                Aporte
            </x-jr.button>
            <x-jr.button wire:click="openCreateModal('bank_withdrawal')" variant="mono" size="sm">
                <span class="material-icons-outlined text-[16px]">north_east</span>
                Resgate
            </x-jr.button>
            <x-jr.button wire:click="openCreateModal('send_to_wallet')" variant="mono" size="sm">
                <span class="material-icons-outlined text-[16px]">sync_alt</span>
                Transferencia
            </x-jr.button>
            <x-jr.button wire:click="openCreateModal('send_to_bet')" variant="mono" size="sm">
                <span class="material-icons-outlined text-[16px]">sports_soccer</span>
                Envio Bet
            </x-jr.button>
            <x-jr.button wire:click="openCreateModal('receive_from_bet')" size="sm">
                <span class="material-icons-outlined text-[16px]">add</span>
                Receber Bet
            </x-jr.button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <x-jr.card>
            <p class="text-xs text-mono-600">Entradas confirmadas</p>
            <p class="text-xl font-bold text-up mt-1">R$ {{ number_format($inTotal, 2, ',', '.') }}</p>
        </x-jr.card>
        <x-jr.card>
            <p class="text-xs text-mono-600">Saidas confirmadas</p>
            <p class="text-xl font-bold text-down mt-1">R$ {{ number_format($outTotal, 2, ',', '.') }}</p>
        </x-jr.card>
    </div>

    <x-jr.card class="mb-4">
        <div class="flex flex-col xl:flex-row gap-3">
            <div class="flex-1">
                <x-jr.input wire:model.live.debounce.300ms="search" placeholder="Buscar por descricao..." icon="search" />
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <select wire:model.live="filterAccount" class="bg-mono-white border border-mono-200 rounded-pill px-3 h-10 text-xs font-medium text-mono-900 focus:border-primary-500 focus:ring-0">
                    <option value="">Conta</option>
                    @foreach($cryptoAccounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="filterAsset" class="bg-mono-white border border-mono-200 rounded-pill px-3 h-10 text-xs font-medium text-mono-900 focus:border-primary-500 focus:ring-0">
                    <option value="">Moeda</option>
                    @foreach($cryptoAssets as $asset)
                        <option value="{{ $asset->id }}">{{ $asset->symbol }}</option>
                    @endforeach
                </select>
                <select wire:model.live="filterType" class="bg-mono-white border border-mono-200 rounded-pill px-3 h-10 text-xs font-medium text-mono-900 focus:border-primary-500 focus:ring-0">
                    <option value="">Tipo</option>
                    @foreach($types as $item)
                        <option value="{{ $item->value }}">{{ $item->label() }}</option>
                    @endforeach
                </select>
                <select wire:model.live="filterStatus" class="bg-mono-white border border-mono-200 rounded-pill px-3 h-10 text-xs font-medium text-mono-900 focus:border-primary-500 focus:ring-0">
                    <option value="">Status</option>
                    @foreach($statuses as $item)
                        <option value="{{ $item->value }}">{{ $item->label() }}</option>
                    @endforeach
                </select>
                @if($search || $filterAccount || $filterType || $filterStatus || $filterAsset)
                    <button wire:click="clearFilters" class="text-xs text-primary-500 hover:underline font-medium">Limpar</button>
                @endif
            </div>
        </div>
    </x-jr.card>

    @if($transactions->isEmpty())
        <x-jr.card>
            <div class="text-center py-8">
                <span class="material-icons-outlined text-[48px] text-mono-200">receipt_long</span>
                <p class="text-mono-600 mt-2">Nenhuma transacao cripto encontrada.</p>
                <div class="mt-4"><x-jr.button wire:click="openCreateModal" size="sm">Criar primeira transacao</x-jr.button></div>
            </div>
        </x-jr.card>
    @else
        <x-jr.table>
            <x-slot name="head">
                <th class="px-4 py-3 text-left text-xs font-semibold text-mono-600 uppercase tracking-wider">Data</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-mono-600 uppercase tracking-wider">Descricao</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-mono-600 uppercase tracking-wider">Conta</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-mono-600 uppercase tracking-wider">Tipo</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-mono-600 uppercase tracking-wider">Valor</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-mono-600 uppercase tracking-wider">Status</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-mono-600 uppercase tracking-wider">Acoes</th>
            </x-slot>
            @foreach($transactions as $transaction)
                <tr class="border-t border-mono-100 hover:bg-mono-50/50 transition-colors">
                    <td class="px-4 py-3 text-sm text-mono-900 whitespace-nowrap">{{ $transaction->occurred_at->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3">
                        <p class="text-sm font-medium text-mono-900">{{ $transaction->description }}</p>
                        @if($transaction->financeTransaction)
                            <p class="text-xs text-mono-600">Financeiro: {{ $transaction->financeTransaction->account?->name }}</p>
                        @elseif($transaction->betTransaction)
                            <p class="text-xs text-mono-600">
                                Bet: {{ $transaction->betTransaction->betAccount?->name }}
                            </p>
                        @elseif($transaction->relatedTransaction)
                            <p class="text-xs text-mono-600">
                                Transferencia: {{ $transaction->relatedTransaction->cryptoAccount?->name }}
                            </p>
                        @elseif($transaction->tx_hash)
                            <p class="text-xs text-mono-600 truncate max-w-[260px]">Hash: {{ $transaction->tx_hash }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <p class="text-sm font-medium text-mono-900">{{ $transaction->cryptoAccount?->name }}</p>
                        <p class="text-xs text-mono-600">
                            {{ $transaction->cryptoAccount?->institution?->name }}
                            @if($transaction->asset) · {{ $transaction->asset->symbol }} @endif
                            @if($transaction->network) · {{ $transaction->network->name }} @endif
                        </p>
                    </td>
                    <td class="px-4 py-3">
                        <x-jr.badge variant="{{ $transaction->type->badge() }}" size="sm">
                            <span class="material-icons-outlined text-[13px]">{{ $transaction->type->icon() }}</span>
                            {{ $transaction->type->label() }}
                        </x-jr.badge>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <span class="text-sm font-semibold {{ $transaction->type->isIn() ? 'text-up' : ($transaction->type->isOut() ? 'text-down' : 'text-mono-900') }}">
                            {{ $transaction->type->isIn() ? '+' : ($transaction->type->isOut() ? '-' : '') }} R$ {{ number_format($transaction->amount_brl, 2, ',', '.') }}
                        </span>
                        @if($transaction->crypto_amount && $transaction->asset)
                            <p class="text-xs text-mono-600">{{ number_format($transaction->crypto_amount, 8, ',', '.') }} {{ $transaction->asset->symbol }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <x-jr.badge variant="{{ $transaction->status->badge() }}" size="sm">{{ $transaction->status->label() }}</x-jr.badge>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            @if($transaction->status !== \App\Enums\CryptoTransactionStatus::Confirmed)
                                <button wire:click="confirmTransaction('{{ $transaction->id }}')" class="p-1.5 rounded-lg text-mono-300 hover:text-up hover:bg-up-bg transition-colors" title="Confirmar">
                                    <span class="material-icons-outlined text-[16px]">check_circle</span>
                                </button>
                            @endif
                            @if($transaction->status === \App\Enums\CryptoTransactionStatus::Confirmed)
                                <button wire:click="cancelTransaction('{{ $transaction->id }}')" class="p-1.5 rounded-lg text-mono-300 hover:text-error hover:bg-down-bg transition-colors" title="Cancelar">
                                    <span class="material-icons-outlined text-[16px]">cancel</span>
                                </button>
                            @endif
                            <button wire:click="openEditModal('{{ $transaction->id }}')" class="p-1.5 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-100 transition-colors" title="Editar">
                                <span class="material-icons-outlined text-[16px]">edit</span>
                            </button>
                            <button wire:click="confirmDelete('{{ $transaction->id }}')" class="p-1.5 rounded-lg text-mono-300 hover:text-error hover:bg-down-bg transition-colors" title="Excluir">
                                <span class="material-icons-outlined text-[16px]">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-jr.table>
        <div class="mt-4">{{ $transactions->links() }}</div>
    @endif

    @if($showModal)
        @php $selectedType = \App\Enums\CryptoTransactionType::tryFrom($type); @endphp
        <div class="fixed inset-0 z-modal overflow-y-auto" wire:keydown.escape="$set('showModal', false)">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-3xl overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-mono-100">
                        <h3 class="text-lg font-bold text-mono-900">{{ $editingId ? 'Editar Transacao Cripto' : 'Nova Transacao Cripto' }}</h3>
                        <button wire:click="$set('showModal', false)" class="p-1 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                            <span class="material-icons-outlined text-[20px]">close</span>
                        </button>
                    </div>

                    <form wire:submit="save">
                        <div class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-mono-600 mb-1.5">{{ $selectedType === \App\Enums\CryptoTransactionType::SendToWallet ? 'Conta origem' : 'Conta/carteira cripto' }}</label>
                                    <select wire:model="crypto_account_id" class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                        <option value="">Selecione...</option>
                                        @foreach($cryptoAccounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->name }} · {{ $account->institution?->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('crypto_account_id') <p class="text-xs font-medium text-error mt-1.5 pl-4">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-mono-600 mb-1.5">Tipo</label>
                                    <select wire:model.live="type" class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                        @foreach($types as $item)
                                            <option value="{{ $item->value }}">{{ $item->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @if($selectedType === \App\Enums\CryptoTransactionType::SendToWallet)
                                    <div>
                                        <label class="block text-sm font-medium text-mono-600 mb-1.5">Conta destino</label>
                                        <select wire:model="target_crypto_account_id" class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                            <option value="">Selecione...</option>
                                            @foreach($cryptoAccounts as $account)
                                                <option value="{{ $account->id }}">{{ $account->name }} · {{ $account->institution?->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('target_crypto_account_id') <p class="text-xs font-medium text-error mt-1.5 pl-4">{{ $message }}</p> @enderror
                                    </div>
                                @endif
                                @if($selectedType?->affectsBet())
                                    <div>
                                        <label class="block text-sm font-medium text-mono-600 mb-1.5">Conta Bet vinculada</label>
                                        <select wire:model="bet_account_id" class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                            <option value="">Selecione...</option>
                                            @foreach($betAccounts as $account)
                                                <option value="{{ $account->id }}">{{ $account->name }} · {{ $account->bettingHouse?->name }} · {{ $account->betUser?->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('bet_account_id') <p class="text-xs font-medium text-error mt-1.5 pl-4">{{ $message }}</p> @enderror
                                    </div>
                                @endif
                                <div>
                                    <label class="block text-sm font-medium text-mono-600 mb-1.5">Status</label>
                                    <select wire:model.live="status" class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                        @foreach($statuses as $item)
                                            <option value="{{ $item->value }}">{{ $item->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <x-jr.input label="Valor em BRL" wire:model="amount_brl" type="number" step="0.01" icon="attach_money" :error="$errors->first('amount_brl')" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-mono-600 mb-1.5">Moeda</label>
                                    <select wire:model="crypto_asset_id" class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                        <option value="">Selecione...</option>
                                        @foreach($cryptoAssets as $asset)
                                            <option value="{{ $asset->id }}">{{ $asset->symbol }} · {{ $asset->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-mono-600 mb-1.5">Rede</label>
                                    <select wire:model="crypto_network_id" class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                        <option value="">Selecione...</option>
                                        @foreach($cryptoNetworks as $network)
                                            <option value="{{ $network->id }}">{{ $network->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <x-jr.input label="Quantidade cripto" wire:model="crypto_amount" type="number" step="0.0000000001" icon="currency_bitcoin" :error="$errors->first('crypto_amount')" />
                                <x-jr.input label="Cotacao em BRL" wire:model="exchange_rate_brl" type="number" step="0.00000001" icon="currency_exchange" :error="$errors->first('exchange_rate_brl')" />
                                <x-jr.input label="Taxa em BRL" wire:model="fee_brl" type="number" step="0.01" icon="receipt_long" :error="$errors->first('fee_brl')" />
                                <x-jr.input label="Taxa cripto" wire:model="fee_crypto_amount" type="number" step="0.0000000001" icon="receipt" :error="$errors->first('fee_crypto_amount')" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <x-jr.input label="Data e hora" wire:model="occurred_at" type="datetime-local" icon="event" :error="$errors->first('occurred_at')" />
                                <x-jr.input label="Hash/TxID" wire:model="tx_hash" icon="tag" :error="$errors->first('tx_hash')" />
                            </div>

                            <x-jr.input label="Descricao" wire:model="description" icon="description" :error="$errors->first('description')" />

                            @if($selectedType?->affectsFinance())
                                <div class="rounded-2xl border border-mono-100 bg-mono-50 p-4">
                                    <label class="flex items-center gap-3 cursor-pointer mb-3">
                                        <input type="checkbox" wire:model.live="sync_finance_transaction" class="rounded border-mono-200 text-primary-500 focus:ring-primary-500">
                                        <span class="text-sm font-semibold text-mono-900">Criar/vincular transacao no Financeiro</span>
                                    </label>
                                    @if($sync_finance_transaction)
                                        <label class="block text-sm font-medium text-mono-600 mb-1.5">Conta financeira</label>
                                        <select wire:model="finance_account_id" class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                            <option value="">Selecione...</option>
                                            @foreach($financeAccounts as $account)
                                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('finance_account_id') <p class="text-xs font-medium text-error mt-1.5 pl-4">{{ $message }}</p> @enderror
                                    @endif
                                </div>
                            @endif

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <x-jr.input label="Endereco origem" wire:model="from_address" icon="logout" :error="$errors->first('from_address')" />
                                <x-jr.input label="Endereco destino" wire:model="to_address" icon="login" :error="$errors->first('to_address')" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Observacoes</label>
                                <textarea wire:model="notes" rows="3" class="w-full bg-mono-white border border-mono-200 rounded-xl px-4 py-3 text-sm text-mono-900 focus:border-primary-500 focus:ring-0 resize-none"></textarea>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-mono-100 bg-mono-50">
                            <x-jr.button variant="mono" type="button" wire:click="$set('showModal', false)">Cancelar</x-jr.button>
                            <x-jr.button type="submit">{{ $editingId ? 'Salvar' : 'Criar Transacao' }}</x-jr.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if($showConfirmModal)
        <div class="fixed inset-0 z-modal overflow-y-auto">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showConfirmModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-md overflow-hidden">
                    <div class="px-6 py-5">
                        <h3 class="text-lg font-bold text-mono-900">Confirmar com Financeiro</h3>
                        <p class="text-sm text-mono-600 mt-2">Esta transacao movimenta banco e cripto. Selecione a conta financeira para manter os saldos alinhados.</p>
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-mono-600 mb-1.5">Conta financeira</label>
                            <select wire:model="confirm_finance_account_id" class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                <option value="">Selecione...</option>
                                @foreach($financeAccounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                @endforeach
                            </select>
                            @error('confirm_finance_account_id') <p class="text-xs font-medium text-error mt-1.5 pl-4">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-mono-100 bg-mono-50">
                        <x-jr.button variant="mono" wire:click="$set('showConfirmModal', false)">Cancelar</x-jr.button>
                        <x-jr.button wire:click="confirmWithFinance">Confirmar</x-jr.button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showDeleteModal)
        <div class="fixed inset-0 z-modal overflow-y-auto">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showDeleteModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-sm overflow-hidden">
                    <div class="px-6 py-5 text-center">
                        <div class="mx-auto w-12 h-12 rounded-full bg-down-bg flex items-center justify-center mb-4">
                            <span class="material-icons-outlined text-[24px] text-error">delete</span>
                        </div>
                        <h3 class="text-lg font-bold text-mono-900">Excluir transacao cripto?</h3>
                        <p class="text-sm text-mono-600 mt-2">Se houver vinculo com Financeiro ou Bets, ele tambem sera ajustado.</p>
                    </div>
                    <div class="flex items-center justify-center gap-3 px-6 py-4 border-t border-mono-100 bg-mono-50">
                        <x-jr.button variant="mono" wire:click="$set('showDeleteModal', false)">Cancelar</x-jr.button>
                        <x-jr.button variant="danger" wire:click="delete">Excluir</x-jr.button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
