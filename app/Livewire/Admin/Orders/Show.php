<?php

namespace App\Livewire\Admin\Orders;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Order Details')]
class Show extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        $this->authorize('view', $order);
        $this->order = $order->load($this->relations());
    }

    #[On('order-updated')]
    public function refreshOrder(): void
    {
        $this->order->refresh()->load($this->relations());
    }

    public function render(): View
    {
        return view('livewire.admin.orders.show')
            ->layout('components.layouts.app');
    }

    /**
     * @return array<int, string>
     */
    private function relations(): array
    {
        return [
            'agent.user',
            'buyer.user',
            'creator',
            'items.product.images',
            'items.supplier',
            'statusHistories.changedBy',
        ];
    }
}
