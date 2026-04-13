<div class="space-y-4">
    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-900/40 dark:text-emerald-300">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid gap-3 sm:grid-cols-2">
        @can('verify', $supplier)
            @if ($supplier->verification_status !== \App\Enums\VerificationStatus::Verified)
                <button
                    type="button"
                    wire:click="verify"
                    class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700"
                >
                    Verify supplier
                </button>
            @endif

            @if ($supplier->verification_status !== \App\Enums\VerificationStatus::Suspended)
                <button
                    type="button"
                    wire:click="suspend"
                    class="inline-flex items-center justify-center rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-medium text-amber-700 transition hover:bg-amber-100 dark:border-amber-900 dark:bg-amber-900/40 dark:text-amber-300 dark:hover:bg-amber-900/60"
                >
                    Suspend supplier
                </button>
            @endif
        @endcan
    </div>

    @can('toggleWarehouseLinked', $supplier)
        <button
            type="button"
            wire:click="toggleWarehouseLinked"
            class="inline-flex items-center justify-center rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700 transition hover:bg-blue-100 dark:border-blue-900 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60"
        >
            {{ $supplier->warehouse_linked ? 'Unlink warehouse access' : 'Mark as warehouse linked' }}
        </button>
    @endcan
</div>
