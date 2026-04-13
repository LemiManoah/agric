<div class="space-y-6">
    <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div class="space-y-2">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">My cart</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Review current product selections, adjust quantities, and move to checkout when you are ready.
                </p>
            </div>

            <a href="{{ route('catalogue.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-900">
                Browse catalogue
            </a>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.7fr)_minmax(320px,0.9fr)]">
        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/60">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            <th class="px-5 py-4">Product</th>
                            <th class="px-5 py-4">Price</th>
                            <th class="px-5 py-4">Quantity</th>
                            <th class="px-5 py-4">Line total</th>
                            <th class="px-5 py-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-sm dark:divide-gray-800">
                        @forelse ($cart->items as $item)
                            <tr class="align-top text-gray-700 dark:text-gray-200">
                                <td class="px-5 py-4">
                                    <div class="flex items-start gap-4">
                                        <div class="h-16 w-16 overflow-hidden rounded-xl border border-gray-200 bg-gray-100 dark:border-gray-800 dark:bg-gray-900">
                                            @if ($item->product?->images->isNotEmpty())
                                                <img src="{{ \Illuminate\Support\Facades\Storage::disk(config('filesystems.default'))->url($item->product->images->first()->path) }}" alt="{{ $item->product->name }}" class="h-full w-full object-cover">
                                            @endif
                                        </div>
                                        <div class="space-y-1">
                                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ $item->product?->name ?? 'Unavailable product' }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $item->product?->supplier?->business_name ?? 'Supplier unavailable' }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">MOQ: {{ number_format((float) ($item->product?->minimum_order_quantity ?? 0), 2) }} {{ $item->product?->unit_of_measure }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300">
                                    ${{ number_format((float) $item->unit_price_usd, 2) }} / {{ $item->product?->unit_of_measure }}
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        <button type="button" wire:click="decreaseQuantity({{ $item->id }})" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 text-lg text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-900">-</button>
                                        <input wire:model.defer="quantities.{{ $item->id }}" type="number" step="0.01" min="0" class="w-24 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                        <button type="button" wire:click="increaseQuantity({{ $item->id }})" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 text-lg text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-900">+</button>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-xs font-medium text-gray-700 dark:text-gray-300">
                                    ${{ number_format((float) $item->quantity * (float) $item->unit_price_usd, 2) }}
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap items-center gap-3 text-xs font-medium">
                                        <button type="button" wire:click="updateQuantity({{ $item->id }})" class="text-blue-600 transition hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">Update</button>
                                        <button type="button" wire:click="removeItem({{ $item->id }})" class="text-rose-600 transition hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300">Remove</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Your cart is empty. Add products from the catalogue to start an order.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="space-y-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Order summary</h2>
            <div class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                <div class="flex items-center justify-between gap-4">
                    <span>Items</span>
                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $cart->items->count() }}</span>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <span>Subtotal</span>
                    <span class="font-medium text-gray-900 dark:text-gray-100">${{ number_format($subtotal, 2) }}</span>
                </div>
            </div>

            <div class="rounded-2xl border border-dashed border-emerald-300 bg-emerald-50 p-4 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-200">
                Payments and receipts are not enabled yet. Checkout currently creates the order and reserves stock.
            </div>

            <a href="{{ route('buyer-portal.checkout') }}" @class([
                'inline-flex w-full items-center justify-center rounded-lg px-4 py-3 text-sm font-medium text-white transition',
                'bg-emerald-600 hover:bg-emerald-700' => $cart->items->isNotEmpty(),
                'cursor-not-allowed bg-gray-400' => $cart->items->isEmpty(),
            ]) @if ($cart->items->isEmpty()) aria-disabled="true" @endif>
                Proceed to checkout
            </a>
        </aside>
    </div>
</div>
