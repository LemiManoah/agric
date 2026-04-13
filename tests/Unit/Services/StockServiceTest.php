<?php

use App\Enums\ListingStatus;
use App\Enums\SupplyFrequency;
use App\Enums\VerificationStatus;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Models\User;
use App\Services\StockService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function createStockProduct(): Product
{
    $location = createTestLocationHierarchy();
    $supplier = Supplier::factory()->create([
        'operating_district_id' => $location['district']->id,
        'supply_frequency' => SupplyFrequency::Weekly,
        'verification_status' => VerificationStatus::Verified,
    ]);

    return Product::factory()->create([
        'product_category_id' => ProductCategory::factory(),
        'linked_supplier_id' => $supplier->id,
        'listing_status' => ListingStatus::Active,
        'minimum_order_quantity' => 5,
        'stock_available' => 100,
    ]);
}

it('reserves stock and logs the action', function () {
    $actor = User::factory()->create();
    $product = createStockProduct();
    $service = app(StockService::class);

    $service->reserve($product, 25, $actor);

    expect((float) $product->fresh()->stock_available)->toBe(75.0)
        ->and(Activity::query()->where('event', 'stock.reserved')->exists())->toBeTrue();
});

it('releases stock and reactivates out of stock listings', function () {
    $actor = User::factory()->create();
    $product = createStockProduct()->forceFill([
        'listing_status' => ListingStatus::OutOfStock,
        'stock_available' => 0,
    ]);
    $product->save();

    $service = app(StockService::class);
    $service->release($product, 20, $actor);

    expect((float) $product->fresh()->stock_available)->toBe(20.0)
        ->and($product->fresh()->listing_status)->toBe(ListingStatus::Active);
});

it('rejects insufficient stock', function () {
    $product = createStockProduct();
    $service = app(StockService::class);

    $service->assertAvailable($product, 1000);
})->throws(ValidationException::class);
