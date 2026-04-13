<div class="space-y-8">
    <section class="overflow-hidden rounded-[2rem] border border-emerald-200 bg-gradient-to-br from-emerald-950 via-emerald-900 to-lime-900 px-8 py-10 text-white shadow-xl">
        <div class="grid gap-8 lg:grid-cols-[minmax(0,1.4fr)_minmax(280px,0.9fr)]">
            <div class="space-y-4">
                <span class="inline-flex rounded-full border border-white/20 bg-white/10 px-4 py-1 text-xs font-semibold uppercase tracking-[0.2em]">AgroFresh Marketplace Preview</span>
                <h1 class="max-w-3xl font-serif text-4xl leading-tight md:text-5xl">Browse verified agro-supply listings before orders go live.</h1>
                <p class="max-w-2xl text-sm leading-7 text-emerald-50/85 md:text-base">
                    Explore supplier-linked catalogue items, inspect quality grades, and see the buying experience foundation that M2 introduces for AgroFresh AgriConnect.
                </p>
            </div>
            <div class="rounded-[1.5rem] border border-white/10 bg-black/10 p-5 backdrop-blur">
                <div class="text-xs uppercase tracking-[0.2em] text-emerald-100/70">Marketplace note</div>
                <p class="mt-3 text-sm leading-7 text-emerald-50/85">
                    Buyers and agents can now build carts and submit orders. Payments remain a clean placeholder for the next phase.
                </p>
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-950">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">Search</span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Product name or description" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            </label>
            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">Category</span>
                <select wire:model.live="categoryId" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">Quality grade</span>
                <select wire:model.live="qualityGradeId" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <option value="">All grades</option>
                    @foreach ($qualityGrades as $qualityGrade)
                        <option value="{{ $qualityGrade->id }}">{{ $qualityGrade->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">Availability</span>
                <select wire:model.live="availability" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <option value="in_stock">In stock</option>
                    <option value="out_of_stock">Out of stock</option>
                    <option value="all">All visible listings</option>
                </select>
            </label>
        </div>
    </section>

    <section class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($products as $product)
            <article class="overflow-hidden rounded-[1.75rem] border border-gray-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg dark:border-gray-800 dark:bg-gray-950">
                <div class="aspect-[4/3] bg-gray-100 dark:bg-gray-900">
                    @if ($product->images->isNotEmpty())
                        <img src="{{ Storage::disk(config('filesystems.default'))->url($product->images->first()->path) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full items-center justify-center text-sm text-gray-400 dark:text-gray-500">No image yet</div>
                    @endif
                </div>
                <div class="space-y-4 p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $product->category?->name ?? 'Catalogue item' }}</div>
                            <h2 class="mt-2 text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $product->name }}</h2>
                        </div>
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                            {{ str($product->listing_status->value)->replace('_', ' ')->title() }}
                        </span>
                    </div>
                    <p class="text-sm leading-7 text-gray-600 dark:text-gray-400">{{ \Illuminate\Support\Str::limit($product->description ?: 'Supplier-linked catalogue listing ready for browsing.', 120) }}</p>
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Price</div>
                            <div class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">${{ number_format((float) $product->price_per_unit_usd, 2) }} / {{ $product->unit_of_measure }}</div>
                        </div>
                        <div class="flex flex-wrap items-center justify-end gap-3">
                            @auth
                                @if (auth()->user()->hasRole('buyer') || auth()->user()->hasRole('agent'))
                                    <div class="flex items-center gap-2">
                                        <input wire:model.defer="quantities.{{ $product->id }}" type="number" step="0.01" min="{{ (float) $product->minimum_order_quantity }}" placeholder="{{ (float) $product->minimum_order_quantity }}" class="w-24 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                        <button type="button" wire:click="addToCart({{ $product->id }})" class="inline-flex items-center rounded-lg border border-emerald-300 px-4 py-2 text-sm font-medium text-emerald-700 transition hover:bg-emerald-50 dark:border-emerald-800 dark:text-emerald-300 dark:hover:bg-emerald-900/30">
                                            Add to cart
                                        </button>
                                    </div>
                                @endif
                            @endauth
                            <a href="{{ route('catalogue.show', $product) }}" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700">
                                View details
                            </a>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-400">
                No catalogue products match the current filters yet.
            </div>
        @endforelse
    </section>

    <div>{{ $products->links() }}</div>
</div>
