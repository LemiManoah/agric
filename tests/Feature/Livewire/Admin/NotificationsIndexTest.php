<?php

use App\Models\OutboundNotification;
use App\Models\User;
use Database\Seeders\NotificationTemplateSeeder;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(NotificationTemplateSeeder::class);
});

it('renders the notification index for a super admin', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    OutboundNotification::factory()->create([
        'template_key' => 'order_placed_email',
        'recipient' => 'buyer@example.test',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.notifications.index'))
        ->assertSuccessful()
        ->assertSee('Notifications')
        ->assertSee('order_placed_email');
});
