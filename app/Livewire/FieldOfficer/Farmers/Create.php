<?php

namespace App\Livewire\FieldOfficer\Farmers;

use App\Models\Farmer;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Field Officer Farmer Registration')]
class Create extends Component
{
    use AuthorizesRequests;

    public function mount(): void
    {
        $user = auth()->user();

        abort_unless($user?->hasRole('field_officer'), 403);
        $this->authorize('create', Farmer::class);
    }

    public function render(): View
    {
        return view('livewire.field-officer.farmers.create')
            ->layout('components.layouts.app');
    }
}
