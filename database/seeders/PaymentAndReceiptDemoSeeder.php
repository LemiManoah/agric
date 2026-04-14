<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Database\Seeder;

class PaymentAndReceiptDemoSeeder extends Seeder
{
    public function run(PaymentService $paymentService): void
    {
        $actor = User::query()->where('email', 'superadmin@agrofresh.test')->first()
            ?? User::query()->role('super_admin')->first()
            ?? User::factory()->create();

        if (! $actor->hasRole('super_admin')) {
            $actor->assignRole('super_admin');
        }

        $orders = Order::query()
            ->with('buyer')
            ->orderBy('id')
            ->take(5)
            ->get();

        foreach ($orders as $index => $order) {
            if (Payment::query()->where('order_id', $order->id)->exists()) {
                continue;
            }

            $payment = $paymentService->createPendingPayment($order, [
                'amount' => $index === 3 ? max(1, round((float) $order->order_total / 2, 2)) : (float) $order->order_total,
                'method' => ['momo', 'airtel', 'pal_bank', 'wire_transfer', 'escrow'][$index] ?? 'wire_transfer',
                'gateway_transaction_reference' => 'DEMO-PAY-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'gateway_reference_payload' => ['seeded' => true, 'order_number' => $order->order_number],
            ], $actor);

            $paymentService->recordCallback(
                ['seeded' => true, 'status' => 'received', 'order_number' => $order->order_number],
                'sandbox',
                $payment->gateway_transaction_reference,
                true,
            );

            match ($index) {
                0 => $paymentService->markSuccessful($payment, $actor),
                1 => $paymentService->markFailed($payment, $actor, ['reason' => 'Insufficient balance']),
                2 => tap($paymentService->markSuccessful($payment, $actor), fn ($successful) => $paymentService->markRefunded($successful, $actor, ['reason' => 'Demo refund'])),
                3 => $paymentService->markSuccessful($payment, $actor),
                default => null,
            };
        }
    }
}
