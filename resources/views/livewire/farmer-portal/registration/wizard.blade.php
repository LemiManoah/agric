<div class="space-y-6">
    @if ($showHeader)
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div class="space-y-2">
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Farmer registration wizard</h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Capture core farmer details, normalized location data, and farm mapping information in one guided flow.
                    </p>
                </div>

                <div class="rounded-full bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                    Step {{ $step }} of 4
                </div>
            </div>

            <div class="mt-6 grid gap-3 md:grid-cols-4">
                @foreach ([1 => 'Personal information', 2 => 'Location', 3 => 'Farm mapping', 4 => 'Review & submit'] as $stepNumber => $label)
                    <div @class([
                        'rounded-2xl border px-4 py-3 text-sm transition',
                        'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-900/40 dark:text-emerald-300' => $step === $stepNumber,
                        'border-gray-200 bg-gray-50 text-gray-500 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400' => $step !== $stepNumber,
                    ])>
                        <div class="text-xs uppercase tracking-wide">Step {{ $stepNumber }}</div>
                        <div class="mt-1 font-medium">{{ $label }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <form wire:submit="submit" class="space-y-6">
        <section @class([
            'rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950',
            'hidden' => $step !== 1,
        ])>
            <div class="mb-6 space-y-2">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Personal information</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Basic identity and household details for the farmer profile.
                </p>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <x-forms.input label="Full name" name="full_name" wire:model.live="full_name" />
                </div>

                <div>
                    <x-forms.input label="Phone" name="phone" wire:model.live="phone" />
                </div>

                <div>
                    <x-forms.input label="National ID number" name="national_id_number" wire:model.live="national_id_number" />
                </div>

                <div class="space-y-1">
                    <label for="gender" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Gender</label>
                    <select
                        id="gender"
                        wire:model.live="gender"
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-700 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                    >
                        <option value="">Select gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                    @error('gender')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <x-forms.input label="Date of birth" name="date_of_birth" type="date" wire:model.live="date_of_birth" />
                </div>

                <div>
                    <x-forms.input label="Education level" name="education_level" wire:model.live="education_level" />
                </div>

                <div>
                    <x-forms.input label="Profession" name="profession" wire:model.live="profession" />
                </div>

                <div>
                    <x-forms.input label="Household size" name="household_size" type="number" min="1" wire:model.live="household_size" />
                </div>

                <div>
                    <x-forms.input label="Number of dependants" name="number_of_dependants" type="number" min="0" wire:model.live="number_of_dependants" />
                </div>

                <div>
                    <x-forms.input
                        label="Languages spoken"
                        name="languages_spoken"
                        placeholder="Comma separated, e.g. Luganda, English"
                        wire:model.live="languages_spoken"
                    />
                </div>

                @if ($managedRegistration)
                    <div class="rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800 dark:border-blue-900/60 dark:bg-blue-900/30 dark:text-blue-200">
                        Registration source is locked to <span class="font-semibold">Field officer</span> for this workflow.
                    </div>
                @else
                    <div class="space-y-1">
                        <label for="registration_source" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Registration source</label>
                        <select
                            id="registration_source"
                            wire:model.live="registration_source"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-700 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                        >
                            <option value="self_registered">Self registered</option>
                            <option value="field_officer">Field officer</option>
                            <option value="imported">Imported</option>
                        </select>
                        @error('registration_source')
                            <span class="text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                @endif
            </div>
        </section>

        <section @class([
            'rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950',
            'hidden' => $step !== 2,
        ])>
            <div class="mb-6 space-y-2">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Location</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Use the normalized Uganda hierarchy so later reporting and map filtering stay consistent.
                </p>
            </div>

            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                <div class="space-y-1">
                    <label for="region_id" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Region</label>
                    <select
                        id="region_id"
                        wire:model.live="region_id"
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-700 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                    >
                        <option value="">Select region</option>
                        @foreach ($regions as $region)
                            <option value="{{ $region->id }}">{{ $region->name }}</option>
                        @endforeach
                    </select>
                    @error('region_id')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label for="district_id" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">District</label>
                    <select
                        id="district_id"
                        wire:model.live="district_id"
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-700 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                    >
                        <option value="">Select district</option>
                        @foreach ($districts as $district)
                            <option value="{{ $district->id }}">{{ $district->name }}</option>
                        @endforeach
                    </select>
                    @error('district_id')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label for="subcounty_id" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Subcounty</label>
                    <select
                        id="subcounty_id"
                        wire:model.live="subcounty_id"
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-700 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                    >
                        <option value="">Select subcounty</option>
                        @foreach ($subcounties as $subcounty)
                            <option value="{{ $subcounty->id }}">{{ $subcounty->name }}</option>
                        @endforeach
                    </select>
                    @error('subcounty_id')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label for="parish_id" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Parish</label>
                    <select
                        id="parish_id"
                        wire:model.live="parish_id"
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-700 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                    >
                        <option value="">Select parish</option>
                        @foreach ($parishes as $parish)
                            <option value="{{ $parish->id }}">{{ $parish->name }}</option>
                        @endforeach
                    </select>
                    @error('parish_id')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label for="village_id" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Village</label>
                    <select
                        id="village_id"
                        wire:model.live="village_id"
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-700 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                    >
                        <option value="">Select village</option>
                        @foreach ($villages as $village)
                            <option value="{{ $village->id }}">{{ $village->name }}</option>
                        @endforeach
                    </select>
                    @error('village_id')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </section>

        <section @class([
            'rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950',
            'hidden' => $step !== 3,
        ])>
            <div class="mb-6 space-y-2">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Farm mapping</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Capture coordinates directly or click on the map. Polygon drawing can come in a later pass, but the boundary field is ready now.
                </p>
            </div>

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1.3fr)_minmax(320px,1fr)]">
                <div wire:ignore class="space-y-3">
                    <div data-registration-map class="h-[26rem] rounded-2xl border border-gray-200 dark:border-gray-800"></div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Tip: click anywhere on the map to set latitude and longitude automatically.
                    </p>
                </div>

                <div class="grid gap-5">
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <x-forms.input label="Latitude" name="latitude" wire:model.live="latitude" />
                        </div>

                        <div>
                            <x-forms.input label="Longitude" name="longitude" wire:model.live="longitude" />
                        </div>
                    </div>

                    <div>
                        <x-forms.input label="Nearest trading centre" name="nearest_trading_centre" wire:model.live="nearest_trading_centre" />
                    </div>

                    <div>
                        <x-forms.input
                            label="Distance to tarmac road (km)"
                            name="distance_to_tarmac_road_km"
                            type="number"
                            step="0.01"
                            min="0"
                            wire:model.live="distance_to_tarmac_road_km"
                        />
                    </div>

                    <div class="space-y-1">
                        <label for="internet_access_level" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Internet access level</label>
                        <select
                            id="internet_access_level"
                            wire:model.live="internet_access_level"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-700 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                        >
                            <option value="">Select access level</option>
                            @foreach ($internetAccessLevels as $accessLevel)
                                <option value="{{ $accessLevel->value }}">{{ strtoupper($accessLevel->value) }}</option>
                            @endforeach
                        </select>
                        @error('internet_access_level')
                            <span class="text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-1">
                        <label for="farm_boundary_geojson" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Farm boundary GeoJSON (optional)</label>
                        <textarea
                            id="farm_boundary_geojson"
                            wire:model.live="farm_boundary_geojson"
                            rows="6"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                            placeholder='{"type":"Polygon","coordinates":[[[32.1,0.3],[32.2,0.4],[32.3,0.3],[32.1,0.3]]]}'
                        ></textarea>
                        @error('farm_boundary_geojson')
                            <span class="text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </section>

        <section @class([
            'rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950',
            'hidden' => $step !== 4,
        ])>
            <div class="mb-6 space-y-2">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Review & submit</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Confirm the captured information before it is saved through the farmer registration service.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Personal</h3>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Full name</dt>
                            <dd class="text-right text-gray-900 dark:text-gray-100">{{ $full_name ?: 'Not provided' }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Phone</dt>
                            <dd class="text-right text-gray-900 dark:text-gray-100">{{ $phone ?: 'Not provided' }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Languages</dt>
                            <dd class="text-right text-gray-900 dark:text-gray-100">{{ $languages_spoken ?: 'Not provided' }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Registration source</dt>
                            <dd class="text-right text-gray-900 dark:text-gray-100">{{ str($registration_source)->replace('_', ' ')->title() }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Location & mapping</h3>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Region</dt>
                            <dd class="text-right text-gray-900 dark:text-gray-100">{{ $regions->firstWhere('id', $region_id)?->name ?? 'Not selected' }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">District</dt>
                            <dd class="text-right text-gray-900 dark:text-gray-100">{{ $districts->firstWhere('id', $district_id)?->name ?? 'Not selected' }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Village</dt>
                            <dd class="text-right text-gray-900 dark:text-gray-100">{{ $villages->firstWhere('id', $village_id)?->name ?? 'Not selected' }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Coordinates</dt>
                            <dd class="text-right text-gray-900 dark:text-gray-100">
                                {{ $latitude ?: '--' }}, {{ $longitude ?: '--' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </section>

        <div class="flex items-center justify-between gap-4">
            <button
                type="button"
                wire:click="previousStep"
                @disabled($step === 1)
                class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-900"
            >
                Back
            </button>

            <div class="flex items-center gap-3">
                @if ($step < 4)
                    <button
                        type="button"
                        wire:click="nextStep"
                        class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700"
                    >
                        Next
                    </button>
                @else
                    <button
                        type="submit"
                        class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700"
                    >
                        Submit registration
                    </button>
                @endif
            </div>
        </div>
    </form>
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
        const mapElement = $wire.$el.querySelector('[data-registration-map]')

        let map = null
        let marker = null
        let boundaryLayer = null

        const initialiseMap = () => {
            if (map || ! mapElement) return

            map = L.map(mapElement).setView(ugandaCenter, 7)

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(map)

            boundaryLayer = L.layerGroup().addTo(map)

            map.on('click', (event) => {
                $wire.$set('latitude', event.latlng.lat.toFixed(6))
                $wire.$set('longitude', event.latlng.lng.toFixed(6))
            })
        }

        const syncMarker = () => {
            initialiseMap()

            if (! map) return

            const latitude = parseFloat($wire.latitude)
            const longitude = parseFloat($wire.longitude)

            if (Number.isNaN(latitude) || Number.isNaN(longitude)) {
                if (marker) {
                    map.removeLayer(marker)
                    marker = null
                }

                return
            }

            if (! marker) {
                marker = L.marker([latitude, longitude]).addTo(map)
            } else {
                marker.setLatLng([latitude, longitude])
            }

            map.setView([latitude, longitude], 14)
        }

        const syncBoundary = () => {
            initialiseMap()

            if (! boundaryLayer) return

            boundaryLayer.clearLayers()

            if (! $wire.farm_boundary_geojson) return

            try {
                const geoJson = JSON.parse($wire.farm_boundary_geojson)
                L.geoJSON(geoJson).addTo(boundaryLayer)
            } catch (error) {
                console.warn('Unable to render boundary preview', error)
            }
        }

        const refreshMapSize = () => {
            if (! map) return

            setTimeout(() => map.invalidateSize(), 150)
        }

        initialiseMap()
        syncMarker()
        syncBoundary()

        $wire.$watch('latitude', () => syncMarker())
        $wire.$watch('longitude', () => syncMarker())
        $wire.$watch('farm_boundary_geojson', () => syncBoundary())
        $wire.$watch('step', (value) => {
            if (parseInt(value, 10) === 3) {
                refreshMapSize()
                syncMarker()
                syncBoundary()
            }
        })
    </script>
@endscript
