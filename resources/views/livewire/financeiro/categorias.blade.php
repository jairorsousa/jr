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

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-sm text-mono-600">Gerencie suas categorias de receitas e despesas</p>
        </div>
        <x-jr.button wire:click="openCreateModal">
            <span class="material-icons-outlined text-[18px]">add</span>
            Nova Categoria
        </x-jr.button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Expense Categories -->
        <div>
            <h3 class="text-sm font-semibold text-mono-900 mb-3 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-down"></span>
                Despesas ({{ $expenseCategories->count() }})
            </h3>
            <x-jr.card :padding="false">
                @forelse($expenseCategories as $category)
                    <div class="flex items-center justify-between px-5 py-3.5 {{ !$loop->last ? 'border-b border-mono-100' : '' }} hover:bg-mono-50/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background-color: {{ $category->color }}20">
                                <span class="material-icons-outlined text-[18px]" style="color: {{ $category->color }}">
                                    {{ $category->icon ?? 'label' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-mono-900">{{ $category->name }}</span>
                                <span class="text-xs text-mono-300 ml-2">{{ $category->transactions_count }} transacoes</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            <button wire:click="openEditModal('{{ $category->id }}')"
                                    class="p-1.5 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-100 transition-colors">
                                <span class="material-icons-outlined text-[16px]">edit</span>
                            </button>
                            <button wire:click="confirmDelete('{{ $category->id }}')"
                                    class="p-1.5 rounded-lg text-mono-300 hover:text-error hover:bg-down-bg transition-colors">
                                <span class="material-icons-outlined text-[16px]">delete</span>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center">
                        <span class="material-icons-outlined text-[36px] text-mono-200">label_off</span>
                        <p class="text-sm text-mono-600 mt-2">Nenhuma categoria de despesa.</p>
                    </div>
                @endforelse
            </x-jr.card>
        </div>

        <!-- Income Categories -->
        <div>
            <h3 class="text-sm font-semibold text-mono-900 mb-3 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-up"></span>
                Receitas ({{ $incomeCategories->count() }})
            </h3>
            <x-jr.card :padding="false">
                @forelse($incomeCategories as $category)
                    <div class="flex items-center justify-between px-5 py-3.5 {{ !$loop->last ? 'border-b border-mono-100' : '' }} hover:bg-mono-50/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background-color: {{ $category->color }}20">
                                <span class="material-icons-outlined text-[18px]" style="color: {{ $category->color }}">
                                    {{ $category->icon ?? 'label' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-mono-900">{{ $category->name }}</span>
                                <span class="text-xs text-mono-300 ml-2">{{ $category->transactions_count }} transacoes</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            <button wire:click="openEditModal('{{ $category->id }}')"
                                    class="p-1.5 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-100 transition-colors">
                                <span class="material-icons-outlined text-[16px]">edit</span>
                            </button>
                            <button wire:click="confirmDelete('{{ $category->id }}')"
                                    class="p-1.5 rounded-lg text-mono-300 hover:text-error hover:bg-down-bg transition-colors">
                                <span class="material-icons-outlined text-[16px]">delete</span>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center">
                        <span class="material-icons-outlined text-[36px] text-mono-200">label_off</span>
                        <p class="text-sm text-mono-600 mt-2">Nenhuma categoria de receita.</p>
                    </div>
                @endforelse
            </x-jr.card>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-modal overflow-y-auto" wire:keydown.escape="$set('showModal', false)">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-lg overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-mono-100">
                        <h3 class="text-lg font-bold text-mono-900">
                            {{ $editingId ? 'Editar Categoria' : 'Nova Categoria' }}
                        </h3>
                        <button wire:click="$set('showModal', false)" class="p-1 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                            <span class="material-icons-outlined text-[20px]">close</span>
                        </button>
                    </div>

                    <form wire:submit="save">
                        <div class="px-6 py-5 space-y-4">
                            <x-jr.input label="Nome" wire:model="name" placeholder="Nome da categoria"
                                        icon="label" :error="$errors->first('name')" />

                            <!-- Type -->
                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Tipo</label>
                                <div class="flex gap-3">
                                    <button type="button" wire:click="$set('type', 'expense')"
                                            class="flex-1 flex items-center justify-center gap-2 h-11 rounded-pill text-sm font-semibold transition-colors
                                                   {{ $type === 'expense' ? 'bg-down-bg text-down border border-down/20' : 'bg-mono-50 text-mono-600 border border-mono-200 hover:bg-mono-100' }}">
                                        <span class="material-icons-outlined text-[18px]">arrow_downward</span>
                                        Despesa
                                    </button>
                                    <button type="button" wire:click="$set('type', 'income')"
                                            class="flex-1 flex items-center justify-center gap-2 h-11 rounded-pill text-sm font-semibold transition-colors
                                                   {{ $type === 'income' ? 'bg-up-bg text-up border border-up/20' : 'bg-mono-50 text-mono-600 border border-mono-200 hover:bg-mono-100' }}">
                                        <span class="material-icons-outlined text-[18px]">arrow_upward</span>
                                        Receita
                                    </button>
                                </div>
                            </div>

                            <!-- Parent Category -->
                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Subcategoria de</label>
                                <select wire:model="parent_id"
                                        class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0 transition-colors">
                                    <option value="">Nenhuma (categoria principal)</option>
                                    @foreach($parentCategories as $parent)
                                        <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Color -->
                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Cor</label>
                                <div class="flex items-center gap-3">
                                    @php
                                        $colors = ['#ff6f00', '#5C6BC0', '#EF5350', '#42A5F5', '#66BB6A', '#AB47BC', '#26C6DA', '#FFA726', '#EC407A', '#8D6E63', '#78909C'];
                                    @endphp
                                    @foreach($colors as $c)
                                        <button type="button" wire:click="$set('color', '{{ $c }}')"
                                                class="w-7 h-7 rounded-full transition-transform {{ $color === $c ? 'ring-2 ring-offset-2 ring-mono-900 scale-110' : 'hover:scale-110' }}"
                                                style="background-color: {{ $c }}">
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Icon -->
                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Icone</label>
                                <div class="flex items-center gap-2 flex-wrap">
                                    @php
                                        $icons = ['label', 'home', 'restaurant', 'directions_car', 'local_hospital', 'school', 'sports_esports', 'subscriptions', 'checkroom', 'pets', 'receipt_long', 'payments', 'work', 'trending_up', 'currency_exchange', 'shopping_cart', 'local_gas_station', 'flight'];
                                    @endphp
                                    @foreach($icons as $i)
                                        <button type="button" wire:click="$set('icon', '{{ $i }}')"
                                                class="w-9 h-9 rounded-xl flex items-center justify-center transition-colors
                                                       {{ $icon === $i ? 'bg-primary-100 text-primary-500' : 'bg-mono-50 text-mono-600 hover:bg-mono-100' }}">
                                            <span class="material-icons-outlined text-[18px]">{{ $i }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-mono-100 bg-mono-50">
                            <x-jr.button variant="mono" wire:click="$set('showModal', false)" type="button">Cancelar</x-jr.button>
                            <x-jr.button type="submit" wire:loading.attr="disabled">
                                <span wire:loading wire:target="save" class="material-icons-outlined text-[18px] animate-spin">autorenew</span>
                                {{ $editingId ? 'Salvar' : 'Criar Categoria' }}
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
                        <h3 class="text-lg font-bold text-mono-900">Excluir categoria?</h3>
                        <p class="text-sm text-mono-600 mt-2">Esta acao nao pode ser desfeita.</p>
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
