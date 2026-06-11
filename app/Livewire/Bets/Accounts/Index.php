<?php

namespace App\Livewire\Bets\Accounts;

use App\Enums\BetAccountStatus;
use App\Enums\BetVerificationStatus;
use App\Models\BetAccount;
use App\Models\BetUser;
use App\Models\BettingHouse;
use App\Services\BetBalanceService;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Index extends Component
{
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?string $editingId = null;
    public ?string $deletingId = null;

    public string $search = '';
    public string $filterHouse = '';
    public string $filterUser = '';
    public string $filterStatus = '';

    public string $betting_house_id = '';
    public string $bet_user_id = '';
    public string $name = '';
    public string $username = '';
    public string $account_code = '';
    public string $status = 'active';
    public string $verification_status = 'pending';
    public string $initial_balance = '0';
    public string $bonus_balance = '0';
    public string $withdrawable_balance = '';
    public string $daily_deposit_limit = '';
    public string $monthly_deposit_limit = '';
    public string $opened_at = '';
    public bool $is_active = true;
    public string $notes = '';

    protected function rules(): array
    {
        return [
            'betting_house_id' => 'required|uuid|exists:betting_houses,id',
            'bet_user_id' => 'required|uuid|exists:bet_users,id',
            'name' => 'required|string|max:255',
            'username' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('bet_accounts', 'username')
                    ->where('betting_house_id', $this->betting_house_id)
                    ->where('bet_user_id', $this->bet_user_id)
                    ->ignore($this->editingId),
            ],
            'account_code' => 'nullable|string|max:255',
            'status' => 'required|string|in:' . implode(',', array_column(BetAccountStatus::cases(), 'value')),
            'verification_status' => 'nullable|string|in:' . implode(',', array_column(BetVerificationStatus::cases(), 'value')),
            'initial_balance' => 'required|numeric|min:0',
            'bonus_balance' => 'nullable|numeric|min:0',
            'withdrawable_balance' => 'nullable|numeric|min:0',
            'daily_deposit_limit' => 'nullable|numeric|min:0',
            'monthly_deposit_limit' => 'nullable|numeric|min:0',
            'opened_at' => 'nullable|date',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:2000',
        ];
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(string $id): void
    {
        $account = BetAccount::findOrFail($id);
        $this->editingId = $id;
        $this->betting_house_id = $account->betting_house_id;
        $this->bet_user_id = $account->bet_user_id;
        $this->name = $account->name;
        $this->username = $account->username ?? '';
        $this->account_code = $account->account_code ?? '';
        $this->status = $account->status->value;
        $this->verification_status = $account->verification_status?->value ?? '';
        $this->initial_balance = (string) $account->initial_balance;
        $this->bonus_balance = (string) $account->bonus_balance;
        $this->withdrawable_balance = (string) ($account->withdrawable_balance ?? '');
        $this->daily_deposit_limit = (string) ($account->daily_deposit_limit ?? '');
        $this->monthly_deposit_limit = (string) ($account->monthly_deposit_limit ?? '');
        $this->opened_at = $account->opened_at?->format('Y-m-d') ?? '';
        $this->is_active = $account->is_active;
        $this->notes = $account->notes ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'betting_house_id' => $this->betting_house_id,
            'bet_user_id' => $this->bet_user_id,
            'name' => $this->name,
            'username' => $this->username ?: null,
            'account_code' => $this->account_code ?: null,
            'status' => $this->status,
            'verification_status' => $this->verification_status ?: null,
            'initial_balance' => (float) $this->initial_balance,
            'bonus_balance' => $this->bonus_balance !== '' ? (float) $this->bonus_balance : 0,
            'withdrawable_balance' => $this->withdrawable_balance !== '' ? (float) $this->withdrawable_balance : null,
            'daily_deposit_limit' => $this->daily_deposit_limit !== '' ? (float) $this->daily_deposit_limit : null,
            'monthly_deposit_limit' => $this->monthly_deposit_limit !== '' ? (float) $this->monthly_deposit_limit : null,
            'opened_at' => $this->opened_at ?: null,
            'is_active' => $this->is_active,
            'notes' => $this->notes ?: null,
        ];

        if ($this->editingId) {
            $account = BetAccount::findOrFail($this->editingId);
            $account->update($data);
            app(BetBalanceService::class)->recalculate($account);
            session()->flash('success', 'Conta de bet atualizada com sucesso.');
        } else {
            $data['current_balance'] = (float) $this->initial_balance;
            BetAccount::create($data);
            session()->flash('success', 'Conta de bet criada com sucesso.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function markChecked(string $id): void
    {
        BetAccount::findOrFail($id)->update(['last_checked_at' => now()]);
        session()->flash('success', 'Conferencia registrada.');
    }

    public function recalculate(string $id): void
    {
        app(BetBalanceService::class)->recalculate(BetAccount::findOrFail($id));
        session()->flash('success', 'Saldo recalculado com sucesso.');
    }

    public function toggleActive(string $id): void
    {
        $account = BetAccount::findOrFail($id);
        $account->update(['is_active' => !$account->is_active]);
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $account = BetAccount::findOrFail($this->deletingId);

        if ($account->transactions()->exists()) {
            session()->flash('error', 'Nao e possivel excluir uma conta com transacoes vinculadas.');
            $this->showDeleteModal = false;
            return;
        }

        $account->delete();
        session()->flash('success', 'Conta excluida com sucesso.');
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId',
            'betting_house_id',
            'bet_user_id',
            'name',
            'username',
            'account_code',
            'withdrawable_balance',
            'daily_deposit_limit',
            'monthly_deposit_limit',
            'opened_at',
            'notes',
        ]);
        $this->status = 'active';
        $this->verification_status = 'pending';
        $this->initial_balance = '0';
        $this->bonus_balance = '0';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $accounts = BetAccount::with(['bettingHouse', 'betUser'])
            ->when($this->search, function ($query) {
                $query->where(function ($subquery) {
                    $subquery->where('name', 'like', "%{$this->search}%")
                        ->orWhere('username', 'like', "%{$this->search}%")
                        ->orWhere('account_code', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterHouse, fn ($query) => $query->where('betting_house_id', $this->filterHouse))
            ->when($this->filterUser, fn ($query) => $query->where('bet_user_id', $this->filterUser))
            ->when($this->filterStatus, fn ($query) => $query->where('status', $this->filterStatus))
            ->orderByDesc('current_balance')
            ->get();

        $totalBalance = $accounts->sum('current_balance');
        $houses = BettingHouse::where('is_active', true)->orderBy('name')->get();
        $users = BetUser::where('is_active', true)->orderBy('name')->get();
        $statuses = BetAccountStatus::cases();
        $verificationStatuses = BetVerificationStatus::cases();

        return view('livewire.bets.accounts.index', compact(
            'accounts',
            'totalBalance',
            'houses',
            'users',
            'statuses',
            'verificationStatuses',
        ));
    }
}
