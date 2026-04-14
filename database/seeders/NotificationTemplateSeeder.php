<?php

namespace Database\Seeders;

use App\Enums\NotificationChannel;
use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            'order_placed' => [
                'name' => 'Order placed',
                'subject' => 'Order {{order_number}} received',
                'email_body' => 'Hello {{buyer_name}}, your order {{order_number}} has been placed successfully for USD {{amount}}.',
                'sms_body' => 'AgroFresh: order {{order_number}} placed for USD {{amount}}.',
            ],
            'order_confirmed' => [
                'name' => 'Order confirmed',
                'subject' => 'Order {{order_number}} confirmed',
                'email_body' => 'Hello {{buyer_name}}, your order {{order_number}} is now confirmed.',
                'sms_body' => 'AgroFresh: order {{order_number}} confirmed.',
            ],
            'order_dispatched' => [
                'name' => 'Order dispatched',
                'subject' => 'Order {{order_number}} dispatched',
                'email_body' => 'Hello {{buyer_name}}, your order {{order_number}} has been dispatched.',
                'sms_body' => 'AgroFresh: order {{order_number}} dispatched.',
            ],
            'payment_received' => [
                'name' => 'Payment received',
                'subject' => 'Payment received for {{order_number}}',
                'email_body' => 'Hello {{buyer_name}}, we received your payment of USD {{amount}} for order {{order_number}}.',
                'sms_body' => 'AgroFresh: payment received for {{order_number}} amount USD {{amount}}.',
            ],
            'payment_failed' => [
                'name' => 'Payment failed',
                'subject' => 'Payment failed for {{order_number}}',
                'email_body' => 'Hello {{buyer_name}}, the payment attempt for order {{order_number}} failed.',
                'sms_body' => 'AgroFresh: payment failed for {{order_number}}.',
            ],
            'receipt_ready' => [
                'name' => 'Receipt ready',
                'subject' => 'Receipt ready for {{order_number}}',
                'email_body' => 'Hello {{buyer_name}}, a receipt is now ready for order {{order_number}}.',
                'sms_body' => 'AgroFresh: receipt ready for {{order_number}}.',
            ],
        ];

        foreach ($templates as $key => $template) {
            NotificationTemplate::query()->updateOrCreate(
                ['key' => $key.'_email', 'channel' => NotificationChannel::Email->value],
                [
                    'name' => $template['name'].' Email',
                    'subject' => $template['subject'],
                    'body' => $template['email_body'],
                    'is_active' => true,
                ],
            );

            NotificationTemplate::query()->updateOrCreate(
                ['key' => $key.'_sms', 'channel' => NotificationChannel::Sms->value],
                [
                    'name' => $template['name'].' SMS',
                    'subject' => null,
                    'body' => $template['sms_body'],
                    'is_active' => true,
                ],
            );
        }
    }
}
