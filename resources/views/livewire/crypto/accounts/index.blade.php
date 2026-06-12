<div>
    @if (session('success'))
        <div class="mb-4"><x-jr.alert variant="success">{{ session('success') }}</x-jr.alert></div>
    @endif
    @if (session('error'))
        <div class="mb-4"><x-jr.alert variant="error">{{ session('error') }}</x-jr.alert></div>
    @endif

    <x-jr.card class="mb-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm text-mono-600 font-medium">Saldo em contas cripto</p>
                <p class="text-3xl font-bold text-mono-900 mt-1">R$ {{ number_format($totalBalance, 2, ',', '.') }}</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
                <x-jr.button variant="mono" href="{{ route('crypto.transactions') }}" size="sm">
                    <span class="material-icons-outlined text-[16px]">swap_vert</span>
                    Transacoes
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
                <x-jr.input wire:model.live.debounce.300ms="search" placeholder="Buscar conta ou identificador..." icon="search" />
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <select wire:model.live="filterInstitution" class="bg-mono-white border border-mono-200 rounded-pill px-3 h-10 text-xs font-medium text-mono-900 focus:border-primary-500 focus:ring-0">
                    <option value="">Instituicao</option>
                    @foreach($institutions as $institution)
                        <option value="{{ $institution->id }}">{{ $institution->name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="filterUser" class="bg-mono-white border border-mono-200 rounded-pill px-3 h-10 text-xs font-medium text-mono-900 focus:border-primary-500 focus:ring-0">
                    <option value="">Usuario bet</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="filterCustody" class="bg-mono-white border border-mono-200 rounded-pill px-3 h-10 text-xs font-medium text-mono-900 focus:border-primary-500 focus:ring-0">
                    <option value="">Custodia</option>
                    @foreach($custodyTypes as $type)
                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </x-jr.card>

    @if($accounts->isEmpty())
        <x-jr.card>
            <div class="text-center py-8">
                <span class="material-icons-outlined text-[48px] text-mono-200">account_balance_wallet</span>
                <p class="text-mono-600 mt-2">Nenhuma conta cripto cadastrada.</p>
                <div class="mt-4"><x-jr.button wire:click="openCreateModal" size="sm">Criar primeira conta</x-jr.button></div>
            </div>
        </x-jr.card>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($accounts as $account)
                @php $primaryAddress = $account->walletAddresses->first(); @endphp
                <x-jr.card class="{{ !$account->is_active ? 'opacity-50' : '' }}">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0" style="background-color: {{ $account->institution?->color ?? '#1a73e8' }}20">
                                <span class="material-icons-outlined text-[24px]" style="color: {{ $account->institution?->color ?? '#1a73e8' }}">account_balance_wallet</span>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-semibold text-mono-900 truncate">{{ $account->name }}</h3>
                                <p class="text-xs text-mono-600 truncate">{{ $account->institution?->name }} @if($account->betUser) · {{ $account->betUser->name }} @endif</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-1">
                            <button wire:click="markChecked('{{ $account->id }}')" class="p-1.5 rounded-lg text-mono-300 hover:text-up hover:bg-up-bg transition-colors" title="Conferido">
                                <span class="material-icons-outlined text-[16px]">fact_check</span>
                            </button>
                            <button wire:click="recalculate('{{ $account->id }}')" class="p-1.5 rounded-lg text-mono-300 hover:text-primary-500 hover:bg-primary-100 transition-colors" title="Recalcular">
                                <span class="material-icons-outlined text-[16px]">sync</span>
                            </button>
                            <button wire:click="openEditModal('{{ $account->id }}')" class="p-1.5 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-100 transition-colors" title="Editar">
                                <span class="material-icons-outlined text-[16px]">edit</span>
                            </button>
                            <button wire:click="toggleActive('{{ $account->id }}')" class="p-1.5 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-100 transition-colors" title="{{ $account->is_active ? 'Desativar' : 'Ativar' }}">
                                <span class="material-icons-outlined text-[16px]">{{ $account->is_active ? 'visibility_off' : 'visibility' }}</span>
                            </button>
                            <button wire:click="confirmDelete('{{ $account->id }}')" class="p-1.5 rounded-lg text-mono-300 hover:text-error hover:bg-down-bg transition-colors" title="Excluir">
                                <span class="material-icons-outlined text-[16px]">delete</span>
                            </button>
                        </div>
                    </div>

                    <div class="mt-5 pt-4 border-t border-mono-100">
                        <div class="flex items-end justify-between gap-4">
                            <div>
                                <p class="text-xs text-mono-600">Custodia</p>
                                <p class="text-sm font-semibold text-mono-900">{{ $account->custody_type->label() }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-mono-600">Saldo BRL</p>
                                <p class="text-lg font-bold text-mono-900">R$ {{ number_format($account->current_balance_brl, 2, ',', '.') }}</p>
                            </div>
                        </div>
                        @if($primaryAddress)
                            <p class="text-xs text-mono-600 truncate mt-3">
                                {{ $primaryAddress->asset?->symbol ?? 'Moeda' }} · {{ $primaryAddress->network?->name ?? 'Rede' }} · {{ $primaryAddress->address }}
                            </p>
                        @endif
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
                        <h3 class="text-lg font-bold text-mono-900">{{ $editingId ? 'Editar Conta Cripto' : 'Nova Conta Cripto' }}</h3>
                        <button wire:click="$set('showModal', false)" class="p-1 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                            <span class="material-icons-outlined text-[20px]">close</span>
                        </button>
                    </div>

                    <form wire:submit="save">
                        <div class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-mono-600 mb-1.5">Instituicao</label>
                                    <select wire:model="crypto_institution_id" class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                        <option value="">Selecione...</option>
                                        @foreach($institutions as $institution)
                                            <option value="{{ $institution->id }}">{{ $institution->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('crypto_institution_id') <p class="text-xs font-medium text-error mt-1.5 pl-4">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-mono-600 mb-1.5">Usuario bet</label>
                                    <select wire:model="bet_user_id" class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                        <option value="">Sem usuario vinculado</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <x-jr.input label="Nome" wire:model="name" placeholder="Ex: Binance Jairo USDT" icon="account_balance_wallet" :error="$errors->first('name')" />
                                <x-jr.input label="Identificador" wire:model="account_identifier" placeholder="Email, UID ou apelido" icon="badge" :error="$errors->first('account_identifier')" />
                                <div>
                                    <label class="block text-sm font-medium text-mono-600 mb-1.5">Custodia</label>
                                    <select wire:model="custody_type" class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                        @foreach($custodyTypes as $type)
                                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <x-jr.input label="Saldo inicial BRL" wire:model="initial_balance_brl" type="number" step="0.01" icon="attach_money" :error="$errors->first('initial_balance_brl')" />
                            </div>

                            <div class="rounded-2xl border border-mono-100 bg-mono-50 p-4">
                                <p class="text-sm font-semibold text-mono-900 mb-3">Endereco principal</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <x-jr.input label="Rotulo" wire:model="address_label" placeholder="Ex: USDT TRC20" icon="label" :error="$errors->first('address_label')" />
                                    <div>
                                        <label class="block text-sm font-medium text-mono-600 mb-1.5">Moeda</label>
                                        <select wire:model="address_asset_id" class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                            <option value="">Selecione...</option>
                                            @foreach($assets as $asset)
                                                <option value="{{ $asset->id }}">{{ $asset->symbol }} · {{ $asset->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-mono-600 mb-1.5">Rede</label>
                                        <select wire:model="address_network_id" class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                            <option value="">Selecione...</option>
                                            @foreach($networks as $network)
                                                <option value="{{ $network->id }}">{{ $network->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <x-jr.input label="Endereco" wire:model="address" icon="link" :error="$errors->first('address')" />
                                </div>
                            </div>

                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="is_active" class="rounded border-mono-200 text-primary-500 focus:ring-primary-500">
                                <span class="text-sm font-medium text-mono-900">Conta ativa</span>
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
                        <h3 class="text-lg font-bold text-mono-900">Excluir conta cripto?</h3>
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
