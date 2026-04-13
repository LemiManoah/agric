<?php

use App\Enums\RegistrationSource;
use App\Enums\VerificationStatus;
use App\Livewire\Admin\Farmers\Index;
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

it('shows only farmers within the signed in regional admins region', function () {
    $visibleRegion = Region::query()->create([
        'name' => 'Central',
        'code' => 'UG-C',
    ]);

    $hiddenRegion = Region::query()->create([
        'name' => 'Western',
        'code' => 'UG-W',
    ]);

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
        'phone' => '256700000001',
        'registration_source' => RegistrationSource::FieldOfficer,
        'verification_status' => VerificationStatus::Verified,
    ]);

    FarmerLocation::query()->create([
        'farmer_id' => $visibleFarmer->id,
        'region_id' => $visibleRegion->id,
        'district_id' => $visibleDistrict->id,
        'subcounty_id' => $visibleSubcounty->id,
    ]);

    $hiddenFarmer = Farmer::query()->create([
        'full_name' => 'Hidden Farmer',
        'phone' => '256700000002',
        'registration_source' => RegistrationSource::Imported,
        'verification_status' => VerificationStatus::Submitted,
    ]);

    FarmerLocation::query()->create([
        'farmer_id' => $hiddenFarmer->id,
        'region_id' => $hiddenRegion->id,
        'district_id' => $hiddenDistrict->id,
        'subcounty_id' => $hiddenSubcounty->id,
    ]);

    $this->actingAs($regionalAdmin)
        ->get(route('admin.farmers.index'))
        ->assertSuccessful()
        ->assertSee('Visible Farmer')
        ->assertDontSee('Hidden Farmer');
});

it('blocks authenticated users without farmer permissions', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.farmers.index'))
        ->assertForbidden();
});

it('filters the farmer list by search term inside the livewire component', function () {
    $region = Region::query()->create([
        'name' => 'Northern',
        'code' => 'UG-N',
    ]);

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

    $matchingFarmer = Farmer::query()->create([
        'full_name' => 'Searchable Farmer',
        'phone' => '256700000010',
        'registration_source' => RegistrationSource::SelfRegistered,
        'verification_status' => VerificationStatus::PendingReview,
    ]);

    $otherFarmer = Farmer::query()->create([
        'full_name' => 'Another Record',
        'phone' => '256700000011',
        'registration_source' => RegistrationSource::Imported,
        'verification_status' => VerificationStatus::Submitted,
    ]);

    FarmerLocation::query()->create([
        'farmer_id' => $matchingFarmer->id,
        'region_id' => $region->id,
        'district_id' => $district->id,
        'subcounty_id' => $subcounty->id,
    ]);

    FarmerLocation::query()->create([
        'farmer_id' => $otherFarmer->id,
        'region_id' => $region->id,
        'district_id' => $district->id,
        'subcounty_id' => $subcounty->id,
    ]);

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->set('search', 'Searchable')
        ->assertSee('Searchable Farmer')
        ->assertDontSee('Another Record');
});
