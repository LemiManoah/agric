<?php

use App\Models\Agent;
use App\Models\AgribusinessProfile;
use App\Models\District;
use App\Models\Farmer;
use App\Models\FarmerBusinessProfile;
use App\Models\FarmerLocation;
use App\Models\FarmerValueChain;
use App\Models\QualityGrade;
use App\Models\Supplier;
use App\Models\ValueChain;
use App\Models\Village;

it('builds a complete normalized geography chain from the village factory', function () {
    $village = Village::factory()->create();
    $village->load('parish.subcounty.district.region');

    expect($village->parish)->not->toBeNull()
        ->and($village->parish->subcounty)->not->toBeNull()
        ->and($village->parish->subcounty->district)->toBeInstanceOf(District::class)
        ->and($village->parish->subcounty->district->region)->not->toBeNull();
});

it('creates the core M1 domain models from factories', function () {
    $valueChain = ValueChain::factory()->create();
    $qualityGrade = QualityGrade::factory()->create();
    $farmer = Farmer::factory()->create();
    $location = FarmerLocation::factory()->create(['farmer_id' => $farmer->id]);
    $businessProfile = FarmerBusinessProfile::factory()->create(['farmer_id' => $farmer->id]);
    $farmerValueChain = FarmerValueChain::factory()->create([
        'farmer_id' => $farmer->id,
        'value_chain_id' => $valueChain->id,
    ]);
    $supplier = Supplier::factory()->create(['operating_district_id' => $location->district_id]);
    $agent = Agent::factory()->create(['primary_district_id' => $location->district_id]);
    $agribusinessProfile = AgribusinessProfile::factory()->create();

    $supplier->valueChains()->sync([$valueChain->id]);
    $supplier->qualityGrades()->sync([$qualityGrade->id]);
    $agent->valueChains()->sync([$valueChain->id]);
    $agent->regions()->sync([$location->region_id]);
    $agribusinessProfile->districts()->sync([$location->district_id]);

    expect($location->region_id)->toBe($location->district->region_id)
        ->and($businessProfile->farmer_id)->toBe($farmer->id)
        ->and($farmerValueChain->value_chain_id)->toBe($valueChain->id)
        ->and($supplier->valueChains()->count())->toBe(1)
        ->and($supplier->qualityGrades()->count())->toBe(1)
        ->and($agent->regions()->count())->toBe(1)
        ->and($agribusinessProfile->districts()->count())->toBe(1);
});
