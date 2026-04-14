<?php

namespace App\Services;

use App\Enums\NotificationChannel;
use App\Enums\NotificationDeliveryStatus;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use App\Models\OutboundNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    public function __construct(
        private NotificationTemplateRenderer $renderer,
    ) {}

    public function queueTemplate(string $templateKey, string $channel, string $recipient, array $payload = [], mixed $notifiable = null): OutboundNotification
    {
        return DB::transaction(function () use ($templateKey, $channel, $recipient, $payload, $notifiable): OutboundNotification {
            $template = NotificationTemplate::query()
                ->whereIn('key', [$templateKey, $templateKey.'_'.$channel])
                ->where('channel', $channel)
                ->where('is_active', true)
                ->first();

            $rendered = $template
                ? $this->renderer->render($template->subject, $template->body, $payload)
                : [
                    'subject' => (string) str($templateKey)->replace('_', ' ')->title(),
                    'body' => json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '',
                ];

            $notification = OutboundNotification::query()->create([
                'notifiable_type' => $notifiable instanceof Model ? $notifiable->getMorphClass() : null,
                'notifiable_id' => $notifiable instanceof Model ? $notifiable->getKey() : null,
                'template_key' => $template?->key ?? $templateKey,
                'channel' => NotificationChannel::from($channel),
                'recipient' => $recipient,
                'subject' => $rendered['subject'],
                'payload' => $payload,
                'rendered_message' => $rendered['body'],
                'status' => NotificationDeliveryStatus::Queued,
            ]);

            $this->writeLog($notification, 'queued', [
                'template_found' => $template !== null,
                'requested_template_key' => $templateKey,
            ]);

            activity()
                ->performedOn($notification)
                ->event('notification.queued')
                ->withProperties([
                    'template_key' => $templateKey,
                    'channel' => $channel,
                    'recipient' => $recipient,
                ])
                ->log('notification.queued');

            return $notification->fresh(['logs', 'template']);
        });
    }

    public function markSent(OutboundNotification $notification, ?string $providerMessageId = null): OutboundNotification
    {
        return DB::transaction(function () use ($notification, $providerMessageId): OutboundNotification {
            $notification->forceFill([
                'status' => NotificationDeliveryStatus::Sent,
                'provider_message_id' => $providerMessageId,
                'sent_at' => now(),
                'failed_at' => null,
                'failure_reason' => null,
            ])->save();

            $this->writeLog($notification, 'sent', [
                'provider_message_id' => $providerMessageId,
            ]);

            return $notification->fresh(['logs', 'template']);
        });
    }

    public function markDelivered(OutboundNotification $notification, array $context = []): OutboundNotification
    {
        return DB::transaction(function () use ($context, $notification): OutboundNotification {
            $notification->forceFill([
                'status' => NotificationDeliveryStatus::Delivered,
                'delivered_at' => now(),
            ])->save();

            $this->writeLog($notification, 'delivered', $context);

            return $notification->fresh(['logs', 'template']);
        });
    }

    public function markFailed(OutboundNotification $notification, ?string $reason = null, array $context = []): OutboundNotification
    {
        return DB::transaction(function () use ($context, $notification, $reason): OutboundNotification {
            $notification->forceFill([
                'status' => NotificationDeliveryStatus::Failed,
                'failed_at' => now(),
                'failure_reason' => $reason,
            ])->save();

            $this->writeLog($notification, 'failed', array_merge($context, [
                'reason' => $reason,
            ]));

            return $notification->fresh(['logs', 'template']);
        });
    }

    private function writeLog(OutboundNotification $notification, string $event, ?array $details = null): NotificationLog
    {
        return NotificationLog::query()->create([
            'notification_id' => $notification->id,
            'event' => $event,
            'details' => $details,
        ]);
    }
}
