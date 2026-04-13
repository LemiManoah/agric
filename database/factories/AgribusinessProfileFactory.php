<?php

namespace Database\Factories;

use App\Enums\AgribusinessEntityType;
use App\Models\AgribusinessProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AgribusinessProfile>
 */
class AgribusinessProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'entity_type' => fake()->randomElement(AgribusinessEntityType::cases()),
            'organization_name' => fake()->company(),
            'registration_number' => fake()->optional()->bothify('REG-#####'),
            'membership_size' => fake()->optional()->numberBetween(20, 400),
            'fleet_size' => fake()->optional()->numberBetween(1, 25),
            'service_rates' => fake()->optional()->sentence(),
            'product_range' => fake()->optional()->sentence(),
            'processing_capacity_tonnes_per_day' => fake()->optional()->randomFloat(2, 2, 80),
            'export_markets' => fake()->optional()->sentence(),
            'buyer_criteria' => fake()->optional()->sentence(),
            'contact_person' => fake()->name(),
            'contact_phone' => fake()->unique()->numerify('25670#######'),
        ];
    }
}
