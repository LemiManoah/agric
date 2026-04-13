<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\District;
use App\Models\Region;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $primaryRegion = Region::query()->with('districts')->orderBy('name')->first();
        $secondaryRegion = Region::query()
            ->with('districts')
            ->whereKeyNot($primaryRegion?->id)
            ->orderBy('name')
            ->first() ?? $primaryRegion;

        $primaryDistrict = $primaryRegion?->districts->sortBy('name')->first();
        $secondaryDistrict = $secondaryRegion?->districts->sortBy('name')->first() ?? $primaryDistrict;

        if (! $primaryRegion || ! $primaryDistrict) {
            $primaryRegion = Region::factory()->create();
            $primaryDistrict = District::factory()->create(['region_id' => $primaryRegion->id]);
        }

        if (! $secondaryRegion || ! $secondaryDistrict) {
            $secondaryRegion = $primaryRegion;
            $secondaryDistrict = $primaryDistrict;
        }

        $password = Hash::make('password');

        $users = [
            [
                'email' => 'superadmin@agrofresh.test',
                'role' => 'super_admin',
                'attributes' => [
                    'name' => 'AgroFresh Super Admin',
                    'phone' => '256700900001',
                    'password' => $password,
                    'status' => UserStatus::Active,
                    'email_verified_at' => now(),
                ],
            ],
            [
                'email' => 'regional.admin@agrofresh.test',
                'role' => 'regional_admin',
                'attributes' => [
                    'name' => 'Central Regional Admin',
                    'phone' => '256700900002',
                    'password' => $password,
                    'status' => UserStatus::Active,
                    'region_id' => $primaryRegion->id,
                    'district_id' => $primaryDistrict->id,
                    'email_verified_at' => now(),
                ],
            ],
            [
                'email' => 'field.officer@agrofresh.test',
                'role' => 'field_officer',
                'attributes' => [
                    'name' => 'Field Officer One',
                    'phone' => '256700900003',
                    'password' => $password,
                    'status' => UserStatus::Active,
                    'region_id' => $primaryRegion->id,
                    'district_id' => $primaryDistrict->id,
                    'email_verified_at' => now(),
                ],
            ],
            [
                'email' => 'farmer.demo@agrofresh.test',
                'role' => 'farmer',
                'attributes' => [
                    'name' => 'Demo Farmer',
                    'phone' => '256700900004',
                    'password' => $password,
                    'status' => UserStatus::Active,
                    'region_id' => $primaryRegion->id,
                    'district_id' => $primaryDistrict->id,
                    'email_verified_at' => now(),
                ],
            ],
            [
                'email' => 'supplier.demo@agrofresh.test',
                'role' => 'supplier',
                'attributes' => [
                    'name' => 'Demo Supplier',
                    'phone' => '256700900005',
                    'password' => $password,
                    'status' => UserStatus::Active,
                    'region_id' => $primaryRegion->id,
                    'district_id' => $primaryDistrict->id,
                    'email_verified_at' => now(),
                ],
            ],
            [
                'email' => 'agent.demo@agrofresh.test',
                'role' => 'agent',
                'attributes' => [
                    'name' => 'Demo Agent',
                    'phone' => '256700900006',
                    'password' => $password,
                    'status' => UserStatus::Active,
                    'region_id' => $secondaryRegion->id,
                    'district_id' => $secondaryDistrict->id,
                    'email_verified_at' => now(),
                ],
            ],
            [
                'email' => 'buyer.demo@agrofresh.test',
                'role' => 'buyer',
                'attributes' => [
                    'name' => 'Demo Buyer',
                    'phone' => '256700900007',
                    'password' => $password,
                    'status' => UserStatus::Active,
                    'email_verified_at' => now(),
                ],
            ],
        ];

        foreach ($users as $userData) {
            $user = User::query()->firstOrNew(['email' => $userData['email']]);
            $user->fill($userData['attributes']);
            $user->save();
            $user->syncRoles([$userData['role']]);
        }
    }
}
