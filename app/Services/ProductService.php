<?php

namespace App\Services;

use App\Enums\ListingStatus;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductPriceHistory;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProductService
{
    public function createProduct(array $data, ?User $actor = null): Product
    {
        if ($actor) {
            throw_unless($actor->can('create', Product::class), AuthorizationException::class);
        }

        $normalized = $this->normalizePayload($data);
        $this->ensureSupplierLinkage($normalized, $actor);

        return DB::transaction(function () use ($actor, $normalized): Product {
            $product = Product::query()->create([
                ...Arr::except($normalized, ['retained_image_ids', 'uploaded_images']),
                'created_by' => $actor?->id,
            ]);

            $this->syncImages($product, [], $normalized['uploaded_images']);
            $product->load($this->relations());

            $this->logEvent('product.created', $product, $actor, $normalized);

            return $product;
        });
    }

    public function updateProduct(Product $product, array $data, ?User $actor = null): Product
    {
        if ($actor) {
            throw_unless($actor->can('update', $product), AuthorizationException::class);
        }

        $normalized = $this->normalizePayload($data);
        $this->ensureSupplierLinkage($normalized, $actor);

        return DB::transaction(function () use ($actor, $normalized, $product): Product {
            $previousPrice = (float) $product->price_per_unit_usd;

            $product->update(Arr::except($normalized, ['retained_image_ids', 'uploaded_images']));

            $this->syncImages($product, $normalized['retained_image_ids'], $normalized['uploaded_images']);
            $this->writePriceHistoryIfChanged($product, $previousPrice, (float) $normalized['price_per_unit_usd'], $actor);

            $product->load($this->relations());

            $this->logEvent('product.updated', $product, $actor, $normalized);

            return $product;
        });
    }

    public function archiveProduct(Product $product, ?User $actor = null): Product
    {
        if ($actor) {
            throw_unless($actor->can('archive', $product), AuthorizationException::class);
        }

        return DB::transaction(function () use ($actor, $product): Product {
            $product->forceFill([
                'listing_status' => ListingStatus::Archived,
            ])->save();

            $product->refresh()->load($this->relations());

            $this->logEvent('product.archived', $product, $actor);

            return $product;
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizePayload(array $data): array
    {
        $listingStatus = $data['listing_status'] ?? ListingStatus::Draft->value;

        return [
            'name' => $this->nullableString($data['name'] ?? null) ?? '',
            'product_category_id' => $this->nullableInt($data['product_category_id'] ?? null),
            'linked_supplier_id' => $this->nullableInt($data['linked_supplier_id'] ?? null),
            'description' => $this->nullableString($data['description'] ?? null),
            'quality_grade_id' => $this->nullableInt($data['quality_grade_id'] ?? null),
            'unit_of_measure' => $this->nullableString($data['unit_of_measure'] ?? null) ?? '',
            'price_per_unit_usd' => $this->nullableFloat($data['price_per_unit_usd'] ?? null) ?? 0,
            'minimum_order_quantity' => $this->nullableFloat($data['minimum_order_quantity'] ?? null) ?? 1,
            'stock_available' => $this->nullableFloat($data['stock_available'] ?? null) ?? 0,
            'listing_status' => $listingStatus instanceof ListingStatus
                ? $listingStatus
                : ListingStatus::from($listingStatus),
            'warehouse_sku' => $this->nullableString($data['warehouse_sku'] ?? null),
            'retained_image_ids' => collect($data['retained_image_ids'] ?? [])
                ->map(fn (mixed $id): int => (int) $id)
                ->filter()
                ->values()
                ->all(),
            'uploaded_images' => collect($data['uploaded_images'] ?? [])
                ->filter(fn (mixed $image): bool => $image instanceof UploadedFile)
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $normalized
     */
    private function ensureSupplierLinkage(array $normalized, ?User $actor): void
    {
        $supplier = Supplier::query()->find($normalized['linked_supplier_id']);

        if (! $supplier) {
            throw ValidationException::withMessages([
                'linked_supplier_id' => 'A valid supplier is required for product listings.',
            ]);
        }

        if ($actor && $actor->isRegionalAdmin() && ! Supplier::query()->visibleTo($actor)->whereKey($supplier->id)->exists()) {
            throw new AuthorizationException('The selected supplier is outside your assigned region.');
        }
    }

    /**
     * @param  array<int, int>  $retainedImageIds
     * @param  array<int, UploadedFile>  $uploadedImages
     */
    private function syncImages(Product $product, array $retainedImageIds, array $uploadedImages): void
    {
        $product->loadMissing('images');

        $product->images
            ->whereNotIn('id', $retainedImageIds)
            ->each(function (ProductImage $image): void {
                Storage::disk($this->diskName())->delete($image->path);
                $image->delete();
            });

        $sortOrder = (int) $product->images()->max('sort_order');

        foreach ($uploadedImages as $uploadedImage) {
            $this->guardUploadedImage($uploadedImage);

            $sortOrder++;

            $product->images()->create([
                'path' => $uploadedImage->store('product-images', $this->diskName()),
                'sort_order' => $sortOrder,
            ]);
        }
    }

    private function writePriceHistoryIfChanged(Product $product, float $oldPrice, float $newPrice, ?User $actor): void
    {
        if (round($oldPrice, 2) === round($newPrice, 2)) {
            return;
        }

        ProductPriceHistory::query()->create([
            'product_id' => $product->id,
            'old_price_per_unit_usd' => $oldPrice,
            'new_price_per_unit_usd' => $newPrice,
            'changed_by_user_id' => $actor?->id,
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function relations(): array
    {
        return [
            'category.linkedValueChain',
            'creator',
            'images',
            'priceHistories.changedBy',
            'qualityGrade',
            'supplier.district.region',
            'supplier.qualityGrades',
            'supplier.valueChains',
        ];
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    private function logEvent(string $event, Product $product, ?User $actor, array $properties = []): void
    {
        activity()
            ->performedOn($product)
            ->causedBy($actor)
            ->event($event)
            ->withProperties([
                'product_id' => $product->id,
                'linked_supplier_id' => $product->linked_supplier_id,
                'listing_status' => $product->listing_status?->value,
                'properties' => Arr::except($properties, ['retained_image_ids', 'uploaded_images']),
            ])
            ->log($event);
    }

    private function guardUploadedImage(UploadedFile $image): void
    {
        if (! $image->isValid()) {
            throw ValidationException::withMessages([
                'uploaded_images' => 'One of the uploaded product images is invalid.',
            ]);
        }
    }

    private function diskName(): string
    {
        return (string) config('filesystems.default', 'public');
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function nullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }
}
