<?php

namespace App\Livewire\Admin\Payments;

use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ActionPanel extends Component
{
    protected PaymentService $paymentService;

    public Payment $payment;

    public function boot(PaymentService $paymentService): void
    {
        $this->paymentService = $paymentService;
    }

    public function mount(Payment $payment): void
    {
        $this->payment = $payment;
    }

    public function markSuccessful(): void
    {
        $this->authorize('confirm', $this->payment);
        $this->paymentService->markSuccessful($this->payment, auth()->user());
        $this->afterAction('Payment marked successful.');
    }

    public function markFailed(): void
    {
        $this->authorize('confirm', $this->payment);
        $this->paymentService->markFailed($this->payment, auth()->user());
        $this->afterAction('Payment marked failed.');
    }

    public function markRefunded(): void
    {
        $this->authorize('refund', $this->payment);
        $this->paymentService->markRefunded($this->payment, auth()->user());
        $this->afterAction('Payment refunded.');
    }

    public function render(): View
    {
        return view('livewire.admin.payments.action-panel');
    }

    private function afterAction(string $message): void
    {
        $this->payment->refresh();
        session()->flash('status', $message);
        $this->dispatch('payment-updated');
    }
}
