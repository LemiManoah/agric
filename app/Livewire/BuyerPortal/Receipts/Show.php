<?php

namespace App\Livewire\BuyerPortal\Receipts;

use App\Models\Receipt;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Receipt')]
class Show extends Component
{
    public Receipt $receipt;

    public function mount(Receipt $receipt): void
    {
        abort_unless(auth()->user()?->hasRole('buyer'), 403);

        $receipt->load('order.buyer', 'payment');

        abort_unless((int) $receipt->order?->buyer?->user_id === (int) auth()->id(), 403);

        $this->receipt = $receipt;
    }

    public function download()
    {
        abort_unless((int) $this->receipt->order?->buyer?->user_id === (int) auth()->id(), 403);

        return Storage::disk($this->receipt->diskName())->download(
            $this->receipt->file_path,
            basename($this->receipt->file_path),
        );
    }

    public function render(): View
    {
        return view('livewire.buyer-portal.receipts.show')
            ->layout('components.layouts.app');
    }
}
