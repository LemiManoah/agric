<?php

namespace App\Livewire\BuyerPortal\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('My Orders')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'search', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: '')]
    public string $status = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('buyer'), 403);
        $this->authorize('viewAny', Order::class);
    }

    public function updating(string $name, mixed $value): void
    {
        if (in_array($name, ['search', 'status'], true)) {
            $this->resetPage();
        }
    }

    public function render(): View
    {
        return view('livewire.buyer-portal.orders.index', [
            'orders' => $this->query()
                ->with(['items', 'statusHistories'])
                ->latest('ordered_at')
                ->paginate(10),
            'statuses' => OrderStatus::cases(),
        ])->layout('components.layouts.app');
    }

    private function query(): Builder
    {
        return Order::query()
            ->visibleTo(auth()->user())
            ->when($this->search !== '', function (Builder $query): void {
                $query->where('order_number', 'like', '%'.$this->search.'%');
            })
            ->when($this->status !== '', fn (Builder $query) => $query->where('status', $this->status));
    }
}
