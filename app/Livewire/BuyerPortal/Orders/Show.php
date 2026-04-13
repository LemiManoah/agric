<?php

namespace App\Livewire\BuyerPortal\Orders;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Order Details')]
class Show extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        abort_unless(auth()->user()?->hasRole('buyer'), 403);

        $this->authorize('view', $order);

        $this->order = $order->load([
            'agent.user',
            'buyer.user',
            'items.product.images',
            'items.supplier',
            'statusHistories.changedBy',
        ]);
    }

    public function render(): View
    {
        return view('livewire.buyer-portal.orders.show')
            ->layout('components.layouts.app');
    }
}
