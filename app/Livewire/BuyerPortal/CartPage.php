<?php

namespace App\Livewire\BuyerPortal;

use App\Models\Cart;
use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('My Cart')]
class CartPage extends Component
{
    protected CartService $cartService;

    public Cart $cart;

    /** @var array<int, string> */
    public array $quantities = [];

    public function boot(CartService $cartService): void
    {
        $this->cartService = $cartService;
    }

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('buyer'), 403);

        $this->refreshCart();
    }

    public function increaseQuantity(int $itemId): void
    {
        $item = $this->resolveItem($itemId);
        $currentQuantity = (float) ($this->quantities[$itemId] ?? $item->quantity);

        $this->cartService->updateItemQuantity($item, $currentQuantity + 1, auth()->user());
        $this->refreshCart();
    }

    public function decreaseQuantity(int $itemId): void
    {
        $item = $this->resolveItem($itemId);
        $currentQuantity = (float) ($this->quantities[$itemId] ?? $item->quantity);

        $this->cartService->updateItemQuantity($item, $currentQuantity - 1, auth()->user());
        $this->refreshCart();
    }

    public function updateQuantity(int $itemId): void
    {
        $item = $this->resolveItem($itemId);

        $this->cartService->updateItemQuantity($item, $this->quantities[$itemId] ?? $item->quantity, auth()->user());
        $this->refreshCart();
    }

    public function removeItem(int $itemId): void
    {
        $this->cartService->removeItem($this->resolveItem($itemId), auth()->user());
        $this->refreshCart();
    }

    public function render(): View
    {
        return view('livewire.buyer-portal.cart-page', [
            'subtotal' => $this->cart->items->sum(fn (CartItem $item): float => round((float) $item->quantity * (float) $item->unit_price_usd, 2)),
        ])->layout('components.layouts.app');
    }

    private function refreshCart(): void
    {
        $this->cart = $this->cartService
            ->getOrCreateCartForUser(auth()->user())
            ->load(['items.product.category', 'items.product.images', 'items.product.supplier']);

        $this->quantities = $this->cart->items
            ->mapWithKeys(fn (CartItem $item): array => [$item->id => (string) $item->quantity])
            ->all();
    }

    private function resolveItem(int $itemId): CartItem
    {
        return $this->cart->items->firstWhere('id', $itemId)
            ?? abort(404);
    }
}
