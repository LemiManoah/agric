<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Supplier registry</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Review supplier profiling records, verification states, and warehouse linkage across the current M1 rollout.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            @can('export', \App\Models\Supplier::class)
                <button type="button" wire:click="exportCsv" class="inline-flex items-center rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-900 dark:bg-emerald-900/40 dark:text-emerald-300 dark:hover:bg-emerald-900/60">
                    Export CSV
                </button>
            @endcan

            @can('create', \App\Models\Supplier::class)
                <a href="{{ route('admin.suppliers.create') }}" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">
                    Create supplier
                </a>
            @endcan
        </div>
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-950">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">Search</span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Business, contact, phone" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            </label>
            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">Verification</span>
                <select wire:model.live="verificationStatus" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <option value="">All statuses</option>
                    <option value="submitted">Submitted</option>
                    <option value="verified">Verified</option>
                    <option value="suspended">Suspended</option>
                </select>
            </label>
            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">Warehouse linked</span>
                <select wire:model.live="warehouseLinked" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <option value="">All</option>
                    <option value="1">Linked</option>
                    <option value="0">Not linked</option>
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
            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">Value chain</span>
                <select wire:model.live="valueChainId" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <option value="">All value chains</option>
                    @foreach ($valueChains as $valueChain)
                        <option value="{{ $valueChain->id }}">{{ $valueChain->name }}</option>
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
                        <th class="px-5 py-4">Supplier</th>
                        <th class="px-5 py-4">District</th>
                        <th class="px-5 py-4">Value chains</th>
                        <th class="px-5 py-4">Verification</th>
                        <th class="px-5 py-4">Warehouse</th>
                        <th class="px-5 py-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    @forelse ($suppliers as $supplier)
                        <tr class="align-top text-gray-700 dark:text-gray-200">
                            <td class="px-5 py-4">
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $supplier->business_name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $supplier->contact_person }} / {{ $supplier->phone }}</div>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-500 dark:text-gray-400">
                                {{ $supplier->district?->name ?? $supplier->farmer?->location?->district?->name ?? 'Pending' }}
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300">
                                {{ $supplier->valueChains->pluck('name')->implode(', ') ?: 'Pending' }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    {{ str($supplier->verification_status->value)->replace('_', ' ')->title() }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300">
                                {{ $supplier->warehouse_linked ? 'Linked' : 'Not linked' }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap items-center gap-3 text-xs font-medium">
                                    @can('view', $supplier)
                                        <a href="{{ route('admin.suppliers.show', $supplier) }}" class="text-blue-600 transition hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">View</a>
                                    @endcan
                                    @can('update', $supplier)
                                        <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="text-emerald-600 transition hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300">Edit</a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                No suppliers match the current filters yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">
            {{ $suppliers->links() }}
        </div>
    </section>
</div>
