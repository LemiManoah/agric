<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $this->supplier ? 'Edit supplier' : 'Create supplier' }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Capture supplier profiling, optional farmer linkage, value chain participation, and quality grades for M1.
            </p>
        </div>

        <a href="{{ route('admin.suppliers.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-900">
            Back to suppliers
        </a>
    </div>

    <form wire:submit="save" class="space-y-6">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <x-forms.input label="Business name" name="business_name" wire:model.live="business_name" />
                </div>
                <div>
                    <x-forms.input label="Contact person" name="contact_person" wire:model.live="contact_person" />
                </div>
                <div>
                    <x-forms.input label="Phone" name="phone" wire:model.live="phone" />
                </div>
                <div>
                    <x-forms.input label="Email" name="email" type="email" wire:model.live="email" />
                </div>
                <div class="space-y-1">
                    <label for="farmer_id" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Linked farmer</label>
                    <select id="farmer_id" wire:model.live="farmer_id" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        <option value="">No linked farmer</option>
                        @foreach ($farmers as $farmer)
                            <option value="{{ $farmer->id }}">{{ $farmer->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1">
                    <label for="operating_district_id" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Operating district</label>
                    <select id="operating_district_id" wire:model.live="operating_district_id" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        <option value="">Select district</option>
                        @foreach ($districts as $district)
                            <option value="{{ $district->id }}">{{ $district->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-forms.input label="Typical monthly supply volume (kg)" name="typical_supply_volume_kg_per_month" type="number" step="0.01" min="0" wire:model.live="typical_supply_volume_kg_per_month" />
                </div>
                <div class="space-y-1">
                    <label for="supply_frequency" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Supply frequency</label>
                    <select id="supply_frequency" wire:model.live="supply_frequency" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        <option value="">Select frequency</option>
                        @foreach ($supplyFrequencies as $frequency)
                            <option value="{{ $frequency->value }}">{{ str($frequency->value)->title() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Value chains</h2>
                <div class="mt-5 grid gap-3 md:grid-cols-2">
                    @foreach ($valueChains as $valueChain)
                        <label wire:key="supplier-value-chain-{{ $valueChain->id }}" class="inline-flex items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 text-sm text-gray-700 dark:border-gray-800 dark:text-gray-300">
                            <input type="checkbox" wire:model.live="value_chain_ids" value="{{ $valueChain->id }}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            {{ $valueChain->name }}
                        </label>
                    @endforeach
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Quality grades</h2>
                <div class="mt-5 grid gap-3 md:grid-cols-2">
                    @foreach ($qualityGrades as $qualityGrade)
                        <label wire:key="supplier-quality-grade-{{ $qualityGrade->id }}" class="inline-flex items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 text-sm text-gray-700 dark:border-gray-800 dark:text-gray-300">
                            <input type="checkbox" wire:model.live="quality_grade_ids" value="{{ $qualityGrade->id }}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            {{ $qualityGrade->name }}
                        </label>
                    @endforeach
                </div>
            </section>
        </section>

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700">
                Save supplier
            </button>
        </div>
    </form>
</div>
