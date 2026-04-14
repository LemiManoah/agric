<?php

namespace Database\Factories;

use App\Enums\NotificationChannel;
use App\Models\NotificationTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationTemplate>
 */
class NotificationTemplateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(2),
            'channel' => fake()->randomElement(NotificationChannel::cases()),
            'name' => fake()->sentence(3),
            'subject' => fake()->sentence(),
            'body' => 'Hello {{buyer_name}}, update for {{order_number}}.',
            'is_active' => true,
        ];
    }
}
