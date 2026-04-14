<div class="flex flex-wrap gap-3">
    @can('confirm', $payment)
        <button type="button" wire:click="markSuccessful" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700">
            Mark successful
        </button>
        <button type="button" wire:click="markFailed" class="inline-flex items-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-amber-700">
            Mark failed
        </button>
    @endcan

    @can('refund', $payment)
        <button type="button" wire:click="markRefunded" class="inline-flex items-center rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-rose-700">
            Mark refunded
        </button>
    @endcan
</div>
