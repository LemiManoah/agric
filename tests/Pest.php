<?php

use App\Enums\RegistrationSource;
use App\Enums\VerificationStatus;
use App\Models\District;
use App\Models\Farmer;
use App\Models\FarmerLocation;
use App\Models\Parish;
use App\Models\Region;
use App\Models\Subcounty;
use App\Models\User;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

function createTestLocationHierarchy(): array
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

function createScopedUser(string $role, array $attributes = []): User
{
    $user = User::factory()->create($attributes);
    $user->assignRole($role);

    return $user;
}

function createFarmerRecord(array $farmerAttributes = [], array $locationAttributes = []): Farmer
{
    $location = createTestLocationHierarchy();

    $farmer = Farmer::query()->create(array_merge([
        'full_name' => 'Farmer '.fake()->unique()->name(),
        'phone' => fake()->unique()->numerify('25670000####'),
        'registration_source' => RegistrationSource::FieldOfficer,
        'verification_status' => VerificationStatus::Submitted,
    ], $farmerAttributes));

    FarmerLocation::query()->create(array_merge([
        'farmer_id' => $farmer->id,
        'region_id' => $location['region']->id,
        'district_id' => $location['district']->id,
        'subcounty_id' => $location['subcounty']->id,
        'parish_id' => $location['parish']->id,
        'village_id' => $location['village']->id,
    ], $locationAttributes));

    return $farmer->fresh(['location.region', 'location.district', 'location.subcounty', 'location.parish', 'location.village']);
}
