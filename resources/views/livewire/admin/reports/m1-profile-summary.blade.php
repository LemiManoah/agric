<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">M1 profile summary</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Compare supplier, agent, and agribusiness profile coverage across the M1 onboarding footprint.
            </p>
        </div>

        @can('exports.create')
            <button type="button" wire:click="exportCsv" class="inline-flex items-center rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-900 dark:bg-emerald-900/40 dark:text-emerald-300 dark:hover:bg-emerald-900/60">
                Export CSV
            </button>
        @endcan
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-950">
        <div class="grid gap-4 md:grid-cols-2">
            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">Region</span>
                <select wire:model.live="regionId" @disabled(auth()->user()?->isRegionalAdmin()) class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 disabled:cursor-not-allowed disabled:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:disabled:bg-gray-800">
                    <option value="">All regions</option>
                    @foreach ($regions as $region)
                        <option value="{{ $region->id }}">{{ $region->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">District</span>
                <select wire:model.live="districtId" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <option value="">All districts</option>
                    @foreach ($districts as $district)
                        <option value="{{ $district->id }}">{{ $district->name }}</option>
                    @endforeach
                </select>
            </label>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Total suppliers</div>
            <div class="mt-3 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($summary['total_suppliers']) }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Verified suppliers</div>
            <div class="mt-3 text-3xl font-semibold text-emerald-600 dark:text-emerald-400">{{ number_format($summary['verified_suppliers']) }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Warehouse linked</div>
            <div class="mt-3 text-3xl font-semibold text-blue-600 dark:text-blue-400">{{ number_format($summary['warehouse_linked_suppliers']) }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Total agents</div>
            <div class="mt-3 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($summary['total_agents']) }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Active agents</div>
            <div class="mt-3 text-3xl font-semibold text-amber-600 dark:text-amber-400">{{ number_format($summary['active_agents']) }}</div>
        </div>
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Agribusiness by entity type</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Distribution of agribusiness profiles under the selected scope.</p>
        </div>

        <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($summary['agribusiness_by_entity'] as $entityType => $count)
                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900">
                    <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ str($entityType)->replace('_', ' ')->title() }}</div>
                    <div class="mt-3 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($count) }}</div>
                </div>
            @endforeach
        </div>
    </section>
</div>
