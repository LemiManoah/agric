<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Payment summary</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Monitor payment volume, lifecycle outcomes, and totals by method.
            </p>
        </div>

        <button type="button" wire:click="exportCsv" class="inline-flex items-center rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-900 dark:bg-emerald-900/40 dark:text-emerald-300 dark:hover:bg-emerald-900/60">
            Export CSV
        </button>
    </div>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Total payments</div>
            <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $summary['total_payments'] }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Successful payments</div>
            <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $summary['successful_payments'] }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Failed payments</div>
            <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $summary['failed_payments'] }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Refunded payments</div>
            <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $summary['refunded_payments'] }}</div>
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Totals by method</h2>
            <div class="mt-5 space-y-4">
                @foreach ($summary['payment_totals_by_method'] as $method => $amount)
                    <div class="flex items-center justify-between gap-4 text-sm">
                        <span class="text-gray-500 dark:text-gray-400">{{ str($method)->replace('_', ' ')->title() }}</span>
                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ number_format((float) $amount, 2) }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Totals by status</h2>
            <div class="mt-5 space-y-4">
                @foreach ($summary['payment_totals_by_status'] as $status => $amount)
                    <div class="flex items-center justify-between gap-4 text-sm">
                        <span class="text-gray-500 dark:text-gray-400">{{ str($status)->replace('_', ' ')->title() }}</span>
                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ number_format((float) $amount, 2) }}</span>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</div>
