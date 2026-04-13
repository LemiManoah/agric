<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $this->agent ? 'Edit agent' : 'Create agent' }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Capture agent identity, commission settings, service regions, and active value chains for the M1 rollout.
            </p>
        </div>

        <a href="{{ route('admin.agents.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-900">
            Cancel
        </a>
    </div>

    <form wire:submit="save" class="space-y-6">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <x-forms.input label="Full name" name="full_name" wire:model.live="full_name" />
                </div>
                <div>
                    <x-forms.input label="Phone" name="phone" wire:model.live="phone" />
                </div>
                <div>
                    <x-forms.input label="Email" name="email" type="email" wire:model.live="email" />
                </div>
                <div class="space-y-1">
                    <label for="primary_district_id" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Primary district</label>
                    <select id="primary_district_id" wire:model.live="primary_district_id" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        <option value="">Select district</option>
                        @foreach ($districts as $district)
                            <option value="{{ $district->id }}">{{ $district->name }}</option>
                        @endforeach
                    </select>
                    @error('primary_district_id')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <x-forms.input label="Commission rate (%)" name="commission_rate" type="number" min="0" max="100" step="0.01" wire:model.live="commission_rate" />
                </div>
                <div class="space-y-1">
                    <label for="onboarding_status" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Onboarding status</label>
                    <select id="onboarding_status" wire:model.live="onboarding_status" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}">{{ str($status->value)->replace('_', ' ')->title() }}</option>
                        @endforeach
                    </select>
                    @error('onboarding_status')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            @if ($this->agent)
                <div class="mt-6 grid gap-4 rounded-2xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 md:grid-cols-3">
                    <div>
                        <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Agent code</div>
                        <div class="mt-1 font-medium text-gray-900 dark:text-gray-100">{{ $this->agent->agent_code }}</div>
                    </div>
                    <div>
                        <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Total orders placed</div>
                        <div class="mt-1 font-medium text-gray-900 dark:text-gray-100">{{ number_format($this->agent->total_orders_placed) }}</div>
                    </div>
                    <div>
                        <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Total commission earned</div>
                        <div class="mt-1 font-medium text-gray-900 dark:text-gray-100">UGX {{ number_format((float) $this->agent->total_commission_earned, 2) }}</div>
                    </div>
                </div>
            @endif
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Service areas</h2>
            <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($regions as $region)
                    <label wire:key="agent-region-{{ $region->id }}" class="inline-flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                        <input type="checkbox" value="{{ $region->id }}" wire:model.live="region_ids" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span>{{ $region->name }}</span>
                    </label>
                @endforeach
            </div>
            @error('region_ids.*')
                <span class="mt-2 block text-red-500">{{ $message }}</span>
            @enderror
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Value chains</h2>
            <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($valueChains as $valueChain)
                    <label wire:key="agent-value-chain-{{ $valueChain->id }}" class="inline-flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                        <input type="checkbox" value="{{ $valueChain->id }}" wire:model.live="value_chain_ids" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span>{{ $valueChain->name }}</span>
                    </label>
                @endforeach
            </div>
            @error('value_chain_ids.*')
                <span class="mt-2 block text-red-500">{{ $message }}</span>
            @enderror
        </section>

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700">
                Save agent
            </button>
        </div>
    </form>
</div>
