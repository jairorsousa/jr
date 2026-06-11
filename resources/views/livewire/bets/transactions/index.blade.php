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
            <x-jr.button wire:click="openCreateModal('deposit')" variant="mono" size="sm">
                <span class="material-icons-outlined text-[16px]">south_west</span>
                Deposito
            </x-jr.button>
            <x-jr.button wire:click="openCreateModal('withdrawal')" variant="mono" size="sm">
                <span class="material-icons-outlined text-[16px]">north_east</span>
                Saque
            </x-jr.button>
            <x-jr.button wire:click="openCreateModal('bet_stake')" size="sm">
                <span class="material-icons-outlined text-[16px]">add</span>
                Nova
            </x-jr.button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <x-jr.card>
            <p class="text-xs text-mono-600">Entradas confirmadas</p>
            <p class="text-xl font-bold text-up mt-1">R$ {{ number_format($inTotal, 2, ',', '.') }}</p>
        </x-jr.card>
        <x-jr.card>
            <p class="text-xs text-mono-600">Saidas confirmadas</p>
            <p class="text-xl font-bold text-down mt-1">R$ {{ number_format($outTotal, 2, ',', '.') }}</p>
        </x-jr.card>
        <x-jr.card>
            <p class="text-xs text-mono-600">Resultado operacional</p>
            <p class="text-xl font-bold {{ $profit >= 0 ? 'text-up' : 'text-down' }} mt-1">
                {{ $profit < 0 ? '-' : '' }}R$ {{ number_format(abs($profit), 2, ',', '.') }}
            </p>
        </x-jr.card>
        <x-jr.card>
            <p class="text-xs text-mono-600">ROI do periodo</p>
            <p class="text-xl font-bold text-mono-900 mt-1">{{ number_format($roi, 2, ',', '.') }}%</p>
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
                    @foreach($betAccounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="filterHouse" class="bg-mono-white border border-mono-200 rounded-pill px-3 h-10 text-xs font-medium text-mono-900 focus:border-primary-500 focus:ring-0">
                    <option value="">Casa</option>
                    @foreach($houses as $house)
                        <option value="{{ $house->id }}">{{ $house->name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="filterUser" class="bg-mono-white border border-mono-200 rounded-pill px-3 h-10 text-xs font-medium text-mono-900 focus:border-primary-500 focus:ring-0">
                    <option value="">Usuario</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
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
                @if($search || $filterAccount || $filterHouse || $filterUser || $filterType || $filterStatus)
                    <button wire:click="clearFilters" class="text-xs text-primary-500 hover:underline font-medium">Limpar</button>
                @endif
            </div>
        </div>
    </x-jr.card>

    @if($transactions->isEmpty())
        <x-jr.card>
            <div class="text-center py-8">
                <span class="material-icons-outlined text-[48px] text-mono-200">receipt_long</span>
                <p class="text-mono-600 mt-2">Nenhuma transacao de bet encontrada.</p>
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
                        @elseif($transaction->type->affectsFinance())
                            <p class="text-xs text-primary-500">Sem vinculo financeiro</p>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('bets.accounts.show', $transaction->betAccount?->id) }}" class="text-sm font-medium text-mono-900 hover:text-primary-500">
                            {{ $transaction->betAccount?->name }}
                        </a>
                        <p class="text-xs text-mono-600">{{ $transaction->betAccount?->bettingHouse?->name }} · {{ $transaction->betAccount?->betUser?->name }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <x-jr.badge variant="{{ $transaction->type->badge() }}" size="sm">
                            <span class="material-icons-outlined text-[13px]">{{ $transaction->type->icon() }}</span>
                            {{ $transaction->type->label() }}
                        </x-jr.badge>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <span class="text-sm font-semibold {{ $transaction->type->isIn() ? 'text-up' : 'text-down' }}">
                            {{ $transaction->type->isIn() ? '+' : '-' }} R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <x-jr.badge variant="{{ $transaction->status->badge() }}" size="sm">{{ $transaction->status->label() }}</x-jr.badge>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            @if($transaction->status !== \App\Enums\BetTransactionStatus::Confirmed)
                                <button wire:click="confirmTransaction('{{ $transaction->id }}')" class="p-1.5 rounded-lg text-mono-300 hover:text-up hover:bg-up-bg transition-colors" title="Confirmar">
                                    <span class="material-icons-outlined text-[16px]">check_circle</span>
                                </button>
                            @endif
                            @if($transaction->status === \App\Enums\BetTransactionStatus::Confirmed)
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
        @php $selectedType = \App\Enums\BetTransactionType::tryFrom($type); @endphp
        <div class="fixed inset-0 z-modal overflow-y-auto" wire:keydown.escape="$set('showModal', false)">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-3xl overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-mono-100">
                        <h3 class="text-lg font-bold text-mono-900">{{ $editingId ? 'Editar Transacao Bet' : 'Nova Transacao Bet' }}</h3>
                        <button wire:click="$set('showModal', false)" class="p-1 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                            <span class="material-icons-outlined text-[20px]">close</span>
                        </button>
                    </div>

                    <form wire:submit="save">
                        <div class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-mono-600 mb-1.5">Conta de bet</label>
                                    <select wire:model="bet_account_id" class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                        <option value="">Selecione...</option>
                                        @foreach($betAccounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->name }} · {{ $account->bettingHouse?->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('bet_account_id') <p class="text-xs font-medium text-error mt-1.5 pl-4">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-mono-600 mb-1.5">Tipo</label>
                                    <select wire:model.live="type" class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                        @foreach($types as $item)
                                            <option value="{{ $item->value }}">{{ $item->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-mono-600 mb-1.5">Status</label>
                                    <select wire:model.live="status" class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                        @foreach($statuses as $item)
                                            <option value="{{ $item->value }}">{{ $item->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <x-jr.input label="Valor" wire:model="amount" type="number" step="0.01" icon="attach_money" :error="$errors->first('amount')" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <x-jr.input label="Data e hora" wire:model="occurred_at" type="datetime-local" icon="event" :error="$errors->first('occurred_at')" />
                                <x-jr.input label="Referencia externa" wire:model="external_reference" icon="tag" :error="$errors->first('external_reference')" />
                            </div>

                            <x-jr.input label="Descricao" wire:model="description" icon="description" :error="$errors->first('description')" />

                            @if($selectedType?->affectsFinance() && $status === 'confirmed')
                                <div class="rounded-2xl border border-mono-100 bg-mono-50 p-4">
                                    <label class="flex items-center gap-3 cursor-pointer mb-3">
                                        <input type="checkbox" wire:model.live="create_finance_transaction" class="rounded border-mono-200 text-primary-500 focus:ring-primary-500">
                                        <span class="text-sm font-semibold text-mono-900">Criar/vincular transacao no Financeiro</span>
                                    </label>
                                    @if($create_finance_transaction)
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
                                <x-jr.input label="Evento" wire:model="event_name" placeholder="Ex: Flamengo x Palmeiras" icon="sports_soccer" :error="$errors->first('event_name')" />
                                <x-jr.input label="Mercado" wire:model="market_name" placeholder="Ex: Resultado final" icon="analytics" :error="$errors->first('market_name')" />
                                <x-jr.input label="Selecao" wire:model="selection_name" placeholder="Ex: Flamengo" icon="check" :error="$errors->first('selection_name')" />
                                <x-jr.input label="Odd" wire:model="odd" type="number" step="0.0001" icon="percent" :error="$errors->first('odd')" />
                                <x-jr.input label="Estrategia" wire:model="strategy" icon="tactic" :error="$errors->first('strategy')" />
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
                        <p class="text-sm text-mono-600 mt-2">Esta transacao e deposito/saque. Selecione a conta financeira para manter os saldos alinhados.</p>
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
                        <h3 class="text-lg font-bold text-mono-900">Excluir transacao?</h3>
                        <p class="text-sm text-mono-600 mt-2">Se houver transacao financeira vinculada, ela tambem sera removida.</p>
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
