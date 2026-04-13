<?php

use App\Enums\AgentOnboardingStatus;
use App\Enums\AgribusinessEntityType;
use App\Enums\SupplyFrequency;
use App\Enums\VerificationStatus;
use App\Livewire\Admin\Reports\M1ProfileSummary;
use App\Models\Agent;
use App\Models\AgribusinessProfile;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('renders the M1 profile summary report for an authorized admin', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $this->actingAs($user)
        ->get(route('admin.reports.m1-profile-summary'))
        ->assertSuccessful()
        ->assertSee('M1 profile summary');
});

it('keeps regional admins scoped to their own region on the report', function () {
    $visibleLocation = createTestLocationHierarchy();
    $hiddenLocation = createTestLocationHierarchy();

    $regionalAdmin = createScopedUser('regional_admin', [
        'region_id' => $visibleLocation['region']->id,
        'district_id' => $visibleLocation['district']->id,
    ]);

    Supplier::query()->create([
        'business_name' => 'Visible Supplier',
        'contact_person' => 'Ann',
        'phone' => '256700003401',
        'operating_district_id' => $visibleLocation['district']->id,
        'supply_frequency' => SupplyFrequency::Weekly,
        'verification_status' => VerificationStatus::Verified,
        'warehouse_linked' => true,
    ]);

    Supplier::query()->create([
        'business_name' => 'Hidden Supplier',
        'contact_person' => 'Ben',
        'phone' => '256700003402',
        'operating_district_id' => $hiddenLocation['district']->id,
        'supply_frequency' => SupplyFrequency::Weekly,
        'verification_status' => VerificationStatus::Verified,
        'warehouse_linked' => true,
    ]);

    $visibleAgent = Agent::query()->create([
        'full_name' => 'Visible Agent',
        'agent_code' => 'AGT-30001',
        'phone' => '256700003403',
        'primary_district_id' => $visibleLocation['district']->id,
        'commission_rate' => 5,
        'onboarding_status' => AgentOnboardingStatus::Active,
    ]);
    $visibleAgent->regions()->sync([$visibleLocation['region']->id]);

    $hiddenAgent = Agent::query()->create([
        'full_name' => 'Hidden Agent',
        'agent_code' => 'AGT-30002',
        'phone' => '256700003404',
        'primary_district_id' => $hiddenLocation['district']->id,
        'commission_rate' => 5,
        'onboarding_status' => AgentOnboardingStatus::Active,
    ]);
    $hiddenAgent->regions()->sync([$hiddenLocation['region']->id]);

    $visibleProfile = AgribusinessProfile::query()->create([
        'entity_type' => AgribusinessEntityType::Cooperative,
        'organization_name' => 'Visible Co-op',
        'contact_person' => 'Clara',
        'contact_phone' => '256700003405',
    ]);
    $visibleProfile->districts()->sync([$visibleLocation['district']->id]);

    $hiddenProfile = AgribusinessProfile::query()->create([
        'entity_type' => AgribusinessEntityType::Cooperative,
        'organization_name' => 'Hidden Co-op',
        'contact_person' => 'David',
        'contact_phone' => '256700003406',
    ]);
    $hiddenProfile->districts()->sync([$hiddenLocation['district']->id]);

    Livewire::actingAs($regionalAdmin)
        ->test(M1ProfileSummary::class)
        ->assertSee('Total suppliers')
        ->assertSee('Verified suppliers')
        ->assertSee('Active agents');
});
