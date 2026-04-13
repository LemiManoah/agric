<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Product listings</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Manage supplier-linked catalogue listings, stock visibility, and pricing before cart and ordering are introduced.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            @can('export', \App\Models\Product::class)
                <button type="button" wire:click="exportCsv" class="inline-flex items-center rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-900 dark:bg-emerald-900/40 dark:text-emerald-300 dark:hover:bg-emerald-900/60">
                    Export CSV
                </button>
            @endcan

            @can('create', \App\Models\Product::class)
                <a href="{{ route('admin.products.create') }}" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">
                    Create product
                </a>
            @endcan
        </div>
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-950">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">Search</span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Name or SKU" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
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
                <span class="font-medium text-gray-700 dark:text-gray-300">Supplier</span>
                <select wire:model.live="supplierId" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <option value="">All suppliers</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->business_name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">Status</span>
                <select wire:model.live="listingStatus" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <option value="">All statuses</option>
                    @foreach (\App\Enums\ListingStatus::cases() as $status)
                        <option value="{{ $status->value }}">{{ str($status->value)->replace('_', ' ')->title() }}</option>
                    @endforeach
                </select>
            </label>
            <label class="space-y-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">Quality grade</span>
                <select wire:model.live="qualityGradeId" class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <option value="">All quality grades</option>
                    @foreach ($qualityGrades as $qualityGrade)
                        <option value="{{ $qualityGrade->id }}">{{ $qualityGrade->name }}</option>
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
                        <th class="px-5 py-4">Product</th>
                        <th class="px-5 py-4">Category</th>
                        <th class="px-5 py-4">Supplier</th>
                        <th class="px-5 py-4">Price</th>
                        <th class="px-5 py-4">Stock</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    @forelse ($products as $product)
                        <tr class="align-top text-gray-700 dark:text-gray-200">
                            <td class="px-5 py-4">
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $product->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $product->warehouse_sku ?: 'No SKU' }}</div>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300">{{ $product->category?->name ?? 'Pending' }}</td>
                            <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300">{{ $product->supplier?->business_name ?? 'Pending' }}</td>
                            <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300">${{ number_format((float) $product->price_per_unit_usd, 2) }} / {{ $product->unit_of_measure }}</td>
                            <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300">{{ number_format((float) $product->stock_available, 2) }}</td>
                            <td class="px-5 py-4 text-xs text-gray-600 dark:text-gray-300">{{ str($product->listing_status->value)->replace('_', ' ')->title() }}</td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap items-center gap-3 text-xs font-medium">
                                    @can('view', $product)
                                        <a href="{{ route('admin.products.show', $product) }}" class="text-blue-600 transition hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">View</a>
                                    @endcan
                                    @can('update', $product)
                                        <a href="{{ route('admin.products.edit', $product) }}" class="text-emerald-600 transition hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300">Edit</a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                No products match the current filters yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">
            {{ $products->links() }}
        </div>
    </section>
</div>
