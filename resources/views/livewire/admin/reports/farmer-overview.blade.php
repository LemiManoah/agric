<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Farmer overview report</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Monitor farmer registration progress, verification coverage, and regional distribution for M1.
            </p>
        </div>

        @can('farmers.export')
            <button
                type="button"
                wire:click="exportCsv"
                class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700"
            >
                Export CSV
            </button>
        @endcan
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-950">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">Region</span>
                <select wire:model.live="regionId" @disabled(auth()->user()?->isRegionalAdmin()) class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200 disabled:cursor-not-allowed disabled:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-emerald-400 dark:focus:ring-emerald-900 dark:disabled:bg-gray-800">
                    <option value="">All regions</option>
                    @foreach ($regions as $region)
                        <option value="{{ $region->id }}">{{ $region->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">District</span>
                <select wire:model.live="districtId" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-emerald-400 dark:focus:ring-emerald-900">
                    <option value="">All districts</option>
                    @foreach ($districts as $district)
                        <option value="{{ $district->id }}">{{ $district->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">Verification</span>
                <select wire:model.live="verificationStatus" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-emerald-400 dark:focus:ring-emerald-900">
                    <option value="">All statuses</option>
                    <option value="submitted">Submitted</option>
                    <option value="pending_review">Pending review</option>
                    <option value="verified">Verified</option>
                    <option value="suspended">Suspended</option>
                    <option value="rejected">Rejected</option>
                </select>
            </label>
            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">Registration source</span>
                <select wire:model.live="registrationSource" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-emerald-400 dark:focus:ring-emerald-900">
                    <option value="">All sources</option>
                    <option value="self_registered">Self registered</option>
                    <option value="field_officer">Field officer</option>
                    <option value="imported">Imported</option>
                </select>
            </label>
            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">Value chain</span>
                <select wire:model.live="valueChainId" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-emerald-400 dark:focus:ring-emerald-900">
                    <option value="">All value chains</option>
                    @foreach ($valueChains as $valueChain)
                        <option value="{{ $valueChain->id }}">{{ $valueChain->name }}</option>
                    @endforeach
                </select>
            </label>
        </div>
    </section>

    <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total farmers</div>
            <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($summary['total_farmers']) }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Verified farmers</div>
            <div class="mt-2 text-3xl font-semibold text-emerald-700 dark:text-emerald-300">{{ number_format($summary['verified_farmers']) }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending farmers</div>
            <div class="mt-2 text-3xl font-semibold text-amber-700 dark:text-amber-300">{{ number_format($summary['pending_farmers']) }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Regions represented</div>
            <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ count($summary['registrations_by_region']) }}</div>
        </div>
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Registrations by region</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Region-limited results respect the signed-in user's allowed scope.
            </p>
        </div>

        <div class="mt-5 space-y-4">
            @forelse ($summary['registrations_by_region'] as $regionName => $total)
                <div class="flex items-center justify-between gap-4 rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $regionName }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-300">{{ number_format($total) }} farmer{{ $total === 1 ? '' : 's' }}</div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    No farmer registrations match the current report filters.
                </div>
            @endforelse
        </div>
    </section>
</div>
