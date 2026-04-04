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
        <div class="flex-1 max-w-sm">
            <x-jr.input wire:model.live.debounce.300ms="search" placeholder="Buscar por nome, email ou empresa..."
                        icon="search" />
        </div>
        <x-jr.button wire:click="openCreateModal">
            <span class="material-icons-outlined text-[18px]">add</span>
            Novo Contato
        </x-jr.button>
    </div>

    <!-- Contacts Table -->
    @if($contacts->isEmpty())
        <x-jr.card>
            <div class="text-center py-8">
                <span class="material-icons-outlined text-[48px] text-mono-200">person_off</span>
                <p class="text-mono-600 mt-2">
                    @if($search)
                        Nenhum contato encontrado para "{{ $search }}".
                    @else
                        Nenhum contato cadastrado.
                    @endif
                </p>
                @unless($search)
                    <div class="mt-4">
                        <x-jr.button wire:click="openCreateModal" size="sm">
                            Criar primeiro contato
                        </x-jr.button>
                    </div>
                @endunless
            </div>
        </x-jr.card>
    @else
        <x-jr.table>
            <x-slot:header>
                <tr>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-mono-600 uppercase tracking-wider">Nome</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-mono-600 uppercase tracking-wider">Email</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-mono-600 uppercase tracking-wider">Telefone</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-mono-600 uppercase tracking-wider">Empresa</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-mono-600 uppercase tracking-wider">Negocios</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-mono-600 uppercase tracking-wider">WhatsApp</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-mono-600 uppercase tracking-wider">Acoes</th>
                </tr>
            </x-slot:header>
            <x-slot:body>
                @foreach($contacts as $contact)
                    <tr class="{{ !$contact->is_active ? 'opacity-50' : '' }}">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center">
                                    <span class="text-xs font-bold text-primary-600">{{ strtoupper(substr($contact->name, 0, 2)) }}</span>
                                </div>
                                <span class="text-sm font-medium text-mono-900">{{ $contact->name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-mono-600">
                            {{ $contact->email ?? '--' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-mono-600">
                            {{ $contact->phone ?? '--' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-mono-600">
                            {{ $contact->company ?? '--' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <x-jr.badge variant="neutral" size="sm">
                                {{ $contact->deals_count }}
                            </x-jr.badge>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($contact->whatsapp_conversations_count > 0)
                                <a href="{{ route('whatsapp.chat') }}" wire:navigate
                                   class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-success/10 text-success hover:bg-success/20 transition-colors"
                                   title="{{ $contact->whatsapp_conversations_count }} conversa(s)">
                                    <span class="material-icons-outlined text-[16px]">chat</span>
                                    <span class="text-xs font-semibold">{{ $contact->whatsapp_conversations_count }}</span>
                                </a>
                            @else
                                <span class="text-mono-200 text-xs">--</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="relative inline-block" x-data="{ open: false }">
                                <button @click="open = !open" class="p-1.5 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                                    <span class="material-icons-outlined text-[18px]">more_vert</span>
                                </button>
                                <div x-show="open" x-cloak @click.away="open = false"
                                     class="absolute right-0 top-8 bg-mono-white rounded-xl shadow-dropdown border border-mono-100 py-1.5 z-50 w-40">
                                    <button wire:click="openEditModal('{{ $contact->id }}')" @click="open = false"
                                            class="flex items-center gap-2 w-full px-3 py-2 text-sm text-mono-900 hover:bg-mono-50">
                                        <span class="material-icons-outlined text-[16px] text-mono-300">edit</span>
                                        Editar
                                    </button>
                                    <button wire:click="toggleActive('{{ $contact->id }}')" @click="open = false"
                                            class="flex items-center gap-2 w-full px-3 py-2 text-sm text-mono-900 hover:bg-mono-50">
                                        <span class="material-icons-outlined text-[16px] text-mono-300">
                                            {{ $contact->is_active ? 'visibility_off' : 'visibility' }}
                                        </span>
                                        {{ $contact->is_active ? 'Desativar' : 'Ativar' }}
                                    </button>
                                    <div class="border-t border-mono-100 my-1"></div>
                                    <button wire:click="confirmDelete('{{ $contact->id }}')" @click="open = false"
                                            class="flex items-center gap-2 w-full px-3 py-2 text-sm text-error hover:bg-down-bg">
                                        <span class="material-icons-outlined text-[16px]">delete</span>
                                        Excluir
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-slot:body>
        </x-jr.table>
    @endif

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-modal overflow-y-auto" wire:keydown.escape="$set('showModal', false)">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-lg overflow-hidden">
                    <!-- Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-mono-100">
                        <h3 class="text-lg font-bold text-mono-900">
                            {{ $editingId ? 'Editar Contato' : 'Novo Contato' }}
                        </h3>
                        <button wire:click="$set('showModal', false)" class="p-1 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                            <span class="material-icons-outlined text-[20px]">close</span>
                        </button>
                    </div>

                    <!-- Form -->
                    <form wire:submit="save">
                        <div class="px-6 py-5 space-y-4">
                            <x-jr.input label="Nome" wire:model="name" placeholder="Nome completo do contato"
                                        icon="person" :error="$errors->first('name')" />

                            <x-jr.input label="Email" wire:model="email" type="email" placeholder="email@exemplo.com"
                                        icon="email" :error="$errors->first('email')" />

                            <x-jr.input label="Telefone" wire:model="phone" placeholder="(00) 00000-0000"
                                        icon="phone" :error="$errors->first('phone')" />

                            <x-jr.input label="Empresa" wire:model="company" placeholder="Nome da empresa"
                                        icon="business" :error="$errors->first('company')" />

                            <x-jr.input label="Observacoes" wire:model="notes" placeholder="Notas sobre o contato"
                                        icon="notes" :error="$errors->first('notes')" />

                            <!-- Active Toggle -->
                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Status</label>
                                <button type="button" wire:click="$toggle('is_active')"
                                        class="flex items-center gap-3 w-full h-12 px-4 rounded-pill border transition-colors
                                               {{ $is_active ? 'border-primary-500 bg-primary-50' : 'border-mono-200 bg-mono-50' }}">
                                    <div class="relative w-10 h-6 rounded-full transition-colors {{ $is_active ? 'bg-primary-500' : 'bg-mono-300' }}">
                                        <div class="absolute top-1 {{ $is_active ? 'left-5' : 'left-1' }} w-4 h-4 rounded-full bg-mono-white shadow transition-all"></div>
                                    </div>
                                    <span class="text-sm font-medium {{ $is_active ? 'text-primary-500' : 'text-mono-600' }}">
                                        {{ $is_active ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </button>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-mono-100 bg-mono-50">
                            <x-jr.button variant="mono" wire:click="$set('showModal', false)" type="button">
                                Cancelar
                            </x-jr.button>
                            <x-jr.button type="submit" wire:loading.attr="disabled">
                                <span wire:loading wire:target="save" class="material-icons-outlined text-[18px] animate-spin">autorenew</span>
                                {{ $editingId ? 'Salvar' : 'Criar Contato' }}
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
                        <h3 class="text-lg font-bold text-mono-900">Excluir contato?</h3>
                        <p class="text-sm text-mono-600 mt-2">Esta acao nao pode ser desfeita. Todas as informacoes deste contato serao perdidas.</p>
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
