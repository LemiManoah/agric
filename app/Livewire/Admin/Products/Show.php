<?php

namespace App\Livewire\Admin\Products;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

#[Title('Product Details')]
class Show extends Component
{
    public Product $product;

    public function mount(Product $product): void
    {
        $this->authorize('view', $product);

        $this->product = $product->load([
            'category.linkedValueChain',
            'creator',
            'images',
            'priceHistories.changedBy',
            'qualityGrade',
            'supplier.district.region',
            'supplier.farmer',
        ]);
    }

    public function render(): View
    {
        return view('livewire.admin.products.show', [
            'activities' => Activity::query()
                ->where('subject_type', Product::class)
                ->where('subject_id', $this->product->id)
                ->with('causer')
                ->latest()
                ->limit(15)
                ->get(),
        ])->layout('components.layouts.app');
    }
}
