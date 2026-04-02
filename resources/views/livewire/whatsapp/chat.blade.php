<div class="flex h-[calc(100vh-8rem)] -mx-6 -mb-6" wire:poll.5s="refreshMessages">
    @if (session('error'))
        <div class="absolute top-0 left-0 right-0 z-50 px-4 pt-2">
            <x-jr.alert variant="error">{{ session('error') }}</x-jr.alert>
        </div>
    @endif

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
                    <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0
                                {{ $conversationId === $conv->id ? 'bg-primary-100' : 'bg-mono-100' }}">
                        @if($conv->profile_pic_url)
                            <img src="{{ $conv->profile_pic_url }}" alt="" class="w-10 h-10 rounded-full object-cover">
                        @else
                            <span class="text-xs font-bold {{ $conversationId === $conv->id ? 'text-primary-600' : 'text-mono-600' }}">
                                {{ $conv->initials() }}
                            </span>
                        @endif
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-mono-900 truncate">{{ $conv->displayName() }}</span>
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
    <div class="flex-1 flex flex-col bg-mono-50 {{ !$conversationId ? 'hidden md:flex' : 'flex' }}">
        @if($activeConversation)
            <!-- Chat Header -->
            <div class="flex items-center gap-3 px-4 py-3 bg-mono-white border-b border-mono-100">
                <!-- Back button (mobile) -->
                <button wire:click="$set('conversationId', null)" class="md:hidden p-1 rounded-lg text-mono-300 hover:text-mono-600">
                    <span class="material-icons-outlined text-[20px]">arrow_back</span>
                </button>

                <!-- Avatar -->
                <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0">
                    @if($activeConversation->profile_pic_url)
                        <img src="{{ $activeConversation->profile_pic_url }}" alt="" class="w-10 h-10 rounded-full object-cover">
                    @else
                        <span class="text-xs font-bold text-primary-600">{{ $activeConversation->initials() }}</span>
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-bold text-mono-900 truncate">{{ $activeConversation->displayName() }}</h3>
                    <p class="text-xs text-mono-300">{{ $activeConversation->contact_phone }}</p>
                </div>

                <!-- Actions -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="p-2 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                        <span class="material-icons-outlined text-[20px]">more_vert</span>
                    </button>
                    <div x-show="open" x-cloak @click.away="open = false"
                         class="absolute right-0 top-10 bg-mono-white rounded-xl shadow-dropdown border border-mono-100 py-1.5 z-50 w-44">
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
                                <img src="{{ $msg->media_url }}" alt="Imagem" class="rounded-lg mb-1 max-w-full">
                            @elseif($msg->type->value === 'audio')
                                <div class="flex items-center gap-2">
                                    <span class="material-icons-outlined text-[20px]">mic</span>
                                    <span class="text-sm">Audio</span>
                                </div>
                            @elseif($msg->type->value === 'video')
                                <div class="flex items-center gap-2">
                                    <span class="material-icons-outlined text-[20px]">videocam</span>
                                    <span class="text-sm">Video</span>
                                </div>
                            @elseif($msg->type->value === 'document')
                                <div class="flex items-center gap-2 {{ $msg->from_me ? 'bg-primary-600' : 'bg-mono-50' }} rounded-lg px-3 py-2 mb-1">
                                    <span class="material-icons-outlined text-[20px]">description</span>
                                    <span class="text-sm truncate">{{ $msg->media_filename ?? 'Documento' }}</span>
                                </div>
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

            <!-- Message Input -->
            <div class="px-4 py-3 bg-mono-white border-t border-mono-100">
                <form wire:submit="sendMessage" class="flex items-end gap-3">
                    <div class="flex-1 relative">
                        <textarea wire:model="messageText"
                                  rows="1"
                                  placeholder="Digite uma mensagem..."
                                  class="w-full px-4 py-3 rounded-2xl border border-mono-200 bg-mono-50 text-sm text-mono-900 placeholder-mono-300 resize-none focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                  x-data
                                  x-on:keydown.enter.prevent="if (!$event.shiftKey) { $wire.sendMessage() }"
                                  x-on:input="$el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 120) + 'px'"
                        ></textarea>
                    </div>
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
                            <x-jr.button variant="mono" wire:click="$set('showNewChatModal', false)" type="button">
                                Cancelar
                            </x-jr.button>
                            <x-jr.button type="submit">
                                Iniciar Conversa
                            </x-jr.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
