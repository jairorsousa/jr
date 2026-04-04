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
                <x-jr.input wire:model.live.debounce.300ms="search" placeholder="Buscar templates..." icon="search" />
            </div>
            <select wire:model.live="filterCategory"
                    class="h-12 px-4 rounded-pill border border-mono-200 bg-mono-white text-sm text-mono-900 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors">
                <option value="">Todas categorias</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->value }}">{{ $cat->label() }}</option>
                @endforeach
            </select>
        </div>
        <x-jr.button wire:click="openCreateModal">
            <span class="material-icons-outlined text-[18px]">add</span>
            Novo Template
        </x-jr.button>
    </div>

    <!-- Templates Grid -->
    @if($templates->isEmpty())
        <x-jr.card>
            <div class="text-center py-12">
                <span class="material-icons-outlined text-[48px] text-mono-200">description</span>
                <p class="text-mono-600 mt-2">
                    @if($search || $filterCategory)
                        Nenhum template encontrado.
                    @else
                        Nenhum template criado ainda.
                    @endif
                </p>
                @unless($search || $filterCategory)
                    <div class="mt-4">
                        <x-jr.button wire:click="openCreateModal" size="sm">
                            Criar primeiro template
                        </x-jr.button>
                    </div>
                @endunless
            </div>
        </x-jr.card>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($templates as $template)
                <x-jr.card class="{{ !$template->is_active ? 'opacity-50' : '' }}">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-2 flex-1 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-primary-100 flex items-center justify-center flex-shrink-0">
                                <span class="material-icons-outlined text-[18px] text-primary-600">{{ $template->category->icon() }}</span>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-semibold text-mono-900 truncate">{{ $template->name }}</h3>
                                <x-jr.badge variant="{{ $template->category->color() }}" size="sm">
                                    {{ $template->category->label() }}
                                </x-jr.badge>
                            </div>
                        </div>
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="p-1.5 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                                <span class="material-icons-outlined text-[18px]">more_vert</span>
                            </button>
                            <div x-show="open" x-cloak @click.away="open = false"
                                 class="absolute right-0 top-8 bg-mono-white rounded-xl shadow-dropdown border border-mono-100 py-1.5 z-50 w-40">
                                <button wire:click="previewTemplate('{{ $template->id }}')" @click="open = false"
                                        class="flex items-center gap-2 w-full px-3 py-2 text-sm text-mono-900 hover:bg-mono-50">
                                    <span class="material-icons-outlined text-[16px] text-mono-300">visibility</span>
                                    Visualizar
                                </button>
                                <button wire:click="openEditModal('{{ $template->id }}')" @click="open = false"
                                        class="flex items-center gap-2 w-full px-3 py-2 text-sm text-mono-900 hover:bg-mono-50">
                                    <span class="material-icons-outlined text-[16px] text-mono-300">edit</span>
                                    Editar
                                </button>
                                <button wire:click="duplicateTemplate('{{ $template->id }}')" @click="open = false"
                                        class="flex items-center gap-2 w-full px-3 py-2 text-sm text-mono-900 hover:bg-mono-50">
                                    <span class="material-icons-outlined text-[16px] text-mono-300">content_copy</span>
                                    Duplicar
                                </button>
                                <button wire:click="toggleActive('{{ $template->id }}')" @click="open = false"
                                        class="flex items-center gap-2 w-full px-3 py-2 text-sm text-mono-900 hover:bg-mono-50">
                                    <span class="material-icons-outlined text-[16px] text-mono-300">
                                        {{ $template->is_active ? 'visibility_off' : 'visibility' }}
                                    </span>
                                    {{ $template->is_active ? 'Desativar' : 'Ativar' }}
                                </button>
                                <div class="border-t border-mono-100 my-1"></div>
                                <button wire:click="confirmDelete('{{ $template->id }}')" @click="open = false"
                                        class="flex items-center gap-2 w-full px-3 py-2 text-sm text-error hover:bg-down-bg">
                                    <span class="material-icons-outlined text-[16px]">delete</span>
                                    Excluir
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Body Preview -->
                    <div class="bg-mono-50 rounded-xl p-3 mb-3">
                        <p class="text-xs text-mono-700 whitespace-pre-line line-clamp-4">{{ $template->body }}</p>
                    </div>

                    <!-- Variables -->
                    @php $vars = $template->getVariables(); @endphp
                    @if(!empty($vars))
                        <div class="flex flex-wrap gap-1 mb-3">
                            @foreach($vars as $var)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-info-100 text-info-700">
                                    {{'{'}}{{ $var }}{{'}'}}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    <!-- Footer Stats -->
                    <div class="flex items-center justify-between pt-3 border-t border-mono-100">
                        <div class="flex items-center gap-1 text-[10px] text-mono-400">
                            <span class="material-icons-outlined text-[14px]">send</span>
                            {{ $template->usage_count }} uso(s)
                        </div>
                        <span class="text-[10px] text-mono-400">
                            {{ $template->updated_at->diffForHumans() }}
                        </span>
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
                    <!-- Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-mono-100">
                        <h3 class="text-lg font-bold text-mono-900">
                            {{ $editingId ? 'Editar Template' : 'Novo Template' }}
                        </h3>
                        <button wire:click="$set('showModal', false)" class="p-1 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                            <span class="material-icons-outlined text-[20px]">close</span>
                        </button>
                    </div>

                    <!-- Form -->
                    <form wire:submit="save">
                        <div class="px-6 py-5 space-y-4 max-h-[60vh] overflow-y-auto">
                            <x-jr.input label="Nome do Template" wire:model="name" placeholder="Ex: Boas-vindas, Follow-up..."
                                        icon="label" :error="$errors->first('name')" />

                            <!-- Category Select -->
                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Categoria</label>
                                <select wire:model="category"
                                        class="w-full h-12 px-4 rounded-pill border border-mono-200 bg-mono-white text-sm text-mono-900 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->value }}">{{ $cat->label() }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Body -->
                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Mensagem</label>
                                <textarea wire:model="body" rows="6" placeholder="Digite a mensagem do template...&#10;&#10;Use variaveis como {nome}, {empresa}, {telefone}"
                                          class="w-full px-4 py-3 rounded-xl border border-mono-200 bg-mono-white text-sm text-mono-900 placeholder-mono-300 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-colors resize-none"></textarea>
                                @error('body')
                                    <p class="text-xs text-error mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-[10px] text-mono-400 mt-1">
                                    Variaveis disponiveis: <code class="bg-mono-100 px-1 rounded">{nome}</code>
                                    <code class="bg-mono-100 px-1 rounded">{empresa}</code>
                                    <code class="bg-mono-100 px-1 rounded">{telefone}</code>
                                    <code class="bg-mono-100 px-1 rounded">{email}</code>
                                </p>
                            </div>

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
                                {{ $editingId ? 'Salvar' : 'Criar Template' }}
                            </x-jr.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Preview Modal -->
    @if($showPreviewModal && $previewTemplate)
        <div class="fixed inset-0 z-modal overflow-y-auto" wire:keydown.escape="$set('showPreviewModal', false)">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showPreviewModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-md overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-mono-100">
                        <h3 class="text-lg font-bold text-mono-900">{{ $previewTemplate->name }}</h3>
                        <button wire:click="$set('showPreviewModal', false)" class="p-1 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                            <span class="material-icons-outlined text-[20px]">close</span>
                        </button>
                    </div>
                    <div class="p-6">
                        <!-- WhatsApp-style bubble -->
                        <div class="bg-[#dcf8c6] rounded-xl rounded-tr-sm p-4 shadow-sm max-w-xs ml-auto">
                            <p class="text-sm text-mono-900 whitespace-pre-line">{{ $previewTemplate->render(['nome' => 'Joao', 'empresa' => 'Empresa ABC', 'telefone' => '11999998888', 'email' => 'joao@email.com']) }}</p>
                            <div class="text-right mt-1">
                                <span class="text-[10px] text-mono-500">12:00</span>
                                <span class="material-icons-outlined text-[12px] text-blue-500 ml-0.5">done_all</span>
                            </div>
                        </div>

                        @php $vars = $previewTemplate->getVariables(); @endphp
                        @if(!empty($vars))
                            <div class="mt-4 p-3 bg-mono-50 rounded-xl">
                                <p class="text-xs font-semibold text-mono-700 mb-2">Variaveis neste template:</p>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($vars as $var)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-info-100 text-info-700">
                                            {{ $var }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="mt-4 flex items-center justify-between text-xs text-mono-400">
                            <span>Categoria: {{ $previewTemplate->category->label() }}</span>
                            <span>{{ $previewTemplate->usage_count }} uso(s)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-modal overflow-y-auto">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showDeleteModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-sm overflow-hidden">
                    <div class="px-6 py-5 text-center">
                        <div class="mx-auto w-12 h-12 rounded-full bg-down-bg flex items-center justify-center mb-4">
                            <span class="material-icons-outlined text-[24px] text-error">delete</span>
                        </div>
                        <h3 class="text-lg font-bold text-mono-900">Excluir template?</h3>
                        <p class="text-sm text-mono-600 mt-2">Esta acao nao pode ser desfeita.</p>
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
