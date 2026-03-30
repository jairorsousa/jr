<?php

namespace App\Livewire\Financeiro;

use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\CreditCard;
use App\Models\Transaction;
use App\Services\BalanceService;
use App\Services\InvoiceService;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class Transacoes extends Component
{
    use WithPagination;

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?string $editingId = null;
    public ?string $deletingId = null;

    // Month navigation
    public string $currentMonth;

    // Custom date range
    public bool $customRange = false;
    public string $filterDateFrom = '';
    public string $filterDateTo = '';

    // Filters
    public string $search = '';
    public string $filterType = '';
    public string $filterCategory = '';
    public string $filterAccount = '';
    public string $filterStatus = '';

    // Form fields
    public string $type = 'expense';
    public string $description = '';
    public string $amount = '';
    public string $date = '';
    public string $due_date = '';
    public string $category_id = '';
    public string $account_id = '';
    public bool $is_paid = false;
    public string $notes = '';
    public string $credit_card_id = '';
    public int $installments = 1;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterType' => ['except' => ''],
        'filterCategory' => ['except' => ''],
        'filterAccount' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'currentMonth' => ['except' => ''],
    ];

    public function mount(): void
    {
        if (!$this->currentMonth) {
            $this->currentMonth = now()->format('Y-m');
        }
    }

    protected function rules(): array
    {
        return [
            'type' => 'required|in:income,expense',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'due_date' => 'nullable|date',
            'category_id' => 'required|uuid|exists:categories,id',
            'account_id' => $this->credit_card_id ? 'nullable|uuid|exists:accounts,id' : 'required|uuid|exists:accounts,id',
            'is_paid' => 'boolean',
            'notes' => 'nullable|string|max:1000',
            'credit_card_id' => 'nullable|uuid|exists:credit_cards,id',
            'installments' => 'integer|min:1|max:48',
        ];
    }

    public function previousMonth(): void
    {
        $this->currentMonth = Carbon::parse($this->currentMonth . '-01')->subMonth()->format('Y-m');
        $this->customRange = false;
        $this->filterDateFrom = '';
        $this->filterDateTo = '';
        $this->resetPage();
    }

    public function nextMonth(): void
    {
        $this->currentMonth = Carbon::parse($this->currentMonth . '-01')->addMonth()->format('Y-m');
        $this->customRange = false;
        $this->filterDateFrom = '';
        $this->filterDateTo = '';
        $this->resetPage();
    }

    public function goToCurrentMonth(): void
    {
        $this->currentMonth = now()->format('Y-m');
        $this->customRange = false;
        $this->filterDateFrom = '';
        $this->filterDateTo = '';
        $this->resetPage();
    }

    public function applyCustomRange(): void
    {
        if ($this->filterDateFrom && $this->filterDateTo) {
            $this->customRange = true;
            $this->resetPage();
        }
    }

    public function clearCustomRange(): void
    {
        $this->customRange = false;
        $this->filterDateFrom = '';
        $this->filterDateTo = '';
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCategory(): void
    {
        $this->resetPage();
    }

    public function updatingFilterAccount(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'filterType', 'filterCategory', 'filterAccount', 'filterStatus']);
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->date = now()->format('Y-m-d');
        $this->showModal = true;
    }

    public function openEditModal(string $id): void
    {
        $transaction = Transaction::findOrFail($id);
        $this->editingId = $id;
        $this->type = $transaction->type->value;
        $this->description = $transaction->description;
        $this->amount = (string) $transaction->amount;
        $this->date = $transaction->date->format('Y-m-d');
        $this->due_date = $transaction->due_date?->format('Y-m-d') ?? '';
        $this->category_id = $transaction->category_id ?? '';
        $this->account_id = $transaction->account_id;
        $this->is_paid = $transaction->is_paid;
        $this->notes = $transaction->notes ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        // Handle installment purchases on credit card
        if (!$this->editingId && $this->credit_card_id && $this->installments > 1) {
            $card = CreditCard::findOrFail($this->credit_card_id);
            app(InvoiceService::class)->createInstallments(
                card: $card,
                description: $this->description,
                totalAmount: (float) $this->amount,
                installments: $this->installments,
                firstDate: Carbon::parse($this->date),
                categoryId: $this->category_id ?: null,
            );
            session()->flash('success', "Compra parcelada em {$this->installments}x criada com sucesso.");
            $this->showModal = false;
            $this->resetForm();
            return;
        }

        $data = [
            'type' => $this->type,
            'description' => $this->description,
            'amount' => (float) $this->amount,
            'date' => $this->date,
            'due_date' => $this->due_date ?: null,
            'category_id' => $this->category_id ?: null,
            'account_id' => $this->account_id,
            'credit_card_id' => $this->credit_card_id ?: null,
            'is_paid' => $this->is_paid,
            'paid_at' => $this->is_paid ? now() : null,
            'notes' => $this->notes ?: null,
        ];

        // Single credit card purchase — link to invoice
        if ($this->credit_card_id && !$this->editingId) {
            $card = CreditCard::findOrFail($this->credit_card_id);
            $invoiceService = app(InvoiceService::class);
            $invoiceMonth = $invoiceService->getInvoiceMonth($card, Carbon::parse($this->date));
            $invoice = $invoiceService->getOrCreateInvoice($card, $invoiceMonth);
            $data['credit_card_invoice_id'] = $invoice->id;
            $data['account_id'] = $card->account_id ?? $this->account_id;
        }

        if ($this->editingId) {
            $transaction = Transaction::findOrFail($this->editingId);
            $oldAccountId = $transaction->account_id;
            $transaction->update($data);

            app(BalanceService::class)->recalculate($transaction->account);
            if ($oldAccountId !== $this->account_id) {
                app(BalanceService::class)->recalculate(Account::find($oldAccountId));
            }

            if ($transaction->credit_card_invoice_id) {
                app(InvoiceService::class)->calculateTotal($transaction->invoice);
            }

            session()->flash('success', 'Transacao atualizada com sucesso.');
        } else {
            $transaction = Transaction::create($data);
            app(BalanceService::class)->recalculate($transaction->account);

            if ($transaction->credit_card_invoice_id) {
                app(InvoiceService::class)->calculateTotal($transaction->invoice);
            }

            session()->flash('success', 'Transacao criada com sucesso.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function markAsPaid(string $id): void
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->update([
            'is_paid' => true,
            'paid_at' => now(),
        ]);
        app(BalanceService::class)->recalculate($transaction->account);
        session()->flash('success', 'Transacao marcada como paga.');
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $transaction = Transaction::findOrFail($this->deletingId);
        $account = $transaction->account;
        $transaction->delete();
        app(BalanceService::class)->recalculate($account);

        session()->flash('success', 'Transacao excluida com sucesso.');
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'description', 'amount', 'date', 'due_date', 'category_id', 'account_id', 'notes', 'credit_card_id']);
        $this->type = 'expense';
        $this->is_paid = false;
        $this->installments = 1;
        $this->resetValidation();
    }

    private function getFilteredQuery()
    {
        $query = Transaction::with(['account', 'category']);

        // Date filtering: custom range or month navigation
        if ($this->customRange && $this->filterDateFrom && $this->filterDateTo) {
            $query->where('date', '>=', $this->filterDateFrom)
                  ->where('date', '<=', $this->filterDateTo);
        } else {
            $ref = Carbon::parse($this->currentMonth . '-01');
            $query->where('date', '>=', $ref->startOfMonth()->format('Y-m-d'))
                  ->where('date', '<=', $ref->endOfMonth()->format('Y-m-d'));
        }

        $query->when($this->search, fn ($q) => $q->where('description', 'like', "%{$this->search}%"))
              ->when($this->filterType, fn ($q) => $q->where('type', $this->filterType))
              ->when($this->filterCategory, fn ($q) => $q->where('category_id', $this->filterCategory))
              ->when($this->filterAccount, fn ($q) => $q->where('account_id', $this->filterAccount))
              ->when($this->filterStatus === 'paid', fn ($q) => $q->where('is_paid', true))
              ->when($this->filterStatus === 'pending', fn ($q) => $q->where('is_paid', false));

        return $query;
    }

    public function render()
    {
        $query = $this->getFilteredQuery();

        $totalIncome = (clone $query)->where('type', TransactionType::Income)->sum('amount');
        $totalExpense = (clone $query)->where('type', TransactionType::Expense)->sum('amount');

        $transactions = $query->orderByDesc('date')->orderByDesc('created_at')->paginate(20);
        $accounts = Account::where('is_active', true)->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $creditCards = CreditCard::where('is_active', true)->orderBy('name')->get();

        $ref = Carbon::parse($this->currentMonth . '-01');
        $monthLabel = ucfirst($ref->translatedFormat('F Y'));
        $isCurrentMonth = $this->currentMonth === now()->format('Y-m');

        return view('livewire.financeiro.transacoes', compact(
            'transactions', 'accounts', 'categories', 'creditCards',
            'totalIncome', 'totalExpense', 'monthLabel', 'isCurrentMonth'
        ));
    }
}
