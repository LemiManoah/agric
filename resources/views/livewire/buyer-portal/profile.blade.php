<div class="space-y-6">
    <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Buyer portal</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Keep your buyer profile current while the rest of the buyer portal grows into orders, enquiries, and receipts in later phases.
            </p>
        </div>
    </div>

    <form wire:submit="save" class="space-y-6">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                <div class="space-y-1">
                    <x-forms.input label="Company name" name="company_name" wire:model.live="company_name" />
                </div>
                <div class="space-y-1">
                    <x-forms.input label="Country" name="country" wire:model.live="country" />
                </div>
                <div class="space-y-1">
                    <x-forms.input label="Business type" name="business_type" wire:model.live="business_type" />
                </div>
                <div class="space-y-1">
                    <x-forms.input label="Registration number" name="company_registration_number" wire:model.live="company_registration_number" />
                </div>
                <div class="space-y-1">
                    <x-forms.input label="Contact person" name="contact_person_full_name" wire:model.live="contact_person_full_name" />
                </div>
                <div class="space-y-1">
                    <x-forms.input label="Phone" name="phone" wire:model.live="phone" />
                </div>
                <div class="space-y-1">
                    <x-forms.input label="Email" name="email" type="email" wire:model.live="email" />
                </div>
                <div class="space-y-1">
                    <x-forms.input label="Annual import volume (USD range)" name="annual_import_volume_usd_range" wire:model.live="annual_import_volume_usd_range" />
                </div>
                <div class="space-y-1">
                    <x-forms.input label="Preferred payment method" name="preferred_payment_method" wire:model.live="preferred_payment_method" />
                </div>
            </div>

            <div class="mt-6 rounded-2xl border border-gray-200 bg-gray-50 p-4 text-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Verification status</div>
                <div class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ str($buyer->verification_status->value)->replace('_', ' ')->title() }}</div>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Value chain interests</h2>
            <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($valueChains as $valueChain)
                    <label wire:key="buyer-portal-interest-{{ $valueChain->id }}" class="inline-flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                        <input type="checkbox" value="{{ $valueChain->id }}" wire:model.live="value_chain_interest_ids" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span>{{ $valueChain->name }}</span>
                    </label>
                @endforeach
            </div>
        </section>

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700">
                Save profile
            </button>
        </div>
    </form>
</div>
