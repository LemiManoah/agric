<?php

use App\Enums\ListingStatus;
use App\Enums\SupplyFrequency;
use App\Enums\VerificationStatus;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Models\User;
use App\Services\CartService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function createOrderableProduct(array $overrides = []): Product
{
    $location = createTestLocationHierarchy();

    $supplier = Supplier::factory()->create(array_merge([
        'operating_district_id' => $location['district']->id,
        'supply_frequency' => SupplyFrequency::Weekly,
        'verification_status' => VerificationStatus::Verified,
    ], $overrides['supplier'] ?? []));

    return Product::factory()->create(array_merge([
        'product_category_id' => ProductCategory::factory(),
        'linked_supplier_id' => $supplier->id,
        'listing_status' => ListingStatus::Active,
        'minimum_order_quantity' => 10,
        'stock_available' => 250,
        'price_per_unit_usd' => 2.5,
    ], $overrides['product'] ?? []));
}

it('gets or creates a single cart per user', function () {
    $user = User::factory()->create();
    $service = app(CartService::class);

    $firstCart = $service->getOrCreateCartForUser($user);
    $secondCart = $service->getOrCreateCartForUser($user);

    expect($firstCart->is($secondCart))->toBeTrue();
});

it('adds items with a product price snapshot', function () {
    $user = User::factory()->create();
    $product = createOrderableProduct();
    $service = app(CartService::class);

    $cart = $service->addItem($user, $product, 15);
    $item = $cart->items->first();

    expect($item)->not->toBeNull()
        ->and((float) $item->quantity)->toBe(15.0)
        ->and((float) $item->unit_price_usd)->toBe(2.5);
});

it('updates and removes cart items', function () {
    $user = User::factory()->create();
    $product = createOrderableProduct();
    $service = app(CartService::class);

    $cart = $service->addItem($user, $product, 12);
    $item = $cart->items->firstOrFail();

    $updatedCart = $service->updateItemQuantity($item, 20, $user);

    expect((float) $updatedCart->items->first()->quantity)->toBe(20.0);

    $emptyCart = $service->removeItem($updatedCart->items->first(), $user);

    expect($emptyCart->items)->toHaveCount(0);
});

it('rejects products that are not orderable', function () {
    $user = User::factory()->create();
    $product = createOrderableProduct([
        'product' => [
            'listing_status' => ListingStatus::Draft,
        ],
    ]);
    $service = app(CartService::class);

    $service->addItem($user, $product, 10);
})->throws(ValidationException::class);
