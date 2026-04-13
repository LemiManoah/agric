<?php

namespace Database\Factories;

use App\Enums\VerificationStatus;
use App\Models\Buyer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Buyer>
 */
class BuyerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_name' => fake()->company(),
            'country' => fake()->country(),
            'business_type' => fake()->randomElement(['Importer', 'Wholesaler', 'Retailer', 'Processor']),
            'company_registration_number' => fake()->optional()->bothify('REG-#####'),
            'contact_person_full_name' => fake()->name(),
            'phone' => fake()->unique()->numerify('25670#######'),
            'email' => fake()->unique()->safeEmail(),
            'annual_import_volume_usd_range' => fake()->optional()->randomElement(['Below 50K', '50K - 250K', '250K - 1M', 'Above 1M']),
            'preferred_payment_method' => fake()->optional()->randomElement(['Bank transfer', 'Letter of credit', 'Mobile money']),
            'verification_status' => VerificationStatus::Submitted,
        ];
    }
}
