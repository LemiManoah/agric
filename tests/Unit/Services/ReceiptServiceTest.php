<?php

use App\Enums\ListingStatus;
use App\Enums\PaymentLifecycleStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\SupplyFrequency;
use App\Enums\VerificationStatus;
use App\Models\Buyer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Receipt;
use App\Models\Supplier;
use App\Models\User;
use App\Services\ReceiptService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Storage::fake(config('filesystems.default', 'public'));
});

it('generates and stores a receipt pdf for an order', function () {
    $location = createTestLocationHierarchy();
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
        'price_per_unit_usd' => 4,
    ]);
    $order = Order::factory()->create([
        'buyer_id' => $buyer->id,
        'subtotal' => 80,
        'discount_applied' => 0,
        'order_total' => 80,
        'payment_status' => PaymentStatus::Paid,
    ]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'supplier_id' => $supplier->id,
        'product_name_snapshot' => $product->name,
        'quantity' => 20,
        'unit_price_usd' => 4,
        'line_total_usd' => 80,
    ]);
    $payment = Payment::factory()->create([
        'order_id' => $order->id,
        'method' => PaymentMethod::WireTransfer,
        'status' => PaymentLifecycleStatus::Successful,
        'amount' => 80,
        'currency' => 'USD',
    ]);
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $receipt = app(ReceiptService::class)->generateForOrder($order, $payment, $admin);

    expect($receipt)->toBeInstanceOf(Receipt::class)
        ->and(Storage::disk(config('filesystems.default', 'public'))->exists($receipt->file_path))->toBeTrue()
        ->and(Storage::disk(config('filesystems.default', 'public'))->get($receipt->file_path))->toStartWith('%PDF-1.4');
});
