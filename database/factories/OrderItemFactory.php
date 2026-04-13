<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->randomFloat(2, 1, 50);
        $price = fake()->randomFloat(2, 1, 500);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'supplier_id' => Supplier::factory(),
            'product_name_snapshot' => ucfirst(fake()->words(3, true)),
            'quantity' => $quantity,
            'unit_price_usd' => $price,
            'line_total_usd' => round($quantity * $price, 2),
        ];
    }
}
