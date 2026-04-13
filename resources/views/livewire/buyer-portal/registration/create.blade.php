<div class="mx-auto max-w-5xl space-y-6">
    <div class="rounded-[2rem] border border-emerald-200 bg-gradient-to-br from-emerald-950 via-emerald-900 to-lime-900 p-8 text-white shadow-xl">
        <div class="space-y-3">
            <span class="inline-flex rounded-full border border-white/15 bg-white/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.2em]">Buyer Self-Onboarding</span>
            <h1 class="font-serif text-4xl leading-tight">Create your buyer account and submit your sourcing profile.</h1>
            <p class="max-w-3xl text-sm leading-7 text-emerald-50/85">
                This creates your buyer login, captures your business profile, and sets your account up for the catalogue and future order workflows.
            </p>
        </div>
    </div>

    <form wire:submit="submit" class="space-y-6">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Account details</h2>
            <div class="mt-5 grid gap-5 md:grid-cols-2">
                <x-forms.input label="Full name" name="name" wire:model.live="name" />
                <x-forms.input label="Email" name="email" type="email" wire:model.live="email" />
                <x-forms.input label="Phone" name="phone" wire:model.live="phone" />
                <x-forms.input label="Password" name="password" type="password" wire:model.live="password" />
                <x-forms.input label="Confirm password" name="password_confirmation" type="password" wire:model.live="password_confirmation" />
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Buyer profile</h2>
            <div class="mt-5 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                <x-forms.input label="Company name" name="company_name" wire:model.live="company_name" />
                <x-forms.input label="Country" name="country" wire:model.live="country" />
                <x-forms.input label="Business type" name="business_type" wire:model.live="business_type" />
                <x-forms.input label="Registration number" name="company_registration_number" wire:model.live="company_registration_number" />
                <x-forms.input label="Contact person" name="contact_person_full_name" wire:model.live="contact_person_full_name" />
                <x-forms.input label="Annual import volume (USD range)" name="annual_import_volume_usd_range" wire:model.live="annual_import_volume_usd_range" />
                <x-forms.input label="Preferred payment method" name="preferred_payment_method" wire:model.live="preferred_payment_method" />
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Value chain interests</h2>
            <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($valueChains as $valueChain)
                    <label wire:key="buyer-registration-interest-{{ $valueChain->id }}" class="inline-flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                        <input type="checkbox" value="{{ $valueChain->id }}" wire:model.live="value_chain_interest_ids" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span>{{ $valueChain->name }}</span>
                    </label>
                @endforeach
            </div>
        </section>

        <div class="flex items-center justify-between gap-4">
            <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 transition hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">Already have an account? Sign in</a>
            <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700">
                Submit buyer registration
            </button>
        </div>
    </form>
</div>
