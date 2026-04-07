<div class="flex h-[calc(100vh-5rem)] -mx-6 -mt-6 -mb-6 relative"
     x-data="whatsappChat()"
     x-init="init()"
>
    {{-- ══ Flash Messages ══ --}}
    @if (session('success'))
        <div class="absolute top-3 left-1/2 -translate-x-1/2 z-[60] w-full max-w-md px-4">
            <x-jr.alert variant="success">{{ session('success') }}</x-jr.alert>
        </div>
    @endif
    @if (session('error'))
        <div class="absolute top-3 left-1/2 -translate-x-1/2 z-[60] w-full max-w-md px-4">
            <x-jr.alert variant="error">{{ session('error') }}</x-jr.alert>
        </div>
    @endif

    <audio id="notification-sound" preload="auto">
        <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVggoaFa1lfcZCln5NwVUxhd36AamFjcIqVkn9kTk1fcHl7b2ZnboSNjYN1aWdxfICAfnl5fYKGiIaDf35+gIKCgYGBgoKDg4ODg4ODg4ODg4OC" type="audio/wav">
    </audio>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- COLUNA 1 — CONVERSAS (360px)                                 --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <aside class="w-[360px] flex flex-col bg-mono-white border-r border-mono-100/80 flex-shrink-0
                  {{ $conversationId ? 'hidden md:flex' : 'flex' }}">

        {{-- Cabecalho --}}
        <div class="px-5 pt-5 pb-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-mono-900 tracking-tight">Conversas</h2>
                <button wire:click="$set('showNewChatModal', true)"
                        class="w-9 h-9 rounded-xl bg-primary-500 text-white flex items-center justify-center hover:bg-primary-600 transition-all shadow-sm hover:shadow-md active:scale-95">
                    <span class="material-icons-outlined text-[18px]">add</span>
                </button>
            </div>

            {{-- Seletor de instancia --}}
            <div class="relative mb-3">
                <span class="material-icons-outlined text-[16px] text-mono-300 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">smartphone</span>
                <select wire:model.live="instanceId"
                        class="w-full h-10 pl-9 pr-8 rounded-xl border border-mono-200/80 bg-mono-50/50 text-sm text-mono-900 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 appearance-none transition-all cursor-pointer hover:border-mono-300">
                    <option value="">Selecione uma instancia</option>
                    @foreach($instances as $inst)
                        <option value="{{ $inst->id }}">
                            {{ $inst->name }}
                            {{ $inst->status->value === 'connected' ? '' : '(Offline)' }}
                        </option>
                    @endforeach
                </select>
                <span class="material-icons-outlined text-[16px] text-mono-300 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">expand_more</span>
            </div>

            {{-- Busca --}}
            <div class="relative">
                <span class="material-icons-outlined text-[16px] text-mono-300 absolute left-3 top-1/2 -translate-y-1/2">search</span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar conversas..."
                       class="w-full h-10 pl-9 pr-4 rounded-xl border border-mono-200/80 bg-mono-50/50 text-sm text-mono-900 placeholder-mono-300 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all">
            </div>
        </div>

        {{-- Lista de conversas --}}
        <div class="flex-1 overflow-y-auto overscroll-contain">
            @forelse($conversations as $conv)
                @php $isActive = $conversationId === $conv->id; @endphp
                <button wire:click="selectConversation('{{ $conv->id }}')"
                        @class([
                            'flex items-center gap-3.5 w-full px-5 py-3.5 text-left transition-all duration-150 relative group',
                            'bg-primary-50/70 after:absolute after:left-0 after:top-2 after:bottom-2 after:w-[3px] after:rounded-r-full after:bg-primary-500' => $isActive,
                            'hover:bg-mono-50/80' => !$isActive,
                        ])>
                    {{-- Avatar --}}
                    <div class="relative flex-shrink-0">
                        <div @class([
                            'w-12 h-12 rounded-full flex items-center justify-center ring-2 transition-all',
                            'ring-primary-500/30 bg-primary-100' => $isActive,
                            'ring-transparent bg-mono-100 group-hover:ring-mono-200' => !$isActive,
                        ])>
                            @if($conv->profile_pic_url)
                                <img src="{{ $conv->profile_pic_url }}" alt="" class="w-12 h-12 rounded-full object-cover">
                            @else
                                <span @class([
                                    'text-sm font-bold',
                                    'text-primary-600' => $isActive,
                                    'text-mono-500' => !$isActive,
                                ])>{{ $conv->initials() }}</span>
                            @endif
                        </div>
                        @if($conv->contact_id)
                            <span class="absolute -bottom-0.5 -right-0.5 w-[18px] h-[18px] rounded-full bg-success flex items-center justify-center border-2 border-mono-white shadow-sm">
                                <span class="material-icons-outlined text-[9px] text-white">person</span>
                            </span>
                        @endif
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-0.5">
                            <span @class([
                                'text-[13px] font-semibold truncate',
                                'text-primary-700' => $isActive,
                                'text-mono-900' => !$isActive,
                            ])>{{ $conv->contact?->name ?? $conv->displayName() }}</span>
                            @if($conv->last_message_at)
                                <span @class([
                                    'text-[10px] flex-shrink-0 ml-3 tabular-nums',
                                    'text-primary-500 font-semibold' => $conv->unread_count > 0,
                                    'text-mono-400' => $conv->unread_count === 0,
                                ])>
                                    {{ $conv->last_message_at->isToday() ? $conv->last_message_at->format('H:i') : $conv->last_message_at->format('d/m') }}
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between">
                            <p class="text-xs text-mono-500 truncate pr-2 leading-relaxed">{{ $conv->last_message ?? 'Sem mensagens' }}</p>
                            @if($conv->unread_count > 0)
                                <span class="flex-shrink-0 min-w-[20px] h-5 px-1.5 rounded-full bg-primary-500 text-white text-[10px] font-bold flex items-center justify-center shadow-sm">
                                    {{ $conv->unread_count > 99 ? '99+' : $conv->unread_count }}
                                </span>
                            @endif
                        </div>
                    </div>
                </button>
            @empty
                <div class="flex flex-col items-center justify-center h-full text-center px-8">
                    <div class="w-16 h-16 rounded-2xl bg-mono-100/60 flex items-center justify-center mb-3">
                        <span class="material-icons-outlined text-[32px] text-mono-300">forum</span>
                    </div>
                    <p class="text-sm font-medium text-mono-500">
                        @if($search)
                            Nenhum resultado
                        @elseif($instanceId)
                            Nenhuma conversa
                        @else
                            Selecione uma instancia
                        @endif
                    </p>
                    <p class="text-xs text-mono-400 mt-1">
                        @if($search)
                            Tente buscar por outro termo.
                        @elseif($instanceId)
                            As conversas aparecerao aqui quando receber mensagens.
                        @else
                            Escolha uma conta WhatsApp acima para comecar.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

        {{-- Indicador tempo real --}}
        <div x-show="echoConnected" x-cloak
             class="px-5 py-2.5 border-t border-mono-100/60 bg-mono-50/50">
            <div class="flex items-center gap-2">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-success"></span>
                </span>
                <span class="text-[11px] text-mono-500 font-medium">Sincronizado em tempo real</span>
            </div>
        </div>
    </aside>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- COLUNA 2 — AREA DO CHAT                                      --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div class="flex-1 flex flex-col min-w-0 {{ !$conversationId ? 'hidden md:flex' : 'flex' }}">
        @if($activeConversation)

            {{-- ── Header do Chat ─────────────────────────────────── --}}
            <header class="flex items-center gap-4 px-5 h-[68px] bg-mono-white border-b border-mono-100/80 flex-shrink-0">
                {{-- Voltar (mobile) --}}
                <button wire:click="$set('conversationId', null)" class="md:hidden p-1.5 -ml-1.5 rounded-lg text-mono-400 hover:text-mono-700 hover:bg-mono-50 transition-colors">
                    <span class="material-icons-outlined text-[22px]">arrow_back</span>
                </button>

                {{-- Avatar --}}
                <div class="relative flex-shrink-0">
                    <div class="w-11 h-11 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center shadow-sm">
                        @if($activeConversation->profile_pic_url)
                            <img src="{{ $activeConversation->profile_pic_url }}" alt="" class="w-11 h-11 rounded-full object-cover">
                        @else
                            <span class="text-sm font-bold text-white">{{ $activeConversation->initials() }}</span>
                        @endif
                    </div>
                    {{-- Online indicator --}}
                    <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full bg-success border-2 border-mono-white"></span>
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <h3 class="text-[15px] font-bold text-mono-900 truncate leading-tight">
                        {{ $linkedContact?->name ?? $activeConversation->displayName() }}
                    </h3>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-xs text-mono-400 tabular-nums">{{ $activeConversation->contact_phone }}</span>
                        @if($linkedContact)
                            <span class="inline-flex items-center gap-1 text-[10px] px-2 py-0.5 rounded-full bg-success/10 text-success font-semibold">
                                <span class="w-1 h-1 rounded-full bg-success"></span>
                                CRM
                            </span>
                        @endif
                        @if($linkedDeal)
                            <span class="inline-flex items-center gap-1 text-[10px] px-2 py-0.5 rounded-full bg-primary-50 text-primary-600 font-semibold truncate max-w-[140px]">
                                <span class="material-icons-outlined text-[10px]">sell</span>
                                {{ $linkedDeal->title }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Acoes do header --}}
                <div class="flex items-center gap-1">
                    <button wire:click="toggleCrmPanel"
                            @class([
                                'w-9 h-9 rounded-xl flex items-center justify-center transition-all',
                                'bg-primary-500 text-white shadow-sm' => $showCrmPanel,
                                'text-mono-400 hover:text-mono-600 hover:bg-mono-100' => !$showCrmPanel,
                            ])
                            title="Painel CRM">
                        <span class="material-icons-outlined text-[20px]">contact_page</span>
                    </button>

                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="w-9 h-9 rounded-xl flex items-center justify-center text-mono-400 hover:text-mono-600 hover:bg-mono-100 transition-all">
                            <span class="material-icons-outlined text-[20px]">more_horiz</span>
                        </button>
                        <div x-show="open" x-cloak @click.away="open = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             class="absolute right-0 top-11 bg-mono-white rounded-2xl shadow-elevated border border-mono-100/80 py-2 z-50 w-52">
                            @unless($linkedContact)
                                <button wire:click="autoLinkContact" @click="open = false"
                                        class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-mono-700 hover:bg-mono-50 transition-colors">
                                    <span class="material-icons-outlined text-[18px] text-mono-400">person_search</span>
                                    Auto-vincular contato
                                </button>
                            @endunless
                            <button wire:click="openLinkContactModal" @click="open = false"
                                    class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-mono-700 hover:bg-mono-50 transition-colors">
                                <span class="material-icons-outlined text-[18px] text-mono-400">person_add</span>
                                {{ $linkedContact ? 'Trocar contato' : 'Vincular contato' }}
                            </button>
                            <button wire:click="openLinkDealModal" @click="open = false"
                                    class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-mono-700 hover:bg-mono-50 transition-colors">
                                <span class="material-icons-outlined text-[18px] text-mono-400">handshake</span>
                                {{ $linkedDeal ? 'Trocar negocio' : 'Vincular negocio' }}
                            </button>
                            <div class="my-1.5 mx-3 border-t border-mono-100"></div>
                            <button wire:click="deleteConversation('{{ $activeConversation->id }}')" @click="open = false"
                                    class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-error hover:bg-down-bg transition-colors">
                                <span class="material-icons-outlined text-[18px]">delete_outline</span>
                                Excluir conversa
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            {{-- ── Mensagens ──────────────────────────────────────── --}}
            <div class="flex-1 overflow-y-auto overscroll-contain px-6 py-5 space-y-1 bg-mono-50/30" id="chat-messages"
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
                        <div class="flex justify-center py-3">
                            <span class="px-4 py-1 rounded-full bg-mono-white/90 backdrop-blur-sm text-[11px] font-semibold text-mono-500 shadow-sm border border-mono-100/50">
                                {{ $msg->message_at->isToday() ? 'Hoje' : ($msg->message_at->isYesterday() ? 'Ontem' : $msg->message_at->format('d/m/Y')) }}
                            </span>
                        </div>
                    @endif

                    <div class="flex {{ $msg->from_me ? 'justify-end' : 'justify-start' }} mb-1">
                        <div @class([
                            'max-w-[70%] lg:max-w-[55%] px-4 py-2.5 shadow-sm relative',
                            'bg-primary-500 text-white rounded-2xl rounded-br-md' => $msg->from_me,
                            'bg-mono-white text-mono-900 rounded-2xl rounded-bl-md border border-mono-100/50' => !$msg->from_me,
                        ])>
                            {{-- Midia --}}
                            @if($msg->type->value === 'image' && $msg->media_url)
                                <a href="{{ $msg->media_url }}" target="_blank" class="block -mx-1 -mt-1 mb-2">
                                    <img src="{{ $msg->media_url }}" alt="Imagem" class="rounded-xl max-w-full max-h-64 cursor-pointer hover:opacity-90 transition-opacity">
                                </a>
                            @elseif($msg->type->value === 'audio')
                                @if($msg->media_url)
                                    <audio controls class="max-w-[220px] h-10">
                                        <source src="{{ $msg->media_url }}" type="{{ $msg->media_mimetype ?? 'audio/mpeg' }}">
                                    </audio>
                                @else
                                    <div class="flex items-center gap-2 opacity-80">
                                        <span class="material-icons-outlined text-[18px]">mic</span>
                                        <span class="text-sm">Audio</span>
                                    </div>
                                @endif
                            @elseif($msg->type->value === 'video')
                                @if($msg->media_url)
                                    <video controls class="rounded-xl -mx-1 -mt-1 mb-2 max-w-full max-h-64">
                                        <source src="{{ $msg->media_url }}" type="{{ $msg->media_mimetype ?? 'video/mp4' }}">
                                    </video>
                                @else
                                    <div class="flex items-center gap-2 opacity-80">
                                        <span class="material-icons-outlined text-[18px]">videocam</span>
                                        <span class="text-sm">Video</span>
                                    </div>
                                @endif
                            @elseif($msg->type->value === 'document')
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
                                <a href="{{ $msg->media_url }}" target="_blank"
                                   class="flex items-center gap-3 {{ $msg->from_me ? 'bg-white/10 hover:bg-white/15' : 'bg-mono-50 hover:bg-mono-100' }} rounded-xl px-3.5 py-3 mb-1.5 transition-colors">
                                    <div class="w-10 h-10 rounded-lg {{ $msg->from_me ? 'bg-white/10' : 'bg-mono-100' }} flex items-center justify-center flex-shrink-0">
                                        <span class="material-icons-outlined text-[22px] {{ $docColor }}">{{ $docIcon }}</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium truncate">{{ $msg->media_filename ?? 'Documento' }}</p>
                                        <p class="text-[10px] {{ $msg->from_me ? 'text-white/50' : 'text-mono-400' }}">{{ strtoupper($ext) ?: 'DOC' }}</p>
                                    </div>
                                    <span class="material-icons-outlined text-[16px] {{ $msg->from_me ? 'text-white/40' : 'text-mono-300' }}">download</span>
                                </a>
                            @elseif($msg->type->value === 'sticker')
                                @if($msg->media_url)
                                    <img src="{{ $msg->media_url }}" alt="Sticker" class="w-28 h-28">
                                @else
                                    <span class="material-icons-outlined text-[40px] opacity-50">emoji_emotions</span>
                                @endif
                            @endif

                            @if($msg->body)
                                <p class="text-[14px] leading-relaxed whitespace-pre-wrap break-words">{{ $msg->body }}</p>
                            @endif

                            {{-- Hora + status --}}
                            <div class="flex items-center justify-end gap-1 mt-1 -mb-0.5">
                                <span class="text-[10px] {{ $msg->from_me ? 'text-white/50' : 'text-mono-400' }} tabular-nums">
                                    {{ $msg->message_at->format('H:i') }}
                                </span>
                                @if($msg->from_me)
                                    <span @class([
                                        'material-icons-outlined text-[14px]',
                                        'text-blue-200' => $msg->status->value === 'read',
                                        'text-white/50' => $msg->status->value !== 'read',
                                    ])>{{ $msg->status->icon() }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- ── Media Preview ──────────────────────────────────── --}}
            @if($showMediaPreview && $mediaFile)
                <div class="px-5 py-3 bg-mono-white border-t border-mono-100/80">
                    <div class="flex items-start gap-4 p-4 rounded-2xl bg-mono-50 border border-mono-200/60">
                        @php
                            $mime = $mediaFile->getMimeType();
                            $isImage = str_starts_with($mime, 'image/');
                            $isVideo = str_starts_with($mime, 'video/');
                            $isAudio = str_starts_with($mime, 'audio/');
                        @endphp
                        <div class="flex-shrink-0">
                            @if($isImage)
                                <div class="w-14 h-14 rounded-xl overflow-hidden bg-mono-100 shadow-sm">
                                    <img src="{{ $mediaFile->temporaryUrl() }}" alt="Preview" class="w-full h-full object-cover">
                                </div>
                            @else
                                <div class="w-14 h-14 rounded-xl bg-mono-200/60 flex items-center justify-center">
                                    <span class="material-icons-outlined text-[24px] text-mono-400">
                                        {{ $isVideo ? 'videocam' : ($isAudio ? 'mic' : 'description') }}
                                    </span>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <p class="text-sm font-medium text-mono-900 truncate">{{ $mediaFile->getClientOriginalName() }}</p>
                                <button wire:click="cancelMedia" class="p-1 rounded-lg text-mono-400 hover:text-error hover:bg-down-bg transition-colors flex-shrink-0">
                                    <span class="material-icons-outlined text-[16px]">close</span>
                                </button>
                            </div>
                            <p class="text-[11px] text-mono-400 mb-2">
                                {{ strtoupper(pathinfo($mediaFile->getClientOriginalName(), PATHINFO_EXTENSION)) }}
                                &bull; {{ number_format($mediaFile->getSize() / 1024, 0) }} KB
                            </p>
                            @if(!$isAudio)
                                <input type="text" wire:model="mediaCaption" placeholder="Legenda (opcional)..."
                                       class="w-full px-3 py-2 rounded-xl border border-mono-200/80 bg-mono-white text-xs text-mono-900 placeholder-mono-300 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all">
                            @endif
                        </div>
                        <button wire:click="sendMedia"
                                class="flex-shrink-0 w-10 h-10 rounded-xl bg-primary-500 text-white flex items-center justify-center hover:bg-primary-600 transition-all shadow-sm self-end active:scale-95"
                                wire:loading.attr="disabled" wire:target="sendMedia">
                            <span wire:loading.remove wire:target="sendMedia" class="material-icons-outlined text-[18px]">send</span>
                            <span wire:loading wire:target="sendMedia" class="material-icons-outlined text-[18px] animate-spin">autorenew</span>
                        </button>
                    </div>
                    @error('mediaFile')
                        <p class="text-xs text-error mt-1.5 px-1">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            {{-- ── Input de mensagem ──────────────────────────────── --}}
            <div class="px-5 py-3 bg-mono-white border-t border-mono-100/80 flex-shrink-0" x-data="mediaUpload()">
                <form wire:submit="sendMessage" class="flex items-end gap-3">
                    {{-- Anexo --}}
                    <button type="button" @click="$refs.fileInput.click()"
                            class="w-10 h-10 rounded-xl flex items-center justify-center text-mono-400 hover:text-primary-500 hover:bg-primary-50 transition-all flex-shrink-0 active:scale-95"
                            title="Anexar arquivo"
                            wire:loading.attr="disabled" wire:target="mediaFile">
                        <span wire:loading.remove wire:target="mediaFile" class="material-icons-outlined text-[22px]">attach_file</span>
                        <span wire:loading wire:target="mediaFile" class="material-icons-outlined text-[20px] animate-spin text-primary-500">autorenew</span>
                    </button>
                    <input type="file" x-ref="fileInput" wire:model="mediaFile" class="hidden"
                           accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.zip,.rar,.txt,.csv">

                    {{-- Textarea --}}
                    <div class="flex-1 relative">
                        <textarea wire:model="messageText"
                                  rows="1"
                                  placeholder="Escreva uma mensagem..."
                                  class="w-full px-4 py-2.5 rounded-2xl border border-mono-200/80 bg-mono-50/50 text-[14px] text-mono-900 placeholder-mono-400 resize-none focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:bg-mono-white transition-all leading-relaxed"
                                  x-on:keydown.enter.prevent="if (!$event.shiftKey) { $wire.sendMessage() }"
                                  x-on:input="$el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 120) + 'px'"
                        ></textarea>
                    </div>

                    {{-- Enviar --}}
                    <button type="submit"
                            class="flex-shrink-0 w-10 h-10 rounded-xl bg-primary-500 text-white flex items-center justify-center hover:bg-primary-600 transition-all shadow-sm active:scale-95"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="sendMessage" class="material-icons-outlined text-[20px]">send</span>
                        <span wire:loading wire:target="sendMessage" class="material-icons-outlined text-[18px] animate-spin">autorenew</span>
                    </button>
                </form>
            </div>

        @else
            {{-- ── Nenhuma conversa selecionada ───────────────────── --}}
            <div class="flex-1 flex flex-col items-center justify-center text-center px-8 bg-mono-50/30">
                <div class="bg-mono-white/80 backdrop-blur-sm rounded-3xl p-10 shadow-sm border border-mono-100/50 max-w-sm">
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center mx-auto mb-5 shadow-lg shadow-primary-500/20">
                        <span class="material-icons-outlined text-[40px] text-white">chat</span>
                    </div>
                    <h3 class="text-xl font-bold text-mono-900 mb-2">WhatsApp</h3>
                    <p class="text-sm text-mono-500 leading-relaxed">
                        Selecione uma conversa para comecar ou inicie uma nova conversa pelo botao acima.
                    </p>
                </div>
            </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- COLUNA 3 — PAINEL CRM (direita, recolhivel)                  --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    @if($showCrmPanel && $activeConversation)
        <aside class="w-[340px] border-l border-mono-100/80 bg-mono-white flex-shrink-0 overflow-y-auto overscroll-contain hidden lg:flex lg:flex-col">

            {{-- Header do painel --}}
            <div class="px-5 h-[68px] flex items-center justify-between border-b border-mono-100/80 flex-shrink-0">
                <div class="flex items-center gap-2">
                    <span class="material-icons-outlined text-[18px] text-primary-500">contact_page</span>
                    <h3 class="text-sm font-bold text-mono-900">Painel CRM</h3>
                </div>
                <button wire:click="$set('showCrmPanel', false)" class="w-8 h-8 rounded-lg flex items-center justify-center text-mono-400 hover:text-mono-600 hover:bg-mono-100 transition-colors">
                    <span class="material-icons-outlined text-[18px]">close</span>
                </button>
            </div>

            {{-- Avatar e info principal --}}
            <div class="px-5 py-5 border-b border-mono-100/60">
                <div class="flex flex-col items-center text-center">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center shadow-lg shadow-primary-500/15 mb-3">
                        @if($activeConversation->profile_pic_url)
                            <img src="{{ $activeConversation->profile_pic_url }}" alt="" class="w-16 h-16 rounded-full object-cover">
                        @else
                            <span class="text-xl font-bold text-white">{{ $activeConversation->initials() }}</span>
                        @endif
                    </div>
                    <h4 class="text-base font-bold text-mono-900">{{ $linkedContact?->name ?? $activeConversation->displayName() }}</h4>
                    <p class="text-xs text-mono-400 mt-0.5 tabular-nums">{{ $activeConversation->contact_phone }}</p>
                </div>
            </div>

            {{-- Contato vinculado --}}
            <div class="px-5 py-4 border-b border-mono-100/60">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-[11px] font-semibold text-mono-400 uppercase tracking-widest">Contato</p>
                    @if($linkedContact)
                        <button wire:click="unlinkContact" class="text-[10px] text-error/70 hover:text-error hover:underline transition-colors">Desvincular</button>
                    @endif
                </div>

                @if($linkedContact)
                    <div class="space-y-2.5">
                        @if($linkedContact->company)
                            <div class="flex items-center gap-2.5">
                                <span class="material-icons-outlined text-[16px] text-mono-300">business</span>
                                <span class="text-sm text-mono-700">{{ $linkedContact->company }}</span>
                            </div>
                        @endif
                        @if($linkedContact->email)
                            <div class="flex items-center gap-2.5">
                                <span class="material-icons-outlined text-[16px] text-mono-300">email</span>
                                <a href="mailto:{{ $linkedContact->email }}" class="text-sm text-mono-700 hover:text-primary-500 transition-colors">{{ $linkedContact->email }}</a>
                            </div>
                        @endif
                        @if($linkedContact->phone)
                            <div class="flex items-center gap-2.5">
                                <span class="material-icons-outlined text-[16px] text-mono-300">phone</span>
                                <span class="text-sm text-mono-700">{{ $linkedContact->phone }}</span>
                            </div>
                        @endif
                        <a href="{{ route('crm.contatos') }}" wire:navigate
                           class="flex items-center gap-1.5 mt-1 text-xs text-primary-500 hover:text-primary-600 font-medium transition-colors">
                            <span class="material-icons-outlined text-[14px]">open_in_new</span>
                            Abrir no CRM
                        </a>
                    </div>
                @else
                    <div class="text-center py-2">
                        <p class="text-xs text-mono-400 mb-3">Nenhum contato vinculado</p>
                        <div class="flex flex-col gap-1.5">
                            <button wire:click="autoLinkContact"
                                    class="flex items-center justify-center gap-2 w-full h-9 rounded-xl text-xs font-medium bg-primary-50 text-primary-600 hover:bg-primary-100 transition-all">
                                <span class="material-icons-outlined text-[15px]">person_search</span>
                                Auto-vincular
                            </button>
                            <div class="flex gap-1.5">
                                <button wire:click="openLinkContactModal"
                                        class="flex items-center justify-center gap-1.5 flex-1 h-9 rounded-xl text-xs font-medium bg-mono-100 text-mono-600 hover:bg-mono-200 transition-all">
                                    <span class="material-icons-outlined text-[14px]">search</span>
                                    Buscar
                                </button>
                                <button wire:click="openCreateContactModal"
                                        class="flex items-center justify-center gap-1.5 flex-1 h-9 rounded-xl text-xs font-medium bg-mono-100 text-mono-600 hover:bg-mono-200 transition-all">
                                    <span class="material-icons-outlined text-[14px]">person_add</span>
                                    Criar
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Negocio vinculado --}}
            <div class="px-5 py-4 border-b border-mono-100/60">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-[11px] font-semibold text-mono-400 uppercase tracking-widest">Negocio</p>
                    @if($linkedDeal)
                        <button wire:click="unlinkDeal" class="text-[10px] text-error/70 hover:text-error hover:underline transition-colors">Desvincular</button>
                    @endif
                </div>

                @if($linkedDeal)
                    <div class="rounded-xl border border-mono-100 bg-mono-50/50 p-3.5 space-y-2.5">
                        <p class="text-sm font-semibold text-mono-900">{{ $linkedDeal->title }}</p>
                        <div class="flex items-center gap-2">
                            <x-jr.badge variant="{{ $linkedDeal->stage->color() }}" size="sm">
                                {{ $linkedDeal->stage->label() }}
                            </x-jr.badge>
                            <span class="text-xs font-bold text-mono-900">R$ {{ number_format($linkedDeal->value, 2, ',', '.') }}</span>
                        </div>
                        @if($linkedDeal->expected_close_date)
                            <div class="flex items-center gap-1.5 text-xs text-mono-400">
                                <span class="material-icons-outlined text-[14px]">event</span>
                                Previsao: {{ $linkedDeal->expected_close_date->format('d/m/Y') }}
                            </div>
                        @endif
                        <a href="{{ route('crm.negocio', $linkedDeal->id) }}" wire:navigate
                           class="flex items-center gap-1.5 text-xs text-primary-500 hover:text-primary-600 font-medium transition-colors">
                            <span class="material-icons-outlined text-[14px]">open_in_new</span>
                            Abrir negocio
                        </a>
                    </div>
                @else
                    <div class="text-center py-2">
                        <p class="text-xs text-mono-400 mb-3">Nenhum negocio vinculado</p>
                        <button wire:click="openLinkDealModal"
                                class="flex items-center justify-center gap-2 w-full h-9 rounded-xl text-xs font-medium bg-primary-50 text-primary-600 hover:bg-primary-100 transition-all">
                            <span class="material-icons-outlined text-[15px]">link</span>
                            Vincular negocio
                        </button>
                    </div>
                @endif
            </div>

            {{-- Negocios do contato --}}
            @if($linkedContact && $contactDeals->isNotEmpty())
                <div class="px-5 py-4">
                    <p class="text-[11px] font-semibold text-mono-400 uppercase tracking-widest mb-3">Negocios do contato</p>
                    <div class="space-y-2">
                        @foreach($contactDeals as $deal)
                            <a href="{{ route('crm.negocio', $deal->id) }}" wire:navigate
                               class="flex items-center justify-between p-3 rounded-xl bg-mono-50/50 hover:bg-mono-100 border border-transparent hover:border-mono-200/60 transition-all">
                                <div class="min-w-0">
                                    <p class="text-xs font-semibold text-mono-900 truncate">{{ $deal->title }}</p>
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
        </aside>
    @endif

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- MODAIS                                                        --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}

    {{-- Nova Conversa --}}
    @if($showNewChatModal)
        <div class="fixed inset-0 z-modal overflow-y-auto" wire:keydown.escape="$set('showNewChatModal', false)">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" wire:click="$set('showNewChatModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-md overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-mono-100">
                        <h3 class="text-lg font-bold text-mono-900">Nova Conversa</h3>
                        <button wire:click="$set('showNewChatModal', false)" class="w-8 h-8 rounded-lg flex items-center justify-center text-mono-400 hover:text-mono-600 hover:bg-mono-100 transition-colors">
                            <span class="material-icons-outlined text-[20px]">close</span>
                        </button>
                    </div>
                    <form wire:submit="startNewChat">
                        <div class="px-6 py-5 space-y-4">
                            <x-jr.input label="Numero de Telefone" wire:model="newChatPhone"
                                        placeholder="5511999999999 (com codigo do pais)"
                                        icon="phone" />
                            <p class="text-xs text-mono-400 -mt-2 px-1">
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

    {{-- Vincular Contato --}}
    @if($showLinkContactModal)
        <div class="fixed inset-0 z-modal overflow-y-auto" wire:keydown.escape="$set('showLinkContactModal', false)">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" wire:click="$set('showLinkContactModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-lg overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-mono-100">
                        <h3 class="text-lg font-bold text-mono-900">Vincular Contato</h3>
                        <button wire:click="$set('showLinkContactModal', false)" class="w-8 h-8 rounded-lg flex items-center justify-center text-mono-400 hover:text-mono-600 hover:bg-mono-100 transition-colors">
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
                                <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0">
                                    <span class="text-xs font-bold text-primary-600">{{ strtoupper(substr($c->name, 0, 2)) }}</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-mono-900 truncate">{{ $c->name }}</p>
                                    <p class="text-xs text-mono-400 truncate">{{ $c->phone ?? $c->email ?? $c->company ?? '--' }}</p>
                                </div>
                            </button>
                        @empty
                            <div class="text-center py-8">
                                <span class="material-icons-outlined text-[32px] text-mono-200">person_off</span>
                                <p class="text-xs text-mono-400 mt-2">
                                    {{ $linkContactSearch ? 'Nenhum contato encontrado.' : 'Digite para buscar contatos.' }}
                                </p>
                            </div>
                        @endforelse
                    </div>
                    <div class="px-6 py-3 border-t border-mono-100 bg-mono-50">
                        <button wire:click="openCreateContactModal"
                                class="flex items-center gap-2 w-full px-3 py-2.5 rounded-xl text-sm font-medium text-primary-500 hover:bg-primary-100/50 transition-colors">
                            <span class="material-icons-outlined text-[18px]">person_add</span>
                            Criar novo contato
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Criar Contato --}}
    @if($showCreateContactModal)
        <div class="fixed inset-0 z-modal overflow-y-auto" wire:keydown.escape="$set('showCreateContactModal', false)">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" wire:click="$set('showCreateContactModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-md overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-mono-100">
                        <h3 class="text-lg font-bold text-mono-900">Criar Contato</h3>
                        <button wire:click="$set('showCreateContactModal', false)" class="w-8 h-8 rounded-lg flex items-center justify-center text-mono-400 hover:text-mono-600 hover:bg-mono-100 transition-colors">
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
                            <div class="flex items-center gap-2 px-1 py-2 rounded-xl bg-info-50 text-xs text-info-600">
                                <span class="material-icons-outlined text-[16px]">info</span>
                                O telefone sera preenchido automaticamente.
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

    {{-- Vincular Negocio --}}
    @if($showLinkDealModal)
        <div class="fixed inset-0 z-modal overflow-y-auto" wire:keydown.escape="$set('showLinkDealModal', false)">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" wire:click="$set('showLinkDealModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-lg overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-mono-100">
                        <h3 class="text-lg font-bold text-mono-900">Vincular Negocio</h3>
                        <button wire:click="$set('showLinkDealModal', false)" class="w-8 h-8 rounded-lg flex items-center justify-center text-mono-400 hover:text-mono-600 hover:bg-mono-100 transition-colors">
                            <span class="material-icons-outlined text-[20px]">close</span>
                        </button>
                    </div>
                    <div class="px-6 py-4">
                        <x-jr.input wire:model.live.debounce.300ms="linkDealSearch" placeholder="Buscar por titulo ou contato..." icon="search" />
                    </div>
                    <div class="px-6 pb-4 max-h-80 overflow-y-auto space-y-1">
                        @forelse($linkDeals as $d)
                            <button wire:click="linkDeal('{{ $d->id }}')"
                                    class="flex items-center justify-between w-full p-3.5 rounded-xl text-left hover:bg-mono-50 transition-colors">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-mono-900 truncate">{{ $d->title }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <x-jr.badge variant="{{ $d->stage->color() }}" size="sm">{{ $d->stage->label() }}</x-jr.badge>
                                        <span class="text-xs text-mono-400 truncate">{{ $d->contact?->name ?? '--' }}</span>
                                    </div>
                                </div>
                                <span class="text-sm font-bold text-mono-900 flex-shrink-0 ml-3">
                                    R$ {{ number_format($d->value, 2, ',', '.') }}
                                </span>
                            </button>
                        @empty
                            <div class="text-center py-8">
                                <span class="material-icons-outlined text-[32px] text-mono-200">handshake</span>
                                <p class="text-xs text-mono-400 mt-2">
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
    Alpine.data('mediaUpload', () => ({}));

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

            this.$watch('$wire.instanceId', (id) => this.subscribeInstance(id));
            this.$watch('$wire.conversationId', (id) => this.subscribeChat(id));

            if (this.$wire.instanceId) this.subscribeInstance(this.$wire.instanceId);
            if (this.$wire.conversationId) this.subscribeChat(this.$wire.conversationId);
        },

        subscribeInstance(instanceId) {
            if (this.instanceChannel) window.Echo.leave(this.instanceChannel);
            if (!instanceId) return;

            this.instanceChannel = `whatsapp.instance.${instanceId}`;
            window.Echo.channel(this.instanceChannel)
                .listen('.message.new', (e) => {
                    this.$wire.dispatch('echo-message-received', { data: e });
                    if (!e.message.from_me) this.playNotification();
                });
        },

        subscribeChat(conversationId) {
            if (this.chatChannel) window.Echo.leave(this.chatChannel);
            if (!conversationId) return;

            this.chatChannel = `whatsapp.chat.${conversationId}`;
            window.Echo.channel(this.chatChannel)
                .listen('.message.new', (e) => {
                    this.$wire.dispatch('echo-message-received', { data: e });
                    this.$nextTick(() => {
                        const el = document.getElementById('chat-messages');
                        if (el) el.scrollTop = el.scrollHeight;
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
