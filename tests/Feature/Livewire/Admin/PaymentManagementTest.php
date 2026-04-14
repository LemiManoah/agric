<?php

use App\Enums\PaymentLifecycleStatus;
use App\Livewire\Admin\Payments\ActionPanel;
use App\Models\Payment;
use App\Models\PaymentCallback;
use App\Models\User;
use Database\Seeders\NotificationTemplateSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(NotificationTemplateSeeder::class);
    Storage::fake(config('filesystems.default', 'public'));
});

it('renders the admin payment index for a super admin', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $this->actingAs($admin)
        ->get(route('admin.payments.index'))
        ->assertSuccessful()
        ->assertSee('Payments');
});

it('renders the payment detail page with callback data', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    ['order' => $order] = createPaymentOrderContext();
    $payment = Payment::factory()->create([
        'order_id' => $order->id,
        'gateway_transaction_reference' => 'PAY-SHOW-1',
    ]);
    PaymentCallback::factory()->create([
        'reference' => 'PAY-SHOW-1',
        'provider' => 'sandbox',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.payments.show', $payment))
        ->assertSuccessful()
        ->assertSee($order->order_number)
        ->assertSee('sandbox');
});

it('updates payment status through the action panel', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    ['order' => $order] = createPaymentOrderContext();
    $payment = Payment::factory()->create([
        'order_id' => $order->id,
        'status' => PaymentLifecycleStatus::Pending,
    ]);

    Livewire::actingAs($admin)
        ->test(ActionPanel::class, ['payment' => $payment])
        ->call('markSuccessful')
        ->assertHasNoErrors();

    expect($payment->fresh()->status)->not->toBe(PaymentLifecycleStatus::Pending);
});
