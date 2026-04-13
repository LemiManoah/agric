<?php

namespace App\Livewire\Admin\Reports;

use App\Enums\ListingStatus;
use App\Models\Product;
use App\Services\Exports\CsvExportService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Product Catalogue Summary')]
class ProductCatalogueSummary extends Component
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
            ['metric' => 'total_products', 'value' => $summary['total_products']],
            ['metric' => 'active_listings', 'value' => $summary['active_listings']],
            ['metric' => 'out_of_stock_listings', 'value' => $summary['out_of_stock_listings']],
        ])->merge(
            collect($summary['listings_by_category'])->map(fn (int $count, string $name) => [
                'metric' => 'category_'.$name,
                'value' => $count,
            ])
        )->merge(
            collect($summary['listings_by_supplier'])->map(fn (int $count, string $name) => [
                'metric' => 'supplier_'.$name,
                'value' => $count,
            ])
        );

        return $this->csvExportService->streamDownload('product-catalogue-summary-'.now()->format('Ymd_His').'.csv', $rows);
    }

    public function render(): View
    {
        return view('livewire.admin.reports.product-catalogue-summary', [
            'summary' => $this->summary(),
        ])->layout('components.layouts.app');
    }

    private function summary(): array
    {
        $query = Product::query()
            ->visibleTo(auth()->user())
            ->with(['category', 'supplier']);

        $products = $query->get();

        return [
            'total_products' => $products->count(),
            'active_listings' => $products->where('listing_status', ListingStatus::Active)->count(),
            'out_of_stock_listings' => $products->where('listing_status', ListingStatus::OutOfStock)->count(),
            'listings_by_category' => $products->groupBy(fn (Product $product) => $product->category?->name ?? 'Uncategorised')->map->count()->all(),
            'listings_by_supplier' => $products->groupBy(fn (Product $product) => $product->supplier?->business_name ?? 'Unlinked')->map->count()->all(),
        ];
    }
}
