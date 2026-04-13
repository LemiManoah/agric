<?php

namespace App\Livewire\BuyerPortal;

use App\Enums\PaymentMethod;
use App\Models\Buyer;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Checkout')]
class CheckoutPage extends Component
{
    protected CartService $cartService;

    protected OrderService $orderService;

    public Buyer $buyer;

    public string $delivery_address = '';

    public string $buyer_notes = '';

    public string $payment_method = '';

    public function boot(CartService $cartService, OrderService $orderService): void
    {
        $this->cartService = $cartService;
        $this->orderService = $orderService;
    }

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('buyer'), 403);

        $this->buyer = Buyer::query()
            ->where('user_id', auth()->id())
            ->with('valueChainInterests')
            ->firstOrFail();

        $this->authorize('view', $this->buyer);
    }

    public function submit()
    {
        $validated = $this->validate([
            'delivery_address' => ['required', 'string', 'min:10'],
            'buyer_notes' => ['nullable', 'string', 'max:2000'],
            'payment_method' => ['nullable', 'in:'.implode(',', array_column(PaymentMethod::cases(), 'value'))],
        ]);

        $order = $this->orderService->checkoutBuyer($this->buyer, auth()->user(), $validated);

        session()->flash('status', 'Order placed successfully.');

        return redirect()->route('buyer-portal.orders.show', $order);
    }

    public function render(): View
    {
        $cart = $this->cartService
            ->getOrCreateCartForUser(auth()->user())
            ->load(['items.product.category', 'items.product.images', 'items.product.supplier']);

        return view('livewire.buyer-portal.checkout-page', [
            'cart' => $cart,
            'paymentMethods' => PaymentMethod::cases(),
            'subtotal' => $cart->items->sum(fn ($item): float => round((float) $item->quantity * (float) $item->unit_price_usd, 2)),
        ])->layout('components.layouts.app');
    }
}
