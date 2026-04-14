<?php

use App\Enums\ListingStatus;
use App\Enums\SupplyFrequency;
use App\Enums\VerificationStatus;
use App\Models\Buyer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function createPublicCatalogueProduct(): Product
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
        'minimum_order_quantity' => 10,
        'stock_available' => 120,
    ]);
}

it('shows the main public navigation on the landing page', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee(route('catalogue.index'), false)
        ->assertSee(route('login'), false)
        ->assertSee(route('farmer-portal.registration.create'), false)
        ->assertSee(route('buyer-portal.registration.create'), false);
});

it('shows sign in to buy messaging to guests on the public catalogue', function () {
    $product = createPublicCatalogueProduct();

    $this->get(route('catalogue.show', $product))
        ->assertSuccessful()
        ->assertSee('Sign in to buy')
        ->assertSee(route('home'), false)
        ->assertSee(route('catalogue.index'), false);
});

it('shows add to cart actions to signed in buyer users', function () {
    $product = createPublicCatalogueProduct();

    $buyerUser = User::factory()->create();
    $buyerUser->assignRole('buyer');
    Buyer::factory()->create(['user_id' => $buyerUser->id]);

    $this->actingAs($buyerUser)
        ->get(route('catalogue.show', $product))
        ->assertSuccessful()
        ->assertSee('Add to cart')
        ->assertSee(route('buyer-portal.cart'), false);
});
