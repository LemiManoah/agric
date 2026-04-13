<?php

use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Role;

it('creates the expected first phase roles and permissions', function () {
    $this->seed(RolePermissionSeeder::class);

    expect(Role::query()->pluck('name')->sort()->values()->all())
        ->toBe([
            'agent',
            'buyer',
            'farmer',
            'field_officer',
            'regional_admin',
            'super_admin',
            'supplier',
        ]);

    $regionalAdmin = Role::findByName('regional_admin');

    expect($regionalAdmin->hasPermissionTo('farmers.view.region'))->toBeTrue()
        ->and($regionalAdmin->hasPermissionTo('orders.view.all'))->toBeFalse()
        ->and($regionalAdmin->hasPermissionTo('roles.view'))->toBeFalse();

    expect(Role::findByName('super_admin')->hasPermissionTo('activity.view'))->toBeTrue()
        ->and(Role::findByName('super_admin')->hasPermissionTo('roles.view'))->toBeTrue()
        ->and(Role::findByName('super_admin')->hasPermissionTo('roles.create'))->toBeTrue()
        ->and(Role::findByName('super_admin')->hasPermissionTo('roles.update'))->toBeTrue();
});
