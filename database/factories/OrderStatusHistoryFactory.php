<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderStatusHistory>
 */
class OrderStatusHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'old_status' => fake()->optional()->randomElement(OrderStatus::cases()),
            'new_status' => fake()->randomElement(OrderStatus::cases()),
            'changed_by_user_id' => User::factory(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
