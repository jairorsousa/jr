<div>
    <!-- Flash Messages -->
    @if (session('success'))
        <div class="mb-4"><x-jr.alert variant="success">{{ session('success') }}</x-jr.alert></div>
    @endif

    <!-- Header: Navigation + View Toggle + New Event -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-2">
            <button wire:click="previousMonth" class="p-2 rounded-xl text-mono-600 hover:bg-mono-100 transition-colors">
                <span class="material-icons-outlined text-[20px]">chevron_left</span>
            </button>
            <h2 class="text-lg font-bold text-mono-900 min-w-[180px] text-center">{{ $monthLabel }}</h2>
            <button wire:click="nextMonth" class="p-2 rounded-xl text-mono-600 hover:bg-mono-100 transition-colors">
                <span class="material-icons-outlined text-[20px]">chevron_right</span>
            </button>
            <button wire:click="goToToday" class="ml-2 text-xs font-semibold text-primary-500 hover:underline">Hoje</button>
        </div>

        <div class="flex items-center gap-2">
            <!-- View Toggle -->
            <div class="flex bg-mono-100 rounded-pill p-0.5">
                @foreach(['month' => 'Mes', 'week' => 'Semana', 'day' => 'Dia'] as $v => $label)
                    <button wire:click="setView('{{ $v }}')"
                            class="px-3 py-1.5 text-xs font-semibold rounded-pill transition-colors
                                   {{ $view === $v ? 'bg-mono-white text-mono-900 shadow-sm' : 'text-mono-600 hover:text-mono-900' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <x-jr.button wire:click="openCreateModal" size="sm">
                <span class="material-icons-outlined text-[16px]">add</span>
                Novo Evento
            </x-jr.button>
        </div>
    </div>

    @if($view === 'month')
        {{-- MONTHLY VIEW --}}
        <x-jr.card :padding="false">
            <!-- Weekday Headers -->
            <div class="grid grid-cols-7 border-b border-mono-100">
                @foreach(['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'] as $dayName)
                    <div class="px-2 py-2.5 text-center text-xs font-semibold text-mono-600 uppercase tracking-wider">
                        {{ $dayName }}
                    </div>
                @endforeach
            </div>

            <!-- Calendar Grid -->
            @foreach($weeks as $week)
                <div class="grid grid-cols-7 {{ !$loop->last ? 'border-b border-mono-100' : '' }}">
                    @foreach($week as $day)
                        <div wire:click="selectDate('{{ $day['date'] }}')"
                             class="min-h-[100px] p-1.5 border-r border-mono-100 last:border-r-0 cursor-pointer transition-colors
                                    {{ !$day['isCurrentMonth'] ? 'bg-mono-50/50' : 'hover:bg-mono-50/30' }}
                                    {{ $day['date'] === $selectedDate ? 'bg-primary-100/30' : '' }}">
                            <!-- Day Number -->
                            <div class="flex items-center justify-between mb-1">
                                <span class="inline-flex items-center justify-center w-7 h-7 text-xs font-semibold rounded-full
                                             {{ $day['isToday'] ? 'bg-primary-500 text-white' : ($day['isCurrentMonth'] ? 'text-mono-900' : 'text-mono-300') }}">
                                    {{ $day['day'] }}
                                </span>
                                @if(count($day['events']) > 3)
                                    <span class="text-[10px] text-mono-300 font-medium">+{{ count($day['events']) - 3 }}</span>
                                @endif
                            </div>

                            <!-- Events (max 3 visible) -->
                            <div class="space-y-0.5">
                                @foreach(array_slice($day['events'], 0, 3) as $ev)
                                    @if($ev['is_financial'])
                                        <a href="{{ route('financeiro.transacoes') }}"
                                           class="block truncate text-[10px] font-medium rounded px-1.5 py-0.5"
                                           style="background-color: {{ $ev['color'] }}15; color: {{ $ev['color'] }}">
                                            <span class="material-icons-outlined text-[10px] align-middle mr-0.5">attach_money</span>
                                            {{ Str::limit($ev['title'], 20) }}
                                        </a>
                                    @else
                                        <button wire:click.stop="showDetail('{{ $ev['id'] }}')"
                                                class="block w-full text-left truncate text-[10px] font-medium rounded px-1.5 py-0.5"
                                                style="background-color: {{ $ev['color'] }}20; color: {{ $ev['color'] }}">
                                            {{ Str::limit($ev['title'], 22) }}
                                        </button>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </x-jr.card>

    @elseif($view === 'week')
        {{-- WEEKLY VIEW --}}
        @php
            $sel = \Carbon\Carbon::parse($selectedDate);
            $weekStart = $sel->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
        @endphp
        <x-jr.card :padding="false">
            <div class="grid grid-cols-7 border-b border-mono-100">
                @for($d = 0; $d < 7; $d++)
                    @php
                        $dayDate = $weekStart->copy()->addDays($d);
                        $dayStr = $dayDate->format('Y-m-d');
                        $dayEvents = collect($weeks)->flatten(1)->firstWhere('date', $dayStr);
                    @endphp
                    <div class="border-r border-mono-100 last:border-r-0 min-h-[300px]">
                        <div class="px-2 py-2.5 text-center border-b border-mono-100 {{ $dayDate->isToday() ? 'bg-primary-100/50' : '' }}">
                            <p class="text-[10px] text-mono-300 uppercase font-semibold">{{ $dayDate->translatedFormat('D') }}</p>
                            <span class="inline-flex items-center justify-center w-8 h-8 text-sm font-bold rounded-full mt-0.5
                                         {{ $dayDate->isToday() ? 'bg-primary-500 text-white' : 'text-mono-900' }}">
                                {{ $dayDate->day }}
                            </span>
                        </div>
                        <div class="p-1.5 space-y-1">
                            @if($dayEvents && !empty($dayEvents['events']))
                                @foreach($dayEvents['events'] as $ev)
                                    @if($ev['is_financial'])
                                        <a href="{{ route('financeiro.transacoes') }}"
                                           class="block truncate text-[10px] font-medium rounded px-1.5 py-1"
                                           style="background-color: {{ $ev['color'] }}15; color: {{ $ev['color'] }}">
                                            <span class="material-icons-outlined text-[10px]">attach_money</span>
                                            {{ Str::limit($ev['title'], 18) }}
                                        </a>
                                    @else
                                        <button wire:click="showDetail('{{ $ev['id'] }}')"
                                                class="block w-full text-left truncate text-[10px] font-medium rounded px-1.5 py-1"
                                                style="background-color: {{ $ev['color'] }}20; color: {{ $ev['color'] }}">
                                            @if(!$ev['is_all_day'])
                                                <span class="opacity-60">{{ $ev['start_at']->format('H:i') }}</span>
                                            @endif
                                            {{ Str::limit($ev['title'], 18) }}
                                        </button>
                                    @endif
                                @endforeach
                            @endif
                            <button wire:click="openCreateModal('{{ $dayStr }}')"
                                    class="w-full text-center text-[10px] text-mono-300 hover:text-primary-500 py-1 transition-colors">
                                <span class="material-icons-outlined text-[14px]">add</span>
                            </button>
                        </div>
                    </div>
                @endfor
            </div>
        </x-jr.card>

    @else
        {{-- DAY VIEW --}}
        @php $selDate = \Carbon\Carbon::parse($selectedDate); @endphp
        <x-jr.card>
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-bold text-mono-900">{{ ucfirst($selDate->translatedFormat('l, d \d\e F')) }}</h3>
                    @if($selDate->isToday())
                        <x-jr.badge variant="primary" size="sm">Hoje</x-jr.badge>
                    @endif
                </div>
                <x-jr.button wire:click="openCreateModal('{{ $selectedDate }}')" size="sm" variant="standard">
                    <span class="material-icons-outlined text-[16px]">add</span>
                    Novo evento neste dia
                </x-jr.button>
            </div>

            @if(empty($selectedDateEvents))
                <div class="text-center py-12">
                    <span class="material-icons-outlined text-[48px] text-mono-200">event</span>
                    <p class="text-sm text-mono-600 mt-2">Nenhum evento neste dia.</p>
                </div>
            @else
                <div class="space-y-2">
                    @foreach($selectedDateEvents as $ev)
                        <div class="flex items-center gap-3 p-3 rounded-xl border border-mono-100 hover:bg-mono-50/50 transition-colors">
                            <div class="w-1 h-10 rounded-full flex-shrink-0" style="background-color: {{ $ev['color'] }}"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-mono-900">{{ $ev['title'] }}</p>
                                <p class="text-xs text-mono-300 mt-0.5">
                                    @if($ev['is_financial'])
                                        <span class="material-icons-outlined text-[12px] align-middle">attach_money</span>
                                        Vencimento financeiro
                                    @elseif($ev['is_all_day'])
                                        Dia inteiro
                                    @else
                                        {{ $ev['start_at']->format('H:i') }}
                                        @if($ev['end_at']) - {{ $ev['end_at']->format('H:i') }} @endif
                                    @endif
                                </p>
                            </div>
                            @if(!$ev['is_financial'])
                                <button wire:click="showDetail('{{ $ev['id'] }}')"
                                        class="p-1.5 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-100 transition-colors">
                                    <span class="material-icons-outlined text-[16px]">visibility</span>
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </x-jr.card>
    @endif

    <!-- Mini calendar for date selection (below week/day views) -->
    @if($view !== 'month')
        <div class="mt-4">
            <x-jr.card>
                <p class="text-xs font-semibold text-mono-600 mb-2">Selecione um dia</p>
                <div class="flex flex-wrap gap-1">
                    @php
                        $ref = \Carbon\Carbon::parse($currentMonth . '-01');
                        $daysInMonth = $ref->daysInMonth;
                    @endphp
                    @for($d = 1; $d <= $daysInMonth; $d++)
                        @php $ds = $ref->copy()->day($d)->format('Y-m-d'); @endphp
                        <button wire:click="selectDate('{{ $ds }}')"
                                class="w-9 h-9 rounded-lg text-xs font-medium transition-colors
                                       {{ $ds === $selectedDate ? 'bg-primary-500 text-white' : ($ds === now()->format('Y-m-d') ? 'bg-primary-100 text-primary-500' : 'text-mono-900 hover:bg-mono-100') }}">
                            {{ $d }}
                        </button>
                    @endfor
                </div>
            </x-jr.card>
        </div>
    @endif

    <!-- Event Detail Modal -->
    @if($showDetailModal && $detailEvent)
        <div class="fixed inset-0 z-modal overflow-y-auto" wire:keydown.escape="$set('showDetailModal', false)">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showDetailModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-md overflow-hidden">
                    <div class="h-2 rounded-t-2xl" style="background-color: {{ $detailEvent['color'] }}"></div>
                    <div class="px-6 py-5">
                        <h3 class="text-lg font-bold text-mono-900">{{ $detailEvent['title'] }}</h3>

                        <div class="mt-4 space-y-3">
                            <div class="flex items-center gap-3 text-sm">
                                <span class="material-icons-outlined text-[18px] text-mono-300">schedule</span>
                                @if($detailEvent['is_all_day'])
                                    <span class="text-mono-600">Dia inteiro · {{ $detailEvent['start_at']->format('d/m/Y') }}</span>
                                @else
                                    <span class="text-mono-600">
                                        {{ $detailEvent['start_at']->format('d/m/Y H:i') }}
                                        @if($detailEvent['end_at']) — {{ $detailEvent['end_at']->format('H:i') }} @endif
                                    </span>
                                @endif
                            </div>

                            @if($detailEvent['location'])
                                <div class="flex items-center gap-3 text-sm">
                                    <span class="material-icons-outlined text-[18px] text-mono-300">location_on</span>
                                    <span class="text-mono-600">{{ $detailEvent['location'] }}</span>
                                </div>
                            @endif

                            @if($detailEvent['is_recurring'])
                                <div class="flex items-center gap-3 text-sm">
                                    <span class="material-icons-outlined text-[18px] text-mono-300">repeat</span>
                                    <span class="text-mono-600">Recorrencia {{ $detailEvent['recurrence_type'] }}</span>
                                </div>
                            @endif

                            @if($detailEvent['description'])
                                <div class="pt-3 border-t border-mono-100">
                                    <p class="text-sm text-mono-600">{{ $detailEvent['description'] }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center justify-between px-6 py-4 border-t border-mono-100 bg-mono-50">
                        <button wire:click="confirmDelete('{{ $detailEvent['id'] }}')"
                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-error hover:underline">
                            <span class="material-icons-outlined text-[14px]">delete</span>
                            Excluir
                        </button>
                        <div class="flex items-center gap-2">
                            <x-jr.button variant="mono" wire:click="$set('showDetailModal', false)" size="sm">Fechar</x-jr.button>
                            <x-jr.button wire:click="openEditModal('{{ $detailEvent['id'] }}')" size="sm">
                                <span class="material-icons-outlined text-[14px]">edit</span>
                                Editar
                            </x-jr.button>
                        </div>
                    </div>
                </div>
            </div>
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
                            {{ $editingId ? 'Editar Evento' : 'Novo Evento' }}
                        </h3>
                        <button wire:click="$set('showModal', false)" class="p-1 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                            <span class="material-icons-outlined text-[20px]">close</span>
                        </button>
                    </div>

                    <form wire:submit="save">
                        <div class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">
                            <x-jr.input label="Titulo" wire:model="title" placeholder="Titulo do evento"
                                        icon="event" :error="$errors->first('title')" />

                            <!-- All Day Toggle -->
                            <label class="flex items-center gap-3 cursor-pointer">
                                <div class="relative">
                                    <input type="checkbox" wire:model.live="is_all_day" class="sr-only peer">
                                    <div class="w-11 h-6 bg-mono-200 rounded-full peer-checked:bg-primary-500 transition-colors"></div>
                                    <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-mono-white rounded-full shadow transition-transform peer-checked:translate-x-5"></div>
                                </div>
                                <span class="text-sm font-medium text-mono-900">Dia inteiro</span>
                            </label>

                            <!-- Date/Time -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-mono-600 mb-1.5">Data inicio</label>
                                    <input type="date" wire:model="start_date"
                                           class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                    @error('start_date') <p class="text-xs font-medium text-error mt-1.5 pl-4">{{ $message }}</p> @enderror
                                </div>
                                @if(!$is_all_day)
                                    <div>
                                        <label class="block text-sm font-medium text-mono-600 mb-1.5">Hora inicio</label>
                                        <input type="time" wire:model="start_time"
                                               class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                    </div>
                                @endif
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-mono-600 mb-1.5">Data fim (opcional)</label>
                                    <input type="date" wire:model="end_date"
                                           class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                </div>
                                @if(!$is_all_day)
                                    <div>
                                        <label class="block text-sm font-medium text-mono-600 mb-1.5">Hora fim</label>
                                        <input type="time" wire:model="end_time"
                                               class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                    </div>
                                @endif
                            </div>

                            <x-jr.input label="Local (opcional)" wire:model="location" placeholder="Onde sera o evento"
                                        icon="location_on" />

                            <!-- Color -->
                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Cor</label>
                                <div class="flex items-center gap-3">
                                    @php $colors = ['#ff6f00', '#5C6BC0', '#EF5350', '#42A5F5', '#66BB6A', '#AB47BC', '#26C6DA', '#FFA726', '#EC407A']; @endphp
                                    @foreach($colors as $c)
                                        <button type="button" wire:click="$set('color', '{{ $c }}')"
                                                class="w-7 h-7 rounded-full transition-transform {{ $color === $c ? 'ring-2 ring-offset-2 ring-mono-900 scale-110' : 'hover:scale-110' }}"
                                                style="background-color: {{ $c }}">
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Reminder -->
                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Lembrete (minutos antes)</label>
                                <select wire:model="reminder_minutes"
                                        class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                    <option value="">Sem lembrete</option>
                                    <option value="5">5 minutos</option>
                                    <option value="15">15 minutos</option>
                                    <option value="30">30 minutos</option>
                                    <option value="60">1 hora</option>
                                    <option value="1440">1 dia</option>
                                </select>
                            </div>

                            <!-- Recurrence -->
                            <label class="flex items-center gap-3 cursor-pointer">
                                <div class="relative">
                                    <input type="checkbox" wire:model.live="is_recurring" class="sr-only peer">
                                    <div class="w-11 h-6 bg-mono-200 rounded-full peer-checked:bg-primary-500 transition-colors"></div>
                                    <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-mono-white rounded-full shadow transition-transform peer-checked:translate-x-5"></div>
                                </div>
                                <span class="text-sm font-medium text-mono-900">Evento recorrente</span>
                            </label>

                            @if($is_recurring)
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-sm font-medium text-mono-600 mb-1.5">Frequencia</label>
                                        <select wire:model="recurrence_type"
                                                class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                            @foreach($recurrenceTypes as $rt)
                                                <option value="{{ $rt->value }}">{{ $rt->label() }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-mono-600 mb-1.5">Ate quando</label>
                                        <input type="date" wire:model="recurrence_end"
                                               class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                    </div>
                                </div>
                            @endif

                            <!-- Description -->
                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Descricao</label>
                                <textarea wire:model="description" rows="2" placeholder="Detalhes do evento..."
                                          class="w-full bg-mono-white border border-mono-200 rounded-xl px-4 py-3 text-sm text-mono-900 placeholder:text-mono-300 focus:border-primary-500 focus:ring-0 resize-none"></textarea>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-mono-100 bg-mono-50">
                            <x-jr.button variant="mono" wire:click="$set('showModal', false)" type="button">Cancelar</x-jr.button>
                            <x-jr.button type="submit" wire:loading.attr="disabled">
                                <span wire:loading wire:target="save" class="material-icons-outlined text-[18px] animate-spin">autorenew</span>
                                {{ $editingId ? 'Salvar' : 'Criar Evento' }}
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
                        <h3 class="text-lg font-bold text-mono-900">Excluir evento?</h3>
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
