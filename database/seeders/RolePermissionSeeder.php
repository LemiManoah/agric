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
            'roles.view',
            'roles.create',
            'roles.update',
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
            'buyers.create',
            'buyers.verify',
            'buyers.update',
            'buyers.export',
            'products.view',
            'products.create',
            'products.update',
            'products.archive',
            'products.export',
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
            'payments.create',
            'payments.confirm',
            'payments.refund',
            'payments.export',
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
                'buyers.create',
                'buyers.verify',
                'buyers.update',
                'buyers.export',
                'products.view',
                'products.create',
                'products.update',
                'products.archive',
                'products.export',
                'orders.view.region',
                'orders.confirm',
                'orders.process',
                'orders.dispatch',
                'orders.deliver',
                'orders.cancel',
                'orders.refund',
                'payments.view',
                'payments.create',
                'payments.confirm',
                'payments.refund',
                'payments.export',
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
                'orders.cancel',
            ],
            'buyer' => [
                'buyers.update',
                'products.view',
                'orders.view.own',
                'orders.create',
                'orders.cancel',
                'payments.view',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($rolePermissions);
        }
    }
}
