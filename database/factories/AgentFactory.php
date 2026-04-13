<?php

namespace Database\Factories;

use App\Enums\AgentOnboardingStatus;
use App\Models\Agent;
use App\Models\District;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Agent>
 */
class AgentFactory extends Factory
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
            'agent_code' => 'AGT-'.fake()->unique()->numerify('#####'),
            'phone' => fake()->unique()->numerify('25670#######'),
            'email' => fake()->unique()->safeEmail(),
            'primary_district_id' => District::factory(),
            'commission_rate' => fake()->randomFloat(2, 0, 12),
            'total_orders_placed' => fake()->numberBetween(0, 40),
            'total_commission_earned' => fake()->randomFloat(2, 0, 2000000),
            'onboarding_status' => fake()->randomElement(AgentOnboardingStatus::cases()),
        ];
    }
}
