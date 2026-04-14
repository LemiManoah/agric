<?php

use App\Models\Payment;
use App\Models\Receipt;
use Database\Seeders\NotificationTemplateSeeder;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(NotificationTemplateSeeder::class);
});

it('shows only the authenticated buyer payments', function () {
    ['buyer' => $buyer, 'buyerUser' => $buyerUser, 'order' => $order] = createPaymentOrderContext();
    $other = createPaymentOrderContext();

    $payment = Payment::factory()->create(['order_id' => $order->id]);
    $otherPayment = Payment::factory()->create(['order_id' => $other['order']->id]);

    $this->actingAs($buyerUser)
        ->get(route('buyer-portal.payments.index'))
        ->assertSuccessful()
        ->assertSee($payment->order->order_number)
        ->assertDontSee($otherPayment->order->order_number);
});

it('blocks buyers from viewing receipts they do not own', function () {
    ['order' => $order] = createPaymentOrderContext();
    $other = createPaymentOrderContext();
    $receipt = Receipt::factory()->create(['order_id' => $other['order']->id]);

    $this->actingAs($order->buyer->user)
        ->get(route('buyer-portal.receipts.show', $receipt))
        ->assertForbidden();
});
