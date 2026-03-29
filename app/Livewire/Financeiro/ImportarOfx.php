<?php

namespace App\Livewire\Financeiro;

use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Services\BalanceService;
use App\Services\OfxParserService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class ImportarOfx extends Component
{
    use WithFileUploads;

    public $ofxFile = null;
    public string $accountId = '';

    // Parsed transactions state
    public array $transactions = [];
    public array $categories = [];  // transaction index => category_id mapping
    public array $removed = [];     // indices of removed transactions

    public bool $parsed = false;
    public int $importedCount = 0;
    public int $skippedCount = 0;
    public bool $showResult = false;

    // Summary
    public float $totalIncome = 0;
    public float $totalExpense = 0;

    public function updatedOfxFile(): void
    {
        $this->validate([
            'ofxFile' => 'required|file|max:5120', // 5MB max
        ]);
    }

    public function parse(): void
    {
        $this->validate([
            'ofxFile' => 'required|file|max:5120',
            'accountId' => 'required|exists:accounts,id',
        ]);

        $parser = new OfxParserService();
        $parsed = $parser->parse($this->ofxFile->getRealPath());

        if ($parsed->isEmpty()) {
            $this->addError('ofxFile', 'Nenhuma transacao encontrada no arquivo OFX.');
            return;
        }

        $this->transactions = $parsed->toArray();
        $this->removed = [];
        $this->categories = [];

        // Auto-assign categories
        $this->autoAssignCategories();

        // Calculate totals
        $this->recalculateTotals();

        $this->parsed = true;
        $this->showResult = false;
    }

    public function removeTransaction(int $index): void
    {
        $this->removed[] = $index;
        $this->removed = array_unique($this->removed);
        $this->recalculateTotals();
    }

    public function restoreTransaction(int $index): void
    {
        $this->removed = array_values(array_diff($this->removed, [$index]));
        $this->recalculateTotals();
    }

    public function updateCategory(int $index, string $categoryId): void
    {
        $this->categories[$index] = $categoryId;
    }

    public function importTransactions(): void
    {
        $account = Account::findOrFail($this->accountId);

        // 1. Collect all FITIDs to check duplicates in ONE query
        $fitids = [];
        foreach ($this->transactions as $index => $txn) {
            if (!in_array($index, $this->removed)) {
                $fitids[$index] = $txn['fitid'];
            }
        }

        // 2. Find existing FITIDs in one query
        $existingFitids = Transaction::where('account_id', $this->accountId)
            ->where(function ($query) use ($fitids) {
                foreach (array_chunk(array_values($fitids), 50) as $chunk) {
                    $query->orWhere(function ($q) use ($chunk) {
                        foreach ($chunk as $fitid) {
                            $q->orWhere('fitid', $fitid);
                        }
                    });
                }
            })
            ->pluck('fitid')
            ->toArray();

        $existingSet = array_flip($existingFitids);

        // 3. Build batch insert
        $toInsert = [];
        $skipped = 0;
        $now = now();

        foreach ($this->transactions as $index => $txn) {
            if (in_array($index, $this->removed)) {
                continue;
            }

            // Skip duplicates
            if (isset($existingSet[$txn['fitid']])) {
                $skipped++;
                continue;
            }

            $categoryId = $this->categories[$index] ?? $this->getDefaultCategoryId($txn['type']);

            $toInsert[] = [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'account_id' => $this->accountId,
                'category_id' => $categoryId,
                'type' => $txn['type'],
                'description' => $txn['description'],
                'fitid' => $txn['fitid'],
                'amount' => $txn['amount'],
                'date' => $txn['date'],
                'due_date' => $txn['date'],
                'is_paid' => true,
                'paid_at' => $txn['date'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // 4. Insert in chunks inside a transaction
        DB::transaction(function () use ($toInsert) {
            foreach (array_chunk($toInsert, 100) as $chunk) {
                Transaction::insert($chunk);
            }
        });

        // 5. Recalculate account balance
        app(BalanceService::class)->recalculate($account);

        $this->importedCount = count($toInsert);
        $this->skippedCount = $skipped;
        $this->showResult = true;
        $this->parsed = false;
        $this->transactions = [];
        $this->ofxFile = null;
    }

    public function resetImport(): void
    {
        $this->reset(['ofxFile', 'transactions', 'categories', 'removed', 'parsed', 'showResult', 'importedCount', 'skippedCount', 'totalIncome', 'totalExpense']);
        $this->resetValidation();
    }

    private function autoAssignCategories(): void
    {
        $expenseCategories = Category::where('type', TransactionType::Expense)->get();
        $incomeCategories = Category::where('type', TransactionType::Income)->get();

        // Keyword -> category mapping for auto-detection
        $keywordMap = [
            'uber' => 'Transporte',
            '99' => 'Transporte',
            'posto' => 'Transporte',
            'combustivel' => 'Transporte',
            'estacionamento' => 'Transporte',
            'pedagio' => 'Transporte',
            'ifood' => 'Alimentacao',
            'restaurante' => 'Alimentacao',
            'lanchonete' => 'Alimentacao',
            'padaria' => 'Alimentacao',
            'mercado' => 'Alimentacao',
            'supermercado' => 'Alimentacao',
            'hortifruti' => 'Alimentacao',
            'acougue' => 'Alimentacao',
            'farmacia' => 'Saude',
            'drogaria' => 'Saude',
            'hospital' => 'Saude',
            'clinica' => 'Saude',
            'medico' => 'Saude',
            'dentista' => 'Saude',
            'laboratorio' => 'Saude',
            'aluguel' => 'Moradia',
            'condominio' => 'Moradia',
            'energia' => 'Moradia',
            'agua' => 'Moradia',
            'gas' => 'Moradia',
            'internet' => 'Assinaturas',
            'netflix' => 'Assinaturas',
            'spotify' => 'Assinaturas',
            'amazon prime' => 'Assinaturas',
            'disney' => 'Assinaturas',
            'hbo' => 'Assinaturas',
            'youtube' => 'Assinaturas',
            'escola' => 'Educacao',
            'faculdade' => 'Educacao',
            'curso' => 'Educacao',
            'livro' => 'Educacao',
            'udemy' => 'Educacao',
            'cinema' => 'Lazer',
            'teatro' => 'Lazer',
            'show' => 'Lazer',
            'ingresso' => 'Lazer',
            'pet' => 'Pets',
            'veterinario' => 'Pets',
            'roupa' => 'Vestuario',
            'loja' => 'Vestuario',
            'calcado' => 'Vestuario',
            'imposto' => 'Impostos',
            'taxa' => 'Impostos',
            'tributo' => 'Impostos',
            'salario' => 'Salario',
            'pagamento' => 'Salario',
            'deposito' => 'Salario',
            'rendimento' => 'Investimentos',
            'dividendo' => 'Investimentos',
            'cashback' => 'Cashback',
            'estorno' => 'Cashback',
            'devolucao' => 'Cashback',
        ];

        foreach ($this->transactions as $index => $txn) {
            $desc = mb_strtolower($txn['description']);
            $assignedCategory = null;

            foreach ($keywordMap as $keyword => $categoryName) {
                if (str_contains($desc, mb_strtolower($keyword))) {
                    if ($txn['type'] === 'expense') {
                        $cat = $expenseCategories->firstWhere('name', $categoryName);
                    } else {
                        $cat = $incomeCategories->firstWhere('name', $categoryName);
                    }
                    if ($cat) {
                        $assignedCategory = $cat->id;
                        break;
                    }
                }
            }

            if (!$assignedCategory) {
                $assignedCategory = $this->getDefaultCategoryId($txn['type']);
            }

            $this->categories[$index] = $assignedCategory;
        }
    }

    private function getDefaultCategoryId(string $type): string
    {
        $transactionType = $type === 'income' ? TransactionType::Income : TransactionType::Expense;

        return Category::where('type', $transactionType)
            ->where('name', 'Outros')
            ->value('id') ?? Category::where('type', $transactionType)->value('id');
    }

    private function recalculateTotals(): void
    {
        $this->totalIncome = 0;
        $this->totalExpense = 0;

        foreach ($this->transactions as $index => $txn) {
            if (in_array($index, $this->removed)) {
                continue;
            }
            if ($txn['type'] === 'income') {
                $this->totalIncome += $txn['amount'];
            } else {
                $this->totalExpense += $txn['amount'];
            }
        }
    }

    public function render()
    {
        $accounts = Account::where('is_active', true)->orderBy('name')->get();
        $expenseCategories = Category::where('type', TransactionType::Expense)->orderBy('name')->get();
        $incomeCategories = Category::where('type', TransactionType::Income)->orderBy('name')->get();

        $incomeTransactions = [];
        $expenseTransactions = [];

        foreach ($this->transactions as $index => $txn) {
            $txn['_index'] = $index;
            $txn['_removed'] = in_array($index, $this->removed);
            $txn['_category_id'] = $this->categories[$index] ?? '';

            if ($txn['type'] === 'income') {
                $incomeTransactions[] = $txn;
            } else {
                $expenseTransactions[] = $txn;
            }
        }

        $activeCount = count($this->transactions) - count($this->removed);

        return view('livewire.financeiro.importar-ofx', compact(
            'accounts',
            'expenseCategories',
            'incomeCategories',
            'incomeTransactions',
            'expenseTransactions',
            'activeCount',
        ));
    }
}
