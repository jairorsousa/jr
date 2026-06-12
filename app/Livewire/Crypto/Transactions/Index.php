<?php

namespace App\Livewire\Crypto\Transactions;

use App\Enums\CryptoTransactionStatus;
use App\Enums\CryptoTransactionType;
use App\Models\Account;
use App\Models\CryptoAccount;
use App\Models\CryptoAsset;
use App\Models\CryptoNetwork;
use App\Models\CryptoTransaction;
use App\Services\CryptoTransactionService;
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
    public string $filterType = '';
    public string $filterStatus = '';
    public string $filterAsset = '';

    public string $crypto_account_id = '';
    public string $crypto_asset_id = '';
    public string $crypto_network_id = '';
    public string $type = 'bank_deposit';
    public string $status = 'confirmed';
    public string $amount_brl = '';
    public string $crypto_amount = '';
    public string $exchange_rate_brl = '';
    public string $fee_brl = '';
    public string $fee_crypto_amount = '';
    public string $tx_hash = '';
    public string $from_address = '';
    public string $to_address = '';
    public string $occurred_at = '';
    public string $description = '';
    public string $notes = '';
    public bool $sync_finance_transaction = true;
    public string $finance_account_id = '';
    public string $confirm_finance_account_id = '';

    protected $queryString = [
        'currentMonth' => ['except' => ''],
        'search' => ['except' => ''],
        'filterAccount' => ['except' => ''],
        'filterType' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'filterAsset' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->currentMonth = $this->currentMonth ?: now()->format('Y-m');
    }

    protected function rules(): array
    {
        return [
            'crypto_account_id' => 'required|uuid|exists:crypto_accounts,id',
            'crypto_asset_id' => 'nullable|uuid|exists:crypto_assets,id',
            'crypto_network_id' => 'nullable|uuid|exists:crypto_networks,id',
            'type' => 'required|string|in:' . implode(',', array_column(CryptoTransactionType::cases(), 'value')),
            'status' => 'required|string|in:' . implode(',', array_column(CryptoTransactionStatus::cases(), 'value')),
            'amount_brl' => 'required|numeric|min:0.01',
            'crypto_amount' => 'nullable|numeric|min:0',
            'exchange_rate_brl' => 'nullable|numeric|min:0',
            'fee_brl' => 'nullable|numeric|min:0',
            'fee_crypto_amount' => 'nullable|numeric|min:0',
            'tx_hash' => 'nullable|string|max:255',
            'from_address' => 'nullable|string|max:255',
            'to_address' => 'nullable|string|max:255',
            'occurred_at' => 'required|date',
            'description' => 'required|string|max:255',
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

    public function clearFilters(): void
    {
        $this->reset(['search', 'filterAccount', 'filterType', 'filterStatus', 'filterAsset']);
        $this->resetPage();
    }

    public function openCreateModal(?string $type = null): void
    {
        $this->resetForm();
        $this->type = $type ?: 'bank_deposit';
        $this->status = 'confirmed';
        $this->occurred_at = now()->format('Y-m-d\TH:i');
        $this->sync_finance_transaction = CryptoTransactionType::from($this->type)->affectsFinance();
        $this->showModal = true;
    }

    public function openEditModal(string $id): void
    {
        $transaction = CryptoTransaction::findOrFail($id);
        $this->editingId = $id;
        $this->crypto_account_id = $transaction->crypto_account_id;
        $this->crypto_asset_id = $transaction->crypto_asset_id ?? '';
        $this->crypto_network_id = $transaction->crypto_network_id ?? '';
        $this->type = $transaction->type->value;
        $this->status = $transaction->status->value;
        $this->amount_brl = (string) $transaction->amount_brl;
        $this->crypto_amount = (string) ($transaction->crypto_amount ?? '');
        $this->exchange_rate_brl = (string) ($transaction->exchange_rate_brl ?? '');
        $this->fee_brl = (string) ($transaction->fee_brl ?? '');
        $this->fee_crypto_amount = (string) ($transaction->fee_crypto_amount ?? '');
        $this->tx_hash = $transaction->tx_hash ?? '';
        $this->from_address = $transaction->from_address ?? '';
        $this->to_address = $transaction->to_address ?? '';
        $this->occurred_at = $transaction->occurred_at->format('Y-m-d\TH:i');
        $this->description = $transaction->description;
        $this->notes = $transaction->notes ?? '';
        $this->sync_finance_transaction = (bool) $transaction->finance_transaction_id;
        $this->finance_account_id = $transaction->financeTransaction?->account_id ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $type = CryptoTransactionType::from($this->type);
        $status = CryptoTransactionStatus::from($this->status);
        $financeAccountId = $this->sync_finance_transaction ? $this->finance_account_id : null;

        if ($type->affectsFinance() && $this->sync_finance_transaction && $status === CryptoTransactionStatus::Confirmed && !$financeAccountId) {
            $this->addError('finance_account_id', 'Selecione a conta financeira para aporte/resgate.');
            return;
        }

        $data = [
            'crypto_account_id' => $this->crypto_account_id,
            'crypto_asset_id' => $this->crypto_asset_id ?: null,
            'crypto_network_id' => $this->crypto_network_id ?: null,
            'type' => $this->type,
            'status' => $this->status,
            'amount_brl' => (float) $this->amount_brl,
            'crypto_amount' => $this->crypto_amount !== '' ? (float) $this->crypto_amount : null,
            'exchange_rate_brl' => $this->exchange_rate_brl !== '' ? (float) $this->exchange_rate_brl : null,
            'fee_brl' => $this->fee_brl !== '' ? (float) $this->fee_brl : 0,
            'fee_crypto_amount' => $this->fee_crypto_amount !== '' ? (float) $this->fee_crypto_amount : null,
            'tx_hash' => $this->tx_hash ?: null,
            'from_address' => $this->from_address ?: null,
            'to_address' => $this->to_address ?: null,
            'occurred_at' => Carbon::parse($this->occurred_at),
            'description' => $this->description,
            'notes' => $this->notes ?: null,
        ];

        if ($this->editingId) {
            app(CryptoTransactionService::class)->update(
                CryptoTransaction::findOrFail($this->editingId),
                $data,
                $financeAccountId,
                $this->sync_finance_transaction,
            );
            session()->flash('success', 'Transacao cripto atualizada com sucesso.');
        } else {
            app(CryptoTransactionService::class)->create($data, $financeAccountId);
            session()->flash('success', 'Transacao cripto criada com sucesso.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmTransaction(string $id): void
    {
        $transaction = CryptoTransaction::findOrFail($id);

        if ($transaction->type->affectsFinance() && !$transaction->finance_transaction_id) {
            $this->confirmingId = $id;
            $this->confirm_finance_account_id = '';
            $this->showConfirmModal = true;
            return;
        }

        app(CryptoTransactionService::class)->confirm($transaction);
        session()->flash('success', 'Transacao cripto confirmada.');
    }

    public function confirmWithFinance(): void
    {
        $this->validate([
            'confirm_finance_account_id' => 'required|uuid|exists:accounts,id',
        ]);

        app(CryptoTransactionService::class)->confirm(
            CryptoTransaction::findOrFail($this->confirmingId),
            $this->confirm_finance_account_id,
        );

        $this->showConfirmModal = false;
        $this->confirmingId = null;
        $this->confirm_finance_account_id = '';
        session()->flash('success', 'Transacao cripto confirmada e vinculada ao Financeiro.');
    }

    public function cancelTransaction(string $id): void
    {
        app(CryptoTransactionService::class)->cancel(CryptoTransaction::findOrFail($id));
        session()->flash('success', 'Transacao cripto cancelada.');
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        app(CryptoTransactionService::class)->delete(CryptoTransaction::findOrFail($this->deletingId));
        session()->flash('success', 'Transacao cripto excluida com sucesso.');
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function updatedType(): void
    {
        $this->sync_finance_transaction = CryptoTransactionType::from($this->type)->affectsFinance();
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId',
            'crypto_account_id',
            'crypto_asset_id',
            'crypto_network_id',
            'amount_brl',
            'crypto_amount',
            'exchange_rate_brl',
            'fee_brl',
            'fee_crypto_amount',
            'tx_hash',
            'from_address',
            'to_address',
            'occurred_at',
            'description',
            'notes',
            'finance_account_id',
        ]);
        $this->type = 'bank_deposit';
        $this->status = 'confirmed';
        $this->sync_finance_transaction = true;
        $this->resetValidation();
    }

    private function getFilteredQuery()
    {
        $ref = Carbon::parse($this->currentMonth . '-01');

        return CryptoTransaction::with([
                'cryptoAccount.institution',
                'asset',
                'network',
                'financeTransaction.account',
                'betTransaction.betAccount.bettingHouse',
            ])
            ->whereBetween('occurred_at', [$ref->copy()->startOfMonth(), $ref->copy()->endOfMonth()])
            ->when($this->search, fn ($query) => $query->where('description', 'like', "%{$this->search}%"))
            ->when($this->filterAccount, fn ($query) => $query->where('crypto_account_id', $this->filterAccount))
            ->when($this->filterType, fn ($query) => $query->where('type', $this->filterType))
            ->when($this->filterStatus, fn ($query) => $query->where('status', $this->filterStatus))
            ->when($this->filterAsset, fn ($query) => $query->where('crypto_asset_id', $this->filterAsset));
    }

    public function render()
    {
        $query = $this->getFilteredQuery();
        $confirmed = (clone $query)->where('status', CryptoTransactionStatus::Confirmed)->get();
        $inTotal = $confirmed->filter(fn (CryptoTransaction $transaction) => $transaction->type->isIn())->sum('amount_brl');
        $outTotal = $confirmed->filter(fn (CryptoTransaction $transaction) => $transaction->type->isOut())->sum('amount_brl');
        $transactions = $query->orderByDesc('occurred_at')->paginate(20);
        $cryptoAccounts = CryptoAccount::with('institution')->where('is_active', true)->orderBy('name')->get();
        $financeAccounts = Account::where('is_active', true)->orderBy('name')->get();
        $cryptoAssets = CryptoAsset::where('is_active', true)->orderBy('symbol')->get();
        $cryptoNetworks = CryptoNetwork::where('is_active', true)->orderBy('name')->get();
        $types = CryptoTransactionType::cases();
        $statuses = CryptoTransactionStatus::cases();
        $ref = Carbon::parse($this->currentMonth . '-01');
        $monthLabel = ucfirst($ref->translatedFormat('F Y'));
        $isCurrentMonth = $this->currentMonth === now()->format('Y-m');

        return view('livewire.crypto.transactions.index', compact(
            'transactions',
            'cryptoAccounts',
            'financeAccounts',
            'cryptoAssets',
            'cryptoNetworks',
            'types',
            'statuses',
            'inTotal',
            'outTotal',
            'monthLabel',
            'isCurrentMonth',
        ));
    }
}
