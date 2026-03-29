<?php

namespace App\Livewire\Financeiro;

use App\Enums\TransactionType;
use App\Models\Category;
use Livewire\Component;

class Categorias extends Component
{
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?string $editingId = null;
    public ?string $deletingId = null;

    // Form fields
    public string $name = '';
    public string $type = 'expense';
    public string $color = '#ff6f00';
    public string $icon = 'label';
    public ?string $parent_id = null;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:income,expense',
            'color' => 'required|string|max:20',
            'icon' => 'nullable|string|max:50',
            'parent_id' => 'nullable|uuid|exists:categories,id',
        ];
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(string $id): void
    {
        $category = Category::findOrFail($id);
        $this->editingId = $id;
        $this->name = $category->name;
        $this->type = $category->type->value;
        $this->color = $category->color;
        $this->icon = $category->icon ?? 'label';
        $this->parent_id = $category->parent_id;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'type' => $this->type,
            'color' => $this->color,
            'icon' => $this->icon ?: null,
            'parent_id' => $this->parent_id ?: null,
        ];

        if ($this->editingId) {
            Category::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Categoria atualizada com sucesso.');
        } else {
            Category::create($data);
            session()->flash('success', 'Categoria criada com sucesso.');
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
        $category = Category::findOrFail($this->deletingId);

        if ($category->transactions()->exists()) {
            session()->flash('error', 'Nao e possivel excluir uma categoria com transacoes vinculadas.');
            $this->showDeleteModal = false;
            return;
        }

        $category->delete();
        session()->flash('success', 'Categoria excluida com sucesso.');
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'editingId', 'parent_id']);
        $this->type = 'expense';
        $this->color = '#ff6f00';
        $this->icon = 'label';
        $this->resetValidation();
    }

    public function render()
    {
        $expenseCategories = Category::where('type', TransactionType::Expense)
            ->whereNull('parent_id')
            ->withCount('transactions')
            ->with('children')
            ->orderBy('name')
            ->get();

        $incomeCategories = Category::where('type', TransactionType::Income)
            ->whereNull('parent_id')
            ->withCount('transactions')
            ->with('children')
            ->orderBy('name')
            ->get();

        $parentCategories = Category::whereNull('parent_id')->orderBy('name')->get();

        return view('livewire.financeiro.categorias', compact('expenseCategories', 'incomeCategories', 'parentCategories'));
    }
}
