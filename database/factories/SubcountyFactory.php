<?php

namespace Database\Factories;

use App\Models\District;
use App\Models\Subcounty;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Subcounty>
 */
class SubcountyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'district_id' => District::factory(),
            'name' => fake()->unique()->streetName().' Subcounty',
            'code' => 'UG-S-'.Str::upper(fake()->unique()->lexify('???')),
        ];
    }
}
