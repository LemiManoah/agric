<?php

use App\Enums\RegistrationSource;
use App\Enums\VerificationStatus;
use App\Livewire\Admin\Farmers\Map;
use App\Models\District;
use App\Models\Farmer;
use App\Models\FarmerLocation;
use App\Models\Region;
use App\Models\Subcounty;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('allows a super admin to access the farmer map page', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $this->actingAs($user)
        ->get(route('admin.farmers.map'))
        ->assertSuccessful()
        ->assertSee('Farm location map');
});

it('limits a regional admin to farmers within their assigned region on the map', function () {
    $visibleRegion = Region::query()->create(['name' => 'Central', 'code' => 'UG-C']);
    $hiddenRegion = Region::query()->create(['name' => 'Western', 'code' => 'UG-W']);

    $visibleDistrict = District::query()->create([
        'region_id' => $visibleRegion->id,
        'name' => 'Kampala',
        'code' => 'UG-C-KLA',
    ]);
    $hiddenDistrict = District::query()->create([
        'region_id' => $hiddenRegion->id,
        'name' => 'Mbarara',
        'code' => 'UG-W-MBA',
    ]);

    $visibleSubcounty = Subcounty::query()->create([
        'district_id' => $visibleDistrict->id,
        'name' => 'Nakawa',
        'code' => 'UG-C-KLA-NAK',
    ]);
    $hiddenSubcounty = Subcounty::query()->create([
        'district_id' => $hiddenDistrict->id,
        'name' => 'Biharwe',
        'code' => 'UG-W-MBA-BIH',
    ]);

    $regionalAdmin = User::factory()->create([
        'region_id' => $visibleRegion->id,
        'district_id' => $visibleDistrict->id,
    ]);
    $regionalAdmin->assignRole('regional_admin');

    $visibleFarmer = Farmer::query()->create([
        'full_name' => 'Visible Farmer',
        'phone' => '256700000100',
        'registration_source' => RegistrationSource::FieldOfficer,
        'verification_status' => VerificationStatus::Verified,
    ]);
    FarmerLocation::query()->create([
        'farmer_id' => $visibleFarmer->id,
        'region_id' => $visibleRegion->id,
        'district_id' => $visibleDistrict->id,
        'subcounty_id' => $visibleSubcounty->id,
        'latitude' => 0.347596,
        'longitude' => 32.582520,
    ]);

    $hiddenFarmer = Farmer::query()->create([
        'full_name' => 'Hidden Farmer',
        'phone' => '256700000101',
        'registration_source' => RegistrationSource::Imported,
        'verification_status' => VerificationStatus::Submitted,
    ]);
    FarmerLocation::query()->create([
        'farmer_id' => $hiddenFarmer->id,
        'region_id' => $hiddenRegion->id,
        'district_id' => $hiddenDistrict->id,
        'subcounty_id' => $hiddenSubcounty->id,
        'latitude' => -0.607159,
        'longitude' => 30.654503,
    ]);

    Livewire::actingAs($regionalAdmin)
        ->test(Map::class)
        ->assertSet('visibleFarmers', 1)
        ->assertSee('Visible Farmer')
        ->assertDontSee('Hidden Farmer');
});

it('blocks users without the farmer map permission', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.farmers.map'))
        ->assertForbidden();
});

it('returns only farmers with coordinates in the map dataset', function () {
    $region = Region::query()->create(['name' => 'Northern', 'code' => 'UG-N']);
    $district = District::query()->create([
        'region_id' => $region->id,
        'name' => 'Gulu',
        'code' => 'UG-N-GUL',
    ]);
    $subcounty = Subcounty::query()->create([
        'district_id' => $district->id,
        'name' => 'Pece-Laroo',
        'code' => 'UG-N-GUL-PEC',
    ]);

    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $mappedFarmer = Farmer::query()->create([
        'full_name' => 'Mapped Farmer',
        'phone' => '256700000200',
        'registration_source' => RegistrationSource::FieldOfficer,
        'verification_status' => VerificationStatus::Verified,
    ]);
    FarmerLocation::query()->create([
        'farmer_id' => $mappedFarmer->id,
        'region_id' => $region->id,
        'district_id' => $district->id,
        'subcounty_id' => $subcounty->id,
        'latitude' => 2.774569,
        'longitude' => 32.298989,
    ]);

    $unmappedFarmer = Farmer::query()->create([
        'full_name' => 'Unmapped Farmer',
        'phone' => '256700000201',
        'registration_source' => RegistrationSource::SelfRegistered,
        'verification_status' => VerificationStatus::Submitted,
    ]);
    FarmerLocation::query()->create([
        'farmer_id' => $unmappedFarmer->id,
        'region_id' => $region->id,
        'district_id' => $district->id,
        'subcounty_id' => $subcounty->id,
    ]);

    Livewire::actingAs($admin)
        ->test(Map::class)
        ->assertSet('visibleFarmers', 1)
        ->assertSee('Mapped Farmer')
        ->assertDontSee('Unmapped Farmer')
        ->assertSet('mapPoints.0.name', 'Mapped Farmer');
});
