<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Orders placed by me</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Review the orders you have submitted on behalf of buyers.
            </p>
        </div>

        <a href="{{ route('agent-portal.checkout-for-buyer') }}" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">
            Checkout for buyer
        </a>
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-950">
        <div class="grid gap-4 md:grid-cols-2">
            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">Search</span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Order number or buyer" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
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
                        <th class="px-5 py-4">Order</th>
                        <th class="px-5 py-4">Buyer</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">Total</th>
                        <th class="px-5 py-4">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    @forelse ($orders as $order)
                        <tr class="text-gray-700 dark:text-gray-200">
                            <td class="px-5 py-4 font-medium text-gray-900 dark:text-gray-100">{{ $order->order_number }}</td>
                            <td class="px-5 py-4 text-xs">{{ $order->buyer?->company_name ?? 'Buyer unavailable' }}</td>
                            <td class="px-5 py-4 text-xs">{{ str($order->status->value)->replace('_', ' ')->title() }}</td>
                            <td class="px-5 py-4 text-xs">${{ number_format((float) $order->order_total, 2) }}</td>
                            <td class="px-5 py-4 text-xs">{{ optional($order->ordered_at)->format('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                No agent orders found yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">
            {{ $orders->links() }}
        </div>
    </section>
</div>
