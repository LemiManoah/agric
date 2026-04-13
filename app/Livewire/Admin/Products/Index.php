<?php

namespace App\Livewire\Admin\Products;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\QualityGrade;
use App\Models\Supplier;
use App\Services\Exports\CsvExportService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Products')]
class Index extends Component
{
    use WithPagination;

    protected CsvExportService $csvExportService;

    #[Url(as: 'search', except: '')]
    public string $search = '';

    #[Url(as: 'category', except: '')]
    public ?int $categoryId = null;

    #[Url(as: 'supplier', except: '')]
    public ?int $supplierId = null;

    #[Url(as: 'status', except: '')]
    public string $listingStatus = '';

    #[Url(as: 'quality_grade', except: '')]
    public ?int $qualityGradeId = null;

    public function boot(CsvExportService $csvExportService): void
    {
        $this->csvExportService = $csvExportService;
    }

    public function mount(): void
    {
        $this->authorize('viewAny', Product::class);
    }

    public function updating(string $name, mixed $value): void
    {
        if (in_array($name, ['search', 'categoryId', 'supplierId', 'listingStatus', 'qualityGradeId'], true)) {
            $this->resetPage();
        }
    }

    public function exportCsv()
    {
        $this->authorize('export', Product::class);

        $rows = $this->productQuery()
            ->with(['category', 'qualityGrade', 'supplier'])
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product): array => [
                'name' => $product->name,
                'category' => $product->category?->name,
                'supplier' => $product->supplier?->business_name,
                'quality_grade' => $product->qualityGrade?->name,
                'listing_status' => $product->listing_status->value,
                'unit_of_measure' => $product->unit_of_measure,
                'price_per_unit_usd' => $product->price_per_unit_usd,
                'stock_available' => $product->stock_available,
            ]);

        return $this->csvExportService->streamDownload('products-'.now()->format('Ymd_His').'.csv', $rows);
    }

    public function render(): View
    {
        return view('livewire.admin.products.index', [
            'categories' => ProductCategory::query()->where('is_active', true)->orderBy('name')->get(),
            'products' => $this->productQuery()
                ->with(['category', 'qualityGrade', 'supplier'])
                ->orderBy('name')
                ->paginate(12),
            'qualityGrades' => QualityGrade::query()->where('is_active', true)->orderBy('name')->get(),
            'suppliers' => Supplier::query()->visibleTo(auth()->user())->orderBy('business_name')->get(),
        ])->layout('components.layouts.app');
    }

    private function productQuery(): Builder
    {
        return Product::query()
            ->visibleTo(auth()->user())
            ->when($this->search !== '', function (Builder $query): void {
                $query->where(function (Builder $searchQuery): void {
                    $searchQuery
                        ->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('warehouse_sku', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->categoryId, fn (Builder $query) => $query->where('product_category_id', $this->categoryId))
            ->when($this->supplierId, fn (Builder $query) => $query->where('linked_supplier_id', $this->supplierId))
            ->when($this->listingStatus !== '', fn (Builder $query) => $query->where('listing_status', $this->listingStatus))
            ->when($this->qualityGradeId, fn (Builder $query) => $query->where('quality_grade_id', $this->qualityGradeId));
    }
}
