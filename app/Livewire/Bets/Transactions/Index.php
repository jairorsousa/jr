<?php

namespace App\Livewire\Bets\Transactions;

use App\Enums\BetTransactionStatus;
use App\Enums\BetTransactionType;
use App\Models\Account;
use App\Models\BetAccount;
use App\Models\BetTransaction;
use App\Services\BetTransactionService;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public bool $showConfirmModal = false;
    public ?string $editingId = null;
    public ?string $deletingId = null;
    public ?string $confirmingId = null;

    public string $currentMonth = '';
    public string $search = '';
    public string $filterAccount = '';
    public string $filterHouse = '';
    public string $filterUser = '';
    public string $filterType = '';
    public string $filterStatus = '';

    public string $bet_account_id = '';
    public string $type = 'deposit';
    public string $status = 'confirmed';
    public string $amount = '';
    public string $occurred_at = '';
    public string $description = '';
    public string $external_reference = '';
    public string $event_name = '';
    public string $market_name = '';
    public string $selection_name = '';
    public string $odd = '';
    public string $strategy = '';
    public string $notes = '';
    public bool $create_finance_transaction = true;
    public string $finance_account_id = '';
    public string $confirm_finance_account_id = '';

    protected $queryString = [
        'currentMonth' => ['except' => ''],
        'search' => ['except' => ''],
        'filterAccount' => ['except' => ''],
        'filterHouse' => ['except' => ''],
        'filterUser' => ['except' => ''],
        'filterType' => ['except' => ''],
        'filterStatus' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->currentMonth = $this->currentMonth ?: now()->format('Y-m');

        if (request('account') && !$this->filterAccount) {
            $this->filterAccount = request('account');
            $this->bet_account_id = request('account');
        }
    }

    protected function rules(): array
    {
        return [
            'bet_account_id' => 'required|uuid|exists:bet_accounts,id',
            'type' => 'required|string|in:' . implode(',', array_column(BetTransactionType::cases(), 'value')),
            'status' => 'required|string|in:' . implode(',', array_column(BetTransactionStatus::cases(), 'value')),
            'amount' => 'required|numeric|min:0.01',
            'occurred_at' => 'required|date',
            'description' => 'required|string|max:255',
            'external_reference' => 'nullable|string|max:255',
            'event_name' => 'nullable|string|max:255',
            'market_name' => 'nullable|string|max:255',
            'selection_name' => 'nullable|string|max:255',
            'odd' => 'nullable|numeric|min:0',
            'strategy' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
            'finance_account_id' => 'nullable|uuid|exists:accounts,id',
        ];
    }

    public function previousMonth(): void
    {
        $this->currentMonth = Carbon::parse($this->currentMonth . '-01')->subMonth()->format('Y-m');
        $this->resetPage();
    }

    public function nextMonth(): void
    {
        $this->currentMonth = Carbon::parse($this->currentMonth . '-01')->addMonth()->format('Y-m');
        $this->resetPage();
    }

    public function goToCurrentMonth(): void
    {
        $this->currentMonth = now()->format('Y-m');
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'filterAccount', 'filterHouse', 'filterUser', 'filterType', 'filterStatus']);
        $this->resetPage();
    }

    public function openCreateModal(?string $type = null): void
    {
        $account = $this->filterAccount ?: '';
        $this->resetForm();
        $this->bet_account_id = $account;
        $this->type = $type ?: 'deposit';
        $this->status = 'confirmed';
        $this->occurred_at = now()->format('Y-m-d\TH:i');
        $this->create_finance_transaction = true;
        $this->showModal = true;
    }

    public function openEditModal(string $id): void
    {
        $transaction = BetTransaction::findOrFail($id);
        $this->editingId = $id;
        $this->bet_account_id = $transaction->bet_account_id;
        $this->type = $transaction->type->value;
        $this->status = $transaction->status->value;
        $this->amount = (string) $transaction->amount;
        $this->occurred_at = $transaction->occurred_at->format('Y-m-d\TH:i');
        $this->description = $transaction->description;
        $this->external_reference = $transaction->external_reference ?? '';
        $this->event_name = $transaction->event_name ?? '';
        $this->market_name = $transaction->market_name ?? '';
        $this->selection_name = $transaction->selection_name ?? '';
        $this->odd = (string) ($transaction->odd ?? '');
        $this->strategy = $transaction->strategy ?? '';
        $this->notes = $transaction->notes ?? '';
        $this->create_finance_transaction = (bool) $transaction->finance_transaction_id;
        $this->finance_account_id = $transaction->financeTransaction?->account_id ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $type = BetTransactionType::from($this->type);
        $status = BetTransactionStatus::from($this->status);
        $financeAccountId = $this->create_finance_transaction ? $this->finance_account_id : null;

        if ($this->create_finance_transaction && $status === BetTransactionStatus::Confirmed && $type->affectsFinance() && !$financeAccountId) {
            $this->addError('finance_account_id', 'Selecione a conta financeira para vincular deposito/saque.');
            return;
        }

        $data = [
            'bet_account_id' => $this->bet_account_id,
            'type' => $this->type,
            'status' => $this->status,
            'amount' => (float) $this->amount,
            'occurred_at' => Carbon::parse($this->occurred_at),
            'description' => $this->description,
            'external_reference' => $this->external_reference ?: null,
            'event_name' => $this->event_name ?: null,
            'market_name' => $this->market_name ?: null,
            'selection_name' => $this->selection_name ?: null,
            'odd' => $this->odd !== '' ? (float) $this->odd : null,
            'strategy' => $this->strategy ?: null,
            'notes' => $this->notes ?: null,
        ];

        if ($this->editingId) {
            app(BetTransactionService::class)->update(BetTransaction::findOrFail($this->editingId), $data, $financeAccountId);
            session()->flash('success', 'Transacao de bet atualizada com sucesso.');
        } else {
            app(BetTransactionService::class)->create($data, $financeAccountId);
            session()->flash('success', 'Transacao de bet criada com sucesso.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmTransaction(string $id): void
    {
        $transaction = BetTransaction::findOrFail($id);

        if ($transaction->type->affectsFinance() && !$transaction->finance_transaction_id) {
            $this->confirmingId = $id;
            $this->confirm_finance_account_id = '';
            $this->showConfirmModal = true;
            return;
        }

        app(BetTransactionService::class)->confirm($transaction);
        session()->flash('success', 'Transacao confirmada com sucesso.');
    }

    public function confirmWithFinance(): void
    {
        $this->validate([
            'confirm_finance_account_id' => 'required|uuid|exists:accounts,id',
        ]);

        app(BetTransactionService::class)->confirm(
            BetTransaction::findOrFail($this->confirmingId),
            $this->confirm_finance_account_id,
        );

        $this->showConfirmModal = false;
        $this->confirmingId = null;
        $this->confirm_finance_account_id = '';
        session()->flash('success', 'Transacao confirmada e vinculada ao Financeiro.');
    }

    public function cancelTransaction(string $id): void
    {
        app(BetTransactionService::class)->cancel(BetTransaction::findOrFail($id));
        session()->flash('success', 'Transacao cancelada.');
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        app(BetTransactionService::class)->delete(BetTransaction::findOrFail($this->deletingId));
        session()->flash('success', 'Transacao excluida com sucesso.');
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId',
            'bet_account_id',
            'amount',
            'occurred_at',
            'description',
            'external_reference',
            'event_name',
            'market_name',
            'selection_name',
            'odd',
            'strategy',
            'notes',
            'finance_account_id',
        ]);
        $this->type = 'deposit';
        $this->status = 'confirmed';
        $this->create_finance_transaction = true;
        $this->resetValidation();
    }

    private function getFilteredQuery()
    {
        $ref = Carbon::parse($this->currentMonth . '-01');

        return BetTransaction::with(['betAccount.bettingHouse', 'betAccount.betUser', 'financeTransaction.account'])
            ->whereBetween('occurred_at', [$ref->copy()->startOfMonth(), $ref->copy()->endOfMonth()])
            ->when($this->search, fn ($query) => $query->where('description', 'like', "%{$this->search}%"))
            ->when($this->filterAccount, fn ($query) => $query->where('bet_account_id', $this->filterAccount))
            ->when($this->filterHouse, fn ($query) => $query->whereHas('betAccount', fn ($q) => $q->where('betting_house_id', $this->filterHouse)))
            ->when($this->filterUser, fn ($query) => $query->whereHas('betAccount', fn ($q) => $q->where('bet_user_id', $this->filterUser)))
            ->when($this->filterType, fn ($query) => $query->where('type', $this->filterType))
            ->when($this->filterStatus, fn ($query) => $query->where('status', $this->filterStatus));
    }

    public function render()
    {
        $query = $this->getFilteredQuery();
        $confirmed = (clone $query)->where('status', BetTransactionStatus::Confirmed)->get();
        $inTotal = $confirmed->filter(fn (BetTransaction $transaction) => $transaction->type->isIn())->sum('amount');
        $outTotal = $confirmed->filter(fn (BetTransaction $transaction) => $transaction->type->isOut())->sum('amount');
        $stake = $confirmed->filter(fn (BetTransaction $transaction) => $transaction->type === BetTransactionType::BetStake)->sum('amount');
        $payout = $confirmed->filter(fn (BetTransaction $transaction) => $transaction->type === BetTransactionType::BetPayout)->sum('amount');

        $transactions = $query->orderByDesc('occurred_at')->paginate(20);
        $betAccounts = BetAccount::with(['bettingHouse', 'betUser'])->where('is_active', true)->orderBy('name')->get();
        $financeAccounts = Account::where('is_active', true)->orderBy('name')->get();
        $houses = \App\Models\BettingHouse::where('is_active', true)->orderBy('name')->get();
        $users = \App\Models\BetUser::where('is_active', true)->orderBy('name')->get();
        $types = BetTransactionType::cases();
        $statuses = BetTransactionStatus::cases();
        $ref = Carbon::parse($this->currentMonth . '-01');
        $monthLabel = ucfirst($ref->translatedFormat('F Y'));
        $isCurrentMonth = $this->currentMonth === now()->format('Y-m');
        $profit = $payout - $stake;
        $roi = $stake > 0 ? ($profit / $stake) * 100 : 0;

        return view('livewire.bets.transactions.index', compact(
            'transactions',
            'betAccounts',
            'financeAccounts',
            'houses',
            'users',
            'types',
            'statuses',
            'inTotal',
            'outTotal',
            'stake',
            'payout',
            'profit',
            'roi',
            'monthLabel',
            'isCurrentMonth',
        ));
    }
}
