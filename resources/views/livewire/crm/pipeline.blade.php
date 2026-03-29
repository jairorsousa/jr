<div>
    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-4"><x-jr.alert variant="success">{{ session('success') }}</x-jr.alert></div>
    @endif

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <x-jr.card>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-info-bg flex items-center justify-center">
                    <span class="material-icons-outlined text-[22px] text-info">view_kanban</span>
                </div>
                <div>
                    <p class="text-xs text-mono-600">Negocios abertos</p>
                    <p class="text-lg font-bold text-mono-900">{{ $totalDeals }}</p>
                </div>
            </div>
        </x-jr.card>
        <x-jr.card>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary-100 flex items-center justify-center">
                    <span class="material-icons-outlined text-[22px] text-primary-500">payments</span>
                </div>
                <div>
                    <p class="text-xs text-mono-600">Valor no pipeline</p>
                    <p class="text-lg font-bold text-mono-900">R$ {{ number_format($totalPipelineValue, 2, ',', '.') }}</p>
                </div>
            </div>
        </x-jr.card>
        <x-jr.card>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-success-bg flex items-center justify-center">
                    <span class="material-icons-outlined text-[22px] text-success">emoji_events</span>
                </div>
                <div>
                    <p class="text-xs text-mono-600">Ganhos este mes</p>
                    <p class="text-lg font-bold text-success">R$ {{ number_format($wonThisMonth, 2, ',', '.') }}</p>
                </div>
            </div>
        </x-jr.card>
    </div>

    {{-- Filters + Actions --}}
    <div class="flex flex-wrap items-center gap-3 mb-6">
        <div class="flex-1 min-w-[200px]">
            <x-jr.input wire:model.live.debounce.300ms="filterSearch" placeholder="Buscar negocio ou contato..." icon="search" />
        </div>
        <select wire:model.live="filterProduct"
                class="bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
            <option value="">Todos os produtos</option>
            @foreach($products as $product)
                <option value="{{ $product->id }}">{{ $product->name }}</option>
            @endforeach
        </select>
        <x-jr.button href="{{ route('crm.produtos') }}" variant="mono" size="sm">
            <span class="material-icons-outlined text-[16px]">inventory_2</span>
            Produtos
        </x-jr.button>
        <x-jr.button wire:click="openCreateDeal">
            <span class="material-icons-outlined text-[18px]">add</span>
            Novo Negocio
        </x-jr.button>
    </div>

    {{-- Kanban Board --}}
    <div class="overflow-x-auto -mx-6 px-6 pb-4"
         x-data="{
             initSortable() {
                 document.querySelectorAll('.kanban-column').forEach(col => {
                     if (col._sortable) return;
                     col._sortable = new Sortable(col, {
                         group: 'deals',
                         animation: 150,
                         ghostClass: 'opacity-30',
                         dragClass: 'rotate-2',
                         handle: '.deal-card',
                         onEnd: (evt) => {
                             const dealId = evt.item.dataset.dealId;
                             const newStage = evt.to.dataset.stage;
                             const newIndex = evt.newIndex;
                             $wire.moveDeal(dealId, newStage, newIndex);
                         }
                     });
                 });
             }
         }"
         x-init="$nextTick(() => initSortable())"
         wire:ignore.self
    >
        <div class="flex gap-4" style="min-width: {{ count($stages) * 300 }}px">
            @foreach($stages as $stage)
                @php
                    $stageDeals = $dealsByStage[$stage->value] ?? collect();
                    $stageTotal = $stageDeals->sum('value');
                @endphp
                <div class="flex-1 min-w-[280px]">
                    {{-- Column Header --}}
                    <div class="flex items-center justify-between mb-3 px-1">
                        <div class="flex items-center gap-2">
                            <span class="material-icons-outlined text-[18px] text-mono-600">{{ $stage->icon() }}</span>
                            <h3 class="text-sm font-bold text-mono-900">{{ $stage->label() }}</h3>
                            <x-jr.badge variant="{{ $stage->color() }}" size="sm">{{ $stageDeals->count() }}</x-jr.badge>
                        </div>
                        <button wire:click="openCreateDeal('{{ $stage->value }}')"
                                class="p-1 rounded-lg text-mono-300 hover:text-primary-500 hover:bg-primary-100 transition-colors">
                            <span class="material-icons-outlined text-[18px]">add</span>
                        </button>
                    </div>

                    <p class="text-xs text-mono-600 mb-2 px-1">R$ {{ number_format($stageTotal, 2, ',', '.') }}</p>

                    {{-- Column Body (droppable) --}}
                    <div class="kanban-column space-y-3 min-h-[200px] p-2 rounded-2xl bg-mono-50 border border-mono-100"
                         data-stage="{{ $stage->value }}">

                        @foreach($stageDeals as $deal)
                            <div class="deal-card cursor-grab active:cursor-grabbing" data-deal-id="{{ $deal->id }}">
                                <x-jr.card class="!p-4 hover:shadow-dropdown transition-shadow">
                                    {{-- Product color dot --}}
                                    <div class="flex items-start justify-between mb-2">
                                        <div class="flex items-center gap-2 min-w-0">
                                            @if($deal->product)
                                                <div class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $deal->product->color }}"></div>
                                            @endif
                                            <a href="{{ route('crm.negocio', $deal->id) }}"
                                               class="text-sm font-semibold text-mono-900 truncate hover:text-primary-500 transition-colors">
                                                {{ $deal->title }}
                                            </a>
                                        </div>
                                        <div x-data="{ open: false }" class="relative flex-shrink-0">
                                            <button @click="open = !open" class="p-0.5 rounded text-mono-300 hover:text-mono-600">
                                                <span class="material-icons-outlined text-[16px]">more_vert</span>
                                            </button>
                                            <div x-show="open" x-cloak @click.away="open = false"
                                                 class="absolute right-0 top-6 bg-mono-white rounded-xl shadow-dropdown border border-mono-100 py-1 z-50 w-36">
                                                <button wire:click="openEditDeal('{{ $deal->id }}')" @click="open = false"
                                                        class="flex items-center gap-2 w-full px-3 py-1.5 text-xs text-mono-900 hover:bg-mono-50">
                                                    <span class="material-icons-outlined text-[14px] text-mono-300">edit</span>
                                                    Editar
                                                </button>
                                                <button wire:click="markAsWon('{{ $deal->id }}')" @click="open = false"
                                                        class="flex items-center gap-2 w-full px-3 py-1.5 text-xs text-success hover:bg-success-bg">
                                                    <span class="material-icons-outlined text-[14px]">emoji_events</span>
                                                    Ganhou
                                                </button>
                                                <button wire:click="markAsLost('{{ $deal->id }}')" @click="open = false"
                                                        class="flex items-center gap-2 w-full px-3 py-1.5 text-xs text-error hover:bg-down-bg">
                                                    <span class="material-icons-outlined text-[14px]">block</span>
                                                    Perdeu
                                                </button>
                                                <div class="border-t border-mono-100 my-1"></div>
                                                <button wire:click="confirmDeleteDeal('{{ $deal->id }}')" @click="open = false"
                                                        class="flex items-center gap-2 w-full px-3 py-1.5 text-xs text-error hover:bg-down-bg">
                                                    <span class="material-icons-outlined text-[14px]">delete</span>
                                                    Excluir
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Contact --}}
                                    <div class="flex items-center gap-1.5 mb-2">
                                        <span class="material-icons-outlined text-[14px] text-mono-300">person</span>
                                        <span class="text-xs text-mono-600 truncate">{{ $deal->contact->name }}</span>
                                    </div>

                                    {{-- Value + Product --}}
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-bold text-mono-900">R$ {{ number_format($deal->value, 2, ',', '.') }}</span>
                                        @if($deal->product)
                                            <x-jr.badge variant="neutral" size="sm">{{ $deal->product->name }}</x-jr.badge>
                                        @endif
                                    </div>

                                    {{-- Expected close --}}
                                    @if($deal->expected_close_date)
                                        <div class="flex items-center gap-1 mt-2 pt-2 border-t border-mono-100">
                                            <span class="material-icons-outlined text-[13px] {{ $deal->expected_close_date->isPast() ? 'text-error' : 'text-mono-300' }}">event</span>
                                            <span class="text-[11px] {{ $deal->expected_close_date->isPast() ? 'text-error font-medium' : 'text-mono-600' }}">
                                                {{ $deal->expected_close_date->format('d/m/Y') }}
                                            </span>
                                        </div>
                                    @endif
                                </x-jr.card>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- SortableJS CDN --}}
    @script
    <script>
        if (!document.querySelector('script[src*="sortablejs"]')) {
            let s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js';
            s.onload = () => {
                document.querySelectorAll('.kanban-column').forEach(col => {
                    if (col._sortable) return;
                    col._sortable = new Sortable(col, {
                        group: 'deals',
                        animation: 150,
                        ghostClass: 'opacity-30',
                        handle: '.deal-card',
                        onEnd: (evt) => {
                            const dealId = evt.item.dataset.dealId;
                            const newStage = evt.to.dataset.stage;
                            const newIndex = evt.newIndex;
                            $wire.moveDeal(dealId, newStage, newIndex);
                        }
                    });
                });
            };
            document.head.appendChild(s);
        }
    </script>
    @endscript

    {{-- Create/Edit Deal Modal --}}
    @if($showDealModal)
        <div class="fixed inset-0 z-modal overflow-y-auto" wire:keydown.escape="$set('showDealModal', false)">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showDealModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-lg overflow-hidden modal-content">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-mono-100">
                        <h3 class="text-lg font-bold text-mono-900">
                            {{ $editingDealId ? 'Editar Negocio' : 'Novo Negocio' }}
                        </h3>
                        <button wire:click="$set('showDealModal', false)" class="p-1 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                            <span class="material-icons-outlined text-[20px]">close</span>
                        </button>
                    </div>

                    <form wire:submit="saveDeal">
                        <div class="px-6 py-5 space-y-4">
                            <x-jr.input label="Titulo" wire:model="dealTitle" placeholder="Ex: Venda SaaS para Empresa X"
                                        icon="handshake" :error="$errors->first('dealTitle')" />

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-mono-600 mb-1.5">Contato</label>
                                    <select wire:model="dealContactId"
                                            class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                        <option value="">Selecione...</option>
                                        @foreach($contacts as $contact)
                                            <option value="{{ $contact->id }}">{{ $contact->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('dealContactId') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-mono-600 mb-1.5">Produto</label>
                                    <select wire:model="dealProductId"
                                            class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                        <option value="">Nenhum</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <x-jr.input label="Valor (R$)" wire:model="dealValue" type="number" step="0.01" min="0"
                                            icon="attach_money" :error="$errors->first('dealValue')" />
                                <div>
                                    <label class="block text-sm font-medium text-mono-600 mb-1.5">Previsao de fechamento</label>
                                    <input type="date" wire:model="dealExpectedCloseDate"
                                           class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Etapa</label>
                                <div class="flex gap-2 flex-wrap">
                                    @foreach(\App\Enums\DealStage::pipelineStages() as $stage)
                                        <button type="button" wire:click="$set('dealStage', '{{ $stage->value }}')"
                                                class="flex items-center gap-1.5 px-3 py-1.5 rounded-pill text-xs font-medium transition-colors
                                                       {{ $dealStage === $stage->value ? 'bg-primary-500 text-white' : 'bg-mono-100 text-mono-600 hover:bg-mono-200' }}">
                                            <span class="material-icons-outlined text-[14px]">{{ $stage->icon() }}</span>
                                            {{ $stage->label() }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Observacoes</label>
                                <textarea wire:model="dealNotes" rows="2" placeholder="Notas sobre o negocio..."
                                          class="w-full bg-mono-white border border-mono-200 rounded-xl px-4 py-3 text-sm text-mono-900 focus:border-primary-500 focus:ring-0"></textarea>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-mono-100 bg-mono-50">
                            <x-jr.button variant="mono" wire:click="$set('showDealModal', false)" type="button">Cancelar</x-jr.button>
                            <x-jr.button type="submit" wire:loading.attr="disabled">
                                <span wire:loading wire:target="saveDeal" class="material-icons-outlined text-[18px] animate-spin">autorenew</span>
                                {{ $editingDealId ? 'Salvar' : 'Criar Negocio' }}
                            </x-jr.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-modal overflow-y-auto">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showDeleteModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-sm overflow-hidden">
                    <div class="px-6 py-5 text-center">
                        <div class="mx-auto w-12 h-12 rounded-full bg-down-bg flex items-center justify-center mb-4">
                            <span class="material-icons-outlined text-[24px] text-error">delete</span>
                        </div>
                        <h3 class="text-lg font-bold text-mono-900">Excluir negocio?</h3>
                        <p class="text-sm text-mono-600 mt-2">Todo o historico de atividades sera perdido.</p>
                    </div>
                    <div class="flex items-center justify-center gap-3 px-6 py-4 border-t border-mono-100 bg-mono-50">
                        <x-jr.button variant="mono" wire:click="$set('showDeleteModal', false)">Cancelar</x-jr.button>
                        <x-jr.button variant="danger" wire:click="deleteDeal">Excluir</x-jr.button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
