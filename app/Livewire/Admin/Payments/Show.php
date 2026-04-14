<?php

namespace App\Livewire\Admin\Payments;

use App\Models\Payment;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

#[Title('Payment Details')]
class Show extends Component
{
    public Payment $payment;

    public function mount(Payment $payment): void
    {
        abort_unless(auth()->user()?->hasRole('super_admin') || auth()->user()?->isRegionalAdmin(), 403);
        $this->authorize('view', $payment);
        $this->payment = $payment->load($this->relations());
    }

    #[On('payment-updated')]
    public function refreshPayment(): void
    {
        $this->payment->refresh()->load($this->relations());
    }

    public function render(): View
    {
        return view('livewire.admin.payments.show', [
            'activities' => Activity::query()
                ->where('subject_type', Payment::class)
                ->where('subject_id', $this->payment->id)
                ->with('causer')
                ->latest()
                ->limit(15)
                ->get(),
        ])->layout('components.layouts.app');
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
            'order.items.supplier',
            'receipt',
        ];
    }
}
