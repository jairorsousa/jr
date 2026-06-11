<div>
    @if (session('success'))
        <div class="mb-4"><x-jr.alert variant="success">{{ session('success') }}</x-jr.alert></div>
    @endif

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
        <div class="flex items-center gap-2">
            <button wire:click="previousMonth" class="p-2 rounded-xl text-mono-600 hover:bg-mono-100 transition-colors" title="Mes anterior">
                <span class="material-icons-outlined text-[22px]">chevron_left</span>
            </button>
            <div class="min-w-[180px] text-center">
                <h2 class="text-lg font-bold text-mono-900">{{ $monthLabel }}</h2>
                @unless($isCurrentMonth)
                    <button wire:click="goToCurrentMonth" class="text-xs text-primary-500 hover:underline font-medium">Voltar ao mes atual</button>
                @endunless
            </div>
            <button wire:click="nextMonth" class="p-2 rounded-xl text-mono-600 hover:bg-mono-100 transition-colors" title="Proximo mes">
                <span class="material-icons-outlined text-[22px]">chevron_right</span>
            </button>
        </div>

        <div class="flex items-center gap-2">
            <x-jr.button variant="mono" href="{{ route('bets.accounts') }}" size="sm">
                <span class="material-icons-outlined text-[16px]">account_balance_wallet</span>
                Contas
            </x-jr.button>
            <x-jr.button href="{{ route('bets.transactions') }}" size="sm">
                <span class="material-icons-outlined text-[16px]">add</span>
                Nova Transacao
            </x-jr.button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <x-jr.card>
            <p class="text-xs text-mono-600">Saldo total em bets</p>
            <p class="text-2xl font-bold text-mono-900 mt-1">R$ {{ number_format($totalBalance, 2, ',', '.') }}</p>
            <p class="text-xs text-mono-600 mt-2">{{ $activeAccounts }} contas ativas</p>
        </x-jr.card>

        <x-jr.card>
            <p class="text-xs text-mono-600">Resultado operacional</p>
            <p class="text-2xl font-bold {{ $periodTotals['profit'] >= 0 ? 'text-up' : 'text-down' }} mt-1">
                {{ $periodTotals['profit'] < 0 ? '-' : '' }}R$ {{ number_format(abs($periodTotals['profit']), 2, ',', '.') }}
            </p>
            <p class="text-xs text-mono-600 mt-2">ROI {{ number_format($periodTotals['roi'], 2, ',', '.') }}%</p>
        </x-jr.card>

        <x-jr.card>
            <p class="text-xs text-mono-600">Depositos / Saques</p>
            <div class="mt-1 flex items-baseline gap-2">
                <span class="text-xl font-bold text-down">R$ {{ number_format($periodTotals['deposits'], 2, ',', '.') }}</span>
                <span class="text-xs text-mono-300">/</span>
                <span class="text-xl font-bold text-up">R$ {{ number_format($periodTotals['withdrawals'], 2, ',', '.') }}</span>
            </div>
            <p class="text-xs text-mono-600 mt-2">Fluxo entre banco e casas</p>
        </x-jr.card>

        <x-jr.card>
            <p class="text-xs text-mono-600">Saques pendentes</p>
            <p class="text-2xl font-bold text-info mt-1">R$ {{ number_format($pendingWithdrawals, 2, ',', '.') }}</p>
            <p class="text-xs text-mono-600 mt-2">Ainda nao confirmados</p>
        </x-jr.card>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mb-6">
        <x-jr.card>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-mono-900">Saldo por casa</h3>
                <a href="{{ route('bets.houses') }}" class="text-xs font-semibold text-primary-500 hover:underline">Ver casas</a>
            </div>
            <div class="space-y-3">
                @forelse($balanceByHouse as $item)
                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-medium text-mono-900">{{ $item->bettingHouse?->name ?? 'Casa removida' }}</span>
                            <span class="font-bold text-mono-900">R$ {{ number_format($item->total_balance, 2, ',', '.') }}</span>
                        </div>
                        @php $percent = $totalBalance > 0 ? min(100, ($item->total_balance / $totalBalance) * 100) : 0; @endphp
                        <div class="h-2 bg-mono-100 rounded-pill mt-2 overflow-hidden">
                            <div class="h-full bg-primary-500 rounded-pill" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-mono-600">Nenhum saldo por casa ainda.</p>
                @endforelse
            </div>
        </x-jr.card>

        <x-jr.card>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-mono-900">Saldo por usuario</h3>
                <a href="{{ route('bets.users') }}" class="text-xs font-semibold text-primary-500 hover:underline">Ver usuarios</a>
            </div>
            <div class="space-y-3">
                @forelse($balanceByUser as $item)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center text-white text-xs font-bold" style="background-color: {{ $item->betUser?->color ?? '#ff6f00' }}">
                                {{ mb_substr($item->betUser?->name ?? '?', 0, 1) }}
                            </div>
                            <span class="text-sm font-medium text-mono-900">{{ $item->betUser?->name ?? 'Usuario removido' }}</span>
                        </div>
                        <span class="text-sm font-bold text-mono-900">R$ {{ number_format($item->total_balance, 2, ',', '.') }}</span>
                    </div>
                @empty
                    <p class="text-sm text-mono-600">Nenhum saldo por usuario ainda.</p>
                @endforelse
            </div>
        </x-jr.card>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
        <x-jr.card class="xl:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-mono-900">Top contas por saldo</h3>
                <a href="{{ route('bets.accounts') }}" class="text-xs font-semibold text-primary-500 hover:underline">Ver todas</a>
            </div>
            <div class="space-y-3">
                @forelse($topAccounts as $account)
                    <a href="{{ route('bets.accounts.show', $account->id) }}" class="flex items-center justify-between p-3 rounded-xl hover:bg-mono-50 transition-colors">
                        <div>
                            <p class="text-sm font-semibold text-mono-900">{{ $account->name }}</p>
                            <p class="text-xs text-mono-600">{{ $account->bettingHouse?->name }} · {{ $account->betUser?->name }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-mono-900">R$ {{ number_format($account->current_balance, 2, ',', '.') }}</p>
                            <x-jr.badge variant="{{ $account->status->badge() }}" size="sm">{{ $account->status->label() }}</x-jr.badge>
                        </div>
                    </a>
                @empty
                    <p class="text-sm text-mono-600">Nenhuma conta cadastrada.</p>
                @endforelse
            </div>
        </x-jr.card>

        <x-jr.card>
            <h3 class="text-sm font-bold text-mono-900 mb-4">Alertas</h3>
            <div class="space-y-3">
                @forelse($criticalAccounts as $account)
                    <div class="p-3 rounded-xl bg-mono-50">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm font-semibold text-mono-900 truncate">{{ $account->name }}</p>
                            <x-jr.badge variant="{{ $account->status->badge() }}" size="sm">{{ $account->status->label() }}</x-jr.badge>
                        </div>
                        <p class="text-xs text-mono-600 mt-1">{{ $account->bettingHouse?->name }} · R$ {{ number_format($account->current_balance, 2, ',', '.') }}</p>
                    </div>
                @empty
                    <p class="text-sm text-mono-600">Nenhuma conta com alerta.</p>
                @endforelse
            </div>
        </x-jr.card>
    </div>

    <x-jr.card class="mt-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-mono-900">Ultimas transacoes</h3>
            <a href="{{ route('bets.transactions') }}" class="text-xs font-semibold text-primary-500 hover:underline">Ver extrato</a>
        </div>
        <div class="space-y-2">
            @forelse($latestTransactions as $transaction)
                <div class="flex items-center justify-between py-2 border-b border-mono-100 last:border-0">
                    <div class="flex items-center gap-3">
                        <span class="material-icons-outlined text-[18px] {{ $transaction->type->isIn() ? 'text-up' : 'text-down' }}">{{ $transaction->type->icon() }}</span>
                        <div>
                            <p class="text-sm font-medium text-mono-900">{{ $transaction->description }}</p>
                            <p class="text-xs text-mono-600">{{ $transaction->betAccount?->bettingHouse?->name }} · {{ $transaction->occurred_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    <span class="text-sm font-bold {{ $transaction->type->isIn() ? 'text-up' : 'text-down' }}">
                        {{ $transaction->type->isIn() ? '+' : '-' }} R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                    </span>
                </div>
            @empty
                <p class="text-sm text-mono-600">Nenhuma transacao cadastrada.</p>
            @endforelse
        </div>
    </x-jr.card>
</div>
