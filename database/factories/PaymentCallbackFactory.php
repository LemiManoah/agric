<?php

namespace Database\Factories;

use App\Models\PaymentCallback;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentCallback>
 */
class PaymentCallbackFactory extends Factory
{
    public function definition(): array
    {
        return [
            'provider' => fake()->randomElement(['momo', 'airtel', 'pal_bank']),
            'reference' => fake()->optional()->bothify('PAY-#####'),
            'payload' => [
                'status' => fake()->randomElement(['SUCCESS', 'PENDING', 'FAILED']),
                'message' => fake()->sentence(),
            ],
            'signature_valid' => fake()->boolean(),
            'processed_at' => fake()->optional()->dateTimeBetween('-7 days'),
        ];
    }
}
