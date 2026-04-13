<?php

namespace App\Livewire\Admin\Orders;

use App\Enums\OrderStatus;
use App\Models\Agent;
use App\Models\Buyer;
use App\Models\Order;
use App\Models\Supplier;
use App\Services\Exports\CsvExportService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Orders')]
class Index extends Component
{
    use WithPagination;

    protected CsvExportService $csvExportService;

    #[Url(as: 'search', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: '')]
    public string $status = '';

    #[Url(as: 'buyer', except: '')]
    public ?int $buyerId = null;

    #[Url(as: 'supplier', except: '')]
    public ?int $supplierId = null;

    #[Url(as: 'agent', except: '')]
    public ?int $agentId = null;

    #[Url(as: 'from', except: '')]
    public string $dateFrom = '';

    #[Url(as: 'to', except: '')]
    public string $dateTo = '';

    public function boot(CsvExportService $csvExportService): void
    {
        $this->csvExportService = $csvExportService;
    }

    public function mount(): void
    {
        $this->authorize('viewAny', Order::class);
    }

    public function updating(string $name, mixed $value): void
    {
        if (in_array($name, ['search', 'status', 'buyerId', 'supplierId', 'agentId', 'dateFrom', 'dateTo'], true)) {
            $this->resetPage();
        }
    }

    public function exportCsv()
    {
        $this->authorize('export', Order::class);

        $rows = $this->query()
            ->with(['agent', 'buyer', 'items.supplier'])
            ->latest('ordered_at')
            ->get()
            ->map(fn (Order $order): array => [
                'order_number' => $order->order_number,
                'buyer' => $order->buyer?->company_name,
                'agent' => $order->agent?->full_name,
                'status' => $order->status->value,
                'supplier_count' => $order->items->pluck('supplier_id')->filter()->unique()->count(),
                'subtotal' => $order->subtotal,
                'order_total' => $order->order_total,
                'ordered_at' => optional($order->ordered_at)->toDateTimeString(),
            ]);

        return $this->csvExportService->streamDownload('orders-'.now()->format('Ymd_His').'.csv', $rows);
    }

    public function render(): View
    {
        $baseQuery = $this->query();
        $buyerIds = (clone $baseQuery)->select('buyer_id')->distinct()->pluck('buyer_id')->filter()->all();
        $agentIds = (clone $baseQuery)->select('placed_by_agent_id')->distinct()->pluck('placed_by_agent_id')->filter()->all();
        $supplierIds = (clone $baseQuery)
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->select('order_items.supplier_id')
            ->distinct()
            ->pluck('order_items.supplier_id')
            ->filter()
            ->all();

        return view('livewire.admin.orders.index', [
            'agents' => Agent::query()->whereIn('id', $agentIds)->orderBy('full_name')->get(),
            'buyers' => Buyer::query()->whereIn('id', $buyerIds)->orderBy('company_name')->get(),
            'orders' => $baseQuery
                ->with(['agent', 'buyer', 'items.supplier'])
                ->latest('ordered_at')
                ->paginate(12),
            'statuses' => OrderStatus::cases(),
            'suppliers' => Supplier::query()->whereIn('id', $supplierIds)->orderBy('business_name')->get(),
        ])->layout('components.layouts.app');
    }

    private function query(): Builder
    {
        return Order::query()
            ->visibleTo(auth()->user())
            ->when($this->search !== '', function (Builder $query): void {
                $query->where(function (Builder $searchQuery): void {
                    $searchQuery
                        ->where('order_number', 'like', '%'.$this->search.'%')
                        ->orWhereHas('buyer', fn (Builder $buyerQuery) => $buyerQuery->where('company_name', 'like', '%'.$this->search.'%'))
                        ->orWhereHas('agent', fn (Builder $agentQuery) => $agentQuery->where('full_name', 'like', '%'.$this->search.'%'));
                });
            })
            ->when($this->status !== '', fn (Builder $query) => $query->where('status', $this->status))
            ->when($this->buyerId, fn (Builder $query) => $query->where('buyer_id', $this->buyerId))
            ->when($this->agentId, fn (Builder $query) => $query->where('placed_by_agent_id', $this->agentId))
            ->when($this->supplierId, fn (Builder $query) => $query->whereHas('items', fn (Builder $itemQuery) => $itemQuery->where('supplier_id', $this->supplierId)))
            ->when($this->dateFrom !== '', fn (Builder $query) => $query->whereDate('ordered_at', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn (Builder $query) => $query->whereDate('ordered_at', '<=', $this->dateTo));
    }
}
