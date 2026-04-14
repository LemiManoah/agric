<div class="space-y-6">
    <div class="space-y-2">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Notifications</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Review queued and processed outbound notification records across channels.
        </p>
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-950">
        <div class="grid gap-4 md:grid-cols-3">
            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">Template</span>
                <select wire:model.live="templateKey" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <option value="">All templates</option>
                    @foreach ($templateKeys as $templateKeyOption)
                        <option value="{{ $templateKeyOption }}">{{ $templateKeyOption }}</option>
                    @endforeach
                </select>
            </label>
            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">Channel</span>
                <select wire:model.live="channel" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <option value="">All channels</option>
                    @foreach ($channels as $channelOption)
                        <option value="{{ $channelOption->value }}">{{ strtoupper($channelOption->value) }}</option>
                    @endforeach
                </select>
            </label>
            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">Status</span>
                <select wire:model.live="status" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $statusOption)
                        <option value="{{ $statusOption->value }}">{{ str($statusOption->value)->replace('_', ' ')->title() }}</option>
                    @endforeach
                </select>
            </label>
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-950">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-900/60">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <th class="px-5 py-4">Template</th>
                        <th class="px-5 py-4">Recipient</th>
                        <th class="px-5 py-4">Channel</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">Message</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    @forelse ($notifications as $notification)
                        <tr class="align-top text-gray-700 dark:text-gray-200">
                            <td class="px-5 py-4">
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $notification->template_key }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $notification->created_at->format('d M Y H:i') }}</div>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300">{{ $notification->recipient }}</td>
                            <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300">{{ strtoupper($notification->channel->value) }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    {{ str($notification->status->value)->replace('_', ' ')->title() }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300">{{ \Illuminate\Support\Str::limit($notification->rendered_message, 120) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                No notifications match the current filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">
            {{ $notifications->links() }}
        </div>
    </section>
</div>
