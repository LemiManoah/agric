<?php

use App\Enums\ListingStatus;
use App\Enums\SupplyFrequency;
use App\Enums\VerificationStatus;
use App\Livewire\BuyerPortal\CartPage;
use App\Models\Buyer;
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

function createBuyerAndCartProduct(): array
{
    $buyerUser = User::factory()->create();
    $buyerUser->assignRole('buyer');
    $buyer = Buyer::factory()->create(['user_id' => $buyerUser->id]);

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
        'minimum_order_quantity' => 10,
        'stock_available' => 100,
        'price_per_unit_usd' => 5,
    ]);

    return compact('buyer', 'buyerUser', 'product');
}

it('renders the buyer cart page', function () {
    ['buyerUser' => $buyerUser, 'product' => $product] = createBuyerAndCartProduct();

    app(CartService::class)->addItem($buyerUser, $product, 10);

    $this->actingAs($buyerUser)
        ->get(route('buyer-portal.cart'))
        ->assertSuccessful()
        ->assertSee('My cart')
        ->assertSee($product->name);
});

it('updates and removes items from the cart page', function () {
    ['buyerUser' => $buyerUser, 'product' => $product] = createBuyerAndCartProduct();

    $cart = app(CartService::class)->addItem($buyerUser, $product, 10);
    $item = $cart->items->firstOrFail();

    Livewire::actingAs($buyerUser)
        ->test(CartPage::class)
        ->set('quantities.'.$item->id, '15')
        ->call('updateQuantity', $item->id)
        ->assertHasNoErrors();

    expect((float) $item->fresh()->quantity)->toBe(15.0);

    Livewire::actingAs($buyerUser)
        ->test(CartPage::class)
        ->call('removeItem', $item->id);

    expect($item->fresh())->toBeNull();
});
