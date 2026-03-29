<div>
    <!-- Flash Messages -->
    @if (session('success'))
        <div class="mb-4"><x-jr.alert variant="success">{{ session('success') }}</x-jr.alert></div>
    @endif

    <!-- Consolidated Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <x-jr.card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-mono-600 font-medium uppercase tracking-wider">Total Investido</p>
                    <p class="text-2xl font-bold text-mono-900 mt-1">
                        R$ {{ number_format($totalInvested, 2, ',', '.') }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-info-bg flex items-center justify-center">
                    <span class="material-icons-outlined text-[22px] text-info">savings</span>
                </div>
            </div>
        </x-jr.card>

        <x-jr.card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-mono-600 font-medium uppercase tracking-wider">Valor Atual</p>
                    <p class="text-2xl font-bold text-mono-900 mt-1">
                        R$ {{ number_format($totalCurrent, 2, ',', '.') }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-primary-100 flex items-center justify-center">
                    <span class="material-icons-outlined text-[22px] text-primary-500">account_balance_wallet</span>
                </div>
            </div>
        </x-jr.card>

        <x-jr.card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-mono-600 font-medium uppercase tracking-wider">Rendimento Total</p>
                    <p class="text-2xl font-bold {{ $totalProfit >= 0 ? 'text-up' : 'text-down' }} mt-1">
                        {{ $totalProfit >= 0 ? '+' : '' }}R$ {{ number_format($totalProfit, 2, ',', '.') }}
                    </p>
                    <x-jr.badge variant="{{ $totalProfit >= 0 ? 'up' : 'down' }}" size="sm" class="mt-1">
                        <span class="material-icons-outlined text-[12px]">
                            {{ $totalProfit >= 0 ? 'arrow_upward' : 'arrow_downward' }}
                        </span>
                        {{ number_format(abs($totalProfitPct), 2, ',', '.') }}%
                    </x-jr.badge>
                </div>
                <div class="w-10 h-10 rounded-xl {{ $totalProfit >= 0 ? 'bg-up-bg' : 'bg-down-bg' }} flex items-center justify-center">
                    <span class="material-icons-outlined text-[22px] {{ $totalProfit >= 0 ? 'text-up' : 'text-down' }}">
                        {{ $totalProfit >= 0 ? 'trending_up' : 'trending_down' }}
                    </span>
                </div>
            </div>
        </x-jr.card>
    </div>

    <!-- Chart + Actions Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <!-- Donut Chart -->
        <x-jr.card>
            <h3 class="text-sm font-semibold text-mono-900 mb-4">Distribuicao por Tipo</h3>
            @if(count($distribution) > 0)
                <div class="h-48 flex items-center justify-center">
                    <canvas id="distributionChart"
                            data-labels='@json(array_column($distribution, "label"))'
                            data-values='@json(array_column($distribution, "value"))'
                            data-colors='@json(array_column($distribution, "color"))'
                    ></canvas>
                </div>
                <div class="mt-4 space-y-2">
                    @foreach($distribution as $item)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full" style="background-color: {{ $item['color'] }}"></div>
                                <span class="text-xs text-mono-900 font-medium">{{ $item['label'] }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-semibold text-mono-900">R$ {{ number_format($item['value'], 2, ',', '.') }}</span>
                                <span class="text-xs text-mono-300 ml-1">({{ $item['percentage'] }}%)</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="h-48 flex items-center justify-center">
                    <p class="text-sm text-mono-300">Sem dados para exibir.</p>
                </div>
            @endif
        </x-jr.card>

        <!-- Table (2 cols) -->
        <div class="lg:col-span-2">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm text-mono-600">{{ $investments->count() }} investimento(s)</p>
                <x-jr.button wire:click="openCreateModal" size="sm">
                    <span class="material-icons-outlined text-[16px]">add</span>
                    Novo Investimento
                </x-jr.button>
            </div>

            @if($investments->isEmpty())
                <x-jr.card>
                    <div class="text-center py-8">
                        <span class="material-icons-outlined text-[48px] text-mono-200">trending_up</span>
                        <p class="text-mono-600 mt-2">Nenhum investimento cadastrado.</p>
                        <div class="mt-4">
                            <x-jr.button wire:click="openCreateModal" size="sm">Criar primeiro investimento</x-jr.button>
                        </div>
                    </div>
                </x-jr.card>
            @else
                <x-jr.table>
                    <x-slot name="head">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-mono-600 uppercase tracking-wider">Nome</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-mono-600 uppercase tracking-wider">Tipo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-mono-600 uppercase tracking-wider">Corretora</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-mono-600 uppercase tracking-wider">Investido</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-mono-600 uppercase tracking-wider">Atual</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-mono-600 uppercase tracking-wider">Rent.</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-mono-600 uppercase tracking-wider">Acoes</th>
                    </x-slot>

                    @foreach($investments as $inv)
                        @php
                            $profit = $inv->profitAmount();
                            $pct = $inv->profitPercentage();
                        @endphp
                        <tr class="border-t border-mono-100 hover:bg-mono-50/50 transition-colors">
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-mono-900">{{ $inv->name }}</p>
                                @if($inv->quantity)
                                    <p class="text-xs text-mono-300 mt-0.5">{{ rtrim(rtrim(number_format($inv->quantity, 8, ',', '.'), '0'), ',') }} un.</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $typeColors = ['crypto' => 'primary', 'fixed_income' => 'info', 'stocks' => 'success', 'funds' => 'neutral', 'other' => 'neutral'];
                                @endphp
                                <x-jr.badge size="sm" variant="{{ $typeColors[$inv->type->value] ?? 'neutral' }}">
                                    {{ $inv->type->label() }}
                                </x-jr.badge>
                            </td>
                            <td class="px-4 py-3 text-sm text-mono-600">{{ $inv->broker ?? '—' }}</td>
                            <td class="px-4 py-3 text-right text-sm text-mono-900">
                                R$ {{ number_format($inv->invested_amount, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-mono-900">
                                R$ {{ number_format($inv->current_amount, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <x-jr.badge variant="{{ $profit >= 0 ? 'up' : 'down' }}" size="sm">
                                    <span class="material-icons-outlined text-[10px]">
                                        {{ $profit >= 0 ? 'arrow_upward' : 'arrow_downward' }}
                                    </span>
                                    {{ number_format(abs($pct), 2, ',', '.') }}%
                                </x-jr.badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button wire:click="openUpdateValue('{{ $inv->id }}')"
                                            class="p-1.5 rounded-lg text-mono-300 hover:text-primary-500 hover:bg-primary-100 transition-colors"
                                            title="Atualizar valor">
                                        <span class="material-icons-outlined text-[16px]">refresh</span>
                                    </button>
                                    <button wire:click="openEditModal('{{ $inv->id }}')"
                                            class="p-1.5 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-100 transition-colors">
                                        <span class="material-icons-outlined text-[16px]">edit</span>
                                    </button>
                                    <button wire:click="confirmDelete('{{ $inv->id }}')"
                                            class="p-1.5 rounded-lg text-mono-300 hover:text-error hover:bg-down-bg transition-colors">
                                        <span class="material-icons-outlined text-[16px]">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-jr.table>
            @endif
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
                            {{ $editingId ? 'Editar Investimento' : 'Novo Investimento' }}
                        </h3>
                        <button wire:click="$set('showModal', false)" class="p-1 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                            <span class="material-icons-outlined text-[20px]">close</span>
                        </button>
                    </div>

                    <form wire:submit="save">
                        <div class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">
                            <x-jr.input label="Nome" wire:model="name" placeholder="Ex: Bitcoin, CDB Inter, PETR4"
                                        icon="trending_up" :error="$errors->first('name')" />

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-mono-600 mb-1.5">Tipo</label>
                                    <select wire:model="type"
                                            class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                        @foreach($types as $t)
                                            <option value="{{ $t->value }}">{{ $t->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <x-jr.input label="Corretora" wire:model="broker" placeholder="Ex: Binance, Inter"
                                            icon="business" :error="$errors->first('broker')" />
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <x-jr.input label="Valor investido (R$)" wire:model="invested_amount" type="number" step="0.01" min="0"
                                            icon="attach_money" :error="$errors->first('invested_amount')" />

                                <x-jr.input label="Valor atual (R$)" wire:model="current_amount" type="number" step="0.01" min="0"
                                            icon="paid" :error="$errors->first('current_amount')" />
                            </div>

                            <x-jr.input label="Quantidade (opcional)" wire:model="quantity" type="number" step="0.00000001" min="0"
                                        icon="tag" :error="$errors->first('quantity')" />

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-mono-600 mb-1.5">Data de compra</label>
                                    <input type="date" wire:model="purchase_date"
                                           class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                    @error('purchase_date') <p class="text-xs font-medium text-error mt-1.5 pl-4">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-mono-600 mb-1.5">Vencimento (opcional)</label>
                                    <input type="date" wire:model="maturity_date"
                                           class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                </div>
                            </div>

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
                                {{ $editingId ? 'Salvar' : 'Criar Investimento' }}
                            </x-jr.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Update Value Modal -->
    @if($showUpdateValueModal)
        <div class="fixed inset-0 z-modal overflow-y-auto" wire:keydown.escape="$set('showUpdateValueModal', false)">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showUpdateValueModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-sm overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-mono-100">
                        <h3 class="text-lg font-bold text-mono-900">Atualizar Valor</h3>
                        <button wire:click="$set('showUpdateValueModal', false)" class="p-1 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                            <span class="material-icons-outlined text-[20px]">close</span>
                        </button>
                    </div>
                    <form wire:submit="updateValue">
                        <div class="px-6 py-5">
                            <x-jr.input label="Novo valor atual (R$)" wire:model="new_value" type="number" step="0.01" min="0"
                                        icon="paid" :error="$errors->first('new_value')" />
                        </div>
                        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-mono-100 bg-mono-50">
                            <x-jr.button variant="mono" wire:click="$set('showUpdateValueModal', false)" type="button">Cancelar</x-jr.button>
                            <x-jr.button type="submit">Atualizar</x-jr.button>
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
                        <h3 class="text-lg font-bold text-mono-900">Excluir investimento?</h3>
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

@script
<script>
    const donutCanvas = document.getElementById('distributionChart');
    if (donutCanvas && typeof Chart !== 'undefined') {
        const ctx = donutCanvas.getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: JSON.parse(donutCanvas.dataset.labels),
                datasets: [{
                    data: JSON.parse(donutCanvas.dataset.values),
                    backgroundColor: JSON.parse(donutCanvas.dataset.colors),
                    borderWidth: 0,
                    spacing: 2,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const pct = ((ctx.parsed / total) * 100).toFixed(1);
                                return `${ctx.label}: R$ ${ctx.parsed.toLocaleString('pt-BR', {minimumFractionDigits: 2})} (${pct}%)`;
                            }
                        }
                    }
                }
            }
        });
    }
</script>
@endscript
