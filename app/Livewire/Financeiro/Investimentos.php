<?php

namespace App\Livewire\Financeiro;

use App\Enums\InvestmentType;
use App\Models\Investment;
use Livewire\Component;

class Investimentos extends Component
{
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public bool $showUpdateValueModal = false;
    public ?string $editingId = null;
    public ?string $deletingId = null;
    public ?string $updatingId = null;

    // Form fields
    public string $name = '';
    public string $type = 'fixed_income';
    public string $broker = '';
    public string $invested_amount = '';
    public string $current_amount = '';
    public string $quantity = '';
    public string $purchase_date = '';
    public string $maturity_date = '';
    public string $notes = '';

    // Update value field
    public string $new_value = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', array_column(InvestmentType::cases(), 'value')),
            'broker' => 'nullable|string|max:255',
            'invested_amount' => 'required|numeric|min:0',
            'current_amount' => 'required|numeric|min:0',
            'quantity' => 'nullable|numeric|min:0',
            'purchase_date' => 'required|date',
            'maturity_date' => 'nullable|date|after_or_equal:purchase_date',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->purchase_date = now()->format('Y-m-d');
        $this->showModal = true;
    }

    public function openEditModal(string $id): void
    {
        $inv = Investment::findOrFail($id);
        $this->editingId = $id;
        $this->name = $inv->name;
        $this->type = $inv->type->value;
        $this->broker = $inv->broker ?? '';
        $this->invested_amount = (string) $inv->invested_amount;
        $this->current_amount = (string) $inv->current_amount;
        $this->quantity = $inv->quantity ? (string) $inv->quantity : '';
        $this->purchase_date = $inv->purchase_date->format('Y-m-d');
        $this->maturity_date = $inv->maturity_date?->format('Y-m-d') ?? '';
        $this->notes = $inv->notes ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'type' => $this->type,
            'broker' => $this->broker ?: null,
            'invested_amount' => (float) $this->invested_amount,
            'current_amount' => (float) $this->current_amount,
            'quantity' => $this->quantity ? (float) $this->quantity : null,
            'purchase_date' => $this->purchase_date,
            'maturity_date' => $this->maturity_date ?: null,
            'notes' => $this->notes ?: null,
        ];

        if ($this->editingId) {
            Investment::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Investimento atualizado com sucesso.');
        } else {
            Investment::create($data);
            session()->flash('success', 'Investimento criado com sucesso.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function openUpdateValue(string $id): void
    {
        $inv = Investment::findOrFail($id);
        $this->updatingId = $id;
        $this->new_value = (string) $inv->current_amount;
        $this->showUpdateValueModal = true;
    }

    public function updateValue(): void
    {
        $this->validate(['new_value' => 'required|numeric|min:0']);

        Investment::findOrFail($this->updatingId)->update([
            'current_amount' => (float) $this->new_value,
        ]);

        session()->flash('success', 'Valor atualizado com sucesso.');
        $this->showUpdateValueModal = false;
        $this->updatingId = null;
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        Investment::findOrFail($this->deletingId)->delete();
        session()->flash('success', 'Investimento excluido com sucesso.');
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'broker', 'invested_amount', 'current_amount', 'quantity', 'purchase_date', 'maturity_date', 'notes']);
        $this->type = 'fixed_income';
        $this->resetValidation();
    }

    public function render()
    {
        $investments = Investment::orderBy('name')->get();
        $types = InvestmentType::cases();

        // Consolidated totals
        $totalInvested = $investments->sum('invested_amount');
        $totalCurrent = $investments->sum('current_amount');
        $totalProfit = $totalCurrent - $totalInvested;
        $totalProfitPct = $totalInvested > 0 ? ($totalProfit / $totalInvested) * 100 : 0;

        // Distribution by type for donut chart
        $distribution = [];
        $typeColors = [
            'crypto' => '#FFA726',
            'fixed_income' => '#42A5F5',
            'stocks' => '#66BB6A',
            'funds' => '#AB47BC',
            'other' => '#78909C',
        ];

        foreach (InvestmentType::cases() as $invType) {
            $amount = $investments->where('type', $invType)->sum('current_amount');
            if ($amount > 0) {
                $distribution[] = [
                    'label' => $invType->label(),
                    'value' => (float) $amount,
                    'color' => $typeColors[$invType->value] ?? '#78909C',
                    'percentage' => $totalCurrent > 0 ? round(($amount / $totalCurrent) * 100, 1) : 0,
                ];
            }
        }

        return view('livewire.financeiro.investimentos', compact(
            'investments', 'types', 'totalInvested', 'totalCurrent',
            'totalProfit', 'totalProfitPct', 'distribution'
        ));
    }
}
