<?php

use App\Enums\AgribusinessEntityType;
use App\Livewire\Admin\AgribusinessProfiles\Form;
use App\Livewire\Admin\AgribusinessProfiles\Index;
use App\Models\AgribusinessProfile;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('renders the agribusiness profile index for a super admin', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $this->actingAs($user)
        ->get(route('admin.agribusiness-profiles.index'))
        ->assertSuccessful()
        ->assertSee('Agribusiness profiles');
});

it('limits agribusiness profiles to the regional admin scope', function () {
    $visibleLocation = createTestLocationHierarchy();
    $hiddenLocation = createTestLocationHierarchy();

    $regionalAdmin = createScopedUser('regional_admin', [
        'region_id' => $visibleLocation['region']->id,
        'district_id' => $visibleLocation['district']->id,
    ]);

    $visibleProfile = AgribusinessProfile::query()->create([
        'entity_type' => AgribusinessEntityType::Cooperative,
        'organization_name' => 'Visible Cooperative',
        'contact_person' => 'Anna',
        'contact_phone' => '256700003200',
    ]);
    $visibleProfile->districts()->sync([$visibleLocation['district']->id]);

    $hiddenProfile = AgribusinessProfile::query()->create([
        'entity_type' => AgribusinessEntityType::ExportCompany,
        'organization_name' => 'Hidden Exporter',
        'contact_person' => 'Paul',
        'contact_phone' => '256700003201',
    ]);
    $hiddenProfile->districts()->sync([$hiddenLocation['district']->id]);

    Livewire::actingAs($regionalAdmin)
        ->test(Index::class)
        ->assertSee('Visible Cooperative')
        ->assertDontSee('Hidden Exporter');
});

it('renders the agribusiness profile form for an authorized admin', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $location = createTestLocationHierarchy();

    Livewire::actingAs($admin)
        ->test(Form::class)
        ->assertSee('Create agribusiness profile')
        ->assertSee('Covered districts')
        ->assertSee($location['district']->name);
});
