<?php

use App\Enums\ListingStatus;
use App\Enums\SupplyFrequency;
use App\Enums\VerificationStatus;
use App\Livewire\AgentPortal\CheckoutForBuyer;
use App\Models\Agent;
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

it('allows an agent to place an order for a buyer', function () {
    $agentUser = User::factory()->create();
    $agentUser->assignRole('agent');
    $location = createTestLocationHierarchy();
    $agent = Agent::factory()->create([
        'user_id' => $agentUser->id,
        'primary_district_id' => $location['district']->id,
    ]);

    $buyer = Buyer::factory()->create();
    $supplier = Supplier::factory()->create([
        'operating_district_id' => $location['district']->id,
        'supply_frequency' => SupplyFrequency::Weekly,
        'verification_status' => VerificationStatus::Verified,
    ]);
    $product = Product::factory()->create([
        'product_category_id' => ProductCategory::factory(),
        'linked_supplier_id' => $supplier->id,
        'listing_status' => ListingStatus::Active,
        'minimum_order_quantity' => 8,
        'stock_available' => 100,
    ]);

    app(CartService::class)->addItem($agentUser, $product, 8);

    Livewire::actingAs($agentUser)
        ->test(CheckoutForBuyer::class)
        ->set('buyer_id', $buyer->id)
        ->set('delivery_address', 'Entebbe Road, Kampala, Uganda')
        ->call('submit')
        ->assertHasNoErrors();

    $order = Order::query()->first();

    expect($order)->not->toBeNull()
        ->and($order->placed_by_agent_id)->toBe($agent->id)
        ->and($order->buyer_id)->toBe($buyer->id);

    $this->actingAs($agentUser)
        ->get(route('agent-portal.orders.index'))
        ->assertSuccessful()
        ->assertSee($order->order_number);
});
