<?php

namespace Database\Factories;

use App\Enums\RegistrationSource;
use App\Enums\VerificationStatus;
use App\Models\Farmer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Farmer>
 */
class FarmerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'phone' => fake()->unique()->numerify('25670#######'),
            'national_id_number' => fake()->unique()->bothify('C###########??'),
            'gender' => fake()->randomElement(['male', 'female']),
            'date_of_birth' => fake()->dateTimeBetween('-60 years', '-18 years'),
            'education_level' => fake()->randomElement(['Primary', 'Secondary', 'Diploma', 'Degree']),
            'profession' => fake()->randomElement(['Farmer', 'Trader', 'Processor']),
            'household_size' => fake()->numberBetween(1, 12),
            'number_of_dependants' => fake()->numberBetween(0, 8),
            'languages_spoken' => fake()->randomElements(['English', 'Luganda', 'Runyankole', 'Swahili'], fake()->numberBetween(1, 3)),
            'registration_source' => RegistrationSource::FieldOfficer,
            'verification_status' => VerificationStatus::Submitted,
        ];
    }
}
