<?php

namespace Database\Factories;

use App\Enums\MarketDestination;
use App\Enums\ProductionScale;
use App\Models\Farmer;
use App\Models\FarmerValueChain;
use App\Models\ValueChain;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FarmerValueChain>
 */
class FarmerValueChainFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'farmer_id' => Farmer::factory(),
            'value_chain_id' => ValueChain::factory(),
            'production_scale' => fake()->randomElement(ProductionScale::cases()),
            'estimated_seasonal_harvest_kg' => fake()->randomFloat(2, 100, 12000),
            'current_market_destination' => fake()->randomElement(MarketDestination::cases()),
            'input_access_details' => [
                'fertilizer_access' => fake()->boolean(),
                'seed_source' => fake()->company(),
            ],
        ];
    }
}
