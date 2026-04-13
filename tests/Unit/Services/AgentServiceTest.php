<?php

use App\Enums\AgentOnboardingStatus;
use App\Models\Agent;
use App\Models\User;
use App\Models\ValueChain;
use App\Services\AgentService;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('creates an agent with a generated code and synced relations', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $location = createTestLocationHierarchy();
    $valueChain = ValueChain::query()->create([
        'name' => 'Coffee',
        'slug' => 'coffee',
        'is_active' => true,
    ]);

    $agent = app(AgentService::class)->createAgent([
        'full_name' => 'Agent Service',
        'phone' => '256700003500',
        'primary_district_id' => $location['district']->id,
        'commission_rate' => 6.5,
        'onboarding_status' => AgentOnboardingStatus::Active,
        'region_ids' => [$location['region']->id],
        'value_chain_ids' => [$valueChain->id],
    ], $admin);

    expect($agent->agent_code)->toMatch('/^AGT-\d{5}$/')
        ->and($agent->regions->pluck('id')->all())->toBe([$location['region']->id])
        ->and($agent->valueChains->pluck('id')->all())->toBe([$valueChain->id]);

    expect(Activity::query()->where('subject_type', Agent::class)->where('event', 'agent.created')->exists())->toBeTrue();
});

it('updates an agent and preserves cumulative totals', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $location = createTestLocationHierarchy();

    $agent = Agent::query()->create([
        'full_name' => 'Original Agent',
        'agent_code' => 'AGT-90001',
        'phone' => '256700003501',
        'primary_district_id' => $location['district']->id,
        'commission_rate' => 4.5,
        'total_orders_placed' => 12,
        'total_commission_earned' => 45000,
        'onboarding_status' => AgentOnboardingStatus::Onboarding,
    ]);

    $updated = app(AgentService::class)->updateAgent($agent, [
        'full_name' => 'Updated Agent',
        'phone' => '256700003501',
        'primary_district_id' => $location['district']->id,
        'commission_rate' => 8.0,
        'onboarding_status' => AgentOnboardingStatus::Active,
        'region_ids' => [$location['region']->id],
        'value_chain_ids' => [],
    ], $admin);

    expect($updated->full_name)->toBe('Updated Agent')
        ->and($updated->total_orders_placed)->toBe(12)
        ->and((float) $updated->total_commission_earned)->toBe(45000.0)
        ->and($updated->onboarding_status)->toBe(AgentOnboardingStatus::Active);

    expect(Activity::query()->where('subject_type', Agent::class)->where('event', 'agent.updated')->exists())->toBeTrue();
});

it('changes agent status and logs the transition', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $location = createTestLocationHierarchy();

    $agent = Agent::query()->create([
        'full_name' => 'Status Agent',
        'agent_code' => 'AGT-90002',
        'phone' => '256700003502',
        'primary_district_id' => $location['district']->id,
        'commission_rate' => 4.5,
        'onboarding_status' => AgentOnboardingStatus::Onboarding,
    ]);

    $updated = app(AgentService::class)->setStatus($agent, AgentOnboardingStatus::Suspended, $admin);

    expect($updated->onboarding_status)->toBe(AgentOnboardingStatus::Suspended);

    expect(Activity::query()->where('subject_type', Agent::class)->where('event', 'agent.status_changed')->exists())->toBeTrue();
});
