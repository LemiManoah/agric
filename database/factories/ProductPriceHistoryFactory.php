<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductPriceHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductPriceHistory>
 */
class ProductPriceHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $oldPrice = fake()->randomFloat(2, 5, 300);
        $newPrice = $oldPrice + fake()->randomFloat(2, 1, 40);

        return [
            'product_id' => Product::factory(),
            'old_price_per_unit_usd' => $oldPrice,
            'new_price_per_unit_usd' => $newPrice,
        ];
    }
}
