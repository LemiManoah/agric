<?php

namespace App\Services;

use App\Enums\RegistrationSource;
use App\Enums\VerificationStatus;
use App\Models\Farmer;
use App\Models\FarmerBusinessProfile;
use App\Models\FarmerValueChain;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FarmerRegistrationService
{
    public function createFarmer(array $data, ?User $actor = null): Farmer
    {
        $normalized = $this->normalizeData($data, $actor);

        if ($this->isManagedRegistration($actor)) {
            throw_unless($actor?->can('create', Farmer::class), AuthorizationException::class);
            $this->ensureLocationScopeMatches($actor, $normalized);
        }

        return DB::transaction(function () use ($actor, $normalized): Farmer {
            $farmer = Farmer::query()->create($this->farmerPayload($normalized, $actor));

            $farmer->location()->create($this->locationPayload($normalized));
            $this->syncBusinessProfile($farmer, $normalized['business_profile']);
            $this->syncValueChains($farmer, $normalized['value_chains']);

            $farmer->load($this->defaultRelations());

            $this->logLifecycle('farmer.created', $farmer, $actor, $normalized);

            return $farmer;
        });
    }

    public function updateFarmer(Farmer $farmer, array $data, ?User $actor = null): Farmer
    {
        if ($actor) {
            throw_unless($actor->can('update', $farmer), AuthorizationException::class);
        }

        $normalized = $this->normalizeData($data, $actor, $farmer);

        if ($this->isManagedRegistration($actor)) {
            $this->ensureLocationScopeMatches($actor, $normalized);
        }

        return DB::transaction(function () use ($actor, $farmer, $normalized): Farmer {
            $farmer->update($this->farmerPayload($normalized, $actor, $farmer));

            $farmer->location()->updateOrCreate([], $this->locationPayload($normalized));
            $this->syncBusinessProfile($farmer, $normalized['business_profile']);
            $this->syncValueChains($farmer, $normalized['value_chains']);

            $farmer->load($this->defaultRelations());

            $this->logLifecycle('farmer.updated', $farmer, $actor, $normalized);

            return $farmer;
        });
    }

    public function verifyFarmer(Farmer $farmer, User $actor): Farmer
    {
        return $this->changeVerificationStatus($farmer, VerificationStatus::Verified, $actor);
    }

    public function changeVerificationStatus(
        Farmer $farmer,
        VerificationStatus $status,
        User $actor,
        ?string $note = null,
    ): Farmer {
        throw_unless($actor->can('verify', $farmer), AuthorizationException::class);

        return DB::transaction(function () use ($actor, $farmer, $note, $status): Farmer {
            $attributes = [
                'verification_status' => $status,
                'verified_at' => null,
                'verified_by_user_id' => null,
            ];

            if ($status === VerificationStatus::Verified) {
                $attributes['verified_at'] = now();
                $attributes['verified_by_user_id'] = $actor->id;
            }

            $farmer->forceFill($attributes)->save();

            $farmer->refresh()->load($this->defaultRelations());

            $this->logLifecycle($this->verificationEvent($status), $farmer, $actor, [
                'verification_status' => $status->value,
                'note' => $note,
            ]);

            return $farmer;
        });
    }

    private function normalizeData(array $data, ?User $actor = null, ?Farmer $farmer = null): array
    {
        $normalized = Arr::except($data, ['location']);

        $normalized['registration_source'] = $this->resolveRegistrationSource($data, $actor, $farmer)->value;
        $normalized['verification_status'] = $this->resolveVerificationStatus($data, $farmer)->value;
        $normalized['languages_spoken'] = $this->normalizeLanguages($data['languages_spoken'] ?? $farmer?->languages_spoken ?? []);

        foreach ([
            'full_name',
            'phone',
            'national_id_number',
            'passport_photo_path',
            'gender',
            'date_of_birth',
            'education_level',
            'profession',
            'nearest_trading_centre',
            'internet_access_level',
            'farm_boundary_geojson',
        ] as $field) {
            $normalized[$field] = $this->nullableString($data[$field] ?? ($farmer?->{$field} ?? null));
        }

        foreach ([
            'household_size',
            'number_of_dependants',
            'region_id',
            'district_id',
            'subcounty_id',
            'parish_id',
            'village_id',
            'user_id',
        ] as $field) {
            $normalized[$field] = $this->nullableInteger($data[$field] ?? ($farmer?->location?->{$field} ?? $farmer?->{$field} ?? null));
        }

        foreach ([
            'latitude',
            'longitude',
            'distance_to_tarmac_road_km',
        ] as $field) {
            $normalized[$field] = $this->nullableNumeric($data[$field] ?? ($farmer?->location?->{$field} ?? null));
        }

        $normalized['business_profile'] = $this->normalizeBusinessProfile($data['business_profile'] ?? []);
        $normalized['value_chains'] = $this->normalizeValueChains($data['value_chains'] ?? []);

        return $normalized;
    }

    private function farmerPayload(array $normalized, ?User $actor = null, ?Farmer $farmer = null): array
    {
        $payload = [
            'user_id' => $normalized['user_id'] ?? $farmer?->user_id,
            'full_name' => $normalized['full_name'],
            'phone' => $normalized['phone'],
            'national_id_number' => $normalized['national_id_number'],
            'passport_photo_path' => $normalized['passport_photo_path'],
            'gender' => $normalized['gender'],
            'date_of_birth' => $normalized['date_of_birth'],
            'education_level' => $normalized['education_level'],
            'profession' => $normalized['profession'],
            'household_size' => $normalized['household_size'],
            'number_of_dependants' => $normalized['number_of_dependants'],
            'languages_spoken' => $normalized['languages_spoken'],
            'registration_source' => $normalized['registration_source'],
            'verification_status' => $normalized['verification_status'],
        ];

        if (! $farmer) {
            $payload['registered_by_user_id'] = $this->isManagedRegistration($actor) ? $actor?->id : null;
        }

        if (! $this->isManagedRegistration($actor) && $actor) {
            $payload['user_id'] = $payload['user_id'] ?? $actor->id;
        }

        return $payload;
    }

    private function locationPayload(array $normalized): array
    {
        return [
            'region_id' => $normalized['region_id'],
            'district_id' => $normalized['district_id'],
            'subcounty_id' => $normalized['subcounty_id'],
            'parish_id' => $normalized['parish_id'],
            'village_id' => $normalized['village_id'],
            'latitude' => $normalized['latitude'],
            'longitude' => $normalized['longitude'],
            'farm_boundary_geojson' => $normalized['farm_boundary_geojson'],
            'nearest_trading_centre' => $normalized['nearest_trading_centre'],
            'distance_to_tarmac_road_km' => $normalized['distance_to_tarmac_road_km'],
            'internet_access_level' => $normalized['internet_access_level'],
        ];
    }

    private function resolveRegistrationSource(array $data, ?User $actor = null, ?Farmer $farmer = null): RegistrationSource
    {
        $value = $data['registration_source'] ?? $farmer?->registration_source?->value;

        if ($value) {
            return RegistrationSource::from($value);
        }

        return $this->isManagedRegistration($actor)
            ? RegistrationSource::FieldOfficer
            : RegistrationSource::SelfRegistered;
    }

    private function resolveVerificationStatus(array $data, ?Farmer $farmer = null): VerificationStatus
    {
        $value = $data['verification_status'] ?? $farmer?->verification_status?->value;

        if ($value) {
            return VerificationStatus::from($value);
        }

        return VerificationStatus::Submitted;
    }

    private function normalizeLanguages(array|string|null $languages): array
    {
        if (is_array($languages)) {
            return collect($languages)
                ->map(fn (mixed $language): ?string => $this->nullableString(is_string($language) ? $language : null))
                ->filter()
                ->values()
                ->all();
        }

        return collect(explode(',', (string) $languages))
            ->map(fn (string $language): ?string => $this->nullableString($language))
            ->filter()
            ->values()
            ->all();
    }

    private function nullableString(null|int|float|string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function nullableInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function nullableNumeric(mixed $value): null|float|int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    private function nullableBoolean(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    private function isManagedRegistration(?User $actor): bool
    {
        return $actor !== null && $actor->can('create', Farmer::class);
    }

    private function ensureLocationScopeMatches(User $actor, array $normalized): void
    {
        if ($actor->hasRole('super_admin')) {
            return;
        }

        if ($actor->district_id !== null && (int) $normalized['district_id'] !== (int) $actor->district_id) {
            throw new AuthorizationException('The selected district is outside your assigned scope.');
        }

        if ($actor->region_id !== null && (int) $normalized['region_id'] !== (int) $actor->region_id) {
            throw new AuthorizationException('The selected region is outside your assigned scope.');
        }
    }

    private function logLifecycle(string $event, Farmer $farmer, ?User $actor, array $payload = []): void
    {
        activity()
            ->performedOn($farmer)
            ->causedBy($actor)
            ->event($event)
            ->withProperties([
                'farmer_id' => $farmer->id,
                'region_id' => $farmer->location?->region_id,
                'district_id' => $farmer->location?->district_id,
                'registration_source' => $farmer->registration_source?->value,
                'verification_status' => $farmer->verification_status?->value,
                'payload' => Arr::only($payload, [
                    'full_name',
                    'phone',
                    'registration_source',
                    'verification_status',
                    'region_id',
                    'district_id',
                    'subcounty_id',
                    'parish_id',
                    'village_id',
                    'note',
                ]),
            ])
            ->log($event);
    }

    /**
     * @return array<int, string>
     */
    private function defaultRelations(): array
    {
        return [
            'businessProfile',
            'location.region',
            'location.district',
            'location.subcounty',
            'location.parish',
            'location.village',
            'registeredBy',
            'valueChainEntries.valueChain',
            'verifiedBy',
        ];
    }

    private function normalizeBusinessProfile(array $profile): ?array
    {
        $normalized = [
            'farm_name' => $this->nullableString($profile['farm_name'] ?? null),
            'ursb_registration_number' => $this->nullableString($profile['ursb_registration_number'] ?? null),
            'farm_size_acres' => $this->nullableNumeric($profile['farm_size_acres'] ?? null),
            'number_of_plots' => $this->nullableInteger($profile['number_of_plots'] ?? null),
            'irrigation_availability' => $this->nullableString($profile['irrigation_availability'] ?? null),
            'post_harvest_storage_capacity_tonnes' => $this->nullableNumeric($profile['post_harvest_storage_capacity_tonnes'] ?? null),
            'has_warehouse_access' => $this->nullableBoolean($profile['has_warehouse_access'] ?? null),
            'cooperative_member' => $this->nullableBoolean($profile['cooperative_member'] ?? null),
            'cooperative_name' => $this->nullableString($profile['cooperative_name'] ?? null),
            'cooperative_role' => $this->nullableString($profile['cooperative_role'] ?? null),
            'average_annual_income_bracket' => $this->nullableString($profile['average_annual_income_bracket'] ?? null),
        ];

        return collect($normalized)->filter(fn (mixed $value): bool => $value !== null)->isEmpty()
            ? null
            : $normalized;
    }

    /**
     * @param  array<int, array<string, mixed>>  $valueChains
     * @return array<int, array<string, mixed>>
     */
    private function normalizeValueChains(array $valueChains): array
    {
        return collect($valueChains)
            ->map(function (mixed $valueChain): ?array {
                if (! is_array($valueChain)) {
                    return null;
                }

                $valueChainId = $this->nullableInteger($valueChain['value_chain_id'] ?? null);

                if (! $valueChainId) {
                    return null;
                }

                return [
                    'value_chain_id' => $valueChainId,
                    'production_scale' => $this->nullableString($valueChain['production_scale'] ?? null),
                    'estimated_seasonal_harvest_kg' => $this->nullableNumeric($valueChain['estimated_seasonal_harvest_kg'] ?? null),
                    'current_market_destination' => $this->nullableString($valueChain['current_market_destination'] ?? null),
                    'input_access_details' => $this->normalizeInputAccessDetails($valueChain['input_access_details'] ?? null),
                ];
            })
            ->filter()
            ->unique('value_chain_id')
            ->values()
            ->all();
    }

    private function normalizeInputAccessDetails(mixed $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value)) {
            return null;
        }

        $decoded = json_decode($value, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : ['notes' => $trimmed];
    }

    private function syncBusinessProfile(Farmer $farmer, ?array $businessProfilePayload): void
    {
        if ($businessProfilePayload === null) {
            $farmer->businessProfile()?->delete();

            return;
        }

        $farmer->businessProfile()->updateOrCreate([], $businessProfilePayload);
    }

    /**
     * @param  array<int, array<string, mixed>>  $valueChainPayloads
     */
    private function syncValueChains(Farmer $farmer, array $valueChainPayloads): void
    {
        $valueChainIds = collect($valueChainPayloads)
            ->pluck('value_chain_id')
            ->filter()
            ->map(fn (mixed $id): int => (int) $id)
            ->all();

        if ($valueChainIds === []) {
            $farmer->valueChainEntries()->delete();

            return;
        }

        foreach ($valueChainPayloads as $payload) {
            $farmer->valueChainEntries()->updateOrCreate(
                ['value_chain_id' => $payload['value_chain_id']],
                Arr::except($payload, ['value_chain_id']),
            );
        }

        $farmer->valueChainEntries()
            ->whereNotIn('value_chain_id', $valueChainIds)
            ->delete();
    }

    private function verificationEvent(VerificationStatus $status): string
    {
        return match ($status) {
            VerificationStatus::PendingReview => 'farmer.pending_review',
            VerificationStatus::Rejected => 'farmer.rejected',
            VerificationStatus::Submitted => 'farmer.submitted',
            VerificationStatus::Suspended => 'farmer.suspended',
            VerificationStatus::Verified => 'farmer.verified',
        };
    }
}
