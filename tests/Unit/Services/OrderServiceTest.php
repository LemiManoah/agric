<?php

use App\Enums\ListingStatus;
use App\Enums\OrderStatus;
use App\Enums\SupplyFrequency;
use App\Enums\VerificationStatus;
use App\Models\Agent;
use App\Models\Buyer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function createOrderActors(): array
{
    $buyerUser = User::factory()->create();
    $buyerUser->assignRole('buyer');
    $buyer = Buyer::factory()->create(['user_id' => $buyerUser->id]);

    $agentUser = User::factory()->create();
    $agentUser->assignRole('agent');
    $location = createTestLocationHierarchy();
    $agent = Agent::factory()->create([
        'user_id' => $agentUser->id,
        'primary_district_id' => $location['district']->id,
    ]);

    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    return compact('admin', 'agent', 'agentUser', 'buyer', 'buyerUser', 'location');
}

function createOrderProduct(): Product
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
        'stock_available' => 300,
        'price_per_unit_usd' => 3.25,
    ]);
}

it('creates a buyer checkout order and clears the cart', function () {
    ['buyer' => $buyer, 'buyerUser' => $buyerUser] = createOrderActors();
    $product = createOrderProduct();
    $cartService = app(CartService::class);
    $orderService = app(OrderService::class);

    $cartService->addItem($buyerUser, $product, 20);

    $order = $orderService->checkoutBuyer($buyer, $buyerUser, [
        'delivery_address' => 'Nakawa Division, Kampala, Uganda',
        'buyer_notes' => 'Please call before delivery.',
    ]);

    expect($order->order_number)->toStartWith('AGF-'.now()->format('Y').'-')
        ->and($order->status)->toBe(OrderStatus::Pending)
        ->and($order->items)->toHaveCount(1)
        ->and($buyerUser->fresh()->id)->not->toBeNull()
        ->and($cartService->getOrCreateCartForUser($buyerUser)->fresh()->items)->toHaveCount(0)
        ->and((float) $product->fresh()->stock_available)->toBe(280.0);
});

it('supports agent checkout for a buyer', function () {
    ['agent' => $agent, 'agentUser' => $agentUser, 'buyer' => $buyer] = createOrderActors();
    $product = createOrderProduct();
    $cartService = app(CartService::class);
    $orderService = app(OrderService::class);

    $cartService->addItem($agentUser, $product, 10);

    $order = $orderService->checkoutAgentForBuyer($agent, $buyer, $agentUser, [
        'delivery_address' => 'Industrial Area, Jinja, Uganda',
    ]);

    expect($order->placed_by_agent_id)->toBe($agent->id)
        ->and($agent->fresh()->total_orders_placed)->toBeGreaterThan(0);
});

it('changes status, cancels orders, and writes activity logs', function () {
    ['admin' => $admin, 'buyer' => $buyer, 'buyerUser' => $buyerUser] = createOrderActors();
    $product = createOrderProduct();
    $cartService = app(CartService::class);
    $orderService = app(OrderService::class);

    $cartService->addItem($buyerUser, $product, 10);

    $order = $orderService->checkoutBuyer($buyer, $buyerUser, [
        'delivery_address' => 'Mbarara City, Uganda',
    ]);

    $order = $orderService->changeStatus($order, OrderStatus::Confirmed, $admin);
    $order = $orderService->cancelOrder($order, $admin, 'Out of schedule window.');

    expect($order->status)->toBe(OrderStatus::Cancelled)
        ->and($order->statusHistories)->toHaveCount(3)
        ->and((float) $product->fresh()->stock_available)->toBe(300.0)
        ->and(Activity::query()->where('event', 'order.cancelled')->exists())->toBeTrue();
});

it('rejects invalid status transitions', function () {
    $order = Order::factory()->create([
        'status' => OrderStatus::Pending,
    ]);
    $service = app(OrderService::class);

    $service->changeStatus($order, OrderStatus::Delivered);
})->throws(ValidationException::class);
