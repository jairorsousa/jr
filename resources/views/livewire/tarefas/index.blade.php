<div>
    <!-- Flash Messages -->
    @if (session('success'))
        <div class="mb-4"><x-jr.alert variant="success">{{ session('success') }}</x-jr.alert></div>
    @endif

    <div class="flex gap-6">
        <!-- Sidebar: Task Lists -->
        <div class="w-64 flex-shrink-0 hidden lg:block">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-semibold text-mono-600 uppercase tracking-wider">Listas</h3>
                <button wire:click="openCreateList"
                        class="p-1 rounded-lg text-mono-300 hover:text-primary-500 hover:bg-primary-100 transition-colors">
                    <span class="material-icons-outlined text-[18px]">add</span>
                </button>
            </div>

            <!-- All Tasks -->
            <button wire:click="selectList(null)"
                    class="flex items-center justify-between w-full px-3 py-2.5 rounded-xl text-sm font-medium transition-colors mb-1
                           {{ !$selectedListId ? 'text-primary-500 bg-primary-100' : 'text-mono-900 hover:bg-mono-100' }}">
                <div class="flex items-center gap-2.5">
                    <span class="material-icons-outlined text-[18px]">inbox</span>
                    <span>Todas</span>
                </div>
                <span class="text-xs text-mono-300">{{ $tasks->count() }}</span>
            </button>

            <div class="space-y-0.5">
                @foreach($lists as $list)
                    <div class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition-colors cursor-pointer
                                {{ $selectedListId === $list->id ? 'text-primary-500 bg-primary-100' : 'text-mono-900 hover:bg-mono-100' }}"
                         wire:click="selectList('{{ $list->id }}')">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $list->color }}"></div>
                            <span class="truncate">{{ $list->name }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="text-xs text-mono-300">{{ $list->pending_count }}</span>
                            <div class="hidden group-hover:flex items-center gap-0.5">
                                <button wire:click.stop="openEditList('{{ $list->id }}')"
                                        class="p-0.5 rounded text-mono-300 hover:text-mono-600 transition-colors">
                                    <span class="material-icons-outlined text-[14px]">edit</span>
                                </button>
                                <button wire:click.stop="confirmDeleteList('{{ $list->id }}')"
                                        class="p-0.5 rounded text-mono-300 hover:text-error transition-colors">
                                    <span class="material-icons-outlined text-[14px]">delete</span>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Main Content: Tasks -->
        <div class="flex-1 min-w-0">
            <!-- Mobile List Selector -->
            <div class="lg:hidden mb-4">
                <select wire:model.live="selectedListId"
                        class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                    <option value="">Todas as listas</option>
                    @foreach($lists as $list)
                        <option value="{{ $list->id }}">{{ $list->name }} ({{ $list->pending_count }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Filters + New Task -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4">
                <div class="flex items-center gap-2 flex-wrap">
                    <!-- Priority Filter -->
                    <div class="flex bg-mono-100 rounded-pill p-0.5">
                        <button wire:click="$set('filterPriority', '')"
                                class="px-2.5 py-1 text-xs font-semibold rounded-pill transition-colors
                                       {{ !$filterPriority ? 'bg-mono-white text-mono-900 shadow-sm' : 'text-mono-600 hover:text-mono-900' }}">
                            Todas
                        </button>
                        @foreach($priorities as $p)
                            <button wire:click="$set('filterPriority', '{{ $p->value }}')"
                                    class="px-2.5 py-1 text-xs font-semibold rounded-pill transition-colors
                                           {{ $filterPriority === $p->value ? 'bg-mono-white text-mono-900 shadow-sm' : 'text-mono-600 hover:text-mono-900' }}">
                                {{ $p->label() }}
                            </button>
                        @endforeach
                    </div>

                    <!-- Status Filter -->
                    <select wire:model.live="filterStatus"
                            class="bg-mono-white border border-mono-200 rounded-pill px-3 h-9 text-xs font-medium text-mono-900 focus:border-primary-500 focus:ring-0">
                        <option value="">Status</option>
                        @foreach($statuses as $s)
                            <option value="{{ $s->value }}">{{ $s->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <x-jr.button wire:click="openCreateTask" size="sm">
                    <span class="material-icons-outlined text-[16px]">add</span>
                    Nova Tarefa
                </x-jr.button>
            </div>

            <!-- Tasks List -->
            @if($tasks->isEmpty())
                <x-jr.card>
                    <div class="text-center py-12">
                        <span class="material-icons-outlined text-[48px] text-mono-200">task_alt</span>
                        <p class="text-mono-600 mt-2">Nenhuma tarefa encontrada.</p>
                        <div class="mt-4">
                            <x-jr.button wire:click="openCreateTask" size="sm">Criar primeira tarefa</x-jr.button>
                        </div>
                    </div>
                </x-jr.card>
            @else
                <div class="space-y-2" wire:sortable="reorder">
                    @foreach($tasks as $task)
                        @php
                            $isDone = $task->status === \App\Enums\TaskStatus::Done;
                            $isOverdue = $task->isOverdue();
                        @endphp
                        <div wire:sortable.item="{{ $task->id }}" wire:key="task-{{ $task->id }}"
                             class="bg-mono-white rounded-xl border shadow-card transition-all
                                    {{ $isOverdue ? 'border-error/30' : 'border-mono-100' }}
                                    {{ $isDone ? 'opacity-60' : '' }}">
                            <div class="flex items-start gap-3 p-4">
                                <!-- Drag Handle -->
                                <div wire:sortable.handle class="pt-0.5 cursor-grab text-mono-200 hover:text-mono-400">
                                    <span class="material-icons-outlined text-[18px]">drag_indicator</span>
                                </div>

                                <!-- Checkbox -->
                                <button wire:click="toggleComplete('{{ $task->id }}')"
                                        class="flex-shrink-0 mt-0.5 w-5 h-5 rounded-md border-2 transition-colors flex items-center justify-center
                                               {{ $isDone ? 'bg-up border-up' : 'border-mono-300 hover:border-primary-500' }}">
                                    @if($isDone)
                                        <span class="material-icons-outlined text-white text-[14px]">check</span>
                                    @endif
                                </button>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="text-sm font-medium {{ $isDone ? 'line-through text-mono-300' : 'text-mono-900' }}">
                                            {{ $task->title }}
                                        </p>
                                        <!-- Actions -->
                                        <div class="flex items-center gap-0.5 flex-shrink-0">
                                            <!-- Status dropdown -->
                                            <div x-data="{ open: false }" class="relative">
                                                <button @click="open = !open"
                                                        class="p-1 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-100 transition-colors">
                                                    <span class="material-icons-outlined text-[16px]">more_horiz</span>
                                                </button>
                                                <div x-show="open" x-cloak @click.away="open = false"
                                                     class="absolute right-0 top-8 bg-mono-white rounded-xl shadow-dropdown border border-mono-100 py-1.5 z-50 w-44">
                                                    <p class="px-3 py-1 text-[10px] font-semibold text-mono-300 uppercase">Status</p>
                                                    @foreach($statuses as $s)
                                                        <button wire:click="setTaskStatus('{{ $task->id }}', '{{ $s->value }}')" @click="open = false"
                                                                class="flex items-center gap-2 w-full px-3 py-1.5 text-xs text-mono-900 hover:bg-mono-50
                                                                       {{ $task->status === $s ? 'font-semibold' : '' }}">
                                                            <x-jr.badge variant="{{ $s->color() }}" size="sm">{{ $s->label() }}</x-jr.badge>
                                                        </button>
                                                    @endforeach
                                                    <div class="border-t border-mono-100 my-1"></div>
                                                    <button wire:click="openEditTask('{{ $task->id }}')" @click="open = false"
                                                            class="flex items-center gap-2 w-full px-3 py-1.5 text-xs text-mono-900 hover:bg-mono-50">
                                                        <span class="material-icons-outlined text-[14px] text-mono-300">edit</span>
                                                        Editar
                                                    </button>
                                                    <button wire:click="confirmDeleteTask('{{ $task->id }}')" @click="open = false"
                                                            class="flex items-center gap-2 w-full px-3 py-1.5 text-xs text-error hover:bg-down-bg">
                                                        <span class="material-icons-outlined text-[14px]">delete</span>
                                                        Excluir
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @if($task->description && !$isDone)
                                        <p class="text-xs text-mono-600 mt-1 line-clamp-2">{{ $task->description }}</p>
                                    @endif

                                    <!-- Meta row -->
                                    <div class="flex items-center gap-2 mt-2 flex-wrap">
                                        <!-- Priority -->
                                        <x-jr.badge size="sm" variant="{{ $task->priority->color() }}">
                                            {{ $task->priority->label() }}
                                        </x-jr.badge>

                                        <!-- Status -->
                                        @if(!$isDone)
                                            <x-jr.badge size="sm" variant="{{ $task->status->color() }}">
                                                {{ $task->status->label() }}
                                            </x-jr.badge>
                                        @endif

                                        <!-- Due date -->
                                        @if($task->due_date)
                                            <span class="inline-flex items-center gap-1 text-xs {{ $isOverdue ? 'text-error font-semibold' : 'text-mono-300' }}">
                                                <span class="material-icons-outlined text-[12px]">event</span>
                                                {{ $task->due_date->format('d/m') }}
                                                @if($isOverdue) · Atrasada @endif
                                            </span>
                                        @endif

                                        <!-- List badge -->
                                        @if($task->taskList && !$selectedListId)
                                            <span class="inline-flex items-center gap-1 text-xs text-mono-300">
                                                <span class="w-2 h-2 rounded-full" style="background-color: {{ $task->taskList->color }}"></span>
                                                {{ $task->taskList->name }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- List Create/Edit Modal -->
    @if($showListModal)
        <div class="fixed inset-0 z-modal overflow-y-auto" wire:keydown.escape="$set('showListModal', false)">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showListModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-sm overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-mono-100">
                        <h3 class="text-lg font-bold text-mono-900">
                            {{ $editingListId ? 'Editar Lista' : 'Nova Lista' }}
                        </h3>
                        <button wire:click="$set('showListModal', false)" class="p-1 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                            <span class="material-icons-outlined text-[20px]">close</span>
                        </button>
                    </div>
                    <form wire:submit="saveList">
                        <div class="px-6 py-5 space-y-4">
                            <x-jr.input label="Nome da lista" wire:model="listName" placeholder="Ex: Pessoal, Trabalho"
                                        icon="list" :error="$errors->first('listName')" />
                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Cor</label>
                                <div class="flex items-center gap-3">
                                    @php $colors = ['#ff6f00', '#5C6BC0', '#EF5350', '#42A5F5', '#66BB6A', '#AB47BC', '#26C6DA', '#FFA726', '#EC407A', '#8D6E63']; @endphp
                                    @foreach($colors as $c)
                                        <button type="button" wire:click="$set('listColor', '{{ $c }}')"
                                                class="w-7 h-7 rounded-full transition-transform {{ $listColor === $c ? 'ring-2 ring-offset-2 ring-mono-900 scale-110' : 'hover:scale-110' }}"
                                                style="background-color: {{ $c }}">
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-mono-100 bg-mono-50">
                            <x-jr.button variant="mono" wire:click="$set('showListModal', false)" type="button">Cancelar</x-jr.button>
                            <x-jr.button type="submit">{{ $editingListId ? 'Salvar' : 'Criar Lista' }}</x-jr.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Task Create/Edit Modal -->
    @if($showTaskModal)
        <div class="fixed inset-0 z-modal overflow-y-auto" wire:keydown.escape="$set('showTaskModal', false)">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showTaskModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-lg overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-mono-100">
                        <h3 class="text-lg font-bold text-mono-900">
                            {{ $editingTaskId ? 'Editar Tarefa' : 'Nova Tarefa' }}
                        </h3>
                        <button wire:click="$set('showTaskModal', false)" class="p-1 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                            <span class="material-icons-outlined text-[20px]">close</span>
                        </button>
                    </div>
                    <form wire:submit="saveTask">
                        <div class="px-6 py-5 space-y-4">
                            <x-jr.input label="Titulo" wire:model="taskTitle" placeholder="O que precisa ser feito?"
                                        icon="check_circle" :error="$errors->first('taskTitle')" />

                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Descricao</label>
                                <textarea wire:model="taskDescription" rows="3" placeholder="Detalhes da tarefa..."
                                          class="w-full bg-mono-white border border-mono-200 rounded-xl px-4 py-3 text-sm text-mono-900 placeholder:text-mono-300 focus:border-primary-500 focus:ring-0 resize-none"></textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <!-- Priority -->
                                <div>
                                    <label class="block text-sm font-medium text-mono-600 mb-1.5">Prioridade</label>
                                    <select wire:model="taskPriority"
                                            class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                        @foreach($priorities as $p)
                                            <option value="{{ $p->value }}">{{ $p->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Due Date -->
                                <div>
                                    <label class="block text-sm font-medium text-mono-600 mb-1.5">Prazo</label>
                                    <input type="date" wire:model="taskDueDate"
                                           class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                </div>
                            </div>

                            <!-- List -->
                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Lista</label>
                                <select wire:model="taskListId"
                                        class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                    <option value="">Sem lista</option>
                                    @foreach($allLists as $l)
                                        <option value="{{ $l->id }}">{{ $l->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-mono-100 bg-mono-50">
                            <x-jr.button variant="mono" wire:click="$set('showTaskModal', false)" type="button">Cancelar</x-jr.button>
                            <x-jr.button type="submit" wire:loading.attr="disabled">
                                <span wire:loading wire:target="saveTask" class="material-icons-outlined text-[18px] animate-spin">autorenew</span>
                                {{ $editingTaskId ? 'Salvar' : 'Criar Tarefa' }}
                            </x-jr.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete List Confirmation -->
    @if($showDeleteListModal)
        <div class="fixed inset-0 z-modal overflow-y-auto">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showDeleteListModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-sm overflow-hidden">
                    <div class="px-6 py-5 text-center">
                        <div class="mx-auto w-12 h-12 rounded-full bg-down-bg flex items-center justify-center mb-4">
                            <span class="material-icons-outlined text-[24px] text-error">delete</span>
                        </div>
                        <h3 class="text-lg font-bold text-mono-900">Excluir lista?</h3>
                        <p class="text-sm text-mono-600 mt-2">As tarefas desta lista nao serao excluidas, apenas desvinculadas.</p>
                    </div>
                    <div class="flex items-center justify-center gap-3 px-6 py-4 border-t border-mono-100 bg-mono-50">
                        <x-jr.button variant="mono" wire:click="$set('showDeleteListModal', false)">Cancelar</x-jr.button>
                        <x-jr.button variant="danger" wire:click="deleteList">Excluir</x-jr.button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Task Confirmation -->
    @if($showDeleteTaskModal)
        <div class="fixed inset-0 z-modal overflow-y-auto">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showDeleteTaskModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-sm overflow-hidden">
                    <div class="px-6 py-5 text-center">
                        <div class="mx-auto w-12 h-12 rounded-full bg-down-bg flex items-center justify-center mb-4">
                            <span class="material-icons-outlined text-[24px] text-error">delete</span>
                        </div>
                        <h3 class="text-lg font-bold text-mono-900">Excluir tarefa?</h3>
                        <p class="text-sm text-mono-600 mt-2">Esta acao nao pode ser desfeita.</p>
                    </div>
                    <div class="flex items-center justify-center gap-3 px-6 py-4 border-t border-mono-100 bg-mono-50">
                        <x-jr.button variant="mono" wire:click="$set('showDeleteTaskModal', false)">Cancelar</x-jr.button>
                        <x-jr.button variant="danger" wire:click="deleteTask">Excluir</x-jr.button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
