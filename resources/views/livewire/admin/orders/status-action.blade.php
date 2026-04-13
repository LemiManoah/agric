<div class="flex flex-wrap items-center gap-3">
    @if ($order->status === \App\Enums\OrderStatus::Pending)
        <button type="button" wire:click="confirm" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">
            Confirm
        </button>
    @endif

    @if ($order->status === \App\Enums\OrderStatus::Confirmed)
        <button type="button" wire:click="process" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700">
            Process
        </button>
    @endif

    @if ($order->status === \App\Enums\OrderStatus::Processing)
        <button type="button" wire:click="markDispatched" class="inline-flex items-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-amber-700">
            Dispatch
        </button>
    @endif

    @if ($order->status === \App\Enums\OrderStatus::Dispatched)
        <button type="button" wire:click="deliver" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700">
            Deliver
        </button>
    @endif

    @if (in_array($order->status, [\App\Enums\OrderStatus::Pending, \App\Enums\OrderStatus::Confirmed, \App\Enums\OrderStatus::Processing], true))
        <button type="button" wire:click="cancel" class="inline-flex items-center rounded-lg border border-rose-300 px-4 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-50 dark:border-rose-900 dark:text-rose-300 dark:hover:bg-rose-900/30">
            Cancel
        </button>
    @endif
</div>
