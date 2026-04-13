<?php

namespace Database\Factories;

use App\Models\QualityGrade;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<QualityGrade>
 */
class QualityGradeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Grade A',
            'Grade B',
            'Premium',
            'Organic',
            'Export Ready',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name.'-'.fake()->unique()->randomNumber(3)),
            'is_active' => true,
        ];
    }
}
