<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Buyer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 5000);
        $discount = fake()->randomFloat(2, 0, 200);

        return [
            'order_number' => 'AGF-'.now()->format('Y').'-'.fake()->unique()->numerify('#####'),
            'buyer_id' => Buyer::factory(),
            'status' => fake()->randomElement(OrderStatus::cases()),
            'subtotal' => $subtotal,
            'discount_applied' => $discount,
            'order_total' => max(0, $subtotal - $discount),
            'payment_status' => fake()->randomElement(PaymentStatus::cases()),
            'delivery_address' => fake()->address(),
            'buyer_notes' => fake()->optional()->sentence(),
            'ordered_at' => now()->subDays(fake()->numberBetween(0, 14)),
        ];
    }
}
