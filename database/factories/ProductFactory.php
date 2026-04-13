<?php

namespace Database\Factories;

use App\Enums\ListingStatus;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\QualityGrade;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ucfirst(fake()->words(3, true)),
            'product_category_id' => ProductCategory::factory(),
            'linked_supplier_id' => Supplier::factory(),
            'description' => fake()->paragraph(),
            'quality_grade_id' => QualityGrade::factory(),
            'unit_of_measure' => fake()->randomElement(['kg', 'tonne', 'bag', 'crate']),
            'price_per_unit_usd' => fake()->randomFloat(2, 2, 500),
            'minimum_order_quantity' => fake()->randomFloat(2, 1, 100),
            'stock_available' => fake()->randomFloat(2, 0, 1000),
            'listing_status' => fake()->randomElement(ListingStatus::cases()),
            'warehouse_sku' => fake()->optional()->bothify('SKU-#####'),
        ];
    }
}
