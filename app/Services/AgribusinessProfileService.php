<?php

namespace App\Services;

use App\Enums\AgribusinessEntityType;
use App\Models\AgribusinessProfile;
use App\Models\District;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AgribusinessProfileService
{
    public function createProfile(array $data, ?User $actor = null): AgribusinessProfile
    {
        if ($actor) {
            throw_unless($actor->can('create', AgribusinessProfile::class), AuthorizationException::class);
        }

        $normalized = $this->normalizePayload([
            'user_id' => $data['user_id'] ?? $profile->user_id,
            ...$data,
        ]);

        if ($actor) {
            $this->ensureScope($normalized, $actor);
        }

        return DB::transaction(function () use ($actor, $normalized): AgribusinessProfile {
            $profile = AgribusinessProfile::query()->create([
                ...Arr::except($normalized, ['district_ids']),
                'created_by' => $actor?->id,
            ]);

            $profile->districts()->sync($normalized['district_ids']);
            $profile->load($this->relations());

            $this->logEvent('agribusiness.created', $profile, $actor, $normalized);

            return $profile;
        });
    }

    public function updateProfile(AgribusinessProfile $profile, array $data, ?User $actor = null): AgribusinessProfile
    {
        if ($actor) {
            throw_unless($actor->can('update', $profile), AuthorizationException::class);
        }

        $normalized = $this->normalizePayload($data);

        if ($actor) {
            $this->ensureScope($normalized, $actor);
        }

        return DB::transaction(function () use ($actor, $normalized, $profile): AgribusinessProfile {
            $profile->update(Arr::except($normalized, ['district_ids']));
            $profile->districts()->sync($normalized['district_ids']);
            $profile->load($this->relations());

            $this->logEvent('agribusiness.updated', $profile, $actor, $normalized);

            return $profile;
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizePayload(array $data): array
    {
        return [
            'user_id' => $this->nullableInt($data['user_id'] ?? null),
            'entity_type' => $data['entity_type'] instanceof AgribusinessEntityType
                ? $data['entity_type']
                : AgribusinessEntityType::from($data['entity_type']),
            'organization_name' => $this->nullableString($data['organization_name'] ?? null) ?? '',
            'registration_number' => $this->nullableString($data['registration_number'] ?? null),
            'membership_size' => $this->nullableInt($data['membership_size'] ?? null),
            'fleet_size' => $this->nullableInt($data['fleet_size'] ?? null),
            'service_rates' => $this->nullableString($data['service_rates'] ?? null),
            'product_range' => $this->nullableString($data['product_range'] ?? null),
            'processing_capacity_tonnes_per_day' => $this->nullableFloat($data['processing_capacity_tonnes_per_day'] ?? null),
            'export_markets' => $this->nullableString($data['export_markets'] ?? null),
            'buyer_criteria' => $this->nullableString($data['buyer_criteria'] ?? null),
            'contact_person' => $this->nullableString($data['contact_person'] ?? null) ?? '',
            'contact_phone' => $this->nullableString($data['contact_phone'] ?? null) ?? '',
            'district_ids' => collect($data['district_ids'] ?? [])
                ->map(fn (mixed $id): int => (int) $id)
                ->filter()
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $normalized
     */
    private function ensureScope(array $normalized, User $actor): void
    {
        if ($actor->hasRole('super_admin')) {
            return;
        }

        $districtIds = $normalized['district_ids'];

        if ($districtIds === []) {
            throw new AuthorizationException('At least one covered district is required.');
        }

        $outsideScopeExists = District::query()
            ->whereIn('id', $districtIds)
            ->when($actor->region_id, fn ($query) => $query->where('region_id', '!=', $actor->region_id))
            ->exists();

        if ($outsideScopeExists) {
            throw new AuthorizationException('One or more covered districts are outside your assigned region.');
        }
    }

    /**
     * @return array<int, string>
     */
    private function relations(): array
    {
        return [
            'creator',
            'districts.region',
            'user',
        ];
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    private function logEvent(string $event, AgribusinessProfile $profile, ?User $actor, array $properties = []): void
    {
        activity()
            ->performedOn($profile)
            ->causedBy($actor)
            ->event($event)
            ->withProperties([
                'agribusiness_profile_id' => $profile->id,
                'entity_type' => $profile->entity_type?->value,
                'district_ids' => $profile->districts()->pluck('districts.id')->all(),
                'properties' => Arr::except($properties, ['district_ids']),
            ])
            ->log($event);
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function nullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }
}
