<?php

namespace App\Livewire\Financeiro;

use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Transaction;
use App\Services\BalanceService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Transferencia extends Component
{
    public bool $showModal = false;

    public string $from_account_id = '';
    public string $to_account_id = '';
    public string $amount = '';
    public string $date = '';
    public string $description = '';

    protected function rules(): array
    {
        return [
            'from_account_id' => 'required|uuid|exists:accounts,id|different:to_account_id',
            'to_account_id' => 'required|uuid|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'description' => 'nullable|string|max:255',
        ];
    }

    protected $messages = [
        'from_account_id.different' => 'A conta de origem deve ser diferente da conta de destino.',
    ];

    public function openModal(): void
    {
        $this->reset(['from_account_id', 'to_account_id', 'amount', 'description']);
        $this->date = now()->format('Y-m-d');
        $this->showModal = true;
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->validate();

        $desc = $this->description ?: 'Transferencia entre contas';
        $fromAccount = Account::findOrFail($this->from_account_id);
        $toAccount = Account::findOrFail($this->to_account_id);
        $balanceService = app(BalanceService::class);

        DB::transaction(function () use ($desc, $fromAccount, $toAccount, $balanceService) {
            // Expense on origin
            Transaction::create([
                'account_id' => $fromAccount->id,
                'type' => TransactionType::Expense,
                'description' => "$desc → {$toAccount->name}",
                'amount' => (float) $this->amount,
                'date' => $this->date,
                'is_paid' => true,
                'paid_at' => now(),
            ]);

            // Income on destination
            Transaction::create([
                'account_id' => $toAccount->id,
                'type' => TransactionType::Income,
                'description' => "$desc ← {$fromAccount->name}",
                'amount' => (float) $this->amount,
                'date' => $this->date,
                'is_paid' => true,
                'paid_at' => now(),
            ]);

            $balanceService->recalculate($fromAccount);
            $balanceService->recalculate($toAccount);
        });

        session()->flash('success', 'Transferencia realizada com sucesso.');
        $this->showModal = false;
        $this->dispatch('transfer-completed');
    }

    public function render()
    {
        $accounts = Account::where('is_active', true)->orderBy('name')->get();
        return view('livewire.financeiro.transferencia', compact('accounts'));
    }
}
