<?php

namespace App\Livewire\Admin\Buyers;

use App\Models\Buyer;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

#[Title('Buyer Details')]
class Show extends Component
{
    public Buyer $buyer;

    public function mount(Buyer $buyer): void
    {
        $this->authorize('view', $buyer);

        $this->buyer = $buyer;
        $this->loadBuyer();
    }

    #[On('buyer-status-updated')]
    public function refreshBuyer(): void
    {
        $this->loadBuyer();
    }

    public function render(): View
    {
        return view('livewire.admin.buyers.show', [
            'activities' => Activity::query()
                ->where('subject_type', Buyer::class)
                ->where('subject_id', $this->buyer->id)
                ->with('causer')
                ->latest()
                ->limit(15)
                ->get(),
        ])->layout('components.layouts.app');
    }

    private function loadBuyer(): void
    {
        $this->buyer->refresh()->load([
            'creator',
            'user',
            'valueChainInterests',
            'verifiedBy',
        ]);
    }
}
