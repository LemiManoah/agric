<?php

namespace Database\Seeders;

use App\Enums\VerificationStatus;
use App\Models\Buyer;
use App\Models\User;
use App\Models\ValueChain;
use Illuminate\Database\Seeder;

class BuyerDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $valueChains = ValueChain::query()->where('is_active', true)->orderBy('name')->get();
        $superAdmin = User::query()->where('email', 'superadmin@agrofresh.test')->first();
        $regionalAdmin = User::query()->where('email', 'regional.admin@agrofresh.test')->first();
        $demoBuyerUser = User::query()->where('email', 'buyer.demo@agrofresh.test')->first();

        $buyers = [
            [
                'lookup' => 'buyer.demo@agrofresh.test',
                'attributes' => [
                    'user_id' => $demoBuyerUser?->id,
                    'company_name' => 'Demo Buyer Trading Co',
                    'country' => 'Uganda',
                    'business_type' => 'Wholesaler',
                    'contact_person_full_name' => 'Demo Buyer',
                    'phone' => '256700900007',
                    'email' => 'buyer.demo@agrofresh.test',
                    'annual_import_volume_usd_range' => '50K - 250K',
                    'preferred_payment_method' => 'Bank transfer',
                    'verification_status' => VerificationStatus::Verified,
                    'verified_at' => now()->subDays(2),
                    'verified_by_user_id' => $superAdmin?->id,
                    'created_by' => $superAdmin?->id,
                ],
                'interest_count' => 2,
            ],
            [
                'lookup' => 'procurement@greenbridge.test',
                'attributes' => [
                    'company_name' => 'GreenBridge Procurement',
                    'country' => 'Kenya',
                    'business_type' => 'Importer',
                    'contact_person_full_name' => 'Alice Kendi',
                    'phone' => '254700111222',
                    'email' => 'procurement@greenbridge.test',
                    'annual_import_volume_usd_range' => '250K - 1M',
                    'preferred_payment_method' => 'Letter of credit',
                    'verification_status' => VerificationStatus::Submitted,
                    'created_by' => $regionalAdmin?->id ?? $superAdmin?->id,
                ],
                'interest_count' => 3,
            ],
            [
                'lookup' => 'sourcing@northstar.test',
                'attributes' => [
                    'company_name' => 'NorthStar Foods',
                    'country' => 'Rwanda',
                    'business_type' => 'Processor',
                    'contact_person_full_name' => 'Jeanette Uwase',
                    'phone' => '250780111333',
                    'email' => 'sourcing@northstar.test',
                    'annual_import_volume_usd_range' => 'Below 50K',
                    'preferred_payment_method' => 'Bank transfer',
                    'verification_status' => VerificationStatus::Suspended,
                    'created_by' => $regionalAdmin?->id ?? $superAdmin?->id,
                ],
                'interest_count' => 1,
            ],
        ];

        foreach ($buyers as $buyerData) {
            $buyer = Buyer::query()->updateOrCreate(
                ['email' => $buyerData['lookup']],
                $buyerData['attributes'],
            );

            $buyer->valueChainInterests()->sync($valueChains->take($buyerData['interest_count'])->pluck('id')->all());
        }
    }
}
