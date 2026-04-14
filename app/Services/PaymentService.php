<?php

namespace App\Services;

use App\Enums\NotificationChannel;
use App\Enums\PaymentLifecycleStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentCallback;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        private NotificationService $notificationService,
        private ReceiptService $receiptService,
    ) {}

    public function createPendingPayment(Order $order, array $data, ?User $actor = null): Payment
    {
        if ($actor) {
            throw_unless($actor->can('create', Payment::class), AuthorizationException::class);
        }

        $order->loadMissing('buyer');
        $normalized = $this->normalizePaymentData($order, $data);

        return DB::transaction(function () use ($actor, $normalized, $order): Payment {
            $payment = Payment::query()->create([
                'order_id' => $order->id,
                'method' => $normalized['method'],
                'gateway_transaction_reference' => $normalized['gateway_transaction_reference'],
                'gateway_reference_payload' => $normalized['gateway_reference_payload'],
                'amount' => $normalized['amount'],
                'currency' => $normalized['currency'],
                'exchange_rate_to_ugx' => $normalized['exchange_rate_to_ugx'],
                'status' => PaymentLifecycleStatus::Pending,
                'created_by' => $actor?->id,
            ]);

            $order->forceFill([
                'payment_method' => $payment->method,
                'payment_reference' => $payment->gateway_transaction_reference ?: $order->payment_reference,
                'payment_status' => $order->payment_status ?: PaymentStatus::Unpaid,
            ])->save();

            $this->logEvent('payment.created', $payment, $actor, [
                'order_number' => $order->order_number,
                'amount' => $payment->amount,
            ]);

            return $payment->fresh($this->relations());
        });
    }

    public function markSuccessful(Payment $payment, ?User $actor = null, array $context = []): Payment
    {
        if ($actor) {
            throw_unless($actor->can('confirm', $payment), AuthorizationException::class);
        }

        return DB::transaction(function () use ($actor, $context, $payment): Payment {
            $payment->loadMissing('order.buyer');

            $status = (float) $payment->amount < (float) $payment->order->order_total
                ? PaymentLifecycleStatus::Partial
                : PaymentLifecycleStatus::Successful;

            $payment->forceFill([
                'status' => $status,
                'paid_at' => now(),
                'confirmed_by_user_id' => $actor?->id,
                'gateway_transaction_reference' => $context['gateway_transaction_reference'] ?? $payment->gateway_transaction_reference,
                'gateway_reference_payload' => $context['gateway_reference_payload'] ?? $payment->gateway_reference_payload,
            ])->save();

            $this->syncOrderPaymentState($payment->order, $payment);

            $receipt = $this->receiptService->generateForOrder($payment->order, $payment, $actor);
            $this->queueBuyerPaymentNotifications($payment, 'payment_received');
            $this->queueReceiptReadyNotifications($payment, $receipt);
            $this->logEvent('payment.successful', $payment, $actor, Arr::only($context, ['gateway_transaction_reference']));

            return $payment->fresh($this->relations());
        });
    }

    public function markFailed(Payment $payment, ?User $actor = null, array $context = []): Payment
    {
        if ($actor) {
            throw_unless($actor->can('confirm', $payment), AuthorizationException::class);
        }

        return DB::transaction(function () use ($actor, $context, $payment): Payment {
            $payment->loadMissing('order.buyer');

            $payment->forceFill([
                'status' => PaymentLifecycleStatus::Failed,
                'gateway_reference_payload' => $context['gateway_reference_payload'] ?? $payment->gateway_reference_payload,
            ])->save();

            $this->syncOrderPaymentState($payment->order, $payment);
            $this->queueBuyerPaymentNotifications($payment, 'payment_failed');
            $this->logEvent('payment.failed', $payment, $actor, Arr::only($context, ['reason']));

            return $payment->fresh($this->relations());
        });
    }

    public function markRefunded(Payment $payment, ?User $actor = null, array $context = []): Payment
    {
        if ($actor) {
            throw_unless($actor->can('refund', $payment), AuthorizationException::class);
        }

        return DB::transaction(function () use ($actor, $context, $payment): Payment {
            $payment->loadMissing('order');

            $payment->forceFill([
                'status' => PaymentLifecycleStatus::Refunded,
            ])->save();

            $this->syncOrderPaymentState($payment->order, $payment);
            $this->logEvent('payment.refunded', $payment, $actor, Arr::only($context, ['reason']));

            return $payment->fresh($this->relations());
        });
    }

    public function recordCallback(array $payload, string $provider, ?string $reference = null, bool $signatureValid = false): PaymentCallback
    {
        return PaymentCallback::query()->create([
            'provider' => $provider,
            'reference' => $reference,
            'payload' => $payload,
            'signature_valid' => $signatureValid,
            'processed_at' => now(),
        ]);
    }

    private function syncOrderPaymentState(Order $order, Payment $payment): void
    {
        $order->loadMissing('payments');

        $successfulTotal = (float) $order->payments
            ->whereIn('status', [PaymentLifecycleStatus::Successful, PaymentLifecycleStatus::Partial])
            ->sum('amount');

        $hasRefundedPayment = $order->payments->contains(
            fn (Payment $item): bool => $item->status === PaymentLifecycleStatus::Refunded
        );

        $hasFailedPayment = $order->payments->contains(
            fn (Payment $item): bool => $item->status === PaymentLifecycleStatus::Failed
        );

        $paymentStatus = PaymentStatus::Unpaid;

        if ($successfulTotal >= (float) $order->order_total && (float) $order->order_total > 0) {
            $paymentStatus = PaymentStatus::Paid;
        } elseif ($successfulTotal > 0) {
            $paymentStatus = PaymentStatus::Partial;
        } elseif ($hasRefundedPayment) {
            $paymentStatus = PaymentStatus::Refunded;
        } elseif ($hasFailedPayment) {
            $paymentStatus = PaymentStatus::Failed;
        }

        $order->forceFill([
            'payment_method' => $payment->method,
            'payment_status' => $paymentStatus,
            'payment_reference' => $payment->gateway_transaction_reference ?: $order->payment_reference,
        ])->save();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizePaymentData(Order $order, array $data): array
    {
        $amount = isset($data['amount']) ? (float) $data['amount'] : (float) $order->order_total;
        $method = $data['method'] ?? $order->payment_method?->value ?? 'wire_transfer';

        return [
            'method' => $method,
            'gateway_transaction_reference' => $this->nullableString($data['gateway_transaction_reference'] ?? null),
            'gateway_reference_payload' => $data['gateway_reference_payload'] ?? null,
            'amount' => $amount,
            'currency' => strtoupper((string) ($data['currency'] ?? 'USD')),
            'exchange_rate_to_ugx' => isset($data['exchange_rate_to_ugx']) ? (float) $data['exchange_rate_to_ugx'] : null,
        ];
    }

    private function queueBuyerPaymentNotifications(Payment $payment, string $templateKey): void
    {
        $buyer = $payment->order->buyer;

        if (! $buyer) {
            return;
        }

        $payload = [
            'order_number' => $payment->order->order_number,
            'amount' => number_format((float) $payment->amount, 2),
            'buyer_name' => $buyer->contact_person_full_name,
            'status' => $payment->status->value,
        ];

        if ($buyer->email) {
            $this->notificationService->queueTemplate($templateKey, NotificationChannel::Email->value, $buyer->email, $payload, $buyer);
        }

        if ($buyer->phone) {
            $this->notificationService->queueTemplate($templateKey, NotificationChannel::Sms->value, $buyer->phone, $payload, $buyer);
        }
    }

    private function queueReceiptReadyNotifications(Payment $payment, $receipt): void
    {
        $buyer = $payment->order->buyer;

        if (! $buyer) {
            return;
        }

        $payload = [
            'order_number' => $payment->order->order_number,
            'amount' => number_format((float) $payment->amount, 2),
            'buyer_name' => $buyer->contact_person_full_name,
            'receipt_id' => $receipt->id,
        ];

        if ($buyer->email) {
            $this->notificationService->queueTemplate('receipt_ready', NotificationChannel::Email->value, $buyer->email, $payload, $buyer);
        }
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    private function logEvent(string $event, Payment $payment, ?User $actor = null, array $properties = []): void
    {
        activity()
            ->performedOn($payment)
            ->causedBy($actor)
            ->event($event)
            ->withProperties([
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'status' => $payment->status->value,
                'properties' => Arr::whereNotNull($properties),
            ])
            ->log($event);
    }

    /**
     * @return array<int, string>
     */
    private function relations(): array
    {
        return [
            'callbacks',
            'confirmedBy',
            'creator',
            'order.buyer',
            'receipt',
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }
}
