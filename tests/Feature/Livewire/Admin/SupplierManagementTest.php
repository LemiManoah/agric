<?php

use App\Enums\SupplyFrequency;
use App\Enums\VerificationStatus;
use App\Livewire\Admin\Suppliers\Form;
use App\Livewire\Admin\Suppliers\Index;
use App\Livewire\Admin\Suppliers\VerificationAction;
use App\Models\Farmer;
use App\Models\QualityGrade;
use App\Models\Supplier;
use App\Models\User;
use App\Models\ValueChain;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('renders the supplier index for a super admin', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $this->actingAs($user)
        ->get(route('admin.suppliers.index'))
        ->assertSuccessful()
        ->assertSee('Supplier registry');
});

it('limits supplier visibility to the regional admin scope', function () {
    $visibleLocation = createTestLocationHierarchy();
    $hiddenLocation = createTestLocationHierarchy();

    $regionalAdmin = createScopedUser('regional_admin', [
        'region_id' => $visibleLocation['region']->id,
        'district_id' => $visibleLocation['district']->id,
    ]);

    Supplier::query()->create([
        'business_name' => 'Central Supplier',
        'contact_person' => 'Alice',
        'phone' => '256700002001',
        'operating_district_id' => $visibleLocation['district']->id,
        'supply_frequency' => SupplyFrequency::Weekly,
        'verification_status' => VerificationStatus::Submitted,
    ]);

    Supplier::query()->create([
        'business_name' => 'Western Supplier',
        'contact_person' => 'Bob',
        'phone' => '256700002002',
        'operating_district_id' => $hiddenLocation['district']->id,
        'supply_frequency' => SupplyFrequency::Monthly,
        'verification_status' => VerificationStatus::Submitted,
    ]);

    Livewire::actingAs($regionalAdmin)
        ->test(Index::class)
        ->assertSee('Central Supplier')
        ->assertDontSee('Western Supplier');
});

it('renders the supplier form for an authorized admin', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $location = createTestLocationHierarchy();
    Farmer::query()->create([
        'full_name' => 'Linked Farmer',
        'phone' => '256700002100',
        'verification_status' => VerificationStatus::Submitted,
        'registration_source' => \App\Enums\RegistrationSource::FieldOfficer,
    ])->location()->create([
        'region_id' => $location['region']->id,
        'district_id' => $location['district']->id,
        'subcounty_id' => $location['subcounty']->id,
        'parish_id' => $location['parish']->id,
        'village_id' => $location['village']->id,
    ]);

    ValueChain::query()->create([
        'name' => 'Maize',
        'slug' => 'maize',
        'is_active' => true,
    ]);

    QualityGrade::query()->create([
        'name' => 'Grade A',
        'slug' => 'grade-a',
        'is_active' => true,
    ]);

    Livewire::actingAs($admin)
        ->test(Form::class)
        ->assertSee('Create supplier')
        ->assertSee('Linked farmer')
        ->assertSee('Value chains')
        ->assertSee('Quality grades');
});

it('verifies, warehouse-links, and suspends a supplier through the action component', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $location = createTestLocationHierarchy();

    $supplier = Supplier::query()->create([
        'business_name' => 'Action Supplier',
        'contact_person' => 'Grace',
        'phone' => '256700002300',
        'operating_district_id' => $location['district']->id,
        'supply_frequency' => SupplyFrequency::Seasonal,
        'verification_status' => VerificationStatus::Submitted,
    ]);

    Livewire::actingAs($admin)
        ->test(VerificationAction::class, ['supplier' => $supplier])
        ->call('verify')
        ->call('toggleWarehouseLinked')
        ->call('suspend');

    $supplier->refresh();

    expect($supplier->verification_status)->toBe(VerificationStatus::Suspended)
        ->and($supplier->warehouse_linked)->toBeTrue();
});
