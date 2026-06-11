<?php

namespace App\Livewire\Bets\Houses;

use App\Models\BettingHouse;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Index extends Component
{
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?string $editingId = null;
    public ?string $deletingId = null;
    public string $search = '';

    public string $name = '';
    public string $slug = '';
    public string $website = '';
    public string $country = 'Brasil';
    public string $logo_url = '';
    public string $color = '#ff6f00';
    public string $min_deposit = '';
    public string $min_withdrawal = '';
    public string $deposit_fee_percent = '';
    public string $withdrawal_fee_percent = '';
    public string $withdrawal_time_hours = '';
    public bool $is_active = true;
    public string $notes = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('betting_houses', 'slug')->ignore($this->editingId)],
            'website' => 'nullable|url|max:255',
            'country' => 'nullable|string|max:100',
            'logo_url' => 'nullable|string|max:255',
            'color' => 'required|string|max:20',
            'min_deposit' => 'nullable|numeric|min:0',
            'min_withdrawal' => 'nullable|numeric|min:0',
            'deposit_fee_percent' => 'nullable|numeric|min:0|max:100',
            'withdrawal_fee_percent' => 'nullable|numeric|min:0|max:100',
            'withdrawal_time_hours' => 'nullable|integer|min:0',
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
        $house = BettingHouse::findOrFail($id);
        $this->editingId = $id;
        $this->name = $house->name;
        $this->slug = $house->slug;
        $this->website = $house->website ?? '';
        $this->country = $house->country ?? '';
        $this->logo_url = $house->logo_url ?? '';
        $this->color = $house->color;
        $this->min_deposit = (string) ($house->min_deposit ?? '');
        $this->min_withdrawal = (string) ($house->min_withdrawal ?? '');
        $this->deposit_fee_percent = (string) ($house->deposit_fee_percent ?? '');
        $this->withdrawal_fee_percent = (string) ($house->withdrawal_fee_percent ?? '');
        $this->withdrawal_time_hours = (string) ($house->withdrawal_time_hours ?? '');
        $this->is_active = $house->is_active;
        $this->notes = $house->notes ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'slug' => $this->slug ?: Str::slug($this->name),
            'website' => $this->website ?: null,
            'country' => $this->country ?: null,
            'logo_url' => $this->logo_url ?: null,
            'color' => $this->color,
            'min_deposit' => $this->min_deposit !== '' ? (float) $this->min_deposit : null,
            'min_withdrawal' => $this->min_withdrawal !== '' ? (float) $this->min_withdrawal : null,
            'deposit_fee_percent' => $this->deposit_fee_percent !== '' ? (float) $this->deposit_fee_percent : null,
            'withdrawal_fee_percent' => $this->withdrawal_fee_percent !== '' ? (float) $this->withdrawal_fee_percent : null,
            'withdrawal_time_hours' => $this->withdrawal_time_hours !== '' ? (int) $this->withdrawal_time_hours : null,
            'is_active' => $this->is_active,
            'notes' => $this->notes ?: null,
        ];

        if ($this->editingId) {
            BettingHouse::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Casa de apostas atualizada com sucesso.');
        } else {
            BettingHouse::create($data);
            session()->flash('success', 'Casa de apostas criada com sucesso.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleActive(string $id): void
    {
        $house = BettingHouse::findOrFail($id);
        $house->update(['is_active' => !$house->is_active]);
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $house = BettingHouse::findOrFail($this->deletingId);

        if ($house->accounts()->exists()) {
            session()->flash('error', 'Nao e possivel excluir uma casa com contas vinculadas.');
            $this->showDeleteModal = false;
            return;
        }

        $house->delete();
        session()->flash('success', 'Casa excluida com sucesso.');
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId',
            'name',
            'slug',
            'website',
            'logo_url',
            'min_deposit',
            'min_withdrawal',
            'deposit_fee_percent',
            'withdrawal_fee_percent',
            'withdrawal_time_hours',
            'notes',
        ]);
        $this->country = 'Brasil';
        $this->color = '#ff6f00';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $houses = BettingHouse::query()
            ->withCount('accounts')
            ->withSum('accounts', 'current_balance')
            ->when($this->search, fn ($query) => $query->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->get();

        return view('livewire.bets.houses.index', compact('houses'));
    }
}
