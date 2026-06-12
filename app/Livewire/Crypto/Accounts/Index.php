<?php

namespace App\Livewire\Crypto\Accounts;

use App\Enums\CryptoCustodyType;
use App\Models\BetUser;
use App\Models\CryptoAccount;
use App\Models\CryptoAsset;
use App\Models\CryptoInstitution;
use App\Models\CryptoNetwork;
use App\Services\CryptoBalanceService;
use Livewire\Component;

class Index extends Component
{
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?string $editingId = null;
    public ?string $deletingId = null;

    public string $search = '';
    public string $filterInstitution = '';
    public string $filterUser = '';
    public string $filterCustody = '';

    public string $crypto_institution_id = '';
    public string $bet_user_id = '';
    public string $name = '';
    public string $account_identifier = '';
    public string $custody_type = 'exchange';
    public string $initial_balance_brl = '0';
    public bool $is_active = true;
    public string $notes = '';

    public string $address = '';
    public string $address_label = '';
    public string $address_asset_id = '';
    public string $address_network_id = '';

    protected function rules(): array
    {
        return [
            'crypto_institution_id' => 'required|uuid|exists:crypto_institutions,id',
            'bet_user_id' => 'nullable|uuid|exists:bet_users,id',
            'name' => 'required|string|max:255',
            'account_identifier' => 'nullable|string|max:255',
            'custody_type' => 'required|string|in:' . implode(',', array_column(CryptoCustodyType::cases(), 'value')),
            'initial_balance_brl' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:2000',
            'address' => 'nullable|string|max:255',
            'address_label' => 'nullable|string|max:255',
            'address_asset_id' => 'nullable|uuid|exists:crypto_assets,id',
            'address_network_id' => 'nullable|uuid|exists:crypto_networks,id',
        ];
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(string $id): void
    {
        $account = CryptoAccount::with('walletAddresses')->findOrFail($id);
        $address = $account->walletAddresses->first();
        $this->editingId = $id;
        $this->crypto_institution_id = $account->crypto_institution_id;
        $this->bet_user_id = $account->bet_user_id ?? '';
        $this->name = $account->name;
        $this->account_identifier = $account->account_identifier ?? '';
        $this->custody_type = $account->custody_type->value;
        $this->initial_balance_brl = (string) $account->initial_balance_brl;
        $this->is_active = $account->is_active;
        $this->notes = $account->notes ?? '';
        $this->address = $address?->address ?? '';
        $this->address_label = $address?->label ?? '';
        $this->address_asset_id = $address?->crypto_asset_id ?? '';
        $this->address_network_id = $address?->crypto_network_id ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'crypto_institution_id' => $this->crypto_institution_id,
            'bet_user_id' => $this->bet_user_id ?: null,
            'name' => $this->name,
            'account_identifier' => $this->account_identifier ?: null,
            'custody_type' => $this->custody_type,
            'initial_balance_brl' => (float) $this->initial_balance_brl,
            'is_active' => $this->is_active,
            'notes' => $this->notes ?: null,
        ];

        if ($this->editingId) {
            $account = CryptoAccount::findOrFail($this->editingId);
            $account->update($data);
            app(CryptoBalanceService::class)->recalculate($account);
            session()->flash('success', 'Conta cripto atualizada com sucesso.');
        } else {
            $data['current_balance_brl'] = (float) $this->initial_balance_brl;
            $account = CryptoAccount::create($data);
            session()->flash('success', 'Conta cripto criada com sucesso.');
        }

        $this->syncPrimaryAddress($account);
        $this->showModal = false;
        $this->resetForm();
    }

    public function markChecked(string $id): void
    {
        CryptoAccount::findOrFail($id)->update(['last_checked_at' => now()]);
        session()->flash('success', 'Conferencia cripto registrada.');
    }

    public function recalculate(string $id): void
    {
        app(CryptoBalanceService::class)->recalculate(CryptoAccount::findOrFail($id));
        session()->flash('success', 'Saldo cripto recalculado com sucesso.');
    }

    public function toggleActive(string $id): void
    {
        $account = CryptoAccount::findOrFail($id);
        $account->update(['is_active' => !$account->is_active]);
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $account = CryptoAccount::findOrFail($this->deletingId);

        if ($account->transactions()->exists()) {
            session()->flash('error', 'Nao e possivel excluir uma conta cripto com transacoes vinculadas.');
            $this->showDeleteModal = false;
            return;
        }

        $account->delete();
        session()->flash('success', 'Conta cripto excluida com sucesso.');
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    private function syncPrimaryAddress(CryptoAccount $account): void
    {
        $currentAddress = $account->walletAddresses()->first();

        if (!$this->address) {
            $currentAddress?->delete();
            return;
        }

        $account->walletAddresses()->updateOrCreate(
            ['id' => $currentAddress?->id],
            [
                'crypto_asset_id' => $this->address_asset_id ?: null,
                'crypto_network_id' => $this->address_network_id ?: null,
                'address' => $this->address,
                'label' => $this->address_label ?: 'Endereco principal',
                'is_deposit_address' => true,
                'is_withdrawal_address' => true,
                'is_active' => true,
            ],
        );
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId',
            'crypto_institution_id',
            'bet_user_id',
            'name',
            'account_identifier',
            'notes',
            'address',
            'address_label',
            'address_asset_id',
            'address_network_id',
        ]);
        $this->custody_type = 'exchange';
        $this->initial_balance_brl = '0';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $accounts = CryptoAccount::with(['institution', 'betUser', 'walletAddresses.asset', 'walletAddresses.network'])
            ->when($this->search, function ($query) {
                $query->where(function ($subquery) {
                    $subquery->where('name', 'like', "%{$this->search}%")
                        ->orWhere('account_identifier', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterInstitution, fn ($query) => $query->where('crypto_institution_id', $this->filterInstitution))
            ->when($this->filterUser, fn ($query) => $query->where('bet_user_id', $this->filterUser))
            ->when($this->filterCustody, fn ($query) => $query->where('custody_type', $this->filterCustody))
            ->orderByDesc('current_balance_brl')
            ->get();

        $totalBalance = $accounts->sum('current_balance_brl');
        $institutions = CryptoInstitution::where('is_active', true)->orderBy('name')->get();
        $users = BetUser::where('is_active', true)->orderBy('name')->get();
        $custodyTypes = CryptoCustodyType::cases();
        $assets = CryptoAsset::where('is_active', true)->orderBy('symbol')->get();
        $networks = CryptoNetwork::where('is_active', true)->orderBy('name')->get();

        return view('livewire.crypto.accounts.index', compact(
            'accounts',
            'totalBalance',
            'institutions',
            'users',
            'custodyTypes',
            'assets',
            'networks',
        ));
    }
}
