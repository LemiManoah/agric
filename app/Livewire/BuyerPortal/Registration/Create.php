<?php

namespace App\Livewire\BuyerPortal\Registration;

use App\Models\User;
use App\Models\ValueChain;
use App\Services\BuyerService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Buyer Registration')]
class Create extends Component
{
    protected BuyerService $buyerService;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $company_name = '';

    public string $country = '';

    public string $business_type = '';

    public string $company_registration_number = '';

    public string $contact_person_full_name = '';

    public string $annual_import_volume_usd_range = '';

    public string $preferred_payment_method = '';

    public array $value_chain_interest_ids = [];

    public function boot(BuyerService $buyerService): void
    {
        $this->buyerService = $buyerService;
    }

    public function submit()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'company_name' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:120'],
            'business_type' => ['required', 'string', 'max:120'],
            'company_registration_number' => ['nullable', 'string', 'max:255'],
            'contact_person_full_name' => ['required', 'string', 'max:255'],
            'annual_import_volume_usd_range' => ['nullable', 'string', 'max:255'],
            'preferred_payment_method' => ['nullable', 'string', 'max:255'],
            'value_chain_interest_ids' => ['array'],
            'value_chain_interest_ids.*' => ['exists:value_chains,id'],
        ]);

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);
        $user->assignRole('buyer');

        $this->buyerService->createBuyer([
            'user_id' => $user->id,
            'company_name' => $validated['company_name'],
            'country' => $validated['country'],
            'business_type' => $validated['business_type'],
            'company_registration_number' => $validated['company_registration_number'],
            'contact_person_full_name' => $validated['contact_person_full_name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'annual_import_volume_usd_range' => $validated['annual_import_volume_usd_range'],
            'preferred_payment_method' => $validated['preferred_payment_method'],
            'value_chain_interest_ids' => $validated['value_chain_interest_ids'],
        ]);

        Auth::login($user);

        session()->flash('status', 'Buyer registration submitted successfully.');

        return redirect()->route('buyer-portal.profile');
    }

    public function render(): View
    {
        return view('livewire.buyer-portal.registration.create', [
            'valueChains' => ValueChain::query()->where('is_active', true)->orderBy('name')->get(),
        ])->layout('components.layouts.auth');
    }
}
