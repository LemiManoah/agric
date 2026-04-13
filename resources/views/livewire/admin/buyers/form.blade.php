<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $buyer ? 'Edit buyer' : 'Create buyer' }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Capture buyer profile details and the value chains they are actively sourcing.
            </p>
        </div>

        <a href="{{ route('admin.buyers.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-900">
            Cancel
        </a>
    </div>

    <form wire:submit="save" class="space-y-6">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                <x-forms.input label="Company name" name="company_name" wire:model.live="company_name" />
                <x-forms.input label="Country" name="country" wire:model.live="country" />
                <x-forms.input label="Business type" name="business_type" wire:model.live="business_type" />
                <x-forms.input label="Registration number" name="company_registration_number" wire:model.live="company_registration_number" />
                <x-forms.input label="Contact person" name="contact_person_full_name" wire:model.live="contact_person_full_name" />
                <x-forms.input label="Phone" name="phone" wire:model.live="phone" />
                <x-forms.input label="Email" name="email" type="email" wire:model.live="email" />
                <x-forms.input label="Annual import volume (USD range)" name="annual_import_volume_usd_range" wire:model.live="annual_import_volume_usd_range" />
                <x-forms.input label="Preferred payment method" name="preferred_payment_method" wire:model.live="preferred_payment_method" />
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Value chain interests</h2>
            <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($valueChains as $valueChain)
                    <label wire:key="buyer-interest-{{ $valueChain->id }}" class="inline-flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                        <input type="checkbox" value="{{ $valueChain->id }}" wire:model.live="value_chain_interest_ids" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span>{{ $valueChain->name }}</span>
                    </label>
                @endforeach
            </div>
        </section>

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700">
                Save buyer
            </button>
        </div>
    </form>
</div>
