<div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-bold text-mono-900">Comparacao Financeira</h2>
            <p class="text-xs text-mono-500">Compare periodos, contas e categorias lado a lado</p>
        </div>

        <!-- Quick Presets -->
        <div class="flex items-center gap-2">
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                        class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-medium text-mono-600 hover:bg-mono-100 transition-colors">
                    <span class="material-icons-outlined text-[18px]">bolt</span>
                    Atalhos
                </button>
                <div x-show="open" x-cloak @click.away="open = false"
                     class="absolute right-0 top-11 bg-mono-white rounded-xl shadow-dropdown border border-mono-100 py-1.5 z-50 w-56">
                    <button wire:click="presetLastTwoMonths" @click="open = false"
                            class="flex items-center gap-2 w-full px-3 py-2 text-sm text-mono-900 hover:bg-mono-50">
                        <span class="material-icons-outlined text-[16px] text-mono-300">calendar_month</span>
                        Mes anterior vs Atual
                    </button>
                    <button wire:click="presetSameMonthLastYear" @click="open = false"
                            class="flex items-center gap-2 w-full px-3 py-2 text-sm text-mono-900 hover:bg-mono-50">
                        <span class="material-icons-outlined text-[16px] text-mono-300">event_repeat</span>
                        Mesmo mes ano passado
                    </button>
                    <button wire:click="presetQuarterVsQuarter" @click="open = false"
                            class="flex items-center gap-2 w-full px-3 py-2 text-sm text-mono-900 hover:bg-mono-50">
                        <span class="material-icons-outlined text-[16px] text-mono-300">date_range</span>
                        Trimestre vs Trimestre
                    </button>
                    <button wire:click="presetYearVsYear" @click="open = false"
                            class="flex items-center gap-2 w-full px-3 py-2 text-sm text-mono-900 hover:bg-mono-50">
                        <span class="material-icons-outlined text-[16px] text-mono-300">compare_arrows</span>
                        Ano anterior vs Atual
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- Two Cards Side by Side -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- ── CARD A ─────────────────────────────────────────────── --}}
        <x-jr.card class="border-2 border-info-200">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-info-100 flex items-center justify-center">
                        <span class="text-sm font-bold text-info-600">A</span>
                    </div>
                    <h3 class="text-sm font-bold text-mono-900">Periodo A</h3>
                </div>
                <button wire:click="copySyncFilters('a_to_b')"
                        class="flex items-center gap-1 text-[10px] text-mono-400 hover:text-primary-500 transition-colors" title="Copiar filtros para B">
                    <span class="material-icons-outlined text-[14px]">content_copy</span>
                    Copiar filtros p/ B
                </button>
            </div>

            <!-- Month nav -->
            <div class="flex items-center justify-center gap-2 mb-4">
                <button wire:click="aPreviousMonth" class="p-1.5 rounded-lg text-mono-400 hover:bg-mono-100 transition-colors">
                    <span class="material-icons-outlined text-[20px]">chevron_left</span>
                </button>
                <div class="text-center min-w-[160px]">
                    @if($aCustomRange)
                        <p class="text-sm font-semibold text-mono-900">Personalizado</p>
                        <p class="text-[10px] text-mono-400">{{ $labelA }}</p>
                    @else
                        <p class="text-sm font-semibold text-mono-900">{{ $labelA }}</p>
                    @endif
                </div>
                <button wire:click="aNextMonth" class="p-1.5 rounded-lg text-mono-400 hover:bg-mono-100 transition-colors">
                    <span class="material-icons-outlined text-[20px]">chevron_right</span>
                </button>
            </div>

            <!-- Custom date range -->
            <div x-data="{ showRange: false }" class="mb-4">
                <button @click="showRange = !showRange"
                        class="flex items-center gap-1 mx-auto text-[10px] font-medium transition-colors
                               {{ $aCustomRange ? 'text-primary-500' : 'text-mono-400 hover:text-mono-600' }}">
                    <span class="material-icons-outlined text-[14px]">date_range</span>
                    {{ $aCustomRange ? 'Editando periodo' : 'Periodo personalizado' }}
                </button>
                <div x-show="showRange" x-cloak class="mt-2 space-y-2">
                    <div class="grid grid-cols-2 gap-2">
                        <input type="date" wire:model="aDateFrom" class="h-9 px-2 rounded-lg border border-mono-200 text-xs text-mono-900 focus:border-primary-500 focus:ring-0">
                        <input type="date" wire:model="aDateTo" class="h-9 px-2 rounded-lg border border-mono-200 text-xs text-mono-900 focus:border-primary-500 focus:ring-0">
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="aApplyRange" class="flex-1 h-8 rounded-lg bg-primary-500 text-white text-xs font-semibold hover:bg-primary-600 transition-colors">Aplicar</button>
                        @if($aCustomRange)
                            <button wire:click="aClearRange" class="h-8 px-3 rounded-lg bg-mono-100 text-mono-600 text-xs font-semibold hover:bg-mono-200 transition-colors">Limpar</button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="space-y-2 mb-5">
                <select wire:model.live="aAccount" class="w-full h-9 px-3 rounded-lg border border-mono-200 bg-mono-white text-xs text-mono-900 focus:border-primary-500 focus:ring-0">
                    <option value="">Todas as contas</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="aCategory" class="w-full h-9 px-3 rounded-lg border border-mono-200 bg-mono-white text-xs text-mono-900 focus:border-primary-500 focus:ring-0">
                    <option value="">Todas as categorias</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="aType" class="w-full h-9 px-3 rounded-lg border border-mono-200 bg-mono-white text-xs text-mono-900 focus:border-primary-500 focus:ring-0">
                    <option value="">Receitas e Despesas</option>
                    <option value="income">Somente Receitas</option>
                    <option value="expense">Somente Despesas</option>
                </select>
            </div>

            <!-- Stats -->
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 rounded-xl bg-success/5">
                    <div class="flex items-center gap-2">
                        <span class="material-icons-outlined text-[18px] text-success">trending_up</span>
                        <span class="text-xs font-medium text-mono-700">Receitas</span>
                    </div>
                    <span class="text-sm font-bold text-success">R$ {{ number_format($statsA['income'], 2, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-xl bg-down-bg/50">
                    <div class="flex items-center gap-2">
                        <span class="material-icons-outlined text-[18px] text-error">trending_down</span>
                        <span class="text-xs font-medium text-mono-700">Despesas</span>
                    </div>
                    <span class="text-sm font-bold text-error">R$ {{ number_format($statsA['expense'], 2, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-xl bg-mono-50">
                    <div class="flex items-center gap-2">
                        <span class="material-icons-outlined text-[18px] text-mono-600">account_balance</span>
                        <span class="text-xs font-medium text-mono-700">Saldo</span>
                    </div>
                    <span class="text-sm font-bold {{ $statsA['balance'] >= 0 ? 'text-success' : 'text-error' }}">
                        R$ {{ number_format($statsA['balance'], 2, ',', '.') }}
                    </span>
                </div>
                <div class="flex items-center justify-between px-3">
                    <span class="text-[10px] text-mono-400">{{ $statsA['count'] }} transacao(es)</span>
                    <button wire:click="toggleTransactions('a')"
                            class="text-[10px] font-medium text-primary-500 hover:underline">
                        {{ $showTransactions && $visibleTransactions === 'a' ? 'Ocultar' : 'Ver transacoes' }}
                    </button>
                </div>
            </div>

            <!-- Top categories -->
            @if($statsA['topCategories']->isNotEmpty())
                <div class="mt-4 pt-4 border-t border-mono-100">
                    <p class="text-[10px] font-semibold text-mono-400 uppercase tracking-wider mb-2">Top Categorias</p>
                    @php $maxA = $statsA['topCategories']->max('total') ?: 1; @endphp
                    <div class="space-y-2">
                        @foreach($statsA['topCategories'] as $tc)
                            <div>
                                <div class="flex items-center justify-between mb-0.5">
                                    <span class="text-xs text-mono-700 truncate">{{ $tc['name'] }}</span>
                                    <span class="text-xs font-semibold text-mono-900">R$ {{ number_format($tc['total'], 2, ',', '.') }}</span>
                                </div>
                                <div class="w-full h-1.5 bg-mono-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full" style="width: {{ round($tc['total'] / $maxA * 100) }}%; background-color: {{ $tc['color'] }}"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </x-jr.card>

        {{-- ── CARD B ─────────────────────────────────────────────── --}}
        <x-jr.card class="border-2 border-primary-200">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-primary-100 flex items-center justify-center">
                        <span class="text-sm font-bold text-primary-600">B</span>
                    </div>
                    <h3 class="text-sm font-bold text-mono-900">Periodo B</h3>
                </div>
                <button wire:click="copySyncFilters('b_to_a')"
                        class="flex items-center gap-1 text-[10px] text-mono-400 hover:text-primary-500 transition-colors" title="Copiar filtros para A">
                    <span class="material-icons-outlined text-[14px]">content_copy</span>
                    Copiar filtros p/ A
                </button>
            </div>

            <!-- Month nav -->
            <div class="flex items-center justify-center gap-2 mb-4">
                <button wire:click="bPreviousMonth" class="p-1.5 rounded-lg text-mono-400 hover:bg-mono-100 transition-colors">
                    <span class="material-icons-outlined text-[20px]">chevron_left</span>
                </button>
                <div class="text-center min-w-[160px]">
                    @if($bCustomRange)
                        <p class="text-sm font-semibold text-mono-900">Personalizado</p>
                        <p class="text-[10px] text-mono-400">{{ $labelB }}</p>
                    @else
                        <p class="text-sm font-semibold text-mono-900">{{ $labelB }}</p>
                    @endif
                </div>
                <button wire:click="bNextMonth" class="p-1.5 rounded-lg text-mono-400 hover:bg-mono-100 transition-colors">
                    <span class="material-icons-outlined text-[20px]">chevron_right</span>
                </button>
            </div>

            <!-- Custom date range -->
            <div x-data="{ showRange: false }" class="mb-4">
                <button @click="showRange = !showRange"
                        class="flex items-center gap-1 mx-auto text-[10px] font-medium transition-colors
                               {{ $bCustomRange ? 'text-primary-500' : 'text-mono-400 hover:text-mono-600' }}">
                    <span class="material-icons-outlined text-[14px]">date_range</span>
                    {{ $bCustomRange ? 'Editando periodo' : 'Periodo personalizado' }}
                </button>
                <div x-show="showRange" x-cloak class="mt-2 space-y-2">
                    <div class="grid grid-cols-2 gap-2">
                        <input type="date" wire:model="bDateFrom" class="h-9 px-2 rounded-lg border border-mono-200 text-xs text-mono-900 focus:border-primary-500 focus:ring-0">
                        <input type="date" wire:model="bDateTo" class="h-9 px-2 rounded-lg border border-mono-200 text-xs text-mono-900 focus:border-primary-500 focus:ring-0">
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="bApplyRange" class="flex-1 h-8 rounded-lg bg-primary-500 text-white text-xs font-semibold hover:bg-primary-600 transition-colors">Aplicar</button>
                        @if($bCustomRange)
                            <button wire:click="bClearRange" class="h-8 px-3 rounded-lg bg-mono-100 text-mono-600 text-xs font-semibold hover:bg-mono-200 transition-colors">Limpar</button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="space-y-2 mb-5">
                <select wire:model.live="bAccount" class="w-full h-9 px-3 rounded-lg border border-mono-200 bg-mono-white text-xs text-mono-900 focus:border-primary-500 focus:ring-0">
                    <option value="">Todas as contas</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="bCategory" class="w-full h-9 px-3 rounded-lg border border-mono-200 bg-mono-white text-xs text-mono-900 focus:border-primary-500 focus:ring-0">
                    <option value="">Todas as categorias</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="bType" class="w-full h-9 px-3 rounded-lg border border-mono-200 bg-mono-white text-xs text-mono-900 focus:border-primary-500 focus:ring-0">
                    <option value="">Receitas e Despesas</option>
                    <option value="income">Somente Receitas</option>
                    <option value="expense">Somente Despesas</option>
                </select>
            </div>

            <!-- Stats -->
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 rounded-xl bg-success/5">
                    <div class="flex items-center gap-2">
                        <span class="material-icons-outlined text-[18px] text-success">trending_up</span>
                        <span class="text-xs font-medium text-mono-700">Receitas</span>
                    </div>
                    <span class="text-sm font-bold text-success">R$ {{ number_format($statsB['income'], 2, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-xl bg-down-bg/50">
                    <div class="flex items-center gap-2">
                        <span class="material-icons-outlined text-[18px] text-error">trending_down</span>
                        <span class="text-xs font-medium text-mono-700">Despesas</span>
                    </div>
                    <span class="text-sm font-bold text-error">R$ {{ number_format($statsB['expense'], 2, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-xl bg-mono-50">
                    <div class="flex items-center gap-2">
                        <span class="material-icons-outlined text-[18px] text-mono-600">account_balance</span>
                        <span class="text-xs font-medium text-mono-700">Saldo</span>
                    </div>
                    <span class="text-sm font-bold {{ $statsB['balance'] >= 0 ? 'text-success' : 'text-error' }}">
                        R$ {{ number_format($statsB['balance'], 2, ',', '.') }}
                    </span>
                </div>
                <div class="flex items-center justify-between px-3">
                    <span class="text-[10px] text-mono-400">{{ $statsB['count'] }} transacao(es)</span>
                    <button wire:click="toggleTransactions('b')"
                            class="text-[10px] font-medium text-primary-500 hover:underline">
                        {{ $showTransactions && $visibleTransactions === 'b' ? 'Ocultar' : 'Ver transacoes' }}
                    </button>
                </div>
            </div>

            <!-- Top categories -->
            @if($statsB['topCategories']->isNotEmpty())
                <div class="mt-4 pt-4 border-t border-mono-100">
                    <p class="text-[10px] font-semibold text-mono-400 uppercase tracking-wider mb-2">Top Categorias</p>
                    @php $maxB = $statsB['topCategories']->max('total') ?: 1; @endphp
                    <div class="space-y-2">
                        @foreach($statsB['topCategories'] as $tc)
                            <div>
                                <div class="flex items-center justify-between mb-0.5">
                                    <span class="text-xs text-mono-700 truncate">{{ $tc['name'] }}</span>
                                    <span class="text-xs font-semibold text-mono-900">R$ {{ number_format($tc['total'], 2, ',', '.') }}</span>
                                </div>
                                <div class="w-full h-1.5 bg-mono-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full" style="width: {{ round($tc['total'] / $maxB * 100) }}%; background-color: {{ $tc['color'] }}"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </x-jr.card>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- Comparison Summary -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <x-jr.card class="mb-6">
        <div class="flex items-center gap-2 mb-4">
            <span class="material-icons-outlined text-[20px] text-primary-500">compare_arrows</span>
            <h3 class="text-sm font-bold text-mono-900">Resultado da Comparacao</h3>
            <span class="text-xs text-mono-400">(B em relacao a A)</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Receitas diff --}}
            <div class="p-4 rounded-xl bg-mono-50">
                <p class="text-[10px] font-semibold text-mono-400 uppercase tracking-wider mb-2">Receitas</p>
                <div class="flex items-end gap-3">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            @if($diff['income'] > 0)
                                <span class="material-icons-outlined text-[20px] text-success">arrow_upward</span>
                                <span class="text-lg font-bold text-success">+R$ {{ number_format($diff['income'], 2, ',', '.') }}</span>
                            @elseif($diff['income'] < 0)
                                <span class="material-icons-outlined text-[20px] text-error">arrow_downward</span>
                                <span class="text-lg font-bold text-error">-R$ {{ number_format(abs($diff['income']), 2, ',', '.') }}</span>
                            @else
                                <span class="material-icons-outlined text-[20px] text-mono-400">remove</span>
                                <span class="text-lg font-bold text-mono-400">R$ 0,00</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-3 text-xs text-mono-500">
                            <span>A: R$ {{ number_format($statsA['income'], 2, ',', '.') }}</span>
                            <span class="material-icons-outlined text-[14px]">arrow_forward</span>
                            <span>B: R$ {{ number_format($statsB['income'], 2, ',', '.') }}</span>
                        </div>
                    </div>
                    @if($diff['income_pct'] !== null)
                        <div @class([
                            'px-2 py-1 rounded-lg text-xs font-bold',
                            'bg-success/10 text-success' => $diff['income_pct'] > 0,
                            'bg-down-bg text-error' => $diff['income_pct'] < 0,
                            'bg-mono-100 text-mono-400' => $diff['income_pct'] == 0,
                        ])>
                            {{ $diff['income_pct'] > 0 ? '+' : '' }}{{ $diff['income_pct'] }}%
                        </div>
                    @endif
                </div>
            </div>

            {{-- Despesas diff --}}
            <div class="p-4 rounded-xl bg-mono-50">
                <p class="text-[10px] font-semibold text-mono-400 uppercase tracking-wider mb-2">Despesas</p>
                <div class="flex items-end gap-3">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            @if($diff['expense'] > 0)
                                <span class="material-icons-outlined text-[20px] text-error">arrow_upward</span>
                                <span class="text-lg font-bold text-error">+R$ {{ number_format($diff['expense'], 2, ',', '.') }}</span>
                            @elseif($diff['expense'] < 0)
                                <span class="material-icons-outlined text-[20px] text-success">arrow_downward</span>
                                <span class="text-lg font-bold text-success">-R$ {{ number_format(abs($diff['expense']), 2, ',', '.') }}</span>
                            @else
                                <span class="material-icons-outlined text-[20px] text-mono-400">remove</span>
                                <span class="text-lg font-bold text-mono-400">R$ 0,00</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-3 text-xs text-mono-500">
                            <span>A: R$ {{ number_format($statsA['expense'], 2, ',', '.') }}</span>
                            <span class="material-icons-outlined text-[14px]">arrow_forward</span>
                            <span>B: R$ {{ number_format($statsB['expense'], 2, ',', '.') }}</span>
                        </div>
                    </div>
                    @if($diff['expense_pct'] !== null)
                        <div @class([
                            'px-2 py-1 rounded-lg text-xs font-bold',
                            'bg-down-bg text-error' => $diff['expense_pct'] > 0,
                            'bg-success/10 text-success' => $diff['expense_pct'] < 0,
                            'bg-mono-100 text-mono-400' => $diff['expense_pct'] == 0,
                        ])>
                            {{ $diff['expense_pct'] > 0 ? '+' : '' }}{{ $diff['expense_pct'] }}%
                        </div>
                    @endif
                </div>
            </div>

            {{-- Saldo diff --}}
            <div class="p-4 rounded-xl bg-mono-50">
                <p class="text-[10px] font-semibold text-mono-400 uppercase tracking-wider mb-2">Saldo</p>
                <div class="flex items-end gap-3">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            @if($diff['balance'] > 0)
                                <span class="material-icons-outlined text-[20px] text-success">arrow_upward</span>
                                <span class="text-lg font-bold text-success">+R$ {{ number_format($diff['balance'], 2, ',', '.') }}</span>
                            @elseif($diff['balance'] < 0)
                                <span class="material-icons-outlined text-[20px] text-error">arrow_downward</span>
                                <span class="text-lg font-bold text-error">-R$ {{ number_format(abs($diff['balance']), 2, ',', '.') }}</span>
                            @else
                                <span class="material-icons-outlined text-[20px] text-mono-400">remove</span>
                                <span class="text-lg font-bold text-mono-400">R$ 0,00</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-3 text-xs text-mono-500">
                            <span>A: R$ {{ number_format($statsA['balance'], 2, ',', '.') }}</span>
                            <span class="material-icons-outlined text-[14px]">arrow_forward</span>
                            <span>B: R$ {{ number_format($statsB['balance'], 2, ',', '.') }}</span>
                        </div>
                    </div>
                    @if($diff['balance_pct'] !== null)
                        <div @class([
                            'px-2 py-1 rounded-lg text-xs font-bold',
                            'bg-success/10 text-success' => $diff['balance_pct'] > 0,
                            'bg-down-bg text-error' => $diff['balance_pct'] < 0,
                            'bg-mono-100 text-mono-400' => $diff['balance_pct'] == 0,
                        ])>
                            {{ $diff['balance_pct'] > 0 ? '+' : '' }}{{ $diff['balance_pct'] }}%
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Visual bar comparison -->
        @if($statsA['expense'] > 0 || $statsB['expense'] > 0)
            <div class="mt-6 pt-4 border-t border-mono-100">
                <p class="text-[10px] font-semibold text-mono-400 uppercase tracking-wider mb-3">Comparacao Visual</p>
                @php
                    $maxVal = max($statsA['income'], $statsA['expense'], $statsB['income'], $statsB['expense'], 1);
                @endphp
                <div class="space-y-3">
                    <!-- Receitas -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs text-mono-600">Receitas</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] text-info-600 font-semibold w-5">A</span>
                            <div class="flex-1 h-5 bg-mono-100 rounded-full overflow-hidden">
                                <div class="h-full bg-info-400 rounded-full transition-all duration-500 flex items-center justify-end pr-2"
                                     style="width: {{ round($statsA['income'] / $maxVal * 100) }}%">
                                    @if($statsA['income'] > 0)
                                        <span class="text-[9px] font-bold text-white whitespace-nowrap">R$ {{ number_format($statsA['income'], 0, ',', '.') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-[10px] text-primary-600 font-semibold w-5">B</span>
                            <div class="flex-1 h-5 bg-mono-100 rounded-full overflow-hidden">
                                <div class="h-full bg-primary-400 rounded-full transition-all duration-500 flex items-center justify-end pr-2"
                                     style="width: {{ round($statsB['income'] / $maxVal * 100) }}%">
                                    @if($statsB['income'] > 0)
                                        <span class="text-[9px] font-bold text-white whitespace-nowrap">R$ {{ number_format($statsB['income'], 0, ',', '.') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Despesas -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs text-mono-600">Despesas</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] text-info-600 font-semibold w-5">A</span>
                            <div class="flex-1 h-5 bg-mono-100 rounded-full overflow-hidden">
                                <div class="h-full bg-info-400 rounded-full transition-all duration-500 flex items-center justify-end pr-2"
                                     style="width: {{ round($statsA['expense'] / $maxVal * 100) }}%">
                                    @if($statsA['expense'] > 0)
                                        <span class="text-[9px] font-bold text-white whitespace-nowrap">R$ {{ number_format($statsA['expense'], 0, ',', '.') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-[10px] text-primary-600 font-semibold w-5">B</span>
                            <div class="flex-1 h-5 bg-mono-100 rounded-full overflow-hidden">
                                <div class="h-full bg-primary-400 rounded-full transition-all duration-500 flex items-center justify-end pr-2"
                                     style="width: {{ round($statsB['expense'] / $maxVal * 100) }}%">
                                    @if($statsB['expense'] > 0)
                                        <span class="text-[9px] font-bold text-white whitespace-nowrap">R$ {{ number_format($statsB['expense'], 0, ',', '.') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </x-jr.card>

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- Transactions List (toggled) -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    @if($showTransactions)
        <x-jr.card>
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center
                                {{ $visibleTransactions === 'a' ? 'bg-info-100' : 'bg-primary-100' }}">
                        <span class="text-xs font-bold {{ $visibleTransactions === 'a' ? 'text-info-600' : 'text-primary-600' }}">
                            {{ strtoupper($visibleTransactions) }}
                        </span>
                    </div>
                    <h3 class="text-sm font-semibold text-mono-900">
                        Transacoes — {{ $visibleTransactions === 'a' ? $labelA : $labelB }}
                    </h3>
                </div>
                <button wire:click="$set('showTransactions', false)" class="p-1 rounded-lg text-mono-300 hover:text-mono-600 transition-colors">
                    <span class="material-icons-outlined text-[18px]">close</span>
                </button>
            </div>

            @php $txList = $visibleTransactions === 'a' ? $transactionsA : $transactionsB; @endphp

            @if($txList->isEmpty())
                <div class="text-center py-6">
                    <span class="material-icons-outlined text-[32px] text-mono-200">receipt_long</span>
                    <p class="text-xs text-mono-400 mt-1">Nenhuma transacao neste periodo.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-mono-100">
                                <th class="text-left px-3 py-2 text-[10px] font-semibold text-mono-500 uppercase">Data</th>
                                <th class="text-left px-3 py-2 text-[10px] font-semibold text-mono-500 uppercase">Descricao</th>
                                <th class="text-left px-3 py-2 text-[10px] font-semibold text-mono-500 uppercase">Categoria</th>
                                <th class="text-left px-3 py-2 text-[10px] font-semibold text-mono-500 uppercase">Conta</th>
                                <th class="text-right px-3 py-2 text-[10px] font-semibold text-mono-500 uppercase">Valor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-mono-50">
                            @foreach($txList as $tx)
                                <tr class="hover:bg-mono-50">
                                    <td class="px-3 py-2.5 text-xs text-mono-600 whitespace-nowrap">{{ $tx->date->format('d/m/Y') }}</td>
                                    <td class="px-3 py-2.5 text-xs text-mono-900 font-medium">{{ $tx->description }}</td>
                                    <td class="px-3 py-2.5">
                                        @if($tx->category)
                                            <span class="inline-flex items-center gap-1 text-xs text-mono-600">
                                                <span class="w-2 h-2 rounded-full flex-shrink-0" style="background-color: {{ $tx->category->color ?? '#94a3b8' }}"></span>
                                                {{ $tx->category->name }}
                                            </span>
                                        @else
                                            <span class="text-xs text-mono-300">--</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2.5 text-xs text-mono-600">{{ $tx->account?->name ?? '--' }}</td>
                                    <td class="px-3 py-2.5 text-right whitespace-nowrap">
                                        <span class="text-xs font-semibold {{ $tx->type === \App\Enums\TransactionType::Income ? 'text-success' : 'text-error' }}">
                                            {{ $tx->type === \App\Enums\TransactionType::Income ? '+' : '-' }}R$ {{ number_format($tx->amount, 2, ',', '.') }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($txList->count() >= 50)
                    <p class="text-[10px] text-mono-400 text-center mt-3">Exibindo as 50 primeiras transacoes.</p>
                @endif
            @endif
        </x-jr.card>
    @endif
</div>
