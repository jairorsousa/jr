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
            <p class="text-sm text-mono-600">Gerencie os produtos e servicos do seu CRM</p>
        </div>
        <x-jr.button wire:click="openCreateModal">
            <span class="material-icons-outlined text-[18px]">add</span>
            Novo Produto
        </x-jr.button>
    </div>

    <!-- Products Grid -->
    @if($products->isEmpty())
        <x-jr.card>
            <div class="text-center py-8">
                <span class="material-icons-outlined text-[48px] text-mono-200">inventory_2</span>
                <p class="text-mono-600 mt-2">Nenhum produto cadastrado.</p>
                <div class="mt-4">
                    <x-jr.button wire:click="openCreateModal" size="sm">
                        Criar primeiro produto
                    </x-jr.button>
                </div>
            </div>
        </x-jr.card>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($products as $product)
                <x-jr.card class="relative {{ !$product->is_active ? 'opacity-50' : '' }}">
                    <!-- Color indicator -->
                    <div class="absolute top-0 left-0 w-full h-1 rounded-t-2xl" style="background-color: {{ $product->color }}"></div>

                    <div class="flex items-start justify-between pt-2">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background-color: {{ $product->color }}20">
                                <span class="material-icons-outlined text-[22px]" style="color: {{ $product->color }}">
                                    inventory_2
                                </span>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-mono-900">{{ $product->name }}</h3>
                                @if($product->description)
                                    <p class="text-xs text-mono-600 truncate max-w-[180px]">{{ $product->description }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-1" x-data="{ open: false }">
                            <button @click="open = !open" class="p-1.5 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                                <span class="material-icons-outlined text-[18px]">more_vert</span>
                            </button>
                            <div x-show="open" x-cloak @click.away="open = false"
                                 class="absolute right-6 top-12 bg-mono-white rounded-xl shadow-dropdown border border-mono-100 py-1.5 z-50 w-40">
                                <button wire:click="openEditModal('{{ $product->id }}')" @click="open = false"
                                        class="flex items-center gap-2 w-full px-3 py-2 text-sm text-mono-900 hover:bg-mono-50">
                                    <span class="material-icons-outlined text-[16px] text-mono-300">edit</span>
                                    Editar
                                </button>
                                <button wire:click="toggleActive('{{ $product->id }}')" @click="open = false"
                                        class="flex items-center gap-2 w-full px-3 py-2 text-sm text-mono-900 hover:bg-mono-50">
                                    <span class="material-icons-outlined text-[16px] text-mono-300">
                                        {{ $product->is_active ? 'visibility_off' : 'visibility' }}
                                    </span>
                                    {{ $product->is_active ? 'Desativar' : 'Ativar' }}
                                </button>
                                <div class="border-t border-mono-100 my-1"></div>
                                <button wire:click="confirmDelete('{{ $product->id }}')" @click="open = false"
                                        class="flex items-center gap-2 w-full px-3 py-2 text-sm text-error hover:bg-down-bg">
                                    <span class="material-icons-outlined text-[16px]">delete</span>
                                    Excluir
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Price & Deals -->
                    <div class="mt-4 pt-3 border-t border-mono-100 flex items-center justify-between">
                        <div>
                            <p class="text-xs text-mono-600">Preco</p>
                            <p class="text-lg font-bold text-mono-900 mt-0.5">
                                @if($product->price)
                                    R$ {{ number_format($product->price, 2, ',', '.') }}
                                @else
                                    <span class="text-mono-300">--</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <x-jr.badge variant="neutral" size="sm">
                                {{ $product->deals_count }} {{ $product->deals_count === 1 ? 'negocio' : 'negocios' }}
                            </x-jr.badge>
                        </div>
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
                            {{ $editingId ? 'Editar Produto' : 'Novo Produto' }}
                        </h3>
                        <button wire:click="$set('showModal', false)" class="p-1 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                            <span class="material-icons-outlined text-[20px]">close</span>
                        </button>
                    </div>

                    <!-- Form -->
                    <form wire:submit="save">
                        <div class="px-6 py-5 space-y-4">
                            <x-jr.input label="Nome do produto" wire:model="name" placeholder="Ex: Consultoria, Plano Premium"
                                        icon="inventory_2" :error="$errors->first('name')" />

                            <x-jr.input label="Descricao" wire:model="description" placeholder="Descricao breve do produto"
                                        icon="notes" :error="$errors->first('description')" />

                            <x-jr.input label="Preco" wire:model="price" type="number" placeholder="0,00"
                                        icon="attach_money" :error="$errors->first('price')" />

                            <!-- Active Toggle -->
                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Status</label>
                                <button type="button" wire:click="$toggle('is_active')"
                                        class="flex items-center gap-3 w-full h-12 px-4 rounded-pill border transition-colors
                                               {{ $is_active ? 'border-primary-500 bg-primary-50' : 'border-mono-200 bg-mono-50' }}">
                                    <div class="relative w-10 h-6 rounded-full transition-colors {{ $is_active ? 'bg-primary-500' : 'bg-mono-300' }}">
                                        <div class="absolute top-1 {{ $is_active ? 'left-5' : 'left-1' }} w-4 h-4 rounded-full bg-mono-white shadow transition-all"></div>
                                    </div>
                                    <span class="text-sm font-medium {{ $is_active ? 'text-primary-500' : 'text-mono-600' }}">
                                        {{ $is_active ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </button>
                            </div>

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
                        </div>

                        <!-- Footer -->
                        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-mono-100 bg-mono-50">
                            <x-jr.button variant="mono" wire:click="$set('showModal', false)" type="button">
                                Cancelar
                            </x-jr.button>
                            <x-jr.button type="submit" wire:loading.attr="disabled">
                                <span wire:loading wire:target="save" class="material-icons-outlined text-[18px] animate-spin">autorenew</span>
                                {{ $editingId ? 'Salvar' : 'Criar Produto' }}
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
                        <h3 class="text-lg font-bold text-mono-900">Excluir produto?</h3>
                        <p class="text-sm text-mono-600 mt-2">Esta acao nao pode ser desfeita. Todas as informacoes deste produto serao perdidas.</p>
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
