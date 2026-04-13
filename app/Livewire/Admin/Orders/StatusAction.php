<?php

namespace App\Livewire\Admin\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class StatusAction extends Component
{
    protected OrderService $orderService;

    public Order $order;

    public function boot(OrderService $orderService): void
    {
        $this->orderService = $orderService;
    }

    public function mount(Order $order): void
    {
        $this->order = $order;
        $this->authorize('view', $order);
    }

    public function confirm(): void
    {
        $this->transitionTo(OrderStatus::Confirmed);
    }

    public function process(): void
    {
        $this->transitionTo(OrderStatus::Processing);
    }

    public function markDispatched(): void
    {
        $this->transitionTo(OrderStatus::Dispatched);
    }

    public function deliver(): void
    {
        $this->transitionTo(OrderStatus::Delivered);
    }

    public function cancel(): void
    {
        $this->authorize('cancel', $this->order);
        $this->order = $this->orderService->cancelOrder($this->order, auth()->user(), 'Cancelled from admin workflow.');
        $this->dispatch('order-updated');
    }

    public function render(): View
    {
        return view('livewire.admin.orders.status-action');
    }

    private function transitionTo(OrderStatus $status): void
    {
        $this->authorize('updateStatus', $this->order);
        $this->order = $this->orderService->changeStatus($this->order, $status, auth()->user());
        $this->dispatch('order-updated');
    }
}
