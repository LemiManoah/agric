<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Receipt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Receipt>
 */
class ReceiptFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'payment_id' => null,
            'file_path' => 'receipts/'.fake()->uuid().'.pdf',
            'file_disk' => 'public',
            'generated_at' => fake()->dateTimeBetween('-7 days'),
        ];
    }
}
