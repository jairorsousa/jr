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
        <div class="flex items-center gap-3">
            <x-jr.button wire:click="syncStatus" variant="mono" size="sm">
                <span class="material-icons-outlined text-[18px]" wire:loading.class="animate-spin" wire:target="syncStatus">sync</span>
                <span x-show="window.innerWidth > 640">Sincronizar</span>
            </x-jr.button>
        </div>
        <x-jr.button wire:click="openCreateModal">
            <span class="material-icons-outlined text-[18px]">add</span>
            Nova Instancia
        </x-jr.button>
    </div>

    <!-- Instances Grid -->
    @if($instances->isEmpty())
        <x-jr.card>
            <div class="text-center py-8">
                <span class="material-icons-outlined text-[48px] text-mono-200">smartphone</span>
                <p class="text-mono-600 mt-2">Nenhuma instancia WhatsApp configurada.</p>
                <div class="mt-4">
                    <x-jr.button wire:click="openCreateModal" size="sm">
                        Criar primeira instancia
                    </x-jr.button>
                </div>
            </div>
        </x-jr.card>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($instances as $instance)
                <x-jr.card>
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center
                                {{ $instance->status->value === 'connected' ? 'bg-success/10' : 'bg-mono-100' }}">
                                <span class="material-icons-outlined text-[22px]
                                    {{ $instance->status->value === 'connected' ? 'text-success' : 'text-mono-300' }}">
                                    {{ $instance->status->value === 'connected' ? 'phone_android' : 'phonelink_off' }}
                                </span>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-mono-900">{{ $instance->name }}</h3>
                                <p class="text-xs text-mono-600">{{ $instance->instance_name }}</p>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <x-jr.badge variant="{{ $instance->status->color() }}" size="sm">
                            {{ $instance->status->label() }}
                        </x-jr.badge>
                    </div>

                    <!-- Info -->
                    <div class="space-y-2 mb-4">
                        @if($instance->phone)
                            <div class="flex items-center gap-2 text-sm text-mono-600">
                                <span class="material-icons-outlined text-[16px]">phone</span>
                                {{ $instance->phone }}
                            </div>
                        @endif
                        <div class="flex items-center gap-2 text-sm text-mono-600">
                            <span class="material-icons-outlined text-[16px]">chat</span>
                            {{ $instance->conversations_count }} conversas
                        </div>
                        @if($instance->connected_at)
                            <div class="flex items-center gap-2 text-sm text-mono-600">
                                <span class="material-icons-outlined text-[16px]">schedule</span>
                                Conectado {{ $instance->connected_at->diffForHumans() }}
                            </div>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2 pt-3 border-t border-mono-100">
                        @if($instance->status->value === 'connected')
                            <a href="{{ route('whatsapp.chat', $instance->id) }}"
                               class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-sm font-medium bg-primary-500 text-white hover:bg-primary-600 transition-colors">
                                <span class="material-icons-outlined text-[16px]">chat</span>
                                Conversas
                            </a>
                            <button wire:click="disconnect('{{ $instance->id }}')"
                                    class="flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-sm font-medium bg-mono-100 text-mono-600 hover:bg-mono-200 transition-colors">
                                <span class="material-icons-outlined text-[16px]">power_settings_new</span>
                            </button>
                        @else
                            <button wire:click="connect('{{ $instance->id }}')"
                                    class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-sm font-medium bg-success/10 text-success hover:bg-success/20 transition-colors">
                                <span class="material-icons-outlined text-[16px]">qr_code</span>
                                Conectar
                            </button>
                        @endif

                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                    class="flex items-center justify-center p-2 rounded-xl text-mono-300 hover:text-mono-600 hover:bg-mono-100 transition-colors">
                                <span class="material-icons-outlined text-[18px]">more_vert</span>
                            </button>
                            <div x-show="open" x-cloak @click.away="open = false"
                                 class="absolute right-0 top-10 bg-mono-white rounded-xl shadow-dropdown border border-mono-100 py-1.5 z-50 w-36">
                                <button wire:click="openEditModal('{{ $instance->id }}')" @click="open = false"
                                        class="flex items-center gap-2 w-full px-3 py-2 text-sm text-mono-900 hover:bg-mono-50">
                                    <span class="material-icons-outlined text-[16px] text-mono-300">edit</span>
                                    Editar
                                </button>
                                <div class="border-t border-mono-100 my-1"></div>
                                <button wire:click="confirmDelete('{{ $instance->id }}')" @click="open = false"
                                        class="flex items-center gap-2 w-full px-3 py-2 text-sm text-error hover:bg-down-bg">
                                    <span class="material-icons-outlined text-[16px]">delete</span>
                                    Excluir
                                </button>
                            </div>
                        </div>
                    </div>
                </x-jr.card>
            @endforeach
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
                            {{ $editingId ? 'Editar Instancia' : 'Nova Instancia' }}
                        </h3>
                        <button wire:click="$set('showModal', false)" class="p-1 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                            <span class="material-icons-outlined text-[20px]">close</span>
                        </button>
                    </div>

                    <form wire:submit="save">
                        <div class="px-6 py-5 space-y-4">
                            <x-jr.input label="Nome" wire:model="name" placeholder="Ex: WhatsApp Comercial"
                                        icon="label" :error="$errors->first('name')" />

                            @unless($editingId)
                                <x-jr.input label="Nome da Instancia" wire:model="instance_name"
                                            placeholder="Ex: comercial-01 (sem espacos)"
                                            icon="developer_board" :error="$errors->first('instance_name')" />
                                <p class="text-xs text-mono-300 -mt-2 px-1">
                                    Identificador unico na Evolution API. Apenas letras, numeros, hifens e underscores.
                                </p>
                            @endunless
                        </div>

                        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-mono-100 bg-mono-50">
                            <x-jr.button variant="mono" wire:click="$set('showModal', false)" type="button">
                                Cancelar
                            </x-jr.button>
                            <x-jr.button type="submit" wire:loading.attr="disabled">
                                <span wire:loading wire:target="save" class="material-icons-outlined text-[18px] animate-spin">autorenew</span>
                                {{ $editingId ? 'Salvar' : 'Criar Instancia' }}
                            </x-jr.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- QR Code Modal -->
    @if($showQrModal)
        <div class="fixed inset-0 z-modal overflow-y-auto" wire:poll.3s="checkConnection">
            <div class="fixed inset-0 bg-black/40"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-md overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-mono-100">
                        <h3 class="text-lg font-bold text-mono-900">Conectar WhatsApp</h3>
                        <button wire:click="$set('showQrModal', false)" class="p-1 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                            <span class="material-icons-outlined text-[20px]">close</span>
                        </button>
                    </div>

                    <div class="px-6 py-6 text-center">
                        <p class="text-sm text-mono-600 mb-4">
                            Abra o WhatsApp no seu celular e escaneie o QR Code abaixo:
                        </p>

                        @if($qrcode)
                            <div class="flex justify-center mb-4">
                                <img src="{{ $qrcode }}" alt="QR Code" class="w-64 h-64 rounded-xl border border-mono-100">
                            </div>
                        @else
                            <div class="flex justify-center items-center w-64 h-64 mx-auto mb-4 rounded-xl border border-mono-100 bg-mono-50">
                                <span class="material-icons-outlined text-[48px] text-mono-200 animate-pulse">qr_code_2</span>
                            </div>
                        @endif

                        <p class="text-xs text-mono-300">
                            Aguardando conexao... O QR Code atualiza automaticamente.
                        </p>

                        <div class="mt-4">
                            <x-jr.button wire:click="refreshQrCode" variant="mono" size="sm">
                                <span class="material-icons-outlined text-[16px]">refresh</span>
                                Atualizar QR Code
                            </x-jr.button>
                        </div>
                    </div>
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
                        <h3 class="text-lg font-bold text-mono-900">Excluir instancia?</h3>
                        <p class="text-sm text-mono-600 mt-2">Esta acao ira remover a instancia da Evolution API e todas as conversas associadas.</p>
                    </div>
                    <div class="flex items-center justify-center gap-3 px-6 py-4 border-t border-mono-100 bg-mono-50">
                        <x-jr.button variant="mono" wire:click="$set('showDeleteModal', false)">
                            Cancelar
                        </x-jr.button>
                        <x-jr.button variant="danger" wire:click="delete">
                            Excluir
                        </x-jr.button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
