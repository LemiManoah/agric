<?php

namespace App\Livewire\Public\Catalogue;

use App\Enums\ListingStatus;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\QualityGrade;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Public Catalogue')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'search', except: '')]
    public string $search = '';

    #[Url(as: 'category', except: '')]
    public ?int $categoryId = null;

    #[Url(as: 'quality_grade', except: '')]
    public ?int $qualityGradeId = null;

    #[Url(as: 'availability', except: '')]
    public string $availability = 'in_stock';

    public function updating(string $name, mixed $value): void
    {
        if (in_array($name, ['search', 'categoryId', 'qualityGradeId', 'availability'], true)) {
            $this->resetPage();
        }
    }

    public function render(): View
    {
        return view('livewire.public.catalogue.index', [
            'categories' => ProductCategory::query()->where('is_active', true)->orderBy('name')->get(),
            'products' => $this->productQuery()
                ->with(['category', 'images', 'qualityGrade', 'supplier'])
                ->orderBy('name')
                ->paginate(12),
            'qualityGrades' => QualityGrade::query()->where('is_active', true)->orderBy('name')->get(),
        ])->layout('components.layouts.auth');
    }

    private function productQuery(): Builder
    {
        return Product::query()
            ->publiclyVisible()
            ->when($this->search !== '', function (Builder $query): void {
                $query->where(function (Builder $searchQuery): void {
                    $searchQuery
                        ->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->categoryId, fn (Builder $query) => $query->where('product_category_id', $this->categoryId))
            ->when($this->qualityGradeId, fn (Builder $query) => $query->where('quality_grade_id', $this->qualityGradeId))
            ->when($this->availability === 'in_stock', fn (Builder $query) => $query->where('listing_status', ListingStatus::Active->value)->where('stock_available', '>', 0))
            ->when($this->availability === 'out_of_stock', fn (Builder $query) => $query->where('listing_status', ListingStatus::OutOfStock->value));
    }
}
