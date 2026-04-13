<?php

namespace Database\Factories;

use App\Models\Parish;
use App\Models\Subcounty;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Parish>
 */
class ParishFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subcounty_id' => Subcounty::factory(),
            'name' => fake()->unique()->streetName().' Parish',
        ];
    }
}
