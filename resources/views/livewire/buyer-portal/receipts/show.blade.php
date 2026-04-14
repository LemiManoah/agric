<div class="space-y-6">
    <a href="{{ route('buyer-portal.payments.index') }}" class="inline-flex items-center text-sm font-medium text-emerald-700 transition hover:text-emerald-800 dark:text-emerald-300 dark:hover:text-emerald-200">
        Back to payments
    </a>

    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
            <div class="space-y-2">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Receipt for {{ $receipt->order?->order_number }}</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Generated on {{ $receipt->generated_at?->format('d M Y H:i') }}.
                </p>
            </div>

            <button type="button" wire:click="download" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700">
                Download receipt
            </button>
        </div>

        <div class="mt-6 grid gap-4 rounded-2xl border border-gray-200 bg-gray-50 p-5 text-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between gap-4"><span class="text-gray-500 dark:text-gray-400">Order</span><span class="font-medium text-gray-900 dark:text-gray-100">{{ $receipt->order?->order_number }}</span></div>
            <div class="flex items-center justify-between gap-4"><span class="text-gray-500 dark:text-gray-400">Payment</span><span class="font-medium text-gray-900 dark:text-gray-100">{{ $receipt->payment?->currency }} {{ number_format((float) ($receipt->payment?->amount ?? 0), 2) }}</span></div>
            <div class="flex items-center justify-between gap-4"><span class="text-gray-500 dark:text-gray-400">Stored file</span><span class="font-medium text-gray-900 dark:text-gray-100">{{ basename($receipt->file_path) }}</span></div>
        </div>
    </section>
</div>
