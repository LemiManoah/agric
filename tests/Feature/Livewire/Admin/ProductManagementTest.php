<?php

use App\Enums\ListingStatus;
use App\Enums\SupplyFrequency;
use App\Enums\VerificationStatus;
use App\Livewire\Admin\ProductCategories\Index as CategoryIndex;
use App\Livewire\Admin\Products\Form;
use App\Livewire\Admin\Products\Index;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\QualityGrade;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('renders the admin product index for a super admin', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $this->actingAs($admin)
        ->get(route('admin.products.index'))
        ->assertSuccessful()
        ->assertSee('Product listings');
});

it('renders the product create page without route collisions', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $this->actingAs($admin)
        ->get(route('admin.products.create'))
        ->assertSuccessful()
        ->assertSee('Create product');
});

it('creates a product category from the management page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    Livewire::actingAs($admin)
        ->test(CategoryIndex::class)
        ->set('name', 'Seeds')
        ->set('slug', 'seeds')
        ->set('is_active', true)
        ->call('save')
        ->assertHasNoErrors();

    expect(ProductCategory::query()->where('slug', 'seeds')->exists())->toBeTrue();
});

it('creates a product through the livewire form', function () {
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

    Livewire::actingAs($admin)
        ->test(Form::class)
        ->set('name', 'Maize Grain Lot')
        ->set('product_category_id', $category->id)
        ->set('linked_supplier_id', $supplier->id)
        ->set('quality_grade_id', $qualityGrade->id)
        ->set('unit_of_measure', 'kg')
        ->set('price_per_unit_usd', '0.89')
        ->set('minimum_order_quantity', '100')
        ->set('stock_available', '900')
        ->set('listing_status', ListingStatus::Active->value)
        ->call('save')
        ->assertHasNoErrors();

    expect(Product::query()->where('name', 'Maize Grain Lot')->exists())->toBeTrue();
});

it('limits regional admin product visibility to scoped suppliers', function () {
    $visible = createTestLocationHierarchy();
    $hidden = createTestLocationHierarchy();

    $regionalAdmin = createScopedUser('regional_admin', [
        'region_id' => $visible['region']->id,
        'district_id' => $visible['district']->id,
    ]);

    $category = ProductCategory::factory()->create();

    $visibleSupplier = Supplier::factory()->create([
        'operating_district_id' => $visible['district']->id,
        'supply_frequency' => SupplyFrequency::Weekly,
        'verification_status' => VerificationStatus::Verified,
    ]);
    $hiddenSupplier = Supplier::factory()->create([
        'operating_district_id' => $hidden['district']->id,
        'supply_frequency' => SupplyFrequency::Weekly,
        'verification_status' => VerificationStatus::Verified,
    ]);

    Product::factory()->create([
        'name' => 'Visible Product',
        'product_category_id' => $category->id,
        'linked_supplier_id' => $visibleSupplier->id,
        'listing_status' => ListingStatus::Active,
    ]);
    Product::factory()->create([
        'name' => 'Hidden Product',
        'product_category_id' => $category->id,
        'linked_supplier_id' => $hiddenSupplier->id,
        'listing_status' => ListingStatus::Active,
    ]);

    Livewire::actingAs($regionalAdmin)
        ->test(Index::class)
        ->assertSee('Visible Product')
        ->assertDontSee('Hidden Product');
});
