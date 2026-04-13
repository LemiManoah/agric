@php
    $summaryFarmers = collect($mapPoints)->take(8);
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Farm location map</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Explore mapped farmers by region, district, verification status, and registration source.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a
                href="{{ route('admin.farmers.index') }}"
                class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-900"
            >
                Back to registry
            </a>
        </div>
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-950">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
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
                <span class="font-medium text-gray-700 dark:text-gray-300">Value chain</span>
                <select
                    wire:model.live="valueChain"
                    disabled
                    class="w-full rounded-xl border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-500 disabled:cursor-not-allowed dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
                >
                    <option value="">Ready for the next schema batch</option>
                </select>
            </label>
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Mapped farms</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $visibleFarmers }} farmer{{ $visibleFarmers === 1 ? '' : 's' }} currently visible on the map.
                        </p>
                    </div>
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                        Leaflet
                    </span>
                </div>
            </div>

            <div class="p-4">
                <div wire:ignore data-farm-map class="h-[32rem] rounded-2xl border border-gray-200 dark:border-gray-800"></div>
            </div>
        </section>

        <aside class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Summary list</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Quick scan of the mapped farmers matching the current filters.
                </p>
            </div>

            <div class="divide-y divide-gray-200 dark:divide-gray-800">
                @forelse ($summaryFarmers as $farmer)
                    <div class="space-y-2 px-5 py-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $farmer['name'] }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $farmer['phone'] }}</div>
                            </div>

                            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                {{ str($farmer['verification_status'])->replace('_', ' ')->title() }}
                            </span>
                        </div>

                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $farmer['district_name'] ?? 'District pending' }} / {{ $farmer['region_name'] ?? 'Region pending' }}
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                        No mapped farmers match the current filters yet.
                    </div>
                @endforelse
            </div>
        </aside>
    </div>
</div>

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
        const ugandaCenter = [1.3733, 32.2903]
        const mapElement = $wire.$el.querySelector('[data-farm-map]')
        const initialMarkers = @js($mapPoints)

        let map = null
        let markersLayer = null
        let boundaryLayer = null

        const resetLayers = () => {
            if (markersLayer) {
                markersLayer.clearLayers()
            }

            if (boundaryLayer) {
                boundaryLayer.clearLayers()
            }
        }

        const initialiseMap = () => {
            if (map || ! mapElement) return

            map = L.map(mapElement).setView(ugandaCenter, 7)

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(map)

            markersLayer = L.layerGroup().addTo(map)
            boundaryLayer = L.layerGroup().addTo(map)
        }

        const drawMarkers = (markers) => {
            initialiseMap()
            resetLayers()

            if (! map) return

            if (! markers.length) {
                map.setView(ugandaCenter, 7)
                return
            }

            const bounds = []

            markers.forEach((marker) => {
                const point = [marker.latitude, marker.longitude]

                bounds.push(point)

                L.marker(point)
                    .bindPopup(`
                        <div class="space-y-1">
                            <div><strong>${marker.name}</strong></div>
                            <div>${marker.phone ?? 'No phone recorded'}</div>
                            <div>${marker.district_name ?? 'District pending'}</div>
                            <div>${marker.verification_status.replaceAll('_', ' ')}</div>
                        </div>
                    `)
                    .addTo(markersLayer)

                if (marker.farm_boundary_geojson) {
                    try {
                        L.geoJSON(marker.farm_boundary_geojson).addTo(boundaryLayer)
                    } catch (error) {
                        console.warn('Unable to render farm boundary', error)
                    }
                }
            })

            map.fitBounds(bounds, { padding: [30, 30] })
        }

        initialiseMap()
        drawMarkers(initialMarkers)

        $wire.$on('farmers-map-updated', ({ markers }) => {
            drawMarkers(markers)
        })
    </script>
@endscript
