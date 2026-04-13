<?php

use App\Enums\ListingStatus;
use App\Enums\SupplyFrequency;
use App\Enums\VerificationStatus;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\QualityGrade;
use App\Models\Supplier;
use App\Models\User;
use App\Services\ProductService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    config()->set('filesystems.default', 'public');
    Storage::fake('public');
});

it('creates a product with images and logs activity', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $location = createTestLocationHierarchy();
    $supplier = Supplier::factory()->create([
        'operating_district_id' => $location['district']->id,
        'supply_frequency' => SupplyFrequency::Weekly,
        'verification_status' => VerificationStatus::Verified,
    ]);
    $category = ProductCategory::factory()->create();
    $qualityGrade = QualityGrade::factory()->create();

    $product = app(ProductService::class)->createProduct([
        'name' => 'Premium Maize',
        'product_category_id' => $category->id,
        'linked_supplier_id' => $supplier->id,
        'quality_grade_id' => $qualityGrade->id,
        'unit_of_measure' => 'kg',
        'price_per_unit_usd' => 0.75,
        'minimum_order_quantity' => 200,
        'stock_available' => 3400,
        'listing_status' => ListingStatus::Active,
        'uploaded_images' => [UploadedFile::fake()->image('maize.jpg')],
    ], $admin);

    expect($product->images)->toHaveCount(1)
        ->and(Activity::query()->where('subject_type', Product::class)->where('event', 'product.created')->exists())->toBeTrue();
});

it('updates a product and records price history', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $location = createTestLocationHierarchy();
    $supplier = Supplier::factory()->create([
        'operating_district_id' => $location['district']->id,
        'supply_frequency' => SupplyFrequency::Weekly,
        'verification_status' => VerificationStatus::Verified,
    ]);
    $category = ProductCategory::factory()->create();

    $product = Product::factory()->create([
        'product_category_id' => $category->id,
        'linked_supplier_id' => $supplier->id,
        'price_per_unit_usd' => 1.5,
        'listing_status' => ListingStatus::Active,
    ]);

    $updated = app(ProductService::class)->updateProduct($product, [
        'name' => $product->name,
        'product_category_id' => $product->product_category_id,
        'linked_supplier_id' => $product->linked_supplier_id,
        'quality_grade_id' => $product->quality_grade_id,
        'unit_of_measure' => $product->unit_of_measure,
        'price_per_unit_usd' => 1.95,
        'minimum_order_quantity' => $product->minimum_order_quantity,
        'stock_available' => $product->stock_available,
        'listing_status' => ListingStatus::Active,
        'retained_image_ids' => [],
        'uploaded_images' => [],
    ], $admin);

    expect($updated->priceHistories)->toHaveCount(1)
        ->and(Activity::query()->where('subject_type', Product::class)->where('event', 'product.updated')->exists())->toBeTrue();
});

it('archives a product and logs the lifecycle event', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $product = Product::factory()->create([
        'listing_status' => ListingStatus::Active,
    ]);

    app(ProductService::class)->archiveProduct($product, $admin);

    $product->refresh();

    expect($product->listing_status)->toBe(ListingStatus::Archived)
        ->and(Activity::query()->where('subject_type', Product::class)->where('event', 'product.archived')->exists())->toBeTrue();
});
