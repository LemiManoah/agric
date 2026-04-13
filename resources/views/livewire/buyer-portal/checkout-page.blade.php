<div class="space-y-6">
    <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Checkout</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Confirm delivery details and submit the order. Payment capture will plug into this flow in a later phase.
            </p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(320px,0.9fr)]">
        <form wire:submit="submit" class="space-y-6">
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Delivery details</h2>
                <div class="mt-5 grid gap-5">
                    <div class="space-y-1">
                        <label for="delivery_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Delivery address</label>
                        <textarea id="delivery_address" wire:model.live="delivery_address" rows="4" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"></textarea>
                        @error('delivery_address') <span class="text-sm text-rose-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1">
                        <label for="buyer_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Buyer notes</label>
                        <textarea id="buyer_notes" wire:model.live="buyer_notes" rows="4" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"></textarea>
                        @error('buyer_notes') <span class="text-sm text-rose-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1">
                        <label for="payment_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Preferred payment method</label>
                        <select id="payment_method" wire:model.live="payment_method" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                            <option value="">To be confirmed later</option>
                            @foreach ($paymentMethods as $paymentMethod)
                                <option value="{{ $paymentMethod->value }}">{{ str($paymentMethod->value)->replace('_', ' ')->title() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </section>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700">
                    Submit order
                </button>
            </div>
        </form>

        <aside class="space-y-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Order summary</h2>
            <div class="space-y-4">
                @forelse ($cart->items as $item)
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900">
                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $item->product?->name ?? 'Unavailable product' }}</div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ number_format((float) $item->quantity, 2) }} x ${{ number_format((float) $item->unit_price_usd, 2) }}</div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-gray-300 px-4 py-10 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        The cart is empty.
                    </div>
                @endforelse
            </div>

            <div class="border-t border-gray-200 pt-4 dark:border-gray-800">
                <div class="flex items-center justify-between gap-4 text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Subtotal</span>
                    <span class="font-semibold text-gray-900 dark:text-gray-100">${{ number_format($subtotal, 2) }}</span>
                </div>
            </div>
        </aside>
    </div>
</div>
