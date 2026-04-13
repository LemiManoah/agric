<?php

use App\Enums\AgribusinessEntityType;
use App\Models\AgribusinessProfile;
use App\Models\User;
use App\Services\AgribusinessProfileService;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('creates an agribusiness profile with covered districts', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $location = createTestLocationHierarchy();

    $profile = app(AgribusinessProfileService::class)->createProfile([
        'entity_type' => AgribusinessEntityType::GrainMiller,
        'organization_name' => 'Miller Service',
        'contact_person' => 'Nora',
        'contact_phone' => '256700003600',
        'district_ids' => [$location['district']->id],
    ], $admin);

    expect($profile->districts->pluck('id')->all())->toBe([$location['district']->id]);

    expect(Activity::query()->where('subject_type', AgribusinessProfile::class)->where('event', 'agribusiness.created')->exists())->toBeTrue();
});

it('updates an agribusiness profile and syncs district coverage', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $firstLocation = createTestLocationHierarchy();
    $secondLocation = createTestLocationHierarchy();

    $profile = AgribusinessProfile::query()->create([
        'entity_type' => AgribusinessEntityType::InputDealer,
        'organization_name' => 'Dealer Service',
        'contact_person' => 'Mark',
        'contact_phone' => '256700003601',
    ]);
    $profile->districts()->sync([$firstLocation['district']->id]);

    $updated = app(AgribusinessProfileService::class)->updateProfile($profile, [
        'entity_type' => AgribusinessEntityType::AgroDealer,
        'organization_name' => 'Dealer Service Updated',
        'contact_person' => 'Mark',
        'contact_phone' => '256700003601',
        'district_ids' => [$secondLocation['district']->id],
    ], $admin);

    expect($updated->entity_type)->toBe(AgribusinessEntityType::AgroDealer)
        ->and($updated->districts->pluck('id')->all())->toBe([$secondLocation['district']->id]);

    expect(Activity::query()->where('subject_type', AgribusinessProfile::class)->where('event', 'agribusiness.updated')->exists())->toBeTrue();
});
