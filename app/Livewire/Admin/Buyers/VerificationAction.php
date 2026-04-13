<?php

namespace App\Livewire\Admin\Buyers;

use App\Models\Buyer;
use App\Services\BuyerService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class VerificationAction extends Component
{
    protected BuyerService $buyerService;

    public Buyer $buyer;

    public function boot(BuyerService $buyerService): void
    {
        $this->buyerService = $buyerService;
    }

    public function mount(Buyer $buyer): void
    {
        $this->buyer = $buyer;
    }

    public function verify(): void
    {
        $this->authorize('verify', $this->buyer);

        $this->buyerService->verifyBuyer($this->buyer, auth()->user());
        $this->afterAction('Buyer verified successfully.');
    }

    public function suspend(): void
    {
        $this->authorize('verify', $this->buyer);

        $this->buyerService->suspendBuyer($this->buyer, auth()->user());
        $this->afterAction('Buyer suspended successfully.');
    }

    public function render(): View
    {
        return view('livewire.admin.buyers.verification-action', [
            'buyer' => $this->buyer,
        ]);
    }

    private function afterAction(string $message): void
    {
        $this->buyer->refresh();

        session()->flash('status', $message);
        $this->dispatch('buyer-status-updated');
    }
}
