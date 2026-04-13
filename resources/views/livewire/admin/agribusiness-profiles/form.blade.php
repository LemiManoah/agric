<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $this->profile ? 'Edit agribusiness profile' : 'Create agribusiness profile' }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Register cooperatives, processors, dealers, and service operators with district-level coverage for M1.
            </p>
        </div>

        <a href="{{ route('admin.agribusiness-profiles.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-900">
            Cancel
        </a>
    </div>

    <form wire:submit="save" class="space-y-6">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                <div class="space-y-1">
                    <label for="entity_type" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Entity type</label>
                    <select id="entity_type" wire:model.live="entity_type" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        @foreach ($entityTypes as $entityType)
                            <option value="{{ $entityType->value }}">{{ str($entityType->value)->replace('_', ' ')->title() }}</option>
                        @endforeach
                    </select>
                    @error('entity_type')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <x-forms.input label="Organization name" name="organization_name" wire:model.live="organization_name" />
                </div>
                <div>
                    <x-forms.input label="Registration number" name="registration_number" wire:model.live="registration_number" />
                </div>
                <div>
                    <x-forms.input label="Membership size" name="membership_size" type="number" min="0" wire:model.live="membership_size" />
                </div>
                <div>
                    <x-forms.input label="Fleet size" name="fleet_size" type="number" min="0" wire:model.live="fleet_size" />
                </div>
                <div>
                    <x-forms.input label="Processing capacity (tonnes/day)" name="processing_capacity_tonnes_per_day" type="number" min="0" step="0.01" wire:model.live="processing_capacity_tonnes_per_day" />
                </div>
                <div>
                    <x-forms.input label="Contact person" name="contact_person" wire:model.live="contact_person" />
                </div>
                <div>
                    <x-forms.input label="Contact phone" name="contact_phone" wire:model.live="contact_phone" />
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="grid gap-5 md:grid-cols-2">
                <div class="space-y-1">
                    <label for="service_rates" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Service rates</label>
                    <textarea id="service_rates" wire:model.live="service_rates" rows="4" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"></textarea>
                    @error('service_rates')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror
                </div>
                <div class="space-y-1">
                    <label for="product_range" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Product range</label>
                    <textarea id="product_range" wire:model.live="product_range" rows="4" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"></textarea>
                    @error('product_range')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror
                </div>
                <div class="space-y-1">
                    <label for="export_markets" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Export markets</label>
                    <textarea id="export_markets" wire:model.live="export_markets" rows="4" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"></textarea>
                    @error('export_markets')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror
                </div>
                <div class="space-y-1">
                    <label for="buyer_criteria" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Buyer criteria</label>
                    <textarea id="buyer_criteria" wire:model.live="buyer_criteria" rows="4" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"></textarea>
                    @error('buyer_criteria')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Covered districts</h2>
            <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($districts as $district)
                    <label wire:key="agribusiness-district-{{ $district->id }}" class="inline-flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                        <input type="checkbox" value="{{ $district->id }}" wire:model.live="district_ids" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span>{{ $district->name }}</span>
                    </label>
                @endforeach
            </div>
            @error('district_ids')
                <span class="mt-2 block text-red-500">{{ $message }}</span>
            @enderror
            @error('district_ids.*')
                <span class="mt-2 block text-red-500">{{ $message }}</span>
            @enderror
        </section>

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700">
                Save profile
            </button>
        </div>
    </form>
</div>
