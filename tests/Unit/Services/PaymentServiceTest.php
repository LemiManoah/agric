<?php

use App\Enums\PaymentLifecycleStatus;
use App\Enums\PaymentStatus;
use App\Models\OutboundNotification;
use App\Models\Receipt;
use App\Services\PaymentService;
use Database\Seeders\NotificationTemplateSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(NotificationTemplateSeeder::class);
    Storage::fake(config('filesystems.default', 'public'));
});

it('creates a pending payment and updates order payment fields', function () {
    ['admin' => $admin, 'order' => $order] = createPaymentOrderContext();

    $payment = app(PaymentService::class)->createPendingPayment($order, [
        'method' => 'momo',
        'gateway_transaction_reference' => 'PAY-1001',
    ], $admin);

    expect($payment->status)->toBe(PaymentLifecycleStatus::Pending)
        ->and($payment->method->value)->toBe('momo')
        ->and($order->fresh()->payment_method?->value)->toBe('momo')
        ->and($order->fresh()->payment_reference)->toBe('PAY-1001');
});

it('marks payments successful and generates receipts and notifications', function () {
    ['admin' => $admin, 'order' => $order] = createPaymentOrderContext();
    $service = app(PaymentService::class);

    $payment = $service->createPendingPayment($order, [
        'method' => 'wire_transfer',
        'gateway_transaction_reference' => 'PAY-2001',
    ], $admin);

    $payment = $service->markSuccessful($payment, $admin);

    expect($payment->status)->toBe(PaymentLifecycleStatus::Successful)
        ->and($order->fresh()->payment_status)->toBe(PaymentStatus::Paid)
        ->and(Receipt::query()->count())->toBe(1)
        ->and(OutboundNotification::query()->where('template_key', 'payment_received_email')->exists())->toBeTrue()
        ->and(OutboundNotification::query()->where('template_key', 'receipt_ready_email')->exists())->toBeTrue();
});

it('marks payments failed, refunded, and records callbacks', function () {
    ['admin' => $admin, 'order' => $order] = createPaymentOrderContext();
    $service = app(PaymentService::class);

    $failedPayment = $service->createPendingPayment($order, [
        'method' => 'airtel',
        'gateway_transaction_reference' => 'PAY-3001',
    ], $admin);

    $failedPayment = $service->markFailed($failedPayment, $admin, ['reason' => 'Gateway rejected']);

    expect($failedPayment->status)->toBe(PaymentLifecycleStatus::Failed)
        ->and($order->fresh()->payment_status)->toBe(PaymentStatus::Failed);

    $successfulPayment = $service->createPendingPayment($order->fresh(), [
        'method' => 'pal_bank',
        'gateway_transaction_reference' => 'PAY-3002',
    ], $admin);

    $successfulPayment = $service->markSuccessful($successfulPayment, $admin);
    $successfulPayment = $service->markRefunded($successfulPayment, $admin, ['reason' => 'Customer request']);

    $callback = $service->recordCallback(['ok' => true], 'sandbox', 'PAY-3002', true);

    expect($successfulPayment->status)->toBe(PaymentLifecycleStatus::Refunded)
        ->and($callback->reference)->toBe('PAY-3002');
});
