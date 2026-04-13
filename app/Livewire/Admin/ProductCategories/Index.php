<?php

namespace App\Livewire\Admin\ProductCategories;

use App\Models\ProductCategory;
use App\Models\ValueChain;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Product Categories')]
class Index extends Component
{
    public ?ProductCategory $editingCategory = null;

    public string $name = '';

    public string $slug = '';

    public ?int $linked_value_chain_id = null;

    public bool $is_active = true;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('products.view'), 403);
    }

    public function edit(int $categoryId): void
    {
        $this->editingCategory = ProductCategory::query()->findOrFail($categoryId);

        $this->name = $this->editingCategory->name;
        $this->slug = $this->editingCategory->slug;
        $this->linked_value_chain_id = $this->editingCategory->linked_value_chain_id;
        $this->is_active = $this->editingCategory->is_active;
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->can('products.create') || auth()->user()?->can('products.update'), 403);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_categories', 'slug')->ignore($this->editingCategory?->id),
            ],
            'linked_value_chain_id' => ['nullable', 'exists:value_chains,id'],
            'is_active' => ['boolean'],
        ]);

        ProductCategory::query()->updateOrCreate(
            ['id' => $this->editingCategory?->id],
            $validated,
        );

        $this->resetForm();

        session()->flash('status', 'Product category saved successfully.');
    }

    public function updatedName(): void
    {
        if ($this->editingCategory === null) {
            $this->slug = Str::slug($this->name);
        }
    }

    public function render(): View
    {
        return view('livewire.admin.product-categories.index', [
            'categories' => ProductCategory::query()->with('linkedValueChain')->orderBy('name')->get(),
            'valueChains' => ValueChain::query()->where('is_active', true)->orderBy('name')->get(),
        ])->layout('components.layouts.app');
    }

    private function resetForm(): void
    {
        $this->editingCategory = null;
        $this->name = '';
        $this->slug = '';
        $this->linked_value_chain_id = null;
        $this->is_active = true;
    }
}
