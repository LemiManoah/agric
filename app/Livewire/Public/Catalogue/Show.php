<?php

namespace App\Livewire\Public\Catalogue;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Catalogue Product')]
class Show extends Component
{
    public Product $product;

    public function mount(Product $product): void
    {
        abort_unless(Product::query()->publiclyVisible()->whereKey($product->id)->exists(), 404);

        $this->product = $product->load([
            'category.linkedValueChain',
            'images',
            'qualityGrade',
            'supplier.district.region',
        ]);
    }

    public function render(): View
    {
        return view('livewire.public.catalogue.show')
            ->layout('components.layouts.auth');
    }
}
