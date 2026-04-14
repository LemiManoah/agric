<div class="space-y-6">
    <a href="{{ route('admin.payments.index') }}" class="inline-flex items-center text-sm font-medium text-emerald-700 transition hover:text-emerald-800 dark:text-emerald-300 dark:hover:text-emerald-200">
        Back to payments
    </a>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.3fr)_minmax(340px,0.9fr)]">
        <section class="space-y-6">
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div class="space-y-2">
                        <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Payment</div>
                        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $payment->order?->order_number }}</h1>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</div>
                    </div>
                    <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                        {{ str($payment->status->value)->replace('_', ' ')->title() }}
                    </span>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Callback records</h2>
                <div class="mt-5 space-y-4">
                    @forelse ($payment->callbacks as $callback)
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900">
                            <div class="flex items-center justify-between gap-4">
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $callback->provider }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $callback->processed_at?->format('d M Y H:i') ?? 'Pending' }}</div>
                            </div>
                            <div class="mt-2 text-xs text-gray-600 dark:text-gray-300">Reference: {{ $callback->reference ?: 'N/A' }}</div>
                            <div class="mt-2 text-xs text-gray-600 dark:text-gray-300">Signature valid: {{ $callback->signature_valid ? 'Yes' : 'No' }}</div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-gray-300 px-4 py-6 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                            No callback records have been captured for this payment yet.
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Activity history</h2>
                <div class="mt-5 space-y-4">
                    @foreach ($activities as $activity)
                        <div class="border-l-2 border-emerald-300 pl-4 dark:border-emerald-700">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ str($activity->event ?: $activity->description)->replace('_', ' ')->title() }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $activity->created_at->format('d M Y H:i') }}{{ $activity->causer ? ' by '.$activity->causer->name : '' }}</div>
                        </div>
                    @endforeach
                </div>
            </section>
        </section>

        <aside class="space-y-6">
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Order summary</h2>
                <div class="mt-4 space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-4"><span class="text-gray-500 dark:text-gray-400">Order number</span><span class="font-medium text-gray-900 dark:text-gray-100">{{ $payment->order?->order_number }}</span></div>
                    <div class="flex items-center justify-between gap-4"><span class="text-gray-500 dark:text-gray-400">Buyer</span><span class="font-medium text-gray-900 dark:text-gray-100">{{ $payment->order?->buyer?->company_name ?? 'N/A' }}</span></div>
                    <div class="flex items-center justify-between gap-4"><span class="text-gray-500 dark:text-gray-400">Method</span><span class="font-medium text-gray-900 dark:text-gray-100">{{ str($payment->method->value)->replace('_', ' ')->title() }}</span></div>
                    <div class="flex items-center justify-between gap-4"><span class="text-gray-500 dark:text-gray-400">Reference</span><span class="font-medium text-gray-900 dark:text-gray-100">{{ $payment->gateway_transaction_reference ?: 'Pending' }}</span></div>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Receipt</h2>
                <div class="mt-4 text-sm text-gray-600 dark:text-gray-300">
                    @if ($payment->receipt)
                        <a href="{{ $payment->receipt->downloadUrl() }}" target="_blank" class="font-medium text-emerald-700 transition hover:text-emerald-800 dark:text-emerald-300 dark:hover:text-emerald-200">
                            Open stored receipt
                        </a>
                    @else
                        Receipt not generated yet.
                    @endif
                </div>
            </section>

            @canany(['confirm', 'refund'], $payment)
                <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Payment actions</h2>
                    <div class="mt-5">
                        <livewire:admin.payments.action-panel :payment="$payment" :key="'payment-actions-'.$payment->id.'-'.$payment->status->value" />
                    </div>
                </section>
            @endcanany
        </aside>
    </div>
</div>
