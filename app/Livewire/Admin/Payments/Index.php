<?php

namespace App\Livewire\Admin\Payments;

use App\Enums\PaymentLifecycleStatus;
use App\Models\Buyer;
use App\Models\Payment;
use App\Services\Exports\CsvExportService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Payments')]
class Index extends Component
{
    use WithPagination;

    protected CsvExportService $csvExportService;

    #[Url(as: 'search', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: '')]
    public string $status = '';

    #[Url(as: 'method', except: '')]
    public string $method = '';

    #[Url(as: 'buyer', except: '')]
    public ?int $buyerId = null;

    public function boot(CsvExportService $csvExportService): void
    {
        $this->csvExportService = $csvExportService;
    }

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('super_admin') || auth()->user()?->isRegionalAdmin(), 403);
        $this->authorize('viewAny', Payment::class);
    }

    public function updating(string $name, mixed $value): void
    {
        if (in_array($name, ['search', 'status', 'method', 'buyerId'], true)) {
            $this->resetPage();
        }
    }

    public function exportCsv()
    {
        $this->authorize('export', Payment::class);

        $rows = $this->paymentQuery()
            ->with(['order.buyer'])
            ->latest()
            ->get()
            ->map(fn (Payment $payment): array => [
                'order_number' => $payment->order?->order_number,
                'buyer' => $payment->order?->buyer?->company_name,
                'method' => $payment->method->value,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'status' => $payment->status->value,
                'paid_at' => optional($payment->paid_at)->toDateTimeString(),
                'reference' => $payment->gateway_transaction_reference,
            ]);

        return $this->csvExportService->streamDownload('payments-'.now()->format('Ymd_His').'.csv', $rows);
    }

    public function render(): View
    {
        $query = $this->paymentQuery();
        $buyerIds = (clone $query)
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->select('orders.buyer_id')
            ->distinct()
            ->pluck('orders.buyer_id')
            ->filter()
            ->all();

        return view('livewire.admin.payments.index', [
            'buyers' => Buyer::query()->whereIn('id', $buyerIds)->orderBy('company_name')->get(),
            'payments' => $query
                ->with(['order.buyer', 'receipt'])
                ->latest()
                ->paginate(12),
            'statuses' => PaymentLifecycleStatus::cases(),
        ])->layout('components.layouts.app');
    }

    private function paymentQuery(): Builder
    {
        return Payment::query()
            ->visibleTo(auth()->user())
            ->when($this->search !== '', function (Builder $query): void {
                $query->where(function (Builder $searchQuery): void {
                    $searchQuery
                        ->where('gateway_transaction_reference', 'like', '%'.$this->search.'%')
                        ->orWhereHas('order', fn (Builder $orderQuery) => $orderQuery->where('order_number', 'like', '%'.$this->search.'%'))
                        ->orWhereHas('order.buyer', fn (Builder $buyerQuery) => $buyerQuery->where('company_name', 'like', '%'.$this->search.'%'));
                });
            })
            ->when($this->status !== '', fn (Builder $query) => $query->where('status', $this->status))
            ->when($this->method !== '', fn (Builder $query) => $query->where('method', $this->method))
            ->when($this->buyerId, fn (Builder $query) => $query->whereHas('order', fn (Builder $orderQuery) => $orderQuery->where('buyer_id', $this->buyerId)));
    }
}
