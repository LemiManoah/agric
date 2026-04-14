<?php

use App\Enums\PaymentLifecycleStatus;
use App\Models\Payment;
use App\Models\User;
use Database\Seeders\NotificationTemplateSeeder;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(NotificationTemplateSeeder::class);
});

it('renders the payment summary report for a super admin', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    ['order' => $order] = createPaymentOrderContext();

    Payment::factory()->create(['order_id' => $order->id, 'status' => PaymentLifecycleStatus::Successful, 'amount' => 100]);
    Payment::factory()->create(['order_id' => $order->id, 'status' => PaymentLifecycleStatus::Failed, 'amount' => 20]);

    $this->actingAs($admin)
        ->get(route('admin.reports.payment-summary'))
        ->assertSuccessful()
        ->assertSee('Payment summary')
        ->assertSee('Total payments')
        ->assertSee('Successful payments');
});
