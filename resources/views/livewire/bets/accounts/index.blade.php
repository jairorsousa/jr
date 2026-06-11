<div>
    @if (session('success'))
        <div class="mb-4"><x-jr.alert variant="success">{{ session('success') }}</x-jr.alert></div>
    @endif
    @if (session('error'))
        <div class="mb-4"><x-jr.alert variant="error">{{ session('error') }}</x-jr.alert></div>
    @endif

    <x-jr.card class="mb-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <p class="text-sm text-mono-600 font-medium">Saldo em contas de bet</p>
                <p class="text-3xl font-bold text-mono-900 mt-1">R$ {{ number_format($totalBalance, 2, ',', '.') }}</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
                <x-jr.button variant="mono" href="{{ route('bets.transactions') }}" size="sm">
                    <span class="material-icons-outlined text-[16px]">receipt_long</span>
                    Extrato
                </x-jr.button>
                <x-jr.button wire:click="openCreateModal">
                    <span class="material-icons-outlined text-[18px]">add</span>
                    Nova Conta
                </x-jr.button>
            </div>
        </div>
    </x-jr.card>

    <x-jr.card class="mb-4">
        <div class="flex flex-col xl:flex-row gap-3">
            <div class="flex-1">
                <x-jr.input wire:model.live.debounce.300ms="search" placeholder="Buscar por nome, login ou codigo..." icon="search" />
            </div>
            <div class="flex flex-wrap items-center gap-2">
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
                <select wire:model.live="filterStatus" class="bg-mono-white border border-mono-200 rounded-pill px-3 h-10 text-xs font-medium text-mono-900 focus:border-primary-500 focus:ring-0">
                    <option value="">Status</option>
                    @foreach($statuses as $item)
                        <option value="{{ $item->value }}">{{ $item->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </x-jr.card>

    @if($accounts->isEmpty())
        <x-jr.card>
            <div class="text-center py-8">
                <span class="material-icons-outlined text-[48px] text-mono-200">account_balance_wallet</span>
                <p class="text-mono-600 mt-2">Nenhuma conta de bet encontrada.</p>
                <div class="mt-4"><x-jr.button wire:click="openCreateModal" size="sm">Criar primeira conta</x-jr.button></div>
            </div>
        </x-jr.card>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($accounts as $account)
                <x-jr.card class="relative {{ !$account->is_active ? 'opacity-50' : '' }}">
                    <div class="absolute top-0 left-0 w-full h-1 rounded-t-2xl" style="background-color: {{ $account->bettingHouse?->color ?? '#ff6f00' }}"></div>

                    <div class="flex items-start justify-between pt-2">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-semibold text-mono-900 truncate">{{ $account->name }}</h3>
                                <x-jr.badge variant="{{ $account->status->badge() }}" size="sm">{{ $account->status->label() }}</x-jr.badge>
                            </div>
                            <p class="text-xs text-mono-600 mt-1 truncate">{{ $account->bettingHouse?->name }} · {{ $account->betUser?->name }}</p>
                            @if($account->username)
                                <p class="text-xs text-mono-300 mt-0.5 truncate">{{ $account->username }}</p>
                            @endif
                        </div>

                        <div class="flex items-center gap-1">
                            <a href="{{ route('bets.accounts.show', $account->id) }}" class="p-1.5 rounded-lg text-mono-300 hover:text-primary-500 hover:bg-primary-100 transition-colors" title="Detalhe">
                                <span class="material-icons-outlined text-[16px]">open_in_new</span>
                            </a>
                            <button wire:click="openEditModal('{{ $account->id }}')" class="p-1.5 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-100 transition-colors" title="Editar">
                                <span class="material-icons-outlined text-[16px]">edit</span>
                            </button>
                            <button wire:click="confirmDelete('{{ $account->id }}')" class="p-1.5 rounded-lg text-mono-300 hover:text-error hover:bg-down-bg transition-colors" title="Excluir">
                                <span class="material-icons-outlined text-[16px]">delete</span>
                            </button>
                        </div>
                    </div>

                    <div class="mt-5 pt-4 border-t border-mono-100">
                        <p class="text-xs text-mono-600">Saldo atual</p>
                        <p class="text-2xl font-bold text-mono-900 mt-0.5">R$ {{ number_format($account->current_balance, 2, ',', '.') }}</p>
                        <div class="grid grid-cols-2 gap-3 mt-4">
                            <div>
                                <p class="text-xs text-mono-600">Bonus</p>
                                <p class="text-sm font-semibold text-mono-900">R$ {{ number_format($account->bonus_balance, 2, ',', '.') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-mono-600">Sacavel</p>
                                <p class="text-sm font-semibold text-mono-900">
                                    {{ $account->withdrawable_balance !== null ? 'R$ ' . number_format($account->withdrawable_balance, 2, ',', '.') : '—' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 mt-4">
                        <x-jr.button href="{{ route('bets.transactions', ['filterAccount' => $account->id]) }}" variant="mono" size="sm">
                            <span class="material-icons-outlined text-[15px]">receipt_long</span>
                            Extrato
                        </x-jr.button>
                        <button wire:click="markChecked('{{ $account->id }}')" class="h-9 px-3 rounded-pill bg-mono-100 text-mono-900 text-xs font-semibold hover:bg-mono-200 transition-colors">
                            Conferir
                        </button>
                        <button wire:click="recalculate('{{ $account->id }}')" class="h-9 px-3 rounded-pill bg-mono-100 text-mono-900 text-xs font-semibold hover:bg-mono-200 transition-colors">
                            Recalcular
                        </button>
                    </div>
                </x-jr.card>
            @endforeach
        </div>
    @endif

    @if($showModal)
        <div class="fixed inset-0 z-modal overflow-y-auto" wire:keydown.escape="$set('showModal', false)">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-3xl overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-mono-100">
                        <h3 class="text-lg font-bold text-mono-900">{{ $editingId ? 'Editar Conta Bet' : 'Nova Conta Bet' }}</h3>
                        <button wire:click="$set('showModal', false)" class="p-1 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                            <span class="material-icons-outlined text-[20px]">close</span>
                        </button>
                    </div>

                    <form wire:submit="save">
                        <div class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-mono-600 mb-1.5">Casa de apostas</label>
                                    <select wire:model="betting_house_id" class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                        <option value="">Selecione...</option>
                                        @foreach($houses as $house)
                                            <option value="{{ $house->id }}">{{ $house->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('betting_house_id') <p class="text-xs font-medium text-error mt-1.5 pl-4">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-mono-600 mb-1.5">Usuario</label>
                                    <select wire:model="bet_user_id" class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                        <option value="">Selecione...</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('bet_user_id') <p class="text-xs font-medium text-error mt-1.5 pl-4">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <x-jr.input label="Nome amigavel" wire:model="name" icon="account_balance_wallet" :error="$errors->first('name')" />
                                <x-jr.input label="Login / username" wire:model="username" icon="alternate_email" :error="$errors->first('username')" />
                                <x-jr.input label="Codigo interno" wire:model="account_code" icon="tag" :error="$errors->first('account_code')" />
                                <x-jr.input label="Data de abertura" wire:model="opened_at" type="date" icon="event" :error="$errors->first('opened_at')" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-mono-600 mb-1.5">Status</label>
                                    <select wire:model="status" class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                        @foreach($statuses as $item)
                                            <option value="{{ $item->value }}">{{ $item->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-mono-600 mb-1.5">Verificacao</label>
                                    <select wire:model="verification_status" class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                        <option value="">Nao informado</option>
                                        @foreach($verificationStatuses as $item)
                                            <option value="{{ $item->value }}">{{ $item->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <x-jr.input label="Saldo inicial" wire:model="initial_balance" type="number" step="0.01" icon="attach_money" :error="$errors->first('initial_balance')" />
                                <x-jr.input label="Saldo de bonus" wire:model="bonus_balance" type="number" step="0.01" icon="redeem" :error="$errors->first('bonus_balance')" />
                                <x-jr.input label="Saldo sacavel" wire:model="withdrawable_balance" type="number" step="0.01" icon="payments" :error="$errors->first('withdrawable_balance')" />
                                <x-jr.input label="Limite diario de deposito" wire:model="daily_deposit_limit" type="number" step="0.01" icon="today" :error="$errors->first('daily_deposit_limit')" />
                                <x-jr.input label="Limite mensal de deposito" wire:model="monthly_deposit_limit" type="number" step="0.01" icon="calendar_month" :error="$errors->first('monthly_deposit_limit')" />
                            </div>

                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="is_active" class="rounded border-mono-200 text-primary-500 focus:ring-primary-500">
                                <span class="text-sm font-medium text-mono-900">Conta ativa no sistema</span>
                            </label>

                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Observacoes</label>
                                <textarea wire:model="notes" rows="3" class="w-full bg-mono-white border border-mono-200 rounded-xl px-4 py-3 text-sm text-mono-900 focus:border-primary-500 focus:ring-0 resize-none"></textarea>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-mono-100 bg-mono-50">
                            <x-jr.button variant="mono" type="button" wire:click="$set('showModal', false)">Cancelar</x-jr.button>
                            <x-jr.button type="submit">{{ $editingId ? 'Salvar' : 'Criar Conta' }}</x-jr.button>
                        </div>
                    </form>
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
                        <h3 class="text-lg font-bold text-mono-900">Excluir conta?</h3>
                        <p class="text-sm text-mono-600 mt-2">Contas com transacoes vinculadas nao podem ser excluidas.</p>
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
