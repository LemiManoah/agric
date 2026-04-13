<?php

namespace App\Livewire\Admin\Farmers;

use App\Models\Farmer;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

#[Title('Farmer Details')]
class Show extends Component
{
    use AuthorizesRequests;

    public Farmer $farmer;

    public function mount(Farmer $farmer): void
    {
        $this->authorize('view', $farmer);

        $this->farmer = $farmer;
        $this->loadFarmer();
    }

    #[On('farmer-verification-updated')]
    public function refreshFarmer(): void
    {
        $this->loadFarmer();
    }

    public function render(): View
    {
        return view('livewire.admin.farmers.show', [
            'activities' => Activity::query()
                ->where('subject_type', Farmer::class)
                ->where('subject_id', $this->farmer->id)
                ->with('causer')
                ->latest()
                ->limit(15)
                ->get(),
        ])->layout('components.layouts.app');
    }

    private function loadFarmer(): void
    {
        $this->farmer->refresh()->load([
            'businessProfile',
            'location.region',
            'location.district',
            'location.subcounty',
            'location.parish',
            'location.village',
            'registeredBy',
            'valueChainEntries.valueChain',
            'verifiedBy',
        ]);
    }
}
