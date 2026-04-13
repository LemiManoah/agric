<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'users.view',
            'users.create',
            'users.update',
            'users.suspend',
            'users.restore',
            'roles.assign',
            'farmers.view',
            'farmers.view.region',
            'farmers.create',
            'farmers.update',
            'farmers.verify',
            'farmers.export',
            'farmers.view.map',
            'suppliers.view',
            'suppliers.create',
            'suppliers.update',
            'suppliers.verify',
            'suppliers.export',
            'suppliers.toggle_warehouse_linked',
            'agents.view',
            'agents.create',
            'agents.update',
            'agents.change_status',
            'agents.export',
            'agents.view.commission',
            'agribusiness_profiles.view',
            'agribusiness_profiles.create',
            'agribusiness_profiles.update',
            'agribusiness_profiles.export',
            'buyers.view',
            'buyers.verify',
            'buyers.update',
            'products.view',
            'products.create',
            'products.update',
            'products.archive',
            'products.manage.stock',
            'products.manage.price',
            'orders.view.own',
            'orders.view.region',
            'orders.view.all',
            'orders.create',
            'orders.confirm',
            'orders.process',
            'orders.dispatch',
            'orders.deliver',
            'orders.cancel',
            'orders.refund',
            'payments.view',
            'payments.confirm',
            'payments.refund',
            'notifications.view',
            'notifications.send.manual',
            'reports.view',
            'reports.view.region',
            'exports.create',
            'exports.download',
            'activity.view',
            'activity.view.region',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $roles = [
            'super_admin' => $permissions,
            'regional_admin' => [
                'users.view',
                'farmers.view',
                'farmers.view.region',
                'farmers.create',
                'farmers.update',
                'farmers.verify',
                'farmers.export',
                'farmers.view.map',
                'suppliers.view',
                'suppliers.create',
                'suppliers.update',
                'suppliers.verify',
                'suppliers.export',
                'suppliers.toggle_warehouse_linked',
                'agents.view',
                'agents.create',
                'agents.update',
                'agents.change_status',
                'agents.export',
                'agribusiness_profiles.view',
                'agribusiness_profiles.create',
                'agribusiness_profiles.update',
                'agribusiness_profiles.export',
                'buyers.view',
                'buyers.verify',
                'buyers.update',
                'products.view',
                'orders.view.region',
                'orders.confirm',
                'orders.process',
                'orders.dispatch',
                'payments.view',
                'notifications.view',
                'reports.view',
                'reports.view.region',
                'exports.create',
                'exports.download',
                'activity.view.region',
            ],
            'field_officer' => [
                'farmers.view',
                'farmers.create',
                'farmers.update',
            ],
            'farmer' => [
                'products.view',
                'orders.view.own',
                'notifications.view',
            ],
            'supplier' => [
                'suppliers.view',
                'suppliers.update',
                'products.view',
                'products.create',
                'products.update',
                'products.manage.stock',
                'products.manage.price',
                'orders.view.own',
            ],
            'agent' => [
                'agents.view',
                'agents.view.commission',
                'buyers.view',
                'products.view',
                'orders.view.own',
                'orders.create',
            ],
            'buyer' => [
                'buyers.update',
                'products.view',
                'orders.view.own',
                'orders.create',
                'payments.view',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($rolePermissions);
        }
    }
}
