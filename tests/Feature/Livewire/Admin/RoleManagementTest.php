<?php

use App\Livewire\Admin\Roles\Form;
use App\Livewire\Admin\Roles\Index;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('renders the role index for a super admin', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $this->actingAs($admin)
        ->get(route('admin.roles.index'))
        ->assertSuccessful()
        ->assertSee('Roles & permissions', false);
});

it('creates a role with attached permissions through the form', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    Livewire::actingAs($admin)
        ->test(Form::class)
        ->set('name', 'warehouse_manager')
        ->set('permission_names', ['suppliers.view', 'suppliers.toggle_warehouse_linked', 'reports.view'])
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.roles.index'));

    $role = Role::findByName('warehouse_manager');

    expect($role->hasPermissionTo('suppliers.view'))->toBeTrue()
        ->and($role->hasPermissionTo('suppliers.toggle_warehouse_linked'))->toBeTrue()
        ->and($role->hasPermissionTo('reports.view'))->toBeTrue();
});

it('blocks users without role permissions from the module', function () {
    $user = User::factory()->create();
    $user->assignRole('regional_admin');

    $this->actingAs($user)
        ->get(route('admin.roles.index'))
        ->assertForbidden();
});

it('lists saved roles in the livewire index', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    Role::findOrCreate('demo_auditor', 'web')->givePermissionTo('reports.view');

    Livewire::actingAs($admin)
        ->test(Index::class)
        ->assertSee('demo_auditor');
});
