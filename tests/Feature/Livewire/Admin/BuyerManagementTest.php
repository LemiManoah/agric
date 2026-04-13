<?php

use App\Enums\VerificationStatus;
use App\Livewire\Admin\Buyers\Form;
use App\Livewire\Admin\Buyers\Index;
use App\Livewire\Admin\Buyers\VerificationAction;
use App\Models\Buyer;
use App\Models\User;
use App\Models\ValueChain;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('renders the buyer index for a super admin', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $this->actingAs($admin)
        ->get(route('admin.buyers.index'))
        ->assertSuccessful()
        ->assertSee('Buyer registry');
});

it('creates a buyer through the livewire form', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    $valueChain = ValueChain::factory()->create();

    Livewire::actingAs($admin)
        ->test(Form::class)
        ->set('company_name', 'Atlas Foods')
        ->set('country', 'Uganda')
        ->set('business_type', 'Wholesaler')
        ->set('contact_person_full_name', 'Grace')
        ->set('phone', '256701234567')
        ->set('email', 'atlas@example.test')
        ->set('value_chain_interest_ids', [$valueChain->id])
        ->call('save')
        ->assertHasNoErrors();

    expect(Buyer::query()->where('email', 'atlas@example.test')->exists())->toBeTrue();
});

it('verifies and suspends a buyer through the action component', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $buyer = Buyer::factory()->create([
        'verification_status' => VerificationStatus::Submitted,
    ]);

    Livewire::actingAs($admin)
        ->test(VerificationAction::class, ['buyer' => $buyer])
        ->call('verify')
        ->call('suspend');

    expect($buyer->fresh()->verification_status)->toBe(VerificationStatus::Suspended);
});

it('shows only the buyer own profile in the buyer portal', function () {
    $buyerUser = User::factory()->create();
    $buyerUser->assignRole('buyer');

    $buyer = Buyer::factory()->create(['user_id' => $buyerUser->id]);

    $this->actingAs($buyerUser)
        ->get(route('buyer-portal.profile'))
        ->assertSuccessful()
        ->assertSee($buyer->company_name);
});
