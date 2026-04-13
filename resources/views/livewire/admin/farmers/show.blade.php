<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $farmer->full_name }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Full farmer profile, verification state, mapped location, and lifecycle activity.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a
                href="{{ route('admin.farmers.index') }}"
                class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-900"
            >
                Back to registry
            </a>

            @can('update', $farmer)
                <a
                    href="{{ route('admin.farmers.edit', $farmer) }}"
                    class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700"
                >
                    Edit farmer
                </a>
            @endcan
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.6fr)_minmax(320px,1fr)]">
        <div class="space-y-6">
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <div class="grid gap-6 md:grid-cols-[140px_minmax(0,1fr)]">
                    <div class="flex items-start justify-center">
                        @if ($farmer->passport_photo_url)
                            <img
                                src="{{ $farmer->passport_photo_url }}"
                                alt="{{ $farmer->full_name }}"
                                class="h-32 w-32 rounded-2xl object-cover ring-1 ring-gray-200 dark:ring-gray-800"
                            >
                        @else
                            <div class="flex h-32 w-32 items-center justify-center rounded-2xl bg-gray-100 text-3xl font-semibold text-gray-500 dark:bg-gray-900 dark:text-gray-300">
                                {{ str($farmer->full_name)->substr(0, 1)->upper() }}
                            </div>
                        @endif
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Phone</div>
                            <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $farmer->phone }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">National ID</div>
                            <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $farmer->national_id_number ?: 'Not captured' }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Registration source</div>
                            <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ str($farmer->registration_source->value)->replace('_', ' ')->title() }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Verification status</div>
                            <div class="mt-1">
                                <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    {{ str($farmer->verification_status->value)->replace('_', ' ')->title() }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Registered by</div>
                            <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $farmer->registeredBy?->name ?? 'Self registered' }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Verified by</div>
                            <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $farmer->verifiedBy?->name ?? 'Not yet verified' }}</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Location summary</h2>
                <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Region</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $farmer->location?->region?->name ?? 'Pending' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">District</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $farmer->location?->district?->name ?? 'Pending' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Subcounty</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $farmer->location?->subcounty?->name ?? 'Pending' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Parish</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $farmer->location?->parish?->name ?? 'Pending' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Village</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $farmer->location?->village?->name ?? 'Pending' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Nearest trading centre</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $farmer->location?->nearest_trading_centre ?? 'Not captured' }}</div>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Map preview</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Coordinate summary and field boundary preview when mapping data exists.
                        </p>
                    </div>
                    <div class="rounded-2xl bg-gray-50 px-4 py-3 text-sm text-gray-600 dark:bg-gray-900 dark:text-gray-300">
                        {{ $farmer->location?->latitude ?: '--' }}, {{ $farmer->location?->longitude ?: '--' }}
                    </div>
                </div>

                @if ($farmer->location?->latitude && $farmer->location?->longitude)
                    <div class="mt-5">
                        <div wire:ignore data-farmer-show-map class="h-[22rem] rounded-2xl border border-gray-200 dark:border-gray-800"></div>
                    </div>
                @else
                    <div class="mt-5 rounded-2xl border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        Coordinates have not been captured for this farmer yet.
                    </div>
                @endif
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Farm business profile</h2>
                <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Farm name</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $farmer->businessProfile?->farm_name ?? 'Not captured' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Farm size</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $farmer->businessProfile?->farm_size_acres ? number_format((float) $farmer->businessProfile->farm_size_acres, 2).' acres' : 'Not captured' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Irrigation</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $farmer->businessProfile?->irrigation_availability ? str($farmer->businessProfile->irrigation_availability->value)->replace('_', ' ')->title() : 'Not captured' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Warehouse access</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $farmer->businessProfile?->has_warehouse_access === null ? 'Not captured' : ($farmer->businessProfile->has_warehouse_access ? 'Yes' : 'No') }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Cooperative</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $farmer->businessProfile?->cooperative_name ?? 'Not captured' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Income bracket</div>
                        <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $farmer->businessProfile?->average_annual_income_bracket ?? 'Not captured' }}</div>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Value chains & production</h2>
                <div class="mt-5 space-y-4">
                    @forelse ($farmer->valueChainEntries as $entry)
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900">
                            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $entry->valueChain?->name ?? 'Value chain' }}</div>
                                    <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $entry->production_scale ? str($entry->production_scale->value)->replace('_', ' ')->title() : 'Scale pending' }}
                                    </div>
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-300">
                                    {{ $entry->estimated_seasonal_harvest_kg ? number_format((float) $entry->estimated_seasonal_harvest_kg, 2).' kg' : 'Harvest pending' }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                            Value chain production data has not been captured yet.
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="space-y-6">
            @can('verify', $farmer)
                <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Verification review</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Approve, reject, or suspend the farmer verification state with an audit trail.
                    </p>

                    <div class="mt-5">
                        <livewire:admin.farmers.verify-action :farmer="$farmer" :key="'verify-'.$farmer->id" />
                    </div>
                </section>
            @endcan

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Activity history</h2>
                <div class="mt-5 space-y-4">
                    @forelse ($activities as $activity)
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ str($activity->description)->replace('.', ' ')->replace('_', ' ')->title() }}</div>
                                    <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $activity->causer?->name ?? 'System' }} / {{ $activity->created_at?->diffForHumans() }}
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $activity->created_at?->format('d M Y H:i') }}</div>
                            </div>

                            @if (filled($activity->properties['payload']['note'] ?? null))
                                <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800 dark:border-amber-900/60 dark:bg-amber-900/30 dark:text-amber-200">
                                    {{ $activity->properties['payload']['note'] }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                            No farmer activity has been logged yet.
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</div>

@if ($farmer->location?->latitude && $farmer->location?->longitude)
    @assets
        <link
            rel="stylesheet"
            href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
            crossorigin=""
        >
        <script
            src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""
        ></script>
    @endassets

    @script
        <script>
            const mapElement = $wire.$el.querySelector('[data-farmer-show-map]')
            const farmerPoint = @js([
                'latitude' => (float) $farmer->location->latitude,
                'longitude' => (float) $farmer->location->longitude,
                'name' => $farmer->full_name,
                'boundary' => $farmer->location->farm_boundary_geojson ? json_decode($farmer->location->farm_boundary_geojson, true) : null,
            ])

            if (mapElement) {
                const map = L.map(mapElement).setView([farmerPoint.latitude, farmerPoint.longitude], 14)

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors',
                }).addTo(map)

                L.marker([farmerPoint.latitude, farmerPoint.longitude])
                    .bindPopup(farmerPoint.name)
                    .addTo(map)

                if (farmerPoint.boundary) {
                    try {
                        const boundary = L.geoJSON(farmerPoint.boundary).addTo(map)
                        map.fitBounds(boundary.getBounds(), { padding: [24, 24] })
                    } catch (error) {
                        console.warn('Unable to render farm boundary', error)
                    }
                }
            }
        </script>
    @endscript
@endif
