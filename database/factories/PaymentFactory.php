<?php

namespace Database\Factories;

use App\Enums\PaymentLifecycleStatus;
use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'method' => fake()->randomElement(PaymentMethod::cases()),
            'gateway_transaction_reference' => fake()->optional()->bothify('PAY-#####'),
            'gateway_reference_payload' => fake()->optional()->randomElement([
                ['provider' => 'sandbox', 'code' => '00'],
                ['provider' => 'sandbox', 'code' => 'PENDING'],
            ]),
            'amount' => fake()->randomFloat(2, 20, 5000),
            'currency' => 'USD',
            'exchange_rate_to_ugx' => fake()->optional()->randomFloat(4, 3600, 3900),
            'status' => fake()->randomElement(PaymentLifecycleStatus::cases()),
            'paid_at' => fake()->optional()->dateTimeBetween('-14 days'),
        ];
    }
}
