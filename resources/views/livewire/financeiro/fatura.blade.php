<div>
    <!-- Flash Messages -->
    @if (session('success'))
        <div class="mb-4"><x-jr.alert variant="success">{{ session('success') }}</x-jr.alert></div>
    @endif
    @if (session('error'))
        <div class="mb-4"><x-jr.alert variant="error">{{ session('error') }}</x-jr.alert></div>
    @endif

    <!-- Card Info + Navigation -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('financeiro.cartoes') }}" class="p-2 rounded-xl text-mono-300 hover:text-mono-600 hover:bg-mono-100 transition-colors">
                <span class="material-icons-outlined text-[20px]">arrow_back</span>
            </a>
            <div>
                <h2 class="text-lg font-bold text-mono-900">{{ $card->name }}</h2>
                <p class="text-xs text-mono-600">•••• {{ $card->last_digits }} · {{ $card->brand->label() }}</p>
            </div>
        </div>

        <!-- Month Navigation -->
        <div class="flex items-center gap-2">
            <button wire:click="previousMonth"
                    class="p-2 rounded-xl text-mono-600 hover:bg-mono-100 transition-colors">
                <span class="material-icons-outlined text-[20px]">chevron_left</span>
            </button>
            <span class="text-sm font-semibold text-mono-900 min-w-[140px] text-center">{{ $monthLabel }}</span>
            <button wire:click="nextMonth"
                    class="p-2 rounded-xl text-mono-600 hover:bg-mono-100 transition-colors">
                <span class="material-icons-outlined text-[20px]">chevron_right</span>
            </button>
        </div>
    </div>

    <!-- Invoice Summary -->
    <x-jr.card class="mb-6">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex items-center gap-6">
                <!-- Total -->
                <div>
                    <p class="text-xs text-mono-600 font-medium uppercase">Total da Fatura</p>
                    <p class="text-3xl font-bold text-mono-900 mt-0.5">
                        R$ {{ number_format($totalAmount, 2, ',', '.') }}
                    </p>
                </div>

                <!-- Status -->
                <div>
                    @if($invoice?->is_paid)
                        <x-jr.badge variant="success">
                            <span class="material-icons-outlined text-[12px]">check_circle</span>
                            Paga
                        </x-jr.badge>
                    @elseif($invoice?->is_closed)
                        <x-jr.badge variant="primary">
                            <span class="material-icons-outlined text-[12px]">lock</span>
                            Fechada
                        </x-jr.badge>
                    @else
                        <x-jr.badge variant="neutral">
                            <span class="material-icons-outlined text-[12px]">lock_open</span>
                            Aberta
                        </x-jr.badge>
                    @endif
                </div>

                <!-- Due date -->
                @if($invoice?->due_date)
                    <div>
                        <p class="text-xs text-mono-600">Vencimento</p>
                        <p class="text-sm font-semibold text-mono-900">{{ $invoice->due_date->format('d/m/Y') }}</p>
                    </div>
                @endif
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2">
                @if($invoice && !$invoice->is_paid)
                    @if(!$invoice->is_closed)
                        <x-jr.button variant="standard" wire:click="closeInvoice" size="sm"
                                     wire:confirm="Tem certeza que deseja fechar esta fatura?">
                            <span class="material-icons-outlined text-[16px]">lock</span>
                            Fechar Fatura
                        </x-jr.button>
                    @else
                        <x-jr.button variant="mono" wire:click="reopenInvoice" size="sm">
                            <span class="material-icons-outlined text-[16px]">lock_open</span>
                            Reabrir
                        </x-jr.button>
                    @endif

                    @if($invoice->is_closed)
                        <x-jr.button wire:click="payInvoice" size="sm"
                                     wire:confirm="Confirma o pagamento desta fatura? Uma transacao sera criada na conta vinculada.">
                            <span class="material-icons-outlined text-[16px]">payments</span>
                            Pagar Fatura
                        </x-jr.button>
                    @endif
                @endif
            </div>
        </div>

        <!-- Limit info bar -->
        @php
            $usedPercent = $card->credit_limit > 0 ? ($totalAmount / $card->credit_limit) * 100 : 0;
        @endphp
        <div class="mt-4 pt-4 border-t border-mono-100">
            <div class="flex items-center justify-between text-xs text-mono-600 mb-1.5">
                <span>R$ {{ number_format($totalAmount, 2, ',', '.') }} de R$ {{ number_format($card->credit_limit, 2, ',', '.') }}</span>
                <span>{{ number_format($usedPercent, 0) }}% utilizado</span>
            </div>
            <div class="h-2 bg-mono-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-500 {{ $usedPercent > 80 ? 'bg-error' : ($usedPercent > 50 ? 'bg-primary-500' : 'bg-up') }}"
                     style="width: {{ min($usedPercent, 100) }}%"></div>
            </div>
        </div>
    </x-jr.card>

    <!-- Transactions List -->
    @if(empty($transactions) || (is_countable($transactions) && count($transactions) === 0))
        <x-jr.card>
            <div class="text-center py-8">
                <span class="material-icons-outlined text-[48px] text-mono-200">receipt</span>
                <p class="text-mono-600 mt-2">Nenhuma transacao nesta fatura.</p>
            </div>
        </x-jr.card>
    @else
        <x-jr.table>
            <x-slot name="head">
                <th class="px-4 py-3 text-left text-xs font-semibold text-mono-600 uppercase tracking-wider">Data</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-mono-600 uppercase tracking-wider">Descricao</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-mono-600 uppercase tracking-wider">Categoria</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-mono-600 uppercase tracking-wider">Parcela</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-mono-600 uppercase tracking-wider">Valor</th>
            </x-slot>

            @foreach($transactions as $transaction)
                <tr class="border-t border-mono-100 hover:bg-mono-50/50 transition-colors">
                    <td class="px-4 py-3 text-sm text-mono-900 whitespace-nowrap">
                        {{ $transaction->date->format('d/m') }}
                    </td>
                    <td class="px-4 py-3 text-sm font-medium text-mono-900">
                        {{ $transaction->description }}
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
                    <td class="px-4 py-3 text-center">
                        @if($transaction->installment_total)
                            <span class="text-xs text-mono-600">{{ $transaction->installment_number }}/{{ $transaction->installment_total }}</span>
                        @else
                            <span class="text-xs text-mono-300">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <span class="text-sm font-semibold text-down">
                            R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                        </span>
                    </td>
                </tr>
            @endforeach

            <!-- Total row -->
            <tr class="border-t-2 border-mono-200 bg-mono-50">
                <td colspan="4" class="px-4 py-3 text-sm font-bold text-mono-900 text-right">Total</td>
                <td class="px-4 py-3 text-right">
                    <span class="text-sm font-bold text-mono-900">
                        R$ {{ number_format($totalAmount, 2, ',', '.') }}
                    </span>
                </td>
            </tr>
        </x-jr.table>
    @endif
</div>
