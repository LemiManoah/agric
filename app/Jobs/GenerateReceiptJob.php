<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\ReceiptService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateReceiptJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $orderId,
        public ?int $paymentId = null,
        public ?int $actorId = null,
    ) {}

    public function handle(ReceiptService $receiptService): void
    {
        $order = Order::query()->findOrFail($this->orderId);
        $payment = $this->paymentId ? Payment::query()->find($this->paymentId) : null;
        $actor = $this->actorId ? User::query()->find($this->actorId) : null;

        $receiptService->generateForOrder($order, $payment, $actor);
    }
}
