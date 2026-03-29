<div>
    {{-- Result Message --}}
    @if($showResult)
        <x-jr.card class="mb-6">
            <div class="text-center py-6">
                <div class="mx-auto w-16 h-16 rounded-full bg-success-bg flex items-center justify-center mb-4">
                    <span class="material-icons-outlined text-[32px] text-success">check_circle</span>
                </div>
                <h3 class="text-xl font-bold text-mono-900">Importacao concluida!</h3>
                <p class="text-sm text-mono-600 mt-2">
                    <span class="font-semibold text-success">{{ $importedCount }}</span> transacao(oes) importada(s)
                    @if($skippedCount > 0)
                        &middot; <span class="font-semibold text-mono-600">{{ $skippedCount }}</span> duplicada(s) ignorada(s)
                    @endif
                </p>
                <div class="flex items-center justify-center gap-3 mt-6">
                    <x-jr.button variant="mono" wire:click="resetImport">
                        <span class="material-icons-outlined text-[18px]">upload_file</span>
                        Importar outro arquivo
                    </x-jr.button>
                    <x-jr.button href="{{ route('financeiro.transacoes') }}">
                        <span class="material-icons-outlined text-[18px]">visibility</span>
                        Ver transacoes
                    </x-jr.button>
                </div>
            </div>
        </x-jr.card>
    @endif

    {{-- Upload Section --}}
    @if(!$parsed && !$showResult)
        <x-jr.card>
            <h3 class="text-lg font-bold text-mono-900 mb-1">Importar arquivo OFX</h3>
            <p class="text-sm text-mono-600 mb-6">Selecione a conta bancaria e o arquivo OFX exportado do seu banco.</p>

            <div class="space-y-5">
                {{-- Account Select --}}
                <div>
                    <label class="block text-sm font-medium text-mono-600 mb-1.5">Conta bancaria</label>
                    <select wire:model="accountId"
                            class="w-full bg-mono-white border border-mono-200 rounded-pill px-4 h-12 text-sm text-mono-900 focus:border-primary-500 focus:ring-0 transition-colors">
                        <option value="">Selecione uma conta...</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">
                                {{ $account->name }}
                                @if($account->bank) ({{ $account->bank }}) @endif
                                — R$ {{ number_format($account->current_balance, 2, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                    @error('accountId') <p class="text-xs font-medium text-error mt-1.5 pl-4">{{ $message }}</p> @enderror
                </div>

                {{-- File Upload --}}
                <div>
                    <label class="block text-sm font-medium text-mono-600 mb-1.5">Arquivo OFX</label>
                    <div class="relative"
                         x-data="{ dragging: false }"
                         @dragover.prevent="dragging = true"
                         @dragleave.prevent="dragging = false"
                         @drop.prevent="dragging = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change'))"
                    >
                        <label :class="dragging ? 'border-primary-500 bg-primary-100' : 'border-mono-200 hover:border-mono-300'"
                               class="flex flex-col items-center justify-center w-full h-36 border-2 border-dashed rounded-2xl cursor-pointer transition-colors">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <span class="material-icons-outlined text-[32px] text-mono-300 mb-2">cloud_upload</span>
                                @if($ofxFile)
                                    <p class="text-sm font-medium text-primary-500">{{ $ofxFile->getClientOriginalName() }}</p>
                                    <p class="text-xs text-mono-600 mt-1">{{ number_format($ofxFile->getSize() / 1024, 1) }} KB</p>
                                @else
                                    <p class="text-sm text-mono-600">
                                        <span class="font-semibold text-primary-500">Clique para selecionar</span> ou arraste o arquivo
                                    </p>
                                    <p class="text-xs text-mono-300 mt-1">Apenas arquivos .ofx (max. 5MB)</p>
                                @endif
                            </div>
                            <input type="file"
                                   wire:model="ofxFile"
                                   x-ref="fileInput"
                                   accept=".ofx,.OFX"
                                   class="hidden" />
                        </label>
                    </div>
                    @error('ofxFile') <p class="text-xs font-medium text-error mt-1.5 pl-4">{{ $message }}</p> @enderror
                </div>

                {{-- Parse Button --}}
                <div class="flex justify-end">
                    <x-jr.button wire:click="parse" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="parse" class="material-icons-outlined text-[18px]">search</span>
                        <span wire:loading wire:target="parse" class="material-icons-outlined text-[18px] animate-spin">autorenew</span>
                        <span wire:loading.remove wire:target="parse">Analisar arquivo</span>
                        <span wire:loading wire:target="parse">Analisando...</span>
                    </x-jr.button>
                </div>
            </div>
        </x-jr.card>
    @endif

    {{-- Preview Section --}}
    @if($parsed)
        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <x-jr.card>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-info-bg flex items-center justify-center">
                        <span class="material-icons-outlined text-[22px] text-info">receipt_long</span>
                    </div>
                    <div>
                        <p class="text-xs text-mono-600">Total de transacoes</p>
                        <p class="text-lg font-bold text-mono-900">{{ $activeCount }}</p>
                    </div>
                </div>
            </x-jr.card>
            <x-jr.card>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-up-bg flex items-center justify-center">
                        <span class="material-icons-outlined text-[22px] text-up">trending_up</span>
                    </div>
                    <div>
                        <p class="text-xs text-mono-600">Total receitas</p>
                        <p class="text-lg font-bold text-up">R$ {{ number_format($totalIncome, 2, ',', '.') }}</p>
                    </div>
                </div>
            </x-jr.card>
            <x-jr.card>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-down-bg flex items-center justify-center">
                        <span class="material-icons-outlined text-[22px] text-down">trending_down</span>
                    </div>
                    <div>
                        <p class="text-xs text-mono-600">Total despesas</p>
                        <p class="text-lg font-bold text-down">R$ {{ number_format($totalExpense, 2, ',', '.') }}</p>
                    </div>
                </div>
            </x-jr.card>
        </div>

        {{-- Income Transactions --}}
        @if(count($incomeTransactions) > 0)
            <div class="mb-6">
                <div class="flex items-center gap-2 mb-3">
                    <span class="material-icons-outlined text-[20px] text-up">arrow_upward</span>
                    <h3 class="text-base font-bold text-mono-900">Receitas</h3>
                    <x-jr.badge variant="success" size="sm">{{ collect($incomeTransactions)->where('_removed', false)->count() }}</x-jr.badge>
                </div>

                <x-jr.table>
                    <x-slot:head>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-mono-600 uppercase w-24">Data</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-mono-600 uppercase">Descricao</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-mono-600 uppercase w-48">Categoria</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-mono-600 uppercase w-32">Valor</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-mono-600 uppercase w-16"></th>
                    </x-slot:head>

                    @foreach($incomeTransactions as $txn)
                        <tr class="border-t border-mono-100 {{ $txn['_removed'] ? 'opacity-30 line-through' : '' }} hover:bg-mono-50 transition-colors">
                            <td class="px-4 py-3 text-sm text-mono-600 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($txn['date'])->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-mono-900 font-medium">
                                {{ $txn['description'] }}
                            </td>
                            <td class="px-4 py-3">
                                @if(!$txn['_removed'])
                                    <select wire:change="updateCategory({{ $txn['_index'] }}, $event.target.value)"
                                            class="w-full bg-mono-white border border-mono-200 rounded-lg px-2 py-1.5 text-xs text-mono-900 focus:border-primary-500 focus:ring-0">
                                        @foreach($incomeCategories as $cat)
                                            <option value="{{ $cat->id }}" {{ $txn['_category_id'] === $cat->id ? 'selected' : '' }}>
                                                {{ $cat->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm font-semibold text-up text-right whitespace-nowrap">
                                + R$ {{ number_format($txn['amount'], 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($txn['_removed'])
                                    <button wire:click="restoreTransaction({{ $txn['_index'] }})"
                                            class="p-1 rounded-lg text-success hover:bg-success-bg transition-colors" title="Restaurar">
                                        <span class="material-icons-outlined text-[18px]">undo</span>
                                    </button>
                                @else
                                    <button wire:click="removeTransaction({{ $txn['_index'] }})"
                                            class="p-1 rounded-lg text-mono-300 hover:text-error hover:bg-down-bg transition-colors" title="Remover">
                                        <span class="material-icons-outlined text-[18px]">close</span>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </x-jr.table>
            </div>
        @endif

        {{-- Expense Transactions --}}
        @if(count($expenseTransactions) > 0)
            <div class="mb-6">
                <div class="flex items-center gap-2 mb-3">
                    <span class="material-icons-outlined text-[20px] text-down">arrow_downward</span>
                    <h3 class="text-base font-bold text-mono-900">Despesas</h3>
                    <x-jr.badge variant="error" size="sm">{{ collect($expenseTransactions)->where('_removed', false)->count() }}</x-jr.badge>
                </div>

                <x-jr.table>
                    <x-slot:head>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-mono-600 uppercase w-24">Data</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-mono-600 uppercase">Descricao</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-mono-600 uppercase w-48">Categoria</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-mono-600 uppercase w-32">Valor</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-mono-600 uppercase w-16"></th>
                    </x-slot:head>

                    @foreach($expenseTransactions as $txn)
                        <tr class="border-t border-mono-100 {{ $txn['_removed'] ? 'opacity-30 line-through' : '' }} hover:bg-mono-50 transition-colors">
                            <td class="px-4 py-3 text-sm text-mono-600 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($txn['date'])->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-mono-900 font-medium">
                                {{ $txn['description'] }}
                            </td>
                            <td class="px-4 py-3">
                                @if(!$txn['_removed'])
                                    <select wire:change="updateCategory({{ $txn['_index'] }}, $event.target.value)"
                                            class="w-full bg-mono-white border border-mono-200 rounded-lg px-2 py-1.5 text-xs text-mono-900 focus:border-primary-500 focus:ring-0">
                                        @foreach($expenseCategories as $cat)
                                            <option value="{{ $cat->id }}" {{ $txn['_category_id'] === $cat->id ? 'selected' : '' }}>
                                                {{ $cat->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm font-semibold text-down text-right whitespace-nowrap">
                                - R$ {{ number_format($txn['amount'], 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($txn['_removed'])
                                    <button wire:click="restoreTransaction({{ $txn['_index'] }})"
                                            class="p-1 rounded-lg text-success hover:bg-success-bg transition-colors" title="Restaurar">
                                        <span class="material-icons-outlined text-[18px]">undo</span>
                                    </button>
                                @else
                                    <button wire:click="removeTransaction({{ $txn['_index'] }})"
                                            class="p-1 rounded-lg text-mono-300 hover:text-error hover:bg-down-bg transition-colors" title="Remover">
                                        <span class="material-icons-outlined text-[18px]">close</span>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </x-jr.table>
            </div>
        @endif

        {{-- Action Bar --}}
        <x-jr.card>
            <div class="flex items-center justify-between">
                <div class="text-sm text-mono-600">
                    <span class="font-semibold text-mono-900">{{ $activeCount }}</span> transacao(oes) serao importadas
                </div>
                <div class="flex items-center gap-3">
                    <x-jr.button variant="mono" wire:click="resetImport">
                        Cancelar
                    </x-jr.button>
                    <x-jr.button wire:click="importTransactions"
                                 wire:loading.attr="disabled"
                                 wire:confirm="Confirma a importacao de {{ $activeCount }} transacao(oes)?">
                        <span wire:loading.remove wire:target="importTransactions" class="material-icons-outlined text-[18px]">file_download</span>
                        <span wire:loading wire:target="importTransactions" class="material-icons-outlined text-[18px] animate-spin">autorenew</span>
                        <span wire:loading.remove wire:target="importTransactions">Importar transacoes</span>
                        <span wire:loading wire:target="importTransactions">Importando...</span>
                    </x-jr.button>
                </div>
            </div>
        </x-jr.card>
    @endif
</div>
