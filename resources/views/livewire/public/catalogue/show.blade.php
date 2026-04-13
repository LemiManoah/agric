<div class="space-y-8">
    <a href="{{ route('catalogue.index') }}" class="inline-flex items-center text-sm font-medium text-emerald-700 transition hover:text-emerald-800 dark:text-emerald-300 dark:hover:text-emerald-200">
        Back to catalogue
    </a>

    <div class="grid gap-8 lg:grid-cols-[minmax(0,1.15fr)_minmax(320px,0.85fr)]">
        <section class="space-y-4">
            <div class="overflow-hidden rounded-[2rem] border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-950">
                @if ($product->images->isNotEmpty())
                    <img src="{{ Storage::disk(config('filesystems.default'))->url($product->images->first()->path) }}" alt="{{ $product->name }}" class="h-[28rem] w-full object-cover">
                @else
                    <div class="flex h-[28rem] items-center justify-center text-sm text-gray-400 dark:text-gray-500">No image available yet</div>
                @endif
            </div>

            @if ($product->images->count() > 1)
                <div class="grid gap-4 md:grid-cols-3">
                    @foreach ($product->images->slice(1) as $image)
                        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-950">
                            <img src="{{ Storage::disk(config('filesystems.default'))->url($image->path) }}" alt="{{ $product->name }}" class="h-40 w-full object-cover">
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="space-y-6 rounded-[2rem] border border-gray-200 bg-white p-8 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="space-y-3">
                <div class="text-xs uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">{{ $product->category?->name ?? 'Catalogue item' }}</div>
                <h1 class="font-serif text-4xl leading-tight text-gray-900 dark:text-gray-100">{{ $product->name }}</h1>
                <p class="text-sm leading-7 text-gray-600 dark:text-gray-400">{{ $product->description ?: 'This supplier-linked catalogue item is ready for public browsing while order workflows are still being built.' }}</p>
            </div>

            <div class="grid gap-4 rounded-2xl border border-gray-200 bg-gray-50 p-5 text-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between gap-4"><span class="text-gray-500 dark:text-gray-400">Supplier</span><span class="font-medium text-gray-900 dark:text-gray-100">{{ $product->supplier?->business_name ?? 'Pending' }}</span></div>
                <div class="flex items-center justify-between gap-4"><span class="text-gray-500 dark:text-gray-400">Quality grade</span><span class="font-medium text-gray-900 dark:text-gray-100">{{ $product->qualityGrade?->name ?? 'Not graded' }}</span></div>
                <div class="flex items-center justify-between gap-4"><span class="text-gray-500 dark:text-gray-400">Price</span><span class="font-medium text-gray-900 dark:text-gray-100">${{ number_format((float) $product->price_per_unit_usd, 2) }} / {{ $product->unit_of_measure }}</span></div>
                <div class="flex items-center justify-between gap-4"><span class="text-gray-500 dark:text-gray-400">Minimum order</span><span class="font-medium text-gray-900 dark:text-gray-100">{{ number_format((float) $product->minimum_order_quantity, 2) }}</span></div>
                <div class="flex items-center justify-between gap-4"><span class="text-gray-500 dark:text-gray-400">Stock available</span><span class="font-medium text-gray-900 dark:text-gray-100">{{ number_format((float) $product->stock_available, 2) }}</span></div>
            </div>

            <div class="rounded-2xl border border-dashed border-emerald-300 bg-emerald-50 p-5 dark:border-emerald-800 dark:bg-emerald-900/20">
                <div class="text-sm font-semibold text-emerald-800 dark:text-emerald-200">Future action</div>
                <p class="mt-2 text-sm leading-7 text-emerald-700 dark:text-emerald-300">
                    Cart and enquiry actions are not live yet. Buyers will be able to act on this listing in a later implementation batch.
                </p>
            </div>
        </section>
    </div>
</div>
