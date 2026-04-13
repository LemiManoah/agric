<?php

namespace App\Services;

use App\Enums\VerificationStatus;
use App\Models\District;
use App\Models\Farmer;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SupplierService
{
    public function createSupplier(array $data, ?User $actor = null): Supplier
    {
        if ($actor) {
            throw_unless($actor->can('create', Supplier::class), AuthorizationException::class);
        }

        $normalized = $this->normalizePayload([
            'user_id' => $data['user_id'] ?? $supplier->user_id,
            'verification_status' => $data['verification_status'] ?? $supplier->verification_status,
            'warehouse_linked' => $data['warehouse_linked'] ?? $supplier->warehouse_linked,
            ...$data,
        ]);

        if ($actor) {
            $this->ensureScope($normalized, $actor);
        }

        return DB::transaction(function () use ($actor, $normalized): Supplier {
            $supplier = Supplier::query()->create([
                ...Arr::except($normalized, ['quality_grade_ids', 'value_chain_ids']),
                'created_by' => $actor?->id,
            ]);

            $supplier->valueChains()->sync($normalized['value_chain_ids']);
            $supplier->qualityGrades()->sync($normalized['quality_grade_ids']);
            $supplier->load($this->relations());

            $this->logEvent('supplier.created', $supplier, $actor, $normalized);

            return $supplier;
        });
    }

    public function updateSupplier(Supplier $supplier, array $data, ?User $actor = null): Supplier
    {
        if ($actor) {
            throw_unless($actor->can('update', $supplier), AuthorizationException::class);
        }

        $normalized = $this->normalizePayload($data);

        if ($actor) {
            $this->ensureScope($normalized, $actor);
        }

        return DB::transaction(function () use ($actor, $normalized, $supplier): Supplier {
            $supplier->update(Arr::except($normalized, ['quality_grade_ids', 'value_chain_ids']));
            $supplier->valueChains()->sync($normalized['value_chain_ids']);
            $supplier->qualityGrades()->sync($normalized['quality_grade_ids']);
            $supplier->load($this->relations());

            $this->logEvent('supplier.updated', $supplier, $actor, $normalized);

            return $supplier;
        });
    }

    public function verifySupplier(Supplier $supplier, User $actor): Supplier
    {
        throw_unless($actor->can('verify', $supplier), AuthorizationException::class);

        return DB::transaction(function () use ($actor, $supplier): Supplier {
            $supplier->forceFill([
                'verification_status' => VerificationStatus::Verified,
                'verified_at' => now(),
                'verified_by_user_id' => $actor->id,
            ])->save();

            $supplier->refresh()->load($this->relations());

            $this->logEvent('supplier.verified', $supplier, $actor);

            return $supplier;
        });
    }

    public function suspendSupplier(Supplier $supplier, User $actor): Supplier
    {
        throw_unless($actor->can('verify', $supplier), AuthorizationException::class);

        return DB::transaction(function () use ($actor, $supplier): Supplier {
            $supplier->forceFill([
                'verification_status' => VerificationStatus::Suspended,
                'verified_at' => null,
                'verified_by_user_id' => null,
            ])->save();

            $supplier->refresh()->load($this->relations());

            $this->logEvent('supplier.suspended', $supplier, $actor);

            return $supplier;
        });
    }

    public function setWarehouseLinked(Supplier $supplier, bool $linked, User $actor): Supplier
    {
        throw_unless($actor->can('toggleWarehouseLinked', $supplier), AuthorizationException::class);

        return DB::transaction(function () use ($actor, $linked, $supplier): Supplier {
            $supplier->forceFill([
                'warehouse_linked' => $linked,
            ])->save();

            $supplier->refresh()->load($this->relations());

            $this->logEvent('supplier.warehouse_linked_updated', $supplier, $actor, [
                'warehouse_linked' => $linked,
            ]);

            return $supplier;
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizePayload(array $data): array
    {
        return [
            'user_id' => $this->nullableInt($data['user_id'] ?? null),
            'farmer_id' => $this->nullableInt($data['farmer_id'] ?? null),
            'business_name' => $this->nullableString($data['business_name'] ?? null) ?? '',
            'contact_person' => $this->nullableString($data['contact_person'] ?? null) ?? '',
            'phone' => $this->nullableString($data['phone'] ?? null) ?? '',
            'email' => $this->nullableString($data['email'] ?? null),
            'operating_district_id' => $this->nullableInt($data['operating_district_id'] ?? null),
            'typical_supply_volume_kg_per_month' => $this->nullableFloat($data['typical_supply_volume_kg_per_month'] ?? null),
            'supply_frequency' => $data['supply_frequency'] instanceof \BackedEnum
                ? $data['supply_frequency']->value
                : $this->nullableString($data['supply_frequency'] ?? null),
            'verification_status' => VerificationStatus::from(
                $data['verification_status'] instanceof VerificationStatus
                    ? $data['verification_status']->value
                    : ($data['verification_status'] ?? VerificationStatus::Submitted->value)
            ),
            'warehouse_linked' => (bool) ($data['warehouse_linked'] ?? false),
            'quality_grade_ids' => collect($data['quality_grade_ids'] ?? [])
                ->map(fn (mixed $id): int => (int) $id)
                ->filter()
                ->values()
                ->all(),
            'value_chain_ids' => collect($data['value_chain_ids'] ?? [])
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

        $district = null;

        if ($normalized['operating_district_id']) {
            $district = District::query()->find($normalized['operating_district_id']);
        } elseif ($normalized['farmer_id']) {
            $district = Farmer::query()
                ->with('location')
                ->find($normalized['farmer_id'])
                ?->location
                ?->district;
        }

        if (! $district) {
            throw new AuthorizationException('A scoped district or linked farmer is required for supplier management.');
        }

        if ($actor->district_id && (int) $district->id !== (int) $actor->district_id) {
            throw new AuthorizationException('The selected supplier district is outside your assigned scope.');
        }

        if ($actor->region_id && (int) $district->region_id !== (int) $actor->region_id) {
            throw new AuthorizationException('The selected supplier district is outside your assigned region.');
        }
    }

    /**
     * @return array<int, string>
     */
    private function relations(): array
    {
        return [
            'creator',
            'district.region',
            'farmer.location.region',
            'qualityGrades',
            'user',
            'valueChains',
            'verifiedBy',
        ];
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    private function logEvent(string $event, Supplier $supplier, ?User $actor, array $properties = []): void
    {
        activity()
            ->performedOn($supplier)
            ->causedBy($actor)
            ->event($event)
            ->withProperties([
                'supplier_id' => $supplier->id,
                'district_id' => $supplier->operating_district_id,
                'verification_status' => $supplier->verification_status?->value,
                'warehouse_linked' => $supplier->warehouse_linked,
                'properties' => Arr::except($properties, ['quality_grade_ids', 'value_chain_ids']),
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
