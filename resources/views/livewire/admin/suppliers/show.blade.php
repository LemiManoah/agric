<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $supplier->business_name }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Supplier profile, linkage details, verification state, and activity history.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.suppliers.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-900">
                Back to suppliers
            </a>
            @can('update', $supplier)
                <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">
                    Edit supplier
                </a>
            @endcan
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.6fr)_minmax(320px,1fr)]">
        <div class="space-y-6">
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Contact person</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $supplier->contact_person }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Phone</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $supplier->phone }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Email</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $supplier->email ?: 'Not captured' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Operating district</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $supplier->district?->name ?? 'Pending' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Supply frequency</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $supplier->supply_frequency ? str($supplier->supply_frequency->value)->title() : 'Pending' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Monthly supply volume</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $supplier->typical_supply_volume_kg_per_month ? number_format((float) $supplier->typical_supply_volume_kg_per_month, 2).' kg' : 'Pending' }}</div>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Linked farmer</h2>
                <div class="mt-4 text-sm text-gray-700 dark:text-gray-300">
                    {{ $supplier->farmer?->full_name ?? 'No farmer linked' }}
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-2">
                <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Value chains</h2>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @forelse ($supplier->valueChains as $valueChain)
                            <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ $valueChain->name }}</span>
                        @empty
                            <span class="text-sm text-gray-500 dark:text-gray-400">No value chains linked yet.</span>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Quality grades</h2>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @forelse ($supplier->qualityGrades as $qualityGrade)
                            <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">{{ $qualityGrade->name }}</span>
                        @empty
                            <span class="text-sm text-gray-500 dark:text-gray-400">No quality grades linked yet.</span>
                        @endforelse
                    </div>
                </section>
            </section>
        </div>

        <div class="space-y-6">
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <div class="grid gap-4">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Verification status</div>
                        <div class="mt-2">
                            <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                {{ str($supplier->verification_status->value)->replace('_', ' ')->title() }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Warehouse linked</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $supplier->warehouse_linked ? 'Yes' : 'No' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Verified by</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $supplier->verifiedBy?->name ?? 'Not yet verified' }}</div>
                    </div>
                </div>

                @if (auth()->user()?->can('verify', $supplier) || auth()->user()?->can('toggleWarehouseLinked', $supplier))
                    <div class="mt-6 border-t border-gray-200 pt-6 dark:border-gray-800">
                        <livewire:admin.suppliers.verification-action :supplier="$supplier" :key="'supplier-actions-'.$supplier->id" />
                    </div>
                @endif
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Activity history</h2>
                <div class="mt-5 space-y-4">
                    @forelse ($activities as $activity)
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900">
                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ str($activity->description)->replace('.', ' ')->replace('_', ' ')->title() }}</div>
                            <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $activity->causer?->name ?? 'System' }} / {{ $activity->created_at?->diffForHumans() }}</div>
                        </div>
                    @empty
                        <div class="text-sm text-gray-500 dark:text-gray-400">No supplier activity has been logged yet.</div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</div>
