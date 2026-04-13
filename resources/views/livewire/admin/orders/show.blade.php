<div class="space-y-6">
    <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center text-sm font-medium text-emerald-700 transition hover:text-emerald-800 dark:text-emerald-300 dark:hover:text-emerald-200">
        Back to orders
    </a>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.3fr)_minmax(340px,0.9fr)]">
        <section class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div class="space-y-2">
                        <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Order number</div>
                        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $order->order_number }}</h1>
                    </div>
                    <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                        {{ str($order->status->value)->replace('_', ' ')->title() }}
                    </span>
                </div>
            </div>

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Line items</h2>
                <div class="mt-5 space-y-4">
                    @foreach ($order->items as $item)
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $item->product_name_snapshot }}</div>
                                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $item->supplier?->business_name ?? 'Supplier unavailable' }}</div>
                                </div>
                                <div class="text-right text-sm text-gray-600 dark:text-gray-300">
                                    <div>{{ number_format((float) $item->quantity, 2) }} x ${{ number_format((float) $item->unit_price_usd, 2) }}</div>
                                    <div class="mt-1 font-semibold text-gray-900 dark:text-gray-100">${{ number_format((float) $item->line_total_usd, 2) }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </section>

        <aside class="space-y-6">
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Buyer and agent</h2>
                <div class="mt-4 space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-4"><span class="text-gray-500 dark:text-gray-400">Buyer</span><span class="font-medium text-gray-900 dark:text-gray-100">{{ $order->buyer?->company_name ?? 'Buyer unavailable' }}</span></div>
                    <div class="flex items-center justify-between gap-4"><span class="text-gray-500 dark:text-gray-400">Contact</span><span class="font-medium text-gray-900 dark:text-gray-100">{{ $order->buyer?->contact_person_full_name ?? 'N/A' }}</span></div>
                    <div class="flex items-center justify-between gap-4"><span class="text-gray-500 dark:text-gray-400">Agent</span><span class="font-medium text-gray-900 dark:text-gray-100">{{ $order->agent?->full_name ?? 'Direct buyer order' }}</span></div>
                </div>
            </section>

            @canany(['updateStatus', 'cancel'], $order)
                <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Order actions</h2>
                    <div class="mt-5">
                        <livewire:admin.orders.status-action :order="$order" :key="'order-status-'.$order->id.'-'.$order->status->value" />
                    </div>
                </section>
            @endcanany

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Totals</h2>
                <div class="mt-4 space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-4"><span class="text-gray-500 dark:text-gray-400">Subtotal</span><span class="font-medium text-gray-900 dark:text-gray-100">${{ number_format((float) $order->subtotal, 2) }}</span></div>
                    <div class="flex items-center justify-between gap-4"><span class="text-gray-500 dark:text-gray-400">Discount</span><span class="font-medium text-gray-900 dark:text-gray-100">${{ number_format((float) $order->discount_applied, 2) }}</span></div>
                    <div class="flex items-center justify-between gap-4"><span class="text-gray-500 dark:text-gray-400">Total</span><span class="font-semibold text-gray-900 dark:text-gray-100">${{ number_format((float) $order->order_total, 2) }}</span></div>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Status history</h2>
                <div class="mt-5 space-y-4">
                    @foreach ($order->statusHistories as $history)
                        <div class="border-l-2 border-emerald-300 pl-4 dark:border-emerald-700">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ str($history->new_status->value)->replace('_', ' ')->title() }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $history->created_at->format('d M Y H:i') }}{{ $history->changedBy ? ' by '.$history->changedBy->name : '' }}</div>
                            @if ($history->notes)
                                <div class="mt-1 text-xs text-gray-600 dark:text-gray-300">{{ $history->notes }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        </aside>
    </div>
</div>
