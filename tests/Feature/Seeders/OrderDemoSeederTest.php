<?php

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Database\Seeders\BuyerDemoSeeder;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\M1DemoDataSeeder;
use Database\Seeders\OrderDemoSeeder;
use Database\Seeders\ProductCatalogueDemoSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UgandaLocationSeeder;

it('creates useful demo order and cart data', function () {
    $this->seed([
        RolePermissionSeeder::class,
        UgandaLocationSeeder::class,
        DemoUserSeeder::class,
        M1DemoDataSeeder::class,
        BuyerDemoSeeder::class,
        ProductCatalogueDemoSeeder::class,
        OrderDemoSeeder::class,
    ]);

    expect(Order::query()->count())->toBeGreaterThan(1)
        ->and(OrderStatusHistory::query()->count())->toBeGreaterThan(1)
        ->and(Cart::query()->count())->toBeGreaterThan(0);
});
