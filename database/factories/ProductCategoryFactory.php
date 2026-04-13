<?php

namespace Database\Factories;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductCategory>
 */
class ProductCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Grains', 'Pulses', 'Processed Foods', 'Fresh Produce', 'Inputs']);

        return [
            'name' => $name,
            'slug' => Str::slug($name.'-'.fake()->unique()->randomNumber(3)),
            'is_active' => true,
        ];
    }
}
