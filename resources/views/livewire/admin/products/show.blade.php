<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $product->name }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Product detail, supplier linkage, image gallery, price history, and activity history.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.products.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-900">
                Back to products
            </a>
            @can('update', $product)
                <a href="{{ route('admin.products.edit', $product) }}" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">
                    Edit product
                </a>
            @endcan
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(320px,1fr)]">
        <div class="space-y-6">
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <div><div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Category</div><div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $product->category?->name ?? 'Pending' }}</div></div>
                    <div><div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Supplier</div><div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $product->supplier?->business_name ?? 'Pending' }}</div></div>
                    <div><div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Quality grade</div><div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $product->qualityGrade?->name ?? 'None' }}</div></div>
                    <div><div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Price</div><div class="mt-1 text-sm text-gray-900 dark:text-gray-100">${{ number_format((float) $product->price_per_unit_usd, 2) }} / {{ $product->unit_of_measure }}</div></div>
                    <div><div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Minimum order</div><div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ number_format((float) $product->minimum_order_quantity, 2) }}</div></div>
                    <div><div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Stock available</div><div class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ number_format((float) $product->stock_available, 2) }}</div></div>
                </div>
                <div class="mt-5 text-sm text-gray-700 dark:text-gray-300">{{ $product->description ?: 'No product description provided yet.' }}</div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Image gallery</h2>
                <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @forelse ($product->images as $image)
                        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-900">
                            <img src="{{ Storage::disk(config('filesystems.default'))->url($image->path) }}" alt="{{ $product->name }}" class="h-56 w-full object-cover">
                        </div>
                    @empty
                        <div class="text-sm text-gray-500 dark:text-gray-400">No product images uploaded yet.</div>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="space-y-6">
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Price history</h2>
                <div class="mt-5 space-y-4">
                    @forelse ($product->priceHistories as $priceHistory)
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 text-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="font-medium text-gray-900 dark:text-gray-100">${{ number_format((float) $priceHistory->old_price_per_unit_usd, 2) }} -> ${{ number_format((float) $priceHistory->new_price_per_unit_usd, 2) }}</div>
                            <div class="mt-1 text-gray-500 dark:text-gray-400">{{ $priceHistory->changedBy?->name ?? 'System' }} / {{ $priceHistory->created_at?->diffForHumans() }}</div>
                        </div>
                    @empty
                        <div class="text-sm text-gray-500 dark:text-gray-400">No price changes have been recorded yet.</div>
                    @endforelse
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Activity history</h2>
                <div class="mt-5 space-y-4">
                    @forelse ($activities as $activity)
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900">
                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ str($activity->description)->replace('.', ' ')->replace('_', ' ')->title() }}</div>
                            <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $activity->causer?->name ?? 'System' }} / {{ $activity->created_at?->diffForHumans() }}</div>
                        </div>
                    @empty
                        <div class="text-sm text-gray-500 dark:text-gray-400">No product activity has been logged yet.</div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</div>
