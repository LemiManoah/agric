<?php

use App\Enums\NotificationDeliveryStatus;
use App\Models\OutboundNotification;
use App\Services\NotificationService;
use Database\Seeders\NotificationTemplateSeeder;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(NotificationTemplateSeeder::class);
});

it('queues a rendered notification from a template', function () {
    $notification = app(NotificationService::class)->queueTemplate(
        'order_placed',
        'email',
        'buyer@example.test',
        [
            'buyer_name' => 'Amina',
            'order_number' => 'AGF-2026-00001',
            'amount' => '100.00',
        ],
    );

    expect($notification)->toBeInstanceOf(OutboundNotification::class)
        ->and($notification->status)->toBe(NotificationDeliveryStatus::Queued)
        ->and($notification->rendered_message)->toContain('AGF-2026-00001')
        ->and($notification->logs)->toHaveCount(1);
});

it('marks notifications sent, delivered, and failed', function () {
    $service = app(NotificationService::class);
    $notification = $service->queueTemplate('payment_failed', 'sms', '256700000000', [
        'buyer_name' => 'Amina',
        'order_number' => 'AGF-2026-00002',
    ]);

    $notification = $service->markSent($notification, 'MSG-1');
    $notification = $service->markDelivered($notification, ['provider' => 'sandbox']);
    $failed = $service->queueTemplate('receipt_ready', 'email', 'buyer@example.test', [
        'buyer_name' => 'Amina',
        'order_number' => 'AGF-2026-00003',
    ]);
    $failed = $service->markFailed($failed, 'Mailbox unavailable');

    expect($notification->status)->toBe(NotificationDeliveryStatus::Delivered)
        ->and($failed->status)->toBe(NotificationDeliveryStatus::Failed)
        ->and($failed->failure_reason)->toBe('Mailbox unavailable');
});
