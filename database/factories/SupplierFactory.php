<?php

namespace Database\Factories;

use App\Enums\SupplyFrequency;
use App\Enums\VerificationStatus;
use App\Models\District;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'business_name' => fake()->company(),
            'contact_person' => fake()->name(),
            'phone' => fake()->unique()->numerify('25670#######'),
            'email' => fake()->unique()->safeEmail(),
            'operating_district_id' => District::factory(),
            'typical_supply_volume_kg_per_month' => fake()->randomFloat(2, 100, 15000),
            'supply_frequency' => fake()->randomElement(SupplyFrequency::cases()),
            'warehouse_linked' => fake()->boolean(),
            'verification_status' => VerificationStatus::Submitted,
        ];
    }
}
