<div>
    <!-- Flash Messages -->
    @if (session('success'))
        <div class="mb-4"><x-jr.alert variant="success">{{ session('success') }}</x-jr.alert></div>
    @endif
    @if (session('error'))
        <div class="mb-4"><x-jr.alert variant="error">{{ session('error') }}</x-jr.alert></div>
    @endif

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-mono-600">Gerencie seus cartoes de credito</p>
        <x-jr.button wire:click="openCreateModal">
            <span class="material-icons-outlined text-[18px]">add</span>
            Novo Cartao
        </x-jr.button>
    </div>

    <!-- Cards Grid -->
    @if($cards->isEmpty())
        <x-jr.card>
            <div class="text-center py-8">
                <span class="material-icons-outlined text-[48px] text-mono-200">credit_card</span>
                <p class="text-mono-600 mt-2">Nenhum cartao cadastrado.</p>
                <div class="mt-4">
                    <x-jr.button wire:click="openCreateModal" size="sm">Criar primeiro cartao</x-jr.button>
                </div>
            </div>
        </x-jr.card>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($cards as $card)
                @php
                    $invoiceAmount = $cardInvoices[$card->id] ?? 0;
                    $available = $card->credit_limit - $invoiceAmount;
                    $usedPercent = $card->credit_limit > 0 ? ($invoiceAmount / $card->credit_limit) * 100 : 0;
                @endphp
                <div class="{{ !$card->is_active ? 'opacity-50' : '' }}">
                    <!-- Visual Card -->
                    <div class="rounded-2xl p-5 text-white relative overflow-hidden h-48 flex flex-col justify-between shadow-card"
                         style="background: linear-gradient(135deg, {{ $card->color }}, {{ $card->color }}cc)">
                        <!-- Pattern overlay -->
                        <div class="absolute inset-0 opacity-10">
                            <div class="absolute -right-8 -top-8 w-40 h-40 rounded-full border-[20px] border-white/20"></div>
                            <div class="absolute -right-4 top-16 w-24 h-24 rounded-full border-[12px] border-white/10"></div>
                        </div>

                        <div class="relative z-10">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold opacity-90">{{ $card->name }}</span>
                                <span class="text-xs font-bold uppercase tracking-wider opacity-75">{{ $card->brand->label() }}</span>
                            </div>
                        </div>

                        <div class="relative z-10">
                            <p class="text-lg font-mono tracking-[0.2em] mb-1">
                                •••• •••• •••• {{ $card->last_digits }}
                            </p>
                            <div class="flex items-center justify-between text-xs opacity-75">
                                <span>Fecha dia {{ $card->closing_day }} · Vence dia {{ $card->due_day }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Info below card -->
                    <x-jr.card class="mt-3">
                        <div class="space-y-3">
                            <!-- Limit bar -->
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1.5">
                                    <span class="text-mono-600">Fatura atual</span>
                                    <span class="font-semibold text-mono-900">R$ {{ number_format($invoiceAmount, 2, ',', '.') }}</span>
                                </div>
                                <div class="h-2 bg-mono-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-500 {{ $usedPercent > 80 ? 'bg-error' : ($usedPercent > 50 ? 'bg-primary-500' : 'bg-up') }}"
                                         style="width: {{ min($usedPercent, 100) }}%"></div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 text-center">
                                <div class="bg-mono-50 rounded-xl p-2.5">
                                    <p class="text-[10px] text-mono-600 uppercase font-semibold">Limite</p>
                                    <p class="text-sm font-bold text-mono-900">R$ {{ number_format($card->credit_limit, 2, ',', '.') }}</p>
                                </div>
                                <div class="bg-mono-50 rounded-xl p-2.5">
                                    <p class="text-[10px] text-mono-600 uppercase font-semibold">Disponivel</p>
                                    <p class="text-sm font-bold {{ $available >= 0 ? 'text-up' : 'text-down' }}">R$ {{ number_format(max($available, 0), 2, ',', '.') }}</p>
                                </div>
                            </div>

                            @if($card->account)
                                <p class="text-xs text-mono-300">
                                    <span class="material-icons-outlined text-[12px] align-middle">link</span>
                                    Pagamento via {{ $card->account->name }}
                                </p>
                            @endif

                            <!-- Actions -->
                            <div class="flex items-center justify-between pt-2 border-t border-mono-100">
                                <a href="{{ route('financeiro.fatura', $card->id) }}"
                                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary-500 hover:text-primary-600 transition-colors">
                                    <span class="material-icons-outlined text-[14px]">receipt</span>
                                    Ver fatura
                                </a>
                                <div class="flex items-center gap-1">
                                    <button wire:click="openEditModal('{{ $card->id }}')"
                                            class="p-1.5 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-100 transition-colors">
                                        <span class="material-icons-outlined text-[16px]">edit</span>
                                    </button>
                                    <button wire:click="toggleActive('{{ $card->id }}')"
                                            class="p-1.5 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-100 transition-colors">
                                        <span class="material-icons-outlined text-[16px]">{{ $card->is_active ? 'visibility_off' : 'visibility' }}</span>
                                    </button>
                                    <button wire:click="confirmDelete('{{ $card->id }}')"
                                            class="p-1.5 rounded-lg text-mono-300 hover:text-error hover:bg-down-bg transition-colors">
                                        <span class="material-icons-outlined text-[16px]">delete</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </x-jr.card>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-modal overflow-y-auto" wire:keydown.escape="$set('showModal', false)">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-lg overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-mono-100">
                        <h3 class="text-lg font-bold text-mono-900">
                            {{ $editingId ? 'Editar Cartao' : 'Novo Cartao' }}
                        </h3>
                        <button wire:click="$set('showModal', false)" class="p-1 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                            <span class="material-icons-outlined text-[20px]">close</span>
                        </button>
                    </div>

                    <form wire:submit="save">
                        <div class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">
                            <x-jr.input label="Nome do cartao" wire:model="name" placeholder="Ex: Nubank Gold"
                                        icon="credit_card" :error="$errors->first('name')" />

                            <div class="grid grid-cols-2 gap-3">
                                <x-jr.input label="Ultimos 4 digitos" wire:model="last_digits" placeholder="0000" maxlength="4"
                                            icon="pin" :error="$errors->first('last_digits')" />

                                <div>
                                    <label class="block text-sm font-medium text-mono-600 mb-1.5">Bandeira</label>
                                    <select wire:model="brand"
                                            class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                        @foreach($brands as $b)
                                            <option value="{{ $b->value }}">{{ $b->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <x-jr.input label="Limite (R$)" wire:model="credit_limit" type="number" step="0.01" min="0"
                                        icon="attach_money" :error="$errors->first('credit_limit')" />

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-mono-600 mb-1.5">Dia de fechamento</label>
                                    <input type="number" wire:model="closing_day" min="1" max="31"
                                           class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                    @error('closing_day') <p class="text-xs font-medium text-error mt-1.5 pl-4">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-mono-600 mb-1.5">Dia de vencimento</label>
                                    <input type="number" wire:model="due_day" min="1" max="31"
                                           class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                    @error('due_day') <p class="text-xs font-medium text-error mt-1.5 pl-4">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <!-- Account for payment -->
                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Conta de pagamento</label>
                                <select wire:model="account_id"
                                        class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                    <option value="">Nenhuma</option>
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Color -->
                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Cor do cartao</label>
                                <div class="flex items-center gap-3">
                                    @php
                                        $colors = ['#212529', '#5C6BC0', '#7B1FA2', '#C62828', '#00695C', '#E65100', '#1565C0', '#2E7D32', '#4E342E', '#37474F'];
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

                        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-mono-100 bg-mono-50">
                            <x-jr.button variant="mono" wire:click="$set('showModal', false)" type="button">Cancelar</x-jr.button>
                            <x-jr.button type="submit" wire:loading.attr="disabled">
                                <span wire:loading wire:target="save" class="material-icons-outlined text-[18px] animate-spin">autorenew</span>
                                {{ $editingId ? 'Salvar' : 'Criar Cartao' }}
                            </x-jr.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-modal overflow-y-auto">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showDeleteModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-sm overflow-hidden">
                    <div class="px-6 py-5 text-center">
                        <div class="mx-auto w-12 h-12 rounded-full bg-down-bg flex items-center justify-center mb-4">
                            <span class="material-icons-outlined text-[24px] text-error">delete</span>
                        </div>
                        <h3 class="text-lg font-bold text-mono-900">Excluir cartao?</h3>
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
