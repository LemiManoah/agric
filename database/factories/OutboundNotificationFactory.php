<?php

namespace Database\Factories;

use App\Enums\NotificationChannel;
use App\Enums\NotificationDeliveryStatus;
use App\Models\OutboundNotification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OutboundNotification>
 */
class OutboundNotificationFactory extends Factory
{
    protected $model = OutboundNotification::class;

    public function definition(): array
    {
        return [
            'template_key' => fake()->slug(2),
            'channel' => fake()->randomElement(NotificationChannel::cases()),
            'recipient' => fake()->safeEmail(),
            'subject' => fake()->sentence(),
            'payload' => ['buyer_name' => fake()->name(), 'order_number' => 'AGF-'.now()->format('Y').'-'.fake()->numerify('#####')],
            'rendered_message' => fake()->paragraph(),
            'status' => NotificationDeliveryStatus::Queued,
        ];
    }
}
