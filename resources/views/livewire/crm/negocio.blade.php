<div>
    <!-- Flash Messages -->
    @if (session('success'))
        <div class="mb-4">
            <x-jr.alert variant="success">{{ session('success') }}</x-jr.alert>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4">
            <x-jr.alert variant="error">{{ session('error') }}</x-jr.alert>
        </div>
    @endif

    <!-- Back button -->
    <div class="mb-4">
        <a href="{{ route('crm.pipeline') }}" wire:navigate class="inline-flex items-center gap-1.5 text-sm text-mono-500 hover:text-mono-900 transition-colors">
            <span class="material-icons-outlined text-[18px]">arrow_back</span>
            Voltar ao Pipeline
        </a>
    </div>

    @if($deal)
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Deal Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Deal Header Card -->
            <x-jr.card>
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h2 class="text-xl font-bold text-mono-900">{{ $deal->title }}</h2>
                            <x-jr.badge variant="{{ $deal->status->color() }}">
                                {{ $deal->status->label() }}
                            </x-jr.badge>
                        </div>
                        <div class="flex items-center gap-4 text-sm text-mono-500">
                            <span class="flex items-center gap-1">
                                <span class="material-icons-outlined text-[16px]">person</span>
                                {{ $deal->contact->name }}
                            </span>
                            @if($deal->product)
                                <span class="flex items-center gap-1">
                                    <span class="material-icons-outlined text-[16px]">inventory_2</span>
                                    {{ $deal->product->name }}
                                </span>
                            @endif
                            <span class="flex items-center gap-1">
                                <span class="material-icons-outlined text-[16px]">calendar_today</span>
                                Criado em {{ $deal->created_at->format('d/m/Y') }}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button wire:click="openEditModal" class="p-2 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors" title="Editar">
                            <span class="material-icons-outlined text-[20px]">edit</span>
                        </button>
                        <button wire:click="confirmDelete" class="p-2 rounded-lg text-mono-300 hover:text-error hover:bg-down-bg transition-colors" title="Excluir">
                            <span class="material-icons-outlined text-[20px]">delete</span>
                        </button>
                    </div>
                </div>

                <!-- Value highlight -->
                <div class="mt-4 p-4 rounded-xl bg-mono-50 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-mono-500 uppercase tracking-wider">Valor do Negocio</p>
                        <p class="text-2xl font-bold text-mono-900">R$ {{ number_format($deal->value, 2, ',', '.') }}</p>
                    </div>
                    @if($deal->expected_close_date)
                        <div class="text-right">
                            <p class="text-xs font-medium text-mono-500 uppercase tracking-wider">Previsao Fechamento</p>
                            <p class="text-lg font-semibold text-mono-700">{{ $deal->expected_close_date->format('d/m/Y') }}</p>
                        </div>
                    @endif
                </div>

                @if($deal->notes)
                    <div class="mt-4">
                        <p class="text-xs font-medium text-mono-500 uppercase tracking-wider mb-1">Observacoes</p>
                        <p class="text-sm text-mono-700 whitespace-pre-line">{{ $deal->notes }}</p>
                    </div>
                @endif
            </x-jr.card>

            <!-- Stage Progress -->
            <x-jr.card>
                <h3 class="text-sm font-semibold text-mono-900 mb-4">Progresso do Negocio</h3>
                <div class="flex items-center gap-1">
                    @php
                        $pipelineStages = \App\Enums\DealStage::pipelineStages();
                        $currentIndex = array_search($deal->stage, $pipelineStages);
                        $isWon = $deal->stage === \App\Enums\DealStage::Won;
                        $isLost = $deal->stage === \App\Enums\DealStage::Lost;
                    @endphp
                    @foreach($pipelineStages as $idx => $stage)
                        @php
                            $isCurrent = $deal->stage === $stage;
                            $isPast = $currentIndex !== false && $idx < $currentIndex;
                            $isActive = $isCurrent || $isPast || $isWon;
                        @endphp
                        <button
                            wire:click="changeStage('{{ $stage->value }}')"
                            @class([
                                'flex-1 py-2.5 px-2 rounded-lg text-xs font-medium transition-all text-center',
                                'bg-primary-500 text-white shadow-sm' => $isCurrent && !$isWon && !$isLost,
                                'bg-primary-100 text-primary-700' => $isPast && !$isWon,
                                'bg-success-100 text-success-700' => $isWon,
                                'bg-mono-100 text-mono-400' => !$isActive && !$isWon && !$isLost,
                                'bg-error-100 text-error-700' => $isLost,
                            ])
                            {{ $deal->isOpen() ? '' : 'disabled' }}
                        >
                            <span class="material-icons-outlined text-[14px] block mb-0.5">{{ $stage->icon() }}</span>
                            {{ $stage->label() }}
                        </button>
                        @if(!$loop->last)
                            <span class="material-icons-outlined text-[16px] text-mono-300">chevron_right</span>
                        @endif
                    @endforeach
                </div>

                <!-- Quick actions -->
                <div class="flex items-center gap-2 mt-4 pt-4 border-t border-mono-100">
                    @if($deal->isOpen())
                        <button wire:click="changeStage('won')" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-success-50 text-success-700 hover:bg-success-100 transition-colors">
                            <span class="material-icons-outlined text-[16px]">emoji_events</span>
                            Marcar como Ganho
                        </button>
                        <button wire:click="changeStage('lost')" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-error-50 text-error-700 hover:bg-error-100 transition-colors">
                            <span class="material-icons-outlined text-[16px]">block</span>
                            Marcar como Perdido
                        </button>
                    @else
                        <button wire:click="reopenDeal" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-info-50 text-info-700 hover:bg-info-100 transition-colors">
                            <span class="material-icons-outlined text-[16px]">replay</span>
                            Reabrir Negocio
                        </button>
                    @endif
                </div>
            </x-jr.card>

            <!-- Activity Timeline -->
            <x-jr.card>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-mono-900">Atividades</h3>
                    <x-jr.button wire:click="openActivityModal" size="sm">
                        <span class="material-icons-outlined text-[16px]">add</span>
                        Nova Atividade
                    </x-jr.button>
                </div>

                @if($deal->activities->isEmpty())
                    <div class="text-center py-8">
                        <span class="material-icons-outlined text-[40px] text-mono-200">timeline</span>
                        <p class="text-sm text-mono-500 mt-2">Nenhuma atividade registrada.</p>
                    </div>
                @else
                    <div class="relative">
                        <!-- Timeline line -->
                        <div class="absolute left-[17px] top-0 bottom-0 w-0.5 bg-mono-100"></div>

                        <div class="space-y-4">
                            @foreach($deal->activities as $activity)
                                <div class="relative flex gap-4">
                                    <!-- Icon -->
                                    <div @class([
                                        'relative z-10 flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center',
                                        'bg-mono-100 text-mono-500' => $activity->type === \App\Enums\ActivityType::Note || $activity->type === \App\Enums\ActivityType::StageChange,
                                        'bg-info-100 text-info-600' => $activity->type === \App\Enums\ActivityType::Call || $activity->type === \App\Enums\ActivityType::Email,
                                        'bg-primary-100 text-primary-600' => $activity->type === \App\Enums\ActivityType::Meeting,
                                        'bg-success/10 text-success' => $activity->type === \App\Enums\ActivityType::WhatsApp,
                                    ])>
                                        <span class="material-icons-outlined text-[18px]">{{ $activity->type->icon() }}</span>
                                    </div>

                                    <!-- Content -->
                                    <div class="flex-1 pb-4">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-semibold text-mono-700 uppercase">{{ $activity->type->label() }}</span>
                                                    <span class="text-xs text-mono-400">{{ $activity->happened_at->format('d/m/Y H:i') }}</span>
                                                </div>
                                                <p class="text-sm text-mono-700 mt-1 whitespace-pre-line">{{ $activity->description }}</p>
                                            </div>
                                            @if($activity->type !== \App\Enums\ActivityType::StageChange)
                                                <button wire:click="deleteActivity('{{ $activity->id }}')"
                                                        wire:confirm="Tem certeza que deseja remover esta atividade?"
                                                        class="p-1 rounded text-mono-300 hover:text-error transition-colors flex-shrink-0">
                                                    <span class="material-icons-outlined text-[16px]">close</span>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </x-jr.card>
        </div>

        <!-- Right Column: Sidebar -->
        <div class="space-y-6">
            <!-- Contact Card -->
            <x-jr.card>
                <h3 class="text-sm font-semibold text-mono-900 mb-3">Contato</h3>
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                        <span class="text-sm font-bold text-primary-600">{{ strtoupper(substr($deal->contact->name, 0, 2)) }}</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-mono-900">{{ $deal->contact->name }}</p>
                        @if($deal->contact->company)
                            <p class="text-xs text-mono-500">{{ $deal->contact->company }}</p>
                        @endif
                    </div>
                </div>
                <div class="space-y-2">
                    @if($deal->contact->email)
                        <div class="flex items-center gap-2 text-sm text-mono-600">
                            <span class="material-icons-outlined text-[16px] text-mono-300">email</span>
                            <a href="mailto:{{ $deal->contact->email }}" class="hover:text-primary-500 transition-colors">{{ $deal->contact->email }}</a>
                        </div>
                    @endif
                    @if($deal->contact->phone)
                        <div class="flex items-center gap-2 text-sm text-mono-600">
                            <span class="material-icons-outlined text-[16px] text-mono-300">phone</span>
                            <a href="tel:{{ $deal->contact->phone }}" class="hover:text-primary-500 transition-colors">{{ $deal->contact->phone }}</a>
                        </div>
                    @endif
                </div>
            </x-jr.card>

            <!-- Product Card -->
            @if($deal->product)
                <x-jr.card>
                    <h3 class="text-sm font-semibold text-mono-900 mb-3">Produto</h3>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background-color: {{ $deal->product->color ?? '#6366f1' }}20">
                            <span class="material-icons-outlined text-[20px]" style="color: {{ $deal->product->color ?? '#6366f1' }}">inventory_2</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-mono-900">{{ $deal->product->name }}</p>
                            @if($deal->product->price)
                                <p class="text-xs text-mono-500">R$ {{ number_format($deal->product->price, 2, ',', '.') }}</p>
                            @endif
                        </div>
                    </div>
                    @if($deal->product->description)
                        <p class="text-xs text-mono-500 mt-2">{{ $deal->product->description }}</p>
                    @endif
                </x-jr.card>
            @endif

            <!-- WhatsApp Conversations -->
            <x-jr.card>
                <h3 class="text-sm font-semibold text-mono-900 mb-3">Conversas WhatsApp</h3>
                @if($whatsappConversations->isEmpty())
                    <div class="text-center py-4">
                        <span class="material-icons-outlined text-[28px] text-mono-200">chat</span>
                        <p class="text-xs text-mono-300 mt-1">Nenhuma conversa vinculada.</p>
                    </div>
                @else
                    <div class="space-y-2">
                        @foreach($whatsappConversations as $wConv)
                            <a href="{{ route('whatsapp.chat', $wConv->instance_id) }}"
                               class="flex items-center gap-3 p-2.5 rounded-xl bg-mono-50 hover:bg-mono-100 transition-colors">
                                <div class="w-8 h-8 rounded-full bg-success/10 flex items-center justify-center flex-shrink-0">
                                    <span class="material-icons-outlined text-[16px] text-success">chat</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-mono-900 truncate">{{ $wConv->displayName() }}</p>
                                    <p class="text-[10px] text-mono-300 truncate">{{ $wConv->last_message ?? 'Sem mensagens' }}</p>
                                </div>
                                @if($wConv->unread_count > 0)
                                    <span class="w-5 h-5 rounded-full bg-primary-500 text-white text-[10px] font-bold flex items-center justify-center flex-shrink-0">
                                        {{ $wConv->unread_count }}
                                    </span>
                                @endif
                                @if($wConv->last_message_at)
                                    <span class="text-[10px] text-mono-300 flex-shrink-0">
                                        {{ $wConv->last_message_at->isToday() ? $wConv->last_message_at->format('H:i') : $wConv->last_message_at->format('d/m') }}
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endif
            </x-jr.card>

            <!-- Deal Details -->
            <x-jr.card>
                <h3 class="text-sm font-semibold text-mono-900 mb-3">Detalhes</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-mono-500">Etapa</span>
                        <x-jr.badge variant="{{ $deal->stage->color() }}" size="sm">
                            {{ $deal->stage->label() }}
                        </x-jr.badge>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-mono-500">Status</span>
                        <x-jr.badge variant="{{ $deal->status->color() }}" size="sm">
                            {{ $deal->status->label() }}
                        </x-jr.badge>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-mono-500">Valor</span>
                        <span class="text-sm font-semibold text-mono-900">R$ {{ number_format($deal->value, 2, ',', '.') }}</span>
                    </div>
                    @if($deal->expected_close_date)
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-mono-500">Previsao</span>
                            <span class="text-sm text-mono-700">{{ $deal->expected_close_date->format('d/m/Y') }}</span>
                        </div>
                    @endif
                    @if($deal->closed_at)
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-mono-500">Fechado em</span>
                            <span class="text-sm text-mono-700">{{ $deal->closed_at->format('d/m/Y') }}</span>
                        </div>
                    @endif
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-mono-500">Criado em</span>
                        <span class="text-sm text-mono-700">{{ $deal->created_at->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-mono-500">Atualizado</span>
                        <span class="text-sm text-mono-700">{{ $deal->updated_at->diffForHumans() }}</span>
                    </div>
                </div>
            </x-jr.card>

            <!-- Activity Summary -->
            <x-jr.card>
                <h3 class="text-sm font-semibold text-mono-900 mb-3">Resumo Atividades</h3>
                <div class="space-y-2">
                    @php
                        $activityCounts = $deal->activities->groupBy(fn($a) => $a->type->value)->map->count();
                    @endphp
                    @foreach(\App\Enums\ActivityType::cases() as $type)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="material-icons-outlined text-[16px] text-mono-300">{{ $type->icon() }}</span>
                                <span class="text-xs text-mono-600">{{ $type->label() }}</span>
                            </div>
                            <span class="text-xs font-semibold text-mono-700">{{ $activityCounts[$type->value] ?? 0 }}</span>
                        </div>
                    @endforeach
                </div>
            </x-jr.card>
        </div>
    </div>

    <!-- Edit Deal Modal -->
    @if($showEditModal)
        <div class="fixed inset-0 z-modal overflow-y-auto" wire:keydown.escape="$set('showEditModal', false)">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showEditModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-lg overflow-hidden">
                    <!-- Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-mono-100">
                        <h3 class="text-lg font-bold text-mono-900">Editar Negocio</h3>
                        <button wire:click="$set('showEditModal', false)" class="p-1 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                            <span class="material-icons-outlined text-[20px]">close</span>
                        </button>
                    </div>

                    <!-- Form -->
                    <form wire:submit="saveDeal">
                        <div class="px-6 py-5 space-y-4 max-h-[60vh] overflow-y-auto">
                            <x-jr.input label="Titulo" wire:model="dealTitle" placeholder="Nome do negocio"
                                        icon="handshake" :error="$errors->first('dealTitle')" />

                            <!-- Contact Select -->
                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Contato</label>
                                <select wire:model="dealContactId"
                                        class="w-full h-12 px-4 rounded-pill border border-mono-200 bg-mono-white text-sm text-mono-900 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors">
                                    <option value="">Selecione um contato</option>
                                    @foreach($contacts as $contact)
                                        <option value="{{ $contact->id }}">{{ $contact->name }}</option>
                                    @endforeach
                                </select>
                                @error('dealContactId')
                                    <p class="text-xs text-error mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Product Select -->
                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Produto</label>
                                <select wire:model="dealProductId"
                                        class="w-full h-12 px-4 rounded-pill border border-mono-200 bg-mono-white text-sm text-mono-900 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors">
                                    <option value="">Sem produto</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Stage Select -->
                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Etapa</label>
                                <select wire:model="dealStage"
                                        class="w-full h-12 px-4 rounded-pill border border-mono-200 bg-mono-white text-sm text-mono-900 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors">
                                    @foreach($stages as $stage)
                                        <option value="{{ $stage->value }}">{{ $stage->label() }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <x-jr.input label="Valor (R$)" wire:model="dealValue" type="number" step="0.01"
                                        icon="attach_money" :error="$errors->first('dealValue')" />

                            <x-jr.input label="Previsao de Fechamento" wire:model="dealExpectedCloseDate" type="date"
                                        icon="event" :error="$errors->first('dealExpectedCloseDate')" />

                            <x-jr.input label="Observacoes" wire:model="dealNotes" placeholder="Notas adicionais"
                                        icon="notes" :error="$errors->first('dealNotes')" />
                        </div>

                        <!-- Footer -->
                        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-mono-100 bg-mono-50">
                            <x-jr.button variant="mono" wire:click="$set('showEditModal', false)" type="button">
                                Cancelar
                            </x-jr.button>
                            <x-jr.button type="submit" wire:loading.attr="disabled">
                                <span wire:loading wire:target="saveDeal" class="material-icons-outlined text-[18px] animate-spin">autorenew</span>
                                Salvar
                            </x-jr.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Activity Modal -->
    @if($showActivityModal)
        <div class="fixed inset-0 z-modal overflow-y-auto" wire:keydown.escape="$set('showActivityModal', false)">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showActivityModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-lg overflow-hidden">
                    <!-- Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-mono-100">
                        <h3 class="text-lg font-bold text-mono-900">Nova Atividade</h3>
                        <button wire:click="$set('showActivityModal', false)" class="p-1 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                            <span class="material-icons-outlined text-[20px]">close</span>
                        </button>
                    </div>

                    <!-- Form -->
                    <form wire:submit="saveActivity">
                        <div class="px-6 py-5 space-y-4">
                            <!-- Type selector -->
                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-2">Tipo</label>
                                <div class="grid grid-cols-4 gap-2">
                                    @foreach(['note' => ['Nota', 'sticky_note_2'], 'call' => ['Ligacao', 'phone'], 'email' => ['E-mail', 'email'], 'meeting' => ['Reuniao', 'groups']] as $type => $info)
                                        <button type="button" wire:click="$set('activityType', '{{ $type }}')"
                                                @class([
                                                    'flex flex-col items-center gap-1 p-3 rounded-xl border-2 transition-all text-center',
                                                    'border-primary-500 bg-primary-50 text-primary-700' => $activityType === $type,
                                                    'border-mono-200 text-mono-500 hover:border-mono-300' => $activityType !== $type,
                                                ])>
                                            <span class="material-icons-outlined text-[20px]">{{ $info[1] }}</span>
                                            <span class="text-xs font-medium">{{ $info[0] }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Description -->
                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Descricao</label>
                                <textarea wire:model="activityDescription" rows="4" placeholder="Descreva a atividade..."
                                          class="w-full px-4 py-3 rounded-xl border border-mono-200 bg-mono-white text-sm text-mono-900 placeholder-mono-300 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors resize-none"></textarea>
                                @error('activityDescription')
                                    <p class="text-xs text-error mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <x-jr.input label="Data/Hora" wire:model="activityDate" type="datetime-local"
                                        icon="schedule" :error="$errors->first('activityDate')" />
                        </div>

                        <!-- Footer -->
                        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-mono-100 bg-mono-50">
                            <x-jr.button variant="mono" wire:click="$set('showActivityModal', false)" type="button">
                                Cancelar
                            </x-jr.button>
                            <x-jr.button type="submit" wire:loading.attr="disabled">
                                <span wire:loading wire:target="saveActivity" class="material-icons-outlined text-[18px] animate-spin">autorenew</span>
                                Registrar Atividade
                            </x-jr.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-modal overflow-y-auto">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showDeleteModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-sm overflow-hidden">
                    <div class="px-6 py-5 text-center">
                        <div class="mx-auto w-12 h-12 rounded-full bg-down-bg flex items-center justify-center mb-4">
                            <span class="material-icons-outlined text-[24px] text-error">delete</span>
                        </div>
                        <h3 class="text-lg font-bold text-mono-900">Excluir negocio?</h3>
                        <p class="text-sm text-mono-600 mt-2">Esta acao nao pode ser desfeita. O negocio e todas as atividades serao perdidos permanentemente.</p>
                    </div>
                    <div class="flex items-center justify-center gap-3 px-6 py-4 border-t border-mono-100 bg-mono-50">
                        <x-jr.button variant="mono" wire:click="$set('showDeleteModal', false)">
                            Cancelar
                        </x-jr.button>
                        <x-jr.button variant="danger" wire:click="deleteDeal">
                            Excluir
                        </x-jr.button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    @endif
</div>
