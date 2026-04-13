<?php

use App\Enums\OrderStatus;
use App\Livewire\Admin\Orders\StatusAction;
use App\Models\Buyer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('renders the admin order index for a super admin', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $this->actingAs($admin)
        ->get(route('admin.orders.index'))
        ->assertSuccessful()
        ->assertSee('Orders');
});

it('limits regional admin order visibility by supplier scope', function () {
    $visible = createTestLocationHierarchy();
    $hidden = createTestLocationHierarchy();

    $regionalAdmin = createScopedUser('regional_admin', [
        'region_id' => $visible['region']->id,
        'district_id' => $visible['district']->id,
    ]);

    $visibleSupplier = Supplier::factory()->create([
        'operating_district_id' => $visible['district']->id,
    ]);
    $hiddenSupplier = Supplier::factory()->create([
        'operating_district_id' => $hidden['district']->id,
    ]);

    $visibleOrder = Order::factory()->create([
        'buyer_id' => Buyer::factory(),
        'status' => OrderStatus::Pending,
    ]);
    OrderItem::factory()->create([
        'order_id' => $visibleOrder->id,
        'supplier_id' => $visibleSupplier->id,
    ]);

    $hiddenOrder = Order::factory()->create([
        'buyer_id' => Buyer::factory(),
        'status' => OrderStatus::Pending,
    ]);
    OrderItem::factory()->create([
        'order_id' => $hiddenOrder->id,
        'supplier_id' => $hiddenSupplier->id,
    ]);

    $this->actingAs($regionalAdmin)
        ->get(route('admin.orders.index'))
        ->assertSuccessful()
        ->assertSee($visibleOrder->order_number)
        ->assertDontSee($hiddenOrder->order_number);
});

it('updates order status through the admin action component', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    $order = Order::factory()->create([
        'status' => OrderStatus::Pending,
    ]);

    Livewire::actingAs($admin)
        ->test(StatusAction::class, ['order' => $order])
        ->call('confirm')
        ->assertHasNoErrors();

    expect($order->fresh()->status)->toBe(OrderStatus::Confirmed);
});
