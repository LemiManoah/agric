<?php

namespace Database\Factories;

use App\Models\ValueChain;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ValueChain>
 */
class ValueChainFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Maize',
            'Beans',
            'Coffee',
            'Soybean',
            'Groundnuts',
            'Rice',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name.'-'.fake()->unique()->randomNumber(3)),
            'is_active' => true,
        ];
    }
}
