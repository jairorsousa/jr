<div>
    @if (session('success'))
        <div class="mb-4"><x-jr.alert variant="success">{{ session('success') }}</x-jr.alert></div>
    @endif

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Balance -->
        <x-jr.card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-mono-600 font-medium uppercase tracking-wider">Saldo Total</p>
                    <p class="text-2xl font-bold text-mono-900 mt-1">
                        R$ {{ number_format($totalBalance, 2, ',', '.') }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-primary-100 flex items-center justify-center">
                    <span class="material-icons-outlined text-[22px] text-primary-500">account_balance_wallet</span>
                </div>
            </div>
        </x-jr.card>

        <!-- Monthly Income -->
        <x-jr.card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-mono-600 font-medium uppercase tracking-wider">Receitas do Mes</p>
                    <p class="text-2xl font-bold text-up mt-1">
                        R$ {{ number_format($monthlyIncome, 2, ',', '.') }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-up-bg flex items-center justify-center">
                    <span class="material-icons-outlined text-[22px] text-up">trending_up</span>
                </div>
            </div>
        </x-jr.card>

        <!-- Monthly Expense -->
        <x-jr.card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-mono-600 font-medium uppercase tracking-wider">Despesas do Mes</p>
                    <p class="text-2xl font-bold text-down mt-1">
                        R$ {{ number_format($monthlyExpense, 2, ',', '.') }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-down-bg flex items-center justify-center">
                    <span class="material-icons-outlined text-[22px] text-down">trending_down</span>
                </div>
            </div>
        </x-jr.card>

        <!-- Credit Card Invoice -->
        <x-jr.card>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-mono-600 font-medium uppercase tracking-wider">
                        Fatura {{ $mainCard?->name ?? 'Cartao' }}
                    </p>
                    <p class="text-2xl font-bold text-mono-900 mt-1">
                        R$ {{ number_format($currentInvoice, 2, ',', '.') }}
                    </p>
                    @if($invoiceStatus)
                        <x-jr.badge size="sm" variant="{{ $invoiceStatus === 'paga' ? 'success' : ($invoiceStatus === 'fechada' ? 'primary' : 'neutral') }}" class="mt-1">
                            {{ ucfirst($invoiceStatus) }}
                        </x-jr.badge>
                    @endif
                </div>
                <div class="w-10 h-10 rounded-xl bg-mono-100 flex items-center justify-center">
                    <span class="material-icons-outlined text-[22px] text-mono-600">credit_card</span>
                </div>
            </div>
        </x-jr.card>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <!-- Income vs Expense Chart (2 cols) -->
        <x-jr.card class="lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-mono-900">Receitas vs Despesas</h3>
                <span class="text-xs text-mono-300">Ultimos 6 meses</span>
            </div>
            <div class="h-64">
                <canvas id="incomeExpenseChart"
                        data-months='@json($chartData["months"])'
                        data-incomes='@json($chartData["incomes"])'
                        data-expenses='@json($chartData["expenses"])'
                ></canvas>
            </div>
        </x-jr.card>

        <!-- Patrimony Evolution (1 col) -->
        <x-jr.card>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-mono-900">Patrimonio</h3>
                <span class="text-xs text-mono-300">12 meses</span>
            </div>
            <div class="h-64">
                <canvas id="patrimonyChart"
                        data-months='@json($patrimonyData["months"])'
                        data-values='@json($patrimonyData["values"])'
                ></canvas>
            </div>
        </x-jr.card>
    </div>

    <!-- Bottom Row: Bills + Events + Tasks -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Upcoming Bills -->
        <x-jr.card :padding="false">
            <div class="px-5 py-4 border-b border-mono-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-mono-900">Proximas Contas</h3>
                    <a href="{{ route('financeiro.transacoes', ['filterStatus' => 'pending']) }}"
                       class="text-xs text-primary-500 hover:underline font-medium">Ver todas</a>
                </div>
            </div>
            @forelse($upcomingBills as $bill)
                @php
                    $isOverdue = $bill->due_date && $bill->due_date->isPast();
                @endphp
                <div class="flex items-center justify-between px-5 py-3 {{ !$loop->last ? 'border-b border-mono-100' : '' }} hover:bg-mono-50/50 transition-colors {{ $isOverdue ? 'bg-down-bg/30' : '' }}">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0
                                    {{ $isOverdue ? 'bg-down-bg' : 'bg-mono-50' }}">
                            <span class="material-icons-outlined text-[16px] {{ $isOverdue ? 'text-error' : 'text-mono-300' }}">
                                {{ $isOverdue ? 'warning' : 'receipt' }}
                            </span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-mono-900 truncate">{{ $bill->description }}</p>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-xs {{ $isOverdue ? 'text-error font-semibold' : 'text-mono-300' }}">
                                    {{ $bill->due_date?->format('d/m') }}
                                    @if($isOverdue) · Vencida @endif
                                </span>
                                @if($bill->category)
                                    <x-jr.badge size="sm" style="background-color: {{ $bill->category->color }}15; color: {{ $bill->category->color }}">
                                        {{ $bill->category->name }}
                                    </x-jr.badge>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span class="text-sm font-semibold text-down">
                            R$ {{ number_format($bill->amount, 2, ',', '.') }}
                        </span>
                        <button wire:click="markAsPaid('{{ $bill->id }}')"
                                class="p-1.5 rounded-lg text-mono-300 hover:text-up hover:bg-up-bg transition-colors"
                                title="Marcar como pago">
                            <span class="material-icons-outlined text-[16px]">check_circle</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="px-5 py-8 text-center">
                    <span class="material-icons-outlined text-[36px] text-mono-200">event_available</span>
                    <p class="text-sm text-mono-600 mt-2">Nenhuma conta proxima.</p>
                </div>
            @endforelse
        </x-jr.card>

        <!-- Upcoming Events -->
        <x-jr.card :padding="false">
            <div class="px-5 py-4 border-b border-mono-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-mono-900">Agenda</h3>
                    <a href="{{ route('agenda') }}" class="text-xs text-primary-500 hover:underline font-medium">Ver agenda</a>
                </div>
                <p class="text-xs text-mono-300 mt-0.5">Hoje e amanha</p>
            </div>
            @forelse($upcomingEvents as $event)
                <div class="flex items-center gap-3 px-5 py-3 {{ !$loop->last ? 'border-b border-mono-100' : '' }} hover:bg-mono-50/50 transition-colors">
                    <div class="w-1 h-8 rounded-full flex-shrink-0" style="background-color: {{ $event->color ?? '#ff6f00' }}"></div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-mono-900 truncate">{{ $event->title }}</p>
                        <p class="text-xs text-mono-300 mt-0.5">
                            @if($event->is_all_day)
                                Dia inteiro
                            @else
                                {{ $event->start_at->format('H:i') }}
                                @if($event->end_at) - {{ $event->end_at->format('H:i') }} @endif
                            @endif
                            · {{ $event->start_at->isToday() ? 'Hoje' : 'Amanha' }}
                        </p>
                    </div>
                    @if($event->location)
                        <span class="material-icons-outlined text-[14px] text-mono-300" title="{{ $event->location }}">location_on</span>
                    @endif
                </div>
            @empty
                <div class="px-5 py-8 text-center">
                    <span class="material-icons-outlined text-[36px] text-mono-200">calendar_today</span>
                    <p class="text-sm text-mono-600 mt-2">Nenhum compromisso proximo.</p>
                </div>
            @endforelse
        </x-jr.card>

        <!-- Pending Tasks -->
        <x-jr.card :padding="false">
            <div class="px-5 py-4 border-b border-mono-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-mono-900">Tarefas</h3>
                    <a href="{{ route('tarefas') }}" class="text-xs text-primary-500 hover:underline font-medium">Ver todas</a>
                </div>
                <p class="text-xs text-mono-300 mt-0.5">Top 5 por prioridade</p>
            </div>
            @forelse($pendingTasks as $task)
                @php $isOverdue = $task->isOverdue(); @endphp
                <div class="flex items-center gap-3 px-5 py-3 {{ !$loop->last ? 'border-b border-mono-100' : '' }} hover:bg-mono-50/50 transition-colors">
                    <div class="w-2 h-2 rounded-full flex-shrink-0 {{ match($task->priority->value) {
                        'urgent' => 'bg-error',
                        'high' => 'bg-primary-500',
                        'medium' => 'bg-info',
                        default => 'bg-mono-300',
                    } }}"></div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-mono-900 truncate {{ $isOverdue ? 'text-error' : '' }}">
                            {{ $task->title }}
                        </p>
                        <div class="flex items-center gap-2 mt-0.5">
                            <x-jr.badge size="sm" variant="{{ $task->priority->color() }}">{{ $task->priority->label() }}</x-jr.badge>
                            @if($task->due_date)
                                <span class="text-xs {{ $isOverdue ? 'text-error font-semibold' : 'text-mono-300' }}">
                                    {{ $task->due_date->format('d/m') }}
                                    @if($isOverdue) · Atrasada @endif
                                </span>
                            @endif
                        </div>
                    </div>
                    <x-jr.badge size="sm" variant="{{ $task->status->color() }}">{{ $task->status->label() }}</x-jr.badge>
                </div>
            @empty
                <div class="px-5 py-8 text-center">
                    <span class="material-icons-outlined text-[36px] text-mono-200">task_alt</span>
                    <p class="text-sm text-mono-600 mt-2">Nenhuma tarefa pendente.</p>
                </div>
            @endforelse
        </x-jr.card>
    </div>
</div>

@script
<script>
    // Income vs Expense Bar Chart
    const barCanvas = document.getElementById('incomeExpenseChart');
    if (barCanvas) {
        const barCtx = barCanvas.getContext('2d');
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: JSON.parse(barCanvas.dataset.months),
                datasets: [
                    {
                        label: 'Receitas',
                        data: JSON.parse(barCanvas.dataset.incomes),
                        backgroundColor: '#15a96f',
                        borderRadius: 6,
                        barPercentage: 0.7,
                        categoryPercentage: 0.6,
                    },
                    {
                        label: 'Despesas',
                        data: JSON.parse(barCanvas.dataset.expenses),
                        backgroundColor: '#e43b3b',
                        borderRadius: 6,
                        barPercentage: 0.7,
                        categoryPercentage: 0.6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 20,
                            font: { family: 'Reddit Sans', size: 12 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.dataset.label}: R$ ${ctx.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Reddit Sans', size: 11 }, color: '#8d959d' }
                    },
                    y: {
                        grid: { color: '#f5f6f7' },
                        ticks: {
                            font: { family: 'Reddit Sans', size: 11 },
                            color: '#8d959d',
                            callback: (v) => 'R$ ' + v.toLocaleString('pt-BR')
                        }
                    }
                }
            }
        });
    }

    // Patrimony Line Chart
    const lineCanvas = document.getElementById('patrimonyChart');
    if (lineCanvas) {
        const lineCtx = lineCanvas.getContext('2d');
        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: JSON.parse(lineCanvas.dataset.months),
                datasets: [{
                    label: 'Patrimonio',
                    data: JSON.parse(lineCanvas.dataset.values),
                    borderColor: '#ff6f00',
                    backgroundColor: 'rgba(255, 111, 0, 0.08)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 2,
                    pointHoverRadius: 5,
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `R$ ${ctx.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Reddit Sans', size: 10 }, color: '#8d959d', maxRotation: 45 }
                    },
                    y: {
                        grid: { color: '#f5f6f7' },
                        ticks: {
                            font: { family: 'Reddit Sans', size: 10 },
                            color: '#8d959d',
                            callback: (v) => 'R$ ' + (v / 1000).toFixed(0) + 'k'
                        }
                    }
                }
            }
        });
    }
</script>
@endscript
