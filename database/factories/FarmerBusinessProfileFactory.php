<?php

namespace Database\Factories;

use App\Enums\IrrigationAvailability;
use App\Models\Farmer;
use App\Models\FarmerBusinessProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FarmerBusinessProfile>
 */
class FarmerBusinessProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cooperativeMember = fake()->boolean();

        return [
            'farmer_id' => Farmer::factory(),
            'farm_name' => fake()->company().' Farm',
            'ursb_registration_number' => fake()->optional()->bothify('URSB-#####'),
            'farm_size_acres' => fake()->randomFloat(2, 1, 120),
            'number_of_plots' => fake()->numberBetween(1, 8),
            'irrigation_availability' => fake()->randomElement(IrrigationAvailability::cases()),
            'post_harvest_storage_capacity_tonnes' => fake()->randomFloat(2, 0, 25),
            'has_warehouse_access' => fake()->boolean(),
            'cooperative_member' => $cooperativeMember,
            'cooperative_name' => $cooperativeMember ? fake()->company() : null,
            'cooperative_role' => $cooperativeMember ? fake()->randomElement(['Member', 'Treasurer', 'Chairperson']) : null,
            'average_annual_income_bracket' => fake()->randomElement([
                'Below UGX 5M',
                'UGX 5M - 10M',
                'UGX 10M - 20M',
                'Above UGX 20M',
            ]),
        ];
    }
}
