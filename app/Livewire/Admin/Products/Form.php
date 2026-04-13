<?php

namespace App\Livewire\Admin\Products;

use App\Enums\ListingStatus;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\QualityGrade;
use App\Models\Supplier;
use App\Services\ProductService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Product Form')]
class Form extends Component
{
    use WithFileUploads;

    protected ProductService $productService;

    public ?Product $product = null;

    public string $name = '';

    public ?int $product_category_id = null;

    public ?int $linked_supplier_id = null;

    public string $description = '';

    public ?int $quality_grade_id = null;

    public string $unit_of_measure = '';

    public string $price_per_unit_usd = '';

    public string $minimum_order_quantity = '1';

    public string $stock_available = '0';

    public string $listing_status = '';

    public string $warehouse_sku = '';

    public array $retained_image_ids = [];

    public array $uploaded_images = [];

    public function boot(ProductService $productService): void
    {
        $this->productService = $productService;
    }

    public function mount(?Product $product = null): void
    {
        $this->product = $product?->exists ? $product->load('images') : null;

        if ($this->product) {
            $this->authorize('update', $this->product);

            $this->name = $this->product->name;
            $this->product_category_id = $this->product->product_category_id;
            $this->linked_supplier_id = $this->product->linked_supplier_id;
            $this->description = $this->product->description ?? '';
            $this->quality_grade_id = $this->product->quality_grade_id;
            $this->unit_of_measure = $this->product->unit_of_measure;
            $this->price_per_unit_usd = (string) $this->product->price_per_unit_usd;
            $this->minimum_order_quantity = (string) $this->product->minimum_order_quantity;
            $this->stock_available = (string) $this->product->stock_available;
            $this->listing_status = $this->product->listing_status->value;
            $this->warehouse_sku = $this->product->warehouse_sku ?? '';
            $this->retained_image_ids = $this->product->images->pluck('id')->map(fn ($id) => (int) $id)->all();
        } else {
            $this->authorize('create', Product::class);
            $this->listing_status = ListingStatus::Draft->value;
        }
    }

    public function removeExistingImage(int $imageId): void
    {
        $this->retained_image_ids = array_values(array_filter(
            $this->retained_image_ids,
            fn (int $retainedId): bool => $retainedId !== $imageId,
        ));
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'product_category_id' => ['required', 'exists:product_categories,id'],
            'linked_supplier_id' => ['required', 'exists:suppliers,id'],
            'description' => ['nullable', 'string'],
            'quality_grade_id' => ['nullable', 'exists:quality_grades,id'],
            'unit_of_measure' => ['required', 'string', 'max:60'],
            'price_per_unit_usd' => ['required', 'numeric', 'min:0'],
            'minimum_order_quantity' => ['required', 'numeric', 'min:0.01'],
            'stock_available' => ['required', 'numeric', 'min:0'],
            'listing_status' => ['required', Rule::in(array_column(ListingStatus::cases(), 'value'))],
            'warehouse_sku' => ['nullable', 'string', 'max:255'],
            'retained_image_ids' => ['array'],
            'retained_image_ids.*' => ['integer'],
            'uploaded_images' => ['array'],
            'uploaded_images.*' => ['image', 'max:5120'],
        ]);

        $product = $this->product
            ? $this->productService->updateProduct($this->product, $validated, auth()->user())
            : $this->productService->createProduct($validated, auth()->user());

        session()->flash('status', 'Product listing saved successfully.');

        return redirect()->route('admin.products.show', $product);
    }

    public function render(): View
    {
        return view('livewire.admin.products.form', [
            'categories' => ProductCategory::query()->where('is_active', true)->orderBy('name')->get(),
            'listingStatuses' => ListingStatus::cases(),
            'qualityGrades' => QualityGrade::query()->where('is_active', true)->orderBy('name')->get(),
            'suppliers' => Supplier::query()->visibleTo(auth()->user())->orderBy('business_name')->get(),
        ])->layout('components.layouts.app');
    }
}
