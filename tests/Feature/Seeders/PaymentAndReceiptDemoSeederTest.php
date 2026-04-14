<?php

use App\Models\OutboundNotification;
use App\Models\Payment;
use App\Models\Receipt;
use Database\Seeders\BuyerDemoSeeder;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\M1DemoDataSeeder;
use Database\Seeders\NotificationTemplateSeeder;
use Database\Seeders\OrderDemoSeeder;
use Database\Seeders\PaymentAndReceiptDemoSeeder;
use Database\Seeders\ProductCatalogueDemoSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UgandaLocationSeeder;
use Illuminate\Support\Facades\Storage;

it('creates meaningful payment, receipt, and notification demo data', function () {
    Storage::fake(config('filesystems.default', 'public'));

    $this->seed([
        RolePermissionSeeder::class,
        UgandaLocationSeeder::class,
        NotificationTemplateSeeder::class,
        DemoUserSeeder::class,
        M1DemoDataSeeder::class,
        BuyerDemoSeeder::class,
        ProductCatalogueDemoSeeder::class,
        OrderDemoSeeder::class,
        PaymentAndReceiptDemoSeeder::class,
    ]);

    expect(Payment::query()->count())->toBeGreaterThan(0)
        ->and(Receipt::query()->count())->toBeGreaterThan(0)
        ->and(OutboundNotification::query()->count())->toBeGreaterThan(0);
});
