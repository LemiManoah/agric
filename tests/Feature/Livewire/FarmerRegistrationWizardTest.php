<?php

use App\Enums\RegistrationSource;
use App\Enums\VerificationStatus;
use App\Livewire\FarmerPortal\Registration\Wizard;
use App\Models\District;
use App\Models\Farmer;
use App\Models\FarmerBusinessProfile;
use App\Models\FarmerValueChain;
use App\Models\Parish;
use App\Models\Region;
use App\Models\Subcounty;
use App\Models\ValueChain;
use App\Models\Village;
use App\Services\FarmerRegistrationService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
    Storage::fake(config('filesystems.default', 'public'));

    $location = createWizardLocationHierarchy();
    $valueChain = ValueChain::factory()->create();
    $photo = UploadedFile::fake()->image('farmer-passport.jpg');

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
        ->set('passport_photo', $photo)
        ->set('business_profile.farm_name', 'Green Acre Demo Farm')
        ->set('business_profile.farm_size_acres', '12.50')
        ->set('business_profile.irrigation_availability', 'seasonal')
        ->set('business_profile.cooperative_member', true)
        ->set('business_profile.cooperative_name', 'Mukono Growers Cooperative')
        ->call('nextStep')
        ->set('value_chains.0.value_chain_id', $valueChain->id)
        ->set('value_chains.0.production_scale', 'smallholder')
        ->set('value_chains.0.estimated_seasonal_harvest_kg', '850')
        ->set('value_chains.0.current_market_destination', 'local_market')
        ->set('value_chains.0.input_access_details', '{"seed":"available"}')
        ->call('nextStep')
        ->call('submit')
        ->assertRedirect(route('home'));

    $farmer = Farmer::query()->firstOrFail();
    $businessProfile = FarmerBusinessProfile::query()->firstOrFail();
    $valueChainEntry = FarmerValueChain::query()->firstOrFail();

    expect($farmer->full_name)->toBe('Wizard Farmer')
        ->and($farmer->registration_source)->toBe(RegistrationSource::SelfRegistered)
        ->and($farmer->verification_status)->toBe(VerificationStatus::Submitted)
        ->and($farmer->passport_photo_path)->not->toBeNull()
        ->and($farmer->location)->not->toBeNull()
        ->and($farmer->location->region_id)->toBe($location['region']->id)
        ->and($farmer->location->district_id)->toBe($location['district']->id)
        ->and($farmer->location->subcounty_id)->toBe($location['subcounty']->id)
        ->and($farmer->location->parish_id)->toBe($location['parish']->id)
        ->and($farmer->location->village_id)->toBe($location['village']->id)
        ->and($businessProfile->farmer_id)->toBe($farmer->id)
        ->and($businessProfile->farm_name)->toBe('Green Acre Demo Farm')
        ->and($valueChainEntry->farmer_id)->toBe($farmer->id)
        ->and($valueChainEntry->value_chain_id)->toBe($valueChain->id);

    Storage::disk(config('filesystems.default', 'public'))->assertExists($farmer->passport_photo_path);
});

it('uses the registration service through the livewire flow', function () {
    $location = createWizardLocationHierarchy();
    $valueChain = ValueChain::factory()->create();

    $mock = Mockery::mock(FarmerRegistrationService::class);
    $mock->shouldReceive('createFarmer')
        ->once()
        ->withArgs(function (array $payload, $actor) use ($location): bool {
            return $payload['full_name'] === 'Mocked Wizard Farmer'
                && $payload['village_id'] === $location['village']->id
                && $payload['region_id'] === $location['region']->id
                && ($payload['business_profile']['farm_name'] ?? null) === 'Mock Farm'
                && ($payload['value_chains'][0]['current_market_destination'] ?? null) === 'farm_gate'
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
        ->set('business_profile.farm_name', 'Mock Farm')
        ->call('nextStep')
        ->set('value_chains.0.value_chain_id', $valueChain->id)
        ->set('value_chains.0.current_market_destination', 'farm_gate')
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
