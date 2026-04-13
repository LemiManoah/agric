<?php

namespace App\Services;

use App\Enums\VerificationStatus;
use App\Models\Buyer;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class BuyerService
{
    public function createBuyer(array $data, ?User $actor = null): Buyer
    {
        if ($actor) {
            throw_unless($actor->can('create', Buyer::class), AuthorizationException::class);
        }

        $normalized = $this->normalizePayload($data);

        return DB::transaction(function () use ($actor, $normalized): Buyer {
            $buyer = Buyer::query()->create([
                ...Arr::except($normalized, ['value_chain_interest_ids']),
                'created_by' => $actor?->id,
            ]);

            $buyer->valueChainInterests()->sync($normalized['value_chain_interest_ids']);
            $buyer->load($this->relations());

            $this->logEvent('buyer.created', $buyer, $actor, $normalized);

            return $buyer;
        });
    }

    public function updateBuyer(Buyer $buyer, array $data, ?User $actor = null): Buyer
    {
        if ($actor) {
            throw_unless($actor->can('update', $buyer), AuthorizationException::class);
        }

        $normalized = $this->normalizePayload([
            'user_id' => $data['user_id'] ?? $buyer->user_id,
            'verification_status' => $data['verification_status'] ?? $buyer->verification_status,
            ...$data,
        ]);

        return DB::transaction(function () use ($actor, $buyer, $normalized): Buyer {
            $buyer->update(Arr::except($normalized, ['value_chain_interest_ids']));
            $buyer->valueChainInterests()->sync($normalized['value_chain_interest_ids']);
            $buyer->load($this->relations());

            $this->logEvent('buyer.updated', $buyer, $actor, $normalized);

            return $buyer;
        });
    }

    public function verifyBuyer(Buyer $buyer, User $actor): Buyer
    {
        throw_unless($actor->can('verify', $buyer), AuthorizationException::class);

        return DB::transaction(function () use ($buyer, $actor): Buyer {
            $buyer->forceFill([
                'verification_status' => VerificationStatus::Verified,
                'verified_at' => now(),
                'verified_by_user_id' => $actor->id,
            ])->save();

            $buyer->refresh()->load($this->relations());

            $this->logEvent('buyer.verified', $buyer, $actor);

            return $buyer;
        });
    }

    public function suspendBuyer(Buyer $buyer, User $actor): Buyer
    {
        throw_unless($actor->can('verify', $buyer), AuthorizationException::class);

        return DB::transaction(function () use ($buyer, $actor): Buyer {
            $buyer->forceFill([
                'verification_status' => VerificationStatus::Suspended,
                'verified_at' => null,
                'verified_by_user_id' => null,
            ])->save();

            $buyer->refresh()->load($this->relations());

            $this->logEvent('buyer.suspended', $buyer, $actor);

            return $buyer;
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizePayload(array $data): array
    {
        $verificationStatus = $data['verification_status'] ?? VerificationStatus::Submitted->value;

        return [
            'user_id' => $this->nullableInt($data['user_id'] ?? null),
            'company_name' => $this->nullableString($data['company_name'] ?? null) ?? '',
            'country' => $this->nullableString($data['country'] ?? null) ?? '',
            'business_type' => $this->nullableString($data['business_type'] ?? null) ?? '',
            'company_registration_number' => $this->nullableString($data['company_registration_number'] ?? null),
            'contact_person_full_name' => $this->nullableString($data['contact_person_full_name'] ?? null) ?? '',
            'phone' => $this->nullableString($data['phone'] ?? null) ?? '',
            'email' => $this->nullableString($data['email'] ?? null) ?? '',
            'annual_import_volume_usd_range' => $this->nullableString($data['annual_import_volume_usd_range'] ?? null),
            'preferred_payment_method' => $this->nullableString($data['preferred_payment_method'] ?? null),
            'verification_status' => $verificationStatus instanceof VerificationStatus
                ? $verificationStatus
                : VerificationStatus::from($verificationStatus),
            'value_chain_interest_ids' => collect($data['value_chain_interest_ids'] ?? [])
                ->map(fn (mixed $id): int => (int) $id)
                ->filter()
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function relations(): array
    {
        return [
            'creator',
            'user',
            'valueChainInterests',
            'verifiedBy',
        ];
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    private function logEvent(string $event, Buyer $buyer, ?User $actor, array $properties = []): void
    {
        activity()
            ->performedOn($buyer)
            ->causedBy($actor)
            ->event($event)
            ->withProperties([
                'buyer_id' => $buyer->id,
                'verification_status' => $buyer->verification_status?->value,
                'value_chain_interest_ids' => $buyer->valueChainInterests()->pluck('value_chains.id')->all(),
                'properties' => Arr::except($properties, ['value_chain_interest_ids']),
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
}
