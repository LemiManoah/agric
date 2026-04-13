<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $buyer->company_name }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Buyer profile, verification state, value chain interests, and activity history.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.buyers.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-900">
                Back to buyers
            </a>
            @can('update', $buyer)
                <a href="{{ route('admin.buyers.edit', $buyer) }}" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">
                    Edit buyer
                </a>
            @endcan
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.6fr)_minmax(320px,1fr)]">
        <div class="space-y-6">
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Country</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $buyer->country }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Business type</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $buyer->business_type }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Registration number</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $buyer->company_registration_number ?: 'Not captured' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Contact person</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $buyer->contact_person_full_name }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Phone</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $buyer->phone }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Email</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $buyer->email }}</div>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Value chain interests</h2>
                <div class="mt-4 flex flex-wrap gap-2">
                    @forelse ($buyer->valueChainInterests as $valueChain)
                        <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ $valueChain->name }}</span>
                    @empty
                        <span class="text-sm text-gray-500 dark:text-gray-400">No value chain interests have been captured yet.</span>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="space-y-6">
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <div class="grid gap-4">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Verification status</div>
                        <div class="mt-2">
                            <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                {{ str($buyer->verification_status->value)->replace('_', ' ')->title() }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Preferred payment method</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $buyer->preferred_payment_method ?: 'Pending' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Annual import volume</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $buyer->annual_import_volume_usd_range ?: 'Pending' }}</div>
                    </div>
                </div>

                @if (auth()->user()?->can('verify', $buyer))
                    <div class="mt-6 border-t border-gray-200 pt-6 dark:border-gray-800">
                        <livewire:admin.buyers.verification-action :buyer="$buyer" :key="'buyer-actions-'.$buyer->id" />
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
                        <div class="text-sm text-gray-500 dark:text-gray-400">No buyer activity has been logged yet.</div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</div>
