<?php

namespace App\Livewire\Financeiro;

use App\Enums\AccountType;
use App\Models\Account;
use App\Services\BalanceService;
use Livewire\Component;

class Contas extends Component
{
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?string $editingId = null;
    public ?string $deletingId = null;

    // Form fields
    public string $name = '';
    public string $type = 'checking';
    public string $bank = '';
    public string $initial_balance = '0';
    public string $color = '#ff6f00';
    public string $icon = 'account_balance';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', array_column(AccountType::cases(), 'value')),
            'bank' => 'nullable|string|max:255',
            'initial_balance' => 'required|numeric|min:0',
            'color' => 'required|string|max:20',
            'icon' => 'nullable|string|max:50',
        ];
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->showModal = true;
    }

    public function openEditModal(string $id): void
    {
        $account = Account::findOrFail($id);
        $this->editingId = $id;
        $this->name = $account->name;
        $this->type = $account->type->value;
        $this->bank = $account->bank ?? '';
        $this->initial_balance = (string) $account->initial_balance;
        $this->color = $account->color;
        $this->icon = $account->icon ?? 'account_balance';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'type' => $this->type,
            'bank' => $this->bank ?: null,
            'initial_balance' => (float) $this->initial_balance,
            'color' => $this->color,
            'icon' => $this->icon ?: null,
        ];

        if ($this->editingId) {
            $account = Account::findOrFail($this->editingId);
            $account->update($data);
            app(BalanceService::class)->recalculate($account);
            session()->flash('success', 'Conta atualizada com sucesso.');
        } else {
            $data['current_balance'] = (float) $this->initial_balance;
            Account::create($data);
            session()->flash('success', 'Conta criada com sucesso.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $account = Account::findOrFail($this->deletingId);

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

    public function toggleActive(string $id): void
    {
        $account = Account::findOrFail($id);
        $account->update(['is_active' => !$account->is_active]);
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'bank', 'editingId']);
        $this->type = 'checking';
        $this->initial_balance = '0';
        $this->color = '#ff6f00';
        $this->icon = 'account_balance';
        $this->resetValidation();
    }

    public function render()
    {
        $accounts = Account::orderBy('name')->get();
        $totalBalance = Account::where('is_active', true)->sum('current_balance');
        $accountTypes = AccountType::cases();

        return view('livewire.financeiro.contas', compact('accounts', 'totalBalance', 'accountTypes'));
    }
}
