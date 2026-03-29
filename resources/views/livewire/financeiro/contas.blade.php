<div>
    <!-- Flash Messages -->
    @if (session('success'))
        <div class="mb-4">
            <x-jr.alert variant="success">{{ session('success') }}</x-jr.alert>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4">
            <x-jr.alert variant="error">{{ session('error') }}</x-jr.alert>
        </div>
    @endif

    <!-- Total Balance Card -->
    <x-jr.card class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-mono-600 font-medium">Saldo Total</p>
                <p class="text-3xl font-bold text-mono-900 mt-1">
                    R$ {{ number_format($totalBalance, 2, ',', '.') }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <x-jr.button variant="mono" href="{{ route('financeiro.importar-ofx') }}" size="sm">
                    <span class="material-icons-outlined text-[18px]">upload_file</span>
                    Importar OFX
                </x-jr.button>
                <livewire:financeiro.transferencia />
                <x-jr.button wire:click="openCreateModal">
                    <span class="material-icons-outlined text-[18px]">add</span>
                    Nova Conta
                </x-jr.button>
            </div>
        </div>
    </x-jr.card>

    <!-- Accounts Grid -->
    @if($accounts->isEmpty())
        <x-jr.card>
            <div class="text-center py-8">
                <span class="material-icons-outlined text-[48px] text-mono-200">account_balance</span>
                <p class="text-mono-600 mt-2">Nenhuma conta cadastrada.</p>
                <div class="mt-4">
                    <x-jr.button wire:click="openCreateModal" size="sm">
                        Criar primeira conta
                    </x-jr.button>
                </div>
            </div>
        </x-jr.card>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($accounts as $account)
                <x-jr.card class="relative {{ !$account->is_active ? 'opacity-50' : '' }}">
                    <!-- Color indicator -->
                    <div class="absolute top-0 left-0 w-full h-1 rounded-t-2xl" style="background-color: {{ $account->color }}"></div>

                    <div class="flex items-start justify-between pt-2">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background-color: {{ $account->color }}20">
                                <span class="material-icons-outlined text-[22px]" style="color: {{ $account->color }}">
                                    {{ $account->icon ?? 'account_balance' }}
                                </span>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-mono-900">{{ $account->name }}</h3>
                                <div class="flex items-center gap-2">
                                    @if($account->bank)
                                        <span class="text-xs text-mono-600">{{ $account->bank }}</span>
                                        <span class="text-mono-200">·</span>
                                    @endif
                                    <x-jr.badge variant="neutral" size="sm">{{ $account->type->label() }}</x-jr.badge>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-1" x-data="{ open: false }">
                            <button @click="open = !open" class="p-1.5 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                                <span class="material-icons-outlined text-[18px]">more_vert</span>
                            </button>
                            <div x-show="open" x-cloak @click.away="open = false"
                                 class="absolute right-6 top-12 bg-mono-white rounded-xl shadow-dropdown border border-mono-100 py-1.5 z-50 w-40">
                                <button wire:click="openEditModal('{{ $account->id }}')" @click="open = false"
                                        class="flex items-center gap-2 w-full px-3 py-2 text-sm text-mono-900 hover:bg-mono-50">
                                    <span class="material-icons-outlined text-[16px] text-mono-300">edit</span>
                                    Editar
                                </button>
                                <button wire:click="toggleActive('{{ $account->id }}')" @click="open = false"
                                        class="flex items-center gap-2 w-full px-3 py-2 text-sm text-mono-900 hover:bg-mono-50">
                                    <span class="material-icons-outlined text-[16px] text-mono-300">
                                        {{ $account->is_active ? 'visibility_off' : 'visibility' }}
                                    </span>
                                    {{ $account->is_active ? 'Desativar' : 'Ativar' }}
                                </button>
                                <div class="border-t border-mono-100 my-1"></div>
                                <button wire:click="confirmDelete('{{ $account->id }}')" @click="open = false"
                                        class="flex items-center gap-2 w-full px-3 py-2 text-sm text-error hover:bg-down-bg">
                                    <span class="material-icons-outlined text-[16px]">delete</span>
                                    Excluir
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Balance -->
                    <div class="mt-4 pt-3 border-t border-mono-100">
                        <p class="text-xs text-mono-600">Saldo atual</p>
                        <p class="text-xl font-bold {{ $account->current_balance >= 0 ? 'text-mono-900' : 'text-down' }} mt-0.5">
                            R$ {{ number_format($account->current_balance, 2, ',', '.') }}
                        </p>
                    </div>
                </x-jr.card>
            @endforeach
        </div>
    @endif

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-modal overflow-y-auto" wire:keydown.escape="$set('showModal', false)">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-lg overflow-hidden">
                    <!-- Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-mono-100">
                        <h3 class="text-lg font-bold text-mono-900">
                            {{ $editingId ? 'Editar Conta' : 'Nova Conta' }}
                        </h3>
                        <button wire:click="$set('showModal', false)" class="p-1 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                            <span class="material-icons-outlined text-[20px]">close</span>
                        </button>
                    </div>

                    <!-- Form -->
                    <form wire:submit="save">
                        <div class="px-6 py-5 space-y-4">
                            <x-jr.input label="Nome da conta" wire:model="name" placeholder="Ex: Nubank, Inter, Carteira"
                                        icon="account_balance" :error="$errors->first('name')" />

                            <!-- Type Select -->
                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Tipo</label>
                                <select wire:model="type"
                                        class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0 transition-colors">
                                    @foreach($accountTypes as $accountType)
                                        <option value="{{ $accountType->value }}">{{ $accountType->label() }}</option>
                                    @endforeach
                                </select>
                                @error('type') <p class="text-xs font-medium text-error mt-1.5 pl-4">{{ $message }}</p> @enderror
                            </div>

                            <x-jr.input label="Banco / Instituicao" wire:model="bank" placeholder="Ex: Nubank, Banco Inter"
                                        icon="business" :error="$errors->first('bank')" />

                            <x-jr.input label="Saldo inicial" wire:model="initial_balance" type="number" step="0.01"
                                        icon="attach_money" :error="$errors->first('initial_balance')" />

                            <!-- Color Picker -->
                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Cor</label>
                                <div class="flex items-center gap-3">
                                    @php
                                        $colors = ['#ff6f00', '#5C6BC0', '#EF5350', '#42A5F5', '#66BB6A', '#AB47BC', '#26C6DA', '#FFA726', '#EC407A', '#8D6E63'];
                                    @endphp
                                    @foreach($colors as $c)
                                        <button type="button" wire:click="$set('color', '{{ $c }}')"
                                                class="w-8 h-8 rounded-full transition-transform {{ $color === $c ? 'ring-2 ring-offset-2 ring-mono-900 scale-110' : 'hover:scale-110' }}"
                                                style="background-color: {{ $c }}">
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Icon Selector -->
                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Icone</label>
                                <div class="flex items-center gap-2 flex-wrap">
                                    @php
                                        $icons = ['account_balance', 'savings', 'wallet', 'payments', 'credit_card', 'account_balance_wallet', 'monetization_on', 'currency_bitcoin'];
                                    @endphp
                                    @foreach($icons as $i)
                                        <button type="button" wire:click="$set('icon', '{{ $i }}')"
                                                class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors
                                                       {{ $icon === $i ? 'bg-primary-100 text-primary-500' : 'bg-mono-50 text-mono-600 hover:bg-mono-100' }}">
                                            <span class="material-icons-outlined text-[20px]">{{ $i }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-mono-100 bg-mono-50">
                            <x-jr.button variant="mono" wire:click="$set('showModal', false)" type="button">
                                Cancelar
                            </x-jr.button>
                            <x-jr.button type="submit" wire:loading.attr="disabled">
                                <span wire:loading wire:target="save" class="material-icons-outlined text-[18px] animate-spin">autorenew</span>
                                {{ $editingId ? 'Salvar' : 'Criar Conta' }}
                            </x-jr.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
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
                        <p class="text-sm text-mono-600 mt-2">Esta acao nao pode ser desfeita. Todas as informacoes desta conta serao perdidas.</p>
                    </div>
                    <div class="flex items-center justify-center gap-3 px-6 py-4 border-t border-mono-100 bg-mono-50">
                        <x-jr.button variant="mono" wire:click="$set('showDeleteModal', false)">
                            Cancelar
                        </x-jr.button>
                        <x-jr.button variant="danger" wire:click="delete">
                            Excluir
                        </x-jr.button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
