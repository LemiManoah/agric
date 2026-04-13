<?php

namespace Database\Seeders;

use App\Enums\ListingStatus;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Models\ProductPriceHistory;
use App\Models\QualityGrade;
use App\Models\Supplier;
use App\Models\User;
use App\Models\ValueChain;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductCatalogueDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->ensureDemoImage('product-images/demo/default-product.svg');

        $superAdmin = User::query()->where('email', 'superadmin@agrofresh.test')->first();
        $suppliers = Supplier::query()->with('valueChains')->orderBy('business_name')->get();
        $valueChains = ValueChain::query()->where('is_active', true)->orderBy('name')->get();
        $qualityGrades = QualityGrade::query()->where('is_active', true)->orderBy('name')->get();

        if ($suppliers->isEmpty()) {
            return;
        }

        $categories = collect([
            ['name' => 'Cereals', 'slug' => 'cereals', 'linked_value_chain_id' => $valueChains->firstWhere('slug', 'maize')?->id],
            ['name' => 'Pulses', 'slug' => 'pulses', 'linked_value_chain_id' => $valueChains->firstWhere('slug', 'beans')?->id],
            ['name' => 'Cash Crops', 'slug' => 'cash-crops', 'linked_value_chain_id' => $valueChains->firstWhere('slug', 'coffee')?->id],
        ])->mapWithKeys(function (array $attributes): array {
            $category = ProductCategory::query()->updateOrCreate(
                ['slug' => $attributes['slug']],
                ['name' => $attributes['name'], 'linked_value_chain_id' => $attributes['linked_value_chain_id'], 'is_active' => true],
            );

            return [$attributes['slug'] => $category];
        });

        $products = [
            [
                'name' => 'Premium Dry Maize',
                'category' => $categories['cereals'],
                'supplier' => $suppliers->first(),
                'quality_grade_id' => $qualityGrades->first()?->id,
                'unit_of_measure' => 'kg',
                'price_per_unit_usd' => 0.62,
                'minimum_order_quantity' => 500,
                'stock_available' => 4200,
                'listing_status' => ListingStatus::Active,
                'warehouse_sku' => 'MAIZE-001',
            ],
            [
                'name' => 'Sorted Beans Export Lot',
                'category' => $categories['pulses'],
                'supplier' => $suppliers->skip(1)->first() ?? $suppliers->first(),
                'quality_grade_id' => $qualityGrades->skip(1)->first()?->id,
                'unit_of_measure' => 'kg',
                'price_per_unit_usd' => 0.95,
                'minimum_order_quantity' => 300,
                'stock_available' => 0,
                'listing_status' => ListingStatus::OutOfStock,
                'warehouse_sku' => 'BEANS-002',
            ],
            [
                'name' => 'Robusta Coffee Green Beans',
                'category' => $categories['cash-crops'],
                'supplier' => $suppliers->last(),
                'quality_grade_id' => $qualityGrades->first()?->id,
                'unit_of_measure' => 'kg',
                'price_per_unit_usd' => 3.4,
                'minimum_order_quantity' => 120,
                'stock_available' => 900,
                'listing_status' => ListingStatus::Active,
                'warehouse_sku' => 'COFFEE-003',
            ],
            [
                'name' => 'Draft Soybean Bulk Lot',
                'category' => $categories['pulses'],
                'supplier' => $suppliers->first(),
                'quality_grade_id' => $qualityGrades->last()?->id,
                'unit_of_measure' => 'kg',
                'price_per_unit_usd' => 0.74,
                'minimum_order_quantity' => 250,
                'stock_available' => 1800,
                'listing_status' => ListingStatus::Draft,
                'warehouse_sku' => 'SOY-004',
            ],
        ];

        foreach ($products as $index => $productData) {
            if (! $productData['supplier']) {
                continue;
            }

            $product = Product::query()->updateOrCreate(
                ['warehouse_sku' => $productData['warehouse_sku']],
                [
                    'name' => $productData['name'],
                    'product_category_id' => $productData['category']->id,
                    'linked_supplier_id' => $productData['supplier']->id,
                    'description' => $productData['name'].' demo listing for local review and testing.',
                    'quality_grade_id' => $productData['quality_grade_id'],
                    'unit_of_measure' => $productData['unit_of_measure'],
                    'price_per_unit_usd' => $productData['price_per_unit_usd'],
                    'minimum_order_quantity' => $productData['minimum_order_quantity'],
                    'stock_available' => $productData['stock_available'],
                    'listing_status' => $productData['listing_status'],
                    'created_by' => $superAdmin?->id,
                ],
            );

            ProductImage::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'sort_order' => 0,
                ],
                [
                    'path' => 'product-images/demo/default-product.svg',
                ],
            );

            if ($index < 3) {
                ProductPriceHistory::query()->updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'new_price_per_unit_usd' => $productData['price_per_unit_usd'],
                    ],
                    [
                        'old_price_per_unit_usd' => max(0.1, $productData['price_per_unit_usd'] - 0.1),
                        'changed_by_user_id' => $superAdmin?->id,
                    ],
                );
            }
        }
    }

    private function ensureDemoImage(string $path): void
    {
        $disk = Storage::disk((string) config('filesystems.default', 'public'));

        if ($disk->exists($path)) {
            return;
        }

        $label = 'AgroFresh';

        $disk->put($path, <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="900" viewBox="0 0 1200 900">
  <rect width="1200" height="900" fill="#ecfdf5"/>
  <rect x="40" y="40" width="1120" height="820" rx="48" fill="#052e16"/>
  <circle cx="980" cy="180" r="120" fill="#34d399" opacity="0.25"/>
  <circle cx="260" cy="700" r="160" fill="#bef264" opacity="0.18"/>
  <text x="90" y="180" fill="#d1fae5" font-family="Georgia, serif" font-size="92">{$label}</text>
  <text x="90" y="300" fill="#f0fdf4" font-family="Arial, sans-serif" font-size="44">Marketplace demo product image</text>
  <text x="90" y="380" fill="#a7f3d0" font-family="Arial, sans-serif" font-size="30">Seeded for local inspection and tests</text>
</svg>
SVG);
    }
}
