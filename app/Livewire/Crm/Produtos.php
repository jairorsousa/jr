<?php

namespace App\Livewire\Crm;

use App\Models\Product;
use Livewire\Component;

class Produtos extends Component
{
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?string $editingId = null;
    public ?string $deletingId = null;

    // Form fields
    public string $name = '';
    public string $description = '';
    public string $price = '';
    public string $color = '#ff6f00';
    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'nullable|numeric|min:0',
            'color' => 'required|string|max:20',
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
        $product = Product::findOrFail($id);
        $this->editingId = $id;
        $this->name = $product->name;
        $this->description = $product->description ?? '';
        $this->price = $product->price ? (string) $product->price : '';
        $this->color = $product->color ?? '#ff6f00';
        $this->is_active = $product->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'description' => $this->description ?: null,
            'price' => $this->price !== '' ? (float) $this->price : null,
            'color' => $this->color,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            Product::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Produto atualizado com sucesso.');
        } else {
            Product::create($data);
            session()->flash('success', 'Produto criado com sucesso.');
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
        $product = Product::findOrFail($this->deletingId);

        if ($product->deals()->exists()) {
            session()->flash('error', 'Nao e possivel excluir um produto com negocios vinculados.');
            $this->showDeleteModal = false;
            return;
        }

        $product->delete();
        session()->flash('success', 'Produto excluido com sucesso.');
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function toggleActive(string $id): void
    {
        $product = Product::findOrFail($id);
        $product->update(['is_active' => !$product->is_active]);
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'description', 'price', 'editingId']);
        $this->color = '#ff6f00';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $products = Product::withCount('deals')
            ->orderBy('name')
            ->get();

        return view('livewire.crm.produtos', compact('products'));
    }
}
