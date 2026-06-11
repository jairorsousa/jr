<div>
    @if (session('success'))
        <div class="mb-4"><x-jr.alert variant="success">{{ session('success') }}</x-jr.alert></div>
    @endif
    @if (session('error'))
        <div class="mb-4"><x-jr.alert variant="error">{{ session('error') }}</x-jr.alert></div>
    @endif

    <x-jr.card class="mb-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm text-mono-600 font-medium">Usuarios de bet</p>
                <p class="text-3xl font-bold text-mono-900 mt-1">{{ $users->count() }}</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
                <x-jr.input wire:model.live.debounce.300ms="search" placeholder="Buscar usuario..." icon="search" />
                <x-jr.button wire:click="openCreateModal">
                    <span class="material-icons-outlined text-[18px]">person_add</span>
                    Novo Usuario
                </x-jr.button>
            </div>
        </div>
    </x-jr.card>

    @if($users->isEmpty())
        <x-jr.card>
            <div class="text-center py-8">
                <span class="material-icons-outlined text-[48px] text-mono-200">groups</span>
                <p class="text-mono-600 mt-2">Nenhum usuario de bet cadastrado.</p>
                <div class="mt-4"><x-jr.button wire:click="openCreateModal" size="sm">Criar primeiro usuario</x-jr.button></div>
            </div>
        </x-jr.card>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($users as $user)
                <x-jr.card class="{{ !$user->is_active ? 'opacity-50' : '' }}">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-white font-bold" style="background-color: {{ $user->color }}">
                                {{ mb_substr($user->name, 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-semibold text-mono-900 truncate">{{ $user->name }}</h3>
                                <p class="text-xs text-mono-600 truncate">{{ $user->nickname ?: $user->email ?: $user->phone ?: 'Sem contato informado' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            <button wire:click="openEditModal('{{ $user->id }}')" class="p-1.5 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-100 transition-colors">
                                <span class="material-icons-outlined text-[16px]">edit</span>
                            </button>
                            <button wire:click="toggleActive('{{ $user->id }}')" class="p-1.5 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-100 transition-colors">
                                <span class="material-icons-outlined text-[16px]">{{ $user->is_active ? 'visibility_off' : 'visibility' }}</span>
                            </button>
                            <button wire:click="confirmDelete('{{ $user->id }}')" class="p-1.5 rounded-lg text-mono-300 hover:text-error hover:bg-down-bg transition-colors">
                                <span class="material-icons-outlined text-[16px]">delete</span>
                            </button>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3 mt-5 pt-4 border-t border-mono-100">
                        <div>
                            <p class="text-xs text-mono-600">Contas</p>
                            <p class="text-lg font-bold text-mono-900">{{ $user->accounts_count }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-mono-600">Saldo</p>
                            <p class="text-lg font-bold text-mono-900">R$ {{ number_format($user->accounts_sum_current_balance ?? 0, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </x-jr.card>
            @endforeach
        </div>
    @endif

    @if($showModal)
        <div class="fixed inset-0 z-modal overflow-y-auto" wire:keydown.escape="$set('showModal', false)">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-2xl overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-mono-100">
                        <h3 class="text-lg font-bold text-mono-900">{{ $editingId ? 'Editar Usuario' : 'Novo Usuario' }}</h3>
                        <button wire:click="$set('showModal', false)" class="p-1 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                            <span class="material-icons-outlined text-[20px]">close</span>
                        </button>
                    </div>

                    <form wire:submit="save">
                        <div class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <x-jr.input label="Nome" wire:model="name" icon="person" :error="$errors->first('name')" />
                                <x-jr.input label="Apelido" wire:model="nickname" icon="badge" :error="$errors->first('nickname')" />
                                <x-jr.input label="Documento" wire:model="document" icon="badge" :error="$errors->first('document')" />
                                <x-jr.input label="Email" wire:model="email" type="email" icon="email" :error="$errors->first('email')" />
                                <x-jr.input label="Telefone" wire:model="phone" icon="phone" :error="$errors->first('phone')" />
                                <x-jr.input label="Chave Pix" wire:model="pix_key" icon="key" :error="$errors->first('pix_key')" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Cor</label>
                                <div class="flex flex-wrap items-center gap-3">
                                    @foreach(['#ff6f00', '#15a96f', '#1a73e8', '#5C6BC0', '#EF5350', '#AB47BC', '#26C6DA', '#FFA726', '#EC407A', '#78909C'] as $c)
                                        <button type="button" wire:click="$set('color', '{{ $c }}')" class="w-8 h-8 rounded-full transition-transform {{ $color === $c ? 'ring-2 ring-offset-2 ring-mono-900 scale-110' : 'hover:scale-110' }}" style="background-color: {{ $c }}"></button>
                                    @endforeach
                                </div>
                            </div>

                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="is_active" class="rounded border-mono-200 text-primary-500 focus:ring-primary-500">
                                <span class="text-sm font-medium text-mono-900">Usuario ativo</span>
                            </label>

                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Observacoes</label>
                                <textarea wire:model="notes" rows="3" class="w-full bg-mono-white border border-mono-200 rounded-xl px-4 py-3 text-sm text-mono-900 focus:border-primary-500 focus:ring-0 resize-none"></textarea>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-mono-100 bg-mono-50">
                            <x-jr.button variant="mono" type="button" wire:click="$set('showModal', false)">Cancelar</x-jr.button>
                            <x-jr.button type="submit">{{ $editingId ? 'Salvar' : 'Criar Usuario' }}</x-jr.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if($showDeleteModal)
        <div class="fixed inset-0 z-modal overflow-y-auto">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showDeleteModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-sm overflow-hidden">
                    <div class="px-6 py-5 text-center">
                        <div class="mx-auto w-12 h-12 rounded-full bg-down-bg flex items-center justify-center mb-4">
                            <span class="material-icons-outlined text-[24px] text-error">delete</span>
                        </div>
                        <h3 class="text-lg font-bold text-mono-900">Excluir usuario?</h3>
                        <p class="text-sm text-mono-600 mt-2">Usuarios com contas vinculadas nao podem ser excluidos.</p>
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
