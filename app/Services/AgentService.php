<?php

namespace App\Services;

use App\Enums\AgentOnboardingStatus;
use App\Models\Agent;
use App\Models\District;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AgentService
{
    public function createAgent(array $data, ?User $actor = null): Agent
    {
        if ($actor) {
            throw_unless($actor->can('create', Agent::class), AuthorizationException::class);
        }

        $normalized = $this->normalizePayload($data);

        if ($actor) {
            $this->ensureScope($normalized, $actor);
        }

        return DB::transaction(function () use ($actor, $normalized): Agent {
            $agent = Agent::query()->create([
                ...Arr::except($normalized, ['region_ids', 'value_chain_ids']),
                'agent_code' => $this->generateAgentCode(),
                'created_by' => $actor?->id,
            ]);

            $agent->regions()->sync($normalized['region_ids']);
            $agent->valueChains()->sync($normalized['value_chain_ids']);
            $agent->load($this->relations());

            $this->logEvent('agent.created', $agent, $actor, $normalized);

            return $agent;
        });
    }

    public function updateAgent(Agent $agent, array $data, ?User $actor = null): Agent
    {
        if ($actor) {
            throw_unless($actor->can('update', $agent), AuthorizationException::class);
        }

        $normalized = $this->normalizePayload([
            'user_id' => $data['user_id'] ?? $agent->user_id,
            'total_orders_placed' => $data['total_orders_placed'] ?? $agent->total_orders_placed,
            'total_commission_earned' => $data['total_commission_earned'] ?? $agent->total_commission_earned,
            ...$data,
        ]);

        if ($actor) {
            $this->ensureScope($normalized, $actor);
        }

        return DB::transaction(function () use ($actor, $agent, $normalized): Agent {
            $agent->update(Arr::except($normalized, ['region_ids', 'value_chain_ids']));
            $agent->regions()->sync($normalized['region_ids']);
            $agent->valueChains()->sync($normalized['value_chain_ids']);
            $agent->load($this->relations());

            $this->logEvent('agent.updated', $agent, $actor, $normalized);

            return $agent;
        });
    }

    public function setStatus(Agent $agent, string|AgentOnboardingStatus $status, ?User $actor = null): Agent
    {
        if ($actor) {
            throw_unless($actor->can('changeStatus', $agent), AuthorizationException::class);
        }

        $resolved = $status instanceof AgentOnboardingStatus
            ? $status
            : AgentOnboardingStatus::from($status);

        return DB::transaction(function () use ($actor, $agent, $resolved): Agent {
            $agent->forceFill([
                'onboarding_status' => $resolved,
            ])->save();

            $agent->refresh()->load($this->relations());

            $this->logEvent('agent.status_changed', $agent, $actor, [
                'onboarding_status' => $resolved->value,
            ]);

            return $agent;
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizePayload(array $data): array
    {
        $onboardingStatus = $data['onboarding_status'] ?? AgentOnboardingStatus::Onboarding->value;

        return [
            'user_id' => $this->nullableInt($data['user_id'] ?? null),
            'full_name' => $this->nullableString($data['full_name'] ?? null) ?? '',
            'phone' => $this->nullableString($data['phone'] ?? null) ?? '',
            'email' => $this->nullableString($data['email'] ?? null),
            'primary_district_id' => $this->nullableInt($data['primary_district_id'] ?? null),
            'commission_rate' => $this->nullableFloat($data['commission_rate'] ?? null) ?? 0,
            'total_orders_placed' => $this->nullableInt($data['total_orders_placed'] ?? null) ?? 0,
            'total_commission_earned' => $this->nullableFloat($data['total_commission_earned'] ?? null) ?? 0,
            'onboarding_status' => $onboardingStatus instanceof AgentOnboardingStatus
                ? $onboardingStatus
                : AgentOnboardingStatus::from($onboardingStatus),
            'region_ids' => collect($data['region_ids'] ?? [])
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

        $district = District::query()->find($normalized['primary_district_id']);

        if (! $district) {
            throw new AuthorizationException('A primary district is required for agent management.');
        }

        if ($actor->region_id && (int) $district->region_id !== (int) $actor->region_id) {
            throw new AuthorizationException('The selected primary district is outside your assigned region.');
        }
    }

    private function generateAgentCode(): string
    {
        do {
            $code = 'AGT-'.str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (Agent::query()->where('agent_code', $code)->exists());

        return $code;
    }

    /**
     * @return array<int, string>
     */
    private function relations(): array
    {
        return [
            'creator',
            'primaryDistrict.region',
            'regions',
            'user',
            'valueChains',
        ];
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    private function logEvent(string $event, Agent $agent, ?User $actor, array $properties = []): void
    {
        activity()
            ->performedOn($agent)
            ->causedBy($actor)
            ->event($event)
            ->withProperties([
                'agent_id' => $agent->id,
                'agent_code' => $agent->agent_code,
                'primary_district_id' => $agent->primary_district_id,
                'properties' => Arr::except($properties, ['region_ids', 'value_chain_ids']),
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
