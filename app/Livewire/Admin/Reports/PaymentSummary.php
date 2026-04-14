<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Payment;
use App\Services\Exports\CsvExportService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Payment Summary')]
class PaymentSummary extends Component
{
    protected CsvExportService $csvExportService;

    public function boot(CsvExportService $csvExportService): void
    {
        $this->csvExportService = $csvExportService;
    }

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('reports.view') || auth()->user()?->can('reports.view.region'), 403);
    }

    public function exportCsv()
    {
        abort_unless(auth()->user()?->can('exports.create'), 403);

        $summary = $this->summary();
        $rows = collect([
            ['metric' => 'total_payments', 'value' => $summary['total_payments']],
            ['metric' => 'successful_payments', 'value' => $summary['successful_payments']],
            ['metric' => 'failed_payments', 'value' => $summary['failed_payments']],
            ['metric' => 'refunded_payments', 'value' => $summary['refunded_payments']],
        ])->merge(
            collect($summary['payment_totals_by_method'])->map(fn ($amount, $method) => [
                'metric' => 'method_'.$method,
                'value' => $amount,
            ])
        )->merge(
            collect($summary['payment_totals_by_status'])->map(fn ($amount, $status) => [
                'metric' => 'status_'.$status,
                'value' => $amount,
            ])
        );

        return $this->csvExportService->streamDownload('payment-summary-'.now()->format('Ymd_His').'.csv', $rows);
    }

    public function render(): View
    {
        return view('livewire.admin.reports.payment-summary', [
            'summary' => $this->summary(),
        ])->layout('components.layouts.app');
    }

    private function summary(): array
    {
        $payments = Payment::query()
            ->visibleTo(auth()->user())
            ->get();

        return [
            'total_payments' => $payments->count(),
            'successful_payments' => $payments->filter(fn (Payment $payment) => in_array($payment->status->value, ['successful', 'partial'], true))->count(),
            'failed_payments' => $payments->filter(fn (Payment $payment) => $payment->status->value === 'failed')->count(),
            'refunded_payments' => $payments->filter(fn (Payment $payment) => $payment->status->value === 'refunded')->count(),
            'payment_totals_by_method' => $payments->groupBy(fn (Payment $payment) => $payment->method->value)->map(fn ($group) => $group->sum('amount'))->all(),
            'payment_totals_by_status' => $payments->groupBy(fn (Payment $payment) => $payment->status->value)->map(fn ($group) => $group->sum('amount'))->all(),
        ];
    }
}
