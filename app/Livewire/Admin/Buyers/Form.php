<?php

namespace App\Livewire\Admin\Buyers;

use App\Models\Buyer;
use App\Models\ValueChain;
use App\Services\BuyerService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Buyer Form')]
class Form extends Component
{
    protected BuyerService $buyerService;

    public ?Buyer $buyer = null;

    public string $company_name = '';

    public string $country = '';

    public string $business_type = '';

    public string $company_registration_number = '';

    public string $contact_person_full_name = '';

    public string $phone = '';

    public string $email = '';

    public string $annual_import_volume_usd_range = '';

    public string $preferred_payment_method = '';

    public array $value_chain_interest_ids = [];

    public function boot(BuyerService $buyerService): void
    {
        $this->buyerService = $buyerService;
    }

    public function mount(?Buyer $buyer = null): void
    {
        $this->buyer = $buyer?->exists ? $buyer->load('valueChainInterests') : null;

        if ($this->buyer) {
            $this->authorize('update', $this->buyer);

            $this->company_name = $this->buyer->company_name;
            $this->country = $this->buyer->country;
            $this->business_type = $this->buyer->business_type;
            $this->company_registration_number = $this->buyer->company_registration_number ?? '';
            $this->contact_person_full_name = $this->buyer->contact_person_full_name;
            $this->phone = $this->buyer->phone;
            $this->email = $this->buyer->email;
            $this->annual_import_volume_usd_range = $this->buyer->annual_import_volume_usd_range ?? '';
            $this->preferred_payment_method = $this->buyer->preferred_payment_method ?? '';
            $this->value_chain_interest_ids = $this->buyer->valueChainInterests->pluck('id')->map(fn ($id) => (int) $id)->all();
        } else {
            $this->authorize('create', Buyer::class);
        }
    }

    public function save()
    {
        $validated = $this->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:120'],
            'business_type' => ['required', 'string', 'max:120'],
            'company_registration_number' => ['nullable', 'string', 'max:255'],
            'contact_person_full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255'],
            'annual_import_volume_usd_range' => ['nullable', 'string', 'max:255'],
            'preferred_payment_method' => ['nullable', 'string', 'max:255'],
            'value_chain_interest_ids' => ['array'],
            'value_chain_interest_ids.*' => ['exists:value_chains,id'],
        ]);

        $buyer = $this->buyer
            ? $this->buyerService->updateBuyer($this->buyer, $validated, auth()->user())
            : $this->buyerService->createBuyer($validated, auth()->user());

        session()->flash('status', 'Buyer profile saved successfully.');

        return redirect()->route('admin.buyers.show', $buyer);
    }

    public function render(): View
    {
        return view('livewire.admin.buyers.form', [
            'valueChains' => ValueChain::query()->where('is_active', true)->orderBy('name')->get(),
        ])->layout('components.layouts.app');
    }
}
