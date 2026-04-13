<?php

use App\Enums\ListingStatus;
use App\Enums\SupplyFrequency;
use App\Enums\VerificationStatus;
use App\Livewire\BuyerPortal\CheckoutPage;
use App\Models\Buyer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Models\User;
use App\Services\CartService;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('submits checkout and creates an order', function () {
    $buyerUser = User::factory()->create();
    $buyerUser->assignRole('buyer');
    Buyer::factory()->create(['user_id' => $buyerUser->id]);

    $location = createTestLocationHierarchy();
    $supplier = Supplier::factory()->create([
        'operating_district_id' => $location['district']->id,
        'supply_frequency' => SupplyFrequency::Weekly,
        'verification_status' => VerificationStatus::Verified,
    ]);
    $product = Product::factory()->create([
        'product_category_id' => ProductCategory::factory(),
        'linked_supplier_id' => $supplier->id,
        'listing_status' => ListingStatus::Active,
        'minimum_order_quantity' => 12,
        'stock_available' => 100,
    ]);

    app(CartService::class)->addItem($buyerUser, $product, 12);

    Livewire::actingAs($buyerUser)
        ->test(CheckoutPage::class)
        ->set('delivery_address', 'Plot 22, Industrial Area, Kampala, Uganda')
        ->set('buyer_notes', 'Ring before delivery.')
        ->call('submit')
        ->assertHasNoErrors();

    expect(Order::query()->count())->toBe(1)
        ->and(Order::query()->first()->items)->toHaveCount(1);
});
