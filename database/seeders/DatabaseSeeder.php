<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UgandaLocationSeeder::class,
            DemoUserSeeder::class,
            M1DemoDataSeeder::class,
            BuyerDemoSeeder::class,
            ProductCatalogueDemoSeeder::class,
        ]);
    }
}
