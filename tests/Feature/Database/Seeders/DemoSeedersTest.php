<?php

use App\Models\Agent;
use App\Models\AgribusinessProfile;
use App\Models\Farmer;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\M1DemoDataSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UgandaLocationSeeder;

it('seeds demo users across the main roles', function () {
    $this->seed([
        RolePermissionSeeder::class,
        UgandaLocationSeeder::class,
        DemoUserSeeder::class,
    ]);

    expect(User::query()->where('email', 'superadmin@agrofresh.test')->first()?->hasRole('super_admin'))->toBeTrue()
        ->and(User::query()->where('email', 'regional.admin@agrofresh.test')->first()?->hasRole('regional_admin'))->toBeTrue()
        ->and(User::query()->where('email', 'field.officer@agrofresh.test')->first()?->hasRole('field_officer'))->toBeTrue()
        ->and(User::query()->where('email', 'agent.demo@agrofresh.test')->first()?->hasRole('agent'))->toBeTrue();
});

it('seeds M1 demo data for the current modules', function () {
    $this->seed([
        RolePermissionSeeder::class,
        UgandaLocationSeeder::class,
        DemoUserSeeder::class,
        M1DemoDataSeeder::class,
    ]);

    expect(Farmer::query()->where('phone', '256701100101')->exists())->toBeTrue()
        ->and(Supplier::query()->where('phone', '256702200101')->exists())->toBeTrue()
        ->and(Agent::query()->where('agent_code', 'AGT-10001')->exists())->toBeTrue()
        ->and(AgribusinessProfile::query()->where('organization_name', 'Central Growers Cooperative')->exists())->toBeTrue();
});
