<?php

namespace Database\Factories;

use App\Enums\InternetAccessLevel;
use App\Models\Farmer;
use App\Models\FarmerLocation;
use App\Models\Village;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FarmerLocation>
 */
class FarmerLocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $village = Village::factory()->create();
        $village->load('parish.subcounty.district.region');

        return [
            'farmer_id' => Farmer::factory(),
            'region_id' => $village->parish->subcounty->district->region->id,
            'district_id' => $village->parish->subcounty->district->id,
            'subcounty_id' => $village->parish->subcounty->id,
            'parish_id' => $village->parish->id,
            'village_id' => $village->id,
            'latitude' => fake()->latitude(0.02, 1.40),
            'longitude' => fake()->longitude(29.50, 35.10),
            'farm_boundary_geojson' => json_encode([
                'type' => 'Polygon',
                'coordinates' => [[
                    [32.5800, 0.3500],
                    [32.5810, 0.3500],
                    [32.5810, 0.3510],
                    [32.5800, 0.3510],
                    [32.5800, 0.3500],
                ]],
            ]),
            'nearest_trading_centre' => fake()->city(),
            'distance_to_tarmac_road_km' => fake()->randomFloat(2, 0, 20),
            'internet_access_level' => fake()->randomElement(InternetAccessLevel::cases()),
        ];
    }
}
