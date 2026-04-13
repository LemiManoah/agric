<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Product catalogue summary</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                A lightweight M2 reporting foundation for catalogue health, stock posture, and listing coverage.
            </p>
        </div>

        <button type="button" wire:click="exportCsv" class="inline-flex items-center rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-900 dark:bg-emerald-900/40 dark:text-emerald-300 dark:hover:bg-emerald-900/60">
            Export CSV
        </button>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950"><div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Total products</div><div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($summary['total_products']) }}</div></div>
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950"><div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Active listings</div><div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($summary['active_listings']) }}</div></div>
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950"><div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Out of stock</div><div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($summary['out_of_stock_listings']) }}</div></div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Listings by category</h2>
            <div class="mt-5 space-y-3">
                @forelse ($summary['listings_by_category'] as $name => $count)
                    <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm dark:border-gray-800 dark:bg-gray-900">
                        <span class="text-gray-700 dark:text-gray-300">{{ $name }}</span>
                        <span class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($count) }}</span>
                    </div>
                @empty
                    <div class="text-sm text-gray-500 dark:text-gray-400">No category metrics available yet.</div>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Listings by supplier</h2>
            <div class="mt-5 space-y-3">
                @forelse ($summary['listings_by_supplier'] as $name => $count)
                    <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm dark:border-gray-800 dark:bg-gray-900">
                        <span class="text-gray-700 dark:text-gray-300">{{ $name }}</span>
                        <span class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($count) }}</span>
                    </div>
                @empty
                    <div class="text-sm text-gray-500 dark:text-gray-400">No supplier metrics available yet.</div>
                @endforelse
            </div>
        </section>
    </div>
</div>
