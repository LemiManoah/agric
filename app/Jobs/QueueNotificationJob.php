<?php

namespace App\Jobs;

use App\Models\OutboundNotification;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class QueueNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $notificationId,
    ) {}

    public function handle(NotificationService $notificationService): void
    {
        $notification = OutboundNotification::query()->findOrFail($this->notificationId);

        if ($notification->status->value !== 'queued') {
            return;
        }

        $notificationService->markSent($notification);
    }
}
