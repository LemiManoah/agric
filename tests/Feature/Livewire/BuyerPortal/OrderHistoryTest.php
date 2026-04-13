<?php

use App\Enums\OrderStatus;
use App\Livewire\BuyerPortal\Orders\Index as BuyerOrderIndex;
use App\Models\Buyer;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('shows only buyer owned orders', function () {
    $buyerUser = User::factory()->create();
    $buyerUser->assignRole('buyer');
    $buyer = Buyer::factory()->create(['user_id' => $buyerUser->id]);

    $otherUser = User::factory()->create();
    $otherUser->assignRole('buyer');
    $otherBuyer = Buyer::factory()->create(['user_id' => $otherUser->id]);

    $ownOrder = Order::factory()->create([
        'buyer_id' => $buyer->id,
        'status' => OrderStatus::Pending,
    ]);
    $otherOrder = Order::factory()->create([
        'buyer_id' => $otherBuyer->id,
        'status' => OrderStatus::Confirmed,
    ]);

    Livewire::actingAs($buyerUser)
        ->test(BuyerOrderIndex::class)
        ->assertSee($ownOrder->order_number)
        ->assertDontSee($otherOrder->order_number);

    $this->actingAs($buyerUser)
        ->get(route('buyer-portal.orders.show', $ownOrder))
        ->assertSuccessful()
        ->assertSee($ownOrder->order_number);

    $this->actingAs($buyerUser)
        ->get(route('buyer-portal.orders.show', $otherOrder))
        ->assertForbidden();
});
