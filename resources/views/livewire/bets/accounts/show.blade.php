<div>
    @if (session('success'))
        <div class="mb-4"><x-jr.alert variant="success">{{ session('success') }}</x-jr.alert></div>
    @endif

    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between mb-6">
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('bets.accounts') }}" class="text-mono-600 hover:text-primary-500 transition-colors">
                    <span class="material-icons-outlined text-[20px]">arrow_back</span>
                </a>
                <h2 class="text-2xl font-bold text-mono-900">{{ $account->name }}</h2>
                <x-jr.badge variant="{{ $account->status->badge() }}">{{ $account->status->label() }}</x-jr.badge>
            </div>
            <p class="text-sm text-mono-600 mt-1">{{ $account->bettingHouse?->name }} · {{ $account->betUser?->name }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <x-jr.button variant="mono" wire:click="markChecked" size="sm">
                <span class="material-icons-outlined text-[16px]">fact_check</span>
                Conferir
            </x-jr.button>
            <x-jr.button variant="mono" wire:click="recalculate" size="sm">
                <span class="material-icons-outlined text-[16px]">sync</span>
                Recalcular
            </x-jr.button>
            <x-jr.button href="{{ route('bets.transactions', ['filterAccount' => $account->id]) }}" size="sm">
                <span class="material-icons-outlined text-[16px]">add</span>
                Nova Transacao
            </x-jr.button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <x-jr.card>
            <p class="text-xs text-mono-600">Saldo atual</p>
            <p class="text-2xl font-bold text-mono-900 mt-1">R$ {{ number_format($account->current_balance, 2, ',', '.') }}</p>
            <p class="text-xs text-mono-600 mt-2">Inicial: R$ {{ number_format($account->initial_balance, 2, ',', '.') }}</p>
        </x-jr.card>
        <x-jr.card>
            <p class="text-xs text-mono-600">Resultado do periodo</p>
            <p class="text-2xl font-bold {{ $profit >= 0 ? 'text-up' : 'text-down' }} mt-1">
                {{ $profit < 0 ? '-' : '' }}R$ {{ number_format(abs($profit), 2, ',', '.') }}
            </p>
            <p class="text-xs text-mono-600 mt-2">ROI {{ number_format($roi, 2, ',', '.') }}%</p>
        </x-jr.card>
        <x-jr.card>
            <p class="text-xs text-mono-600">Apostado / Retorno</p>
            <p class="text-lg font-bold text-mono-900 mt-1">R$ {{ number_format($stake, 2, ',', '.') }}</p>
            <p class="text-xs text-mono-600 mt-2">Retorno: R$ {{ number_format($payout, 2, ',', '.') }}</p>
        </x-jr.card>
        <x-jr.card>
            <p class="text-xs text-mono-600">Ultima conferencia</p>
            <p class="text-lg font-bold text-mono-900 mt-1">
                {{ $account->last_checked_at ? $account->last_checked_at->format('d/m/Y H:i') : 'Nunca' }}
            </p>
            <p class="text-xs text-mono-600 mt-2">Bonus: R$ {{ number_format($account->bonus_balance, 2, ',', '.') }}</p>
        </x-jr.card>
    </div>

    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between mb-4">
        <div class="flex items-center gap-2">
            <button wire:click="previousMonth" class="p-2 rounded-xl text-mono-600 hover:bg-mono-100 transition-colors">
                <span class="material-icons-outlined text-[22px]">chevron_left</span>
            </button>
            <div class="text-center min-w-[180px]">
                <h3 class="text-lg font-bold text-mono-900">{{ $monthLabel }}</h3>
                @unless($isCurrentMonth)
                    <button wire:click="goToCurrentMonth" class="text-xs text-primary-500 hover:underline font-medium">Voltar ao mes atual</button>
                @endunless
            </div>
            <button wire:click="nextMonth" class="p-2 rounded-xl text-mono-600 hover:bg-mono-100 transition-colors">
                <span class="material-icons-outlined text-[22px]">chevron_right</span>
            </button>
        </div>

        <div class="flex flex-wrap gap-2">
            <x-jr.input wire:model.live.debounce.300ms="search" placeholder="Buscar no extrato..." icon="search" />
            <select wire:model.live="filterType" class="bg-mono-white border border-mono-200 rounded-pill px-3 h-10 text-xs font-medium text-mono-900 focus:border-primary-500 focus:ring-0">
                <option value="">Tipo</option>
                @foreach($types as $item)
                    <option value="{{ $item->value }}">{{ $item->label() }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterStatus" class="bg-mono-white border border-mono-200 rounded-pill px-3 h-10 text-xs font-medium text-mono-900 focus:border-primary-500 focus:ring-0">
                <option value="">Status</option>
                @foreach($statuses as $item)
                    <option value="{{ $item->value }}">{{ $item->label() }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if($transactions->isEmpty())
        <x-jr.card>
            <div class="text-center py-8">
                <span class="material-icons-outlined text-[48px] text-mono-200">receipt_long</span>
                <p class="text-mono-600 mt-2">Nenhuma transacao neste periodo.</p>
                <div class="mt-4">
                    <x-jr.button href="{{ route('bets.transactions', ['filterAccount' => $account->id]) }}" size="sm">Criar transacao</x-jr.button>
                </div>
            </div>
        </x-jr.card>
    @else
        <x-jr.table>
            <x-slot name="head">
                <th class="px-4 py-3 text-left text-xs font-semibold text-mono-600 uppercase tracking-wider">Data</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-mono-600 uppercase tracking-wider">Descricao</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-mono-600 uppercase tracking-wider">Tipo</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-mono-600 uppercase tracking-wider">Valor</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-mono-600 uppercase tracking-wider">Status</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-mono-600 uppercase tracking-wider">Saldo</th>
            </x-slot>
            @foreach($transactions as $transaction)
                <tr class="border-t border-mono-100 hover:bg-mono-50/50 transition-colors">
                    <td class="px-4 py-3 text-sm text-mono-900 whitespace-nowrap">{{ $transaction->occurred_at->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3">
                        <p class="text-sm font-medium text-mono-900">{{ $transaction->description }}</p>
                        @if($transaction->event_name)
                            <p class="text-xs text-mono-600">{{ $transaction->event_name }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <x-jr.badge variant="{{ $transaction->type->badge() }}" size="sm">
                            <span class="material-icons-outlined text-[13px]">{{ $transaction->type->icon() }}</span>
                            {{ $transaction->type->label() }}
                        </x-jr.badge>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <span class="text-sm font-semibold {{ $transaction->type->isIn() ? 'text-up' : 'text-down' }}">
                            {{ $transaction->type->isIn() ? '+' : '-' }} R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <x-jr.badge variant="{{ $transaction->status->badge() }}" size="sm">{{ $transaction->status->label() }}</x-jr.badge>
                    </td>
                    <td class="px-4 py-3 text-right text-sm text-mono-900">
                        {{ $transaction->balance_after !== null ? 'R$ ' . number_format($transaction->balance_after, 2, ',', '.') : '—' }}
                    </td>
                </tr>
            @endforeach
        </x-jr.table>
        <div class="mt-4">{{ $transactions->links() }}</div>
    @endif
</div>
