<?php

namespace Database\Seeders;

use App\Enums\AgentOnboardingStatus;
use App\Enums\AgribusinessEntityType;
use App\Enums\InternetAccessLevel;
use App\Enums\IrrigationAvailability;
use App\Enums\MarketDestination;
use App\Enums\ProductionScale;
use App\Enums\RegistrationSource;
use App\Enums\SupplyFrequency;
use App\Enums\VerificationStatus;
use App\Models\Agent;
use App\Models\AgribusinessProfile;
use App\Models\District;
use App\Models\Farmer;
use App\Models\FarmerBusinessProfile;
use App\Models\FarmerLocation;
use App\Models\FarmerValueChain;
use App\Models\Parish;
use App\Models\QualityGrade;
use App\Models\Region;
use App\Models\Subcounty;
use App\Models\Supplier;
use App\Models\User;
use App\Models\ValueChain;
use App\Models\Village;
use Illuminate\Database\Seeder;

class M1DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $valueChains = collect([
            ['name' => 'Maize', 'slug' => 'maize'],
            ['name' => 'Beans', 'slug' => 'beans'],
            ['name' => 'Coffee', 'slug' => 'coffee'],
            ['name' => 'Soybean', 'slug' => 'soybean'],
        ])->mapWithKeys(function (array $attributes): array {
            $valueChain = ValueChain::query()->firstOrCreate(
                ['slug' => $attributes['slug']],
                ['name' => $attributes['name'], 'is_active' => true],
            );

            return [$attributes['slug'] => $valueChain];
        });

        $qualityGrades = collect([
            ['name' => 'Grade A', 'slug' => 'grade-a'],
            ['name' => 'Grade B', 'slug' => 'grade-b'],
            ['name' => 'Organic', 'slug' => 'organic'],
        ])->mapWithKeys(function (array $attributes): array {
            $qualityGrade = QualityGrade::query()->firstOrCreate(
                ['slug' => $attributes['slug']],
                ['name' => $attributes['name'], 'is_active' => true],
            );

            return [$attributes['slug'] => $qualityGrade];
        });

        $superAdmin = User::query()->where('email', 'superadmin@agrofresh.test')->first();
        $regionalAdmin = User::query()->where('email', 'regional.admin@agrofresh.test')->first();
        $fieldOfficer = User::query()->where('email', 'field.officer@agrofresh.test')->first();

        $primaryLocation = $this->resolveLocation();
        $secondaryLocation = $this->resolveLocation($regionalAdmin?->region_id, $primaryLocation['region']->id);

        $farmers = collect([
            [
                'phone' => '256701100101',
                'full_name' => 'Demo Farmer Grace',
                'national_id_number' => 'CF12345678901234',
                'gender' => 'female',
                'registration_source' => RegistrationSource::FieldOfficer,
                'verification_status' => VerificationStatus::Verified,
                'registered_by_user_id' => $fieldOfficer?->id,
                'verified_by_user_id' => $regionalAdmin?->id ?? $superAdmin?->id,
                'verified_at' => now()->subDays(4),
                'location' => [
                    ...$primaryLocation,
                    'latitude' => 0.347596,
                    'longitude' => 32.582520,
                    'nearest_trading_centre' => 'Nakasero',
                    'distance_to_tarmac_road_km' => 1.20,
                    'internet_access_level' => InternetAccessLevel::FourG,
                ],
                'business' => [
                    'farm_name' => 'Grace Mixed Farm',
                    'farm_size_acres' => 6.5,
                    'number_of_plots' => 3,
                    'irrigation_availability' => IrrigationAvailability::Seasonal,
                    'post_harvest_storage_capacity_tonnes' => 4.5,
                    'has_warehouse_access' => true,
                    'cooperative_member' => true,
                    'cooperative_name' => 'Central Growers SACCO',
                    'cooperative_role' => 'Member',
                    'average_annual_income_bracket' => 'UGX 5M - 10M',
                ],
                'value_chains' => [
                    [
                        'value_chain' => $valueChains['maize'],
                        'production_scale' => ProductionScale::Smallholder,
                        'estimated_seasonal_harvest_kg' => 2400,
                        'current_market_destination' => MarketDestination::Cooperative,
                    ],
                    [
                        'value_chain' => $valueChains['beans'],
                        'production_scale' => ProductionScale::Smallholder,
                        'estimated_seasonal_harvest_kg' => 1200,
                        'current_market_destination' => MarketDestination::LocalMarket,
                    ],
                ],
            ],
            [
                'phone' => '256701100102',
                'full_name' => 'Demo Farmer Patrick',
                'national_id_number' => 'CM22345678901234',
                'gender' => 'male',
                'registration_source' => RegistrationSource::SelfRegistered,
                'verification_status' => VerificationStatus::Submitted,
                'registered_by_user_id' => null,
                'verified_by_user_id' => null,
                'verified_at' => null,
                'location' => [
                    ...$secondaryLocation,
                    'latitude' => 0.313611,
                    'longitude' => 32.581111,
                    'nearest_trading_centre' => 'Wakiso Town',
                    'distance_to_tarmac_road_km' => 3.80,
                    'internet_access_level' => InternetAccessLevel::ThreeG,
                ],
                'business' => [
                    'farm_name' => 'Patrick Produce Hub',
                    'farm_size_acres' => 12.0,
                    'number_of_plots' => 4,
                    'irrigation_availability' => IrrigationAvailability::YearRound,
                    'post_harvest_storage_capacity_tonnes' => 8.0,
                    'has_warehouse_access' => false,
                    'cooperative_member' => false,
                    'average_annual_income_bracket' => 'UGX 10M - 20M',
                ],
                'value_chains' => [
                    [
                        'value_chain' => $valueChains['coffee'],
                        'production_scale' => ProductionScale::MediumScale,
                        'estimated_seasonal_harvest_kg' => 3400,
                        'current_market_destination' => MarketDestination::Processor,
                    ],
                ],
            ],
            [
                'phone' => '256701100103',
                'full_name' => 'Demo Farmer Amina',
                'national_id_number' => 'CF32345678901234',
                'gender' => 'female',
                'registration_source' => RegistrationSource::FieldOfficer,
                'verification_status' => VerificationStatus::PendingReview,
                'registered_by_user_id' => $fieldOfficer?->id,
                'verified_by_user_id' => null,
                'verified_at' => null,
                'location' => [
                    ...$primaryLocation,
                    'latitude' => 0.285420,
                    'longitude' => 32.554978,
                    'nearest_trading_centre' => 'Busega',
                    'distance_to_tarmac_road_km' => 2.50,
                    'internet_access_level' => InternetAccessLevel::FourG,
                ],
                'business' => [
                    'farm_name' => 'Amina Agro Acres',
                    'farm_size_acres' => 4.25,
                    'number_of_plots' => 2,
                    'irrigation_availability' => IrrigationAvailability::None,
                    'post_harvest_storage_capacity_tonnes' => 2.0,
                    'has_warehouse_access' => false,
                    'cooperative_member' => true,
                    'cooperative_name' => 'Women Farmers Circle',
                    'cooperative_role' => 'Treasurer',
                    'average_annual_income_bracket' => 'Below UGX 5M',
                ],
                'value_chains' => [
                    [
                        'value_chain' => $valueChains['soybean'],
                        'production_scale' => ProductionScale::Smallholder,
                        'estimated_seasonal_harvest_kg' => 900,
                        'current_market_destination' => MarketDestination::FarmGate,
                    ],
                ],
            ],
        ])->map(fn (array $payload): Farmer => $this->seedFarmerRecord($payload));

        $grace = $farmers->firstWhere('phone', '256701100101');
        $patrick = $farmers->firstWhere('phone', '256701100102');

        foreach ([
            [
                'business_name' => 'Grace Grain Suppliers',
                'contact_person' => 'Grace',
                'phone' => '256702200101',
                'email' => 'grace.supplier@agrofresh.test',
                'farmer_id' => $grace?->id,
                'operating_district_id' => $grace?->location?->district_id,
                'typical_supply_volume_kg_per_month' => 3200,
                'supply_frequency' => SupplyFrequency::Weekly,
                'warehouse_linked' => true,
                'verification_status' => VerificationStatus::Verified,
                'verified_at' => now()->subDays(2),
                'verified_by_user_id' => $superAdmin?->id,
                'created_by' => $regionalAdmin?->id ?? $superAdmin?->id,
                'value_chains' => [$valueChains['maize']->id, $valueChains['beans']->id],
                'quality_grades' => [$qualityGrades['grade-a']->id, $qualityGrades['organic']->id],
            ],
            [
                'business_name' => 'Patrick Coffee Traders',
                'contact_person' => 'Patrick',
                'phone' => '256702200102',
                'email' => 'patrick.supplier@agrofresh.test',
                'farmer_id' => $patrick?->id,
                'operating_district_id' => $patrick?->location?->district_id,
                'typical_supply_volume_kg_per_month' => 5400,
                'supply_frequency' => SupplyFrequency::Monthly,
                'warehouse_linked' => false,
                'verification_status' => VerificationStatus::Submitted,
                'verified_at' => null,
                'verified_by_user_id' => null,
                'created_by' => $regionalAdmin?->id ?? $superAdmin?->id,
                'value_chains' => [$valueChains['coffee']->id],
                'quality_grades' => [$qualityGrades['grade-b']->id],
            ],
        ] as $supplierData) {
            $supplier = Supplier::query()->firstOrNew(['phone' => $supplierData['phone']]);
            $supplier->fill(collect($supplierData)->except(['value_chains', 'quality_grades'])->all());
            $supplier->save();
            $supplier->valueChains()->sync($supplierData['value_chains']);
            $supplier->qualityGrades()->sync($supplierData['quality_grades']);
        }

        foreach ([
            [
                'agent_code' => 'AGT-10001',
                'full_name' => 'Demo Agent Sarah',
                'phone' => '256703300101',
                'email' => 'agent.sarah@agrofresh.test',
                'primary_district_id' => $primaryLocation['district']->id,
                'commission_rate' => 4.50,
                'total_orders_placed' => 14,
                'total_commission_earned' => 750000,
                'onboarding_status' => AgentOnboardingStatus::Active,
                'created_by' => $superAdmin?->id,
                'region_ids' => [$primaryLocation['region']->id],
                'value_chain_ids' => [$valueChains['maize']->id, $valueChains['beans']->id],
            ],
            [
                'agent_code' => 'AGT-10002',
                'full_name' => 'Demo Agent Joel',
                'phone' => '256703300102',
                'email' => 'agent.joel@agrofresh.test',
                'primary_district_id' => $secondaryLocation['district']->id,
                'commission_rate' => 3.25,
                'total_orders_placed' => 5,
                'total_commission_earned' => 210000,
                'onboarding_status' => AgentOnboardingStatus::Onboarding,
                'created_by' => $superAdmin?->id,
                'region_ids' => [$secondaryLocation['region']->id],
                'value_chain_ids' => [$valueChains['coffee']->id],
            ],
        ] as $agentData) {
            $agent = Agent::query()->firstOrNew(['agent_code' => $agentData['agent_code']]);
            $agent->fill(collect($agentData)->except(['region_ids', 'value_chain_ids'])->all());
            $agent->save();
            $agent->regions()->sync($agentData['region_ids']);
            $agent->valueChains()->sync($agentData['value_chain_ids']);
        }

        foreach ([
            [
                'organization_name' => 'Central Growers Cooperative',
                'entity_type' => AgribusinessEntityType::Cooperative,
                'registration_number' => 'COOP-001',
                'membership_size' => 82,
                'service_rates' => 'Seasonal aggregation and transport support.',
                'product_range' => 'Maize, beans, soybean',
                'contact_person' => 'Ruth Namara',
                'contact_phone' => '256704400101',
                'created_by' => $superAdmin?->id,
                'district_ids' => [$primaryLocation['district']->id],
            ],
            [
                'organization_name' => 'Lakeview Agro Processing',
                'entity_type' => AgribusinessEntityType::GrainMiller,
                'registration_number' => 'MILL-002',
                'processing_capacity_tonnes_per_day' => 18.5,
                'buyer_criteria' => 'Moisture below 13%, clean grain, bagged on delivery.',
                'contact_person' => 'Isaac Ssembatya',
                'contact_phone' => '256704400102',
                'created_by' => $superAdmin?->id,
                'district_ids' => [$secondaryLocation['district']->id],
            ],
        ] as $profileData) {
            $profile = AgribusinessProfile::query()->firstOrNew([
                'organization_name' => $profileData['organization_name'],
            ]);
            $profile->fill(collect($profileData)->except(['district_ids'])->all());
            $profile->save();
            $profile->districts()->sync($profileData['district_ids']);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function seedFarmerRecord(array $payload): Farmer
    {
        $farmer = Farmer::query()->firstOrNew(['phone' => $payload['phone']]);
        $farmer->fill(collect($payload)->except(['location', 'business', 'value_chains'])->all());
        $farmer->save();

        FarmerLocation::query()->updateOrCreate(
            ['farmer_id' => $farmer->id],
            [
                'region_id' => $payload['location']['region']->id,
                'district_id' => $payload['location']['district']->id,
                'subcounty_id' => $payload['location']['subcounty']->id,
                'parish_id' => $payload['location']['parish']->id,
                'village_id' => $payload['location']['village']->id,
                'latitude' => $payload['location']['latitude'],
                'longitude' => $payload['location']['longitude'],
                'nearest_trading_centre' => $payload['location']['nearest_trading_centre'],
                'distance_to_tarmac_road_km' => $payload['location']['distance_to_tarmac_road_km'],
                'internet_access_level' => $payload['location']['internet_access_level'],
                'farm_boundary_geojson' => $this->defaultBoundaryGeoJson(
                    (float) $payload['location']['latitude'],
                    (float) $payload['location']['longitude'],
                ),
            ],
        );

        FarmerBusinessProfile::query()->updateOrCreate(
            ['farmer_id' => $farmer->id],
            $payload['business'],
        );

        foreach ($payload['value_chains'] as $entry) {
            FarmerValueChain::query()->updateOrCreate(
                [
                    'farmer_id' => $farmer->id,
                    'value_chain_id' => $entry['value_chain']->id,
                ],
                [
                    'production_scale' => $entry['production_scale'],
                    'estimated_seasonal_harvest_kg' => $entry['estimated_seasonal_harvest_kg'],
                    'current_market_destination' => $entry['current_market_destination'],
                    'input_access_details' => [
                        'seed_source' => 'Demo agro input shop',
                        'last_updated' => now()->toDateString(),
                    ],
                ],
            );
        }

        return $farmer->fresh(['location', 'businessProfile', 'valueChainEntries']);
    }

    /**
     * @return array{region: Region, district: District, subcounty: Subcounty, parish: Parish, village: Village}
     */
    private function resolveLocation(?int $preferredRegionId = null, ?int $excludeRegionId = null): array
    {
        $village = Village::query()
            ->with('parish.subcounty.district.region')
            ->when($preferredRegionId, function ($query, int $preferredRegionId): void {
                $query->whereHas('parish.subcounty.district', function ($districtQuery) use ($preferredRegionId): void {
                    $districtQuery->where('region_id', $preferredRegionId);
                });
            })
            ->when($excludeRegionId, function ($query, int $excludeRegionId): void {
                $query->whereHas('parish.subcounty.district', function ($districtQuery) use ($excludeRegionId): void {
                    $districtQuery->where('region_id', '!=', $excludeRegionId);
                });
            })
            ->orderBy('name')
            ->first();

        if (! $village) {
            $village = Village::factory()->create();
            $village->load('parish.subcounty.district.region');
        }

        $parish = $village->parish;
        $subcounty = $parish->subcounty;
        $district = $subcounty->district;
        $region = $district->region;

        return compact('region', 'district', 'subcounty', 'parish', 'village');
    }

    private function defaultBoundaryGeoJson(float $latitude, float $longitude): string
    {
        $offset = 0.0015;

        return json_encode([
            'type' => 'Polygon',
            'coordinates' => [[
                [$longitude, $latitude],
                [$longitude + $offset, $latitude],
                [$longitude + $offset, $latitude + $offset],
                [$longitude, $latitude + $offset],
                [$longitude, $latitude],
            ]],
        ], JSON_THROW_ON_ERROR);
    }
}
