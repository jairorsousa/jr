<div>
    <!-- Flash Messages -->
    @if (session('success'))
        <div class="mb-4"><x-jr.alert variant="success">{{ session('success') }}</x-jr.alert></div>
    @endif
    @if (session('error'))
        <div class="mb-4"><x-jr.alert variant="error">{{ session('error') }}</x-jr.alert></div>
    @endif

    <!-- Month Navigation -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-2">
            <button wire:click="previousMonth" class="p-2 rounded-xl text-mono-600 hover:bg-mono-100 transition-colors" title="Mes anterior">
                <span class="material-icons-outlined text-[22px]">chevron_left</span>
            </button>

            <div class="text-center min-w-[180px]">
                @if($customRange)
                    <h2 class="text-lg font-bold text-mono-900">Periodo personalizado</h2>
                    <p class="text-xs text-mono-500">{{ \Carbon\Carbon::parse($filterDateFrom)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($filterDateTo)->format('d/m/Y') }}</p>
                @else
                    <h2 class="text-lg font-bold text-mono-900">{{ $monthLabel }}</h2>
                    @unless($isCurrentMonth)
                        <button wire:click="goToCurrentMonth" class="text-xs text-primary-500 hover:underline font-medium">
                            Voltar ao mes atual
                        </button>
                    @endunless
                @endif
            </div>

            <button wire:click="nextMonth" class="p-2 rounded-xl text-mono-600 hover:bg-mono-100 transition-colors" title="Proximo mes">
                <span class="material-icons-outlined text-[22px]">chevron_right</span>
            </button>
        </div>

        <div class="flex items-center gap-2">
            <!-- Custom date range toggle -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                        class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-medium transition-colors
                               {{ $customRange ? 'bg-primary-100 text-primary-600' : 'text-mono-600 hover:bg-mono-100' }}">
                    <span class="material-icons-outlined text-[18px]">date_range</span>
                    <span class="hidden sm:inline">Periodo</span>
                </button>

                <div x-show="open" x-cloak @click.away="open = false"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="absolute right-0 top-11 bg-mono-white rounded-2xl shadow-elevated border border-mono-100 p-4 z-50 w-72">
                    <p class="text-sm font-semibold text-mono-900 mb-3">Periodo personalizado</p>
                    <div class="space-y-3">
                        <div>
                            <label class="text-xs text-mono-500 font-medium mb-1 block">Data inicial</label>
                            <input type="date" wire:model="filterDateFrom"
                                   class="w-full bg-mono-white border border-mono-200 rounded-xl px-3 h-10 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                        </div>
                        <div>
                            <label class="text-xs text-mono-500 font-medium mb-1 block">Data final</label>
                            <input type="date" wire:model="filterDateTo"
                                   class="w-full bg-mono-white border border-mono-200 rounded-xl px-3 h-10 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                        </div>
                        <div class="flex gap-2">
                            <button wire:click="applyCustomRange" @click="open = false"
                                    class="flex-1 h-9 rounded-xl bg-primary-500 text-white text-xs font-semibold hover:bg-primary-600 transition-colors">
                                Aplicar
                            </button>
                            @if($customRange)
                                <button wire:click="clearCustomRange" @click="open = false"
                                        class="h-9 px-3 rounded-xl bg-mono-100 text-mono-600 text-xs font-semibold hover:bg-mono-200 transition-colors">
                                    Limpar
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <x-jr.button wire:click="openCreateModal" size="sm">
                <span class="material-icons-outlined text-[16px]">add</span>
                Nova
            </x-jr.button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <x-jr.card>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-up-bg flex items-center justify-center">
                    <span class="material-icons-outlined text-[22px] text-up">arrow_upward</span>
                </div>
                <div>
                    <p class="text-xs text-mono-600">Receitas</p>
                    <p class="text-lg font-bold text-up">R$ {{ number_format($totalIncome, 2, ',', '.') }}</p>
                </div>
            </div>
        </x-jr.card>
        <x-jr.card>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-down-bg flex items-center justify-center">
                    <span class="material-icons-outlined text-[22px] text-down">arrow_downward</span>
                </div>
                <div>
                    <p class="text-xs text-mono-600">Despesas</p>
                    <p class="text-lg font-bold text-down">R$ {{ number_format($totalExpense, 2, ',', '.') }}</p>
                </div>
            </div>
        </x-jr.card>
        <x-jr.card>
            <div class="flex items-center gap-3">
                @php $balance = $totalIncome - $totalExpense; @endphp
                <div class="w-10 h-10 rounded-xl {{ $balance >= 0 ? 'bg-up-bg' : 'bg-down-bg' }} flex items-center justify-center">
                    <span class="material-icons-outlined text-[22px] {{ $balance >= 0 ? 'text-up' : 'text-down' }}">account_balance</span>
                </div>
                <div>
                    <p class="text-xs text-mono-600">Saldo do Periodo</p>
                    <p class="text-lg font-bold {{ $balance >= 0 ? 'text-up' : 'text-down' }}">
                        {{ $balance < 0 ? '-' : '' }}R$ {{ number_format(abs($balance), 2, ',', '.') }}
                    </p>
                </div>
            </div>
        </x-jr.card>
    </div>

    <!-- Filters -->
    <x-jr.card class="mb-4">
        <div class="flex flex-col lg:flex-row gap-4">
            <!-- Search -->
            <div class="flex-1">
                <x-jr.input wire:model.live.debounce.300ms="search" placeholder="Buscar por descricao..." icon="search" />
            </div>

            <!-- Filter pills -->
            <div class="flex flex-wrap items-center gap-2">
                <select wire:model.live="filterType"
                        class="bg-mono-white border border-mono-200 rounded-pill px-3 h-10 text-xs font-medium text-mono-900 focus:border-primary-500 focus:ring-0">
                    <option value="">Tipo</option>
                    <option value="income">Receita</option>
                    <option value="expense">Despesa</option>
                </select>

                <select wire:model.live="filterCategory"
                        class="bg-mono-white border border-mono-200 rounded-pill px-3 h-10 text-xs font-medium text-mono-900 focus:border-primary-500 focus:ring-0">
                    <option value="">Categoria</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>

                <select wire:model.live="filterAccount"
                        class="bg-mono-white border border-mono-200 rounded-pill px-3 h-10 text-xs font-medium text-mono-900 focus:border-primary-500 focus:ring-0">
                    <option value="">Conta</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                    @endforeach
                </select>

                <select wire:model.live="filterStatus"
                        class="bg-mono-white border border-mono-200 rounded-pill px-3 h-10 text-xs font-medium text-mono-900 focus:border-primary-500 focus:ring-0">
                    <option value="">Status</option>
                    <option value="paid">Pago</option>
                    <option value="pending">Pendente</option>
                </select>

                @if($search || $filterType || $filterCategory || $filterAccount || $filterStatus)
                    <button wire:click="clearFilters" class="text-xs text-primary-500 hover:underline font-medium">Limpar</button>
                @endif
            </div>
        </div>
    </x-jr.card>

    <!-- Transactions Table -->
    @if($transactions->isEmpty())
        <x-jr.card>
            <div class="text-center py-8">
                <span class="material-icons-outlined text-[48px] text-mono-200">receipt_long</span>
                <p class="text-mono-600 mt-2">Nenhuma transacao encontrada neste periodo.</p>
                <div class="mt-4">
                    <x-jr.button wire:click="openCreateModal" size="sm">Criar primeira transacao</x-jr.button>
                </div>
            </div>
        </x-jr.card>
    @else
        <x-jr.table>
            <x-slot name="head">
                <th class="px-4 py-3 text-left text-xs font-semibold text-mono-600 uppercase tracking-wider">Data</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-mono-600 uppercase tracking-wider">Descricao</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-mono-600 uppercase tracking-wider">Categoria</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-mono-600 uppercase tracking-wider">Conta</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-mono-600 uppercase tracking-wider">Valor</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-mono-600 uppercase tracking-wider">Status</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-mono-600 uppercase tracking-wider">Acoes</th>
            </x-slot>

            @foreach($transactions as $transaction)
                <tr class="border-t border-mono-100 hover:bg-mono-50/50 transition-colors">
                    <td class="px-4 py-3 text-sm text-mono-900 whitespace-nowrap">
                        {{ $transaction->date->format('d/m/Y') }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <span class="material-icons-outlined text-[16px] {{ $transaction->type === \App\Enums\TransactionType::Income ? 'text-up' : 'text-down' }}">
                                {{ $transaction->type === \App\Enums\TransactionType::Income ? 'arrow_upward' : 'arrow_downward' }}
                            </span>
                            <span class="text-sm font-medium text-mono-900">{{ $transaction->description }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        @if($transaction->category)
                            <x-jr.badge size="sm" style="background-color: {{ $transaction->category->color }}15; color: {{ $transaction->category->color }}">
                                {{ $transaction->category->name }}
                            </x-jr.badge>
                        @else
                            <span class="text-xs text-mono-300">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-mono-600">
                        {{ $transaction->account->name ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <span class="text-sm font-semibold {{ $transaction->type === \App\Enums\TransactionType::Income ? 'text-up' : 'text-down' }}">
                            {{ $transaction->type === \App\Enums\TransactionType::Income ? '+' : '-' }}
                            R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($transaction->is_paid)
                            <x-jr.badge variant="success" size="sm">Pago</x-jr.badge>
                        @else
                            <button wire:click="markAsPaid('{{ $transaction->id }}')"
                                    class="inline-flex items-center gap-1 text-xs font-semibold text-primary-500 bg-primary-100 rounded-pill px-2.5 py-1 hover:bg-primary-500 hover:text-white transition-colors">
                                Pendente
                            </button>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <button wire:click="openEditModal('{{ $transaction->id }}')"
                                    class="p-1.5 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-100 transition-colors">
                                <span class="material-icons-outlined text-[16px]">edit</span>
                            </button>
                            <button wire:click="confirmDelete('{{ $transaction->id }}')"
                                    class="p-1.5 rounded-lg text-mono-300 hover:text-error hover:bg-down-bg transition-colors">
                                <span class="material-icons-outlined text-[16px]">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-jr.table>

        <div class="mt-4">
            {{ $transactions->links() }}
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
                            {{ $editingId ? 'Editar Transacao' : 'Nova Transacao' }}
                        </h3>
                        <button wire:click="$set('showModal', false)" class="p-1 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                            <span class="material-icons-outlined text-[20px]">close</span>
                        </button>
                    </div>

                    <form wire:submit="save">
                        <div class="px-6 py-5 space-y-4 max-h-[65vh] overflow-y-auto">
                            <!-- Type Toggle -->
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

                            <x-jr.input label="Descricao" wire:model="description" placeholder="Ex: Supermercado, Salario"
                                        icon="description" :error="$errors->first('description')" />

                            <x-jr.input label="Valor (R$)" wire:model="amount" type="number" step="0.01" min="0.01"
                                        icon="attach_money" :error="$errors->first('amount')" />

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-mono-600 mb-1.5">Data</label>
                                    <input type="date" wire:model="date"
                                           class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                    @error('date') <p class="text-xs font-medium text-error mt-1.5 pl-4">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-mono-600 mb-1.5">Vencimento</label>
                                    <input type="date" wire:model="due_date"
                                           class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                </div>
                            </div>

                            <!-- Category -->
                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Categoria</label>
                                <select wire:model="category_id"
                                        class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                    <option value="">Selecione...</option>
                                    @foreach($categories->where('type.value', $type) as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <p class="text-xs font-medium text-error mt-1.5 pl-4">{{ $message }}</p> @enderror
                            </div>

                            <!-- Account -->
                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Conta</label>
                                <select wire:model="account_id"
                                        class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                    <option value="">Selecione...</option>
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                                    @endforeach
                                </select>
                                @error('account_id') <p class="text-xs font-medium text-error mt-1.5 pl-4">{{ $message }}</p> @enderror
                            </div>

                            <!-- Credit Card (expense only) -->
                            @if($type === 'expense')
                                <div>
                                    <label class="block text-sm font-medium text-mono-600 mb-1.5">Cartao de credito (opcional)</label>
                                    <select wire:model.live="credit_card_id"
                                            class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                        <option value="">Nenhum (pagamento direto)</option>
                                        @foreach($creditCards as $cc)
                                            <option value="{{ $cc->id }}">{{ $cc->name }} (•••• {{ $cc->last_digits }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Installments (only when credit card selected) -->
                                @if($credit_card_id && !$editingId)
                                    <div>
                                        <label class="block text-sm font-medium text-mono-600 mb-1.5">Parcelas</label>
                                        <div class="flex items-center gap-3">
                                            <select wire:model="installments"
                                                    class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                                @for($i = 1; $i <= 24; $i++)
                                                    <option value="{{ $i }}">
                                                        {{ $i }}x {{ $i > 1 ? 'de R$ ' . number_format((float)$amount / $i, 2, ',', '.') : '(a vista)' }}
                                                    </option>
                                                @endfor
                                            </select>
                                        </div>
                                        @if($installments > 1 && $amount)
                                            <p class="text-xs text-mono-600 mt-1.5 pl-4">
                                                {{ $installments }}x de R$ {{ number_format((float)$amount / $installments, 2, ',', '.') }}
                                                · Total: R$ {{ number_format((float)$amount, 2, ',', '.') }}
                                            </p>
                                        @endif
                                    </div>
                                @endif
                            @endif

                            <!-- Paid -->
                            <label class="flex items-center gap-3 cursor-pointer">
                                <div class="relative">
                                    <input type="checkbox" wire:model="is_paid" class="sr-only peer">
                                    <div class="w-11 h-6 bg-mono-200 rounded-full peer-checked:bg-primary-500 transition-colors"></div>
                                    <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-mono-white rounded-full shadow transition-transform peer-checked:translate-x-5"></div>
                                </div>
                                <span class="text-sm font-medium text-mono-900">Ja foi pago</span>
                            </label>

                            <!-- Notes -->
                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Observacoes</label>
                                <textarea wire:model="notes" rows="2" placeholder="Notas opcionais..."
                                          class="w-full bg-mono-white border border-mono-200 rounded-xl px-4 py-3 text-sm text-mono-900 placeholder:text-mono-300 focus:border-primary-500 focus:ring-0 resize-none"></textarea>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-mono-100 bg-mono-50">
                            <x-jr.button variant="mono" wire:click="$set('showModal', false)" type="button">Cancelar</x-jr.button>
                            <x-jr.button type="submit" wire:loading.attr="disabled">
                                <span wire:loading wire:target="save" class="material-icons-outlined text-[18px] animate-spin">autorenew</span>
                                {{ $editingId ? 'Salvar' : 'Criar Transacao' }}
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
                        <h3 class="text-lg font-bold text-mono-900">Excluir transacao?</h3>
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
