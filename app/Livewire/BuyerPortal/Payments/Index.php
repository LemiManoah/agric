<?php

namespace App\Livewire\BuyerPortal\Payments;

use App\Models\Payment;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('My Payments')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'status', except: '')]
    public string $status = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('buyer'), 403);
        $this->authorize('viewAny', Payment::class);
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        return view('livewire.buyer-portal.payments.index', [
            'payments' => Payment::query()
                ->visibleTo(auth()->user())
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->with(['order', 'receipt'])
                ->latest()
                ->paginate(10),
        ])->layout('components.layouts.app');
    }
}
