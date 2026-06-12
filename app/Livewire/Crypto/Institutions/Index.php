<?php

namespace App\Livewire\Crypto\Institutions;

use App\Enums\CryptoInstitutionType;
use App\Models\CryptoInstitution;
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
    public string $type = 'exchange';
    public string $website = '';
    public string $color = '#1a73e8';
    public bool $is_active = true;
    public string $notes = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('crypto_institutions', 'slug')->ignore($this->editingId)],
            'type' => 'required|string|in:' . implode(',', array_column(CryptoInstitutionType::cases(), 'value')),
            'website' => 'nullable|url|max:255',
            'color' => 'required|string|max:20',
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
        $institution = CryptoInstitution::findOrFail($id);
        $this->editingId = $id;
        $this->name = $institution->name;
        $this->slug = $institution->slug;
        $this->type = $institution->type->value;
        $this->website = $institution->website ?? '';
        $this->color = $institution->color;
        $this->is_active = $institution->is_active;
        $this->notes = $institution->notes ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'slug' => $this->slug ?: Str::slug($this->name),
            'type' => $this->type,
            'website' => $this->website ?: null,
            'color' => $this->color,
            'is_active' => $this->is_active,
            'notes' => $this->notes ?: null,
        ];

        if ($this->editingId) {
            CryptoInstitution::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Instituicao cripto atualizada com sucesso.');
        } else {
            CryptoInstitution::create($data);
            session()->flash('success', 'Instituicao cripto criada com sucesso.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleActive(string $id): void
    {
        $institution = CryptoInstitution::findOrFail($id);
        $institution->update(['is_active' => !$institution->is_active]);
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $institution = CryptoInstitution::findOrFail($this->deletingId);

        if ($institution->accounts()->exists()) {
            session()->flash('error', 'Nao e possivel excluir uma instituicao com contas vinculadas.');
            $this->showDeleteModal = false;
            return;
        }

        $institution->delete();
        session()->flash('success', 'Instituicao excluida com sucesso.');
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'slug', 'website', 'notes']);
        $this->type = 'exchange';
        $this->color = '#1a73e8';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $institutions = CryptoInstitution::query()
            ->withCount('accounts')
            ->withSum('accounts', 'current_balance_brl')
            ->when($this->search, fn ($query) => $query->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->get();
        $types = CryptoInstitutionType::cases();

        return view('livewire.crypto.institutions.index', compact('institutions', 'types'));
    }
}
