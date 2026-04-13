<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Agent registry</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Track agent onboarding, commission setup, service coverage, and active value chain assignments.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            @can('export', \App\Models\Agent::class)
                <button type="button" wire:click="exportCsv" class="inline-flex items-center rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-900 dark:bg-emerald-900/40 dark:text-emerald-300 dark:hover:bg-emerald-900/60">
                    Export CSV
                </button>
            @endcan

            @can('create', \App\Models\Agent::class)
                <a href="{{ route('admin.agents.create') }}" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">
                    Create agent
                </a>
            @endcan
        </div>
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-950">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">Search</span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Name, code, phone" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            </label>
            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">Status</span>
                <select wire:model.live="onboardingStatus" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}">{{ str($status->value)->replace('_', ' ')->title() }}</option>
                    @endforeach
                </select>
            </label>
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
                <span class="font-medium text-gray-700 dark:text-gray-300">Primary district</span>
                <select wire:model.live="primaryDistrictId" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
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
                        <th class="px-5 py-4">Agent</th>
                        <th class="px-5 py-4">Coverage</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">Commission</th>
                        <th class="px-5 py-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    @forelse ($agents as $agent)
                        <tr class="align-top text-gray-700 dark:text-gray-200">
                            <td class="px-5 py-4">
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $agent->full_name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $agent->agent_code }} / {{ $agent->phone }}</div>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-500 dark:text-gray-400">
                                <div>{{ $agent->primaryDistrict?->name ?? 'District pending' }}</div>
                                <div>{{ $agent->regions->pluck('name')->implode(', ') ?: 'No extra regions' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                                    {{ str($agent->onboarding_status->value)->replace('_', ' ')->title() }}
                                </span>
                                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $agent->valueChains->pluck('name')->implode(', ') ?: 'No value chains assigned' }}
                                </div>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300">
                                <div>{{ number_format((float) $agent->commission_rate, 2) }}%</div>
                                <div>{{ number_format($agent->total_orders_placed) }} orders</div>
                                <div>UGX {{ number_format((float) $agent->total_commission_earned, 2) }}</div>
                            </td>
                            <td class="px-5 py-4">
                                @can('update', $agent)
                                    <a href="{{ route('admin.agents.edit', $agent) }}" class="text-emerald-600 transition hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300">
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
                                No agents match the current filters yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">
            {{ $agents->links() }}
        </div>
    </section>
</div>
