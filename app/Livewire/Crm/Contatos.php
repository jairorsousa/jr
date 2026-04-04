<?php

namespace App\Livewire\Crm;

use App\Models\Contact;
use App\Models\WhatsAppConversation;
use Livewire\Component;

class Contatos extends Component
{
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?string $editingId = null;
    public ?string $deletingId = null;
    public string $search = '';

    // Form fields
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $company = '';
    public string $notes = '';
    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
            'is_active' => 'boolean',
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
        $contact = Contact::findOrFail($id);
        $this->editingId = $id;
        $this->name = $contact->name;
        $this->email = $contact->email ?? '';
        $this->phone = $contact->phone ?? '';
        $this->company = $contact->company ?? '';
        $this->notes = $contact->notes ?? '';
        $this->is_active = $contact->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email ?: null,
            'phone' => $this->phone ?: null,
            'company' => $this->company ?: null,
            'notes' => $this->notes ?: null,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            Contact::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Contato atualizado com sucesso.');
        } else {
            Contact::create($data);
            session()->flash('success', 'Contato criado com sucesso.');
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
        $contact = Contact::findOrFail($this->deletingId);

        if ($contact->deals()->exists()) {
            session()->flash('error', 'Nao e possivel excluir um contato com negocios vinculados.');
            $this->showDeleteModal = false;
            return;
        }

        $contact->delete();
        session()->flash('success', 'Contato excluido com sucesso.');
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function toggleActive(string $id): void
    {
        $contact = Contact::findOrFail($id);
        $contact->update(['is_active' => !$contact->is_active]);
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'email', 'phone', 'company', 'notes', 'editingId']);
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $contacts = Contact::withCount(['deals', 'whatsappConversations'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%")
                      ->orWhere('company', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('name')
            ->get();

        return view('livewire.crm.contatos', compact('contacts'));
    }
}
