<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div class="flex flex-col gap-2">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Farmer registry</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Review registered farmers, apply regional filters, and continue the M1 registration workflow.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            @can('viewMap', \App\Models\Farmer::class)
                <a
                    href="{{ route('admin.farmers.map') }}"
                    class="inline-flex items-center rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-900 dark:bg-emerald-900/40 dark:text-emerald-300 dark:hover:bg-emerald-900/60"
                >
                    Farm map
                </a>
            @endcan

            @can('create', \App\Models\Farmer::class)
                <a
                    href="{{ auth()->user()?->hasRole('field_officer') ? route('field-officer.farmers.create') : route('farmer-portal.registration.create') }}"
                    class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700"
                >
                    Register farmer
                </a>
            @endcan
        </div>
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-950">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">Search</span>
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Name or phone"
                    class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-emerald-400 dark:focus:ring-emerald-900"
                >
            </label>

            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">Verification</span>
                <select
                    wire:model.live="verificationStatus"
                    class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-emerald-400 dark:focus:ring-emerald-900"
                >
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
                <select
                    wire:model.live="registrationSource"
                    class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-emerald-400 dark:focus:ring-emerald-900"
                >
                    <option value="">All sources</option>
                    <option value="self_registered">Self registered</option>
                    <option value="field_officer">Field officer</option>
                    <option value="imported">Imported</option>
                </select>
            </label>

            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">Region</span>
                <select
                    wire:model.live="regionId"
                    @disabled(auth()->user()?->isRegionalAdmin())
                    class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200 disabled:cursor-not-allowed disabled:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-emerald-400 dark:focus:ring-emerald-900 dark:disabled:bg-gray-800"
                >
                    <option value="">All regions</option>
                    @foreach ($regions as $region)
                        <option value="{{ $region->id }}">{{ $region->name }}</option>
                    @endforeach
                </select>
            </label>

            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">District</span>
                <select
                    wire:model.live="districtId"
                    class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:border-emerald-400 dark:focus:ring-emerald-900"
                >
                    <option value="">All districts</option>
                    @foreach ($districts as $district)
                        <option value="{{ $district->id }}">{{ $district->name }}</option>
                    @endforeach
                </select>
            </label>
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-950">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-900/60">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <th class="px-5 py-4">Farmer</th>
                        <th class="px-5 py-4">Location</th>
                        <th class="px-5 py-4">Registration</th>
                        <th class="px-5 py-4">Verification</th>
                        <th class="px-5 py-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    @forelse ($farmers as $farmer)
                        <tr class="align-top text-gray-700 dark:text-gray-200">
                            <td class="px-5 py-4">
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $farmer->full_name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $farmer->phone }}</div>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-500 dark:text-gray-400">
                                <div>{{ $farmer->location?->district?->name ?? 'District pending' }}</div>
                                <div>{{ $farmer->location?->region?->name ?? 'Region pending' }}</div>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300">
                                {{ str($farmer->registration_source->value)->replace('_', ' ')->title() }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    {{ str($farmer->verification_status->value)->replace('_', ' ')->title() }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap items-center gap-3 text-xs font-medium">
                                    @can('view', $farmer)
                                        <a href="{{ route('admin.farmers.show', $farmer) }}" class="text-blue-600 transition hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                            View
                                        </a>
                                    @endcan
                                    @can('update', $farmer)
                                        <a href="{{ route('admin.farmers.edit', $farmer) }}" class="text-emerald-600 transition hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300">
                                            Edit
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                No farmers match the current filters yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">
            {{ $farmers->links() }}
        </div>
    </section>
</div>
