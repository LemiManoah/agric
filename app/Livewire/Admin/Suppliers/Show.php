<?php

namespace App\Livewire\Admin\Suppliers;

use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

#[Title('Supplier Details')]
class Show extends Component
{
    public Supplier $supplier;

    public function mount(Supplier $supplier): void
    {
        $this->authorize('view', $supplier);

        $this->supplier = $supplier;
        $this->loadSupplier();
    }

    #[On('supplier-status-updated')]
    public function refreshSupplier(): void
    {
        $this->loadSupplier();
    }

    public function render(): View
    {
        return view('livewire.admin.suppliers.show', [
            'activities' => Activity::query()
                ->where('subject_type', Supplier::class)
                ->where('subject_id', $this->supplier->id)
                ->with('causer')
                ->latest()
                ->limit(15)
                ->get(),
            'supplier' => $this->supplier,
        ])->layout('components.layouts.app');
    }

    private function loadSupplier(): void
    {
        $this->supplier->refresh()->load([
            'creator',
            'district.region',
            'farmer',
            'qualityGrades',
            'user',
            'valueChains',
            'verifiedBy',
        ]);
    }
}
