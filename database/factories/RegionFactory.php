<?php

namespace Database\Factories;

use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Region>
 */
class RegionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Central', 'Eastern', 'Northern', 'Western']).' '.fake()->unique()->citySuffix(),
            'code' => 'UG-'.Str::upper(fake()->unique()->lexify('??')),
        ];
    }
}
