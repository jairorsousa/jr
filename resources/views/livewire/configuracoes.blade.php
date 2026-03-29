<div class="max-w-3xl mx-auto space-y-6">
    {{-- Flash Messages --}}
    @if(session('success'))
        <x-jr.alert type="success">{{ session('success') }}</x-jr.alert>
    @endif

    {{-- Tabs --}}
    <div class="flex gap-1 bg-mono-white rounded-xl border border-mono-100 p-1">
        <button wire:click="$set('activeTab', 'perfil')"
                class="flex-1 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors
                       {{ $activeTab === 'perfil' ? 'bg-primary-500 text-white' : 'text-mono-600 hover:bg-mono-50' }}">
            <span class="material-icons-outlined text-[16px] align-middle mr-1">person</span>
            Perfil
        </button>
        <button wire:click="$set('activeTab', 'seguranca')"
                class="flex-1 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors
                       {{ $activeTab === 'seguranca' ? 'bg-primary-500 text-white' : 'text-mono-600 hover:bg-mono-50' }}">
            <span class="material-icons-outlined text-[16px] align-middle mr-1">lock</span>
            Seguranca
        </button>
        <button wire:click="$set('activeTab', 'aparencia')"
                class="flex-1 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors
                       {{ $activeTab === 'aparencia' ? 'bg-primary-500 text-white' : 'text-mono-600 hover:bg-mono-50' }}">
            <span class="material-icons-outlined text-[16px] align-middle mr-1">palette</span>
            Aparencia
        </button>
    </div>

    {{-- Perfil Tab --}}
    @if($activeTab === 'perfil')
        <x-jr.card>
            <h3 class="text-lg font-bold text-mono-900 mb-1">Informacoes do Perfil</h3>
            <p class="text-sm text-mono-600 mb-6">Atualize seu nome e email.</p>

            <form wire:submit="updateProfile" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-mono-900 mb-1">Nome</label>
                    <x-jr.input wire:model="name" placeholder="Seu nome" />
                    @error('name') <span class="text-xs text-error mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-mono-900 mb-1">Email</label>
                    <x-jr.input type="email" wire:model="email" placeholder="seu@email.com" />
                    @error('email') <span class="text-xs text-error mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end pt-2">
                    <x-jr.button type="submit" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="updateProfile">Salvar</span>
                        <span wire:loading wire:target="updateProfile">Salvando...</span>
                    </x-jr.button>
                </div>
            </form>
        </x-jr.card>
    @endif

    {{-- Seguranca Tab --}}
    @if($activeTab === 'seguranca')
        <x-jr.card>
            <h3 class="text-lg font-bold text-mono-900 mb-1">Alterar Senha</h3>
            <p class="text-sm text-mono-600 mb-6">Use uma senha forte com pelo menos 8 caracteres.</p>

            <form wire:submit="updatePassword" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-mono-900 mb-1">Senha Atual</label>
                    <x-jr.input type="password" wire:model="currentPassword" placeholder="Senha atual" />
                    @error('currentPassword') <span class="text-xs text-error mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-mono-900 mb-1">Nova Senha</label>
                    <x-jr.input type="password" wire:model="newPassword" placeholder="Nova senha" />
                    @error('newPassword') <span class="text-xs text-error mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-mono-900 mb-1">Confirmar Nova Senha</label>
                    <x-jr.input type="password" wire:model="newPasswordConfirmation" placeholder="Confirmar nova senha" />
                    @error('newPasswordConfirmation') <span class="text-xs text-error mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end pt-2">
                    <x-jr.button type="submit" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="updatePassword">Alterar Senha</span>
                        <span wire:loading wire:target="updatePassword">Alterando...</span>
                    </x-jr.button>
                </div>
            </form>
        </x-jr.card>

        {{-- Danger Zone --}}
        <x-jr.card>
            <h3 class="text-lg font-bold text-error mb-1">Zona de Perigo</h3>
            <p class="text-sm text-mono-600 mb-4">Ao excluir sua conta, todos os dados serao permanentemente removidos.</p>

            <div x-data="{ confirmDelete: false }">
                <button @click="confirmDelete = true"
                        x-show="!confirmDelete"
                        class="px-4 py-2 rounded-xl text-sm font-medium border border-error text-error hover:bg-down-bg transition-colors">
                    Excluir Conta
                </button>

                <div x-show="confirmDelete" x-cloak class="flex items-center gap-3 p-4 rounded-xl bg-down-bg border border-error/20">
                    <span class="material-icons-outlined text-error">warning</span>
                    <p class="text-sm text-error flex-1">Tem certeza? Esta acao nao pode ser desfeita.</p>
                    <button @click="confirmDelete = false"
                            class="px-3 py-1.5 rounded-lg text-sm font-medium text-mono-600 hover:bg-mono-100 transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="deleteAccount"
                            class="px-3 py-1.5 rounded-lg text-sm font-medium bg-error text-white hover:bg-error/90 transition-colors">
                        Sim, Excluir
                    </button>
                </div>
            </div>
        </x-jr.card>
    @endif

    {{-- Aparencia Tab --}}
    @if($activeTab === 'aparencia')
        <x-jr.card>
            <h3 class="text-lg font-bold text-mono-900 mb-1">Tema</h3>
            <p class="text-sm text-mono-600 mb-6">Escolha entre o tema claro e escuro.</p>

            <div class="flex gap-4">
                <button @click="darkMode = false; localStorage.setItem('jr-theme', 'light')"
                        class="flex-1 p-4 rounded-xl border-2 transition-all"
                        :class="!darkMode ? 'border-primary-500 bg-primary-100' : 'border-mono-200 hover:border-mono-300'">
                    <div class="w-full h-20 rounded-lg bg-[#f5f6f7] border border-[#ecedef] mb-3 flex items-center justify-center">
                        <span class="material-icons-outlined text-[32px] text-[#ff6f00]">light_mode</span>
                    </div>
                    <p class="text-sm font-semibold text-mono-900 text-center">Claro</p>
                </button>

                <button @click="darkMode = true; localStorage.setItem('jr-theme', 'dark')"
                        class="flex-1 p-4 rounded-xl border-2 transition-all"
                        :class="darkMode ? 'border-primary-500 bg-primary-100' : 'border-mono-200 hover:border-mono-300'">
                    <div class="w-full h-20 rounded-lg bg-[#1a1d21] border border-[#2c3138] mb-3 flex items-center justify-center">
                        <span class="material-icons-outlined text-[32px] text-[#ff8c33]">dark_mode</span>
                    </div>
                    <p class="text-sm font-semibold text-mono-900 text-center">Escuro</p>
                </button>
            </div>
        </x-jr.card>

        <x-jr.card>
            <h3 class="text-lg font-bold text-mono-900 mb-1">Sobre</h3>
            <p class="text-sm text-mono-600 mb-4">Sistema JR - Gerenciamento Financeiro Pessoal</p>

            <div class="space-y-2 text-sm">
                <div class="flex justify-between py-2 border-b border-mono-100">
                    <span class="text-mono-600">Versao</span>
                    <span class="font-medium text-mono-900">1.0.0</span>
                </div>
                <div class="flex justify-between py-2 border-b border-mono-100">
                    <span class="text-mono-600">Laravel</span>
                    <span class="font-medium text-mono-900">{{ app()->version() }}</span>
                </div>
                <div class="flex justify-between py-2">
                    <span class="text-mono-600">PHP</span>
                    <span class="font-medium text-mono-900">{{ PHP_VERSION }}</span>
                </div>
            </div>
        </x-jr.card>
    @endif
</div>
