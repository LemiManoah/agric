<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $product ? 'Edit product' : 'Create product' }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Configure supplier-linked catalogue listings, pricing, stock, and image presentation for the M2 foundation.
            </p>
        </div>

        <a href="{{ route('admin.products.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-900">
            Cancel
        </a>
    </div>

    <form wire:submit="save" class="space-y-6">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                <div class="space-y-1">
                    <x-forms.input label="Product name" name="name" wire:model.live="name" />
                </div>

                <div class="space-y-1">
                    <label for="product_category_id" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                    <select id="product_category_id" wire:model.live="product_category_id" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        <option value="">Select category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <label for="linked_supplier_id" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Supplier</label>
                    <select id="linked_supplier_id" wire:model.live="linked_supplier_id" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        <option value="">Select supplier</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->business_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <label for="quality_grade_id" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Quality grade</label>
                    <select id="quality_grade_id" wire:model.live="quality_grade_id" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        <option value="">None</option>
                        @foreach ($qualityGrades as $qualityGrade)
                            <option value="{{ $qualityGrade->id }}">{{ $qualityGrade->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <x-forms.input label="Unit of measure" name="unit_of_measure" wire:model.live="unit_of_measure" />
                </div>
                <div class="space-y-1">
                    <x-forms.input label="Warehouse SKU" name="warehouse_sku" wire:model.live="warehouse_sku" />
                </div>
                <div class="space-y-1">
                    <x-forms.input label="Price per unit (USD)" name="price_per_unit_usd" type="number" step="0.01" min="0" wire:model.live="price_per_unit_usd" />
                </div>
                <div class="space-y-1">
                    <x-forms.input label="Minimum order quantity" name="minimum_order_quantity" type="number" step="0.01" min="0.01" wire:model.live="minimum_order_quantity" />
                </div>
                <div class="space-y-1">
                    <x-forms.input label="Stock available" name="stock_available" type="number" step="0.01" min="0" wire:model.live="stock_available" />
                </div>

                <div class="space-y-1">
                    <label for="listing_status" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Listing status</label>
                    <select id="listing_status" wire:model.live="listing_status" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        @foreach ($listingStatuses as $status)
                            <option value="{{ $status->value }}">{{ str($status->value)->replace('_', ' ')->title() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-5 space-y-1">
                <label for="description" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                <textarea id="description" wire:model.live="description" rows="5" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"></textarea>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Images</h2>

            @if ($product?->images?->isNotEmpty())
                <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($product->images as $image)
                        @if (in_array($image->id, $retained_image_ids, true))
                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900">
                                <div class="mb-3 aspect-[4/3] overflow-hidden rounded-xl bg-gray-200 dark:bg-gray-800">
                                    <img src="{{ Storage::disk(config('filesystems.default'))->url($image->path) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                </div>
                                <button type="button" wire:click="removeExistingImage({{ $image->id }})" class="text-sm font-medium text-red-600 transition hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                                    Remove image
                                </button>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

            <div class="mt-5">
                <label for="uploaded_images" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300">Upload new images</label>
                <input id="uploaded_images" type="file" wire:model.live="uploaded_images" multiple accept="image/*" class="mt-2 block w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
            </div>
        </section>

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700">
                Save product
            </button>
        </div>
    </form>
</div>
