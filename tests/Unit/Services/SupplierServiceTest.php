<?php

use App\Enums\SupplyFrequency;
use App\Enums\VerificationStatus;
use App\Models\QualityGrade;
use App\Models\Supplier;
use App\Models\User;
use App\Models\ValueChain;
use App\Services\SupplierService;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('creates a supplier and syncs pivots', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $location = createTestLocationHierarchy();
    $valueChain = ValueChain::query()->create([
        'name' => 'Beans',
        'slug' => 'beans',
        'is_active' => true,
    ]);
    $qualityGrade = QualityGrade::query()->create([
        'name' => 'Premium',
        'slug' => 'premium',
        'is_active' => true,
    ]);

    $supplier = app(SupplierService::class)->createSupplier([
        'business_name' => 'Service Supplier',
        'contact_person' => 'Martha',
        'phone' => '256700002400',
        'operating_district_id' => $location['district']->id,
        'supply_frequency' => SupplyFrequency::Daily,
        'value_chain_ids' => [$valueChain->id],
        'quality_grade_ids' => [$qualityGrade->id],
    ], $admin);

    expect($supplier->valueChains->pluck('id')->all())->toBe([$valueChain->id])
        ->and($supplier->qualityGrades->pluck('id')->all())->toBe([$qualityGrade->id]);

    expect(Activity::query()->where('subject_type', Supplier::class)->where('event', 'supplier.created')->exists())->toBeTrue();
});

it('updates a supplier without resetting verification or warehouse linkage', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $location = createTestLocationHierarchy();

    $supplier = Supplier::query()->create([
        'business_name' => 'Original Supplier',
        'contact_person' => 'Hellen',
        'phone' => '256700002500',
        'operating_district_id' => $location['district']->id,
        'supply_frequency' => SupplyFrequency::Weekly,
        'verification_status' => VerificationStatus::Verified,
        'warehouse_linked' => true,
        'verified_at' => now(),
        'verified_by_user_id' => $admin->id,
    ]);

    $updated = app(SupplierService::class)->updateSupplier($supplier, [
        'business_name' => 'Updated Supplier',
        'contact_person' => 'Hellen',
        'phone' => '256700002500',
        'operating_district_id' => $location['district']->id,
        'supply_frequency' => SupplyFrequency::Monthly,
        'value_chain_ids' => [],
        'quality_grade_ids' => [],
    ], $admin);

    expect($updated->business_name)->toBe('Updated Supplier')
        ->and($updated->verification_status)->toBe(VerificationStatus::Verified)
        ->and($updated->warehouse_linked)->toBeTrue();

    expect(Activity::query()->where('subject_type', Supplier::class)->where('event', 'supplier.updated')->exists())->toBeTrue();
});

it('verifies and suspends a supplier while logging lifecycle activity', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $location = createTestLocationHierarchy();

    $supplier = Supplier::query()->create([
        'business_name' => 'Lifecycle Supplier',
        'contact_person' => 'Peter',
        'phone' => '256700002600',
        'operating_district_id' => $location['district']->id,
        'supply_frequency' => SupplyFrequency::Seasonal,
        'verification_status' => VerificationStatus::Submitted,
    ]);

    $service = app(SupplierService::class);
    $service->verifySupplier($supplier, $admin);
    $service->setWarehouseLinked($supplier->fresh(), true, $admin);
    $service->suspendSupplier($supplier->fresh(), $admin);

    $supplier->refresh();

    expect($supplier->verification_status)->toBe(VerificationStatus::Suspended)
        ->and($supplier->warehouse_linked)->toBeTrue();

    expect(Activity::query()->where('subject_type', Supplier::class)->where('event', 'supplier.verified')->exists())->toBeTrue()
        ->and(Activity::query()->where('subject_type', Supplier::class)->where('event', 'supplier.warehouse_linked_updated')->exists())->toBeTrue()
        ->and(Activity::query()->where('subject_type', Supplier::class)->where('event', 'supplier.suspended')->exists())->toBeTrue();
});
