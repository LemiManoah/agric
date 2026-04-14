<div class="space-y-6">
    @php
        $stepLabels = [1 => 'Personal', 2 => 'Location', 3 => 'Mapping', 4 => 'Business & photo', 5 => 'Value chains', 6 => 'Review'];
    @endphp

    @if ($showHeader)
        <div class="overflow-hidden rounded-[2rem] border border-[#d9e3db] bg-white shadow-sm">
            <div class="grid lg:grid-cols-[minmax(0,1.35fr)_320px]">
                <div class="space-y-4 bg-gradient-to-br from-[#113a2d] via-[#0f5132] to-[#8a6a1f] px-6 py-7 text-white sm:px-8">
                    <span class="inline-flex w-fit rounded-full border border-white/20 bg-white/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.22em]">Farmer self-onboarding</span>
                    <div class="space-y-2">
                        <h1 class="text-2xl font-black sm:text-3xl">Farmer registration wizard</h1>
                        <p class="max-w-2xl text-sm leading-7 text-white/85">Capture personal details, geography, mapping, passport photo, business profile, and production data in one guided flow.</p>
                    </div>
                </div>
                <div class="bg-[#f4f0e6] px-6 py-7">
                    <div class="text-xs font-semibold uppercase tracking-[0.22em] text-[#6d5a2b]">Progress</div>
                    <div class="mt-3 text-2xl font-black text-[#113a2d]">Step {{ $step }} of 6</div>
                    <p class="mt-2 text-sm leading-7 text-[#395047]">This wizard now captures the full M1 farmer onboarding data set.</p>
                </div>
            </div>
            <div class="grid gap-3 border-t border-[#e5ece6] px-6 py-5 md:grid-cols-3 xl:grid-cols-6">
                @foreach ($stepLabels as $stepNumber => $label)
                    <div @class([
                        'rounded-2xl border px-4 py-3 text-sm transition',
                        'border-[#caa64b] bg-[#fff6de] text-[#6d561d]' => $step === $stepNumber,
                        'border-[#dde7df] bg-[#f8faf8] text-[#52635c]' => $step !== $stepNumber,
                    ])>
                        <div class="text-[11px] font-semibold uppercase tracking-[0.2em]">Step {{ $stepNumber }}</div>
                        <div class="mt-1 font-semibold">{{ $label }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <form wire:submit="submit" class="space-y-6">
        <section @class(['rounded-[2rem] border border-[#d9e3db] bg-white p-6 shadow-sm', 'hidden' => $step !== 1])>
            <div class="mb-6 space-y-2">
                <h2 class="text-xl font-bold text-[#113a2d]">Personal information</h2>
                <p class="text-sm leading-7 text-[#55665f]">Start with identity and household details.</p>
            </div>
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                <div><x-forms.input label="Full name" name="full_name" wire:model.live="full_name" /></div>
                <div><x-forms.input label="Phone" name="phone" wire:model.live="phone" /></div>
                <div><x-forms.input label="National ID number" name="national_id_number" wire:model.live="national_id_number" /></div>
                <div class="space-y-1">
                    <label for="gender" class="ml-1 block text-sm font-medium text-[#355046]">Gender</label>
                    <select id="gender" wire:model.live="gender" class="w-full rounded-xl border border-[#cfd9d2] bg-[#fbfcfb] px-4 py-2.5 text-[#16392f] focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#1f7a53]">
                        <option value="">Select gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                    @error('gender') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
                <div><x-forms.input label="Date of birth" name="date_of_birth" type="date" wire:model.live="date_of_birth" /></div>
                <div><x-forms.input label="Education level" name="education_level" wire:model.live="education_level" /></div>
                <div><x-forms.input label="Profession" name="profession" wire:model.live="profession" /></div>
                <div><x-forms.input label="Household size" name="household_size" type="number" min="1" wire:model.live="household_size" /></div>
                <div><x-forms.input label="Number of dependants" name="number_of_dependants" type="number" min="0" wire:model.live="number_of_dependants" /></div>
                <div class="md:col-span-2 xl:col-span-3"><x-forms.input label="Languages spoken" name="languages_spoken" placeholder="Comma separated, e.g. Luganda, English" wire:model.live="languages_spoken" /></div>
                @if ($managedRegistration)
                    <div class="rounded-2xl border border-[#b9d9cb] bg-[#edf8f2] px-4 py-3 text-sm text-[#145337] md:col-span-2 xl:col-span-3">Registration source is locked to <span class="font-semibold">Field officer</span> for this workflow.</div>
                @else
                    <div class="space-y-1 md:col-span-2 xl:col-span-3">
                        <label for="registration_source" class="ml-1 block text-sm font-medium text-[#355046]">Registration source</label>
                        <select id="registration_source" wire:model.live="registration_source" class="w-full rounded-xl border border-[#cfd9d2] bg-[#fbfcfb] px-4 py-2.5 text-[#16392f] focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#1f7a53]">
                            <option value="self_registered">Self registered</option>
                            <option value="field_officer">Field officer</option>
                            <option value="imported">Imported</option>
                        </select>
                        @error('registration_source') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                @endif
            </div>
        </section>

        <section @class(['rounded-[2rem] border border-[#d9e3db] bg-white p-6 shadow-sm', 'hidden' => $step !== 2])>
            <div class="mb-6 space-y-2">
                <h2 class="text-xl font-bold text-[#113a2d]">Location</h2>
                <p class="text-sm leading-7 text-[#55665f]">Use the normalized Uganda geography structure.</p>
            </div>
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                <div class="space-y-1">
                    <label for="region_id" class="ml-1 block text-sm font-medium text-[#355046]">Region</label>
                    <select id="region_id" wire:model.live="region_id" class="w-full rounded-xl border border-[#cfd9d2] bg-[#fbfcfb] px-4 py-2.5 text-[#16392f] focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#1f7a53]"><option value="">Select region</option>@foreach ($regions as $region)<option value="{{ $region->id }}">{{ $region->name }}</option>@endforeach</select>
                    @error('region_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-1">
                    <label for="district_id" class="ml-1 block text-sm font-medium text-[#355046]">District</label>
                    <select id="district_id" wire:model.live="district_id" class="w-full rounded-xl border border-[#cfd9d2] bg-[#fbfcfb] px-4 py-2.5 text-[#16392f] focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#1f7a53]"><option value="">Select district</option>@foreach ($districts as $district)<option value="{{ $district->id }}">{{ $district->name }}</option>@endforeach</select>
                    @error('district_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-1">
                    <label for="subcounty_id" class="ml-1 block text-sm font-medium text-[#355046]">Subcounty</label>
                    <select id="subcounty_id" wire:model.live="subcounty_id" class="w-full rounded-xl border border-[#cfd9d2] bg-[#fbfcfb] px-4 py-2.5 text-[#16392f] focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#1f7a53]"><option value="">Select subcounty</option>@foreach ($subcounties as $subcounty)<option value="{{ $subcounty->id }}">{{ $subcounty->name }}</option>@endforeach</select>
                    @error('subcounty_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-1">
                    <label for="parish_id" class="ml-1 block text-sm font-medium text-[#355046]">Parish</label>
                    <select id="parish_id" wire:model.live="parish_id" class="w-full rounded-xl border border-[#cfd9d2] bg-[#fbfcfb] px-4 py-2.5 text-[#16392f] focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#1f7a53]"><option value="">Select parish</option>@foreach ($parishes as $parish)<option value="{{ $parish->id }}">{{ $parish->name }}</option>@endforeach</select>
                    @error('parish_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-1">
                    <label for="village_id" class="ml-1 block text-sm font-medium text-[#355046]">Village</label>
                    <select id="village_id" wire:model.live="village_id" class="w-full rounded-xl border border-[#cfd9d2] bg-[#fbfcfb] px-4 py-2.5 text-[#16392f] focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#1f7a53]"><option value="">Select village</option>@foreach ($villages as $village)<option value="{{ $village->id }}">{{ $village->name }}</option>@endforeach</select>
                    @error('village_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>
        </section>

        <section @class(['rounded-[2rem] border border-[#d9e3db] bg-white p-6 shadow-sm', 'hidden' => $step !== 3])>
            <div class="mb-6 space-y-2">
                <h2 class="text-xl font-bold text-[#113a2d]">Farm mapping</h2>
                <p class="text-sm leading-7 text-[#55665f]">Capture coordinates directly or click on the map.</p>
            </div>
            <div class="grid gap-6 lg:grid-cols-[minmax(0,1.3fr)_minmax(320px,1fr)]">
                <div wire:ignore class="space-y-3">
                    <div data-registration-map class="h-[26rem] rounded-[1.5rem] border border-[#d5e0d8]"></div>
                    <p class="text-xs text-[#61726a]">Tip: click anywhere on the map to set latitude and longitude automatically.</p>
                </div>
                <div class="grid gap-5">
                    <div class="grid gap-5 md:grid-cols-2">
                        <div><x-forms.input label="Latitude" name="latitude" wire:model.live="latitude" /></div>
                        <div><x-forms.input label="Longitude" name="longitude" wire:model.live="longitude" /></div>
                    </div>
                    <div><x-forms.input label="Nearest trading centre" name="nearest_trading_centre" wire:model.live="nearest_trading_centre" /></div>
                    <div><x-forms.input label="Distance to tarmac road (km)" name="distance_to_tarmac_road_km" type="number" step="0.01" min="0" wire:model.live="distance_to_tarmac_road_km" /></div>
                    <div class="space-y-1">
                        <label for="internet_access_level" class="ml-1 block text-sm font-medium text-[#355046]">Internet access level</label>
                        <select id="internet_access_level" wire:model.live="internet_access_level" class="w-full rounded-xl border border-[#cfd9d2] bg-[#fbfcfb] px-4 py-2.5 text-[#16392f] focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#1f7a53]">
                            <option value="">Select access level</option>
                            @foreach ($internetAccessLevels as $accessLevel)
                                <option value="{{ $accessLevel->value }}">{{ strtoupper($accessLevel->value) }}</option>
                            @endforeach
                        </select>
                        @error('internet_access_level') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-1">
                        <label for="farm_boundary_geojson" class="ml-1 block text-sm font-medium text-[#355046]">Farm boundary GeoJSON (optional)</label>
                        <textarea id="farm_boundary_geojson" wire:model.live="farm_boundary_geojson" rows="6" class="w-full rounded-xl border border-[#cfd9d2] bg-[#fbfcfb] px-4 py-2.5 text-sm text-[#16392f] focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#1f7a53]" placeholder='{"type":"Polygon","coordinates":[[[32.1,0.3],[32.2,0.4],[32.3,0.3],[32.1,0.3]]]}'></textarea>
                        @error('farm_boundary_geojson') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </section>

        <section @class(['rounded-[2rem] border border-[#d9e3db] bg-white p-6 shadow-sm', 'hidden' => $step !== 4])>
            <div class="mb-6 space-y-2">
                <h2 class="text-xl font-bold text-[#113a2d]">Business profile & passport photo</h2>
                <p class="text-sm leading-7 text-[#55665f]">Complete the farm business profile and upload a passport photo.</p>
            </div>
            <div class="grid gap-6 xl:grid-cols-[240px_minmax(0,1fr)]">
                <div class="rounded-[1.5rem] border border-[#e0e8e1] bg-[#f8fbf8] p-5">
                    <div class="text-xs font-semibold uppercase tracking-[0.22em] text-[#6d5a2b]">Passport photo</div>
                    <div class="mt-4 flex justify-center">
                        @if ($passport_photo)
                            <img src="{{ $passport_photo->temporaryUrl() }}" alt="Passport photo preview" class="h-44 w-44 rounded-[1.5rem] object-cover ring-1 ring-[#d5e0d8]">
                        @else
                            <div class="flex h-44 w-44 items-center justify-center rounded-[1.5rem] bg-[#113a2d] text-5xl font-black text-white">{{ str($full_name ?: 'F')->substr(0, 1)->upper() }}</div>
                        @endif
                    </div>
                    <div class="mt-5 space-y-2">
                        <label for="passport_photo" class="ml-1 block text-sm font-medium text-[#355046]">Upload passport photo</label>
                        <input id="passport_photo" type="file" wire:model.live="passport_photo" accept="image/*" class="block w-full rounded-xl border border-[#cfd9d2] bg-white px-4 py-2.5 text-sm text-[#16392f] file:mr-4 file:rounded-lg file:border-0 file:bg-[#1f7a53] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white">
                        @error('passport_photo') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    <div><x-forms.input label="Farm name" name="business_profile.farm_name" wire:model.live="business_profile.farm_name" /></div>
                    <div><x-forms.input label="URSB registration number" name="business_profile.ursb_registration_number" wire:model.live="business_profile.ursb_registration_number" /></div>
                    <div><x-forms.input label="Farm size (acres)" name="business_profile.farm_size_acres" type="number" step="0.01" min="0" wire:model.live="business_profile.farm_size_acres" /></div>
                    <div><x-forms.input label="Number of plots" name="business_profile.number_of_plots" type="number" min="0" wire:model.live="business_profile.number_of_plots" /></div>
                    <div class="space-y-1">
                        <label for="business_profile_irrigation" class="ml-1 block text-sm font-medium text-[#355046]">Irrigation availability</label>
                        <select id="business_profile_irrigation" wire:model.live="business_profile.irrigation_availability" class="w-full rounded-xl border border-[#cfd9d2] bg-[#fbfcfb] px-4 py-2.5 text-[#16392f] focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#1f7a53]">
                            <option value="">Select irrigation status</option>
                            @foreach ($irrigationAvailabilityOptions as $option)
                                <option value="{{ $option->value }}">{{ str($option->value)->replace('_', ' ')->title() }}</option>
                            @endforeach
                        </select>
                        @error('business_profile.irrigation_availability') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div><x-forms.input label="Storage capacity (tonnes)" name="business_profile.post_harvest_storage_capacity_tonnes" type="number" step="0.01" min="0" wire:model.live="business_profile.post_harvest_storage_capacity_tonnes" /></div>
                    <label class="inline-flex items-center gap-3 rounded-2xl border border-[#e0e8e1] bg-[#f8fbf8] px-4 py-3 text-sm text-[#355046]"><input type="checkbox" wire:model.live="business_profile.has_warehouse_access" class="rounded border-[#b6c7bc] text-[#1f7a53] focus:ring-[#1f7a53]">Has warehouse access</label>
                    <label class="inline-flex items-center gap-3 rounded-2xl border border-[#e0e8e1] bg-[#f8fbf8] px-4 py-3 text-sm text-[#355046]"><input type="checkbox" wire:model.live="business_profile.cooperative_member" class="rounded border-[#b6c7bc] text-[#1f7a53] focus:ring-[#1f7a53]">Cooperative member</label>
                    <div><x-forms.input label="Cooperative name" name="business_profile.cooperative_name" wire:model.live="business_profile.cooperative_name" /></div>
                    <div><x-forms.input label="Cooperative role" name="business_profile.cooperative_role" wire:model.live="business_profile.cooperative_role" /></div>
                    <div class="md:col-span-2 xl:col-span-3"><x-forms.input label="Average annual income bracket" name="business_profile.average_annual_income_bracket" wire:model.live="business_profile.average_annual_income_bracket" /></div>
                </div>
            </div>
        </section>

        <section @class(['rounded-[2rem] border border-[#d9e3db] bg-white p-6 shadow-sm', 'hidden' => $step !== 5])>
            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-[#113a2d]">Value chains & production</h2>
                    <p class="mt-2 text-sm leading-7 text-[#55665f]">Record the active value chains, scale, harvest, and market destination.</p>
                </div>
                <button type="button" wire:click="addValueChain" class="inline-flex items-center rounded-xl bg-[#1f7a53] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#176243]">Add value chain</button>
            </div>
            <div class="mt-5 space-y-5">
                @foreach ($value_chains as $index => $row)
                    <div wire:key="value-chain-row-{{ $index }}" class="rounded-[1.5rem] border border-[#e0e8e1] bg-[#f8fbf8] p-5">
                        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                            <div class="space-y-1">
                                <label for="value_chain_{{ $index }}" class="ml-1 block text-sm font-medium text-[#355046]">Value chain</label>
                                <select id="value_chain_{{ $index }}" wire:model.live="value_chains.{{ $index }}.value_chain_id" class="w-full rounded-xl border border-[#cfd9d2] bg-white px-4 py-2.5 text-[#16392f] focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#1f7a53]">
                                    <option value="">Select value chain</option>
                                    @foreach ($valueChainOptions as $option)
                                        <option value="{{ $option->id }}">{{ $option->name }}</option>
                                    @endforeach
                                </select>
                                @error("value_chains.$index.value_chain_id") <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div class="space-y-1">
                                <label for="production_scale_{{ $index }}" class="ml-1 block text-sm font-medium text-[#355046]">Production scale</label>
                                <select id="production_scale_{{ $index }}" wire:model.live="value_chains.{{ $index }}.production_scale" class="w-full rounded-xl border border-[#cfd9d2] bg-white px-4 py-2.5 text-[#16392f] focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#1f7a53]">
                                    <option value="">Select scale</option>
                                    @foreach ($productionScaleOptions as $option)
                                        <option value="{{ $option->value }}">{{ str($option->value)->replace('_', ' ')->title() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div><x-forms.input label="Estimated seasonal harvest (kg)" name="value_chains.{{ $index }}.estimated_seasonal_harvest_kg" type="number" step="0.01" min="0" wire:model.live="value_chains.{{ $index }}.estimated_seasonal_harvest_kg" /></div>
                            <div class="space-y-1">
                                <label for="market_destination_{{ $index }}" class="ml-1 block text-sm font-medium text-[#355046]">Market destination</label>
                                <select id="market_destination_{{ $index }}" wire:model.live="value_chains.{{ $index }}.current_market_destination" class="w-full rounded-xl border border-[#cfd9d2] bg-white px-4 py-2.5 text-[#16392f] focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#1f7a53]">
                                    <option value="">Select destination</option>
                                    @foreach ($marketDestinationOptions as $option)
                                        <option value="{{ $option->value }}">{{ str($option->value)->replace('_', ' ')->title() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-1 md:col-span-2 xl:col-span-4">
                                <label for="value_chain_notes_{{ $index }}" class="ml-1 block text-sm font-medium text-[#355046]">Input access details</label>
                                <textarea id="value_chain_notes_{{ $index }}" wire:model.live="value_chains.{{ $index }}.input_access_details" rows="4" class="w-full rounded-xl border border-[#cfd9d2] bg-white px-4 py-2.5 text-sm text-[#16392f] focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#1f7a53]"></textarea>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button type="button" wire:click="removeValueChain({{ $index }})" class="text-sm font-semibold text-red-600 transition hover:text-red-700">Remove</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section @class(['rounded-[2rem] border border-[#d9e3db] bg-white p-6 shadow-sm', 'hidden' => $step !== 6])>
            <div class="mb-6 space-y-2">
                <h2 class="text-xl font-bold text-[#113a2d]">Review & submit</h2>
                <p class="text-sm leading-7 text-[#55665f]">Confirm the captured information before it is saved.</p>
            </div>
            <div class="grid gap-6 xl:grid-cols-3">
                <div class="rounded-[1.5rem] border border-[#e0e8e1] bg-[#f8fbf8] p-5">
                    <h3 class="text-sm font-semibold uppercase tracking-[0.22em] text-[#6d5a2b]">Personal</h3>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-start justify-between gap-4"><dt class="text-[#60736a]">Full name</dt><dd class="text-right font-medium text-[#113a2d]">{{ $full_name ?: 'Not provided' }}</dd></div>
                        <div class="flex items-start justify-between gap-4"><dt class="text-[#60736a]">Phone</dt><dd class="text-right font-medium text-[#113a2d]">{{ $phone ?: 'Not provided' }}</dd></div>
                        <div class="flex items-start justify-between gap-4"><dt class="text-[#60736a]">Languages</dt><dd class="text-right font-medium text-[#113a2d]">{{ $languages_spoken ?: 'Not provided' }}</dd></div>
                        <div class="flex items-start justify-between gap-4"><dt class="text-[#60736a]">Registration source</dt><dd class="text-right font-medium text-[#113a2d]">{{ str($registration_source)->replace('_', ' ')->title() }}</dd></div>
                    </dl>
                </div>
                <div class="rounded-[1.5rem] border border-[#e0e8e1] bg-[#f8fbf8] p-5">
                    <h3 class="text-sm font-semibold uppercase tracking-[0.22em] text-[#6d5a2b]">Location & mapping</h3>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-start justify-between gap-4"><dt class="text-[#60736a]">Region</dt><dd class="text-right font-medium text-[#113a2d]">{{ $regions->firstWhere('id', $region_id)?->name ?? 'Not selected' }}</dd></div>
                        <div class="flex items-start justify-between gap-4"><dt class="text-[#60736a]">District</dt><dd class="text-right font-medium text-[#113a2d]">{{ $districts->firstWhere('id', $district_id)?->name ?? 'Not selected' }}</dd></div>
                        <div class="flex items-start justify-between gap-4"><dt class="text-[#60736a]">Village</dt><dd class="text-right font-medium text-[#113a2d]">{{ $villages->firstWhere('id', $village_id)?->name ?? 'Not selected' }}</dd></div>
                        <div class="flex items-start justify-between gap-4"><dt class="text-[#60736a]">Coordinates</dt><dd class="text-right font-medium text-[#113a2d]">{{ $latitude ?: '--' }}, {{ $longitude ?: '--' }}</dd></div>
                    </dl>
                </div>
                <div class="rounded-[1.5rem] border border-[#e0e8e1] bg-[#f8fbf8] p-5">
                    <h3 class="text-sm font-semibold uppercase tracking-[0.22em] text-[#6d5a2b]">Business & production</h3>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-start justify-between gap-4"><dt class="text-[#60736a]">Passport photo</dt><dd class="text-right font-medium text-[#113a2d]">{{ $passport_photo ? 'Ready to upload' : 'Not added' }}</dd></div>
                        <div class="flex items-start justify-between gap-4"><dt class="text-[#60736a]">Farm name</dt><dd class="text-right font-medium text-[#113a2d]">{{ $business_profile['farm_name'] ?: 'Not provided' }}</dd></div>
                        <div class="flex items-start justify-between gap-4"><dt class="text-[#60736a]">Farm size</dt><dd class="text-right font-medium text-[#113a2d]">{{ $business_profile['farm_size_acres'] ?: 'Not provided' }}</dd></div>
                        <div class="flex items-start justify-between gap-4"><dt class="text-[#60736a]">Value chains</dt><dd class="text-right font-medium text-[#113a2d]">{{ collect($value_chains)->filter(fn ($row) => filled($row['value_chain_id'] ?? null))->count() }}</dd></div>
                    </dl>
                </div>
            </div>
        </section>

        <div class="flex items-center justify-between gap-4">
            <button type="button" wire:click="previousStep" @disabled($step === 1) class="inline-flex items-center rounded-xl border border-[#cfd9d2] bg-white px-4 py-2.5 text-sm font-semibold text-[#355046] transition hover:bg-[#f8fbf8] disabled:cursor-not-allowed disabled:opacity-50">Back</button>
            @if ($step < 6)
                <button type="button" wire:click="nextStep" class="inline-flex items-center rounded-xl bg-[#1f7a53] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#176243]">Next</button>
            @else
                <button type="submit" class="inline-flex items-center rounded-xl bg-[#caa64b] px-5 py-2.5 text-sm font-semibold text-[#1e241f] transition hover:bg-[#b8943f]">Submit registration</button>
            @endif
        </div>
    </form>
</div>

@assets
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
@endassets

@script
    <script>
        const ugandaCenter = [1.3733, 32.2903]
        const mapElement = $wire.$el.querySelector('[data-registration-map]')
        let map = null
        let marker = null
        let boundaryLayer = null

        const initialiseMap = () => {
            if (map || !mapElement) return
            map = L.map(mapElement).setView(ugandaCenter, 7)
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap contributors' }).addTo(map)
            boundaryLayer = L.layerGroup().addTo(map)
            map.on('click', (event) => {
                $wire.$set('latitude', event.latlng.lat.toFixed(6))
                $wire.$set('longitude', event.latlng.lng.toFixed(6))
            })
        }

        const syncMarker = () => {
            initialiseMap()
            if (!map) return
            const latitude = parseFloat($wire.latitude)
            const longitude = parseFloat($wire.longitude)
            if (Number.isNaN(latitude) || Number.isNaN(longitude)) {
                if (marker) {
                    map.removeLayer(marker)
                    marker = null
                }
                return
            }
            if (!marker) {
                marker = L.marker([latitude, longitude]).addTo(map)
            } else {
                marker.setLatLng([latitude, longitude])
            }
            map.setView([latitude, longitude], 14)
        }

        const syncBoundary = () => {
            initialiseMap()
            if (!boundaryLayer) return
            boundaryLayer.clearLayers()
            if (!$wire.farm_boundary_geojson) return
            try {
                const geoJson = JSON.parse($wire.farm_boundary_geojson)
                L.geoJSON(geoJson).addTo(boundaryLayer)
            } catch (error) {
                console.warn('Unable to render boundary preview', error)
            }
        }

        initialiseMap()
        syncMarker()
        syncBoundary()

        $wire.$watch('latitude', () => syncMarker())
        $wire.$watch('longitude', () => syncMarker())
        $wire.$watch('farm_boundary_geojson', () => syncBoundary())
        $wire.$watch('step', (value) => {
            if (parseInt(value, 10) === 3 && map) {
                setTimeout(() => map.invalidateSize(), 150)
                syncMarker()
                syncBoundary()
            }
        })
    </script>
@endscript
