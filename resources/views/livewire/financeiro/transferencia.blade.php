<div>
    <!-- Trigger Button -->
    <x-jr.button variant="standard" wire:click="openModal" size="sm">
        <span class="material-icons-outlined text-[16px]">swap_horiz</span>
        Transferir
    </x-jr.button>

    <!-- Transfer Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-modal overflow-y-auto" wire:keydown.escape="$set('showModal', false)">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showModal', false)"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-mono-white rounded-2xl shadow-elevated w-full sm:max-w-md overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-mono-100">
                        <h3 class="text-lg font-bold text-mono-900">Transferencia entre Contas</h3>
                        <button wire:click="$set('showModal', false)" class="p-1 rounded-lg text-mono-300 hover:text-mono-600 hover:bg-mono-50 transition-colors">
                            <span class="material-icons-outlined text-[20px]">close</span>
                        </button>
                    </div>

                    <form wire:submit="save">
                        <div class="px-6 py-5 space-y-4">
                            <!-- From Account -->
                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Conta de origem</label>
                                <select wire:model="from_account_id"
                                        class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                    <option value="">Selecione...</option>
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->name }} (R$ {{ number_format($acc->current_balance, 2, ',', '.') }})</option>
                                    @endforeach
                                </select>
                                @error('from_account_id') <p class="text-xs font-medium text-error mt-1.5 pl-4">{{ $message }}</p> @enderror
                            </div>

                            <!-- Arrow icon -->
                            <div class="flex justify-center">
                                <div class="w-10 h-10 rounded-full bg-mono-50 flex items-center justify-center">
                                    <span class="material-icons-outlined text-[20px] text-mono-300">arrow_downward</span>
                                </div>
                            </div>

                            <!-- To Account -->
                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Conta de destino</label>
                                <select wire:model="to_account_id"
                                        class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                    <option value="">Selecione...</option>
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->name }} (R$ {{ number_format($acc->current_balance, 2, ',', '.') }})</option>
                                    @endforeach
                                </select>
                                @error('to_account_id') <p class="text-xs font-medium text-error mt-1.5 pl-4">{{ $message }}</p> @enderror
                            </div>

                            <x-jr.input label="Valor (R$)" wire:model="amount" type="number" step="0.01" min="0.01"
                                        icon="attach_money" :error="$errors->first('amount')" />

                            <div>
                                <label class="block text-sm font-medium text-mono-600 mb-1.5">Data</label>
                                <input type="date" wire:model="date"
                                       class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0">
                                @error('date') <p class="text-xs font-medium text-error mt-1.5 pl-4">{{ $message }}</p> @enderror
                            </div>

                            <x-jr.input label="Descricao (opcional)" wire:model="description" placeholder="Transferencia entre contas"
                                        icon="description" />
                        </div>

                        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-mono-100 bg-mono-50">
                            <x-jr.button variant="mono" wire:click="$set('showModal', false)" type="button">Cancelar</x-jr.button>
                            <x-jr.button type="submit" wire:loading.attr="disabled">
                                <span wire:loading wire:target="save" class="material-icons-outlined text-[18px] animate-spin">autorenew</span>
                                Transferir
                            </x-jr.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
