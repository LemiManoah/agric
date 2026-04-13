<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Edit farmer</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Update core farmer details, normalized location data, passport photo, business profile, and production records.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a
                href="{{ route('admin.farmers.show', $farmer) }}"
                class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-900"
            >
                Cancel
            </a>
        </div>
    </div>

    <form wire:submit="save" class="space-y-6">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Core profile</h2>
            <div class="mt-5 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
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
                <div class="md:col-span-2 xl:col-span-3">
                    <x-forms.input label="Languages spoken" name="languages_spoken" wire:model.live="languages_spoken" placeholder="Comma separated, e.g. Luganda, English" />
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Passport photo</h2>
            <div class="mt-5 grid gap-5 md:grid-cols-[140px_minmax(0,1fr)]">
                <div class="flex items-start justify-center">
                    @if ($passport_photo)
                        <img src="{{ $passport_photo->temporaryUrl() }}" alt="Passport photo preview" class="h-32 w-32 rounded-2xl object-cover ring-1 ring-gray-200 dark:ring-gray-800">
                    @elseif ($farmer->passport_photo_url && ! $removePassportPhoto)
                        <img src="{{ $farmer->passport_photo_url }}" alt="{{ $farmer->full_name }}" class="h-32 w-32 rounded-2xl object-cover ring-1 ring-gray-200 dark:ring-gray-800">
                    @else
                        <div class="flex h-32 w-32 items-center justify-center rounded-2xl bg-gray-100 text-3xl font-semibold text-gray-500 dark:bg-gray-900 dark:text-gray-300">
                            {{ str($farmer->full_name)->substr(0, 1)->upper() }}
                        </div>
                    @endif
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="passport_photo" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Upload passport photo</label>
                        <input
                            id="passport_photo"
                            type="file"
                            wire:model.live="passport_photo"
                            accept="image/*"
                            class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-blue-600 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                        >
                        @error('passport_photo')
                            <span class="text-red-500">{{ $message }}</span>
                        @enderror
                    </div>

                    <label class="inline-flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" wire:model.live="removePassportPhoto" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        Remove existing photo
                    </label>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Location</h2>
            <div class="mt-5 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                <div class="space-y-1">
                    <label for="region_id" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Region</label>
                    <select id="region_id" wire:model.live="region_id" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-700 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
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
                    <select id="district_id" wire:model.live="district_id" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-700 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
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
                    <select id="subcounty_id" wire:model.live="subcounty_id" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-700 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
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
                    <select id="parish_id" wire:model.live="parish_id" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-700 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
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
                    <select id="village_id" wire:model.live="village_id" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-700 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        <option value="">Select village</option>
                        @foreach ($villages as $village)
                            <option value="{{ $village->id }}">{{ $village->name }}</option>
                        @endforeach
                    </select>
                    @error('village_id')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <x-forms.input label="Nearest trading centre" name="nearest_trading_centre" wire:model.live="nearest_trading_centre" />
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="grid gap-6 lg:grid-cols-[minmax(0,1.25fr)_minmax(320px,1fr)]">
                <div wire:ignore class="space-y-3">
                    <div data-farmer-edit-map class="h-[26rem] rounded-2xl border border-gray-200 dark:border-gray-800"></div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Click the map to update coordinates quickly.</p>
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
                        <x-forms.input label="Distance to tarmac road (km)" name="distance_to_tarmac_road_km" type="number" step="0.01" min="0" wire:model.live="distance_to_tarmac_road_km" />
                    </div>
                    <div class="space-y-1">
                        <label for="internet_access_level" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Internet access level</label>
                        <select id="internet_access_level" wire:model.live="internet_access_level" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-700 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
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
                        <label for="farm_boundary_geojson" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Farm boundary GeoJSON</label>
                        <textarea id="farm_boundary_geojson" wire:model.live="farm_boundary_geojson" rows="7" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"></textarea>
                        @error('farm_boundary_geojson')
                            <span class="text-red-500">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Farm business profile</h2>
            <div class="mt-5 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <x-forms.input label="Farm name" name="business_profile.farm_name" wire:model.live="business_profile.farm_name" />
                </div>
                <div>
                    <x-forms.input label="URSB registration number" name="business_profile.ursb_registration_number" wire:model.live="business_profile.ursb_registration_number" />
                </div>
                <div>
                    <x-forms.input label="Farm size (acres)" name="business_profile.farm_size_acres" type="number" step="0.01" min="0" wire:model.live="business_profile.farm_size_acres" />
                </div>
                <div>
                    <x-forms.input label="Number of plots" name="business_profile.number_of_plots" type="number" min="0" wire:model.live="business_profile.number_of_plots" />
                </div>
                <div class="space-y-1">
                    <label for="business_profile_irrigation" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Irrigation availability</label>
                    <select id="business_profile_irrigation" wire:model.live="business_profile.irrigation_availability" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-700 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        <option value="">Select irrigation status</option>
                        @foreach ($irrigationAvailabilityOptions as $option)
                            <option value="{{ $option->value }}">{{ str($option->value)->replace('_', ' ')->title() }}</option>
                        @endforeach
                    </select>
                    @error('business_profile.irrigation_availability')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <x-forms.input label="Storage capacity (tonnes)" name="business_profile.post_harvest_storage_capacity_tonnes" type="number" step="0.01" min="0" wire:model.live="business_profile.post_harvest_storage_capacity_tonnes" />
                </div>
                <label class="inline-flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300">
                    <input type="checkbox" wire:model.live="business_profile.has_warehouse_access" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    Has warehouse access
                </label>
                <label class="inline-flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300">
                    <input type="checkbox" wire:model.live="business_profile.cooperative_member" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    Cooperative member
                </label>
                <div>
                    <x-forms.input label="Cooperative name" name="business_profile.cooperative_name" wire:model.live="business_profile.cooperative_name" />
                </div>
                <div>
                    <x-forms.input label="Cooperative role" name="business_profile.cooperative_role" wire:model.live="business_profile.cooperative_role" />
                </div>
                <div class="md:col-span-2 xl:col-span-3">
                    <x-forms.input label="Average annual income bracket" name="business_profile.average_annual_income_bracket" wire:model.live="business_profile.average_annual_income_bracket" />
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Value chain production</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Capture the farmer's current value chains, scale, and market destination.</p>
                </div>

                <button type="button" wire:click="addValueChain" class="inline-flex items-center rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-900 dark:bg-emerald-900/40 dark:text-emerald-300 dark:hover:bg-emerald-900/60">
                    Add value chain
                </button>
            </div>

            <div class="mt-5 space-y-5">
                @forelse ($value_chains as $index => $row)
                    <div wire:key="value-chain-row-{{ $index }}" class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-800 dark:bg-gray-900">
                        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                            <div class="space-y-1">
                                <label for="value_chain_{{ $index }}" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Value chain</label>
                                <select id="value_chain_{{ $index }}" wire:model.live="value_chains.{{ $index }}.value_chain_id" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-700 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    <option value="">Select value chain</option>
                                    @foreach ($valueChainOptions as $option)
                                        <option value="{{ $option->id }}">{{ $option->name }}</option>
                                    @endforeach
                                </select>
                                @error("value_chains.$index.value_chain_id")
                                    <span class="text-red-500">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="space-y-1">
                                <label for="production_scale_{{ $index }}" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Production scale</label>
                                <select id="production_scale_{{ $index }}" wire:model.live="value_chains.{{ $index }}.production_scale" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-700 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    <option value="">Select scale</option>
                                    @foreach ($productionScaleOptions as $option)
                                        <option value="{{ $option->value }}">{{ str($option->value)->replace('_', ' ')->title() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-forms.input label="Estimated seasonal harvest (kg)" name="value_chains.{{ $index }}.estimated_seasonal_harvest_kg" type="number" step="0.01" min="0" wire:model.live="value_chains.{{ $index }}.estimated_seasonal_harvest_kg" />
                            </div>
                            <div class="space-y-1">
                                <label for="market_destination_{{ $index }}" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Market destination</label>
                                <select id="market_destination_{{ $index }}" wire:model.live="value_chains.{{ $index }}.current_market_destination" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-700 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    <option value="">Select destination</option>
                                    @foreach ($marketDestinationOptions as $option)
                                        <option value="{{ $option->value }}">{{ str($option->value)->replace('_', ' ')->title() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-2 xl:col-span-4 space-y-1">
                                <label for="value_chain_notes_{{ $index }}" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Input access details</label>
                                <textarea id="value_chain_notes_{{ $index }}" wire:model.live="value_chains.{{ $index }}.input_access_details" rows="4" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"></textarea>
                            </div>
                        </div>

                        <div class="mt-4 flex justify-end">
                            <button type="button" wire:click="removeValueChain({{ $index }})" class="text-sm font-medium text-red-600 transition hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                                Remove
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        Add the first value chain record to capture production data.
                    </div>
                @endforelse
            </div>
        </section>

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700">
                Save changes
            </button>
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
        const mapElement = $wire.$el.querySelector('[data-farmer-edit-map]')

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
                console.warn('Unable to render farm boundary preview', error)
            }
        }

        initialiseMap()
        syncMarker()
        syncBoundary()

        $wire.$watch('latitude', () => syncMarker())
        $wire.$watch('longitude', () => syncMarker())
        $wire.$watch('farm_boundary_geojson', () => syncBoundary())
    </script>
@endscript
