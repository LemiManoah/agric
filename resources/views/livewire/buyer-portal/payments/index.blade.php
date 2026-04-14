<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">My payments</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Track payment attempts, status, and receipt availability for your orders.
            </p>
        </div>

        <label class="space-y-2 text-sm">
            <span class="font-medium text-gray-700 dark:text-gray-300">Status</span>
            <select wire:model.live="status" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                <option value="">All statuses</option>
                @foreach (\App\Enums\PaymentLifecycleStatus::cases() as $statusOption)
                    <option value="{{ $statusOption->value }}">{{ str($statusOption->value)->replace('_', ' ')->title() }}</option>
                @endforeach
            </select>
        </label>
    </div>

    <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-950">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-900/60">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <th class="px-5 py-4">Order</th>
                        <th class="px-5 py-4">Method</th>
                        <th class="px-5 py-4">Amount</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">Receipt</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    @forelse ($payments as $payment)
                        <tr class="align-top text-gray-700 dark:text-gray-200">
                            <td class="px-5 py-4">
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $payment->order?->order_number }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $payment->created_at->format('d M Y H:i') }}</div>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300">{{ str($payment->method->value)->replace('_', ' ')->title() }}</td>
                            <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300">{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    {{ str($payment->status->value)->replace('_', ' ')->title() }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                @if ($payment->receipt)
                                    <a href="{{ route('buyer-portal.receipts.show', $payment->receipt) }}" class="text-xs font-medium text-emerald-700 transition hover:text-emerald-800 dark:text-emerald-300 dark:hover:text-emerald-200">
                                        View receipt
                                    </a>
                                @else
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Pending</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                No payments are available yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">
            {{ $payments->links() }}
        </div>
    </section>
</div>
