<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Agribusiness profiles</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Manage cooperative, dealer, processor, and service-provider profiles linked to the M1 ecosystem.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            @can('export', \App\Models\AgribusinessProfile::class)
                <button type="button" wire:click="exportCsv" class="inline-flex items-center rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-900 dark:bg-emerald-900/40 dark:text-emerald-300 dark:hover:bg-emerald-900/60">
                    Export CSV
                </button>
            @endcan

            @can('create', \App\Models\AgribusinessProfile::class)
                <a href="{{ route('admin.agribusiness-profiles.create') }}" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">
                    Create profile
                </a>
            @endcan
        </div>
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-950">
        <div class="grid gap-4 md:grid-cols-3">
            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">Search</span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Organization, contact, phone" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            </label>
            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">Entity type</span>
                <select wire:model.live="entityType" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <option value="">All entity types</option>
                    @foreach ($entityTypes as $entityType)
                        <option value="{{ $entityType->value }}">{{ str($entityType->value)->replace('_', ' ')->title() }}</option>
                    @endforeach
                </select>
            </label>
            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">Covered district</span>
                <select wire:model.live="districtId" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
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
                        <th class="px-5 py-4">Organization</th>
                        <th class="px-5 py-4">Entity type</th>
                        <th class="px-5 py-4">Coverage</th>
                        <th class="px-5 py-4">Contact</th>
                        <th class="px-5 py-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    @forelse ($profiles as $profile)
                        <tr class="align-top text-gray-700 dark:text-gray-200">
                            <td class="px-5 py-4">
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $profile->organization_name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $profile->registration_number ?: 'Registration pending' }}</div>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300">
                                {{ str($profile->entity_type->value)->replace('_', ' ')->title() }}
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-500 dark:text-gray-400">
                                {{ $profile->districts->pluck('name')->implode(', ') ?: 'No districts assigned' }}
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300">
                                <div>{{ $profile->contact_person }}</div>
                                <div>{{ $profile->contact_phone }}</div>
                            </td>
                            <td class="px-5 py-4">
                                @can('update', $profile)
                                    <a href="{{ route('admin.agribusiness-profiles.edit', $profile) }}" class="text-emerald-600 transition hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300">
                                        Edit
                                    </a>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-gray-500">View only</span>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                No agribusiness profiles match the current filters yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">
            {{ $profiles->links() }}
        </div>
    </section>
</div>
