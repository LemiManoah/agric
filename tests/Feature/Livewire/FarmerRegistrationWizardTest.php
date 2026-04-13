<?php

use App\Enums\RegistrationSource;
use App\Enums\VerificationStatus;
use App\Livewire\FarmerPortal\Registration\Wizard;
use App\Models\District;
use App\Models\Farmer;
use App\Models\Parish;
use App\Models\Region;
use App\Models\Subcounty;
use App\Models\Village;
use App\Services\FarmerRegistrationService;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('renders the farmer registration wizard', function () {
    $this->get(route('farmer-portal.registration.create'))
        ->assertSuccessful()
        ->assertSee('Farmer registration wizard');
});

it('validates the first step before moving forward', function () {
    Livewire::test(Wizard::class)
        ->call('nextStep')
        ->assertHasErrors([
            'full_name',
            'phone',
        ]);
});

it('creates a farmer and normalized farmer location through the wizard', function () {
    $location = createWizardLocationHierarchy();

    Livewire::test(Wizard::class)
        ->set('full_name', 'Wizard Farmer')
        ->set('phone', '256700001000')
        ->set('registration_source', RegistrationSource::SelfRegistered->value)
        ->set('languages_spoken', 'English, Luganda')
        ->call('nextStep')
        ->set('region_id', $location['region']->id)
        ->set('district_id', $location['district']->id)
        ->set('subcounty_id', $location['subcounty']->id)
        ->set('parish_id', $location['parish']->id)
        ->set('village_id', $location['village']->id)
        ->call('nextStep')
        ->set('latitude', '0.313611')
        ->set('longitude', '32.581111')
        ->set('nearest_trading_centre', 'Kalerwe')
        ->set('distance_to_tarmac_road_km', '2.40')
        ->set('internet_access_level', '4g')
        ->call('nextStep')
        ->call('submit')
        ->assertRedirect(route('home'));

    $farmer = Farmer::query()->firstOrFail();

    expect($farmer->full_name)->toBe('Wizard Farmer')
        ->and($farmer->registration_source)->toBe(RegistrationSource::SelfRegistered)
        ->and($farmer->verification_status)->toBe(VerificationStatus::Submitted)
        ->and($farmer->location)->not->toBeNull()
        ->and($farmer->location->region_id)->toBe($location['region']->id)
        ->and($farmer->location->district_id)->toBe($location['district']->id)
        ->and($farmer->location->subcounty_id)->toBe($location['subcounty']->id)
        ->and($farmer->location->parish_id)->toBe($location['parish']->id)
        ->and($farmer->location->village_id)->toBe($location['village']->id);
});

it('uses the registration service through the livewire flow', function () {
    $location = createWizardLocationHierarchy();

    $mock = Mockery::mock(FarmerRegistrationService::class);
    $mock->shouldReceive('createFarmer')
        ->once()
        ->withArgs(function (array $payload, $actor) use ($location): bool {
            return $payload['full_name'] === 'Mocked Wizard Farmer'
                && $payload['village_id'] === $location['village']->id
                && $payload['region_id'] === $location['region']->id
                && $actor === null;
        })
        ->andReturn(Farmer::make([
            'full_name' => 'Mocked Wizard Farmer',
            'phone' => '256700001100',
            'registration_source' => RegistrationSource::SelfRegistered,
            'verification_status' => VerificationStatus::Submitted,
        ]));

    $this->app->instance(FarmerRegistrationService::class, $mock);

    Livewire::test(Wizard::class)
        ->set('full_name', 'Mocked Wizard Farmer')
        ->set('phone', '256700001100')
        ->set('registration_source', RegistrationSource::SelfRegistered->value)
        ->call('nextStep')
        ->set('region_id', $location['region']->id)
        ->set('district_id', $location['district']->id)
        ->set('subcounty_id', $location['subcounty']->id)
        ->set('parish_id', $location['parish']->id)
        ->set('village_id', $location['village']->id)
        ->call('nextStep')
        ->call('nextStep')
        ->call('submit')
        ->assertRedirect(route('home'));
});

function createWizardLocationHierarchy(): array
{
    $region = Region::query()->create([
        'name' => 'Central '.fake()->unique()->word(),
        'code' => 'UG-C-'.fake()->unique()->numerify('###'),
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
