<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Payments</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Review order-linked payments, lifecycle status, and receipt readiness.
            </p>
        </div>

        @can('export', \App\Models\Payment::class)
            <button type="button" wire:click="exportCsv" class="inline-flex items-center rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-900 dark:bg-emerald-900/40 dark:text-emerald-300 dark:hover:bg-emerald-900/60">
                Export CSV
            </button>
        @endcan
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-950">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">Order number or reference</span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Order number, buyer, reference" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
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
            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">Method</span>
                <select wire:model.live="method" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <option value="">All methods</option>
                    @foreach (\App\Enums\PaymentMethod::cases() as $methodOption)
                        <option value="{{ $methodOption->value }}">{{ str($methodOption->value)->replace('_', ' ')->title() }}</option>
                    @endforeach
                </select>
            </label>
            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">Buyer</span>
                <select wire:model.live="buyerId" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <option value="">All buyers</option>
                    @foreach ($buyers as $buyer)
                        <option value="{{ $buyer->id }}">{{ $buyer->company_name }}</option>
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
                        <th class="px-5 py-4">Payment</th>
                        <th class="px-5 py-4">Buyer</th>
                        <th class="px-5 py-4">Method</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">Receipt</th>
                        <th class="px-5 py-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    @forelse ($payments as $payment)
                        <tr class="align-top text-gray-700 dark:text-gray-200">
                            <td class="px-5 py-4">
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $payment->order?->order_number }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}
                                    @if ($payment->gateway_transaction_reference)
                                        / {{ $payment->gateway_transaction_reference }}
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300">{{ $payment->order?->buyer?->company_name ?? 'Unavailable' }}</td>
                            <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300">{{ str($payment->method->value)->replace('_', ' ')->title() }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    {{ str($payment->status->value)->replace('_', ' ')->title() }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300">
                                {{ $payment->receipt ? 'Ready' : 'Pending' }}
                            </td>
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.payments.show', $payment) }}" class="text-xs font-medium text-blue-600 transition hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                No payments match the current filters yet.
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
