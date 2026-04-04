<?php

namespace App\Livewire\WhatsApp;

use App\Enums\TemplateCategory;
use App\Models\WhatsAppTemplate;
use Livewire\Component;

class Templates extends Component
{
    public string $search = '';
    public string $filterCategory = '';

    // Modal
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?string $editingId = null;
    public ?string $deletingId = null;

    // Form
    public string $name = '';
    public string $body = '';
    public string $category = 'general';
    public bool $is_active = true;

    // Preview
    public bool $showPreviewModal = false;
    public ?WhatsAppTemplate $previewTemplate = null;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'body' => 'required|string|max:4000',
            'category' => 'required|string',
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
        $template = WhatsAppTemplate::findOrFail($id);
        $this->editingId = $id;
        $this->name = $template->name;
        $this->body = $template->body;
        $this->category = $template->category->value;
        $this->is_active = $template->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'body' => $this->body,
            'category' => $this->category,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            WhatsAppTemplate::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Template atualizado com sucesso.');
        } else {
            WhatsAppTemplate::create($data);
            session()->flash('success', 'Template criado com sucesso.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function duplicateTemplate(string $id): void
    {
        $template = WhatsAppTemplate::findOrFail($id);
        WhatsAppTemplate::create([
            'name' => $template->name . ' (Copia)',
            'body' => $template->body,
            'category' => $template->category,
            'is_active' => true,
        ]);
        session()->flash('success', 'Template duplicado com sucesso.');
    }

    public function toggleActive(string $id): void
    {
        $template = WhatsAppTemplate::findOrFail($id);
        $template->update(['is_active' => !$template->is_active]);
    }

    public function previewTemplate(string $id): void
    {
        $this->previewTemplate = WhatsAppTemplate::findOrFail($id);
        $this->showPreviewModal = true;
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $template = WhatsAppTemplate::findOrFail($this->deletingId);

        if ($template->campaigns()->whereIn('status', ['sending', 'scheduled'])->exists()) {
            session()->flash('error', 'Nao e possivel excluir um template com campanhas ativas.');
            $this->showDeleteModal = false;
            return;
        }

        $template->delete();
        session()->flash('success', 'Template excluido com sucesso.');
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'body', 'editingId']);
        $this->category = 'general';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $templates = WhatsAppTemplate::query()
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('name', 'like', "%{$this->search}%")
                       ->orWhere('body', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterCategory, function ($q) {
                $q->where('category', $this->filterCategory);
            })
            ->orderByDesc('updated_at')
            ->get();

        $categories = TemplateCategory::cases();

        return view('livewire.whatsapp.templates', compact('templates', 'categories'));
    }
}
