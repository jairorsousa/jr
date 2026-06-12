<div>
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between mb-6">
        <div class="flex items-center gap-2">
            <button wire:click="previousMonth" class="p-2 rounded-xl text-mono-600 hover:bg-mono-100 transition-colors" title="Mes anterior">
                <span class="material-icons-outlined text-[22px]">chevron_left</span>
            </button>
            <div class="text-center min-w-[180px]">
                <h2 class="text-lg font-bold text-mono-900">{{ $monthLabel }}</h2>
                @unless($isCurrentMonth)
                    <button wire:click="goToCurrentMonth" class="text-xs text-primary-500 hover:underline font-medium">Voltar ao mes atual</button>
                @endunless
            </div>
            <button wire:click="nextMonth" class="p-2 rounded-xl text-mono-600 hover:bg-mono-100 transition-colors" title="Proximo mes">
                <span class="material-icons-outlined text-[22px]">chevron_right</span>
            </button>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <x-jr.button variant="mono" href="{{ route('crypto.accounts') }}" size="sm">
                <span class="material-icons-outlined text-[16px]">account_balance_wallet</span>
                Contas
            </x-jr.button>
            <x-jr.button href="{{ route('crypto.transactions') }}" size="sm">
                <span class="material-icons-outlined text-[16px]">swap_vert</span>
                Nova transacao
            </x-jr.button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <x-jr.card>
            <p class="text-xs text-mono-600">Saldo total em cripto</p>
            <p class="text-xl font-bold text-mono-900 mt-1">R$ {{ number_format($totalBalance, 2, ',', '.') }}</p>
        </x-jr.card>
        <x-jr.card>
            <p class="text-xs text-mono-600">Entradas do periodo</p>
            <p class="text-xl font-bold text-up mt-1">R$ {{ number_format($inTotal, 2, ',', '.') }}</p>
        </x-jr.card>
        <x-jr.card>
            <p class="text-xs text-mono-600">Saidas do periodo</p>
            <p class="text-xl font-bold text-down mt-1">R$ {{ number_format($outTotal, 2, ',', '.') }}</p>
        </x-jr.card>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <x-jr.card class="xl:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-mono-900">Contas e carteiras</h3>
                <a href="{{ route('crypto.accounts') }}" class="text-xs font-semibold text-primary-500 hover:underline">Ver todas</a>
            </div>
            @forelse($accounts as $account)
                <div class="flex items-center justify-between py-3 border-t border-mono-100 first:border-t-0 first:pt-0">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-mono-900 truncate">{{ $account->name }}</p>
                        <p class="text-xs text-mono-600 truncate">{{ $account->institution?->name }} @if($account->betUser) · {{ $account->betUser->name }} @endif</p>
                    </div>
                    <p class="text-sm font-bold text-mono-900 whitespace-nowrap">R$ {{ number_format($account->current_balance_brl, 2, ',', '.') }}</p>
                </div>
            @empty
                <div class="text-center py-8">
                    <span class="material-icons-outlined text-[48px] text-mono-200">account_balance_wallet</span>
                    <p class="text-mono-600 mt-2">Nenhuma conta cripto cadastrada.</p>
                </div>
            @endforelse
        </x-jr.card>

        <div class="space-y-6">
            <x-jr.card>
                <h3 class="text-sm font-bold text-mono-900 mb-4">Por instituicao</h3>
                @forelse($institutionBalances as $row)
                    <div class="flex items-center justify-between py-2 border-t border-mono-100 first:border-t-0 first:pt-0">
                        <p class="text-sm text-mono-700 truncate">{{ $row->institution?->name ?? 'Sem instituicao' }}</p>
                        <p class="text-sm font-bold text-mono-900">R$ {{ number_format($row->total_balance, 2, ',', '.') }}</p>
                    </div>
                @empty
                    <p class="text-sm text-mono-600">Sem saldos ativos.</p>
                @endforelse
            </x-jr.card>

            <x-jr.card>
                <h3 class="text-sm font-bold text-mono-900 mb-4">Moedas movimentadas</h3>
                @forelse($assetBalances as $row)
                    <div class="flex items-center justify-between py-2 border-t border-mono-100 first:border-t-0 first:pt-0">
                        <p class="text-sm text-mono-700 truncate">{{ $row->asset?->symbol ?? 'Moeda' }}</p>
                        <p class="text-sm font-bold text-mono-900">{{ number_format($row->crypto_balance, 8, ',', '.') }}</p>
                    </div>
                @empty
                    <p class="text-sm text-mono-600">Sem movimentacoes por moeda.</p>
                @endforelse
            </x-jr.card>
        </div>
    </div>

    <x-jr.card class="mt-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-mono-900">Ultimas transacoes cripto</h3>
            <a href="{{ route('crypto.transactions') }}" class="text-xs font-semibold text-primary-500 hover:underline">Ver extrato</a>
        </div>
        @forelse($recentTransactions as $transaction)
            <div class="flex items-center justify-between py-3 border-t border-mono-100 first:border-t-0 first:pt-0">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-mono-900 truncate">{{ $transaction->description }}</p>
                    <p class="text-xs text-mono-600 truncate">
                        {{ $transaction->occurred_at->format('d/m/Y H:i') }} · {{ $transaction->cryptoAccount?->name }}
                        @if($transaction->asset) · {{ $transaction->asset->symbol }} @endif
                    </p>
                </div>
                <div class="text-right">
                    <x-jr.badge variant="{{ $transaction->type->badge() }}" size="sm">{{ $transaction->type->label() }}</x-jr.badge>
                    <p class="text-sm font-bold {{ $transaction->type->isIn() ? 'text-up' : ($transaction->type->isOut() ? 'text-down' : 'text-mono-900') }} mt-1">
                        {{ $transaction->type->isIn() ? '+' : ($transaction->type->isOut() ? '-' : '') }} R$ {{ number_format($transaction->amount_brl, 2, ',', '.') }}
                    </p>
                </div>
            </div>
        @empty
            <p class="text-sm text-mono-600">Nenhuma transacao cripto registrada.</p>
        @endforelse
    </x-jr.card>
</div>
