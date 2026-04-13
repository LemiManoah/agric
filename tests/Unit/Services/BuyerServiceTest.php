<?php

use App\Enums\VerificationStatus;
use App\Models\Buyer;
use App\Models\User;
use App\Models\ValueChain;
use App\Services\BuyerService;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('creates a buyer and syncs value chain interests', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $valueChain = ValueChain::factory()->create();

    $buyer = app(BuyerService::class)->createBuyer([
        'company_name' => 'Buyer Service Co',
        'country' => 'Uganda',
        'business_type' => 'Wholesaler',
        'contact_person_full_name' => 'Martha',
        'phone' => '256700111111',
        'email' => 'buyer-service@example.test',
        'value_chain_interest_ids' => [$valueChain->id],
    ], $admin);

    expect($buyer->valueChainInterests->pluck('id')->all())->toBe([$valueChain->id])
        ->and(Activity::query()->where('subject_type', Buyer::class)->where('event', 'buyer.created')->exists())->toBeTrue();
});

it('updates a buyer while preserving verification state when not explicitly changed', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $buyer = Buyer::factory()->create([
        'verification_status' => VerificationStatus::Verified,
        'verified_at' => now(),
        'verified_by_user_id' => $admin->id,
    ]);

    $updated = app(BuyerService::class)->updateBuyer($buyer, [
        'company_name' => 'Updated Buyer Co',
        'country' => $buyer->country,
        'business_type' => $buyer->business_type,
        'contact_person_full_name' => $buyer->contact_person_full_name,
        'phone' => $buyer->phone,
        'email' => $buyer->email,
        'value_chain_interest_ids' => [],
    ], $admin);

    expect($updated->company_name)->toBe('Updated Buyer Co')
        ->and($updated->verification_status)->toBe(VerificationStatus::Verified)
        ->and(Activity::query()->where('subject_type', Buyer::class)->where('event', 'buyer.updated')->exists())->toBeTrue();
});

it('verifies and suspends a buyer with activity logs', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $buyer = Buyer::factory()->create([
        'verification_status' => VerificationStatus::Submitted,
    ]);

    $service = app(BuyerService::class);
    $service->verifyBuyer($buyer, $admin);
    $service->suspendBuyer($buyer->fresh(), $admin);

    $buyer->refresh();

    expect($buyer->verification_status)->toBe(VerificationStatus::Suspended)
        ->and(Activity::query()->where('subject_type', Buyer::class)->where('event', 'buyer.verified')->exists())->toBeTrue()
        ->and(Activity::query()->where('subject_type', Buyer::class)->where('event', 'buyer.suspended')->exists())->toBeTrue();
});
