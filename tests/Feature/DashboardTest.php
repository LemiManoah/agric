<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

test('guests are redirected to the login page', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    $this->actingAs($user = User::factory()->create());

    $this->get('/dashboard')->assertSuccessful();
});

test('authorized users can see the M1 sidebar navigation', function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertSuccessful()
        ->assertSee('Farmers')
        ->assertSee('Suppliers')
        ->assertSee('Agents')
        ->assertSee('Agribusiness')
        ->assertSee('Reports')
        ->assertSee('Access')
        ->assertSee('Roles')
        ->assertSee(route('admin.farmers.index'), false)
        ->assertSee(route('admin.suppliers.index'), false)
        ->assertSee(route('admin.agents.index'), false)
        ->assertSee(route('admin.agribusiness-profiles.index'), false)
        ->assertSee(route('admin.reports.m1-profile-summary'), false)
        ->assertSee(route('admin.roles.index'), false);
});
