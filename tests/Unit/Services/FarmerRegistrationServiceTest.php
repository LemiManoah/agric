<?php

use App\Enums\RegistrationSource;
use App\Enums\VerificationStatus;
use App\Models\District;
use App\Models\Farmer;
use App\Models\Parish;
use App\Models\Region;
use App\Models\Subcounty;
use App\Models\User;
use App\Models\Village;
use App\Services\FarmerRegistrationService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\QueryException;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('creates a farmer with a normalized location and activity log entry', function () {
    $service = app(FarmerRegistrationService::class);
    $location = createServiceLocationHierarchy();

    $actor = User::factory()->create([
        'region_id' => $location['region']->id,
        'district_id' => $location['district']->id,
    ]);
    $actor->assignRole('super_admin');

    $farmer = $service->createFarmer([
        'full_name' => 'Service Farmer',
        'phone' => '256700002000',
        'registration_source' => RegistrationSource::FieldOfficer->value,
        'languages_spoken' => 'English, Luganda',
        'region_id' => $location['region']->id,
        'district_id' => $location['district']->id,
        'subcounty_id' => $location['subcounty']->id,
        'parish_id' => $location['parish']->id,
        'village_id' => $location['village']->id,
        'latitude' => '0.333333',
        'longitude' => '32.577777',
    ], $actor);

    expect($farmer->exists)->toBeTrue()
        ->and($farmer->registered_by_user_id)->toBe($actor->id)
        ->and($farmer->location->village_id)->toBe($location['village']->id);

    expect(Activity::query()->where('description', 'farmer.created')->exists())->toBeTrue();
});

it('updates a farmer and refreshes the linked location inside a transaction', function () {
    $service = app(FarmerRegistrationService::class);
    $originalLocation = createServiceLocationHierarchy();
    $updatedLocation = createServiceLocationHierarchy();

    $actor = User::factory()->create([
        'region_id' => $updatedLocation['region']->id,
        'district_id' => $updatedLocation['district']->id,
    ]);
    $actor->assignRole('super_admin');

    $farmer = Farmer::query()->create([
        'full_name' => 'Original Farmer',
        'phone' => '256700002100',
        'registration_source' => RegistrationSource::FieldOfficer,
        'verification_status' => VerificationStatus::Submitted,
    ]);

    $farmer->location()->create([
        'region_id' => $originalLocation['region']->id,
        'district_id' => $originalLocation['district']->id,
        'subcounty_id' => $originalLocation['subcounty']->id,
        'parish_id' => $originalLocation['parish']->id,
        'village_id' => $originalLocation['village']->id,
    ]);

    $updatedFarmer = $service->updateFarmer($farmer, [
        'full_name' => 'Updated Farmer',
        'phone' => '256700002101',
        'region_id' => $updatedLocation['region']->id,
        'district_id' => $updatedLocation['district']->id,
        'subcounty_id' => $updatedLocation['subcounty']->id,
        'parish_id' => $updatedLocation['parish']->id,
        'village_id' => $updatedLocation['village']->id,
        'nearest_trading_centre' => 'Nakasero',
    ], $actor);

    expect($updatedFarmer->full_name)->toBe('Updated Farmer')
        ->and($updatedFarmer->phone)->toBe('256700002101')
        ->and($updatedFarmer->location->region_id)->toBe($updatedLocation['region']->id)
        ->and($updatedFarmer->location->village_id)->toBe($updatedLocation['village']->id);

    expect(Activity::query()->where('description', 'farmer.updated')->exists())->toBeTrue();
});

it('verifies a farmer and records the verification activity entry', function () {
    $service = app(FarmerRegistrationService::class);
    $location = createServiceLocationHierarchy();

    $actor = User::factory()->create([
        'region_id' => $location['region']->id,
        'district_id' => $location['district']->id,
    ]);
    $actor->assignRole('super_admin');

    $farmer = Farmer::query()->create([
        'full_name' => 'Verification Farmer',
        'phone' => '256700002200',
        'registration_source' => RegistrationSource::FieldOfficer,
        'verification_status' => VerificationStatus::PendingReview,
    ]);

    $farmer->location()->create([
        'region_id' => $location['region']->id,
        'district_id' => $location['district']->id,
        'subcounty_id' => $location['subcounty']->id,
        'parish_id' => $location['parish']->id,
        'village_id' => $location['village']->id,
    ]);

    $verifiedFarmer = $service->verifyFarmer($farmer, $actor);

    expect($verifiedFarmer->verification_status)->toBe(VerificationStatus::Verified)
        ->and($verifiedFarmer->verified_by_user_id)->toBe($actor->id)
        ->and($verifiedFarmer->verified_at)->not->toBeNull();

    expect(Activity::query()->where('description', 'farmer.verified')->exists())->toBeTrue();
});

it('rolls back farmer persistence when the location write fails', function () {
    $service = app(FarmerRegistrationService::class);

    expect(fn () => $service->createFarmer([
        'full_name' => 'Broken Farmer',
        'phone' => '256700002300',
        'registration_source' => RegistrationSource::SelfRegistered->value,
        'region_id' => 999999,
        'district_id' => 999999,
        'subcounty_id' => 999999,
        'village_id' => 999999,
    ]))->toThrow(QueryException::class);

    expect(Farmer::query()->count())->toBe(0);
});

function createServiceLocationHierarchy(): array
{
    $region = Region::query()->create([
        'name' => 'Region '.fake()->unique()->word(),
        'code' => 'UG-R-'.fake()->unique()->numerify('###'),
    ]);

    $district = District::query()->create([
        'region_id' => $region->id,
        'name' => 'District '.fake()->unique()->word(),
        'code' => 'UG-D-'.fake()->unique()->numerify('###'),
    ]);

    $subcounty = Subcounty::query()->create([
        'district_id' => $district->id,
        'name' => 'Subcounty '.fake()->unique()->word(),
        'code' => 'UG-S-'.fake()->unique()->numerify('###'),
    ]);

    $parish = Parish::query()->create([
        'subcounty_id' => $subcounty->id,
        'name' => 'Parish '.fake()->unique()->word(),
    ]);

    $village = Village::query()->create([
        'parish_id' => $parish->id,
        'name' => 'Village '.fake()->unique()->word(),
    ]);

    return compact('region', 'district', 'subcounty', 'parish', 'village');
}
