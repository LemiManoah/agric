<?php

namespace App\Livewire\Public\Catalogue;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Catalogue Product')]
class Show extends Component
{
    protected CartService $cartService;

    public Product $product;

    public string $quantity = '';

    public function boot(CartService $cartService): void
    {
        $this->cartService = $cartService;
    }

    public function mount(Product $product): void
    {
        abort_unless(Product::query()->publiclyVisible()->whereKey($product->id)->exists(), 404);

        $this->product = $product->load([
            'category.linkedValueChain',
            'images',
            'qualityGrade',
            'supplier.district.region',
        ]);

        $this->quantity = (string) $this->product->minimum_order_quantity;
    }

    public function addToCart()
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        abort_unless(auth()->user()?->hasRole('buyer') || auth()->user()?->hasRole('agent'), 403);

        $this->cartService->addItem(auth()->user(), $this->product, $this->quantity);

        session()->flash('status', 'Product added to cart.');
    }

    public function render(): View
    {
        return view('livewire.public.catalogue.show')
            ->layout('components.layouts.auth');
    }
}
