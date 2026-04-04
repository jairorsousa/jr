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

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3 flex-1">
            <div class="max-w-sm flex-1">
                <x-jr.input wire:model.live.debounce.300ms="search" placeholder="Buscar campanhas..." icon="search" />
            </div>
            <select wire:model.live="filterStatus"
                    class="h-12 px-4 rounded-pill border border-mono-200 bg-mono-white text-sm text-mono-900 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors">
                <option value="">Todos status</option>
                @foreach($statuses as $st)
                    <option value="{{ $st->value }}">{{ $st->label() }}</option>
                @endforeach
            </select>
        </div>
        <x-jr.button wire:click="openCreateModal">
            <span class="material-icons-outlined text-[18px]">add</span>
            Nova Campanha
        </x-jr.button>
    </div>

    <!-- Campaigns List -->
    @if($campaigns->isEmpty())
        <x-jr.card>
            <div class="text-center py-12">
                <span class="material-icons-outlined text-[48px] text-mono-200">campaign</span>
                <p class="text-mono-600 mt-2">
                    @if($search || $filterStatus)
                        Nenhuma campanha encontrada.
                    @else
                        Nenhuma campanha criada ainda.
                    @endif
                </p>
                @unless($search || $filterStatus)
                    <div class="mt-4">
                        <x-jr.button wire:click="openCreateModal" size="sm">
                            Criar primeira campanha
                        </x-jr.button>
                    </div>
                @endunless
            </div>
        </x-jr.card>
    @else
        <div class="space-y-3">
            @foreach($campaigns as $campaign)
                <x-jr.card>
                    <div class="flex items-center gap-4">
                        <!-- Icon -->
                        <div @class([
                            'w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0',
                            'bg-mono-100' => $campaign->status === \App\Enums\CampaignStatus::Draft,
                            'bg-info-100' => $campaign->status === \App\Enums\CampaignStatus::Scheduled,
                            'bg-warning-100' => $campaign->status === \App\Enums\CampaignStatus::Sending,
                            'bg-mono-100' => $campaign->status === \App\Enums\CampaignStatus::Paused,
                            'bg-success-100' => $campaign->status === \App\Enums\CampaignStatus::Completed,
                            'bg-error-100' => $campaign->status === \App\Enums\CampaignStatus::Cancelled,
                        ])>
                            <span @class([
                                'material-icons-outlined text-[24px]',
                                'text-mono-500' => $campaign->status === \App\Enums\CampaignStatus::Draft || $campaign->status === \App\Enums\CampaignStatus::Paused,
                                'text-info-600' => $campaign->status === \App\Enums\CampaignStatus::Scheduled,
                                'text-warning-600' => $campaign->status === \App\Enums\CampaignStatus::Sending,
                                'text-success-600' => $campaign->status === \App\Enums\CampaignStatus::Completed,
                                'text-error-600' => $campaign->status === \App\Enums\CampaignStatus::Cancelled,
                            ])>{{ $campaign->status->icon() }}</span>
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="text-sm font-semibold text-mono-900 truncate">{{ $campaign->name }}</h3>
                                <x-jr.badge variant="{{ $campaign->status->color() }}" size="sm">
                                    {{ $campaign->status->label() }}
                                </x-jr.badge>
                            </div>
                            <div class="flex items-center gap-4 text-xs text-mono-500">
                                <span class="flex items-center gap-1">
                                    <span class="material-icons-outlined text-[14px]">smartphone</span>
                                    {{ $campaign->instance?->name ?? 'Sem instancia' }}
                                </span>
                                @if($campaign->template)
                                    <span class="flex items-center gap-1">
                                        <span class="material-icons-outlined text-[14px]">description</span>
                                        {{ $campaign->template->name }}
                                    </span>
                                @endif
                                <span class="flex items-center gap-1">
                                    <span class="material-icons-outlined text-[14px]">group</span>
                                    {{ $campaign->total_recipients }} destinatario(s)
                                </span>
                            </div>

                            <!-- Progress bar (for sending/completed) -->
                            @if($campaign->status === \App\Enums\CampaignStatus::Sending || $campaign->status === \App\Enums\CampaignStatus::Completed || $campaign->status === \App\Enums\CampaignStatus::Paused)
                                <div class="mt-2">
                                    <div class="flex items-center justify-between text-[10px] text-mono-500 mb-1">
                                        <span>{{ $campaign->sent_count }} enviados, {{ $campaign->failed_count }} falhas</span>
                                        <span>{{ $campaign->progressPercent() }}%</span>
                                    </div>
                                    <div class="w-full h-1.5 bg-mono-100 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-500
                                                    {{ $campaign->status === \App\Enums\CampaignStatus::Completed ? 'bg-success' : 'bg-primary-500' }}"
                                             style="width: {{ $campaign->progressPercent() }}%"></div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-1 flex-shrink-0">
                            @if($campaign->isDraft())
                                <button wire:click="openRecipientsModal('{{ $campaign->id }}')"
                                        class="p-2 rounded-lg text-mono-300 hover:text-primary-500 hover:bg-primary-50 transition-colors" title="Destinatarios">
                                    <span class="material-icons-outlined text-[20px]">group_add</span>
                                </button>
                                <button wire:click="startCampaign('{{ $campaign->id }}')"
                                        class="p-2 rounded-lg text-mono-300 hover:text-success hover:bg-success/10 transition-colors" title="Iniciar envio">
                                    <span class="material-icons-outlined text-[20px]">play_arrow</span>
                                </button>
                            @endif

                            @if($campaign->isSending())
                                <button wire:click="pauseCampaign('{{ $campaign->id }}')"
                                        class="p-2 rounded-lg text-mono-300 hover:text-warning-600 hover:bg-warning-100 transition-colors" title="Pausar">
                                    <span class="material-icons-outlined text-[20px]">pause</span>
                                </button>
                                <button wire:click="cancelCampaign('{{ $campaign->id }}')"
                                        class="p-2 rounded-lg text-mono-300 hover:text-error hover:bg-down-bg transition-colors" title="Cancelar">
                                    <span class="material-icons-outlined text-[20px]">stop</span>
                                </button>
                            @endif

                            @if($campaign->isPaused())
                                <button wire:click="resumeCampaign('{{ $campaign->id }}')"
                                        class="p-2 rounded-lg text-mono-300 hover:text-success hover:bg-success/10 transition-colors" title="Retomar">
                                    <span class="material-icons-outlined text-[20px]">play_arrow</span>
                                </button>
                                <button wire:click="cancelCampaign('{{ $campaign->id }}')"
                                        class="p-2 rounded-lg text-mono-300 hover:text-error hover:bg-down-bg transition-colors" title="Cancelar">
                                    <span class="material-icons-outlined text-[20px]">stop</span>
                                </button>
                            @endif

                            <button wire:click="openDetailModal('{{ $campaign->id }}')"
                                    class="p-2 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors" title="Detalhes">
                                <span class="material-icons-outlined text-[20px]">visibility</span>
                            </button>

                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="p-2 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                                    <span class="material-icons-outlined text-[18px]">more_vert</span>
                                </button>
                                <div x-show="open" x-cloak @click.away="open = false"
                                     class="absolute right-0 top-10 bg-mono-white rounded-xl shadow-dropdown border border-mono-100 py-1.5 z-50 w-44">
                                    @if($campaign->isDraft())
                                        <button wire:click="openEditModal('{{ $campaign->id }}')" @click="open = false"
                                                class="flex items-center gap-2 w-full px-3 py-2 text-sm text-mono-900 hover:bg-mono-50">
                                            <span class="material-icons-outlined text-[16px] text-mono-300">edit</span>
                                            Editar
                                        </button>
                                    @endif
                                    <button wire:click="duplicateCampaign('{{ $campaign->id }}')" @click="open = false"
                                            class="flex items-center gap-2 w-full px-3 py-2 text-sm text-mono-900 hover:bg-mono-50">
                                        <span class="material-icons-outlined text-[16px] text-mono-300">content_copy</span>
                                        Duplicar
                                    </button>
                                    @unless($campaign->isSending())
                                        <div class="border-t border-mono-100 my-1"></div>
                                        <button wire:click="confirmDelete('{{ $campaign->id }}')" @click="open = false"
                                                class="flex items-center gap-2 w-full px-3 py-2 text-sm text-error hover:bg-down-bg">
                                            <span class="material-icons-outlined text-[16px]">delete</span>
                                            Excluir
                                        </button>
                                    @endunless
                                </div>
                            </div>
                        </div>
                    </div>
                </x-jr.card>
            @endforeach
        </div>
    @endif

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- Create/Edit Campaign Modal -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    @if($showModal)
        <div class="fixed inset-0 z-modal overflow-y-auto" wire:keydown.escape="$set('showModal', false)">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-lg overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-mono-100">
                        <h3 class="text-lg font-bold text-mono-900">
                            {{ $editingId ? 'Editar Campanha' : 'Nova Campanha' }}
                        </h3>
                        <button wire:click="$set('showModal', false)" class="p-1 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                            <span class="material-icons-outlined text-[20px]">close</span>
                        </button>
                    </div>

                    <form wire:submit="saveCampaign">
                        <div class="px-6 py-5 space-y-4 max-h-[60vh] overflow-y-auto">
                            <x-jr.input label="Nome da Campanha" wire:model="campaignName" placeholder="Ex: Black Friday, Boas-vindas..."
                                        icon="campaign" :error="$errors->first('campaignName')" />

                            <!-- Instance Select -->
                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Instancia WhatsApp</label>
                                <select wire:model="campaignInstanceId"
                                        class="w-full h-12 px-4 rounded-pill border border-mono-200 bg-mono-white text-sm text-mono-900 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors">
                                    <option value="">Selecione uma instancia</option>
                                    @foreach($instances as $inst)
                                        <option value="{{ $inst->id }}">{{ $inst->name }} ({{ $inst->phone ?? $inst->instance_name }})</option>
                                    @endforeach
                                </select>
                                @error('campaignInstanceId')
                                    <p class="text-xs text-error mt-1">{{ $message }}</p>
                                @enderror
                                @if($instances->isEmpty())
                                    <p class="text-xs text-warning-600 mt-1">Nenhuma instancia conectada. Conecte uma instancia primeiro.</p>
                                @endif
                            </div>

                            <!-- Message Source Toggle -->
                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-2">Tipo de Mensagem</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button" wire:click="$set('messageSource', 'template')"
                                            @class([
                                                'flex items-center justify-center gap-2 p-3 rounded-xl border-2 transition-all',
                                                'border-primary-500 bg-primary-50 text-primary-700' => $messageSource === 'template',
                                                'border-mono-200 text-mono-500 hover:border-mono-300' => $messageSource !== 'template',
                                            ])>
                                        <span class="material-icons-outlined text-[20px]">description</span>
                                        <span class="text-sm font-medium">Template</span>
                                    </button>
                                    <button type="button" wire:click="$set('messageSource', 'custom')"
                                            @class([
                                                'flex items-center justify-center gap-2 p-3 rounded-xl border-2 transition-all',
                                                'border-primary-500 bg-primary-50 text-primary-700' => $messageSource === 'custom',
                                                'border-mono-200 text-mono-500 hover:border-mono-300' => $messageSource !== 'custom',
                                            ])>
                                        <span class="material-icons-outlined text-[20px]">edit_note</span>
                                        <span class="text-sm font-medium">Personalizada</span>
                                    </button>
                                </div>
                            </div>

                            @if($messageSource === 'template')
                                <div>
                                    <label class="block text-sm font-medium text-mono-600 mb-1.5">Template</label>
                                    <select wire:model="campaignTemplateId"
                                            class="w-full h-12 px-4 rounded-pill border border-mono-200 bg-mono-white text-sm text-mono-900 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors">
                                        <option value="">Selecione um template</option>
                                        @foreach($templates as $tpl)
                                            <option value="{{ $tpl->id }}">{{ $tpl->name }} ({{ $tpl->category->label() }})</option>
                                        @endforeach
                                    </select>
                                    @error('campaignTemplateId')
                                        <p class="text-xs text-error mt-1">{{ $message }}</p>
                                    @enderror
                                    @if($templates->isEmpty())
                                        <p class="text-xs text-warning-600 mt-1">
                                            Nenhum template ativo. <a href="{{ route('whatsapp.templates') }}" class="underline" wire:navigate>Criar template</a>
                                        </p>
                                    @endif
                                </div>
                            @else
                                <div>
                                    <label class="block text-sm font-medium text-mono-600 mb-1.5">Mensagem</label>
                                    <textarea wire:model="campaignCustomMessage" rows="5" placeholder="Digite sua mensagem...&#10;&#10;Use {nome} para personalizar"
                                              class="w-full px-4 py-3 rounded-xl border border-mono-200 bg-mono-white text-sm text-mono-900 placeholder-mono-300 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors resize-none"></textarea>
                                    @error('campaignCustomMessage')
                                        <p class="text-xs text-error mt-1">{{ $message }}</p>
                                    @enderror
                                    <p class="text-[10px] text-mono-400 mt-1">Variaveis: <code class="bg-mono-100 px-1 rounded">{nome}</code> <code class="bg-mono-100 px-1 rounded">{telefone}</code></p>
                                </div>
                            @endif

                            <!-- Delay -->
                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Intervalo entre mensagens (segundos)</label>
                                <input type="range" wire:model.live="campaignDelay" min="1" max="60" step="1"
                                       class="w-full accent-primary-500">
                                <div class="flex justify-between text-[10px] text-mono-400 mt-1">
                                    <span>1s</span>
                                    <span class="text-sm font-semibold text-primary-500">{{ $campaignDelay }}s</span>
                                    <span>60s</span>
                                </div>
                                <p class="text-[10px] text-mono-400 mt-1">Intervalos maiores reduzem o risco de bloqueio do numero.</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-mono-100 bg-mono-50">
                            <x-jr.button variant="mono" wire:click="$set('showModal', false)" type="button">Cancelar</x-jr.button>
                            <x-jr.button type="submit" wire:loading.attr="disabled">
                                <span wire:loading wire:target="saveCampaign" class="material-icons-outlined text-[18px] animate-spin">autorenew</span>
                                {{ $editingId ? 'Salvar' : 'Criar Campanha' }}
                            </x-jr.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- Recipients Modal -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    @if($showRecipientsModal && $recipientsCampaignId)
        @php $rcCampaign = \App\Models\WhatsAppCampaign::find($recipientsCampaignId); @endphp
        <div class="fixed inset-0 z-modal overflow-y-auto" wire:keydown.escape="$set('showRecipientsModal', false)">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showRecipientsModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-2xl overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-mono-100">
                        <div>
                            <h3 class="text-lg font-bold text-mono-900">Destinatarios</h3>
                            <p class="text-xs text-mono-500">{{ $rcCampaign?->name }} &mdash; {{ $recipients->count() }} destinatario(s)</p>
                        </div>
                        <button wire:click="$set('showRecipientsModal', false)" class="p-1 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                            <span class="material-icons-outlined text-[20px]">close</span>
                        </button>
                    </div>

                    <div class="p-6 max-h-[70vh] overflow-y-auto space-y-4">
                        <!-- Source Tabs -->
                        <div class="flex items-center gap-1 bg-mono-50 rounded-xl p-1">
                            @foreach(['manual' => 'Adicionar Manual', 'contacts' => 'Importar Contatos', 'csv' => 'Colar Numeros'] as $src => $label)
                                <button wire:click="$set('recipientSource', '{{ $src }}')"
                                        @class([
                                            'flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-all text-center',
                                            'bg-mono-white shadow-sm text-mono-900' => $recipientSource === $src,
                                            'text-mono-500 hover:text-mono-700' => $recipientSource !== $src,
                                        ])>
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>

                        <!-- Manual Add -->
                        @if($recipientSource === 'manual')
                            <div class="flex items-end gap-2">
                                <div class="flex-1">
                                    <x-jr.input label="Telefone" wire:model="addPhone" placeholder="5511999998888" icon="phone"
                                                :error="$errors->first('addPhone')" />
                                </div>
                                <div class="flex-1">
                                    <x-jr.input label="Nome (opcional)" wire:model="addName" placeholder="Nome do contato" icon="person" />
                                </div>
                                <x-jr.button wire:click="addRecipientManual" class="mb-0.5">
                                    <span class="material-icons-outlined text-[18px]">add</span>
                                </x-jr.button>
                            </div>
                        @endif

                        <!-- Import from Contacts -->
                        @if($recipientSource === 'contacts')
                            <div>
                                <x-jr.input wire:model.live.debounce.300ms="contactFilter" placeholder="Buscar contatos..." icon="search" />
                                <div class="mt-3 max-h-48 overflow-y-auto space-y-1 border border-mono-100 rounded-xl p-2">
                                    @forelse($availableContacts as $ac)
                                        <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-mono-50 cursor-pointer">
                                            <input type="checkbox" wire:model="selectedContacts" value="{{ $ac->id }}"
                                                   class="w-4 h-4 rounded border-mono-300 text-primary-500 focus:ring-primary-500">
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-mono-900 truncate">{{ $ac->name }}</p>
                                                <p class="text-xs text-mono-500">{{ $ac->phone }}</p>
                                            </div>
                                            @if($ac->company)
                                                <span class="text-xs text-mono-400">{{ $ac->company }}</span>
                                            @endif
                                        </label>
                                    @empty
                                        <p class="text-xs text-mono-400 text-center py-4">
                                            {{ $contactFilter ? 'Nenhum contato encontrado.' : 'Busque um contato pelo nome, telefone ou empresa.' }}
                                        </p>
                                    @endforelse
                                </div>
                                @if(!empty($selectedContacts))
                                    <div class="mt-2">
                                        <x-jr.button wire:click="addContactsAsBulk" size="sm">
                                            <span class="material-icons-outlined text-[16px]">group_add</span>
                                            Adicionar {{ count($selectedContacts) }} contato(s)
                                        </x-jr.button>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- CSV / Paste Phones -->
                        @if($recipientSource === 'csv')
                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Cole os numeros (um por linha)</label>
                                <textarea wire:model="csvPhones" rows="5"
                                          placeholder="5511999998888|Joao&#10;5521988887777|Maria&#10;5531977776666&#10;&#10;Formato: telefone|nome (nome e opcional)"
                                          class="w-full px-4 py-3 rounded-xl border border-mono-200 bg-mono-white text-sm text-mono-900 placeholder-mono-300 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors resize-none font-mono"></textarea>
                                <div class="mt-2">
                                    <x-jr.button wire:click="importCsvPhones" size="sm">
                                        <span class="material-icons-outlined text-[16px]">upload</span>
                                        Importar Numeros
                                    </x-jr.button>
                                </div>
                            </div>
                        @endif

                        <!-- Current Recipients List -->
                        @if($recipients->isNotEmpty())
                            <div class="border-t border-mono-100 pt-4">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="text-sm font-semibold text-mono-900">
                                        Destinatarios ({{ $recipients->count() }})
                                    </h4>
                                    <button wire:click="removeAllRecipients"
                                            wire:confirm="Tem certeza que deseja remover todos os destinatarios?"
                                            class="text-xs text-error hover:underline">
                                        Remover todos
                                    </button>
                                </div>
                                <div class="max-h-48 overflow-y-auto space-y-1">
                                    @foreach($recipients as $rc)
                                        <div class="flex items-center justify-between p-2 rounded-lg bg-mono-50 group">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <span class="material-icons-outlined text-[16px] text-mono-300">person</span>
                                                <span class="text-sm text-mono-900 truncate">{{ $rc->name ?? 'Sem nome' }}</span>
                                                <span class="text-xs text-mono-400">{{ $rc->phone }}</span>
                                            </div>
                                            <button wire:click="removeRecipient('{{ $rc->id }}')"
                                                    class="p-1 rounded text-mono-300 hover:text-error opacity-0 group-hover:opacity-100 transition-all">
                                                <span class="material-icons-outlined text-[16px]">close</span>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="text-center py-6">
                                <span class="material-icons-outlined text-[32px] text-mono-200">group</span>
                                <p class="text-xs text-mono-400 mt-1">Nenhum destinatario adicionado ainda.</p>
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center justify-end px-6 py-4 border-t border-mono-100 bg-mono-50">
                        <x-jr.button wire:click="$set('showRecipientsModal', false)">
                            Fechar
                        </x-jr.button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- Campaign Detail / Progress Modal -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    @if($showDetailModal && $detailCampaign)
        <div class="fixed inset-0 z-modal overflow-y-auto" wire:keydown.escape="$set('showDetailModal', false)">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showDetailModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-2xl overflow-hidden"
                     @if($detailCampaign->isSending()) wire:poll.5s="openDetailModal('{{ $detailCampaign->id }}')" @endif>
                    <div class="flex items-center justify-between px-6 py-4 border-b border-mono-100">
                        <div>
                            <h3 class="text-lg font-bold text-mono-900">{{ $detailCampaign->name }}</h3>
                            <div class="flex items-center gap-2 mt-1">
                                <x-jr.badge variant="{{ $detailCampaign->status->color() }}">
                                    {{ $detailCampaign->status->label() }}
                                </x-jr.badge>
                                @if($detailCampaign->isSending())
                                    <span class="flex items-center gap-1 text-xs text-warning-600">
                                        <span class="material-icons-outlined text-[14px] animate-spin">autorenew</span>
                                        Enviando...
                                    </span>
                                @endif
                            </div>
                        </div>
                        <button wire:click="$set('showDetailModal', false)" class="p-1 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                            <span class="material-icons-outlined text-[20px]">close</span>
                        </button>
                    </div>

                    <div class="p-6 max-h-[70vh] overflow-y-auto space-y-4">
                        <!-- Stats Cards -->
                        <div class="grid grid-cols-4 gap-3">
                            <div class="bg-mono-50 rounded-xl p-3 text-center">
                                <p class="text-xl font-bold text-mono-900">{{ $detailCampaign->total_recipients }}</p>
                                <p class="text-[10px] text-mono-500 uppercase">Total</p>
                            </div>
                            <div class="bg-success/10 rounded-xl p-3 text-center">
                                <p class="text-xl font-bold text-success">{{ $detailCampaign->sent_count }}</p>
                                <p class="text-[10px] text-success uppercase">Enviados</p>
                            </div>
                            <div class="bg-down-bg rounded-xl p-3 text-center">
                                <p class="text-xl font-bold text-error">{{ $detailCampaign->failed_count }}</p>
                                <p class="text-[10px] text-error uppercase">Falhas</p>
                            </div>
                            <div class="bg-info-100 rounded-xl p-3 text-center">
                                <p class="text-xl font-bold text-info-700">{{ $detailCampaign->total_recipients - $detailCampaign->sent_count - $detailCampaign->failed_count }}</p>
                                <p class="text-[10px] text-info-600 uppercase">Pendentes</p>
                            </div>
                        </div>

                        <!-- Progress -->
                        <div class="w-full h-3 bg-mono-100 rounded-full overflow-hidden">
                            @php
                                $sentPct = $detailCampaign->total_recipients > 0 ? round($detailCampaign->sent_count / $detailCampaign->total_recipients * 100) : 0;
                                $failedPct = $detailCampaign->total_recipients > 0 ? round($detailCampaign->failed_count / $detailCampaign->total_recipients * 100) : 0;
                            @endphp
                            <div class="h-full flex">
                                <div class="bg-success h-full transition-all duration-500" style="width: {{ $sentPct }}%"></div>
                                <div class="bg-error h-full transition-all duration-500" style="width: {{ $failedPct }}%"></div>
                            </div>
                        </div>

                        <!-- Details -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-mono-500">Instancia</span>
                                    <span class="text-xs font-medium text-mono-700">{{ $detailCampaign->instance?->name ?? '--' }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-mono-500">Template</span>
                                    <span class="text-xs font-medium text-mono-700">{{ $detailCampaign->template?->name ?? 'Personalizada' }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-mono-500">Intervalo</span>
                                    <span class="text-xs font-medium text-mono-700">{{ $detailCampaign->delay_seconds }}s</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-mono-500">Criada em</span>
                                    <span class="text-xs font-medium text-mono-700">{{ $detailCampaign->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                @if($detailCampaign->started_at)
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-mono-500">Iniciada em</span>
                                        <span class="text-xs font-medium text-mono-700">{{ $detailCampaign->started_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                @endif
                                @if($detailCampaign->completed_at)
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-mono-500">Concluida em</span>
                                        <span class="text-xs font-medium text-mono-700">{{ $detailCampaign->completed_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Recipients Table -->
                        <div class="border-t border-mono-100 pt-4">
                            <h4 class="text-sm font-semibold text-mono-900 mb-3">Destinatarios</h4>
                            <div class="max-h-64 overflow-y-auto">
                                <table class="w-full">
                                    <thead class="sticky top-0 bg-mono-white">
                                        <tr class="border-b border-mono-100">
                                            <th class="text-left px-3 py-2 text-[10px] font-semibold text-mono-500 uppercase">Nome</th>
                                            <th class="text-left px-3 py-2 text-[10px] font-semibold text-mono-500 uppercase">Telefone</th>
                                            <th class="text-center px-3 py-2 text-[10px] font-semibold text-mono-500 uppercase">Status</th>
                                            <th class="text-right px-3 py-2 text-[10px] font-semibold text-mono-500 uppercase">Enviado em</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-mono-50">
                                        @foreach($detailCampaign->recipients as $dr)
                                            <tr class="hover:bg-mono-50">
                                                <td class="px-3 py-2 text-xs text-mono-900">{{ $dr->name ?? '--' }}</td>
                                                <td class="px-3 py-2 text-xs text-mono-600">{{ $dr->phone }}</td>
                                                <td class="px-3 py-2 text-center">
                                                    @if($dr->status === 'sent')
                                                        <span class="inline-flex items-center gap-1 text-[10px] font-medium text-success">
                                                            <span class="material-icons-outlined text-[12px]">check_circle</span>
                                                            Enviado
                                                        </span>
                                                    @elseif($dr->status === 'failed')
                                                        <span class="inline-flex items-center gap-1 text-[10px] font-medium text-error" title="{{ $dr->error_message }}">
                                                            <span class="material-icons-outlined text-[12px]">error</span>
                                                            Falhou
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 text-[10px] font-medium text-mono-400">
                                                            <span class="material-icons-outlined text-[12px]">schedule</span>
                                                            Pendente
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2 text-xs text-mono-500 text-right">
                                                    {{ $dr->sent_at?->format('d/m H:i') ?? '--' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end px-6 py-4 border-t border-mono-100 bg-mono-50">
                        <x-jr.button wire:click="$set('showDetailModal', false)">Fechar</x-jr.button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- Delete Confirmation Modal -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-modal overflow-y-auto">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showDeleteModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-sm overflow-hidden">
                    <div class="px-6 py-5 text-center">
                        <div class="mx-auto w-12 h-12 rounded-full bg-down-bg flex items-center justify-center mb-4">
                            <span class="material-icons-outlined text-[24px] text-error">delete</span>
                        </div>
                        <h3 class="text-lg font-bold text-mono-900">Excluir campanha?</h3>
                        <p class="text-sm text-mono-600 mt-2">A campanha e todos os destinatarios serao removidos permanentemente.</p>
                    </div>
                    <div class="flex items-center justify-center gap-3 px-6 py-4 border-t border-mono-100 bg-mono-50">
                        <x-jr.button variant="mono" wire:click="$set('showDeleteModal', false)">Cancelar</x-jr.button>
                        <x-jr.button variant="danger" wire:click="deleteCampaign">Excluir</x-jr.button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
