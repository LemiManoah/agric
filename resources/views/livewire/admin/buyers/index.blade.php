<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Buyer registry</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Manage buyer onboarding, verification, and value chain interests for marketplace readiness.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            @can('export', \App\Models\Buyer::class)
                <button type="button" wire:click="exportCsv" class="inline-flex items-center rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-900 dark:bg-emerald-900/40 dark:text-emerald-300 dark:hover:bg-emerald-900/60">
                    Export CSV
                </button>
            @endcan

            @can('create', \App\Models\Buyer::class)
                <a href="{{ route('admin.buyers.create') }}" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">
                    Create buyer
                </a>
            @endcan
        </div>
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-950">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">Search</span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Company, contact, email" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
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
                <span class="font-medium text-gray-700 dark:text-gray-300">Country</span>
                <select wire:model.live="country" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <option value="">All countries</option>
                    @foreach ($countries as $countryOption)
                        <option value="{{ $countryOption }}">{{ $countryOption }}</option>
                    @endforeach
                </select>
            </label>
            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">Business type</span>
                <select wire:model.live="businessType" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <option value="">All business types</option>
                    @foreach ($businessTypes as $businessTypeOption)
                        <option value="{{ $businessTypeOption }}">{{ $businessTypeOption }}</option>
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
                        <th class="px-5 py-4">Buyer</th>
                        <th class="px-5 py-4">Country</th>
                        <th class="px-5 py-4">Business type</th>
                        <th class="px-5 py-4">Interests</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    @forelse ($buyers as $buyer)
                        <tr class="align-top text-gray-700 dark:text-gray-200">
                            <td class="px-5 py-4">
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $buyer->company_name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $buyer->contact_person_full_name }} / {{ $buyer->email }}</div>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300">{{ $buyer->country }}</td>
                            <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300">{{ $buyer->business_type }}</td>
                            <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300">{{ $buyer->valueChainInterests->pluck('name')->implode(', ') ?: 'Pending' }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    {{ str($buyer->verification_status->value)->replace('_', ' ')->title() }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap items-center gap-3 text-xs font-medium">
                                    @can('view', $buyer)
                                        <a href="{{ route('admin.buyers.show', $buyer) }}" class="text-blue-600 transition hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">View</a>
                                    @endcan
                                    @can('update', $buyer)
                                        <a href="{{ route('admin.buyers.edit', $buyer) }}" class="text-emerald-600 transition hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300">Edit</a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                No buyers match the current filters yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">
            {{ $buyers->links() }}
        </div>
    </section>
</div>
