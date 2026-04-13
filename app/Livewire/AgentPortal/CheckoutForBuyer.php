<?php

namespace App\Livewire\AgentPortal;

use App\Models\Agent;
use App\Models\Buyer;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Checkout For Buyer')]
class CheckoutForBuyer extends Component
{
    protected CartService $cartService;

    protected OrderService $orderService;

    public Agent $agent;

    public ?int $buyer_id = null;

    public string $delivery_address = '';

    public string $buyer_notes = '';

    public function boot(CartService $cartService, OrderService $orderService): void
    {
        $this->cartService = $cartService;
        $this->orderService = $orderService;
    }

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('agent'), 403);
        $this->authorize('create', Order::class);

        $this->agent = Agent::query()
            ->where('user_id', auth()->id())
            ->with(['primaryDistrict', 'regions'])
            ->firstOrFail();
    }

    public function submit()
    {
        $validated = $this->validate([
            'buyer_id' => ['required', 'exists:buyers,id'],
            'delivery_address' => ['required', 'string', 'min:10'],
            'buyer_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $buyer = Buyer::query()->findOrFail($validated['buyer_id']);
        $order = $this->orderService->checkoutAgentForBuyer($this->agent, $buyer, auth()->user(), $validated);

        session()->flash('status', 'Order placed successfully for buyer.');

        return redirect()->route('agent-portal.orders.index');
    }

    public function render(): View
    {
        $cart = $this->cartService
            ->getOrCreateCartForUser(auth()->user())
            ->load(['items.product.category', 'items.product.images', 'items.product.supplier']);

        return view('livewire.agent-portal.checkout-for-buyer', [
            'buyers' => Buyer::query()->orderBy('company_name')->get(),
            'cart' => $cart,
            'subtotal' => $cart->items->sum(fn ($item): float => round((float) $item->quantity * (float) $item->unit_price_usd, 2)),
        ])->layout('components.layouts.app');
    }
}
