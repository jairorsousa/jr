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
                <p class="text-sm text-mono-600 font-medium">Casas de apostas</p>
                <p class="text-3xl font-bold text-mono-900 mt-1">{{ $houses->count() }}</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
                <x-jr.input wire:model.live.debounce.300ms="search" placeholder="Buscar casa..." icon="search" />
                <x-jr.button wire:click="openCreateModal">
                    <span class="material-icons-outlined text-[18px]">add_business</span>
                    Nova Casa
                </x-jr.button>
            </div>
        </div>
    </x-jr.card>

    @if($houses->isEmpty())
        <x-jr.card>
            <div class="text-center py-8">
                <span class="material-icons-outlined text-[48px] text-mono-200">storefront</span>
                <p class="text-mono-600 mt-2">Nenhuma casa de apostas cadastrada.</p>
                <div class="mt-4"><x-jr.button wire:click="openCreateModal" size="sm">Criar primeira casa</x-jr.button></div>
            </div>
        </x-jr.card>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($houses as $house)
                <x-jr.card class="relative {{ !$house->is_active ? 'opacity-50' : '' }}">
                    <div class="absolute top-0 left-0 w-full h-1 rounded-t-2xl" style="background-color: {{ $house->color }}"></div>
                    <div class="flex items-start justify-between pt-2">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0" style="background-color: {{ $house->color }}20">
                                <span class="material-icons-outlined text-[24px]" style="color: {{ $house->color }}">storefront</span>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-semibold text-mono-900 truncate">{{ $house->name }}</h3>
                                <p class="text-xs text-mono-600 truncate">{{ $house->website ?: $house->country ?: 'Sem site informado' }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-1">
                            <button wire:click="openEditModal('{{ $house->id }}')" class="p-1.5 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-100 transition-colors">
                                <span class="material-icons-outlined text-[16px]">edit</span>
                            </button>
                            <button wire:click="toggleActive('{{ $house->id }}')" class="p-1.5 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-100 transition-colors">
                                <span class="material-icons-outlined text-[16px]">{{ $house->is_active ? 'visibility_off' : 'visibility' }}</span>
                            </button>
                            <button wire:click="confirmDelete('{{ $house->id }}')" class="p-1.5 rounded-lg text-mono-300 hover:text-error hover:bg-down-bg transition-colors">
                                <span class="material-icons-outlined text-[16px]">delete</span>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mt-5 pt-4 border-t border-mono-100">
                        <div>
                            <p class="text-xs text-mono-600">Contas</p>
                            <p class="text-lg font-bold text-mono-900">{{ $house->accounts_count }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-mono-600">Saldo</p>
                            <p class="text-lg font-bold text-mono-900">R$ {{ number_format($house->accounts_sum_current_balance ?? 0, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </x-jr.card>
            @endforeach
        </div>
    @endif

    @if($showModal)
        <div class="fixed inset-0 z-modal overflow-y-auto" wire:keydown.escape="$set('showModal', false)">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-2xl overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-mono-100">
                        <h3 class="text-lg font-bold text-mono-900">{{ $editingId ? 'Editar Casa' : 'Nova Casa' }}</h3>
                        <button wire:click="$set('showModal', false)" class="p-1 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                            <span class="material-icons-outlined text-[20px]">close</span>
                        </button>
                    </div>

                    <form wire:submit="save">
                        <div class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <x-jr.input label="Nome" wire:model="name" placeholder="Ex: Betano" icon="storefront" :error="$errors->first('name')" />
                                <x-jr.input label="Slug" wire:model="slug" placeholder="betano" icon="link" :error="$errors->first('slug')" />
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <x-jr.input label="Site" wire:model="website" placeholder="https://..." icon="language" :error="$errors->first('website')" />
                                <x-jr.input label="Pais" wire:model="country" placeholder="Brasil" icon="public" :error="$errors->first('country')" />
                            </div>
                            <x-jr.input label="Logo URL" wire:model="logo_url" placeholder="https://..." icon="image" :error="$errors->first('logo_url')" />

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <x-jr.input label="Deposito minimo" wire:model="min_deposit" type="number" step="0.01" icon="south_west" :error="$errors->first('min_deposit')" />
                                <x-jr.input label="Saque minimo" wire:model="min_withdrawal" type="number" step="0.01" icon="north_east" :error="$errors->first('min_withdrawal')" />
                                <x-jr.input label="Taxa deposito (%)" wire:model="deposit_fee_percent" type="number" step="0.01" icon="percent" :error="$errors->first('deposit_fee_percent')" />
                                <x-jr.input label="Taxa saque (%)" wire:model="withdrawal_fee_percent" type="number" step="0.01" icon="percent" :error="$errors->first('withdrawal_fee_percent')" />
                            </div>
                            <x-jr.input label="Tempo medio de saque (horas)" wire:model="withdrawal_time_hours" type="number" icon="schedule" :error="$errors->first('withdrawal_time_hours')" />

                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Cor</label>
                                <div class="flex flex-wrap items-center gap-3">
                                    @foreach(['#ff6f00', '#15a96f', '#1a73e8', '#5C6BC0', '#EF5350', '#AB47BC', '#26C6DA', '#FFA726', '#EC407A', '#78909C'] as $c)
                                        <button type="button" wire:click="$set('color', '{{ $c }}')" class="w-8 h-8 rounded-full transition-transform {{ $color === $c ? 'ring-2 ring-offset-2 ring-mono-900 scale-110' : 'hover:scale-110' }}" style="background-color: {{ $c }}"></button>
                                    @endforeach
                                </div>
                            </div>

                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="is_active" class="rounded border-mono-200 text-primary-500 focus:ring-primary-500">
                                <span class="text-sm font-medium text-mono-900">Casa ativa</span>
                            </label>

                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Observacoes</label>
                                <textarea wire:model="notes" rows="3" class="w-full bg-mono-white border border-mono-200 rounded-xl px-4 py-3 text-sm text-mono-900 focus:border-primary-500 focus:ring-0 resize-none"></textarea>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-mono-100 bg-mono-50">
                            <x-jr.button variant="mono" type="button" wire:click="$set('showModal', false)">Cancelar</x-jr.button>
                            <x-jr.button type="submit">{{ $editingId ? 'Salvar' : 'Criar Casa' }}</x-jr.button>
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
                        <h3 class="text-lg font-bold text-mono-900">Excluir casa?</h3>
                        <p class="text-sm text-mono-600 mt-2">Casas com contas vinculadas nao podem ser excluidas.</p>
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
