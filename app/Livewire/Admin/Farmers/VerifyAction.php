<?php

namespace App\Livewire\Admin\Farmers;

use App\Enums\VerificationStatus;
use App\Models\Farmer;
use App\Services\FarmerRegistrationService;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class VerifyAction extends Component
{
    use AuthorizesRequests;

    protected FarmerRegistrationService $registrationService;

    public Farmer $farmer;

    public string $note = '';

    public function boot(FarmerRegistrationService $registrationService): void
    {
        $this->registrationService = $registrationService;
    }

    public function mount(Farmer $farmer): void
    {
        $this->authorize('verify', $farmer);

        $this->farmer = $farmer;
    }

    public function approve(): void
    {
        $this->registrationService->verifyFarmer($this->farmer, auth()->user());

        $this->afterReview('Farmer verification approved.');
    }

    public function reject(): void
    {
        $this->reviewAs(VerificationStatus::Rejected, 'Farmer verification rejected.');
    }

    public function suspend(): void
    {
        $this->reviewAs(VerificationStatus::Suspended, 'Farmer verification suspended.');
    }

    public function render(): View
    {
        return view('livewire.admin.farmers.verify-action');
    }

    private function reviewAs(VerificationStatus $status, string $message): void
    {
        $this->validate([
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->registrationService->changeVerificationStatus(
            $this->farmer,
            $status,
            auth()->user(),
            trim($this->note) !== '' ? trim($this->note) : null,
        );

        $this->afterReview($message);
    }

    private function afterReview(string $message): void
    {
        $this->farmer->refresh();
        $this->note = '';

        session()->flash('status', $message);

        $this->dispatch('farmer-verification-updated');
    }
}
