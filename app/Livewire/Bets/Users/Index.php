<?php

namespace App\Livewire\Bets\Users;

use App\Models\BetUser;
use Livewire\Component;

class Index extends Component
{
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?string $editingId = null;
    public ?string $deletingId = null;
    public string $search = '';

    public string $name = '';
    public string $nickname = '';
    public string $document = '';
    public string $email = '';
    public string $phone = '';
    public string $pix_key = '';
    public string $color = '#ff6f00';
    public bool $is_active = true;
    public string $notes = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:255',
            'document' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'pix_key' => 'nullable|string|max:255',
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
        $user = BetUser::findOrFail($id);
        $this->editingId = $id;
        $this->name = $user->name;
        $this->nickname = $user->nickname ?? '';
        $this->document = $user->document ?? '';
        $this->email = $user->email ?? '';
        $this->phone = $user->phone ?? '';
        $this->pix_key = $user->pix_key ?? '';
        $this->color = $user->color;
        $this->is_active = $user->is_active;
        $this->notes = $user->notes ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'nickname' => $this->nickname ?: null,
            'document' => $this->document ?: null,
            'email' => $this->email ?: null,
            'phone' => $this->phone ?: null,
            'pix_key' => $this->pix_key ?: null,
            'color' => $this->color,
            'is_active' => $this->is_active,
            'notes' => $this->notes ?: null,
        ];

        if ($this->editingId) {
            BetUser::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Usuario de bet atualizado com sucesso.');
        } else {
            BetUser::create($data);
            session()->flash('success', 'Usuario de bet criado com sucesso.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleActive(string $id): void
    {
        $user = BetUser::findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $user = BetUser::findOrFail($this->deletingId);

        if ($user->accounts()->exists()) {
            session()->flash('error', 'Nao e possivel excluir um usuario com contas vinculadas.');
            $this->showDeleteModal = false;
            return;
        }

        $user->delete();
        session()->flash('success', 'Usuario excluido com sucesso.');
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'nickname', 'document', 'email', 'phone', 'pix_key', 'notes']);
        $this->color = '#ff6f00';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $users = BetUser::query()
            ->withCount('accounts')
            ->withSum('accounts', 'current_balance')
            ->when($this->search, function ($query) {
                $query->where(function ($subquery) {
                    $subquery->where('name', 'like', "%{$this->search}%")
                        ->orWhere('nickname', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhere('phone', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('name')
            ->get();

        return view('livewire.bets.users.index', compact('users'));
    }
}
