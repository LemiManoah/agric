<?php

namespace App\Livewire\Admin\Notifications;

use App\Enums\NotificationChannel;
use App\Enums\NotificationDeliveryStatus;
use App\Models\OutboundNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Notifications')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'template', except: '')]
    public string $templateKey = '';

    #[Url(as: 'channel', except: '')]
    public string $channel = '';

    #[Url(as: 'status', except: '')]
    public string $status = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('notifications.view'), 403);
    }

    public function updating(string $name, mixed $value): void
    {
        if (in_array($name, ['templateKey', 'channel', 'status'], true)) {
            $this->resetPage();
        }
    }

    public function render(): View
    {
        $query = $this->notificationQuery();

        return view('livewire.admin.notifications.index', [
            'channels' => NotificationChannel::cases(),
            'notifications' => $query->with('logs')->latest()->paginate(12),
            'statuses' => NotificationDeliveryStatus::cases(),
            'templateKeys' => (clone $query)->select('template_key')->distinct()->orderBy('template_key')->pluck('template_key'),
        ])->layout('components.layouts.app');
    }

    private function notificationQuery(): Builder
    {
        return OutboundNotification::query()
            ->when($this->templateKey !== '', fn (Builder $query) => $query->where('template_key', $this->templateKey))
            ->when($this->channel !== '', fn (Builder $query) => $query->where('channel', $this->channel))
            ->when($this->status !== '', fn (Builder $query) => $query->where('status', $this->status));
    }
}
