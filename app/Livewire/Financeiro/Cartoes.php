<?php

namespace App\Livewire\Financeiro;

use App\Enums\CardBrand;
use App\Models\Account;
use App\Models\CreditCard;
use App\Services\InvoiceService;
use Livewire\Component;

class Cartoes extends Component
{
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?string $editingId = null;
    public ?string $deletingId = null;

    // Form fields
    public string $name = '';
    public string $last_digits = '';
    public string $brand = 'visa';
    public string $credit_limit = '';
    public int $closing_day = 1;
    public int $due_day = 10;
    public string $color = '#212529';
    public string $account_id = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'last_digits' => 'required|string|size:4',
            'brand' => 'required|string|in:' . implode(',', array_column(CardBrand::cases(), 'value')),
            'credit_limit' => 'required|numeric|min:0',
            'closing_day' => 'required|integer|min:1|max:31',
            'due_day' => 'required|integer|min:1|max:31',
            'color' => 'required|string|max:20',
            'account_id' => 'nullable|uuid|exists:accounts,id',
        ];
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(string $id): void
    {
        $card = CreditCard::findOrFail($id);
        $this->editingId = $id;
        $this->name = $card->name;
        $this->last_digits = $card->last_digits;
        $this->brand = $card->brand->value;
        $this->credit_limit = (string) $card->credit_limit;
        $this->closing_day = $card->closing_day;
        $this->due_day = $card->due_day;
        $this->color = $card->color;
        $this->account_id = $card->account_id ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'last_digits' => $this->last_digits,
            'brand' => $this->brand,
            'credit_limit' => (float) $this->credit_limit,
            'closing_day' => $this->closing_day,
            'due_day' => $this->due_day,
            'color' => $this->color,
            'account_id' => $this->account_id ?: null,
        ];

        if ($this->editingId) {
            CreditCard::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Cartao atualizado com sucesso.');
        } else {
            CreditCard::create($data);
            session()->flash('success', 'Cartao criado com sucesso.');
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
        $card = CreditCard::findOrFail($this->deletingId);

        if ($card->transactions()->exists()) {
            session()->flash('error', 'Nao e possivel excluir um cartao com transacoes vinculadas.');
            $this->showDeleteModal = false;
            return;
        }

        $card->delete();
        session()->flash('success', 'Cartao excluido com sucesso.');
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function toggleActive(string $id): void
    {
        $card = CreditCard::findOrFail($id);
        $card->update(['is_active' => !$card->is_active]);
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'last_digits', 'credit_limit', 'editingId', 'account_id']);
        $this->brand = 'visa';
        $this->closing_day = 1;
        $this->due_day = 10;
        $this->color = '#212529';
        $this->resetValidation();
    }

    public function render()
    {
        $invoiceService = app(InvoiceService::class);
        $cards = CreditCard::with('account')->orderBy('name')->get();
        $accounts = Account::where('is_active', true)->orderBy('name')->get();
        $brands = CardBrand::cases();

        // Calculate current invoice amounts
        $cardInvoices = [];
        foreach ($cards as $card) {
            $cardInvoices[$card->id] = $invoiceService->getCurrentInvoiceAmount($card);
        }

        return view('livewire.financeiro.cartoes', compact('cards', 'accounts', 'brands', 'cardInvoices'));
    }
}
