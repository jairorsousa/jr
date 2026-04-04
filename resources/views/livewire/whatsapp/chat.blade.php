<div class="flex h-[calc(100vh-8rem)] -mx-6 -mb-6"
     x-data="whatsappChat()"
     x-init="init()"
>
    @if (session('success'))
        <div class="absolute top-0 left-0 right-0 z-50 px-4 pt-2">
            <x-jr.alert variant="success">{{ session('success') }}</x-jr.alert>
        </div>
    @endif
    @if (session('error'))
        <div class="absolute top-0 left-0 right-0 z-50 px-4 pt-2">
            <x-jr.alert variant="error">{{ session('error') }}</x-jr.alert>
        </div>
    @endif

    <audio id="notification-sound" preload="auto">
        <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVggoaFa1lfcZCln5NwVUxhd36AamFjcIqVkn9kTk1fcHl7b2ZnboSNjYN1aWdxfICAfnl5fYKGiIaDf35+gIKCgYGBgoKDg4ODg4ODg4ODg4OC" type="audio/wav">
    </audio>

    <!-- Sidebar: Conversations List -->
    <div class="w-80 lg:w-96 border-r border-mono-100 flex flex-col bg-mono-white flex-shrink-0
                {{ $conversationId ? 'hidden md:flex' : 'flex' }}">
        <!-- Instance Selector -->
        <div class="px-4 py-3 border-b border-mono-100">
            <select wire:model.live="instanceId"
                    class="w-full h-10 px-3 rounded-xl border border-mono-200 bg-mono-white text-sm text-mono-900 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                <option value="">Selecione uma instancia</option>
                @foreach($instances as $inst)
                    <option value="{{ $inst->id }}">
                        {{ $inst->name }}
                        {{ $inst->status->value === 'connected' ? '' : '(Desconectado)' }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Search -->
        <div class="px-4 py-3 border-b border-mono-100">
            <div class="relative">
                <span class="material-icons-outlined text-[18px] text-mono-300 absolute left-3 top-1/2 -translate-y-1/2">search</span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar conversas..."
                       class="w-full h-10 pl-10 pr-4 rounded-xl border border-mono-200 bg-mono-white text-sm text-mono-900 placeholder-mono-300 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
            </div>
        </div>

        <!-- New Chat Button -->
        <div class="px-4 py-2 border-b border-mono-100">
            <button wire:click="$set('showNewChatModal', true)"
                    class="flex items-center gap-2 w-full px-3 py-2 rounded-xl text-sm font-medium text-primary-500 hover:bg-primary-50 transition-colors">
                <span class="material-icons-outlined text-[18px]">add</span>
                Nova Conversa
            </button>
        </div>

        <!-- Conversations List -->
        <div class="flex-1 overflow-y-auto">
            @forelse($conversations as $conv)
                <button wire:click="selectConversation('{{ $conv->id }}')"
                        class="flex items-center gap-3 w-full px-4 py-3 text-left hover:bg-mono-50 transition-colors border-b border-mono-50
                               {{ $conversationId === $conv->id ? 'bg-primary-50 border-l-2 border-l-primary-500' : '' }}">
                    <!-- Avatar -->
                    <div class="relative w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0
                                {{ $conversationId === $conv->id ? 'bg-primary-100' : 'bg-mono-100' }}">
                        @if($conv->profile_pic_url)
                            <img src="{{ $conv->profile_pic_url }}" alt="" class="w-10 h-10 rounded-full object-cover">
                        @else
                            <span class="text-xs font-bold {{ $conversationId === $conv->id ? 'text-primary-600' : 'text-mono-600' }}">
                                {{ $conv->initials() }}
                            </span>
                        @endif
                        {{-- CRM badge --}}
                        @if($conv->contact_id)
                            <span class="absolute -bottom-0.5 -right-0.5 w-4 h-4 rounded-full bg-success flex items-center justify-center border-2 border-mono-white">
                                <span class="material-icons-outlined text-[8px] text-white">person</span>
                            </span>
                        @endif
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <span class="text-sm font-medium text-mono-900 truncate">{{ $conv->contact?->name ?? $conv->displayName() }}</span>
                                @if($conv->contact?->company)
                                    <span class="text-[10px] text-mono-300 truncate hidden lg:inline">{{ $conv->contact->company }}</span>
                                @endif
                            </div>
                            @if($conv->last_message_at)
                                <span class="text-[10px] text-mono-300 flex-shrink-0 ml-2">
                                    {{ $conv->last_message_at->isToday() ? $conv->last_message_at->format('H:i') : $conv->last_message_at->format('d/m') }}
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between mt-0.5">
                            <p class="text-xs text-mono-600 truncate">{{ $conv->last_message ?? 'Sem mensagens' }}</p>
                            @if($conv->unread_count > 0)
                                <span class="flex-shrink-0 ml-2 w-5 h-5 rounded-full bg-primary-500 text-white text-[10px] font-bold flex items-center justify-center">
                                    {{ $conv->unread_count > 99 ? '99+' : $conv->unread_count }}
                                </span>
                            @endif
                        </div>
                    </div>
                </button>
            @empty
                <div class="flex flex-col items-center justify-center h-full text-center px-4">
                    <span class="material-icons-outlined text-[40px] text-mono-200">forum</span>
                    <p class="text-sm text-mono-300 mt-2">
                        @if($search)
                            Nenhuma conversa encontrada.
                        @elseif($instanceId)
                            Nenhuma conversa ainda.
                        @else
                            Selecione uma instancia.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Chat Area -->
    <div class="flex-1 flex flex-col bg-mono-50 min-w-0 {{ !$conversationId ? 'hidden md:flex' : 'flex' }}">
        @if($activeConversation)
            <!-- Chat Header -->
            <div class="flex items-center gap-3 px-4 py-3 bg-mono-white border-b border-mono-100">
                <button wire:click="$set('conversationId', null)" class="md:hidden p-1 rounded-lg text-mono-300 hover:text-mono-600">
                    <span class="material-icons-outlined text-[20px]">arrow_back</span>
                </button>

                <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0">
                    @if($activeConversation->profile_pic_url)
                        <img src="{{ $activeConversation->profile_pic_url }}" alt="" class="w-10 h-10 rounded-full object-cover">
                    @else
                        <span class="text-xs font-bold text-primary-600">{{ $activeConversation->initials() }}</span>
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-bold text-mono-900 truncate">
                        {{ $linkedContact?->name ?? $activeConversation->displayName() }}
                    </h3>
                    <div class="flex items-center gap-2">
                        <p class="text-xs text-mono-300">{{ $activeConversation->contact_phone }}</p>
                        @if($linkedContact)
                            <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-success/10 text-success font-medium">CRM</span>
                        @endif
                        @if($linkedDeal)
                            <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-primary-100 text-primary-600 font-medium truncate max-w-[120px]">{{ $linkedDeal->title }}</span>
                        @endif
                    </div>
                </div>

                <!-- CRM Toggle -->
                <button wire:click="toggleCrmPanel"
                        class="p-2 rounded-lg transition-colors {{ $showCrmPanel ? 'text-primary-500 bg-primary-50' : 'text-mono-300 hover:text-mono-600 hover:bg-mono-50' }}"
                        title="Painel CRM">
                    <span class="material-icons-outlined text-[20px]">contact_page</span>
                </button>

                <!-- Actions -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="p-2 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                        <span class="material-icons-outlined text-[20px]">more_vert</span>
                    </button>
                    <div x-show="open" x-cloak @click.away="open = false"
                         class="absolute right-0 top-10 bg-mono-white rounded-xl shadow-dropdown border border-mono-100 py-1.5 z-50 w-48">
                        @unless($linkedContact)
                            <button wire:click="autoLinkContact" @click="open = false"
                                    class="flex items-center gap-2 w-full px-3 py-2 text-sm text-mono-900 hover:bg-mono-50">
                                <span class="material-icons-outlined text-[16px] text-mono-300">person_search</span>
                                Auto-vincular contato
                            </button>
                        @endunless
                        <button wire:click="openLinkContactModal" @click="open = false"
                                class="flex items-center gap-2 w-full px-3 py-2 text-sm text-mono-900 hover:bg-mono-50">
                            <span class="material-icons-outlined text-[16px] text-mono-300">person_add</span>
                            {{ $linkedContact ? 'Trocar contato' : 'Vincular contato' }}
                        </button>
                        <button wire:click="openLinkDealModal" @click="open = false"
                                class="flex items-center gap-2 w-full px-3 py-2 text-sm text-mono-900 hover:bg-mono-50">
                            <span class="material-icons-outlined text-[16px] text-mono-300">handshake</span>
                            {{ $linkedDeal ? 'Trocar negocio' : 'Vincular negocio' }}
                        </button>
                        <div class="border-t border-mono-100 my-1"></div>
                        <button wire:click="deleteConversation('{{ $activeConversation->id }}')" @click="open = false"
                                class="flex items-center gap-2 w-full px-3 py-2 text-sm text-error hover:bg-down-bg">
                            <span class="material-icons-outlined text-[16px]">delete</span>
                            Excluir conversa
                        </button>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            <div class="flex-1 overflow-y-auto px-4 py-4 space-y-2" id="chat-messages"
                 x-data x-init="$nextTick(() => { $el.scrollTop = $el.scrollHeight })"
                 x-effect="$wire.conversationId; $nextTick(() => { $el.scrollTop = $el.scrollHeight })">
                @php $lastDate = null; @endphp
                @foreach($messages as $msg)
                    @php
                        $msgDate = $msg->message_at->format('Y-m-d');
                        $showDate = $msgDate !== $lastDate;
                        $lastDate = $msgDate;
                    @endphp

                    @if($showDate)
                        <div class="flex justify-center my-3">
                            <span class="px-3 py-1 rounded-full bg-mono-200/50 text-[11px] font-medium text-mono-600">
                                {{ $msg->message_at->isToday() ? 'Hoje' : ($msg->message_at->isYesterday() ? 'Ontem' : $msg->message_at->format('d/m/Y')) }}
                            </span>
                        </div>
                    @endif

                    <div class="flex {{ $msg->from_me ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[75%] lg:max-w-[60%] rounded-2xl px-4 py-2 shadow-sm
                                    {{ $msg->from_me ? 'bg-primary-500 text-white rounded-br-md' : 'bg-mono-white text-mono-900 rounded-bl-md' }}">

                            @if($msg->type->value === 'image' && $msg->media_url)
                                <a href="{{ $msg->media_url }}" target="_blank" class="block">
                                    <img src="{{ $msg->media_url }}" alt="Imagem" class="rounded-lg mb-1 max-w-full max-h-64 cursor-pointer hover:opacity-90 transition-opacity">
                                </a>
                            @elseif($msg->type->value === 'audio')
                                @if($msg->media_url)
                                    <audio controls class="max-w-[240px]">
                                        <source src="{{ $msg->media_url }}" type="{{ $msg->media_mimetype ?? 'audio/mpeg' }}">
                                    </audio>
                                @else
                                    <div class="flex items-center gap-2">
                                        <span class="material-icons-outlined text-[20px]">mic</span>
                                        <span class="text-sm">Audio</span>
                                    </div>
                                @endif
                            @elseif($msg->type->value === 'video')
                                @if($msg->media_url)
                                    <video controls class="rounded-lg mb-1 max-w-full max-h-64">
                                        <source src="{{ $msg->media_url }}" type="{{ $msg->media_mimetype ?? 'video/mp4' }}">
                                    </video>
                                @else
                                    <div class="flex items-center gap-2">
                                        <span class="material-icons-outlined text-[20px]">videocam</span>
                                        <span class="text-sm">Video</span>
                                    </div>
                                @endif
                            @elseif($msg->type->value === 'document')
                                <a href="{{ $msg->media_url }}" target="_blank"
                                   class="flex items-center gap-3 {{ $msg->from_me ? 'bg-primary-600/80 hover:bg-primary-600' : 'bg-mono-50 hover:bg-mono-100' }} rounded-xl px-3 py-2.5 mb-1 transition-colors">
                                    @php
                                        $ext = strtolower(pathinfo($msg->media_filename ?? '', PATHINFO_EXTENSION));
                                        $docIcon = match($ext) {
                                            'pdf' => 'picture_as_pdf',
                                            'doc', 'docx' => 'article',
                                            'xls', 'xlsx', 'csv' => 'table_chart',
                                            'zip', 'rar' => 'folder_zip',
                                            'txt' => 'text_snippet',
                                            default => 'description',
                                        };
                                        $docColor = match($ext) {
                                            'pdf' => 'text-red-400',
                                            'doc', 'docx' => 'text-blue-400',
                                            'xls', 'xlsx', 'csv' => 'text-green-400',
                                            default => $msg->from_me ? 'text-white/70' : 'text-mono-400',
                                        };
                                    @endphp
                                    <span class="material-icons-outlined text-[24px] {{ $docColor }}">{{ $docIcon }}</span>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium truncate">{{ $msg->media_filename ?? 'Documento' }}</p>
                                        <p class="text-[10px] {{ $msg->from_me ? 'text-white/50' : 'text-mono-300' }}">
                                            {{ strtoupper($ext) ?: 'DOC' }}
                                            &bull; Clique para abrir
                                        </p>
                                    </div>
                                    <span class="material-icons-outlined text-[18px] {{ $msg->from_me ? 'text-white/50' : 'text-mono-300' }}">download</span>
                                </a>
                            @elseif($msg->type->value === 'sticker')
                                @if($msg->media_url)
                                    <img src="{{ $msg->media_url }}" alt="Sticker" class="w-32 h-32">
                                @else
                                    <span class="material-icons-outlined text-[40px]">emoji_emotions</span>
                                @endif
                            @endif

                            @if($msg->body)
                                <p class="text-sm whitespace-pre-wrap break-words">{{ $msg->body }}</p>
                            @endif

                            <div class="flex items-center justify-end gap-1 mt-1">
                                <span class="text-[10px] {{ $msg->from_me ? 'text-white/60' : 'text-mono-300' }}">
                                    {{ $msg->message_at->format('H:i') }}
                                </span>
                                @if($msg->from_me)
                                    <span class="material-icons-outlined text-[14px] {{ $msg->status->value === 'read' ? 'text-blue-200' : 'text-white/60' }}">
                                        {{ $msg->status->icon() }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Media Preview Bar -->
            @if($showMediaPreview && $mediaFile)
                <div class="px-4 py-3 bg-mono-white border-t border-mono-100">
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-mono-50 border border-mono-200">
                        <!-- Preview Thumbnail -->
                        <div class="flex-shrink-0">
                            @php
                                $mime = $mediaFile->getMimeType();
                                $isImage = str_starts_with($mime, 'image/');
                                $isVideo = str_starts_with($mime, 'video/');
                                $isAudio = str_starts_with($mime, 'audio/');
                            @endphp
                            @if($isImage)
                                <div class="w-16 h-16 rounded-lg overflow-hidden bg-mono-100">
                                    <img src="{{ $mediaFile->temporaryUrl() }}" alt="Preview" class="w-full h-full object-cover">
                                </div>
                            @elseif($isVideo)
                                <div class="w-16 h-16 rounded-lg bg-mono-200 flex items-center justify-center">
                                    <span class="material-icons-outlined text-[28px] text-mono-500">videocam</span>
                                </div>
                            @elseif($isAudio)
                                <div class="w-16 h-16 rounded-lg bg-mono-200 flex items-center justify-center">
                                    <span class="material-icons-outlined text-[28px] text-mono-500">mic</span>
                                </div>
                            @else
                                <div class="w-16 h-16 rounded-lg bg-mono-200 flex items-center justify-center">
                                    <span class="material-icons-outlined text-[28px] text-mono-500">description</span>
                                </div>
                            @endif
                        </div>

                        <!-- File Info & Caption -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <p class="text-xs font-medium text-mono-900 truncate">{{ $mediaFile->getClientOriginalName() }}</p>
                                <button wire:click="cancelMedia" class="p-1 rounded-lg text-mono-300 hover:text-error transition-colors flex-shrink-0">
                                    <span class="material-icons-outlined text-[18px]">close</span>
                                </button>
                            </div>
                            <p class="text-[10px] text-mono-400 mb-2">
                                {{ strtoupper(pathinfo($mediaFile->getClientOriginalName(), PATHINFO_EXTENSION)) }}
                                &bull; {{ number_format($mediaFile->getSize() / 1024, 0) }} KB
                            </p>
                            @if(!str_starts_with($mime, 'audio/'))
                                <input type="text" wire:model="mediaCaption"
                                       placeholder="Adicionar legenda (opcional)..."
                                       class="w-full px-3 py-1.5 rounded-lg border border-mono-200 bg-mono-white text-xs text-mono-900 placeholder-mono-300 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                            @endif
                        </div>

                        <!-- Send Button -->
                        <button wire:click="sendMedia"
                                class="flex-shrink-0 w-10 h-10 rounded-full bg-primary-500 text-white flex items-center justify-center hover:bg-primary-600 transition-colors shadow-sm self-end"
                                wire:loading.attr="disabled" wire:target="sendMedia">
                            <span wire:loading.remove wire:target="sendMedia" class="material-icons-outlined text-[20px]">send</span>
                            <span wire:loading wire:target="sendMedia" class="material-icons-outlined text-[20px] animate-spin">autorenew</span>
                        </button>
                    </div>
                    @error('mediaFile')
                        <p class="text-xs text-error mt-1 px-1">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <!-- Message Input -->
            <div class="px-4 py-3 bg-mono-white border-t border-mono-100" x-data="mediaUpload()">
                <form wire:submit="sendMessage" class="flex items-end gap-2">
                    <!-- Attachment Button -->
                    <div class="relative flex-shrink-0">
                        <button type="button" @click="$refs.fileInput.click()"
                                class="w-11 h-11 rounded-full flex items-center justify-center text-mono-400 hover:text-primary-500 hover:bg-primary-50 transition-colors"
                                title="Anexar arquivo"
                                wire:loading.attr="disabled" wire:target="mediaFile">
                            <span wire:loading.remove wire:target="mediaFile" class="material-icons-outlined text-[22px]">attach_file</span>
                            <span wire:loading wire:target="mediaFile" class="material-icons-outlined text-[22px] animate-spin text-primary-500">autorenew</span>
                        </button>
                        <input type="file" x-ref="fileInput" wire:model="mediaFile" class="hidden"
                               accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.zip,.rar,.txt,.csv">
                    </div>

                    <!-- Text Input -->
                    <div class="flex-1 relative">
                        <textarea wire:model="messageText"
                                  rows="1"
                                  placeholder="Digite uma mensagem..."
                                  class="w-full px-4 py-3 rounded-2xl border border-mono-200 bg-mono-50 text-sm text-mono-900 placeholder-mono-300 resize-none focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                  x-on:keydown.enter.prevent="if (!$event.shiftKey) { $wire.sendMessage() }"
                                  x-on:input="$el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 120) + 'px'"
                        ></textarea>
                    </div>

                    <!-- Send Button -->
                    <button type="submit"
                            class="flex-shrink-0 w-11 h-11 rounded-full bg-primary-500 text-white flex items-center justify-center hover:bg-primary-600 transition-colors shadow-sm"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="sendMessage" class="material-icons-outlined text-[20px]">send</span>
                        <span wire:loading wire:target="sendMessage" class="material-icons-outlined text-[20px] animate-spin">autorenew</span>
                    </button>
                </form>
            </div>
        @else
            <!-- No conversation selected -->
            <div class="flex-1 flex flex-col items-center justify-center text-center px-4">
                <div class="w-20 h-20 rounded-full bg-mono-100 flex items-center justify-center mb-4">
                    <span class="material-icons-outlined text-[40px] text-mono-200">chat</span>
                </div>
                <h3 class="text-lg font-bold text-mono-900">WhatsApp</h3>
                <p class="text-sm text-mono-300 mt-1 max-w-sm">
                    Selecione uma conversa para comecar a trocar mensagens ou inicie uma nova conversa.
                </p>
            </div>
        @endif
    </div>

    <!-- CRM Panel (Right sidebar) -->
    @if($showCrmPanel && $activeConversation)
        <div class="w-80 border-l border-mono-100 bg-mono-white flex-shrink-0 overflow-y-auto hidden lg:block">
            <div class="px-4 py-3 border-b border-mono-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-mono-900">Painel CRM</h3>
                <button wire:click="$set('showCrmPanel', false)" class="p-1 rounded-lg text-mono-300 hover:text-mono-600">
                    <span class="material-icons-outlined text-[18px]">close</span>
                </button>
            </div>

            <!-- Linked Contact -->
            <div class="px-4 py-4 border-b border-mono-100">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-[10px] font-semibold text-mono-300 uppercase tracking-wider">Contato</p>
                    @if($linkedContact)
                        <button wire:click="unlinkContact" class="text-[10px] text-error hover:underline">Desvincular</button>
                    @endif
                </div>

                @if($linkedContact)
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0">
                            <span class="text-xs font-bold text-primary-600">{{ strtoupper(substr($linkedContact->name, 0, 2)) }}</span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-mono-900 truncate">{{ $linkedContact->name }}</p>
                            @if($linkedContact->company)
                                <p class="text-xs text-mono-300 truncate">{{ $linkedContact->company }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        @if($linkedContact->email)
                            <div class="flex items-center gap-2 text-xs text-mono-600">
                                <span class="material-icons-outlined text-[14px] text-mono-300">email</span>
                                {{ $linkedContact->email }}
                            </div>
                        @endif
                        @if($linkedContact->phone)
                            <div class="flex items-center gap-2 text-xs text-mono-600">
                                <span class="material-icons-outlined text-[14px] text-mono-300">phone</span>
                                {{ $linkedContact->phone }}
                            </div>
                        @endif
                    </div>
                    <a href="{{ route('crm.contatos') }}"
                       class="flex items-center gap-1 mt-3 text-xs text-primary-500 hover:underline">
                        <span class="material-icons-outlined text-[14px]">open_in_new</span>
                        Ver no CRM
                    </a>
                @else
                    <div class="text-center py-3">
                        <span class="material-icons-outlined text-[32px] text-mono-200">person_off</span>
                        <p class="text-xs text-mono-300 mt-1">Nenhum contato vinculado</p>
                        <div class="flex flex-col gap-2 mt-3">
                            <button wire:click="autoLinkContact"
                                    class="flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium bg-primary-50 text-primary-500 hover:bg-primary-100 transition-colors">
                                <span class="material-icons-outlined text-[14px]">person_search</span>
                                Auto-vincular
                            </button>
                            <button wire:click="openLinkContactModal"
                                    class="flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium bg-mono-100 text-mono-600 hover:bg-mono-200 transition-colors">
                                <span class="material-icons-outlined text-[14px]">search</span>
                                Buscar contato
                            </button>
                            <button wire:click="openCreateContactModal"
                                    class="flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium bg-mono-100 text-mono-600 hover:bg-mono-200 transition-colors">
                                <span class="material-icons-outlined text-[14px]">person_add</span>
                                Criar contato
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Linked Deal -->
            <div class="px-4 py-4 border-b border-mono-100">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-[10px] font-semibold text-mono-300 uppercase tracking-wider">Negocio</p>
                    @if($linkedDeal)
                        <button wire:click="unlinkDeal" class="text-[10px] text-error hover:underline">Desvincular</button>
                    @endif
                </div>

                @if($linkedDeal)
                    <div class="bg-mono-50 rounded-xl p-3 space-y-2">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-mono-900 truncate">{{ $linkedDeal->title }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-jr.badge variant="{{ $linkedDeal->stage->color() }}" size="sm">
                                {{ $linkedDeal->stage->label() }}
                            </x-jr.badge>
                            <span class="text-xs font-bold text-mono-900">R$ {{ number_format($linkedDeal->value, 2, ',', '.') }}</span>
                        </div>
                        @if($linkedDeal->expected_close_date)
                            <div class="flex items-center gap-1 text-xs text-mono-300">
                                <span class="material-icons-outlined text-[14px]">event</span>
                                Previsao: {{ $linkedDeal->expected_close_date->format('d/m/Y') }}
                            </div>
                        @endif
                        <a href="{{ route('crm.negocio', $linkedDeal->id) }}"
                           class="flex items-center gap-1 text-xs text-primary-500 hover:underline">
                            <span class="material-icons-outlined text-[14px]">open_in_new</span>
                            Ver negocio
                        </a>
                    </div>
                @else
                    <div class="text-center py-3">
                        <span class="material-icons-outlined text-[32px] text-mono-200">handshake</span>
                        <p class="text-xs text-mono-300 mt-1">Nenhum negocio vinculado</p>
                        <button wire:click="openLinkDealModal"
                                class="flex items-center justify-center gap-1.5 mx-auto mt-3 px-3 py-1.5 rounded-xl text-xs font-medium bg-primary-50 text-primary-500 hover:bg-primary-100 transition-colors">
                            <span class="material-icons-outlined text-[14px]">link</span>
                            Vincular negocio
                        </button>
                    </div>
                @endif
            </div>

            <!-- Contact Deals List -->
            @if($linkedContact && $contactDeals->isNotEmpty())
                <div class="px-4 py-4">
                    <p class="text-[10px] font-semibold text-mono-300 uppercase tracking-wider mb-3">Negocios do contato</p>
                    <div class="space-y-2">
                        @foreach($contactDeals as $deal)
                            <a href="{{ route('crm.negocio', $deal->id) }}"
                               class="flex items-center justify-between p-2.5 rounded-xl bg-mono-50 hover:bg-mono-100 transition-colors">
                                <div class="min-w-0">
                                    <p class="text-xs font-medium text-mono-900 truncate">{{ $deal->title }}</p>
                                    <x-jr.badge variant="{{ $deal->stage->color() }}" size="sm">
                                        {{ $deal->stage->label() }}
                                    </x-jr.badge>
                                </div>
                                <span class="text-xs font-bold text-mono-600 flex-shrink-0 ml-2">
                                    R$ {{ number_format($deal->value, 2, ',', '.') }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Real-time connection indicator -->
    <div x-show="echoConnected" x-cloak
         class="absolute bottom-4 left-4 z-50 flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-success/10 text-success text-[10px] font-medium">
        <span class="w-1.5 h-1.5 rounded-full bg-success animate-pulse"></span>
        Tempo real
    </div>

    <!-- New Chat Modal -->
    @if($showNewChatModal)
        <div class="fixed inset-0 z-modal overflow-y-auto" wire:keydown.escape="$set('showNewChatModal', false)">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showNewChatModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-md overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-mono-100">
                        <h3 class="text-lg font-bold text-mono-900">Nova Conversa</h3>
                        <button wire:click="$set('showNewChatModal', false)" class="p-1 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                            <span class="material-icons-outlined text-[20px]">close</span>
                        </button>
                    </div>
                    <form wire:submit="startNewChat">
                        <div class="px-6 py-5 space-y-4">
                            <x-jr.input label="Numero de Telefone" wire:model="newChatPhone"
                                        placeholder="5511999999999 (com codigo do pais)"
                                        icon="phone" />
                            <p class="text-xs text-mono-300 -mt-2 px-1">
                                Informe o numero completo com codigo do pais (ex: 5511999999999).
                            </p>
                            <x-jr.input label="Nome (opcional)" wire:model="newChatName"
                                        placeholder="Nome do contato"
                                        icon="person" />
                        </div>
                        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-mono-100 bg-mono-50">
                            <x-jr.button variant="mono" wire:click="$set('showNewChatModal', false)" type="button">Cancelar</x-jr.button>
                            <x-jr.button type="submit">Iniciar Conversa</x-jr.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Link Contact Modal -->
    @if($showLinkContactModal)
        <div class="fixed inset-0 z-modal overflow-y-auto" wire:keydown.escape="$set('showLinkContactModal', false)">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showLinkContactModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-lg overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-mono-100">
                        <h3 class="text-lg font-bold text-mono-900">Vincular Contato</h3>
                        <button wire:click="$set('showLinkContactModal', false)" class="p-1 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                            <span class="material-icons-outlined text-[20px]">close</span>
                        </button>
                    </div>
                    <div class="px-6 py-4">
                        <x-jr.input wire:model.live.debounce.300ms="linkContactSearch" placeholder="Buscar por nome, telefone, email..." icon="search" />
                    </div>
                    <div class="px-6 pb-4 max-h-80 overflow-y-auto space-y-1">
                        @forelse($linkContacts as $c)
                            <button wire:click="linkContact('{{ $c->id }}')"
                                    class="flex items-center gap-3 w-full p-3 rounded-xl text-left hover:bg-mono-50 transition-colors">
                                <div class="w-9 h-9 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0">
                                    <span class="text-xs font-bold text-primary-600">{{ strtoupper(substr($c->name, 0, 2)) }}</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-mono-900 truncate">{{ $c->name }}</p>
                                    <p class="text-xs text-mono-300 truncate">{{ $c->phone ?? $c->email ?? $c->company ?? '--' }}</p>
                                </div>
                            </button>
                        @empty
                            <div class="text-center py-6">
                                <span class="material-icons-outlined text-[32px] text-mono-200">person_off</span>
                                <p class="text-xs text-mono-300 mt-2">
                                    {{ $linkContactSearch ? 'Nenhum contato encontrado.' : 'Digite para buscar contatos.' }}
                                </p>
                            </div>
                        @endforelse
                    </div>
                    <div class="px-6 py-3 border-t border-mono-100 bg-mono-50">
                        <button wire:click="openCreateContactModal"
                                class="flex items-center gap-2 w-full px-3 py-2 rounded-xl text-sm font-medium text-primary-500 hover:bg-primary-50 transition-colors">
                            <span class="material-icons-outlined text-[18px]">person_add</span>
                            Criar novo contato
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Create Contact Modal -->
    @if($showCreateContactModal)
        <div class="fixed inset-0 z-modal overflow-y-auto" wire:keydown.escape="$set('showCreateContactModal', false)">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showCreateContactModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-md overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-mono-100">
                        <h3 class="text-lg font-bold text-mono-900">Criar Contato</h3>
                        <button wire:click="$set('showCreateContactModal', false)" class="p-1 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                            <span class="material-icons-outlined text-[20px]">close</span>
                        </button>
                    </div>
                    <form wire:submit="createAndLinkContact">
                        <div class="px-6 py-5 space-y-4">
                            <x-jr.input label="Nome" wire:model="newContactName" placeholder="Nome do contato"
                                        icon="person" :error="$errors->first('newContactName')" />
                            <x-jr.input label="Email" wire:model="newContactEmail" type="email" placeholder="email@exemplo.com"
                                        icon="email" :error="$errors->first('newContactEmail')" />
                            <x-jr.input label="Empresa" wire:model="newContactCompany" placeholder="Empresa"
                                        icon="business" :error="$errors->first('newContactCompany')" />
                            <div class="flex items-center gap-2 px-1 text-xs text-mono-300">
                                <span class="material-icons-outlined text-[14px]">info</span>
                                O telefone sera preenchido automaticamente com o numero da conversa.
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-mono-100 bg-mono-50">
                            <x-jr.button variant="mono" wire:click="$set('showCreateContactModal', false)" type="button">Cancelar</x-jr.button>
                            <x-jr.button type="submit">Criar e Vincular</x-jr.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Link Deal Modal -->
    @if($showLinkDealModal)
        <div class="fixed inset-0 z-modal overflow-y-auto" wire:keydown.escape="$set('showLinkDealModal', false)">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showLinkDealModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-lg overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-mono-100">
                        <h3 class="text-lg font-bold text-mono-900">Vincular Negocio</h3>
                        <button wire:click="$set('showLinkDealModal', false)" class="p-1 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                            <span class="material-icons-outlined text-[20px]">close</span>
                        </button>
                    </div>
                    <div class="px-6 py-4">
                        <x-jr.input wire:model.live.debounce.300ms="linkDealSearch" placeholder="Buscar por titulo ou contato..." icon="search" />
                    </div>
                    <div class="px-6 pb-4 max-h-80 overflow-y-auto space-y-1">
                        @forelse($linkDeals as $d)
                            <button wire:click="linkDeal('{{ $d->id }}')"
                                    class="flex items-center justify-between w-full p-3 rounded-xl text-left hover:bg-mono-50 transition-colors">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-mono-900 truncate">{{ $d->title }}</p>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <x-jr.badge variant="{{ $d->stage->color() }}" size="sm">{{ $d->stage->label() }}</x-jr.badge>
                                        <span class="text-xs text-mono-300 truncate">{{ $d->contact?->name ?? '--' }}</span>
                                    </div>
                                </div>
                                <span class="text-sm font-bold text-mono-900 flex-shrink-0 ml-3">
                                    R$ {{ number_format($d->value, 2, ',', '.') }}
                                </span>
                            </button>
                        @empty
                            <div class="text-center py-6">
                                <span class="material-icons-outlined text-[32px] text-mono-200">handshake</span>
                                <p class="text-xs text-mono-300 mt-2">
                                    {{ $linkDealSearch ? 'Nenhum negocio encontrado.' : 'Digite para buscar negocios abertos.' }}
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@script
<script>
    Alpine.data('mediaUpload', () => ({
        // Simple wrapper - file input is handled by Livewire
    }));

    Alpine.data('whatsappChat', () => ({
        echoConnected: false,
        instanceChannel: null,
        chatChannel: null,

        init() {
            if (typeof window.Echo === 'undefined') {
                console.warn('Laravel Echo not available');
                return;
            }

            this.echoConnected = true;

            this.$watch('$wire.instanceId', (instanceId) => {
                this.subscribeInstance(instanceId);
            });

            this.$watch('$wire.conversationId', (conversationId) => {
                this.subscribeChat(conversationId);
            });

            if (this.$wire.instanceId) {
                this.subscribeInstance(this.$wire.instanceId);
            }
            if (this.$wire.conversationId) {
                this.subscribeChat(this.$wire.conversationId);
            }
        },

        subscribeInstance(instanceId) {
            if (this.instanceChannel) {
                window.Echo.leave(this.instanceChannel);
            }
            if (!instanceId) return;

            this.instanceChannel = `whatsapp.instance.${instanceId}`;
            window.Echo.channel(this.instanceChannel)
                .listen('.message.new', (e) => {
                    this.$wire.dispatch('echo-message-received', { data: e });
                    if (!e.message.from_me) {
                        this.playNotification();
                    }
                });
        },

        subscribeChat(conversationId) {
            if (this.chatChannel) {
                window.Echo.leave(this.chatChannel);
            }
            if (!conversationId) return;

            this.chatChannel = `whatsapp.chat.${conversationId}`;
            window.Echo.channel(this.chatChannel)
                .listen('.message.new', (e) => {
                    this.$wire.dispatch('echo-message-received', { data: e });
                    this.$nextTick(() => {
                        const container = document.getElementById('chat-messages');
                        if (container) container.scrollTop = container.scrollHeight;
                    });
                })
                .listen('.message.status', (e) => {
                    this.$wire.dispatch('echo-status-updated', { data: e });
                });
        },

        playNotification() {
            try {
                const audio = document.getElementById('notification-sound');
                if (audio) { audio.currentTime = 0; audio.play().catch(() => {}); }
            } catch (e) {}
        },

        destroy() {
            if (this.instanceChannel) window.Echo.leave(this.instanceChannel);
            if (this.chatChannel) window.Echo.leave(this.chatChannel);
        }
    }));
</script>
@endscript
