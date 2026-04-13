<?php

use App\Enums\AgentOnboardingStatus;
use App\Livewire\Admin\Agents\Form;
use App\Livewire\Admin\Agents\Index;
use App\Models\Agent;
use App\Models\User;
use App\Models\ValueChain;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('renders the agent index for a super admin', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $this->actingAs($user)
        ->get(route('admin.agents.index'))
        ->assertSuccessful()
        ->assertSee('Agent registry');
});

it('limits agent visibility to regional scope', function () {
    $visibleLocation = createTestLocationHierarchy();
    $hiddenLocation = createTestLocationHierarchy();

    $regionalAdmin = createScopedUser('regional_admin', [
        'region_id' => $visibleLocation['region']->id,
        'district_id' => $visibleLocation['district']->id,
    ]);

    Agent::query()->create([
        'full_name' => 'Central Agent',
        'agent_code' => 'AGT-10001',
        'phone' => '256700003001',
        'primary_district_id' => $visibleLocation['district']->id,
        'commission_rate' => 5,
        'onboarding_status' => AgentOnboardingStatus::Active,
    ]);

    Agent::query()->create([
        'full_name' => 'Hidden Agent',
        'agent_code' => 'AGT-10002',
        'phone' => '256700003002',
        'primary_district_id' => $hiddenLocation['district']->id,
        'commission_rate' => 5,
        'onboarding_status' => AgentOnboardingStatus::Onboarding,
    ]);

    Livewire::actingAs($regionalAdmin)
        ->test(Index::class)
        ->assertSee('Central Agent')
        ->assertDontSee('Hidden Agent');
});

it('renders the agent form for an authorized admin', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $location = createTestLocationHierarchy();
    ValueChain::query()->create([
        'name' => 'Soya',
        'slug' => 'soya',
        'is_active' => true,
    ]);

    Livewire::actingAs($admin)
        ->test(Form::class)
        ->assertSee('Create agent')
        ->assertSee('Service areas')
        ->assertSee('Value chains');
});
